<?php
// pestaña administracion de una liga

// variables que vienen de enfrentamientos.php (para que el editor no marque error)
/** @var callable $e */
/** @var array $liga_actual */
/** @var array $administradores_liga */
/** @var array $planes_liga */
/** @var array $torneos */
/** @var string $pestana */
/** @var bool $puede_gestionar_ligas */
$campos_liga = function ($accion) use ($e, $liga_actual) {
    return '<input type="hidden" name="csrf_token" value="' . $e($_SESSION["csrf_clubes"] ?? "") . '">'
        . '<input type="hidden" name="accion" value="' . $e($accion) . '">'
        . '<input type="hidden" name="liga_id" value="' . (int) $liga_actual["id"] . '">';
};
$hay_lugar_admin = $liga_actual["uso"]["administradores"] < $liga_actual["limites"]["administradores"];
?>
<section class="pestana_panel panel_admin" id="panel-admin" data-pestana="admin" aria-labelledby="titulo-admin" <?php echo $pestana === "admin" ? "" : "hidden"; ?>>

    <div class="seccion_cabecera">
        <h2 id="titulo-admin">Administración de la liga</h2>
        <p>
            <?php echo $puede_gestionar_ligas
                ? "Como organizador puedes cambiar el plan de la liga y elegir qué clientes la administran."
                : "Administras esta liga: puedes agregar equipos, armar torneos, el calendario y cargar resultados."; ?>
        </p>
    </div>

    <div class="plan_liga_grilla">

        <?php if ($puede_gestionar_ligas): ?>
            <section class="bloque_torneo" aria-labelledby="titulo-datos-liga">
                <h4 id="titulo-datos-liga">Datos y plan</h4>
                <form method="post" action="/index.php?pagina=enfrentamientos">
                    <?php echo $campos_liga("editar_liga"); ?>
                    <div class="form_grilla">
                        <div class="campo campo_ancho_grilla">
                            <label for="editarLigaNombre">Nombre</label>
                            <input id="editarLigaNombre" name="nombre" type="text" minlength="3" maxlength="60" required value="<?php echo $e($liga_actual["nombre"]); ?>">
                        </div>
                        <div class="campo campo_ancho_grilla">
                            <label for="editarLigaPlan">Plan contratado</label>
                            <select id="editarLigaPlan" name="plan" required>
                                <?php foreach ($planes_liga as $id_plan => $plan): ?>
                                    <option value="<?php echo $e($id_plan); ?>" <?php echo $id_plan === $liga_actual["plan"] ? "selected" : ""; ?>><?php echo $e($plan["nombre"]); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="campo">
                            <label for="editarEquiposExtra">Equipos adicionales</label>
                            <input id="editarEquiposExtra" name="equipos_extra" type="number" min="0" max="1000" value="<?php echo (int) $liga_actual["equipos_extra"]; ?>">
                        </div>
                        <div class="campo">
                            <label for="editarJugadoresExtra">Jugadores adicionales</label>
                            <input id="editarJugadoresExtra" name="jugadores_extra" type="number" min="0" max="1000" value="<?php echo (int) $liga_actual["jugadores_extra"]; ?>">
                        </div>
                    </div>
                    <p class="campo_ayuda">Deporte: <?php echo $e($liga_actual["nombre_deporte"]); ?> (no se puede cambiar). Si bajas de plan y la liga tiene más equipos o jugadores que el nuevo límite, no se borra nada, pero no se podrán sumar más.</p>
                    <button type="submit" class="boton_agregar boton_chico">Guardar cambios</button>
                </form>
            </section>
        <?php endif; ?>

        <!-- logo y portada -->
        <section class="bloque_torneo" id="bloqueImagenesLiga" aria-labelledby="titulo-imagenes-liga">
            <h4 id="titulo-imagenes-liga">Logo y foto de portada</h4>
            <form method="post" action="/index.php?pagina=enfrentamientos" enctype="multipart/form-data">
                <?php echo $campos_liga("imagenes_liga"); ?>

                <div class="portada_editar">
                    <?php if (!empty($liga_actual["portada"])): ?>
                        <img class="portada_vista" id="vistaPortada" src="<?php echo $e($liga_actual["portada"]); ?>" alt="Foto de portada actual">
                    <?php else: ?>
                        <img class="portada_vista" id="vistaPortada" alt="Vista previa de la portada" hidden>
                        <span class="portada_vacia" id="portadaVacia">Sin foto de portada</span>
                    <?php endif; ?>
                </div>
                <div class="campo">
                    <label for="imagenPortada">Foto de portada <small>(una foto horizontal de la liga · JPG, PNG o WEBP, máx. 2 MB)</small></label>
                    <input id="imagenPortada" name="portada_liga" type="file" accept="image/jpeg,image/png,image/webp">
                    <?php if (!empty($liga_actual["portada"])): ?>
                        <label class="filtro_mias"><input type="checkbox" name="quitar_portada" value="1"> Quitar la foto de portada</label>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label for="imagenLogo">Logo <small>(JPG, PNG o WEBP, máx. 2 MB)</small></label>
                    <div class="logo_editar">
                        <?php if (!empty($liga_actual["logo"])): ?>
                            <img class="liga_logo" id="vistaLogo" src="<?php echo $e($liga_actual["logo"]); ?>" alt="Logo actual">
                        <?php else: ?>
                            <img class="liga_logo" id="vistaLogo" alt="Vista previa del logo" hidden>
                            <span class="liga_logo liga_logo_iniciales" id="logoIniciales" aria-hidden="true"><?php echo $e($liga_actual["iniciales"]); ?></span>
                        <?php endif; ?>
                        <input id="imagenLogo" name="logo_liga" type="file" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <?php if (!empty($liga_actual["logo"])): ?>
                        <label class="filtro_mias"><input type="checkbox" name="quitar_logo" value="1"> Quitar el logo (se muestran las iniciales)</label>
                    <?php endif; ?>
                </div>

                <button type="submit" class="boton_agregar boton_chico">Guardar imágenes</button>
            </form>
        </section>

        <section class="bloque_torneo" aria-labelledby="titulo-admins-liga">
            <h4 id="titulo-admins-liga">Administradores de la liga
                <small>(<?php echo (int) $liga_actual["uso"]["administradores"]; ?> de <?php echo (int) $liga_actual["limites"]["administradores"]; ?>)</small>
            </h4>
            <ul class="lista_editores">
                <?php if (count($administradores_liga) === 0): ?>
                    <li class="lista_editores_vacia">Ningún cliente administra esta liga todavía.</li>
                <?php endif; ?>
                <?php foreach ($administradores_liga as $admin): ?>
                    <li class="editor">
                        <span class="editor_nombre"><?php echo $e($admin["nombre"]); ?></span>
                        <span class="editor_correo"><?php echo $e($admin["correo"]); ?></span>
                        <?php if ($puede_gestionar_ligas): ?>
                            <form method="post" action="/index.php?pagina=enfrentamientos" class="form_confirmar" data-confirmar="¿Quitarle a <?php echo $e($admin["nombre"]); ?> la administración de la liga?">
                                <?php echo $campos_liga("quitar_admin"); ?>
                                <input type="hidden" name="usuario_id" value="<?php echo (int) $admin["id"]; ?>">
                                <button type="submit" class="integrante_quitar" title="Quitar administrador" aria-label="Quitar a <?php echo $e($admin["nombre"]); ?>">×</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($puede_gestionar_ligas): ?>
                <?php if ($hay_lugar_admin): ?>
                    <form class="form_editor" method="post" action="/index.php?pagina=enfrentamientos">
                        <?php echo $campos_liga("agregar_admin"); ?>
                        <label for="correoAdminLiga" class="solo-lector">Correo del cliente</label>
                        <input type="email" name="correo" id="correoAdminLiga" required maxlength="190" autocomplete="off" placeholder="Correo del cliente que contrató el plan">
                        <button type="submit" class="boton_agregar boton_chico">Agregar</button>
                    </form>
                    <p class="campo_ayuda">La cuenta tiene que tener rol Cliente. Puede agregar equipos, torneos, partidos y resultados en esta liga.</p>
                <?php else: ?>
                    <p class="limite_alcanzado">El <?php echo $e($liga_actual["nombre_plan"]); ?> permite <?php echo (int) $liga_actual["limites"]["administradores"]; ?> administrador(es).</p>
                <?php endif; ?>
            <?php endif; ?>
        </section>

    </div>

    <?php if ($puede_gestionar_ligas): ?>
        <!-- eliminar la liga (hay que escribir el nombre) -->
        <section class="bloque_torneo zona_peligro" aria-labelledby="titulo-eliminar-liga">
            <h4 id="titulo-eliminar-liga">Eliminar liga</h4>
            <p>
                Se borran la liga, sus <?php echo count($torneos); ?> torneo(s) con su calendario y resultados, sus administradores, el logo y la portada.
                <strong>Esto no se puede deshacer.</strong>
                <?php if ($liga_actual["uso"]["equipos"] > 0): ?>
                    Sus <?php echo (int) $liga_actual["uso"]["equipos"]; ?> equipo(s) no se borran: quedan en "Equipos sin liga" para pasarlos a otra.
                <?php endif; ?>
            </p>
            <form method="post" action="/index.php?pagina=enfrentamientos" class="form_eliminar_liga">
                <?php echo $campos_liga("eliminar_liga"); ?>
                <label for="confirmarEliminarLiga">Para confirmar, escribe el nombre de la liga: <strong><?php echo $e($liga_actual["nombre"]); ?></strong></label>
                <div class="form_linea">
                    <input id="confirmarEliminarLiga" name="confirmacion" type="text" autocomplete="off" required data-nombre="<?php echo $e($liga_actual["nombre"]); ?>">
                    <button type="submit" class="boton_peligro" id="botonEliminarLiga" disabled>Eliminar liga</button>
                </div>
            </form>
        </section>
    <?php endif; ?>
