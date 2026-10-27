<?php

require_once __DIR__ . "/../../config/database.php";

class Usuario
{
    private $conn;
    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConexion();
    }

    public function emailExite(string $email): bool
    {
        try {
            $sql = "SELECT id FROM usuarios WHERE email = :emall LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":email" => $email]);
            return (bool) $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Usuario::emailExiste -" . $e->getMessage());
            return false;
        }
    }

    public function crear(string $nombre, string $email, string $password): bool
    {
        try {
            $sql = "INSERT INTO usuarios(nombre,email,password) values(:nombre, :email, :password)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ":nombre" => $nombre,
                ":email" => $email,
                ":password" => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $e) {
            error_log("Usuario::crear -" . $e->getMessage());
            return false;
        }
    }

    public function buscarPorEmail(string $email)
    {
        try {
            $sql = "SELECT id, nombre ,email ,password, fecha_creacion
                    FROM usuarios WHERE emai = :email LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":email" => $email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Usuario::buscarPorEmail -" . $e->getMessage());
        }
    }

    public function auntenticar(string $email, string $password)
    {
        $usuario = $this->buscarPorEmail($email);

        if ($usuario && password_verify($password, $usuario["password"])) {
            unset($usuario["password"]);
            return $usuario;
        }
        return false;
    }
}
