<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: /");
    exit;
}

require_once __DIR__ . '/class/BackendFacade.php';

$backend = new BackendFacade();
$teacherId = $_SESSION["ID"] ?? null;

$statistics = [
    "total_courses" => 0,
    "total_evaluations" => 0,
    "total_submissions" => 0
];

if ($teacherId !== null) {
    $statistics = $backend->getTeacherStatistics((int) $teacherId);
}

$usuario = $_SESSION["usuario"];
$nombre = $backend->getTeacherName($usuario);
?>
<html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/assets/img/favicon.ico" rel="icon" type="image">
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
                    <h3><?php echo htmlspecialchars($statistics["total_courses"]); ?></h3>
                    <p>Cursos</p>
                </div>
            </div>
            <div class="summary-card">
                <span class="summary-icon"><i class="fa-regular fa-file-lines" style="color: var(--icon-color);"></i></span>
                <div>
                    <h3><?php echo htmlspecialchars($statistics["total_evaluations"]); ?></h3>
                    <p>Evaluaciones</p>
                </div>
            </div>
            
        </section>
        <a href="/download/Release.zip" download="" target="_blank"> <button> <i class="fa-solid fa-download" style="color: white;"></i>  Descargar IDE </button> </a> 

    </main>
</div>

</body>

</html>