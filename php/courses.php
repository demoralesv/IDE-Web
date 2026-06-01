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

$selectedCourse = null;
$students = [];
$statistics = [
    "total_students" => 0,
    "total_tasks" => 0,
    "total_submissions" => 0
];
$tasks = [];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete_course") {
    if ($teacherId === null) {
        $error = "No se pudo identificar al profesor de la sesión actual.";
    } elseif ($courseId === false || $courseId === null) {
        $error = "No se pudo identificar el curso a eliminar.";
    } else {
        if ($backend->deleteCourse((int) $courseId, (int) $teacherId)) {
            header("Location: /panel");
            exit;
        } else {
            $error = "No se pudo eliminar el curso. Verifique que no tenga datos asociados.";
        }
    }
}

if ($teacherId === null) {
    $error = "No se pudo identificar al profesor de la sesión actual.";
} elseif ($courseId === false || $courseId === null) {
    $error = "No se ha seleccionado ningún curso.";
} else {
    $selectedCourse = $backend->getCourseByIdAndTeacher($courseId, $teacherId);

    

    if (!$selectedCourse) {
        $error = "El curso seleccionado no existe o no pertenece a este profesor.";
    } else {
        $statistics = $backend->getCourseStatistics($courseId);
        $students = $backend->getStudentsByCourse($courseId);
        $tasks = $backend->getAssignmentsByCourse($courseId);

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

            <section class="topbar course-topbar">
                <div>
                    <h1><?php echo htmlspecialchars($selectedCourse["nombre"]); ?></h1>
                    <p>
                        Código: <?php echo htmlspecialchars($selectedCourse["codigo"]); ?> |
                        Grupo: <?php echo htmlspecialchars($selectedCourse["grupo"]); ?>
                    </p>
                </div>

                <button type="button" class="delete-course-button" onclick="openDeleteCourseModal()">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </section>

            <section class="dashboard-summary">

                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h3><?php echo htmlspecialchars($statistics["total_students"]); ?></h3>
                        <p>Estudiantes</p>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <h3><?php echo htmlspecialchars($statistics["total_tasks"]); ?></h3>
                        <p>Evaluaciones</p>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <div>
                        <h3><?php echo htmlspecialchars($statistics["total_submissions"]); ?></h3>
                        <p>Entregas</p>
                    </div>
                </div>

            </section>

            <section class="course-content-grid">

                <section class="dashboard-card course-students-card">
                    <div class="card-header-row">
                        <div>
                            <h2>Estudiantes</h2>
                            <p>Estudiantes matriculados en este curso.</p>
                        </div>

                        <a 
                            href="/courses/<?php echo urlencode($selectedCourse["ID"]); ?>/students/add"
                            class="circle-action-button"
                            title="Agregar estudiantes"
                        >
                            <i class="fa-solid fa-plus"></i>
                        </a>
                    </div>

                    <input 
                        type="text" 
                        id="studentSearch" 
                        class="list-search-input"
                        placeholder="Buscar estudiante..."
                        onkeyup="filterList('studentSearch', 'studentsList')"
                    >

                    <?php if (empty($students)) { ?>

                        <div class="message error">
                            Este curso todavía no tiene estudiantes registrados.
                        </div>

                    <?php } else { ?>

                        <div id="studentsList" class="compact-list paginated-list" data-page-size="5">
                            <?php foreach ($students as $student) { ?>
                                <div class="compact-list-item">
                                    <strong>
                                        <?php echo htmlspecialchars($student["apellido1"] . ", " . $student["nombre"]); ?>
                                    </strong>
                                    <span><?php echo htmlspecialchars($student["correo"]); ?></span>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="pagination-controls" data-list="studentsList"></div>

                    <?php } ?>
                </section>

                <section class="dashboard-card course-tasks-card">
                    <div class="card-header-row">
                        <div>
                            <h2>Tareas del curso</h2>
                            <p>Listado de tareas creadas para este curso.</p>
                        </div>

                        <a 
                            href="/courses/<?php echo urlencode($selectedCourse["ID"]); ?>/assignments/create"
                            class="circle-action-button"
                            title="Crear tarea"
                        >
                            <i class="fa-solid fa-plus"></i>
                        </a>
                    </div>

                    <input 
                        type="text" 
                        id="taskSearch" 
                        class="list-search-input"
                        placeholder="Buscar tarea..."
                        onkeyup="filterList('taskSearch', 'tasksList')"
                    >

                    <?php if (empty($tasks)) { ?>

                        <div class="message error">
                            Este curso todavía no tiene tareas registradas.
                        </div>

                    <?php } else { ?>

                        <div id="tasksList" class="task-list paginated-list" data-page-size="5">
                            <?php foreach ($tasks as $task) { ?>
                                <a 
                                    href="/courses/<?php echo urlencode($selectedCourse["ID"]); ?>/assignments/<?php echo urlencode($task["ID"]); ?>"
                                    class="task-list-item"
                                >
                                    <div>
                                        <strong><?php echo htmlspecialchars($task["titulo"]); ?></strong>
                                        <span>
                                            Fecha de entrega:
                                            <?php echo htmlspecialchars($task["fechaentrega"]); ?>
                                        </span>
                                    </div>

                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            <?php } ?>
                        </div>

                        <div class="pagination-controls" data-list="tasksList"></div>

                    <?php } ?>
                </section>

            </section>

        <?php } ?>

    </main>

</div>

<div id="deleteCourseModal" class="modal-overlay">
    <div class="modal danger">
        <div class="modal-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <h2>Eliminar curso</h2>

        <p>
            ¿Está seguro de que desea eliminar el curso
            <strong><?php echo htmlspecialchars($selectedCourse["nombre"] ?? ""); ?></strong>?
        </p>

        <p class="modal-highlight">
            Esta acción no se puede deshacer.
        </p>

        <form 
            method="POST" 
            action="/courses/<?php echo urlencode($selectedCourse["ID"] ?? ""); ?>/delete"
            class="modal-actions"
        >
            <input type="hidden" name="action" value="delete_course">

            <button 
                type="button" 
                class="modal-cancel-button" 
                onclick="closeDeleteCourseModal()"
            >
                Cancelar
            </button>

            <button type="submit" class="modal-delete-button">
                Sí, eliminar
            </button>
        </form>
    </div>
</div>

<script>
function openDeleteCourseModal() {
    document.getElementById("deleteCourseModal").classList.add("show");
}

function closeDeleteCourseModal() {
    document.getElementById("deleteCourseModal").classList.remove("show");
}

document.getElementById("deleteCourseModal").addEventListener("click", function(event) {
    if (event.target === this) {
        closeDeleteCourseModal();
    }
});

function filterList(inputId, listId) {
    const inputElement = document.getElementById(inputId);
    const list = document.getElementById(listId);

    if (!inputElement || !list) return;

    const input = inputElement.value.toLowerCase();
    const items = list.querySelectorAll(".compact-list-item, .task-list-item");

    items.forEach(item => {
        item.dataset.visible = item.textContent.toLowerCase().includes(input) ? "true" : "false";
    });

    setupPagination(listId, 1);
}

function setupPagination(listId, page = 1) {
    const list = document.getElementById(listId);
    if (!list) return;

    const pageSize = parseInt(list.dataset.pageSize || "5");
    const allItems = Array.from(list.querySelectorAll(".compact-list-item, .task-list-item"));
    const visibleItems = allItems.filter(item => item.dataset.visible !== "false");

    const totalPages = Math.max(1, Math.ceil(visibleItems.length / pageSize));

    allItems.forEach(item => {
        item.style.display = "none";
    });

    visibleItems.forEach((item, index) => {
        if (index >= (page - 1) * pageSize && index < page * pageSize) {
            item.style.display = "";
        }
    });

    const controls = document.querySelector(`.pagination-controls[data-list="${listId}"]`);
    if (!controls) return;

    controls.innerHTML = "";

    if (totalPages <= 1) return;

    const prev = document.createElement("button");
    prev.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
    prev.disabled = page === 1;
    prev.onclick = function() {
        setupPagination(listId, page - 1);
    };

    const label = document.createElement("span");
    label.textContent = `${page} / ${totalPages}`;

    const next = document.createElement("button");
    next.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
    next.disabled = page === totalPages;
    next.onclick = function() {
        setupPagination(listId, page + 1);
    };

    controls.appendChild(prev);
    controls.appendChild(label);
    controls.appendChild(next);
}

document.addEventListener("DOMContentLoaded", function() {
    setupPagination("studentsList", 1);
    setupPagination("tasksList", 1);
});
</script>

</body>
</html>