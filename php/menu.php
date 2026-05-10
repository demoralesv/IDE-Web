<?php
$currentPage = basename($_SERVER["PHP_SELF"]);

function isActive($page, $currentPage) {
    return $page === $currentPage ? "active" : "";
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

        <a href="cursos.php" class="menu-item <?php echo isActive('cursos.php', $currentPage); ?>">
            <span class="menu-icon"><i class="fa-solid fa-book" style="color: rgb(255, 255, 255);"></i></span>
            <span>Mis cursos</span>
        </a>

        <a href="crearCurso.php" class="menu-item <?php echo isActive('crearCurso.php', $currentPage); ?>">
            <span class="menu-icon"><i class="fa-solid fa-plus" style="color: rgb(255, 255, 255);"></i></span>
            <span>Crear curso</span>
        </a>

        <a href="estudiantes.php" class="menu-item <?php echo isActive('estudiantes.php', $currentPage); ?>">
            <span class="menu-icon"><i class="fa-solid fa-users" style="color: rgb(255, 255, 255);"></i></span>
            <span>Estudiantes</span>
        </a>

        <a href="evaluaciones.php" class="menu-item <?php echo isActive('evaluaciones.php', $currentPage); ?>">
            <span class="menu-icon"><i class="fa-solid fa-file-alt" style="color: rgb(255, 255, 255);"></i></span>
            <span>Evaluaciones</span>
        </a>

        <a href="entregas.php" class="menu-item <?php echo isActive('entregas.php', $currentPage); ?>">
            <span class="menu-icon"><i class="fa-solid fa-file-arrow-up" style="color: rgb(255, 255, 255);"></i></span>
            <span>Entregas</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="menu-item logout">
            <span class="menu-icon"><i class="fa-solid fa-arrow-right-from-bracket" style="color: rgb(255, 255, 255);"></i></span>
            <span>Cerrar sesión</span>
        </a>
    </div>
</aside>