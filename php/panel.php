<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/class/class.php';

function countTableRows($tableName) {
    global $conn;

    $allowedTables = ["curso", "usuario", "evaluacion", "entrega"];

    if (!in_array($tableName, $allowedTables)) {
        return 0;
    }

    try {
        $stmt = $conn->query("SELECT COUNT(*) AS total FROM $tableName");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result["total"] : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

$cursosCount = countTableRows("curso");
$estudiantesCount = countTableRows("usuario");
$evaluacionesCount = countTableRows("evaluacion");
$entregasCount = countTableRows("entrega");

$usuario = $_SESSION["usuario"];
$nombre = getTeacherName($usuario);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/img/favicon.ico" rel="icon" type="image">
    <title>Dashboard | SIED</title>
    <link href="assets/styles/style.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/b539121292.js" crossorigin="anonymous"></script>
</head>
<body>

<div class="app-layout">
    <?php require_once __DIR__ . '/menu.php'; ?>

    <main class="app-main">
        <header class="topbar">
            <div>
                <h1>Dashboard</h1>
                
            </div>
        </header>

        <section class="dashboard-summary">
            <div class="summary-card">
                <span class="summary-icon"><i class="fa-solid fa-chalkboard-user" style="color: var(--icon-color);"></i></span>
                <div>
                    <h3><?php echo $cursosCount; ?></h3>
                    <p>Cursos registrados</p>
                </div>
            </div>

            <div class="summary-card">
                <span class="summary-icon"><i class="fa-solid fa-graduation-cap" style="color: var(--icon-color);"></i></span>
                <div>
                    <h3><?php echo $estudiantesCount; ?></h3>
                    <p>Estudiantes registrados</p>
                </div>
            </div>

            
        </section>

        <section class="dashboard-grid">
            <div class="dashboard-card large-card">
                <h2>Principales funcionalidades</h2>
             

                <div class="quick-actions">
                    <a href="crearCurso.php" class="quick-action">
                        <span><i class="fa-solid fa-plus" style="color: var(--icon-color);"></i></span>
                        Crear nuevo curso
                    </a>

                    <a href="cursos.php" class="quick-action">
                        <span><i class="fa-solid fa-book" style="color: var(--icon-color);"></i></span>
                        Ver mis cursos
                    </a>

                    <a href="estudiantes.php" class="quick-action">
                        <span><i class="fa-solid fa-users" style="color: var(--icon-color);"></i></span>
                        Gestionar estudiantes
                    </a>

                    <a href="evaluaciones.php" class="quick-action">
                        <span><i class="fa-solid fa-file-alt" style="color: var(--icon-color);"></i></span>
                        Crear evaluación
                    </a>

                    <a href="entregas.php" class="quick-action">
                        <span><i class="fa-solid fa-file-arrow-up" style="color: var(--icon-color);"></i></span>
                        Revisar entregas
                    </a>
                </div>
            </div>

            <!-- <div class="dashboard-card">
                <h2>Resumen del sistema</h2>

                <div class="info-list">
                    <div class="info-item">
                        <strong>Cursos</strong>
                        <span>Creación y administración de cursos.</span>
                    </div>

                    <div class="info-item">
                        <strong>Estudiantes</strong>
                        <span>Asignación de estudiantes a cursos.</span>
                    </div>

                    <div class="info-item">
                        <strong>Evaluaciones</strong>
                        <span>Creación de tareas, proyectos o pruebas.</span>
                    </div>

                    <div class="info-item">
                        <strong>Entregas</strong>
                        <span>Consulta de entregas realizadas por estudiantes.</span>
                    </div>
                </div>
            </div> -->
        </section>
    </main>
</div>

</body>

</html>