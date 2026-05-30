<?php
$teacherId = $_SESSION["ID"] ?? null;

$courses = [];

if (!isset($backend)) {
    require_once __DIR__ . '/class/BackendFacade.php';
    $backend = new BackendFacade();
}

if ($teacherId !== null) {
    $courses = $backend->getCoursesByTeacher($teacherId);
}

function isActiveRoute($route) {
    $currentPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    return $currentPath === $route ? "active" : "";
}

function isCourseActive($courseId) {
    $currentPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    return $currentPath === "/courses/" . $courseId ? "active" : "";
}
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">SIED</div>
        <p>Bienvenid@, <?php echo htmlspecialchars($nombre ?? "Docente"); ?></p>
    </div>

    <nav class="sidebar-menu">

        <a href="/panel" class="menu-item <?php echo isActiveRoute('/panel'); ?>">
            <span class="menu-icon">
                <i class="fa-solid fa-house" style="color: rgb(255, 255, 255);"></i>
            </span>
            <span>Inicio</span>
        </a>

        <a href="/addCourse" class="menu-item <?php echo isActiveRoute('/addCourse'); ?>">
            <span class="menu-icon">
                <i class="fa-solid fa-plus" style="color: rgb(255, 255, 255);"></i>
            </span>
            <span>Crear curso</span>
        </a>

        <?php foreach ($courses as $menuCourse) { ?>
            <a 
                href="/courses/<?php echo urlencode($menuCourse["ID"]); ?>" 
                class="menu-item <?php echo isCourseActive($menuCourse["ID"]); ?>"
            >
                <span class="menu-icon">
                    <i class="fa-solid fa-chalkboard-user" style="color: rgb(255, 255, 255);"></i>
                </span>

                <span><?php echo htmlspecialchars($menuCourse["nombre"]); ?></span>

                <div class="menu-group-text">
                    <span><?php echo htmlspecialchars("Gr: " . $menuCourse["grupo"]); ?></span>
                </div>
            </a>
        <?php } ?>

    </nav>

    <div class="sidebar-footer">
        <a href="/logout" class="menu-item logout">
            <span class="menu-icon">
                <i class="fa-solid fa-arrow-right-from-bracket" style="color: rgb(255, 255, 255);"></i>
            </span>
            <span>Cerrar sesión</span>
        </a>
    </div>
</aside>