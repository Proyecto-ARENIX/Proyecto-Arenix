<?php
$usuario = $usuario ?? usuario_actual();
$roles_perfil_pagina = roles_disponibles();
$actualizado = $actualizado ?? false;
$error = $error ?? "";
$mis_clubes = $mis_clubes ?? [];
$mis_ligas = $mis_ligas ?? [];
$muestra_mis_clubes = in_array($usuario["rol"], ["cliente", "participante"], true);
$partes_nombre = preg_split('/\s+/u', trim($usuario["nombre"]), -1, PREG_SPLIT_NO_EMPTY);
$iniciales_perfil = "";
foreach (array_slice($partes_nombre, 0, 2) as $parte_nombre) {
    if (preg_match('/^\p{L}/u', $parte_nombre, $coincidencia)) {
        $iniciales_perfil .= $coincidencia[0];
    }
}
if ($iniciales_perfil === "") {
    $iniciales_perfil = "U";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil - ARENIX</title>
    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/perfil.css">
</head>
<body class="perfil-body">
    <header class="perfil-barra">
        <a class="perfil-marca" href="/index.php?pagina=inicio">ARE<span>NIX</span></a>
        <a class="perfil-volver" href="/index.php?pagina=inicio">Volver al inicio</a>
    </header>

    <main class="perfil-pagina">
        <header class="perfil-titulo">
            <div>
                <p>CUENTA</p>
                <h1>Mi perfil</h1>
            </div>
            <a class="perfil-cerrar" href="/app/controllers/salir.php">Cerrar sesión</a>
        </header>

        <?php if ($actualizado): ?>
            <p class="perfil-mensaje perfil-exito" role="status">Tu foto de perfil se actualizó correctamente.</p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="perfil-mensaje perfil-error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></p>
        <?php endif; ?>

        <div class="perfil-grilla">
            <section class="perfil-panel-usuario" aria-label="Resumen de la cuenta">
                <div class="perfil-foto-marco">
                    <?php if (!empty($usuario["foto"])): ?>
                        <img class="perfil-foto" id="perfil-foto-previa" src="<?php echo htmlspecialchars($usuario["foto"], ENT_QUOTES, "UTF-8"); ?>" alt="Foto de perfil">
                        <span class="perfil-iniciales" id="perfil-iniciales" hidden><?php echo htmlspecialchars($iniciales_perfil, ENT_QUOTES, "UTF-8"); ?></span>
                    <?php else: ?>
                        <img class="perfil-foto" id="perfil-foto-previa" alt="Vista previa de la foto" hidden>
                        <span class="perfil-iniciales" id="perfil-iniciales"><?php echo htmlspecialchars($iniciales_perfil, ENT_QUOTES, "UTF-8"); ?></span>
                    <?php endif; ?>
                </div>
                <h2><?php echo htmlspecialchars($usuario["nombre"], ENT_QUOTES, "UTF-8"); ?></h2>
                <p><?php echo htmlspecialchars($usuario["correo"], ENT_QUOTES, "UTF-8"); ?></p>
                <span class="perfil-etiqueta">Cuenta ARENIX</span>
            </section>

            <section class="perfil-panel-datos" aria-labelledby="titulo-datos">
                <header class="perfil-seccion-cabecera">
                    <div>
                        <p>INFORMACIÓN PERSONAL</p>
                        <h2 id="titulo-datos">Datos de la cuenta</h2>
                    </div>
                </header>

                <dl class="perfil-datos">
                    <div><dt>Nombre</dt><dd><?php echo htmlspecialchars($usuario["nombre"], ENT_QUOTES, "UTF-8"); ?></dd></div>
                    <div><dt>Correo electrónico</dt><dd><?php echo htmlspecialchars($usuario["correo"], ENT_QUOTES, "UTF-8"); ?></dd></div>
                    <div><dt>Teléfono</dt><dd><?php echo !empty($usuario["telefono"]) ? htmlspecialchars($usuario["prefijo_telefono"] . " " . $usuario["telefono"], ENT_QUOTES, "UTF-8") : "Sin teléfono registrado"; ?></dd></div>
                    <div><dt>ID de usuario</dt><dd>#<?php echo (int) $usuario["id"]; ?></dd></div>
                    <div><dt>Rango</dt><dd><?php echo htmlspecialchars($roles_perfil_pagina[$usuario["rol"]] ?? "Público", ENT_QUOTES, "UTF-8"); ?></dd></div>
                </dl>

                <form class="perfil-form-foto" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_perfil"], ENT_QUOTES, "UTF-8"); ?>">
                    <label for="foto">Foto de perfil</label>
                    <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" required>
                    <small>JPG, PNG o WEBP · máximo 2 MB</small>
                    <button type="submit">Guardar foto</button>
                </form>
            </section>

            <?php if ($muestra_mis_clubes): ?>
                <!-- acceso rapido a los clubes del usuario -->
                <section class="perfil-clubes" aria-labelledby="titulo-clubes">
                    <header class="perfil-seccion-cabecera">
                        <div>
                            <p>ACCESO RÁPIDO</p>
                            <h2 id="titulo-clubes"><?php echo count($mis_ligas) > 0 ? "Mis ligas y clubes" : (count($mis_clubes) === 1 ? "Mi club" : "Mis clubes"); ?></h2>
                        </div>
                    </header>

                    <?php if (count($mis_ligas) > 0): ?>
                        <!-- ligas que administra el cliente -->
                        <div class="perfil-clubes-lista">
                            <?php foreach ($mis_ligas as $liga): ?>
                                <article class="perfil-club perfil-liga">
                                    <?php if (!empty($liga["logo"])): ?>
                                        <img src="<?php echo htmlspecialchars($liga["logo"], ENT_QUOTES, "UTF-8"); ?>" alt="">
                                    <?php else: ?>
                                        <span class="perfil-liga-iniciales" aria-hidden="true"><?php echo htmlspecialchars($liga["iniciales"], ENT_QUOTES, "UTF-8"); ?></span>
                                    <?php endif; ?>
                                    <div class="perfil-club-info">
                                        <strong><?php echo htmlspecialchars($liga["nombre"], ENT_QUOTES, "UTF-8"); ?></strong>
                                        <small><?php echo htmlspecialchars($liga["nombre_deporte"] . " · " . $liga["nombre_plan"], ENT_QUOTES, "UTF-8"); ?></small>
                                        <span class="perfil-club-avisos">
                                            <span class="perfil-aviso"><?php echo (int) $liga["uso"]["equipos"]; ?> / <?php echo (int) $liga["limites"]["equipos"]; ?> equipos</span>
                                            <span class="perfil-aviso"><?php echo (int) $liga["uso"]["torneos"]; ?> / <?php echo (int) $liga["limites"]["torneos"]; ?> torneos activos</span>
                                        </span>
                                    </div>
                                    <div class="perfil-club-acciones">
                                        <a class="perfil-club-editar" href="/index.php?pagina=enfrentamientos&amp;liga=<?php echo (int) $liga["id"]; ?>">Administrar liga</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (count($mis_clubes) === 0 && count($mis_ligas) === 0): ?>
                        <p class="perfil-clubes-vacio">
                            <?php echo $usuario["rol"] === "cliente"
                                ? "Todavía no tienes una liga ni un club asignado. Cuando un organizador te asigne la liga que contrataste, aparecerá acá."
                                : "Todavía no formas parte de ningún club. Cuando tu club te agregue, aparecerá acá."; ?>
                        </p>
                    <?php elseif (count($mis_clubes) > 0): ?>
                        <div class="perfil-clubes-lista">
                            <?php foreach ($mis_clubes as $club): ?>
                                <article class="perfil-club">
                                    <img src="<?php echo htmlspecialchars($club["logo"], ENT_QUOTES, "UTF-8"); ?>" alt="">
                                    <div class="perfil-club-info">
                                        <strong><?php echo htmlspecialchars($club["nombre"], ENT_QUOTES, "UTF-8"); ?></strong>
                                        <small><?php echo htmlspecialchars($club["detalle"], ENT_QUOTES, "UTF-8"); ?></small>

                                        <?php if ($club["amarillas"] > 0 || $club["expulsiones"] > 0): ?>
                                            <span class="perfil-club-avisos">
                                                <?php if ($club["amarillas"] > 0): ?>
                                                    <span class="perfil-aviso perfil-aviso-amarilla"><?php echo (int) $club["amarillas"]; ?> <?php echo $club["amarillas"] === 1 ? "amarilla" : "amarillas"; ?></span>
                                                <?php endif; ?>
                                                <?php if ($club["expulsiones"] > 0): ?>
                                                    <span class="perfil-aviso perfil-aviso-roja"><?php echo (int) $club["expulsiones"]; ?> <?php echo $club["expulsiones"] === 1 ? "expulsado" : "expulsados"; ?></span>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="perfil-club-avisos"><span class="perfil-aviso">Sin avisos disciplinarios</span></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="perfil-club-acciones">
                                        <a href="/index.php?pagina=enfrentamientos&amp;ver=<?php echo urlencode($club["id"]); ?>">Ver club</a>
                                        <?php if ($club["editable"]): ?>
                                            <a class="perfil-club-editar" href="/index.php?pagina=enfrentamientos&amp;editar=<?php echo urlencode($club["id"]); ?>">Editar</a>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <section class="perfil-opciones" aria-labelledby="titulo-opciones">
                <header class="perfil-seccion-cabecera">
                    <div>
                        <p>PREFERENCIAS Y SEGURIDAD</p>
                        <h2 id="titulo-opciones">Opciones de cuenta</h2>
                    </div>
                </header>
                <div class="perfil-opciones-lista">
                    <a href="/index.php?pagina=recuperar-contrasena">
                        <span><strong>Cambiar contraseña</strong><small>Actualiza las credenciales de acceso.</small></span>
                        <span aria-hidden="true">&#8250;</span>
                    </a>
                    <a href="/index.php?pagina=inicio">
                        <span><strong>Volver a ARENIX</strong><small>Regresa al inicio de la plataforma.</small></span>
                        <span aria-hidden="true">&#8250;</span>
                    </a>
                </div>
            </section>
        </div>
    </main>

    <script>
        const campoFotoPerfil = document.getElementById("foto");
        const previaFotoPerfil = document.getElementById("perfil-foto-previa");
        const perfilIniciales = document.getElementById("perfil-iniciales");

        campoFotoPerfil.addEventListener("change", () => {
            const archivo = campoFotoPerfil.files[0];
            if (!archivo) return;

            const urlAnterior = previaFotoPerfil.dataset.objectUrl;
            if (urlAnterior) URL.revokeObjectURL(urlAnterior);

            const urlVistaPrevia = URL.createObjectURL(archivo);
            previaFotoPerfil.src = urlVistaPrevia;
            previaFotoPerfil.dataset.objectUrl = urlVistaPrevia;
            previaFotoPerfil.hidden = false;
            perfilIniciales.hidden = true;
        });
    </script>
</body>
</html>