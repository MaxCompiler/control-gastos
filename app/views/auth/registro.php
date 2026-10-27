<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - Control de gastos</title>
    <link rel="stylesheet" href="/control-gastos/public/css/estilos.css">
</head>
<body>
    <main class="auth">
        <h1>Crear Cuenta</h1>
        <?php if(!empty($errores)): ?>
            <ul class="errores">
                <?php foreach($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>

                <?php endforeach ?>
            </ul>
        <?php endif?>

        <form action="index.php?accion=registro" method="POST">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

            <label for="email">Correo</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" minlength="6" required>

            <label for="password_confirmar">Repetir contraseña</label>
            <input type="password" id="password_confirmar" name="password_confirmar" minlength="6" required>

            <button type="submit">Registrarme</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="index.php?accion=login">Inicia sesión</a></p>
    </main>
    
</body>
</html>