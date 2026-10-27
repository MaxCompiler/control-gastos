<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Control de gastos</title>
    <link rel="stylesheet" href="/control-gastos/public/css/estilos.css">
</head>
<body>
    <main class="auth">
        <h1>Iniciar sesión</h1>

        <?php if(!empty($_SESSION['mensaje'])): ?>
            <p class="exito"><?= htmlspecialchars($_SESSION['mensaje']) ?></p>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if(!empty($errores)): ?>
            <ul class="errores">
                <?php foreach($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" action="index.php?accion=login">
            <label for="email">Correo</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Entrar</button>
        </form>

        <p>¿No tienes cuenta? <a href="index.php?accion=registro">Regístrate</a></p>
    </main>
</body>
</html>