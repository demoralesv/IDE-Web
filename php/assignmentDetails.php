<?php
session_start();

require_once __DIR__ . '/class/BackendFacade.php';
require_once __DIR__ . '/class/Parsedown.php';

$backend = new BackendFacade();
$parsedown = new Parsedown();

if (!isset($_SESSION["usuario"])) {
    header("Location: /");
    exit;
}

$usuario = $_SESSION["usuario"];
$nombre = $backend->getTeacherName($usuario);
$teacherId = $_SESSION["ID"] ?? null;

$courseId = filter_var($ID ?? null, FILTER_VALIDATE_INT);
$assignmentId = filter_var($assignmentID ?? null, FILTER_VALIDATE_INT);

$error = "";
$selectedCourse = null;
$assignment = null;
$submissions = [];

if ($teacherId === null) {
    $error = "No se pudo identificar al profesor.";
} elseif ($courseId === false || $courseId === null) {
    $error = "No se pudo identificar el curso.";
} elseif ($assignmentId === false || $assignmentId === null) {
    $error = "No se pudo identificar la tarea.";
} else {
    $selectedCourse = $backend->getCourseByIdAndTeacher((int)$courseId, (int)$teacherId);

    if (!$selectedCourse) {
        $error = "El curso no existe o no pertenece a este profesor.";
    } else {
        $assignment = $backend->getAssignmentByIdAndCourse((int)$assignmentId, (int)$courseId);

        if (!$assignment) {
            $error = "La tarea no existe o no pertenece a este curso.";
        } else {
            $submissions = $backend->getSubmissionsByAssignment((int)$assignmentId);

            $submissionsByGroup = [];
            $latestSubmissions = [];

            if (!empty($submissions)) {
                foreach ($submissions as $submission) {
                    $groupKey = (string)($submission["grupoNumero"] ?? "sin-grupo");

                    if (!isset($submissionsByGroup[$groupKey])) {
                        $submissionsByGroup[$groupKey] = [];
                    }

                    $submissionsByGroup[$groupKey][] = $submission;
                }

                foreach ($submissionsByGroup as $groupKey => &$groupSubmissions) {
                    usort($groupSubmissions, function ($a, $b) {
                        return strtotime($b["fechaentrega"] ?? "") <=> strtotime($a["fechaentrega"] ?? "");
                    });

                    $latestSubmissions[$groupKey] = $groupSubmissions[0];
                }

                unset($groupSubmissions);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de tarea | SIED</title>

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
                <h1>Tarea no disponible</h1>
                <p>No se pudo cargar la información de la tarea solicitada.</p>
            </section>

            <section class="dashboard-card">
                <div class="message error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </section>

        <?php } else { ?>

            <section class="topbar course-topbar">
                <div>
                    <h1><?php echo htmlspecialchars($assignment["titulo"]); ?></h1>
                    <p>
                        Curso: <?php echo htmlspecialchars($selectedCourse["nombre"]); ?> |
                        Fecha de entrega: <?php echo htmlspecialchars($assignment["fechaentrega"]); ?>
                    </p>
                </div>

                <a 
                    href="/courses/<?php echo urlencode($selectedCourse["ID"]); ?>/assignments/<?php echo urlencode($assignment["ID"]); ?>/groups" 
                    class="quick-action"
                >
                    <i class="fa-solid fa-users"></i>
                    Administrar grupos
                </a>

                <a 
                    href="/courses/<?php echo urlencode($courseId); ?>/assignments/<?php echo urlencode($assignmentId); ?>/edit"
                    class="circle-action-button"
                    title="Editar tarea"
                >
                    <i class="fa-solid fa-pencil"></i>
                </a>
            </section>

            <section class="assignment-layout">

                <section class="dashboard-card assignment-detail-card">
                    <h2>Información de la tarea</h2>

                    <div class="assignment-field">
                        <span>Título</span>
                        <strong><?php echo htmlspecialchars($assignment["titulo"]); ?></strong>
                    </div>

                    <div class="assignment-field">
                        <span>Fecha de entrega</span>
                        <strong><?php echo htmlspecialchars($assignment["fechaentrega"]); ?></strong>
                    </div>

                    <div class="assignment-field">
                        <span>Archivo adjunto</span>

                        <?php if (!empty($assignment["adjunto"])) { ?>
                            <a 
                                href="<?php echo htmlspecialchars($assignment["adjunto"]); ?>" 
                                target="_blank"
                                class="attachment-link"
                            >
                                <i class="fa-solid fa-paperclip"></i>
                                <?php echo htmlspecialchars($assignment["adjunto"]); ?>
                            </a>
                        <?php } else { ?>
                            <p>No hay archivo adjunto.</p>
                        <?php } ?>
                    </div>

                    <div class="assignment-field">
                        <span>Descripción</span>

                        <div class="assignment-description markdown-content">
                            <?php echo $parsedown->text($assignment["descripcion"]); ?>
                        </div>
                    </div>
                </section>

                <section class="dashboard-card submissions-card">
                    <h2>Entregas</h2>
                    <p>Última entrega registrada por cada grupo.</p>

                    <?php if (empty($submissions)) { ?>

                        <div class="message error">
                            Esta tarea todavía no tiene entregas registradas.
                        </div>

                    <?php } else { ?>

                        <div class="task-list">
                            <?php foreach ($latestSubmissions as $groupKey => $submission) { 
                                $safeGroupId = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$groupKey);
                                $groupSubmissionCount = count($submissionsByGroup[$groupKey]);
                            ?>
                                <div class="task-list-item">
                                    <div>
                                        <strong>
                                            Entrega #<?php echo htmlspecialchars($submission["numero"]); ?>
                                        </strong>

                                        <span>
                                            Grupo #<?php echo htmlspecialchars($submission["grupoNumero"]); ?>
                                        </span>

                                        <span>
                                            Fecha:
                                            <?php echo htmlspecialchars($submission["fechaentrega"]); ?>
                                        </span>
                                    </div>

                                    <div class="submission-actions">
                                        <?php if ($groupSubmissionCount > 1) { ?>
                                            <button
                                                type="button"
                                                class="history-button"
                                                title="Ver historial de entregas"
                                                onclick="openModal('historyModal-<?php echo htmlspecialchars($safeGroupId, ENT_QUOTES); ?>')"
                                            >
                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                            </button>
                                        <?php } ?>

                                        <?php if (!empty($submission["proyecto"])) { ?>
                                            <a 
                                                href="/submissions/<?php echo urlencode($submission["ID"]); ?>/download" 
                                                class="attachment-link"
                                            >
                                                <i class="fa-solid fa-up-right-from-square"></i>
                                                Ver entrega
                                            </a>
                                        <?php } else { ?>
                                            <span>Sin archivo</span>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <?php foreach ($submissionsByGroup as $groupKey => $groupSubmissions) { 
                            if (count($groupSubmissions) <= 1) {
                                continue;
                            }

                            $safeGroupId = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$groupKey);
                        ?>
                            <div id="historyModal-<?php echo htmlspecialchars($safeGroupId); ?>" class="modal-overlay">
                                <div class="modal submission-history-modal">
                                    <div class="modal-header">
                                        <div>
                                            <h2>Historial de entregas</h2>
                                            <p>Grupo #<?php echo htmlspecialchars($groupKey); ?></p>
                                        </div>

                                        <button 
                                            type="button" 
                                            class="modal-close-button"
                                            onclick="closeModal('historyModal-<?php echo htmlspecialchars($safeGroupId, ENT_QUOTES); ?>')"
                                        >
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>

                                    <div class="task-list">
                                        <?php foreach ($groupSubmissions as $submission) { ?>
                                            <div class="task-list-item">
                                                <div>
                                                    <strong>
                                                        Entrega #<?php echo htmlspecialchars($submission["numero"]); ?>
                                                    </strong>

                                                    <span>
                                                        Grupo #<?php echo htmlspecialchars($submission["grupoNumero"]); ?>
                                                    </span>

                                                    <span>
                                                        Fecha:
                                                        <?php echo htmlspecialchars($submission["fechaentrega"]); ?>
                                                    </span>
                                                </div>

                                                <?php if (!empty($submission["proyecto"])) { ?>
                                                    <a 
                                                        href="/submissions/<?php echo urlencode($submission["ID"]); ?>/download" 
                                                        class="attachment-link"
                                                    >
                                                        <i class="fa-solid fa-up-right-from-square"></i>
                                                        Ver entrega
                                                    </a>
                                                <?php } else { ?>
                                                    <span>Sin archivo</span>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                    <?php } ?>
                </section>

            </section>

        <?php } ?>

    </main>
</div>
<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.add("show");
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove("show");
    }

    document.querySelectorAll(".modal-overlay").forEach(function(modal) {
        modal.addEventListener("click", function(event) {
            if (event.target === this) {
                this.classList.remove("show");
            }
        });
    });

    document.addEventListener("keydown", function(event) {
        if (event.key === "Escape") {
            document.querySelectorAll(".modal-overlay.show").forEach(function(modal) {
                modal.classList.remove("show");
            });
        }
    });
</script>
</body>
</html>