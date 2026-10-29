<?php
// variables que vienen de controllers/horarios.php (para que el editor no marque error)
/** @var string $mensaje */
/** @var string $tipo_mensaje */
/** @var bool $formulario_con_error */
/** @var bool $puede_agregar */
/** @var array $ligas_formulario */
/** @var array $horarios_por_deporte */
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENIX | Horarios</title>

    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/reglas.css">
    <link rel="stylesheet" href="/public/assets/css/resultados.css">
    <link rel="stylesheet" href="/public/assets/css/horarios.css">
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
                <a href="/index.php?pagina=enfrentamientos">
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
            <a href="/index.php?pagina=horarios" class="activo">
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

    <!-- contenido -->
    <main class="resultados-pagina">
        <?php if ($mensaje !== ""): ?>
            <p class="resultados-mensaje resultados-<?php echo $tipo_mensaje; ?>">
                <?php echo $e($mensaje); ?>
                <?php if ($enlace_calendario): ?>
                    <a href="<?php echo $e($enlace_calendario); ?>">Ver el calendario de la liga</a>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <div class="resultados-barra horarios-barra">
            <label class="resultados-buscador">
                <span class="resultados-oculto">Buscar partidos</span>
                <input type="search" id="buscarHorarios" placeholder="Buscar equipo, liga, torneo o lugar" autocomplete="off">
            </label>

            <label class="horarios-filtro">
                <span class="resultados-oculto">Deporte</span>
                <select id="filtroDeporte">
                    <option value="todos">Todos los deportes</option>
                    <?php foreach ($horarios_por_deporte as $deporte): ?>
                        <option value="<?php echo $e($deporte["id"]); ?>"><?php echo $e($deporte["nombre"]); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="horarios-filtro">
                <span class="resultados-oculto">Cuándo</span>
                <select id="filtroPeriodo">
                    <option value="todos">Todas las fechas</option>
                    <option value="hoy">Hoy</option>
                    <option value="semana">Próximos 7 días</option>
                    <option value="mes">Próximos 30 días</option>
                    <option value="pendientes">Sin resultado cargado</option>
                </select>
            </label>

            <?php if ($puede_agregar): ?>
                <button type="button" class="resultados-agregar" id="abrirNuevoHorario">+ Agregar partido</button>
            <?php endif; ?>
        </div>

        <?php if ($puede_agregar): ?>
            <?php $previo = $formulario_con_error ? $_POST : []; ?>
            <dialog class="resultado-formulario" id="nuevoHorario" <?php echo $formulario_con_error ? "data-abrir" : ""; ?>>
                <?php if (count($ligas_formulario) === 0): ?>
                    <div class="resultado-formulario-cuerpo">
                        <h3>Nuevo partido</h3>
                        <p class="resultado-formulario-aviso">No hay ligas con un torneo activo y al menos dos equipos. Crealos desde la página de ligas.</p>
                        <div class="resultado-formulario-botones">
                            <button type="button" class="resultado-cancelar" data-cerrar-horario>Cerrar</button>
                        </div>
                    </div>
                <?php else: ?>
                    <form class="resultado-formulario-cuerpo" method="post" action="/index.php?pagina=horarios">
                        <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_horarios"]); ?>">

                        <h3>Nuevo partido</h3>

                        <label>
                            Liga y torneo
                            <select name="torneo_id" required data-elegir-torneo>
                                <?php foreach ($ligas_formulario as $liga): ?>
                                    <optgroup label="<?php echo $e($liga["nombre"] . " · " . $liga["deporte"]); ?>">
                                        <?php foreach ($liga["torneos"] as $torneo): ?>
                                            <option value="<?php echo $torneo["id"]; ?>" data-liga="<?php echo $liga["id"]; ?>" <?php echo ($previo["torneo_id"] ?? "") === (string) $torneo["id"] ? "selected" : ""; ?>><?php echo $e($torneo["nombre"]); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <div class="resultado-formulario-equipos horario-formulario-equipos">
                            <?php foreach (["local" => "Local", "visitante" => "Visitante"] as $lado => $titulo): ?>
                                <label>
                                    <?php echo $titulo; ?>
                                    <select name="<?php echo $lado; ?>" required data-equipos-liga>
                                        <?php foreach ($ligas_formulario as $liga): ?>
                                            <?php foreach ($liga["equipos"] as $club): ?>
                                                <option value="<?php echo $e($club["id"]); ?>" data-liga="<?php echo $liga["id"]; ?>" <?php echo ($previo[$lado] ?? "") === $club["id"] ? "selected" : ""; ?>><?php echo $e($club["nombre"]); ?></option>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="resultado-formulario-equipos horario-formulario-equipos">
                            <label>
                                Fecha
                                <input type="date" name="fecha" required min="<?php echo date("Y-m-d"); ?>" value="<?php echo $e($previo["fecha"] ?? ""); ?>">
                            </label>
                            <label>
                                Hora <small>(opcional)</small>
                                <input type="time" name="hora" value="<?php echo $e($previo["hora"] ?? ""); ?>">
                            </label>
                        </div>

                        <label>
                            Lugar <small>(opcional)</small>
                            <input type="text" name="lugar" maxlength="80" placeholder="Ej.: Complejo Municipal · Cancha 2" value="<?php echo $e($previo["lugar"] ?? ""); ?>">
                        </label>

                        <p class="resultado-formulario-aviso">El partido se suma al calendario de la liga. Cuando se juegue, el resultado se carga desde Resultados o desde la liga.</p>

                        <div class="resultado-formulario-botones">
                            <button type="button" class="resultado-cancelar" data-cerrar-horario>Cancelar</button>
                            <button type="submit" class="resultados-agregar">Agregar</button>
                        </div>
                    </form>
                <?php endif; ?>
            </dialog>
        <?php endif; ?>

        <div class="resultados-lista">
            <?php if (count($horarios_por_deporte) === 0): ?>
                <p class="resultados-vacio">No hay partidos programados. Se agregan desde el calendario de cada liga.</p>
            <?php endif; ?>
            <p class="resultados-vacio" id="sinCoincidencias" hidden>No hay partidos que coincidan con la búsqueda.</p>

            <?php foreach ($horarios_por_deporte as $deporte): ?>
                <section class="resultados-deporte" data-tabla-deporte="<?php echo $e($deporte["id"]); ?>">
                    <header class="resultados-deporte-cabecera">
                        <h2><?php echo $e($deporte["nombre"]); ?></h2>
                        <span><span data-contador-partidos><?php echo count($deporte["partidos"]); ?></span> partidos</span>
                    </header>
                    <div class="tabla-resultados-desplazable" role="region" aria-label="Horarios de <?php echo $e($deporte["nombre"]); ?>" tabindex="0">
                        <table class="tabla-resultados horarios-tabla">
                            <thead>
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Hora</th>
                                    <th scope="col">Liga</th>
                                    <th scope="col">Local</th>
                                    <th scope="col"><span class="resultados-oculto">Contra</span></th>
                                    <th scope="col">Visitante</th>
                                    <th scope="col">Lugar</th>
                                    <th scope="col">Estado</th>
                                    <?php if ($puede_agregar): ?>
                                        <th scope="col"><span class="resultados-oculto">Acciones</span></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deporte["partidos"] as $partido): ?>
                                    <tr data-fecha="<?php echo $e($partido["fecha"]); ?>"
                                        data-pendiente="<?php echo $partido["pendiente"] ? "1" : "0"; ?>"
                                        data-buscar="<?php echo $e(implode(" ", [$partido["liga"], $partido["torneo"], $partido["deporte"], $partido["local"]["nombre"], $partido["visitante"]["nombre"], $partido["lugar"]])); ?>">
                                        <td data-label="Fecha">
                                            <span class="horario-fecha">
                                                <strong><?php echo $e($partido["dia"]); ?></strong>
                                                <small><?php echo $e($partido["mes"]); ?></small>
                                            </span>
                                        </td>
                                        <td data-label="Hora"><span class="horario-hora"><?php echo $e($partido["hora"] ?: "A confirmar"); ?></span></td>
                                        <td data-label="Liga">
                                            <span class="resultado-liga"><?php echo $e($partido["liga"]); ?></span>
                                            <span class="resultado-liga-detalle"><?php echo $e($partido["deporte"] . " · " . $partido["torneo"]); ?></span>
                                        </td>
                                        <td data-label="Local">
                                            <span class="resultado-equipo">
                                                <img src="<?php echo $e($partido["local"]["logo"]); ?>" alt="">
                                                <?php echo $e($partido["local"]["nombre"]); ?>
                                            </span>
                                        </td>
                                        <td class="horario-vs"><strong class="marcador-resultado">vs</strong></td>
                                        <td data-label="Visitante">
                                            <span class="resultado-equipo">
                                                <img src="<?php echo $e($partido["visitante"]["logo"]); ?>" alt="">
                                                <?php echo $e($partido["visitante"]["nombre"]); ?>
                                            </span>
                                        </td>
                                        <td data-label="Lugar"><?php echo $e($partido["lugar"] ?: "A confirmar"); ?></td>
                                        <td data-label="Estado">
                                            <?php if ($partido["pendiente"]): ?>
                                                <span class="estado-resultado horario-pendiente">Sin resultado</span>
                                                <span class="horario-se-borra" title="Si no se carga el resultado, se borra solo a los 30 días">Se borra el <?php echo $e($partido["se_borra"]); ?></span>
                                            <?php else: ?>
                                                <span class="estado-resultado estado-programado">Programado</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($puede_agregar): ?>
                                            <td>
                                                <form method="post" action="/index.php?pagina=horarios" onsubmit="return confirm('¿Eliminar este partido del calendario? No se puede deshacer.');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_horarios"]); ?>">
                                                    <input type="hidden" name="accion" value="eliminar_horario">
                                                    <input type="hidden" name="id" value="<?php echo $partido["id"]; ?>">
                                                    <button type="submit" class="resultado-eliminar">Eliminar</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </main>

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

