<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/styles/style.css" rel="stylesheet">
    <title>Registrarse | SIED</title>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Registrarse</h1>
            <p>Crea una nueva cuenta para continuar</p>
        </div>

       

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Correo Electronico</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Iniciar sesión</button>
        </form>

        <div class="footer-links">
            <a href="signup.php">Registrarse</a>
        </div>
    </div>
</body>
</html>
