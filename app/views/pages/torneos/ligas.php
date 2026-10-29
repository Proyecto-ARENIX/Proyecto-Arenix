<?php
// lista de ligas con buscador (cuando no hay una liga elegida)

// variables que vienen de enfrentamientos.php (para que el editor no marque error)
/** @var callable $e */
/** @var array $ligas */
/** @var array $ligas_que_administro */
/** @var array $contrataciones */
/** @var array $historial_contrataciones */
/** @var array $equipos_sin_liga */
/** @var bool $puede_gestionar_ligas */

// solo se ofrecen en los filtros los deportes y planes que tienen alguna liga
$deportes_con_ligas = array_unique(array_column($ligas, "nombre_deporte", "deporte"));
asort($deportes_con_ligas);
$planes_con_ligas = [];
foreach (planes_liga() as $id_plan => $plan) {
    if (in_array($id_plan, array_column($ligas, "plan"), true)) {
        $planes_con_ligas[$id_plan] = $plan["nombre"];
    }
}

// el plan lo ven organizador/admin y el cliente de esa liga
$ve_plan = fn($liga) => $puede_gestionar_ligas || in_array($liga["id"], $ligas_que_administro, true);

// logo de la liga o, si no tiene, sus iniciales
$logo_liga = function ($liga, $clase) use ($e) {
    if (!empty($liga["logo"])) {
        return '<img class="' . $clase . '" src="' . $e($liga["logo"]) . '" alt="">';
    }
    return '<span class="' . $clase . ' liga_logo_iniciales" aria-hidden="true">' . $e($liga["iniciales"]) . '</span>';
};
?>
<section class="seccion_ligas" aria-labelledby="titulo-ligas">

    <div class="seccion_cabecera seccion_cabecera_acciones">
        <div>
            <h2 id="titulo-ligas">Ligas</h2>
            <p>Elige una liga para ver sus equipos, el calendario, los resultados y la tabla de posiciones.</p>
        </div>
        <?php
            $planes_por_id = planes_liga();
            $deportes_todos = deportes_clubes();
            $pendientes = count($contrataciones);

            // datos de una contratacion (los usan el menu y el historial)
            $datos_contratacion = function ($orden) use ($e, $planes_por_id, $deportes_todos) { ?>
                <div class="contratacion_fila">
                    <span class="liga_tarjeta_plan liga_<?php echo $e($orden["plan"]); ?>"><?php echo $e($planes_por_id[$orden["plan"]]["nombre"] ?? $orden["plan"]); ?></span>
                    <small>#<?php echo (int) $orden["id"]; ?> · <?php echo $e(date("d/m/Y", strtotime($orden["creado_en"]))); ?> · USD <?php echo (int) $orden["total_mensual"]; ?>/mes</small>
                </div>
                <strong><?php echo $e($orden["organizacion"]); ?></strong>
                <small class="contratacion_extra">
                    <?php echo $e(($deportes_todos[$orden["deporte"]] ?? $orden["deporte"]) . " · " . $orden["ciudad"]); ?>
                    <?php if ($orden["equipos_extra"] > 0 || $orden["jugadores_extra"] > 0): ?>
                        · +<?php echo (int) $orden["equipos_extra"]; ?> eq · +<?php echo (int) $orden["jugadores_extra"]; ?> jug
                    <?php endif; ?>
                </small>
                <small class="contratacion_extra"><?php echo $e($orden["nombre"] . " · " . $orden["correo"] . " · " . $orden["telefono"]); ?></small>
                <small class="contratacion_extra">
                    <?php echo $orden["estado"] === "pagado_prueba" ? "Pago de prueba" : "Pagado"; ?> · <?php echo $e(ucfirst($orden["tarjeta_marca"]) . " •••• " . $orden["tarjeta_ultimos"]); ?>
                </small>
            <?php };

            if ($puede_gestionar_ligas): ?>
            <div class="cabecera_botones">
                <!-- planes contratados sin liga (cuando se crea la liga pasan al historial) -->
                <div class="contrataciones_menu">
                    <button type="button" class="boton_contrataciones" id="abrirContrataciones" aria-expanded="false" aria-controls="panelContrataciones">
                        Planes contratados
                        <?php if ($pendientes > 0): ?>
                            <span class="notificacion" aria-label="<?php echo $pendientes; ?> sin liga"><?php echo $pendientes; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="panel_contrataciones" id="panelContrataciones" hidden>
                        <div class="panel_contrataciones_cabeza">
                            <strong>Planes contratados</strong>
                            <small><?php echo $pendientes === 1 ? "1 sin liga" : "$pendientes sin liga"; ?></small>
                        </div>
                        <?php if ($pendientes === 0): ?>
                            <p class="panel_contrataciones_vacio">No hay planes nuevos. Los que ya tienen su liga están en el historial.</p>
                        <?php else: ?>
                            <ul class="lista_contrataciones">
                                <?php foreach ($contrataciones as $orden): ?>
                                    <li class="contratacion contratacion_pendiente">
                                        <?php $datos_contratacion($orden); ?>
                                        <button type="button" class="boton_agregar boton_chico crear_desde_contratacion"
                                            data-contratacion="<?php echo (int) $orden["id"]; ?>"
                                            data-nombre="<?php echo $e($orden["organizacion"]); ?>"
                                            data-deporte="<?php echo $e($orden["deporte"]); ?>"
                                            data-plan="<?php echo $e($orden["plan"]); ?>"
                                            data-equipos="<?php echo (int) $orden["equipos_extra"]; ?>"
                                            data-jugadores="<?php echo (int) $orden["jugadores_extra"]; ?>"
                                            data-cliente="<?php echo $e($orden["nombre"] . " (" . $orden["correo"] . ")"); ?>">+ Crear su liga</button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <button type="button" class="panel_contrataciones_historial" id="abrirHistorial">
                            Ver historial <small>(<?php echo count($historial_contrataciones); ?>)</small>
                        </button>
                    </div>
                </div>
                <button type="button" class="boton_agregar" id="abrirCrearLiga">+ Crear liga</button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (count($ligas) === 0): ?>
        <p class="sin_resultados">Todavía no hay ligas.</p>
    <?php else: ?>
        <!-- buscador y filtros -->
        <div class="buscador buscador_ligas">
            <label for="buscarLiga" class="solo-lector">Buscar liga</label>
            <input id="buscarLiga" type="search" placeholder="Buscar liga o equipo..." autocomplete="off">

            <label for="filtroDeporteLiga" class="solo-lector">Filtrar por deporte</label>
            <select id="filtroDeporteLiga" class="filtro_deporte">
                <option value="">Todos los deportes</option>
                <?php foreach ($deportes_con_ligas as $id_deporte => $nombre_deporte): ?>
                    <option value="<?php echo $e($id_deporte); ?>"><?php echo $e($nombre_deporte); ?></option>
                <?php endforeach; ?>
            </select>

            <?php if ($puede_gestionar_ligas): ?>
                <label for="filtroPlanLiga" class="solo-lector">Filtrar por plan</label>
                <select id="filtroPlanLiga" class="filtro_deporte">
                    <option value="">Todos los planes</option>
                    <?php foreach ($planes_con_ligas as $id_plan => $nombre_plan): ?>
                        <option value="<?php echo $e($id_plan); ?>"><?php echo $e($nombre_plan); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <?php if (count($ligas_que_administro) > 0): ?>
                <label class="filtro_mias">
                    <input type="checkbox" id="filtroMisLigas"> Solo mis ligas
                </label>
            <?php endif; ?>

            <span class="buscador_contador" id="contadorLigas" role="status"><?php echo count($ligas); ?> <?php echo count($ligas) === 1 ? "liga" : "ligas"; ?></span>
        </div>

        <?php
            // nombres de los equipos de cada liga, para que el buscador tambien los encuentre
            $nombres_por_club = array_column(listar_equipos(), "nombre", "id");
        ?>
        <ul class="lista_ligas" id="listaLigas">
            <?php foreach ($ligas as $liga):
                $con_plan = $ve_plan($liga);
                $busqueda = $liga["nombre"] . " " . $liga["nombre_deporte"] . " " . ($con_plan ? $liga["nombre_plan"] : "") . " "
                    . implode(" ", array_map(fn($club) => $nombres_por_club[$club] ?? "", $liga["clubes"]));
            ?>
                <li class="fila_liga"
                    data-busqueda="<?php echo $e(mb_strtolower($busqueda, "UTF-8")); ?>"
                    data-deporte="<?php echo $e($liga["deporte"]); ?>"
                    data-plan="<?php echo $con_plan ? $e($liga["plan"]) : ""; ?>"
                    data-mia="<?php echo in_array($liga["id"], $ligas_que_administro, true) ? "1" : "0"; ?>">
                    <a class="liga_tarjeta <?php echo $con_plan ? "liga_" . $e($liga["plan"]) : "liga_neutra"; ?> <?php echo !empty($liga["portada"]) ? "liga_tarjeta_con_portada" : ""; ?>" href="/index.php?pagina=enfrentamientos&amp;liga=<?php echo (int) $liga["id"]; ?>">
                        <?php if (!empty($liga["portada"])): ?>
                            <img class="liga_tarjeta_portada" src="<?php echo $e($liga["portada"]); ?>" alt="" loading="lazy">
                        <?php endif; ?>
                        <span class="liga_tarjeta_cabeza">
                            <?php echo $logo_liga($liga, "liga_logo"); ?>
                            <?php if ($con_plan): ?>
                                <span class="liga_tarjeta_plan"><?php echo $e($liga["nombre_plan"]); ?></span>
                            <?php else: ?>
                                <span class="liga_tarjeta_ver" aria-hidden="true">Ver liga ›</span>
                            <?php endif; ?>
                        </span>
                        <strong><?php echo $e($liga["nombre"]); ?></strong>
                        <small><?php echo $e($liga["nombre_deporte"]); ?></small>
                        <span class="liga_tarjeta_datos">
                            <span><?php echo (int) $liga["uso"]["equipos"]; ?> equipos</span>
                            <span><?php echo (int) $liga["uso"]["torneos"]; ?> <?php echo $liga["uso"]["torneos"] === 1 ? "torneo activo" : "torneos activos"; ?></span>
                        </span>
                        <?php if (in_array($liga["id"], $ligas_que_administro, true)): ?>
                            <span class="liga_tarjeta_mia">Tú administras esta liga</span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="sin_resultados" id="sinLigas" hidden>No hay ligas que coincidan con la búsqueda.</p>

        <script>
            // buscador y filtros de ligas
            (function () {
                const buscar = document.getElementById("buscarLiga");
                const deporte = document.getElementById("filtroDeporteLiga");
                const plan = document.getElementById("filtroPlanLiga");
                const mias = document.getElementById("filtroMisLigas");
                const filas = document.querySelectorAll(".fila_liga");
                const sinTexto = texto => texto.toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g, "");

                function filtrarLigas() {
                    const termino = sinTexto(buscar.value.trim());
                    let visibles = 0;
                    filas.forEach(fila => {
                        const coincide = sinTexto(fila.dataset.busqueda).includes(termino)
                            && (!deporte.value || fila.dataset.deporte === deporte.value)
                            && (!plan || !plan.value || fila.dataset.plan === plan.value)
                            && (!mias || !mias.checked || fila.dataset.mia === "1");
                        fila.hidden = !coincide;
                        if (coincide) visibles++;
                    });
                    document.getElementById("contadorLigas").textContent = visibles + (visibles === 1 ? " liga" : " ligas");
                    document.getElementById("sinLigas").hidden = visibles > 0;
                }

                buscar.addEventListener("input", filtrarLigas);
                [deporte, plan, mias].forEach(control => control && control.addEventListener("change", filtrarLigas));
            })();
        </script>
    <?php endif; ?>

    <?php if (count($equipos_sin_liga) > 0): ?>
        <!-- equipos que quedaron sin liga -->
        <section class="equipos_sin_liga" aria-labelledby="titulo-sin-liga">
            <h3 id="titulo-sin-liga">Equipos sin liga <small>(<?php echo count($equipos_sin_liga); ?>)</small></h3>
            <p class="campo_ayuda">Quedaron de ligas eliminadas. Pásalos a una liga de su mismo deporte (se respetan los límites del plan).</p>
            <ul class="lista_sin_liga">
                <?php foreach ($equipos_sin_liga as $equipo_suelto):
                    $destinos = array_filter($ligas, fn($l) => $l["deporte"] === $equipo_suelto["deporte"]);
                ?>
                    <li>
                        <img class="mini_escudo" src="<?php echo $e($equipo_suelto["logo"]); ?>" alt="">
                        <span class="sin_liga_nombre">
                            <strong><?php echo $e($equipo_suelto["nombre"]); ?></strong>
                            <small><?php echo $e($equipo_suelto["nombre_deporte"]); ?> · <?php echo count($equipo_suelto["jugadores"]); ?> jugadores</small>
                        </span>
                        <?php if (count($destinos) === 0): ?>
                            <span class="campo_ayuda">No hay ligas de <?php echo $e($equipo_suelto["nombre_deporte"]); ?>. Crea una para pasarlo.</span>
                        <?php else: ?>
                            <form method="post" action="/index.php?pagina=enfrentamientos" class="form_mover">
                                <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"] ?? ""); ?>">
                                <input type="hidden" name="accion" value="mover_equipo">
                                <input type="hidden" name="club_id" value="<?php echo $e($equipo_suelto["id"]); ?>">
                                <label class="solo-lector" for="mover-<?php echo $e($equipo_suelto["id"]); ?>">Liga de destino</label>
                                <select name="liga_id" id="mover-<?php echo $e($equipo_suelto["id"]); ?>" required>
                                    <?php foreach ($destinos as $destino): ?>
                                        <option value="<?php echo (int) $destino["id"]; ?>"><?php echo $e($destino["nombre"] . " (" . $destino["uso"]["equipos"] . "/" . $destino["limites"]["equipos"] . " equipos)"); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="boton_agregar boton_chico">Pasar a esta liga</button>
                            </form>
                        <?php endif; ?>
                        <details class="eliminar_suelto">
                            <summary>Eliminar</summary>
                            <form method="post" action="/index.php?pagina=enfrentamientos" class="form_linea">
                                <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"] ?? ""); ?>">
                                <input type="hidden" name="accion" value="eliminar_club">
                                <input type="hidden" name="club_id" value="<?php echo $e($equipo_suelto["id"]); ?>">
                                <input name="confirmacion" type="text" autocomplete="off" required aria-label="Nombre del equipo para confirmar" placeholder="Escribe &quot;<?php echo $e($equipo_suelto["nombre"]); ?>&quot; para confirmar">
                                <button type="submit" class="boton_peligro boton_chico">Eliminar equipo</button>
                            </form>
                        </details>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

