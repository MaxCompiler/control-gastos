<?php
session_start();

require __DIR__ . "/../app/controllers/AuthController.php";

$accion = $_GET['accion'] ?? 'login';
$auth = new AuthController();

switch($accion){
    case 'registro':
        $auth->registro();
        break;
    case 'login':
        $auth->login();
        break;
    case 'logout':
        $auth->logout();
        break;
    case 'dashboard':
        if(empty($_SESSION['usuario_id'])){
            header('Location: index.php?accion=login');
            exit;
        }
        echo '<h1>Hola '.htmlspecialchars($_SESSION['usuario_nombre']) .'</h1>';
        echo '<p>Aquí irá el dashboard.</p>';
        echo '<a href="index.php?accion=logout">Cerrar sesión</a>';
        break;

    default:
        http_response_code(404);
        echo 'pagina no encontrada';
}