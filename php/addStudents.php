<?php
session_start();

require_once __DIR__ . '/class/BackendFacade.php';

$backend = new BackendFacade();

if (!isset($_SESSION["usuario"])) {
    header("Location: /");
    exit;
}

$usuario = $_SESSION["usuario"];
$nombre = $backend->getTeacherName($usuario);
$teacherId = $_SESSION["ID"] ?? null;

$courseId = filter_var($ID ?? null, FILTER_VALIDATE_INT);
$search = trim($_GET["search"] ?? "");

$error = "";
$success = "";
$selectedCourse = null;
$students = [];

if ($teacherId === null) {
    $error = "No se pudo identificar al profesor.";
} elseif ($courseId === false || $courseId === null) {
    $error = "No se pudo identificar el curso.";
} else {
    $selectedCourse = $backend->getCourseByIdAndTeacher($courseId, $teacherId);

    if (!$selectedCourse) {
        $error = "El curso no existe o no pertenece a este profesor.";
    } else {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $studentId = filter_input(INPUT_POST, "studentId", FILTER_VALIDATE_INT);

            if ($studentId && $backend->addStudentToCourse((int)$courseId, (int)$studentId)) {
                $success = "Estudiante agregado correctamente.";
            } else {
                $error = "No se pudo agregar el estudiante al curso.";
            }
        }

        $students = $backend->getStudentsNotInCourse((int)$courseId, $search);
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar estudiantes | SIED</title>
    <link href="/assets/img/favicon.ico" rel="icon" type="image">
    <link href="/assets/styles/style.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/b539121292.js" crossorigin="anonymous"></script>
</head>

<body>
<div class="app-layout">

    <?php require_once __DIR__ . '/menu.php'; ?>

    <main class="app-main">

        <section class="topbar course-topbar">
            <div>
                <h1>Agregar estudiantes</h1>

                <?php if ($selectedCourse) { ?>
                    <p>
                        Curso: <?php echo htmlspecialchars($selectedCourse["nombre"]); ?> |
                        Código: <?php echo htmlspecialchars($selectedCourse["codigo"]); ?> |
                        Grupo: <?php echo htmlspecialchars($selectedCourse["grupo"]); ?>
                    </p>
                <?php } ?>
            </div>
        </section>

        <section class="dashboard-card">

            <?php if ($error !== "") { ?>
                <div class="message error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <?php if ($success !== "") { ?>
                <div class="message success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php } ?>

            <?php if ($selectedCourse) { ?>

                <form method="GET" action="/courses/<?php echo urlencode($courseId); ?>/students/add" class="form-group">
                    <label for="search">Buscar estudiante</label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        placeholder="Buscar por nombre, apellido o correo"
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                    <button type="submit" style="margin-top: 12px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Buscar
                    </button>
                </form>

                <?php if (empty($students)) { ?>

                    <div class="message error">
                        No hay estudiantes disponibles para agregar.
                    </div>

                <?php } else { ?>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Apellido</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($students as $student) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student["apellido1"]); ?></td>
                                        <td><?php echo htmlspecialchars($student["nombre"]); ?></td>
                                        <td><?php echo htmlspecialchars($student["correo"]); ?></td>
                                        <td>
                                            <form method="POST" action="/courses/<?php echo urlencode($courseId); ?>/students/add">
                                                <input 
                                                    type="hidden" 
                                                    name="studentId" 
                                                    value="<?php echo htmlspecialchars($student["ID"]); ?>"
                                                >

                                                <button type="submit">
                                                    <i class="fa-solid fa-user-plus"></i>
                                                    Agregar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                <?php } ?>

            <?php } ?>

        </section>

    </main>
</div>
</body>
</html>