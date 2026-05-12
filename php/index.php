<?php
session_start();

require_once __DIR__ . '/class/class.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (login($email, $password)) {
        $_SESSION["usuario"] = $email;

        header("Location: panel.php");
        exit;
    } else {
        $error = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/img/favicon.ico" rel="icon" type="image">
    <link href="assets/styles/style.css" rel="stylesheet">
    <title>Login | SIED</title>
</head>
<body class="auth-page">

    <div class="login-container">
        <div class="login-header">
            <h1>Bienvenido Docente</h1>
            <p>Inicia sesión para continuar</p>
        </div>

        <?php if (!empty($error)): ?>
            <p style="color: var(--danger-color); text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>

            <button type="submit">Iniciar sesión</button>
        </form>

        <div class="footer-links">
            <a href="signup.php">Registrarse</a>
        </div>
    </div>
</body>
</html>