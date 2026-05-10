
<?php
session_start();

require_once __DIR__ . '/class/class.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $lastname = trim($_POST["lastname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (register($name, $lastname, $email, $password)) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Error al registrar el usuario.";
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
    <title>Sign Up | SIED</title>
</head>
<body class="auth-page">
    <div class="login-container">
        <div class="login-header">
            <h1>Registrarse</h1>
            <p>Crea una nueva cuenta para continuar</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="lastname">Apellido</label>
                <input type="text" id="lastname" name="lastname" required>
            </div>

            <div class="form-group">
                <label for="email">Correo Electronico</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Registrarse</button>
        </form>

        <div class="footer-links">
            <a href="index.php">Cancelar</a>
        </div>
    </div>
</body>
</html>
