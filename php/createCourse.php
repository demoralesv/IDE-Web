<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/class/BackendFacade.php';

$backend = new BackendFacade();

$usuario = $_SESSION["usuario"];
$nombre = $backend->getTeacherName($usuario);

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $code = trim($_POST["code"] ?? "");
    $group = trim($_POST["group"] ?? "");

    $teacherId = $_SESSION["ID"] ?? null;
    
    if ($name === "" || $code === "" || $group === "") {
        $error = "Favor ingresar todos los datos del curso.";
    } elseif ($teacherId === null) {
        $error = "No se pudo identificar al profesor.";
    } else {
        if ($backend->addCourse($name, $code, $group, $teacherId)) {
            $success = "Curso creado correctamente.";
        } else {
            $error = "Ocurrió un error al crear el curso.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Curso | SIED</title>
    <link href="/assets/img/favicon.ico" rel="icon" type="image">
    <link href="assets/styles/style.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/b539121292.js" crossorigin="anonymous"></script>
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . '/menu.php'; ?>

    <main class="app-main">

        <section class="topbar">
            <h1>Crear Curso</h1>
            <p>Complete la información necesaria para registrar un nuevo curso en el sistema.</p>
        </section>

        <section class="dashboard-card">

            <h2>Información del curso</h2>
            <p>Ingrese el nombre, código y grupo del curso que desea crear.</p>

            <?php if ($success !== "") { ?>
                <div class="message success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="message error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <form method="POST" action="/addCourse">

                <div class="form-group">
                    <label for="name">Nombre del curso</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Ejemplo: Introducción a la Programación"
                        value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="code">Código del curso</label>
                    <input 
                        type="text" 
                        id="code" 
                        name="code" 
                        placeholder="Ejemplo: IC1802"
                        value="<?php echo htmlspecialchars($_POST["code"] ?? ""); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="group">Grupo</label>
                    <input 
                        type="text" 
                        id="group" 
                        name="group" 
                        placeholder="Ejemplo: 1"
                        value="<?php echo htmlspecialchars($_POST["group"] ?? ""); ?>"
                        required
                    >
                </div>

                <button type="submit">
                    <i class="fa-solid fa-plus"></i>
                    Crear curso
                </button>
            </form>


        </section>

    </main>

</div>

</body>
</html>