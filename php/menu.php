<?php
$currentPage = basename($_SERVER["PHP_SELF"]);
$teacherId = $_SESSION["ID"] ?? null;

$courses = [];

if ($teacherId !== null) {
    $courses = getCoursesByTeacher($teacherId);
}
function isActive($page, $currentPage) {
    return $page === $currentPage ? "active" : "";
}

function isCourseActive($courseId) {
    $currentPage = basename($_SERVER["PHP_SELF"]);
    $currentCourseId = $_GET["id"] ?? null;

    return $currentPage === "cursos.php" && (int)$currentCourseId === (int)$courseId
        ? "active"
        : "";
}
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">SIED</div>
        <p>Bienvenid@, <?php echo htmlspecialchars($nombre); ?></p>
    </div>

    <nav class="sidebar-menu">
        <a href="panel.php" class="menu-item <?php echo isActive('panel.php', $currentPage); ?>">
            <span class="menu-icon"><i class="fa-solid fa-house" style="color: rgb(255, 255, 255);"></i></span>
            <span>Inicio</span>
        </a>

        <a href="crearCurso.php" class="menu-item <?php echo isActive('crearCurso.php', $currentPage); ?>">
            <span class="menu-icon"><i class="fa-solid fa-plus" style="color: rgb(255, 255, 255);"></i></span>
            <span>Crear curso</span>
        </a>

        <?php foreach ($courses as $menucourses) { ?>
            <a 
                href="cursos.php?id=<?php echo urlencode($menucourses['ID']); ?>" 
                class="menu-item <?php echo isCourseActive($menucourses['ID']); ?>"
            >
                <span class="menu-icon">
                    <i class="fa-solid fa-chalkboard-user" style="color: rgb(255, 255, 255);"></i>
                </span>

                <span><?php echo htmlspecialchars($menucourses['nombre']); ?></span>

                <div class="menu-group-text">
                    <span><?php echo htmlspecialchars("Gr: " . $menucourses['grupo']); ?></span>
                </div>
            </a>
        <?php } ?>
        




        
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="menu-item logout">
            <span class="menu-icon"><i class="fa-solid fa-arrow-right-from-bracket" style="color: rgb(255, 255, 255);"></i></span>
            <span>Cerrar sesión</span>
        </a>
    </div>
</aside>