<script>
    const tablasDeporte = document.querySelectorAll("[data-tabla-deporte]");
    const buscador = document.getElementById("buscarHorarios");
    const filtroDeporte = document.getElementById("filtroDeporte");
    const filtroPeriodo = document.getElementById("filtroPeriodo");
    const sinCoincidencias = document.getElementById("sinCoincidencias");

    // sin tildes ni mayusculas, asi "aguilas" encuentra "Águilas"
    const normalizar = (texto) => texto.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase().trim();

    // fecha local en formato 2026-10-21 (igual que la base)
    const comoFecha = (fecha) => fecha.getFullYear() + "-" + String(fecha.getMonth() + 1).padStart(2, "0") + "-" + String(fecha.getDate()).padStart(2, "0");
    const dentroDeDias = (dias) => {
        const fecha = new Date();
        fecha.setDate(fecha.getDate() + dias);
        return comoFecha(fecha);
    };

    function entraEnPeriodo(fila, periodo) {
        const fecha = fila.dataset.fecha;
        const hoy = comoFecha(new Date());
        if (periodo === "hoy") return fecha === hoy;
        if (periodo === "semana") return fecha >= hoy && fecha <= dentroDeDias(7);
        if (periodo === "mes") return fecha >= hoy && fecha <= dentroDeDias(30);
        if (periodo === "pendientes") return fila.dataset.pendiente === "1";
        return true;
    }

    // junta el buscador con los dos filtros
    function filtrarHorarios() {
        const busqueda = normalizar(buscador.value);
        const deporte = filtroDeporte.value;
        const periodo = filtroPeriodo.value;
        let visibles = 0;

        tablasDeporte.forEach((tabla) => {
            let filasVisibles = 0;
            tabla.querySelectorAll("tbody tr").forEach((fila) => {
                const coincide = (busqueda === "" || normalizar(fila.dataset.buscar).includes(busqueda)) && entraEnPeriodo(fila, periodo);
                fila.hidden = !coincide;
                if (coincide) filasVisibles++;
            });

            tabla.querySelector("[data-contador-partidos]").textContent = filasVisibles;
            tabla.hidden = filasVisibles === 0 || (deporte !== "todos" && tabla.dataset.tablaDeporte !== deporte);
            if (!tabla.hidden) visibles++;
        });

        sinCoincidencias.hidden = visibles > 0 || tablasDeporte.length === 0;
    }

    buscador.addEventListener("input", filtrarHorarios);
    filtroDeporte.addEventListener("change", filtrarHorarios);
    filtroPeriodo.addEventListener("change", filtrarHorarios);

    // formulario de nuevo partido (solo admin y organizador)
    (() => {
        const ventana = document.getElementById("nuevoHorario");
        if (!ventana) return;

        document.getElementById("abrirNuevoHorario").addEventListener("click", () => ventana.showModal());
        ventana.querySelectorAll("[data-cerrar-horario]").forEach((boton) => {
            boton.addEventListener("click", () => ventana.close());
        });

        const torneo = ventana.querySelector("[data-elegir-torneo]");
        const listasEquipos = ventana.querySelectorAll("[data-equipos-liga]");

        // solo se muestran los equipos de la liga del torneo elegido
        function filtrarEquipos() {
            const liga = torneo.selectedOptions[0].dataset.liga;

            listasEquipos.forEach((lista, indiceLista) => {
                const deLaLiga = Array.from(lista.options).filter((opcion) => opcion.dataset.liga === liga);
                Array.from(lista.options).forEach((opcion) => {
                    opcion.hidden = opcion.dataset.liga !== liga;
                    opcion.disabled = opcion.hidden;
                });
                if (lista.selectedOptions[0].disabled) {
                    // local el primero y visitante el segundo, asi no arrancan iguales
                    const sugerido = deLaLiga[indiceLista] || deLaLiga[0];
                    if (sugerido) sugerido.selected = true;
                }
            });
        }

        if (torneo) {
            torneo.addEventListener("change", filtrarEquipos);
            filtrarEquipos();
        }

        if (ventana.hasAttribute("data-abrir")) ventana.showModal();
    })();
</script>
</body>
</html>



