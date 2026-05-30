<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/class/BackendFacade.php';

$backend = new BackendFacade();

$usuario = $_SESSION["usuario"];
$nombre = $backend->getTeacherName($usuario);
$teacherId = $_SESSION["ID"] ?? null;

$courseId = filter_var($ID, FILTER_VALIDATE_INT);

$selectedcourse = null;
$students = [];
$tasks = [];
$error = "";

if ($teacherId === null) {
    $error = "No se pudo identificar al profesor de la sesión actual.";
} elseif ($courseId === false || $courseId === null) {
    $error = "No se ha seleccionado ningún curso.";
} else {
    $selectedcourse = $backend->getCourseByIdAndTeacher($courseId, $teacherId);

    if (!$selectedcourse) {
        $error = "El curso seleccionado no existe o no pertenece a este profesor.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos | SIED</title>
    <link href="/assets/img/favicon.ico" rel="icon" type="image">
    <link href="/assets/styles/style.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/b539121292.js" crossorigin="anonymous"></script>
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . '/menu.php'; ?>

    <main class="app-main">

        <?php if ($error !== "") { ?>

            <section class="topbar">
                <h1>Curso no disponible</h1>
                <p>No se pudo cargar la información del curso solicitado.</p>
            </section>

            <section class="dashboard-card">
                <div class="message error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </section>

        <?php } else { ?>

            <section class="topbar">
                <h1><?php echo htmlspecialchars($selectedcourse["nombre"]); ?></h1>
                <p>
                    Código: <?php echo htmlspecialchars($selectedcourse["codigo"]); ?> |
                    Grupo: <?php echo htmlspecialchars($selectedcourse["grupo"]); ?>
                </p>
            </section>

            <section class="dashboard-summary">

                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h3><?php echo count($students); ?></h3>
                        <p>Estudiantes</p>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <h3><?php echo count($tasks); ?></h3>
                        <p>Tareas</p>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <h3>0</h3>
                        <p>Evaluaciones</p>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <div>
                        <h3>0</h3>
                        <p>Entregas</p>
                    </div>
                </div>

            </section>

            <section class="dashboard-grid">

                <div class="dashboard-card">
                    <h2>Administrar curso</h2>
                    <p>Seleccione una acción para gestionar este curso.</p>

                    <div class="quick-actions">

                        <a 
                            href="agregarEstudiantes.php?cursoId=<?php echo urlencode($selectedcourse["ID"]); ?>" 
                            class="quick-action"
                        >
                            <i class="fa-solid fa-user-plus"></i>
                            Agregar estudiantes
                        </a>

                        <a 
                            href="crearTarea.php?cursoId=<?php echo urlencode($selectedcourse["ID"]); ?>" 
                            class="quick-action"
                        >
                            <i class="fa-solid fa-plus"></i>
                            Crear tarea
                        </a>

                        <a 
                            href="assignments.php?cursoId=<?php echo urlencode($selectedcourse["ID"]); ?>" 
                            class="quick-action"
                        >
                            <i class="fa-solid fa-clipboard-list"></i>
                            Evaluaciones
                        </a>

                        <a 
                            href="turnIn.php?cursoId=<?php echo urlencode($selectedcourse["ID"]); ?>" 
                            class="quick-action"
                        >
                            <i class="fa-solid fa-folder-open"></i>
                            Ver entregas
                        </a>

                    </div>
                </div>

                <div class="dashboard-card">
                    <h2>Estudiantes</h2>
                    <p>Estudiantes asociados a este curso.</p>

                    <?php if (empty($students)) { ?>

                        <div class="message error">
                            Este curso todavía no tiene estudiantes registrados.
                        </div>

                    <?php } else { ?>

                        <div class="info-list">
                            <?php foreach ($students as $student) { ?>
                                <div class="info-item">
                                    <strong>
                                        <?php 
                                            echo htmlspecialchars(
                                                $student["nombre"] . " " . $student["apellido1"]
                                            ); 
                                        ?>
                                    </strong>
                                    <span>
                                        <?php echo htmlspecialchars($student["correo"]); ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>

                    <?php } ?>

                </div>

            </section>

            <section class="dashboard-card" style="margin-top: 24px;">
                <h2>Tareas del curso</h2>
                <p>Listado de tareas creadas para este curso.</p>

                <?php if (empty($tasks)) { ?>

                    <div class="message error">
                        Este curso todavía no tiene tareas registradas.
                    </div>

                <?php } else { ?>

                    <div class="info-list">
                        <?php foreach ($tasks as $task) { ?>
                            <div class="info-item">
                                <strong>
                                    <?php echo htmlspecialchars($task["nombre"]); ?>
                                </strong>

                                <span>
                                    Fecha de entrega:
                                    <?php echo htmlspecialchars($task["fechaentrega"]); ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>

                <?php } ?>

            </section>

        <?php } ?>

    </main>

</div>

</body>
</html>