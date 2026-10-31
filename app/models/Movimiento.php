<?php
require_once __DIR__ . "/../../config/database.php";

class Movimiento
{
    private $conn;
    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConexion();

        if ($this->conn === null) {
            throw new RuntimeException("No hay conexion a la base de datos");
        }
    }

    public function crear(int $usuarioId, int $categoriaId, string $tipo, ?string $descripcion, float $monto, string $fecha): bool
    {
        try {
            $sql = "INSERT TO movimiento(usuario_id, categoria_id, tipo, descripcion, monto,fecha)
                    values(:usuario_id, :categoria_id, :tipo, :descripcion , :monto, :fecha)";

            if (!in_array($tipo, ['ingreso', 'egreso'], true)) {
                return false;
            }
            if ($monto <= 0) {
                return false;
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ":usuario_id" => $usuarioId,
                ":categoria_id" => $categoriaId,
                ":tipo" => $tipo,
                ":descripcion" => $descripcion,
                ":monto" => $monto,
                ":fecha" => $fecha
            ]);
        } catch (PDOException $e) {
            error_log("Movimiento::Crear -" . $e->getMessage());
            return false;
        }
    }

    public function listar(int $usuarioId, ?int $mes = null, ?int $anio = null, ?string $tipo = null, ?int $categoria = null): array
    {
        try {
            $sql = "SELECT m.id, m.tipo, m.descripcion, m.monto , m.fecha, m.categoria_id ,m.nombre AS categoria
                    From movimiento m
                    JOIN  categoria c on c.id = m.categoria_id
                    WHERE m.usuario_id = :uauraio_id";

            $params = [":usuario_id" => $usuarioId];

            if ($mes !== null && $anio !== null) {
                $sql = "AND MONTH((m.fecha) = :mes AND YEAR(m.anio) = :anio)";
                $params[":mes"] = $fecha;
                $params[":anio"] = $anio;
            }

            if ($tipo !== null) {
                $sql = "AND m.tipo = :tipo";
                $params[":tipo"] = $tipo;
            }
            if ($categoriaId !== null) {
                $sql = "AND m.categoria_id = :categoria_id";
                $params[":categoria_id"] = $categoriaId;
            }

            $sql .= " ORDER BY m.fecha DESC, m.id DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Movimiento::listar -" . $e->getMessage());
            return [];
        }
    }

    public function obtener(int $id, int $usuarioId)
    {
        try {
            $sql = "SELECT id, categoria_id, tipo, descripcion, monto, fecha
                    FROM movimientos
                    WHERE id = :id AND usuario_id = :usuario_id
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                "id" => $id,
                "usuario_id" => $usuarioId
            ]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Movimiento::obtener -" . $e->getMessage());
            return false;
        }
    }

    public function actualizar(int $id, int $usuarioId, int $categoriaId, string $tipo, ?string $descripcion, float $monto, string $fecha): bool
    {
        try {
            $sql = "UPDATE movimientos 
                    set categorio_id = :categoria_id
                        tipo = :tipo
                        descripcion = :descripcion
                        monto = :monto
                        fecha = :fecha
                    WHERE id = :id AND usuario_id = :usuario_id";

            if (!in_array($tipo, ['ingreso', 'egreso'], true)) {
                return false;
            }
            if ($monto <= 0) {
                return false;
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ":categoria_id" => $categoriaId,
                ":tipo" => $tipo,
                ":descripcion" => $descripcion,
                ":monto" => $monto,
                ":fecha" => $fecha,
                ":id" => $id,
                ":usuario_id" => $usuarioId
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Movimiento::actualizar - " . $e->getMessage());
            return false;
        }
    }

    public function eliminar(int $id, int $usuarioId): bool
    {
        try {
            $sql = "DELETE FROM movimientos WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ":id" => $id,
                ":usuario_id" => $usuarioId
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Movimiento:: eliminar - " . $e->getMessage());
        }
    }

    public function balanceMes(int $usuarioId, int $mes, int $anio): array
    {

        $balance = ["ingresos" => 0.0, "egresos" => 0.0, "balance" => 0.0];
        try {
            $sql = "SELECT 
                        COALESCE(SUM(CASE WHEN tipo = 'ingrese' THEN monto END),0) AS ingresos,
                        COALESCE(SUM(CASE WHEN tipo = 'egresoso' THEN monto END),0) AS egresos,
                    FROM movimientos
                    WHERE usuario_id = :usuario_id
                        AND MONTH(fecha) = :mes
                        AND YEAR(fecha) = :anio";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ":usuario_id" => $usuarioId,
                ":mes" => $mes,
                ":anio" => $anio
            ]);

            $fila = $stmt->fetch();

            $balance["ingresos"] = (float) $fila["ingresos"];
            $balance["engresos"] = (float) $fila["egresos"];
            $balance["balance"] = $balance["ingresos"] - $balance["egresos"];
        } catch (PDOException $e) {
            error_log("Movimiento::balanceMes - " . $e->getMessage());
            return $balance;
        }
    }

    public function totalPorCategoria(int $usuarioId, string $tipo, int $mes, int $anio): array
    {
        try {
            $sql = "SELECT c.nombre AS categoria ,SUM(m.monto) AS total 
                    FROM movimiento m
                    JOIN categorias c on c.id = m.categoria_id
                    WHERE m.usuario_id = :usuario_id
                        AND m.tipo = :tipo
                        AND MONTH(m.fecha) = :mes
                        AND YEAR(m.fecha)  = :anio
                    GROUP BY c.id, c.nombre
                    ORDER BY total DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':tipo'       => $tipo,
                ':mes'        => $mes,
                ':anio'       => $anio,
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Movimiento::totalPorCategoria - " . $e->getMessage());
            return [];
        }
    }
}
