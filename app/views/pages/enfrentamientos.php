<?php
// se carga desde app/controllers/torneos.php
$puede_agregar_clubes = $puede_agregar_clubes ?? false;
$puede_editar_clubes = $puede_editar_clubes ?? false;
$puede_usar_form_club = $puede_usar_form_club ?? false;
$error_club = $error_club ?? "";
$club_guardado = $club_guardado ?? "";
$clubes_editables = $clubes_editables ?? [];
$clientes = $clientes ?? [];
$editores = $editores ?? [];
$mensaje_modal = $mensaje_modal ?? "";
$mensaje_modal_ok = $mensaje_modal_ok ?? false;
$participantes_club = $participantes_club ?? [];
$sanciones = $sanciones ?? [];
$confirmar_participante = $confirmar_participante ?? null;
$puede_sancionar = $puede_sancionar ?? false;
$puede_editar_habilidades = $puede_editar_habilidades ?? false;
$partidos_jugados = $partidos_jugados ?? [];
$tipos_sancion = $tipos_sancion ?? [];
$limites_subida = $limites_subida ?? ["total" => 8 * 1024 * 1024, "archivos" => 20];
$ligas = $ligas ?? [];
$equipos_sin_liga = $equipos_sin_liga ?? [];
$contrataciones = $contrataciones ?? [];
$historial_contrataciones = $historial_contrataciones ?? [];
$clubes_eliminables = $clubes_eliminables ?? [];
$vista_publica = $vista_publica ?? true;
$destacados = $destacados ?? [];
/** @var array|null $liga_actual */
$liga_actual = $liga_actual ?? null;
$deportes = $deportes ?? [];
$ligas_que_administro = $ligas_que_administro ?? [];
$puede_gestionar_ligas = $puede_gestionar_ligas ?? false;
$puede_administrar = $puede_administrar ?? false;
$puede_agregar_en_liga = $puede_agregar_en_liga ?? false;
$mensaje_liga = $mensaje_liga ?? "";
$mensaje_liga_ok = $mensaje_liga_ok ?? false;
$torneos = $torneos ?? [];
$torneo_actual = $torneo_actual ?? null;
$partidos = $partidos ?? [];
$tabla = $tabla ?? [];
$administradores_liga = $administradores_liga ?? [];
$pestana = $pestana ?? "equipos";
$planes_liga = $planes_liga ?? [];
$funciones_plan = $funciones_plan ?? [];
$equipos = $equipos ?? [];
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
$viejo = fn($clave) => isset($_POST[$clave]) && is_string($_POST[$clave]) ? $_POST[$clave] : "";
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENIX | Ligas</title>

    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/reglas.css">
    <link rel="stylesheet" href="/public/assets/css/enfrentamiento.css">
    <link rel="stylesheet" href="/public/assets/css/ligas.css">

</head>

