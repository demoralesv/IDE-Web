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

$courseId = isset($courseID) ? filter_var($courseID, FILTER_VALIDATE_INT) : null;
$evaluationId = isset($evaluationID) ? filter_var($evaluationID, FILTER_VALIDATE_INT) : null;

$error = "";
$success = "";

$assignment = null;
$groups = [];
$availableStudents = [];

if ($teacherId === null) {
    $error = "No se pudo identificar al profesor.";
} elseif ($courseId === false || $courseId === null || $evaluationId === false || $evaluationId === null) {
    $error = "No se pudo identificar el curso o la evaluación.";
} else {
    $assignment = $backend->getAssignmentByCourseAndTeacher($evaluationId, $courseId, $teacherId);

    if (!$assignment) {
        $error = "La evaluación no existe o no pertenece a este profesor.";
    }
}

if ($assignment && $_SERVER["REQUEST_METHOD"] === "POST") {
    $studentIds = $_POST["students"] ?? [];

    if (empty($studentIds)) {
        $error = "Debe seleccionar al menos un estudiante para crear el grupo.";
    } else {
        $newGroupId = $backend->createAssignmentGroup($evaluationId, $studentIds);

        if ($newGroupId !== false) {
            $success = "Grupo creado correctamente.";
        } else {
            $error = "No se pudo crear el grupo. Verifique que los estudiantes no estén ya en otro grupo.";
        }
    }
}

if ($assignment) {
    $groups = $backend->getGroupsByAssignment($evaluationId);
    $availableStudents = $backend->getAvailableStudentsForAssignment($courseId, $evaluationId);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Grupos de tarea | SIED</title>

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
                <h1>Grupos de evaluación</h1>

                <?php if ($assignment) { ?>
                    <p>
                        Evaluación: <?php echo htmlspecialchars($assignment["titulo"]); ?> |
                        Curso: <?php echo htmlspecialchars($assignment["cursoNombre"]); ?> |
                        Grupo: <?php echo htmlspecialchars($assignment["cursoGrupo"]); ?>
                    </p>
                <?php } ?>
            </div>
        </section>

        <section class="dashboard-card">
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

            <?php if ($assignment) { ?>

                <h2>Crear nuevo grupo</h2>
                <p>Seleccione los estudiantes que formarán parte del nuevo grupo.</p>

                <?php if (empty($availableStudents)) { ?>

                    <div class="message error">
                        No hay estudiantes disponibles para agregar a un nuevo grupo.
                    </div>

                <?php } else { ?>

                    <form 
                        method="POST" 
                        action="/courses/<?php echo urlencode($courseId); ?>/assignments/<?php echo urlencode($evaluationId); ?>/groups"
                    >

                        <div class="info-list">
                            <?php foreach ($availableStudents as $student) { ?>
                                <label class="student-checkbox-item">
                                    <input 
                                        type="checkbox" 
                                        name="students[]" 
                                        value="<?php echo htmlspecialchars($student["ID"]); ?>"
                                    >

                                    <span>
                                        <?php 
                                            echo htmlspecialchars(
                                                $student["nombre"] . " " . $student["apellido1"] . " - " . $student["correo"]
                                            ); 
                                        ?>
                                    </span>
                                </label>
                            <?php } ?>
                        </div>

                        <button type="submit">
                            <i class="fa-solid fa-users"></i>
                            Crear grupo
                        </button>

                    </form>

                <?php } ?>

            <?php } ?>
        </section>

        <?php if ($assignment) { ?>
            <section class="dashboard-card" style="margin-top: 24px;">
                <h2>Grupos existentes</h2>
                <p>Estos son los grupos creados para esta evaluación.</p>

                <?php if (empty($groups)) { ?>

                    <div class="message error">
                        Todavía no hay grupos creados para esta evaluación.
                    </div>

                <?php } else { ?>

                    <div class="courses-grid">
                        <?php foreach ($groups as $group) { ?>
                            <div class="course-card">
                                <div class="course-info">
                                    <h3>Grupo <?php echo htmlspecialchars($group["numero"]); ?></h3>

                                    <div class="info-list">
                                        <?php foreach ($group["estudiantes"] as $student) { ?>
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
                                </div>
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