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

        

        <form method="POST" action="" onsubmit="return validarFormulario(event)">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" title="Correo Electrónico" novalidate>
                <span id="error-email" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" minlength="8" novalidate>
                <span id="error-password" class="error-message"></span>
            </div>

            <button type="submit">Iniciar sesión</button>
        </form>
        <script>
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const emailError = document.getElementById('error-email');
            const passwordError = document.getElementById('error-password');

           
            emailInput.addEventListener('input', () => {
                emailError.classList.remove('show');
                emailInput.classList.remove('input-error');
            });

            passwordInput.addEventListener('input', () => {
                passwordError.classList.remove('show');
                passwordInput.classList.remove('input-error');
            });
            function validarFormatoEmail(email) {
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return regexEmail.test(email);
            }

            function validarFormulario(event) {
                const email = emailInput.value.trim();
                const password = passwordInput.value.trim();
                let isValid = true;

                // Validar email
                 if (!validarFormatoEmail(email)) {
                    emailError.textContent = 'Favor ingresar un correo válido';
                    emailError.classList.add('show');
                    emailInput.classList.add('input-error');
                    isValid = false;
                }
                if (!email) {
                    emailError.textContent = 'Favor ingresar un correo electrónico';
                    emailError.classList.add('show');
                    emailInput.classList.add('input-error');
                    isValid = false;
                }

                // Validar contraseña
                if (!password) {
                    passwordError.textContent = 'Favor ingresar tu contraseña';
                    passwordError.classList.add('show');
                    passwordInput.classList.add('input-error');
                    isValid = false;
                }

                if (!isValid) {
                    event.preventDefault();
                    return false;
                }

                return true;
            }

            
        </script>

        <div class="footer-links">
            <a href="signup.php">Registrarse</a>
        </div>
    </div>
</body>
</html>