<?php
// variables que vienen de controllers/eventos.php (para que el editor no marque error)
/** @var string $mensaje */
/** @var string $tipo_mensaje */
/** @var bool $formulario_con_error */
/** @var bool $puede_editar */
/** @var array $eventos_por_deporte */
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENIX | Eventos</title>

    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/reglas.css">
    <link rel="stylesheet" href="/public/assets/css/resultados.css">
    <link rel="stylesheet" href="/public/assets/css/horarios.css">
    <link rel="stylesheet" href="/public/assets/css/eventos.css">
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
            <a href="/index.php?pagina=horarios">
                <img src="/public/assets/images/nav-horarios.png" alt="">
                <span>HORARIOS</span>
            </a>
            <a href="/index.php?pagina=eventos" class="activo">
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
            <p class="resultados-mensaje resultados-<?php echo $tipo_mensaje; ?>"><?php echo $e($mensaje); ?></p>
        <?php endif; ?>

        <div class="resultados-barra horarios-barra">
            <label class="resultados-buscador">
                <span class="resultados-oculto">Buscar eventos</span>
                <input type="search" id="buscarEventos" placeholder="Buscar evento, modalidad o sede" autocomplete="off">
            </label>

            <label class="horarios-filtro">
                <span class="resultados-oculto">Deporte</span>
                <select id="filtroDeporte">
                    <option value="todos">Todos los deportes</option>
                    <?php foreach ($eventos_por_deporte as $deporte): ?>
                        <option value="<?php echo $e($deporte["id"]); ?>"><?php echo $e($deporte["nombre"]); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="horarios-filtro">
                <span class="resultados-oculto">Cuándo</span>
                <select id="filtroPeriodo">
                    <option value="proximos">Próximos</option>
                    <option value="semana">Próximos 7 días</option>
                    <option value="mes">Próximos 30 días</option>
                    <option value="pasados">Ya realizados</option>
                    <option value="todos">Todas las fechas</option>
                </select>
            </label>

            <label class="horarios-filtro">
                <span class="resultados-oculto">Estado</span>
                <select id="filtroEstado">
                    <option value="todos">Todos los estados</option>
                    <?php foreach (estados_evento() as $clave_estado => $nombre_estado): ?>
                        <option value="<?php echo $clave_estado; ?>"><?php echo $e($nombre_estado); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <?php if ($puede_editar): ?>
                <button type="button" class="resultados-agregar" id="abrirNuevoEvento">+ Agregar evento</button>
            <?php endif; ?>
        </div>

        <?php if ($puede_editar): ?>
            <?php $previo = $formulario_con_error ? $_POST : []; ?>
            <dialog class="resultado-formulario" id="nuevoEvento" <?php echo $formulario_con_error ? "data-abrir" : ""; ?>>
                <form class="resultado-formulario-cuerpo" method="post" action="/index.php?pagina=eventos">
                    <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_eventos"]); ?>">
                    <input type="hidden" name="accion" value="guardar_evento">
                    <input type="hidden" name="id" value="<?php echo $e($previo["id"] ?? ""); ?>">

                    <h3 data-titulo-evento><?php echo !empty($previo["id"]) ? "Editar evento" : "Nuevo evento"; ?></h3>

                    <label>
                        Nombre del evento
                        <input type="text" name="nombre" minlength="3" maxlength="80" required placeholder="Ej.: Torneo de verano" value="<?php echo $e($previo["nombre"] ?? ""); ?>">
                    </label>

                    <div class="resultado-formulario-equipos horario-formulario-equipos">
                        <label>
                            Deporte
                            <select name="deporte" required>
                                <?php foreach (deportes_eventos() as $clave_deporte => $nombre_deporte): ?>
                                    <option value="<?php echo $clave_deporte; ?>" <?php echo ($previo["deporte"] ?? "") === $clave_deporte ? "selected" : ""; ?>><?php echo $e($nombre_deporte); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            Estado
                            <select name="estado" required>
                                <?php foreach (estados_evento() as $clave_estado => $nombre_estado): ?>
                                    <option value="<?php echo $clave_estado; ?>" <?php echo ($previo["estado"] ?? "abierto") === $clave_estado ? "selected" : ""; ?>><?php echo $e($nombre_estado); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <div class="resultado-formulario-equipos horario-formulario-equipos">
                        <label>
                            Fecha
                            <input type="date" name="fecha" required value="<?php echo $e($previo["fecha"] ?? ""); ?>">
                        </label>
                        <label>
                            Hora <small>(opcional)</small>
                            <input type="time" name="hora" value="<?php echo $e($previo["hora"] ?? ""); ?>">
                        </label>
                    </div>

                    <div class="resultado-formulario-equipos horario-formulario-equipos">
                        <label>
                            Modalidad <small>(opcional)</small>
                            <input type="text" name="modalidad" maxlength="60" placeholder="Ej.: Sub-18, mixto" value="<?php echo $e($previo["modalidad"] ?? ""); ?>">
                        </label>
                        <label>
                            Cupos <small>(opcional)</small>
                            <input type="text" name="cupos" maxlength="40" placeholder="Ej.: 12 equipos" value="<?php echo $e($previo["cupos"] ?? ""); ?>">
                        </label>
                    </div>

                    <label>
                        Sede <small>(opcional)</small>
                        <input type="text" name="sede" maxlength="80" placeholder="Ej.: Polideportivo Norte" value="<?php echo $e($previo["sede"] ?? ""); ?>">
                    </label>

                    <div class="resultado-formulario-botones">
                        <button type="button" class="resultado-cancelar" data-cerrar-evento>Cancelar</button>
                        <button type="submit" class="resultados-agregar" data-boton-guardar><?php echo !empty($previo["id"]) ? "Guardar cambios" : "Agregar"; ?></button>
                    </div>
                </form>
            </dialog>
        <?php endif; ?>

        <div class="resultados-lista">
            <?php if (count($eventos_por_deporte) === 0): ?>
                <p class="resultados-vacio">Todavía no hay eventos cargados.</p>
            <?php endif; ?>
            <p class="resultados-vacio" id="sinCoincidencias" hidden>No hay eventos que coincidan con la búsqueda.</p>

            <?php foreach ($eventos_por_deporte as $deporte): ?>
                <section class="resultados-deporte" data-tabla-deporte="<?php echo $e($deporte["id"]); ?>">
                    <header class="resultados-deporte-cabecera">
                        <h2><?php echo $e($deporte["nombre"]); ?></h2>
                        <span><span data-contador-eventos><?php echo count($deporte["eventos"]); ?></span> eventos</span>
                    </header>
                    <div class="tabla-resultados-desplazable" role="region" aria-label="Eventos de <?php echo $e($deporte["nombre"]); ?>" tabindex="0">
                        <table class="tabla-resultados eventos-tabla">
                            <thead>
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Hora</th>
                                    <th scope="col">Evento</th>
                                    <th scope="col">Modalidad</th>
                                    <th scope="col">Sede</th>
                                    <th scope="col">Cupos</th>
                                    <th scope="col">Estado</th>
                                    <?php if ($puede_editar): ?>
                                        <th scope="col"><span class="resultados-oculto">Acciones</span></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deporte["eventos"] as $evento): ?>
                                    <tr data-fecha="<?php echo $e($evento["fecha"]); ?>"
                                        data-estado="<?php echo $e($evento["estado"]); ?>"
                                        data-buscar="<?php echo $e(implode(" ", [$evento["nombre"], $evento["deporte"], $evento["modalidad"], $evento["sede"], $evento["cupos"], $evento["nombre_estado"]])); ?>">
                                        <td>
                                            <span class="horario-fecha">
                                                <strong><?php echo $e($evento["dia"]); ?></strong>
                                                <small><?php echo $e($evento["mes"]); ?></small>
                                            </span>
                                        </td>
                                        <td><span class="horario-hora"><?php echo $e($evento["hora"] ?: "A confirmar"); ?></span></td>
                                        <td><span class="resultado-liga"><?php echo $e($evento["nombre"]); ?></span></td>
                                        <td><?php echo $e($evento["modalidad"] ?: "—"); ?></td>
                                        <td><?php echo $e($evento["sede"] ?: "A confirmar"); ?></td>
                                        <td><?php echo $e($evento["cupos"] ?: "—"); ?></td>
                                        <td><span class="estado-resultado evento-estado-<?php echo $e($evento["estado"]); ?>"><?php echo $e($evento["nombre_estado"]); ?></span></td>
                                        <?php if ($puede_editar): ?>
                                            <td class="evento-acciones">
                                                <button type="button" class="resultado-eliminar evento-editar"
                                                    data-editar-evento
                                                    data-id="<?php echo $evento["id"]; ?>"
                                                    data-nombre="<?php echo $e($evento["nombre"]); ?>"
                                                    data-deporte="<?php echo $e($evento["clave_deporte"]); ?>"
                                                    data-estado="<?php echo $e($evento["estado_guardado"]); ?>"
                                                    data-fecha="<?php echo $e($evento["fecha"]); ?>"
                                                    data-hora="<?php echo $e($evento["hora"]); ?>"
                                                    data-modalidad="<?php echo $e($evento["modalidad"]); ?>"
                                                    data-cupos="<?php echo $e($evento["cupos"]); ?>"
                                                    data-sede="<?php echo $e($evento["sede"]); ?>">Editar</button>
                                                <form method="post" action="/index.php?pagina=eventos" onsubmit="return confirm('¿Eliminar este evento? No se puede deshacer.');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_eventos"]); ?>">
                                                    <input type="hidden" name="accion" value="eliminar_evento">
                                                    <input type="hidden" name="id" value="<?php echo $evento["id"]; ?>">
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
    const buscador = document.getElementById("buscarEventos");
    const filtroDeporte = document.getElementById("filtroDeporte");
    const filtroPeriodo = document.getElementById("filtroPeriodo");
    const filtroEstado = document.getElementById("filtroEstado");
    const sinCoincidencias = document.getElementById("sinCoincidencias");

    // sin tildes ni mayusculas, asi "clinica" encuentra "Clínica"
    const normalizar = (texto) => texto.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase().trim();

    // fecha local en formato 2026-10-21 (igual que la base)
    const comoFecha = (fecha) => fecha.getFullYear() + "-" + String(fecha.getMonth() + 1).padStart(2, "0") + "-" + String(fecha.getDate()).padStart(2, "0");
    const dentroDeDias = (dias) => {
        const fecha = new Date();
        fecha.setDate(fecha.getDate() + dias);
        return comoFecha(fecha);
    };

    function entraEnPeriodo(fecha, periodo) {
        const hoy = comoFecha(new Date());
        if (periodo === "proximos") return fecha >= hoy;
        if (periodo === "semana") return fecha >= hoy && fecha <= dentroDeDias(7);
        if (periodo === "mes") return fecha >= hoy && fecha <= dentroDeDias(30);
        if (periodo === "pasados") return fecha < hoy;
        return true;
    }

    // junta el buscador con los tres filtros
    function filtrarEventos() {
        const busqueda = normalizar(buscador.value);
        let visibles = 0;

        tablasDeporte.forEach((tabla) => {
            let filasVisibles = 0;
            tabla.querySelectorAll("tbody tr").forEach((fila) => {
                const coincide = (busqueda === "" || normalizar(fila.dataset.buscar).includes(busqueda))
                    && entraEnPeriodo(fila.dataset.fecha, filtroPeriodo.value)
                    && (filtroEstado.value === "todos" || fila.dataset.estado === filtroEstado.value);
                fila.hidden = !coincide;
                if (coincide) filasVisibles++;
            });

            tabla.querySelector("[data-contador-eventos]").textContent = filasVisibles;
            tabla.hidden = filasVisibles === 0 || (filtroDeporte.value !== "todos" && tabla.dataset.tablaDeporte !== filtroDeporte.value);
            if (!tabla.hidden) visibles++;
        });

        sinCoincidencias.hidden = visibles > 0 || tablasDeporte.length === 0;
    }

    buscador.addEventListener("input", filtrarEventos);
    [filtroDeporte, filtroPeriodo, filtroEstado].forEach((filtro) => filtro.addEventListener("change", filtrarEventos));
    // arranca mostrando los proximos
    filtrarEventos();

    // formulario de nuevo evento (solo admin y organizador)
    (() => {
        const ventana = document.getElementById("nuevoEvento");
        if (!ventana) return;

        const formulario = ventana.querySelector("form");
        const campos = ["id", "nombre", "deporte", "estado", "fecha", "hora", "modalidad", "cupos", "sede"];

        // el mismo formulario sirve para agregar (vacio) y para editar (con los datos del evento)
        function abrir(datos) {
            campos.forEach((campo) => {
                formulario.elements[campo].value = datos[campo] || "";
            });
            if (!datos.id) {
                formulario.elements.deporte.selectedIndex = 0;
                formulario.elements.estado.value = "abierto";
            }
            ventana.querySelector("[data-titulo-evento]").textContent = datos.id ? "Editar evento" : "Nuevo evento";
            ventana.querySelector("[data-boton-guardar]").textContent = datos.id ? "Guardar cambios" : "Agregar";
            ventana.showModal();
        }

        document.getElementById("abrirNuevoEvento").addEventListener("click", () => abrir({}));
        document.querySelectorAll("[data-editar-evento]").forEach((boton) => {
            boton.addEventListener("click", () => abrir(boton.dataset));
        });
        ventana.querySelector("[data-cerrar-evento]").addEventListener("click", () => ventana.close());
        if (ventana.hasAttribute("data-abrir")) ventana.showModal();
    })();
</script>
</body>
</html>