<body>

    <!-- menu de arriba -->
    <nav class="barra_navegacion">

        <a href="/index.php?pagina=inicio" class="logo">
            <img src="/public/assets/images/logo a.png" alt="ARENIX">
            <div class="logo-texto">
                <h1>ARE<span>NIX</span></h1>
                <p>Plataforma Deportiva y de Gestión</p>
            </div>
        </a>

        <input type="checkbox" id="botonMenu" class="menu-boton">
        <label for="botonMenu" class="hamburguesa" aria-label="Abrir menu">
            <span></span>
            <span></span>
            <span></span>
        </label>

        <div class="menu">
            <div class="menu-desplegable">
                <a href="/index.php?pagina=enfrentamientos" class="activo">
                    <img src="/public/assets/images/nav-enfrentamientos.png" alt="">
                    <span>LIGAS</span>
                </a>
                <div class="submenu">
                    <a href="/index.php?pagina=reglamentos">Reglamentos</a>
                </div>
            </div>
            <div class="menu-desplegable">
                <a href="/index.php?pagina=resultados">
                    <img src="/public/assets/images/nav-resultados.png" alt="">
                    <span>RESULTADOS</span>
                </a>
                <div class="submenu">
                    <a href="/index.php?pagina=habilidades">Habilidades de jugadores</a>
                </div>
            </div>
            <a href="/index.php?pagina=horarios">
                <img src="/public/assets/images/nav-horarios.png" alt="">
                <span>HORARIOS</span>
            </a>
            <a href="/index.php?pagina=eventos">
                <img src="/public/assets/images/nav-eventos.png" alt="">
                <span>EVENTOS</span>
            </a>
            <a href="/index.php?pagina=noticias">
                <img src="/public/assets/images/nav-noticias.png" alt="">
                <span>NOTICIAS</span>
            </a>
            <a href="/index.php?pagina=planes">
                <img src="/public/assets/images/logo_planes_transparente.png" alt="">
                <span>PLANES</span>
            </a>
        </div>

        <?php require __DIR__ . "/../partials/menu-perfil.php"; ?>

    </nav>


    <main>

        <div class="contenedor">

            <?php if ($mensaje_liga !== ""): ?>
                <p class="aviso_club <?php echo $mensaje_liga_ok ? "aviso_exito" : "aviso_error"; ?>" role="status"><?php echo $e($mensaje_liga); ?></p>
            <?php endif; ?>

            <?php if (!$liga_actual): ?>

                <?php require __DIR__ . "/torneos/ligas.php"; ?>

            <?php else: ?>

            <?php require __DIR__ . "/torneos/liga_cabecera.php"; ?>

            <!-- equipos de la liga -->
            <section class="seccion_equipos pestana_panel" id="panel-equipos" data-pestana="equipos" aria-labelledby="titulo-equipos" <?php echo $pestana === "equipos" ? "" : "hidden"; ?>>

                <div class="seccion_cabecera seccion_cabecera_acciones">
                    <div>
                        <h2 id="titulo-equipos">Equipos de la liga</h2>
                        <p>Tocá el escudo de un equipo para ver sus integrantes, la plantilla titular y sus avisos.</p>
                    </div>
                    <?php if ($puede_agregar_en_liga): ?>
                        <?php if ($liga_actual["uso"]["equipos"] < $liga_actual["limites"]["equipos"]): ?>
                            <button type="button" class="boton_agregar" id="abrirAgregarClub">+ Agregar equipo</button>
                        <?php else: ?>
                            <span class="limite_alcanzado" title="Se pueden sumar equipos adicionales (USD 10/mes) o cambiar a un plan mayor">Límite de equipos del plan alcanzado</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if ($club_guardado === "creado"): ?>
                    <p class="aviso_club aviso_exito" role="status">El equipo se agregó a la liga.</p>
                <?php elseif ($club_guardado === "editado"): ?>
                    <p class="aviso_club aviso_exito" role="status">Los cambios del club se guardaron correctamente.</p>
                <?php endif; ?>

                <div class="buscador">
                    <label for="buscarEquipo" class="solo-lector">Buscar equipo</label>
                    <input id="buscarEquipo" type="search" placeholder="Buscar equipo, ciudad o jugador..." autocomplete="off">
                    <span class="buscador_contador" id="contadorEquipos" role="status"><?php echo count($equipos); ?> equipos</span>
                </div>

                <ul class="lista_equipos" id="listaEquipos">
                    <?php foreach ($equipos as $equipo):
                        $busqueda = $equipo["nombre"] . " " . $equipo["torneo"] . " " . $equipo["ciudad"] . " " . $equipo["entrenador"] . " " . implode(" ", array_column($equipo["jugadores"], "nombre"));
                    ?>
                        <li class="fila_equipo" data-deporte="<?php echo htmlspecialchars($equipo["deporte"], ENT_QUOTES, "UTF-8"); ?>" data-busqueda="<?php echo htmlspecialchars(mb_strtolower($busqueda, "UTF-8"), ENT_QUOTES, "UTF-8"); ?>">
                            <button type="button" class="fila_equipo_boton" data-equipo="<?php echo htmlspecialchars($equipo["id"], ENT_QUOTES, "UTF-8"); ?>">
                                <img class="fila_equipo_logo" src="<?php echo htmlspecialchars($equipo["logo"], ENT_QUOTES, "UTF-8"); ?>" alt="">
                                <span class="fila_equipo_info">
                                    <strong><?php echo htmlspecialchars($equipo["nombre"], ENT_QUOTES, "UTF-8"); ?></strong>
                                    <small><?php echo htmlspecialchars($equipo["nombre_deporte"] . " · " . $equipo["torneo"] . " · " . $equipo["ciudad"], ENT_QUOTES, "UTF-8"); ?></small>
                                </span>
                                <span class="fila_equipo_dato"><?php echo count($equipo["jugadores"]); ?> jugadores</span>
                                <span class="fila_equipo_ver" aria-hidden="true">Ver equipo ›</span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <p class="sin_resultados" id="sinResultados" <?php echo count($equipos) > 0 ? "hidden" : ""; ?>>
                    <?php echo count($equipos) > 0 ? "No se encontró ningún equipo con esa búsqueda." : "Esta liga todavía no tiene equipos."; ?>
                </p>

            </section>

            <?php require __DIR__ . "/torneos/liga_torneos.php"; ?>
            <?php if (!$vista_publica): ?>
                <?php require __DIR__ . "/torneos/liga_plan.php"; ?>
                <?php require __DIR__ . "/torneos/liga_admin.php"; ?>
            <?php endif; ?>

            <?php endif; ?>

        </div>

    </main>


    <!-- ventana del equipo -->
    <dialog class="modal_equipo" id="modalEquipo" aria-labelledby="modalNombre">

        <div class="modal_cabecera">
            <img class="modal_logo" id="modalLogo" src="" alt="">
            <div>
                <h2 id="modalNombre"></h2>
                <p id="modalDetalle"></p>
            </div>
            <!-- acciones del equipo -->
            <div class="modal_acciones">
                <?php if ($puede_editar_clubes): ?>
                    <button type="button" class="boton_secundario boton_editar" id="editarEquipo" hidden>Editar club</button>
                <?php endif; ?>
                <?php if (count($clubes_eliminables) > 0): ?>
                    <button type="button" class="boton_secundario boton_editar boton_eliminar_equipo" id="abrirEliminarEquipo" aria-expanded="false" aria-controls="bloqueEliminarEquipo" hidden>Eliminar equipo</button>
                <?php endif; ?>
            </div>
            <button type="button" class="modal_cerrar" id="cerrarModal" aria-label="Cerrar">×</button>
        </div>

        <?php if (count($clubes_eliminables) > 0): ?>
            <!-- confirmar antes de eliminar el equipo -->
            <div class="eliminar_equipo" id="bloqueEliminarEquipo" hidden>
                <form method="post" action="/index.php?pagina=enfrentamientos" class="form_eliminar_liga">
                    <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"] ?? ""); ?>">
                    <input type="hidden" name="accion" value="eliminar_club">
                    <input type="hidden" name="club_id" value="">
                    <p>Se borran el equipo, sus partidos (calendario y resultados), sus avisos, sus participantes vinculados, el logo y las fotos de los jugadores. <strong>No se puede deshacer.</strong></p>
                    <label for="confirmarEliminarEquipo">Para confirmar, escribe el nombre del equipo: <strong id="nombreEquipoEliminar"></strong></label>
                    <div class="form_linea">
                        <input id="confirmarEliminarEquipo" name="confirmacion" type="text" autocomplete="off" required>
                        <button type="submit" class="boton_peligro" id="botonEliminarEquipo" disabled>Eliminar equipo</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($mensaje_modal !== ""): ?>
            <p class="aviso_club aviso_modal <?php echo $mensaje_modal_ok ? "aviso_exito" : "aviso_error"; ?>" id="avisoModal" role="status"><?php echo $e($mensaje_modal); ?></p>
        <?php endif; ?>

        <div class="modal_cuerpo">

            <section class="modal_bloque">
                <h3>Plantilla titular <span id="modalFormacion"></span></h3>
                <div class="cancha" id="modalCancha"></div>
            </section>

            <section class="modal_bloque">
                <h3>Habilidades del equipo</h3>
                <div id="modalHabilidades"></div>
            </section>

            <section class="modal_bloque modal_bloque_ancho">
                <h3>Integrantes</h3>
                <ul class="integrantes" id="modalIntegrantes"></ul>
            </section>

            <!-- avisos: los ve cualquiera, los carga el organizador -->
            <section class="modal_bloque modal_bloque_ancho bloque_avisos">
                <h3>Avisos disciplinarios
                    <span class="ayuda_bloque">Amarillas y rojas de los jugadores. Se borran solas cuando el club juega los partidos que dura cada una.</span>
                </h3>

                <div class="partidos_jugados">
                    <span>Partidos jugados registrados: <strong id="modalPartidos">0</strong></span>
                    <?php if ($puede_sancionar): ?>
                        <form method="post" action="/index.php?pagina=enfrentamientos" id="formRegistrarPartido">
                            <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"]); ?>">
                            <input type="hidden" name="accion" value="registrar_partido">
                            <input type="hidden" name="club_id" value="">
                            <button type="submit" class="boton_secundario boton_chico">+ Registrar partido jugado</button>
                        </form>
                    <?php endif; ?>
                </div>

                <ul class="lista_avisos" id="modalSanciones"></ul>

                <?php if ($puede_sancionar): ?>
                    <form class="form_aviso" method="post" action="/index.php?pagina=enfrentamientos" id="formAgregarSancion">
                        <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"]); ?>">
                        <input type="hidden" name="accion" value="agregar_sancion">
                        <input type="hidden" name="club_id" value="">
                        <p class="form_subtitulo">Nuevo aviso</p>
                        <div class="form_aviso_campos">
                            <div class="campo">
                                <label for="sancionJugador">Jugador</label>
                                <select id="sancionJugador" name="jugador" required></select>
                            </div>
                            <div class="campo">
                                <label for="sancionTipo">Tipo</label>
                                <select id="sancionTipo" name="tipo" required>
                                    <?php foreach ($tipos_sancion as $id_tipo => $nombre_tipo): ?>
                                        <option value="<?php echo $e($id_tipo); ?>"><?php echo $e($nombre_tipo); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="campo">
                                <label for="sancionPartido">Partido</label>
                                <input id="sancionPartido" name="partido" type="text" maxlength="80" required placeholder="vs Halcones · Fecha 7">
                            </div>
                            <div class="campo">
                                <label for="sancionDuracion" id="sancionDuracionEtiqueta">Vence después de (partidos)</label>
                                <input id="sancionDuracion" name="duracion" type="number" min="1" max="<?php echo MAX_PARTIDOS_SANCION; ?>" value="3" required>
                            </div>
                            <div class="campo">
                                <label for="sancionFecha">Fecha</label>
                                <input id="sancionFecha" name="fecha" type="date" required max="<?php echo date("Y-m-d"); ?>" value="<?php echo date("Y-m-d"); ?>">
                            </div>
                            <div class="campo campo_ancho">
                                <label for="sancionDetalle">Detalle <small>(opcional)</small></label>
                                <input id="sancionDetalle" name="detalle" type="text" maxlength="120" placeholder="Ej.: suspendido para la próxima fecha">
                            </div>
                        </div>
                        <button type="submit" class="boton_agregar boton_chico">Agregar aviso</button>
                    </form>

                    <form method="post" action="/index.php?pagina=enfrentamientos" id="formQuitarSancion" hidden>
                        <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"]); ?>">
                        <input type="hidden" name="accion" value="quitar_sancion">
                        <input type="hidden" name="club_id" value="">
                        <input type="hidden" name="sancion_id" value="">
                    </form>
                <?php endif; ?>
            </section>

            <?php if ($puede_editar_clubes): ?>
                <!-- participantes del club -->
                <section class="modal_bloque modal_bloque_ancho bloque_editores" id="bloqueParticipantes" hidden>
                    <h3>Participantes del club
                        <span class="ayuda_bloque">Las cuentas que juegan en el club. Lo ven en su perfil.</span>
                    </h3>
                    <ul class="lista_editores" id="modalParticipantes"></ul>

                    <?php if ($confirmar_participante): ?>
                        <!-- si el correo es de un cliente sin club se pregunta antes de cambiarle el rol -->
                        <div class="confirmar_rol" id="confirmarRol" data-club="<?php echo $e($confirmar_participante["club_id"]); ?>" role="alertdialog" aria-labelledby="confirmarRolTexto">
                            <p id="confirmarRolTexto">
                                <strong><?php echo $e($confirmar_participante["nombre"]); ?></strong> tiene el rol <strong>Cliente</strong>.
                                ¿Quieres darle el rol <strong>Participante</strong> y unirlo al club?
                                <small>Va a dejar de ser cliente.</small>
                            </p>
                            <form method="post" action="/index.php?pagina=enfrentamientos">
                                <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"]); ?>">
                                <input type="hidden" name="accion" value="agregar_participante">
                                <input type="hidden" name="club_id" value="<?php echo $e($confirmar_participante["club_id"]); ?>">
                                <input type="hidden" name="correo" value="<?php echo $e($confirmar_participante["correo"]); ?>">
                                <input type="hidden" name="confirmar_rol" value="1">
                                <button type="submit" class="boton_agregar boton_chico">Sí, darle el rol y unirlo</button>
                                <button type="button" class="boton_secundario boton_chico" id="cancelarConfirmarRol">Cancelar</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- se escribe el correo (no se muestran las cuentas) -->
                    <form class="form_editor" method="post" action="/index.php?pagina=enfrentamientos" id="formAgregarParticipante">
                        <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"]); ?>">
                        <input type="hidden" name="accion" value="agregar_participante">
                        <input type="hidden" name="club_id" value="">
                        <label for="correoParticipante" class="solo-lector">Correo del participante</label>
                        <input type="email" name="correo" id="correoParticipante" required maxlength="190" autocomplete="off" placeholder="Correo del participante (ej.: jugador@gmail.com)">
                        <button type="submit" class="boton_agregar boton_chico">Agregar al club</button>
                    </form>
                    <p class="campo_ayuda">La persona tiene que tener cuenta en ARENIX.</p>

                    <form method="post" action="/index.php?pagina=enfrentamientos" id="formQuitarParticipante" hidden>
                        <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"]); ?>">
                        <input type="hidden" name="accion" value="quitar_participante">
                        <input type="hidden" name="club_id" value="">
                        <input type="hidden" name="usuario_id" value="">
                    </form>
                </section>
            <?php endif; ?>

            <?php if ($puede_agregar_clubes): ?>
                <!-- clientes que pueden editar este club -->
                <section class="modal_bloque modal_bloque_ancho bloque_editores">
                    <h3>Clientes que pueden editar este club
                        <span class="ayuda_bloque">Clientes que pueden editar este club.</span>
                    </h3>
                    <ul class="lista_editores" id="modalEditores"></ul>

                    <?php if (count($clientes) > 0): ?>
                        <form class="form_editor" method="post" action="/index.php?pagina=enfrentamientos" id="formAsignarEditor">
                            <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"]); ?>">
                            <input type="hidden" name="accion" value="asignar_editor">
                            <input type="hidden" name="club_id" value="">
                            <label for="selectorCliente" class="solo-lector">Cliente</label>
                            <select name="usuario_id" id="selectorCliente" required>
                                <option value="">Elegir cliente…</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo (int) $cliente["id"]; ?>"><?php echo $e($cliente["nombre"] . " · " . $cliente["correo"]); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="boton_agregar boton_chico">Dar permiso</button>
                        </form>
                    <?php else: ?>
                        <p class="campo_ayuda">Todavía no hay cuentas con rol Cliente. Asígnalo desde la página de permisos.</p>
                    <?php endif; ?>

                    <!-- formulario oculto para quitar un cliente -->
                    <form method="post" action="/index.php?pagina=enfrentamientos" id="formQuitarEditor" hidden>
                        <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"]); ?>">
                        <input type="hidden" name="accion" value="quitar_editor">
                        <input type="hidden" name="club_id" value="">
                        <input type="hidden" name="usuario_id" value="">
                    </form>
                </section>
            <?php endif; ?>

        </div>

    </dialog>


    <?php if ($puede_usar_form_club): ?>
    <!-- agregar o editar club (agregar: organizador y admin, editar: tambien el cliente) -->
    <dialog class="modal_equipo modal_form" id="modalAgregarClub" aria-labelledby="tituloAgregarClub">

        <div class="modal_cabecera">
            <div>
                <h2 id="tituloAgregarClub">Agregar club</h2>
                <p id="subtituloAgregarClub">El club aparecerá en la lista de equipos de la liga.</p>
            </div>
            <button type="button" class="modal_cerrar" id="cerrarAgregarClub" aria-label="Cerrar">×</button>
        </div>

        <form class="form_club" method="post" action="/index.php?pagina=enfrentamientos" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_clubes"]); ?>">
            <input type="hidden" name="club_id" id="clubId" value="<?php echo $e($viejo("club_id")); ?>">

            <?php if ($error_club !== ""): ?>
                <p class="aviso_club aviso_error" role="alert"><?php echo $e($error_club); ?></p>
            <?php endif; ?>

            <div class="form_grilla">
                <div class="campo">
                    <label for="clubNombre">Nombre del club</label>
                    <input id="clubNombre" name="nombre" type="text" maxlength="60" required value="<?php echo $e($viejo("nombre")); ?>">
                </div>
                <div class="campo">
                    <label for="clubLogo">Logo <small id="ayudaLogo">(JPG, PNG o WEBP, máx. 2 MB)</small></label>
                    <input id="clubLogo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" required>
                </div>
                <div class="campo">
                    <label for="clubDeporte">Deporte</label>
                    <select id="clubDeporte" name="deporte" required>
                        <?php foreach ($deportes as $id_deporte => $nombre_deporte): ?>
                            <option value="<?php echo $e($id_deporte); ?>" <?php echo $viejo("deporte") === $id_deporte ? "selected" : ""; ?>><?php echo $e($nombre_deporte); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <label for="clubFormacion">Formación</label>
                    <select id="clubFormacion" name="formacion" required data-valor="<?php echo $e($viejo("formacion")); ?>"></select>
                </div>
                <div class="campo">
                    <label for="clubTorneo">Torneo</label>
                    <input id="clubTorneo" name="torneo" type="text" maxlength="60" required list="listaTorneos" value="<?php echo $e($viejo("torneo")); ?>">
                    <datalist id="listaTorneos">
                        <?php foreach (array_unique(array_column($equipos, "torneo")) as $torneo): ?>
                            <option value="<?php echo $e($torneo); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="campo">
                    <label for="clubCiudad">Ciudad</label>
                    <input id="clubCiudad" name="ciudad" type="text" maxlength="60" required value="<?php echo $e($viejo("ciudad")); ?>">
                </div>
                <div class="campo">
                    <label for="clubEntrenador">Entrenador (DT)</label>
                    <input id="clubEntrenador" name="entrenador" type="text" maxlength="60" required value="<?php echo $e($viejo("entrenador")); ?>">
                </div>
                <div class="campo">
                    <span class="campo_etiqueta">Plan</span>
                    <!-- el plan es de la liga (habilidades con enterprise, fotos desde profesional) -->
                    <input type="hidden" id="clubPlan" value="<?php echo plan_incluye($liga_actual["plan"], "estadisticas_avanzadas") ? "pro" : "basico"; ?>">
                    <input type="hidden" name="liga_id" value="<?php echo (int) $liga_actual["id"]; ?>">
                    <p class="campo_fijo"><?php echo $e($liga_actual["nombre_plan"]); ?> <small>de la liga <?php echo $e($liga_actual["nombre"]); ?></small></p>
                </div>
            </div>

            <?php if ($puede_editar_habilidades): ?>
            <!-- habilidades: solo organizador y admin -->
            <fieldset class="form_habilidades" id="clubHabilidades" hidden>
                <legend>Habilidades del equipo <small>(0 a 100)</small></legend>
                <div class="form_habilidades_grilla">
                    <?php for ($i = 0; $i < 6; $i++):
                        $valor_habilidad = is_array($_POST["habilidades"] ?? null) && is_string($_POST["habilidades"][$i] ?? null) ? $_POST["habilidades"][$i] : "";
                    ?>
                        <div class="campo">
                            <label for="clubHabilidad<?php echo $i; ?>"></label>
                            <input id="clubHabilidad<?php echo $i; ?>" name="habilidades[<?php echo $i; ?>]" type="number" min="0" max="100" value="<?php echo $e($valor_habilidad); ?>">
                        </div>
                    <?php endfor; ?>
                </div>
            </fieldset>
            <?php endif; ?>

            <!-- titulares (las filas se arman desde js) -->
            <div class="form_titulares">
                <div class="form_titulares_lista">
                    <p class="form_subtitulo">Titulares <small id="contadorTitulares"></small></p>
                    <p class="campo_ayuda">Cada fila es una posición de la formación. Tocá el círculo para ponerle foto al jugador. El número es opcional: si no lo ponés, se asigna solo.</p>
                    <div id="clubTitularesLista"></div>
                </div>
                <div class="form_titulares_cancha">
                    <p class="form_subtitulo">Así queda en la cancha</p>
                    <div class="cancha cancha_mini" id="clubCanchaPrevia"></div>
                </div>
            </div>

            <!-- suplentes -->
            <div class="form_suplentes">
                <p class="form_subtitulo">Suplentes <small>(opcional, hasta 15)</small></p>
                <div id="clubSuplentesLista"></div>
                <!-- sugerencias con los participantes del club -->
                <datalist id="listaParticipantesClub"></datalist>
                <button type="button" class="boton_secundario boton_chico" id="agregarSuplente">+ Agregar suplente</button>
            </div>

            <div class="form_acciones">
                <button type="button" class="boton_secundario" id="cancelarAgregarClub">Cancelar</button>
                <button type="submit" class="boton_agregar" id="guardarClub">Guardar club</button>
            </div>
        </form>

    </dialog>
    <?php endif; ?>

    <script>
        const EQUIPOS = <?php echo json_encode($equipos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        const buscador = document.getElementById("buscarEquipo");
        const filas = document.querySelectorAll(".fila_equipo");
        const contador = document.getElementById("contadorEquipos");
        const sinResultados = document.getElementById("sinResultados");
        const modal = document.getElementById("modalEquipo");

        // quita tildes para que "aguilas" encuentre "Águilas"
        const normalizar = texto => texto.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

        // buscador de equipos (solo dentro de una liga)
        function filtrar() {
            const termino = normalizar(buscador.value.trim());
            let visibles = 0;

            filas.forEach(fila => {
                const coincide = normalizar(fila.dataset.busqueda).includes(termino);
                fila.hidden = !coincide;
                if (coincide) visibles++;
            });

            contador.textContent = visibles + (visibles === 1 ? " equipo" : " equipos");
            sinResultados.hidden = visibles > 0;
        }

        if (buscador) buscador.addEventListener("input", filtrar);

        const crear = (etiqueta, clase, texto) => {
            const el = document.createElement(etiqueta);
            if (clase) el.className = clase;
            if (texto !== undefined) el.textContent = texto;
            return el;
        };

        let equipoAbierto = null;

        // clubes que puede editar el usuario
        const CLUBES_EDITABLES = <?php echo json_encode(array_values($clubes_editables), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        // vista publica: sin info de planes
        const VISTA_PUBLICA = <?php echo $vista_publica ? "true" : "false"; ?>;
        // clubes que puede eliminar
        const CLUBES_ELIMINABLES = <?php echo json_encode(array_values($clubes_eliminables), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        // el boton de eliminar equipo se habilita solo con el nombre exacto
        const confirmarEquipo = document.getElementById("confirmarEliminarEquipo");
        if (confirmarEquipo) {
            // el boton de la cabecera abre/cierra la confirmacion
            document.getElementById("abrirEliminarEquipo").addEventListener("click", evento => {
                const bloque = document.getElementById("bloqueEliminarEquipo");
                bloque.hidden = !bloque.hidden;
                evento.currentTarget.setAttribute("aria-expanded", bloque.hidden ? "false" : "true");
                if (!bloque.hidden) confirmarEquipo.focus();
            });

            const normalizarNombre = texto => texto.trim().replace(/\s+/g, " ").toLowerCase();
            confirmarEquipo.addEventListener("input", () => {
                document.getElementById("botonEliminarEquipo").disabled =
                    normalizarNombre(confirmarEquipo.value) !== normalizarNombre(document.getElementById("nombreEquipoEliminar").textContent);
            });
        }
        // clientes que pueden editar cada club (solo organizador/admin)
        const EDITORES = <?php echo json_encode((object) $editores, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        // participantes de los clubes que el usuario puede editar
        const PARTICIPANTES_CLUB = <?php echo json_encode((object) $participantes_club, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        // avisos disciplinarios de cada club
        const SANCIONES = <?php echo json_encode((object) $sanciones, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const TIPOS_SANCION = <?php echo json_encode((object) $tipos_sancion, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        const formatearFecha = fecha => fecha.split("-").reverse().join("/");

        // amarillas y expulsiones de un jugador en su club
        function tarjetasDe(equipo, nombre) {
            const avisos = (SANCIONES[equipo.id] || []).filter(a => a.jugador === nombre);
            return {
                amarillas: avisos.filter(a => a.tipo === "amarilla").length,
                expulsado: avisos.some(a => a.tipo !== "amarilla")
            };
        }

        // iconitos de tarjeta roja o amarilla
        function iconosTarjetas(tarjetas, clase) {
            const caja = crear("span", "tarjetas " + clase);
            if (tarjetas.expulsado) {
                const roja = crear("span", "tarjeta tarjeta_roja");
                roja.title = "Expulsado";
                caja.append(roja);
            }
            if (tarjetas.amarillas > 0) {
                const amarilla = crear("span", "tarjeta tarjeta_amarilla", tarjetas.amarillas > 1 ? tarjetas.amarillas : "");
                amarilla.title = tarjetas.amarillas + (tarjetas.amarillas === 1 ? " amarilla" : " amarillas");
                caja.append(amarilla);
            }
            return caja;
        }

        // nombre completo de cada posicion, por deporte ("DC" -> "Delantero centro")
        const POSICIONES = <?php echo json_encode(posiciones_por_deporte(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const nombrePosicion = (deporte, codigo) => (POSICIONES[deporte] || {})[codigo] || codigo;

        const iniciales = nombre => nombre.split(" ").filter(Boolean).slice(0, 2).map(p => p[0]).join("").toUpperCase();

        // foto del jugador (o iniciales, o la posicion si no tiene nombre)
        function avatar(jugador, clase) {
            const circulo = crear("span", "avatar " + clase);
            if (jugador.foto) {
                const img = crear("img");
                img.src = jugador.foto;
                img.alt = "";
                img.loading = "lazy";
                circulo.append(img);
            } else if (jugador.nombre) {
                circulo.textContent = iniciales(jugador.nombre);
            } else {
                circulo.textContent = jugador.posicion;
                circulo.classList.add("avatar_vacio");
            }
            return circulo;
        }

        // dibuja los titulares en la cancha: la primera linea abajo y el resto hacia arriba
        function dibujarCancha(cancha, deporte, lineas, titulares) {
            cancha.className = cancha.className.replace(/\bcancha_(?!mini)\w+/g, "").trim() + " cancha_" + deporte;
            cancha.replaceChildren();
            let indice = 0;

            lineas.forEach(cantidad => {
                const linea = crear("div", "cancha_linea");
                titulares.slice(indice, indice + cantidad).forEach(j => {
                    const jugador = crear("div", "cancha_jugador");
                    const circulo = avatar(j, "cancha_numero");
                    if (j.numero !== "" && j.numero !== null && j.numero !== undefined) {
                        circulo.append(crear("span", "cancha_dorsal", j.numero));
                    }
                    if (j.tarjetas && (j.tarjetas.expulsado || j.tarjetas.amarillas)) {
                        circulo.append(iconosTarjetas(j.tarjetas, "cancha_tarjetas"));
                    }
                    const etiqueta = j.nombre ? (j.nombre.split(" ")[1] || j.nombre) : nombrePosicion(deporte, j.posicion);
                    jugador.append(circulo, crear("span", "cancha_nombre", etiqueta));
                    jugador.title = (j.nombre ? j.nombre + " · " : "") + nombrePosicion(deporte, j.posicion);
                    linea.append(jugador);
                });
                indice += cantidad;
                cancha.append(linea);
            });
        }

        // lista de cuentas con boton para quitarlas
        function mostrarCuentas(equipo, cuentas, ids, textos) {
            const lista = document.getElementById(ids.lista);
            if (!lista) return;

            [ids.formAgregar, ids.formQuitar].forEach(id => {
                const form = document.getElementById(id);
                if (form) form.elements.club_id.value = equipo.id;
            });
            lista.replaceChildren();

            if (cuentas.length === 0) {
                lista.append(crear("li", "lista_editores_vacia", textos.vacio));
            }
            cuentas.forEach(cuenta => {
                const item = crear("li", "editor");
                item.append(crear("span", "editor_nombre", cuenta.nombre), crear("span", "editor_correo", cuenta.correo));

                const quitar = crear("button", "integrante_quitar", "×");
                quitar.type = "button";
                quitar.title = textos.quitar;
                quitar.setAttribute("aria-label", textos.quitar + ": " + cuenta.nombre);
                quitar.addEventListener("click", () => {
                    if (!confirm(textos.confirmar(cuenta.nombre))) return;
                    const form = document.getElementById(ids.formQuitar);
                    form.elements.usuario_id.value = cuenta.id;
                    form.submit();
                });
                item.append(quitar);
                lista.append(item);
            });

            const select = document.getElementById(ids.select);
            if (select) {
                select.value = "";
                [...select.options].forEach(opcion => {
                    opcion.hidden = cuentas.some(c => String(c.id) === opcion.value);
                });
            }
        }

        function mostrarEditores(equipo) {
            mostrarCuentas(equipo, EDITORES[equipo.id] || [],
                { lista: "modalEditores", select: "selectorCliente", formAgregar: "formAsignarEditor", formQuitar: "formQuitarEditor" },
                {
                    vacio: "Ningún cliente puede editar este club todavía.",
                    quitar: "Quitar permiso",
                    confirmar: nombre => "¿Quitarle a " + nombre + " el permiso para editar " + equipo.nombre + "?"
                });
        }

        function mostrarParticipantes(equipo, editable) {
            const bloque = document.getElementById("bloqueParticipantes");
            if (!bloque) return;
            bloque.hidden = !editable;
            if (!editable) return;

            mostrarCuentas(equipo, PARTICIPANTES_CLUB[equipo.id] || [],
                { lista: "modalParticipantes", formAgregar: "formAgregarParticipante", formQuitar: "formQuitarParticipante" },
                {
                    vacio: "Este club todavía no tiene participantes vinculados.",
                    quitar: "Quitar del club",
                    confirmar: nombre => "¿Quitar a " + nombre + " de " + equipo.nombre + "?"
                });
        }

        // avisos: la lista la ve cualquiera, el formulario solo el organizador
        const PUEDE_SANCIONAR = <?php echo $puede_sancionar ? "true" : "false"; ?>;
        const PARTIDOS_JUGADOS = <?php echo json_encode((object) $partidos_jugados, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const plural = (n, uno, varios) => n + " " + (n === 1 ? uno : varios);

        // texto de cuanto le falta al aviso
        function textoDuracion(aviso) {
            if (aviso.tipo === "amarilla") {
                return "Vence en " + plural(aviso.restantes, "partido", "partidos");
            }
            return "Suspendido: " + (aviso.restantes === 1 ? "falta 1 partido" : "faltan " + aviso.restantes + " partidos") + " de " + aviso.duracion;
        }

        // en el formulario: la etiqueta y el valor sugerido de la duracion cambian segun el tipo
        const DURACION_SUGERIDA = { amarilla: 3, doble_amarilla: 1, roja: 2 };
        const tipoSancion = document.getElementById("sancionTipo");
        if (tipoSancion) {
            tipoSancion.addEventListener("change", () => {
                const amarilla = tipoSancion.value === "amarilla";
                document.getElementById("sancionDuracionEtiqueta").textContent = amarilla ? "Vence después de (partidos)" : "Partidos de suspensión";
                document.getElementById("sancionDuracion").value = DURACION_SUGERIDA[tipoSancion.value] || 1;
            });
        }

        const formularioPartido = document.getElementById("formRegistrarPartido");
        if (formularioPartido) {
            formularioPartido.addEventListener("submit", evento => {
                const equipo = equipoAbierto;
                const vencen = (SANCIONES[equipo.id] || []).filter(a => a.restantes === 1).length;
                const aviso = vencen === 1 ? "\n\nSe va a eliminar 1 aviso que cumple su último partido."
                    : vencen > 1 ? "\n\nSe van a eliminar " + vencen + " avisos que cumplen su último partido." : "";
                if (!confirm("¿Registrar un partido jugado de " + equipo.nombre + "?" + aviso)) evento.preventDefault();
            });
        }

        function mostrarSanciones(equipo) {
            const editable = PUEDE_SANCIONAR;
            const lista = document.getElementById("modalSanciones");
            document.getElementById("modalPartidos").textContent = PARTIDOS_JUGADOS[equipo.id] || 0;
            if (formularioPartido) formularioPartido.elements.club_id.value = equipo.id;
            const avisos = SANCIONES[equipo.id] || [];
            lista.replaceChildren();

            if (avisos.length === 0) {
                lista.append(crear("li", "lista_editores_vacia", "Sin avisos: ningún jugador tiene amarillas ni expulsiones."));
            }
            avisos.forEach(aviso => {
                const expulsion = aviso.tipo !== "amarilla";
                const item = crear("li", "aviso_disciplina " + (expulsion ? "aviso_roja" : "aviso_amarilla"));
                const texto = crear("span", "aviso_texto");
                const titulo = crear("span", "aviso_titulo");
                titulo.append(crear("strong", "", aviso.jugador), " · " + (TIPOS_SANCION[aviso.tipo] || aviso.tipo));
                texto.append(
                    titulo,
                    crear("span", "aviso_partido", aviso.partido + " · " + formatearFecha(aviso.fecha)),
                    crear("span", "aviso_duracion " + (aviso.restantes === 1 ? "aviso_ultimo" : ""), textoDuracion(aviso))
                );
                if (aviso.detalle) texto.append(crear("span", "aviso_detalle", aviso.detalle));

                item.append(crear("span", "tarjeta " + (expulsion ? "tarjeta_roja" : "tarjeta_amarilla")), texto);

                if (editable) {
                    const quitar = crear("button", "integrante_quitar", "×");
                    quitar.type = "button";
                    quitar.title = "Quitar aviso";
                    quitar.setAttribute("aria-label", "Quitar el aviso de " + aviso.jugador);
                    quitar.addEventListener("click", () => {
                        if (!confirm("¿Quitar el aviso de " + aviso.jugador + " (" + aviso.partido + ")?")) return;
                        const form = document.getElementById("formQuitarSancion");
                        form.elements.club_id.value = equipo.id;
                        form.elements.sancion_id.value = aviso.id;
                        form.submit();
                    });
                    item.append(quitar);
                }
                lista.append(item);
            });

            const form = document.getElementById("formAgregarSancion");
            if (!form) return;
            form.hidden = !editable;
            form.elements.club_id.value = equipo.id;
            const select = document.getElementById("sancionJugador");
            select.replaceChildren(...equipo.jugadores.map(j => new Option(j.numero + " · " + j.nombre, j.nombre)));
        }

        function abrirEquipo(id) {
            const equipo = EQUIPOS.find(e => e.id === id);
            if (!equipo) return;
            equipoAbierto = equipo;

            document.getElementById("modalLogo").src = equipo.logo;
            document.getElementById("modalNombre").textContent = equipo.nombre;
            document.getElementById("modalDetalle").textContent = equipo.nombre_deporte + " · " + equipo.torneo + " · " + equipo.ciudad + " · DT: " + equipo.entrenador;

            const titulares = equipo.jugadores.filter(j => j.titular);
            document.getElementById("modalFormacion").textContent = equipo.formacion + " · " + titulares.length + " jugadores";

            dibujarCancha(document.getElementById("modalCancha"), equipo.deporte, equipo.lineas,
                titulares.map(j => Object.assign({}, j, { tarjetas: tarjetasDe(equipo, j.nombre) })));

            // habilidades: solo si el equipo tiene el plan Pro
            const habilidades = document.getElementById("modalHabilidades");
            habilidades.replaceChildren();

            // en la vista publica si no hay habilidades no se muestra el bloque
            habilidades.closest(".modal_bloque").hidden = VISTA_PUBLICA && equipo.plan !== "pro";

            if (equipo.plan === "pro" && Object.keys(equipo.habilidades).length === 0) {
                // las carga el organizador o el admin
                habilidades.append(crear("p", "campo_ayuda", "Todavía no se cargaron las habilidades de este equipo."));
            } else if (equipo.plan === "pro") {
                const valores = Object.values(equipo.habilidades);
                const promedio = Math.round(valores.reduce((a, b) => a + b, 0) / valores.length);
                const media = crear("p", "habilidades_media");
                media.append(crear("strong", "", promedio), " de media general");
                habilidades.append(media);

                Object.entries(equipo.habilidades).forEach(([nombre, valor]) => {
                    const fila = crear("div", "habilidad");
                    const barra = crear("div", "habilidad_barra");
                    const relleno = crear("span");
                    relleno.style.width = valor + "%";
                    barra.append(relleno);
                    fila.append(crear("span", "habilidad_nombre", nombre), barra, crear("span", "habilidad_valor", valor));
                    habilidades.append(fila);
                });
            } else {
                const bloqueo = crear("div", "habilidades_bloqueadas");
                bloqueo.append(
                    crear("strong", "", "Habilidades no disponibles"),
                    crear("p", "", "Las estadísticas avanzadas (habilidades del equipo, medidas con Veo Analytics) están incluidas en el Plan Enterprise de la liga.")
                );
                habilidades.append(bloqueo);
            }

            // integrantes: titulares y suplentes
            const integrantes = document.getElementById("modalIntegrantes");
            integrantes.replaceChildren();
            equipo.jugadores.forEach(j => {
                const item = crear("li", j.titular ? "" : "suplente");
                const posicion = crear("span", "integrante_pos", j.posicion);
                posicion.title = nombrePosicion(equipo.deporte, j.posicion);

                const nombre = crear("span", "integrante_nombre", j.nombre);
                nombre.append(iconosTarjetas(tarjetasDe(equipo, j.nombre), "integrante_tarjetas"));

                item.append(
                    avatar(j, "integrante_foto"),
                    crear("span", "integrante_numero", j.numero),
                    nombre,
                    posicion,
                    crear("span", "integrante_rol", j.titular ? "Titular" : "Suplente")
                );
                integrantes.append(item);
            });

            // editar club, avisos y participantes: solo si puede editar
            const editable = CLUBES_EDITABLES.includes(equipo.id);
            const botonEditarClub = document.getElementById("editarEquipo");
            if (botonEditarClub) botonEditarClub.hidden = !editable;

            // eliminar equipo: organizador, admin o cliente de la liga
            const bloqueEliminar = document.getElementById("bloqueEliminarEquipo");
            if (bloqueEliminar) {
                const botonAbrirEliminar = document.getElementById("abrirEliminarEquipo");
                botonAbrirEliminar.hidden = !CLUBES_ELIMINABLES.includes(equipo.id);
                botonAbrirEliminar.setAttribute("aria-expanded", "false");
                bloqueEliminar.hidden = true;
                bloqueEliminar.querySelector('[name="club_id"]').value = equipo.id;
                document.getElementById("nombreEquipoEliminar").textContent = equipo.nombre;
                document.getElementById("confirmarEliminarEquipo").value = "";
                document.getElementById("botonEliminarEquipo").disabled = true;
            }

            mostrarSanciones(equipo);
            mostrarParticipantes(equipo, editable);
            mostrarEditores(equipo);
            modal.showModal();
        }

        document.querySelectorAll(".fila_equipo_boton").forEach(boton => {
            boton.addEventListener("click", () => abrirEquipo(boton.dataset.equipo));
        });

        document.getElementById("cerrarModal").addEventListener("click", () => modal.close());

        // despues de una accion se vuelve a abrir el mismo equipo (?ver=id)
        const equipoParaVer = new URLSearchParams(location.search).get("ver");
        if (equipoParaVer) {
            abrirEquipo(equipoParaVer);
            // la direccion queda en la liga del equipo, sin ?ver=
            const sinVer = new URL(location.href);
            sinVer.searchParams.delete("ver");
            <?php if ($liga_actual): ?>
                sinVer.searchParams.set("liga", "<?php echo (int) $liga_actual["id"]; ?>");
            <?php endif; ?>
            history.replaceState(null, "", sinVer);
        }

        // el aviso y la pregunta del rol se muestran una sola vez
        const confirmarRol = document.getElementById("confirmarRol");
        modal.addEventListener("close", () => {
            ["avisoModal", "confirmarRol"].forEach(id => {
                const elemento = document.getElementById(id);
                if (elemento) elemento.remove();
            });
        });
        if (confirmarRol) {
            confirmarRol.hidden = !equipoAbierto || equipoAbierto.id !== confirmarRol.dataset.club;
            document.getElementById("cancelarConfirmarRol").addEventListener("click", () => confirmarRol.remove());
        }

        // cerrar al tocar fuera de la ventana
        modal.addEventListener("click", e => {
            if (e.target === modal) modal.close();
        });
    </script>

    <?php if ($puede_usar_form_club):
        // si hubo error se devuelven los jugadores cargados
        $filas_enviadas = function ($clave) {
            $filas = [];
            foreach (is_array($_POST[$clave] ?? null) ? $_POST[$clave] : [] as $fila) {
                if (is_array($fila)) {
                    $limpia = array_map(fn($valor) => is_string($valor) ? $valor : "", array_intersect_key($fila, array_flip(["numero", "nombre", "posicion", "foto"])));
                    // la foto anterior solo si tiene la forma de una foto subida
                    if (!preg_match('#^/public/assets/uploads/jugadores/[a-f0-9]{32}\.(jpg|png|webp)$#', $limpia["foto"] ?? "")) {
                        $limpia["foto"] = "";
                    }
                    $filas[] = $limpia;
                }
            }
            return $filas;
        };
    ?>
    <script>
        const CONFIG_CLUBES = {
            formaciones: <?php echo json_encode(formaciones_por_deporte(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            habilidades: <?php echo json_encode(habilidades_por_deporte(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
        };
        const JUGADORES_ENVIADOS = {
            titulares: <?php echo json_encode($filas_enviadas("titulares"), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            suplentes: <?php echo json_encode($filas_enviadas("suplentes"), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
        };
        const MAX_SUPLENTES = 15;
        const MAX_FOTO = 2 * 1024 * 1024;
        const LIMITES_SUBIDA = <?php echo json_encode($limites_subida); ?>;

        const modalClub = document.getElementById("modalAgregarClub");
        const selectorDeporte = document.getElementById("clubDeporte");
        const selectorFormacion = document.getElementById("clubFormacion");
        const selectorPlan = document.getElementById("clubPlan");
        const bloqueHabilidades = document.getElementById("clubHabilidades");
        const listaTitulares = document.getElementById("clubTitularesLista");
        const listaSuplentes = document.getElementById("clubSuplentesLista");
        const canchaPrevia = document.getElementById("clubCanchaPrevia");
        const contadorTitulares = document.getElementById("contadorTitulares");
        const botonSuplente = document.getElementById("agregarSuplente");

        const formacionActual = () => CONFIG_CLUBES.formaciones[selectorDeporte.value][selectorFormacion.value];
        let filaResaltada = -1;
        let contadorFilasSuplentes = 0;

        function campoJugador(nombre, tipo, valor, placeholder) {
            const input = crear("input");
            input.type = tipo;
            input.name = nombre;
            input.value = valor || "";
            input.placeholder = placeholder;
            if (tipo === "number") {
                input.min = 1;
                input.max = 99;
                input.className = "campo_numero";
            } else {
                input.maxLength = 60;
                input.autocomplete = "off";
                input.setAttribute("list", "listaParticipantesClub");
            }
            return input;
        }

        // circulito para elegir la foto del jugador
        function controlFoto(nombreArchivo, nombreOculto, fotoActual, archivoAnterior, alCambiar) {
            const caja = crear("span", "foto_jugador");
            const oculto = crear("input");
            oculto.type = "hidden";
            oculto.name = nombreOculto;
            oculto.value = fotoActual || "";

            const archivo = crear("input");
            archivo.type = "file";
            archivo.name = nombreArchivo;
            archivo.accept = "image/jpeg,image/png,image/webp";
            archivo.className = "foto_campo";
            if (archivoAnterior && archivoAnterior.files.length) {
                const transferencia = new DataTransfer();
                transferencia.items.add(archivoAnterior.files[0]);
                archivo.files = transferencia.files;
            }

            const boton = crear("label", "foto_boton");
            boton.title = "Elegir foto del jugador";
            const vista = crear("span", "foto_vista");
            boton.append(archivo, vista);

            const quitar = crear("button", "foto_quitar", "×");
            quitar.type = "button";
            quitar.title = "Quitar foto";
            quitar.setAttribute("aria-label", "Quitar foto");

            let urlNueva = null;
            const url = () => {
                if (archivo.files.length) {
                    urlNueva = urlNueva || URL.createObjectURL(archivo.files[0]);
                    return urlNueva;
                }
                return oculto.value;
            };

            function pintar() {
                vista.replaceChildren();
                if (url()) {
                    const img = crear("img");
                    img.src = url();
                    img.alt = "";
                    vista.append(img);
                } else {
                    vista.textContent = "+ foto";
                }
                quitar.hidden = !url();
            }

            archivo.addEventListener("change", () => {
                if (archivo.files[0] && archivo.files[0].size > MAX_FOTO) {
                    alert("La foto no puede superar los 2 MB.");
                    archivo.value = "";
                }
                urlNueva = null;
                pintar();
                if (alCambiar) alCambiar();
            });
            quitar.addEventListener("click", () => {
                archivo.value = "";
                oculto.value = "";
                urlNueva = null;
                pintar();
                if (alCambiar) alCambiar();
            });

            caja.append(boton, oculto, quitar);
            pintar();
            return { caja, oculto, archivo, url };
        }

        // lo que ya esta escrito en las filas de titulares (para no perderlo al cambiar de formacion)
        const titularesEscritos = () => [...listaTitulares.querySelectorAll(".fila_jugador")].map(fila => ({
            numero: fila.querySelector(".campo_numero").value,
            nombre: fila.querySelector("input[type=text]").value,
            foto: fila.controlFoto.oculto.value,
            archivo: fila.controlFoto.archivo,
            url: fila.controlFoto.url()
        }));

        // una fila por posicion de la formacion
        function armarTitulares(valores) {
            const deporte = selectorDeporte.value;
            listaTitulares.replaceChildren();

            formacionActual().posiciones.forEach((codigo, i) => {
                const fila = crear("div", "fila_jugador");
                const etiqueta = crear("span", "fila_posicion");
                etiqueta.append(crear("strong", "", codigo), crear("span", "", nombrePosicion(deporte, codigo)));

                const numero = campoJugador("titulares[" + i + "][numero]", "number", valores[i] && valores[i].numero, "Nº");
                const nombre = campoJugador("titulares[" + i + "][nombre]", "text", valores[i] && valores[i].nombre, "Nombre y apellido");
                nombre.required = true;
                numero.setAttribute("aria-label", "Número del " + nombrePosicion(deporte, codigo));
                nombre.setAttribute("aria-label", "Nombre del " + nombrePosicion(deporte, codigo));

                // al escribir en una fila, se marca en la cancha la posicion donde va el jugador
                [numero, nombre].forEach(input => {
                    input.addEventListener("focus", () => resaltar(i));
                    input.addEventListener("input", actualizarPrevia);
                });

                const valor = valores[i] || {};
                fila.controlFoto = controlFoto("foto_titular[" + i + "]", "titulares[" + i + "][foto]", valor.foto, valor.archivo, actualizarPrevia);
                fila.controlFoto.archivo.addEventListener("focus", () => resaltar(i));

                fila.append(etiqueta, fila.controlFoto.caja, numero, nombre);
                listaTitulares.append(fila);
            });

            actualizarPrevia();
        }

        function resaltar(indice) {
            filaResaltada = indice;
            listaTitulares.querySelectorAll(".fila_jugador").forEach((fila, i) => fila.classList.toggle("fila_activa", i === indice));
            canchaPrevia.querySelectorAll(".cancha_jugador").forEach((jugador, i) => jugador.classList.toggle("cancha_resaltado", i === indice));
        }

        function actualizarPrevia() {
            const formacion = formacionActual();
            const escritos = titularesEscritos();
            const titulares = formacion.posiciones.map((codigo, i) => ({
                posicion: codigo,
                nombre: (escritos[i] && escritos[i].nombre.trim()) || "",
                numero: (escritos[i] && escritos[i].numero) || "",
                foto: (escritos[i] && escritos[i].url) || ""
            }));

            dibujarCancha(canchaPrevia, selectorDeporte.value, formacion.lineas, titulares);
            resaltar(filaResaltada);

            const completos = titulares.filter(j => j.nombre !== "").length;
            contadorTitulares.textContent = "(" + completos + " de " + titulares.length + " completos)";
            contadorTitulares.classList.toggle("campo_ayuda_ok", completos === titulares.length);
        }

        // opciones de posicion para un suplente segun el deporte
        function opcionesPosicion(select, elegida) {
            const posiciones = POSICIONES[selectorDeporte.value];
            const valor = elegida in posiciones ? elegida : "SUP";
            select.replaceChildren(...Object.entries(posiciones).map(([codigo, nombre]) =>
                new Option(codigo === "SUP" ? "Sin posición fija" : codigo + " · " + nombre, codigo, false, codigo === valor)
            ));
        }

        function agregarSuplente(jugador) {
            if (listaSuplentes.children.length >= MAX_SUPLENTES) return;
            const clave = contadorFilasSuplentes++;
            const fila = crear("div", "fila_jugador fila_suplente");

            const posicion = crear("select");
            posicion.name = "suplentes[" + clave + "][posicion]";
            posicion.className = "selector_posicion";
            posicion.setAttribute("aria-label", "Posición del suplente");
            opcionesPosicion(posicion, jugador && jugador.posicion);

            const quitar = crear("button", "integrante_quitar", "×");
            quitar.type = "button";
            quitar.title = "Quitar suplente";
            quitar.setAttribute("aria-label", "Quitar suplente");
            quitar.addEventListener("click", () => {
                fila.remove();
                actualizarBotonSuplente();
            });

            fila.controlFoto = controlFoto("foto_suplente[" + clave + "]", "suplentes[" + clave + "][foto]", jugador && jugador.foto);

            fila.append(
                fila.controlFoto.caja,
                posicion,
                campoJugador("suplentes[" + clave + "][numero]", "number", jugador && jugador.numero, "Nº"),
                campoJugador("suplentes[" + clave + "][nombre]", "text", jugador && jugador.nombre, "Nombre y apellido"),
                quitar
            );
            listaSuplentes.append(fila);
            actualizarBotonSuplente();
            return fila;
        }

        function actualizarBotonSuplente() {
            botonSuplente.disabled = listaSuplentes.children.length >= MAX_SUPLENTES;
        }

        function ponerSuplentes(jugadores) {
            listaSuplentes.replaceChildren();
            jugadores.forEach(agregarSuplente);
            actualizarBotonSuplente();
        }

        // cambia formaciones, habilidades, posiciones y titulares segun el deporte
        function actualizarDeporte(formacionElegida, titulares) {
            const deporte = selectorDeporte.value;
            const anterior = typeof formacionElegida === "string" && formacionElegida !== ""
                ? formacionElegida
                : selectorFormacion.value || selectorFormacion.dataset.valor;
            const formaciones = Object.keys(CONFIG_CLUBES.formaciones[deporte]);

            selectorFormacion.replaceChildren(...formaciones.map(f => new Option(f, f, false, f === anterior)));
            selectorFormacion.disabled = formaciones.length === 1;

            if (bloqueHabilidades) {
                CONFIG_CLUBES.habilidades[deporte].forEach((nombre, i) => {
                    document.querySelector('label[for="clubHabilidad' + i + '"]').textContent = nombre;
                });
            }

            listaSuplentes.querySelectorAll(".selector_posicion").forEach(select => opcionesPosicion(select, select.value));
            filaResaltada = -1;
            armarTitulares(titulares || titularesEscritos());
        }

        function actualizarPlan() {
            if (!bloqueHabilidades) return;
            const esPro = selectorPlan.value === "pro";
            bloqueHabilidades.hidden = !esPro;
            bloqueHabilidades.querySelectorAll("input").forEach(input => input.required = esPro);
        }

        selectorDeporte.addEventListener("change", () => actualizarDeporte());
        selectorFormacion.addEventListener("change", () => {
            filaResaltada = -1;
            armarTitulares(titularesEscritos());
        });
        selectorPlan.addEventListener("change", actualizarPlan);
        botonSuplente.addEventListener("click", () => {
            const fila = agregarSuplente(null);
            if (fila) fila.querySelector("input[type=text]").focus();
        });

        // un select deshabilitado no se manda, se habilita antes de enviar
        modalClub.querySelector("form").addEventListener("submit", evento => {
            // antes de mandar: que las fotos no superen los limites de subida del servidor
            const archivos = [...modalClub.querySelectorAll("input[type=file]")].filter(input => input.files.length).map(input => input.files[0]);
            const total = archivos.reduce((suma, archivo) => suma + archivo.size, 0);
            if (archivos.length > LIMITES_SUBIDA.archivos) {
                evento.preventDefault();
                alert("Puedes subir hasta " + LIMITES_SUBIDA.archivos + " imágenes por vez (logo + fotos). Guarda y sube el resto después.");
                return;
            }
            if (total > LIMITES_SUBIDA.total * 0.95) {
                evento.preventDefault();
                alert("Las imágenes pesan demasiado en total (" + Math.round(total / 1048576) + " MB). Guarda con menos fotos y sube el resto después.");
                return;
            }
            selectorFormacion.disabled = false;
        });

        // titulo, logo obligatorio y club_id segun si se agrega o se edita
        function ponerModo(equipo) {
            const editando = Boolean(equipo);
            document.getElementById("clubId").value = editando ? equipo.id : "";
            document.getElementById("tituloAgregarClub").textContent = editando ? "Editar club" : "Agregar club";
            document.getElementById("subtituloAgregarClub").textContent = editando
                ? "Editando " + equipo.nombre + ". Los cambios se verán en la lista de equipos."
                : "El club aparecerá en la lista de equipos de la liga.";
            document.getElementById("guardarClub").textContent = editando ? "Guardar cambios" : "Guardar club";
            document.getElementById("clubLogo").required = !editando;
            document.getElementById("ayudaLogo").textContent = editando
                ? "(opcional: dejalo vacío para mantener el actual)"
                : "(JPG, PNG o WEBP, máx. 2 MB)";
        }

        // abre el formulario vacio (agregar) o completo con los datos del club (editar)
        function abrirFormulario(equipo) {
            const valor = (id, texto) => document.getElementById(id).value = texto;

            const sugerencias = document.getElementById("listaParticipantesClub");
            sugerencias.replaceChildren(...((equipo && PARTICIPANTES_CLUB[equipo.id]) || []).map(p => new Option(p.nombre)));

            valor("clubNombre", equipo ? equipo.nombre : "");
            valor("clubTorneo", equipo ? equipo.torneo : "");
            valor("clubCiudad", equipo ? equipo.ciudad : "");
            valor("clubEntrenador", equipo ? equipo.entrenador : "");
            valor("clubLogo", "");
            // dentro de una liga el unico deporte posible es el de la liga
            selectorDeporte.value = equipo ? equipo.deporte : selectorDeporte.options[0].value;

            ponerSuplentes(equipo ? equipo.jugadores.filter(j => !j.titular) : []);
            actualizarDeporte(equipo ? equipo.formacion : Object.keys(CONFIG_CLUBES.formaciones[selectorDeporte.value])[0],
                equipo ? equipo.jugadores.filter(j => j.titular) : []);

            if (bloqueHabilidades) {
                CONFIG_CLUBES.habilidades[selectorDeporte.value].forEach((nombre, i) => {
                    valor("clubHabilidad" + i, equipo && equipo.habilidades[nombre] !== undefined ? equipo.habilidades[nombre] : "");
                });
            }
            actualizarPlan();

            const aviso = modalClub.querySelector(".aviso_error");
            if (aviso) aviso.remove();

            ponerModo(equipo);
            modalClub.showModal();
        }

        // estado inicial (si hubo un error, con lo que se habia mandado)
        ponerSuplentes(JUGADORES_ENVIADOS.suplentes);
        actualizarDeporte(undefined, JUGADORES_ENVIADOS.titulares);
        actualizarPlan();

        const cerrarClub = () => modalClub.close();
        const botonAgregar = document.getElementById("abrirAgregarClub");
        if (botonAgregar) botonAgregar.addEventListener("click", () => abrirFormulario(null));
        document.getElementById("cerrarAgregarClub").addEventListener("click", cerrarClub);
        document.getElementById("cancelarAgregarClub").addEventListener("click", cerrarClub);

        const botonEditar = document.getElementById("editarEquipo");
        if (botonEditar) {
            botonEditar.addEventListener("click", () => {
                modal.close();
                abrirFormulario(equipoAbierto);
            });
        }

        // desde el perfil: ?editar=id abre el formulario de ese club
        const clubParaEditar = new URLSearchParams(location.search).get("editar");
        if (clubParaEditar && CLUBES_EDITABLES.includes(clubParaEditar)) {
            const equipoEditar = EQUIPOS.find(e => e.id === clubParaEditar);
            if (equipoEditar) abrirFormulario(equipoEditar);
            const sinEditar = new URL(location.href);
            sinEditar.searchParams.delete("editar");
            history.replaceState(null, "", sinEditar);
        }

        <?php if ($error_club !== ""): ?>
            // si hubo error se abre de nuevo el formulario con lo que habia
            const clubConError = EQUIPOS.find(e => e.id === document.getElementById("clubId").value) || null;
            ponerModo(clubConError);
            modalClub.showModal();
        <?php endif; ?>
    </script>
    <?php endif; ?>


    <!-- pie de pagina -->
    <footer>
        <div class="pie">
            <div class="pie_izquierda">
                <img src="/public/assets/images/logo web.png" alt="logo" class="pie_logo">
            </div>

            <div class="pie_enlaces">
                <a href="/public/assets/docs/Términos y Condiciones de Uso.pdf">Términos y Condiciones</a>
                <a href="/public/assets/docs/Documento sin título.pdf">Política de Privacidad</a>
                <a href="/index.php?pagina=contacto">Contacto</a>
            </div>

            <div class="pie_redes">
                <span>Síguenos</span>
                <a href="#"><img src="/public/assets/images/fa.png" alt="Facebook"></a>
                <a href="#"><img src="/public/assets/images/in.png" alt="Instagram"></a>
                <a href="#"><img src="/public/assets/images/xxx.png" alt="X"></a>
            </div>
        </div>

        <p class="pie_derechos">© 2026 ARENIX · Plataforma Deportiva y de Gestión</p>
    </footer>

</body>
</html>



