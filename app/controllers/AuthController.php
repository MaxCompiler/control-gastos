<?php
require_once __DIR__ . "/../models/Usuario.php";

class AuthController
{

    public function registro()
    {
        try {
            $errores = [];
            $nombre = "";
            $email = "";
            $password = "";
            $confirmar = "";

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $nombre = trim($_POST['nombre'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirmar = $_POST['password_confirmar'] ?? '';

                if ($nombre === '') {
                    $errores[] = "EL nombre deber ser obligatorio.";
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = "El correo no es valido.";
                }
                if (strlen($password) < 6) {
                    $errores[] = "La contraseña deber contener minimo 6 caracteres.";
                }
                if ($password != $confirmar) {
                    $errores[] = "Las contraseñas no coinciden.";
                }

                if (empty($errores)) {
                    $usuario = new Usuario();

                    if ($usuario->buscarPorEmail($email)) {
                        $errores[] = "Ese correo ya está registrado.";
                    } elseif ($usuario->crear($nombre, $email, $password)) {
                        $_SESSION["mensaje"] = "Cuenta creada. Ya puedes iniciar sesión.";
                        header("Location: index.php?accion=login");
                        exit;
                    } else {
                        $errores[] = "No se pudo crear la cuenta. Intenta de nuevo.";
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error al registrarse ". $e->getMessage());
        }

        require __DIR__ . "/../views/auth/registro.php";
    }

    public function login()
    {
        try {
            $errores = [];
            $email = "";

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';

                if ($email === '' || $password === '') {
                    $errores[] = 'Completa el correo y la contraseña.';
                } else {
                    $modelo = new Usuario();

                    $usuario = $modelo->auntenticar($email, $password);

                    if ($usuario) {
                        session_regenerate_id(true);
                        $_SESSION['usuario_id'] = $usuario['id'];
                        $_SESSION['usuario_nombre'] = $usuario['nombre'];
                        header('Location: index.php?accion=dashboard');
                        exit;
                    }
                    $errores[] = 'Correo o contraseña incorrectos.';
                }
            }
        } catch (PDOException $e) {
            error_log("Error al iniciar sesion: " . $e->getMessage());
            exit;
        }

        require __DIR__ . "/../views/auth/login.php";
    }

    public function logout()
    {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php?accion=login');
        exit;
    }
}
