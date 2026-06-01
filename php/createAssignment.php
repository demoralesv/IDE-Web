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

$error = "";
$success = "";
$selectedCourse = null;
$titleValue = "";
$descriptionValue = "";
$filePathValue = "";
$dueDateValue = "";

if ($teacherId === null) {
    $error = "No se pudo identificar al profesor.";
} elseif ($courseId === false || $courseId === null) {
    $error = "No se pudo identificar el curso.";
} else {
    $selectedCourse = $backend->getCourseByIdAndTeacher($courseId, $teacherId);

    if (!$selectedCourse) {
        $error = "El curso no existe o no pertenece a este profesor.";
    }
}
if ($selectedCourse && $_SERVER["REQUEST_METHOD"] === "POST") {
    $titleValue = trim($_POST["title"] ?? "");
    $descriptionValue = trim($_POST["description"] ?? "");
    $filePathValue = trim($_POST["filePath"] ?? "");
    $dueDateValue = trim($_POST["dueDate"] ?? "");

    $title = $titleValue;
    $description = $descriptionValue;
    $filePath = $filePathValue;
    $dueDate = $dueDateValue;

    if ($title === "" || $description === "" || $dueDate === "") {
        $error = "Favor ingresar título, descripción y fecha de entrega.";
    } elseif ($dueDate < date("Y-m-d")) {
        $error = "La fecha de entrega no puede ser anterior a hoy.";
    } else {
        if ($backend->createAssignment($courseId, $title, $description, $filePath, $dueDate)) {
            header("Location: /courses/" . urlencode($courseId));
            exit;
        } else {
            $error = "Ocurrió un error al crear la tarea.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear tarea | SIED</title>

    <link href="/assets/img/favicon.ico" rel="icon" type="image">
    <link href="/assets/styles/style.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
    <script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>

    <script src="https://kit.fontawesome.com/b539121292.js" crossorigin="anonymous"></script>
</head>

<body>
<div class="app-layout">

    <?php require_once __DIR__ . '/menu.php'; ?>

    <main class="app-main">

        <section class="topbar course-topbar">
            <div>
                <h1>Crear tarea</h1>

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

            <?php if ($selectedCourse) { ?>

                <form method="POST" action="/courses/<?php echo urlencode($courseId); ?>/assignments/create">

                    <div class="form-group">
                        <label for="title">Título de la tarea</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            placeholder="Ej: Investigación"
                            value="<?php echo htmlspecialchars($titleValue); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea 
                            id="description" 
                            name="description"
                            placeholder="# Instrucciones de la tarea"
                        ><?php echo htmlspecialchars($descriptionValue); ?></textarea></textarea>
                    </div>

                    <div class="form-group">
                        <label for="filePath">Dirección del archivo adjunto</label>
                        <input 
                            type="text" 
                            id="filePath" 
                            name="filePath" 
                            placeholder="Ej: /download/tarea1.pdf o https://..."
                            value="<?php echo htmlspecialchars($filePathValue); ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="localFile">Seleccionar archivo</label>
                        <input 
                            type="file" 
                            id="localFile"
                            onchange="setFilePath(this)"
                        >
                    </div>
                    <div class="form-group">
                        <label for="dueDate">Fecha de entrega</label>
                        <input 
                            type="date" 
                            id="dueDate" 
                            name="dueDate"
                            min="<?php echo date('Y-m-d'); ?>"
                            value="<?php echo htmlspecialchars($dueDateValue); ?>"
                            required
                        >
                    </div>
                    <button type="submit">
                        <i class="fa-solid fa-plus"></i>
                        Crear tarea
                    </button>

                </form>

            <?php } ?>

        </section>

    </main>
</div>

<script>
    function setFilePath(input) {
        if (input.files.length > 0) {
            document.getElementById("filePath").value = input.files[0].name;
        }
    }
    const easyMDE = new EasyMDE({
        element: document.getElementById("description"),
        spellChecker: false,
        status: false,
        minHeight: "260px",
        maxHeight: "260px",
        toolbar: [
            "bold",
            "italic",
            "heading",
            "quote",
            "unordered-list",
            "ordered-list",
            "link",
            "preview",
            "side-by-side",
            "fullscreen"
        ]
    });
</script>

</body>
</html>