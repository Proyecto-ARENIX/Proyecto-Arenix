<?php
$usuario_perfil = usuario_actual();
$roles_perfil = roles_disponibles();
$nombre_rol_perfil = $roles_perfil[$usuario_perfil["rol"] ?? "publico"] ?? "Público";
$palabras_nombre = preg_split('/\s+/u', trim($usuario_perfil["nombre"] ?? ""), -1, PREG_SPLIT_NO_EMPTY);
$iniciales_perfil = "";

foreach (array_slice($palabras_nombre, 0, 2) as $palabra_nombre) {
    if (preg_match('/^\p{L}/u', $palabra_nombre, $coincidencia)) {
        $iniciales_perfil .= $coincidencia[0];
    }
}

if ($iniciales_perfil === "") {
    $iniciales_perfil = "U";
}

// acceso rapido a los clubes del usuario (clientes y participantes)
$clubes_menu_perfil = [];
$ligas_menu_perfil = [];
if ($usuario_perfil && in_array($usuario_perfil["rol"], ["cliente", "participante"], true)) {
    require_once __DIR__ . "/../../models/equipos.php";
    $clubes_menu_perfil = clubes_del_usuario($usuario_perfil);
    $ligas_menu_perfil = ligas_del_usuario($usuario_perfil);
}
?>
<?php if ($usuario_perfil): ?>
    <details class="perfil-menu">
        <summary class="perfil-disparador" aria-label="Abrir menú de perfil">
            <span class="perfil-avatar" aria-hidden="true">
                <?php if (!empty($usuario_perfil["foto"])): ?>
                    <img src="<?php echo htmlspecialchars($usuario_perfil["foto"], ENT_QUOTES, "UTF-8"); ?>" alt="">
                <?php else: ?>
                    <?php echo htmlspecialchars($iniciales_perfil, ENT_QUOTES, "UTF-8"); ?>
                <?php endif; ?>
            </span>
            <span class="perfil-identidad">
                <span class="perfil-nombre"><?php echo htmlspecialchars($usuario_perfil["nombre"], ENT_QUOTES, "UTF-8"); ?></span>
                <span class="perfil-subtitulo"><?php echo htmlspecialchars($nombre_rol_perfil, ENT_QUOTES, "UTF-8"); ?></span>
            </span>
            <span class="perfil-flecha" aria-hidden="true">&#9662;</span>
        </summary>
        <div class="perfil-panel">
            <div class="perfil-panel-cuenta">
                <span class="perfil-avatar perfil-avatar-grande" aria-hidden="true">
                    <?php if (!empty($usuario_perfil["foto"])): ?>
                        <img src="<?php echo htmlspecialchars($usuario_perfil["foto"], ENT_QUOTES, "UTF-8"); ?>" alt="">
                    <?php else: ?>
                        <?php echo htmlspecialchars($iniciales_perfil, ENT_QUOTES, "UTF-8"); ?>
                    <?php endif; ?>
                </span>
                <div>
                    <strong><?php echo htmlspecialchars($usuario_perfil["nombre"], ENT_QUOTES, "UTF-8"); ?></strong>
                    <span><?php echo htmlspecialchars($usuario_perfil["correo"], ENT_QUOTES, "UTF-8"); ?></span>
                    <span class="perfil-rango"><?php echo htmlspecialchars($nombre_rol_perfil, ENT_QUOTES, "UTF-8"); ?></span>
                </div>
            </div>
            <a class="perfil-enlace" href="/index.php?pagina=perfil">Mi perfil</a>
            <?php foreach ($ligas_menu_perfil as $liga_menu): ?>
                <a class="perfil-enlace perfil-enlace-club" href="/index.php?pagina=enfrentamientos&amp;liga=<?php echo (int) $liga_menu["id"]; ?>">
                    <b class="perfil-enlace-liga" aria-hidden="true">L</b>
                    <span><?php echo htmlspecialchars($liga_menu["nombre"], ENT_QUOTES, "UTF-8"); ?></span>
                </a>
            <?php endforeach; ?>
            <?php foreach ($clubes_menu_perfil as $club_menu): ?>
                <a class="perfil-enlace perfil-enlace-club" href="/index.php?pagina=enfrentamientos&amp;ver=<?php echo urlencode($club_menu["id"]); ?>">
                    <img src="<?php echo htmlspecialchars($club_menu["logo"], ENT_QUOTES, "UTF-8"); ?>" alt="">
                    <span><?php echo htmlspecialchars($club_menu["nombre"], ENT_QUOTES, "UTF-8"); ?></span>
                    <?php if ($club_menu["amarillas"] + $club_menu["expulsiones"] > 0): ?>
                        <span class="perfil-enlace-avisos" title="Avisos disciplinarios"><?php echo (int) ($club_menu["amarillas"] + $club_menu["expulsiones"]); ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
            <?php if (es_administrador()): ?>
                <a class="perfil-enlace" href="/index.php?pagina=permisos">Administrar roles</a>
            <?php endif; ?>
            <a class="perfil-enlace" href="/index.php?pagina=inicio">Ir al inicio</a>
            <a class="perfil-enlace perfil-salir" href="/app/controllers/salir.php">Cerrar sesión</a>
        </div>
    </details>
<?php else: ?>
    <a href="/index.php?pagina=ingresar" class="boton-ingresar">INGRESAR</a>
<?php endif; ?>

<script>
    document.addEventListener("click", (event) => {
        document.querySelectorAll(".perfil-menu[open]").forEach((menu) => {
            if (!menu.contains(event.target)) menu.open = false;
        });
    });

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") return;
        document.querySelectorAll(".perfil-menu[open]").forEach((menu) => {
            menu.open = false;
            menu.querySelector("summary").focus();
        });
    });
</script>