</section>

<?php if ($puede_gestionar_ligas): ?>
    <!-- crear liga: solo organizadores y administradores -->
    <dialog class="modal_equipo modal_liga" id="modalCrearLiga" aria-labelledby="tituloCrearLiga">
        <div class="modal_cabecera">
            <div>
                <h2 id="tituloCrearLiga">Crear liga</h2>
                <p>El plan define cuántos equipos, jugadores, torneos y administradores puede tener.</p>
            </div>
            <button type="button" class="modal_cerrar" data-cerrar="modalCrearLiga" aria-label="Cerrar">×</button>
        </div>
        <form class="form_club" method="post" action="/index.php?pagina=enfrentamientos" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"]); ?>">
            <input type="hidden" name="accion" value="crear_liga">
            <input type="hidden" name="contratacion_id" id="ligaContratacion" value="">
            <p class="aviso_contratacion" id="avisoContratacion" hidden></p>
            <div class="form_grilla">
                <div class="campo">
                    <label for="ligaNombre">Nombre de la liga</label>
                    <input id="ligaNombre" name="nombre" type="text" minlength="3" maxlength="60" required placeholder="Ej.: Liga Metropolitana de Fútbol">
                </div>
                <div class="campo">
                    <label for="ligaLogo">Logo <small>(opcional · JPG, PNG o WEBP, máx. 2 MB)</small></label>
                    <input id="ligaLogo" name="logo_liga" type="file" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="campo campo_ancho_grilla">
                    <label for="ligaPortada">Foto de portada <small>(opcional · una foto horizontal de la liga, máx. 2 MB)</small></label>
                    <input id="ligaPortada" name="portada_liga" type="file" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="campo">
                    <label for="ligaDeporte">Deporte</label>
                    <select id="ligaDeporte" name="deporte" required>
                        <?php foreach (deportes_clubes() as $id_deporte => $nombre_deporte): ?>
                            <option value="<?php echo $e($id_deporte); ?>"><?php echo $e($nombre_deporte); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <label for="ligaPlan">Plan contratado</label>
                    <select id="ligaPlan" name="plan" required>
                        <?php foreach (planes_liga() as $id_plan => $plan): ?>
                            <option value="<?php echo $e($id_plan); ?>">
                                <?php echo $e($plan["nombre"] . " · " . $plan["equipos"] . " equipos · " . $plan["torneos"] . " torneo(s)"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <label for="ligaEquiposExtra">Equipos adicionales <small>(USD 10/mes c/u)</small></label>
                    <input id="ligaEquiposExtra" name="equipos_extra" type="number" min="0" max="1000" value="0">
                </div>
                <div class="campo">
                    <label for="ligaJugadoresExtra">Jugadores adicionales <small>(USD 2/mes c/u)</small></label>
                    <input id="ligaJugadoresExtra" name="jugadores_extra" type="number" min="0" max="1000" value="0">
                </div>
            </div>
            <p class="campo_ayuda">Cada plan ya incluye 10 jugadores adicionales gratis. El deporte no se puede cambiar después.</p>
            <div class="form_acciones">
                <button type="button" class="boton_secundario" data-cerrar="modalCrearLiga">Cancelar</button>
                <button type="submit" class="boton_agregar">Crear liga</button>
            </div>
        </form>
    </dialog>

    <!-- historial: planes contratados que ya tienen su liga -->
    <dialog class="modal_equipo modal_historial" id="modalHistorial" aria-labelledby="tituloHistorial">
        <div class="modal_cabecera">
            <div>
                <h2 id="tituloHistorial">Historial de planes contratados</h2>
                <p>Los planes que ya tienen su liga creada.</p>
            </div>
            <button type="button" class="modal_cerrar" data-cerrar="modalHistorial" aria-label="Cerrar">×</button>
        </div>
        <div class="historial_cuerpo">
        <?php if (count($historial_contrataciones) === 0): ?>
            <p class="panel_contrataciones_vacio">Todavía no hay nada en el historial.</p>
        <?php else: ?>
            <div class="buscador historial_buscador">
                <label for="buscarHistorial" class="solo-lector">Buscar en el historial</label>
                <input id="buscarHistorial" type="search" placeholder="Buscar por liga, persona, correo o número de orden..." autocomplete="off">
                <span class="buscador_contador" id="contadorHistorial" role="status"><?php echo count($historial_contrataciones); ?></span>
            </div>
            <ul class="lista_contrataciones lista_historial">
                <?php foreach ($historial_contrataciones as $orden):
                    $busqueda = "#" . $orden["id"] . " " . $orden["organizacion"] . " " . $orden["nombre"] . " " . $orden["correo"] . " "
                        . $orden["ciudad"] . " " . ($orden["nombre_liga"] ?? "") . " " . ($planes_por_id[$orden["plan"]]["nombre"] ?? "");
                ?>
                    <li class="contratacion fila_historial" data-busqueda="<?php echo $e(mb_strtolower($busqueda, "UTF-8")); ?>">
                        <?php $datos_contratacion($orden); ?>
                        <?php if ($orden["nombre_liga"] !== null): ?>
                            <a class="contratacion_lista" href="/index.php?pagina=enfrentamientos&amp;liga=<?php echo (int) $orden["liga_id"]; ?>">Liga creada: <?php echo $e($orden["nombre_liga"]); ?> ›</a>
                        <?php else: ?>
                            <span class="contratacion_lista contratacion_lista_eliminada">Su liga fue eliminada</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="sin_resultados" id="sinHistorial" hidden>No hay nada en el historial que coincida.</p>
        <?php endif; ?>
        </div>
    </dialog>

    <script>
        (function () {
            const modal = document.getElementById("modalCrearLiga");
            const form = modal.querySelector("form");
            const aviso = document.getElementById("avisoContratacion");
            const boton = document.getElementById("abrirContrataciones");
            const panel = document.getElementById("panelContrataciones");

            // + Crear liga: formulario vacio
            document.getElementById("abrirCrearLiga").addEventListener("click", () => {
                form.reset();
                document.getElementById("ligaContratacion").value = "";
                aviso.hidden = true;
                modal.showModal();
            });
            document.querySelectorAll('[data-cerrar="modalCrearLiga"]').forEach(cerrar => {
                cerrar.addEventListener("click", () => modal.close());
            });

            // boton con notificacion: abre y cierra el panel
            const mostrarPanel = abierto => {
                panel.hidden = !abierto;
                boton.setAttribute("aria-expanded", abierto ? "true" : "false");
            };
            boton.addEventListener("click", () => mostrarPanel(panel.hidden));
            document.addEventListener("click", evento => {
                if (!panel.hidden && !evento.target.closest(".contrataciones_menu")) mostrarPanel(false);
            });
            document.addEventListener("keydown", evento => {
                if (evento.key === "Escape" && !panel.hidden) { mostrarPanel(false); boton.focus(); }
            });

            // historial con buscador
            const historial = document.getElementById("modalHistorial");
            document.getElementById("abrirHistorial").addEventListener("click", () => {
                mostrarPanel(false);
                historial.showModal();
            });
            document.querySelectorAll('[data-cerrar="modalHistorial"]').forEach(cerrar => {
                cerrar.addEventListener("click", () => historial.close());
            });
            const buscarHistorial = document.getElementById("buscarHistorial");
            if (buscarHistorial) {
                const sinTildes = texto => texto.toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g, "");
                buscarHistorial.addEventListener("input", () => {
                    const termino = sinTildes(buscarHistorial.value.trim());
                    let visibles = 0;
                    document.querySelectorAll(".fila_historial").forEach(fila => {
                        fila.hidden = !sinTildes(fila.dataset.busqueda).includes(termino);
                        if (!fila.hidden) visibles++;
                    });
                    document.getElementById("contadorHistorial").textContent = visibles;
                    document.getElementById("sinHistorial").hidden = visibles > 0;
                });
            }

            // + Crear su liga: el formulario ya viene completo con lo que contrato
            document.querySelectorAll(".crear_desde_contratacion").forEach(crear => {
                crear.addEventListener("click", () => {
                    const datos = crear.dataset;
                    form.reset();
                    document.getElementById("ligaContratacion").value = datos.contratacion;
                    document.getElementById("ligaNombre").value = datos.nombre;
                    document.getElementById("ligaDeporte").value = datos.deporte;
                    document.getElementById("ligaPlan").value = datos.plan;
                    document.getElementById("ligaEquiposExtra").value = datos.equipos;
                    document.getElementById("ligaJugadoresExtra").value = datos.jugadores;
                    aviso.textContent = "Liga del plan contratado #" + datos.contratacion + ". Al crearla, " + datos.cliente + " queda como administrador (si su cuenta tiene rol Cliente).";
                    aviso.hidden = false;
                    mostrarPanel(false);
                    modal.showModal();
                });
            });
        })();
    </script>
<?php endif; ?>