</section>

<script>
    // el boton de eliminar se habilita solo cuando se escribio el nombre exacto de la liga
    const confirmarEliminar = document.getElementById("confirmarEliminarLiga");
    if (confirmarEliminar) {
        const normalizarNombre = texto => texto.trim().replace(/\s+/g, " ").toLowerCase();
        confirmarEliminar.addEventListener("input", () => {
            document.getElementById("botonEliminarLiga").disabled =
                normalizarNombre(confirmarEliminar.value) !== normalizarNombre(confirmarEliminar.dataset.nombre);
        });
    }
</script>

<script>
    // vista previa de la portada y el logo antes de guardar (y aviso si pesan mas de 2 MB)
    [["imagenPortada", "vistaPortada", "portadaVacia"], ["imagenLogo", "vistaLogo", "logoIniciales"]].forEach(([idInput, idVista, idVacio]) => {
        const input = document.getElementById(idInput);
        input.addEventListener("change", () => {
            const archivo = input.files[0];
            if (!archivo) return;
            if (archivo.size > 2 * 1024 * 1024) {
                alert("La imagen no puede superar los 2 MB.");
                input.value = "";
                return;
            }
            const vista = document.getElementById(idVista);
            vista.src = URL.createObjectURL(archivo);
            vista.hidden = false;
            const vacio = document.getElementById(idVacio);
            if (vacio) vacio.hidden = true;
        });
    });

    document.querySelectorAll("#panel-admin .form_confirmar").forEach(form => {
        form.addEventListener("submit", evento => {
            if (!confirm(form.dataset.confirmar)) evento.preventDefault();
        });
    });
</script>
