<?php
// variables que vienen de controllers/resultados.php (para que el editor no marque error)
/** @var string $mensaje */
/** @var string $tipo_mensaje */
/** @var string|null $enlace_tabla */
/** @var bool $formulario_con_error */
/** @var bool $puede_cargar */
/** @var array $ligas_formulario */
/** @var array $resultados_por_deporte */
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENIX | Resultados</title>

    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/reglas.css">
    <link rel="stylesheet" href="/public/assets/css/resultados.css">
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
                <a href="/index.php?pagina=resultados" class="activo">
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

    <!-- contenido -->
    <main class="resultados-pagina">
        <?php if ($mensaje !== ""): ?>
            <p class="resultados-mensaje resultados-<?php echo $tipo_mensaje; ?>">
                <?php echo $e($mensaje); ?>
                <?php if ($enlace_tabla): ?>
                    <a href="<?php echo $e($enlace_tabla); ?>">Ver la tabla de la liga</a>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <div class="resultados-barra">
            <div class="resultados-herramientas">
                <label class="resultados-buscador">
                    <span class="resultados-oculto">Buscar resultados</span>
                    <input type="search" id="buscarResultados" placeholder="Buscar deporte, equipo, liga o torneo" autocomplete="off">
                </label>

                <?php if ($puede_cargar): ?>
                    <button type="button" class="resultados-agregar" id="abrirNuevoResultado">+ Agregar resultado</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($puede_cargar): ?>
            <?php $previo = $formulario_con_error ? $_POST : []; ?>
            <dialog class="resultado-formulario" id="nuevoResultado" <?php echo $formulario_con_error ? "data-abrir" : ""; ?>>
                <?php if (count($ligas_formulario) === 0): ?>
                    <div class="resultado-formulario-cuerpo">
                        <h3>Nuevo resultado</h3>
                        <p class="resultado-formulario-aviso">No hay ligas con un torneo activo y al menos dos equipos. Crealos desde la página de ligas.</p>
                        <div class="resultado-formulario-botones">
                            <button type="button" class="resultado-cancelar" data-cerrar-resultado>Cerrar</button>
                        </div>
                    </div>
                <?php else: ?>
                    <form class="resultado-formulario-cuerpo" method="post" action="/index.php?pagina=resultados">
                        <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_resultados"]); ?>">
                        <input type="hidden" name="accion" value="agregar_resultado">

                        <h3>Nuevo resultado</h3>

                        <label>
                            Liga y torneo
                            <select name="torneo_id" required data-elegir-torneo>
                                <?php foreach ($ligas_formulario as $liga): ?>
                                    <optgroup label="<?php echo $e($liga["nombre"] . " · " . $liga["deporte"]); ?>">
                                        <?php foreach ($liga["torneos"] as $torneo): ?>
                                            <option value="<?php echo $torneo["id"]; ?>" data-liga="<?php echo $liga["id"]; ?>" data-tanteador="<?php echo $e($liga["tanteador"]); ?>" data-sets="<?php echo (int) $liga["sets"]; ?>" data-definicion="<?php echo $liga["definicion"] ? "1" : "0"; ?>" <?php echo ($previo["torneo_id"] ?? "") === (string) $torneo["id"] ? "selected" : ""; ?>><?php echo $e($torneo["nombre"]); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <div class="resultado-formulario-equipos">
                            <label>
                                Local
                                <select name="local" required data-equipos-liga>
                                    <?php foreach ($ligas_formulario as $liga): ?>
                                        <?php foreach ($liga["equipos"] as $club): ?>
                                            <option value="<?php echo $e($club["id"]); ?>" data-liga="<?php echo $liga["id"]; ?>" <?php echo ($previo["local"] ?? "") === $club["id"] ? "selected" : ""; ?>><?php echo $e($club["nombre"]); ?></option>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span data-tanteador>Goles</span>
                                <input type="number" name="goles_local" min="0" max="999" required value="<?php echo $e($previo["goles_local"] ?? ""); ?>">
                            </label>
                            <label>
                                Visitante
                                <select name="visitante" required data-equipos-liga>
                                    <?php foreach ($ligas_formulario as $liga): ?>
                                        <?php foreach ($liga["equipos"] as $club): ?>
                                            <option value="<?php echo $e($club["id"]); ?>" data-liga="<?php echo $liga["id"]; ?>" <?php echo ($previo["visitante"] ?? "") === $club["id"] ? "selected" : ""; ?>><?php echo $e($club["nombre"]); ?></option>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span data-tanteador>Goles</span>
                                <input type="number" name="goles_visitante" min="0" max="999" required value="<?php echo $e($previo["goles_visitante"] ?? ""); ?>">
                            </label>
                        </div>

                        <div class="resultado-formulario-equipos">
                            <label>
                                Fecha
                                <input type="date" name="fecha" required max="<?php echo date("Y-m-d"); ?>" value="<?php echo $e($previo["fecha"] ?? date("Y-m-d")); ?>">
                            </label>
                            <label>
                                Hora
                                <input type="time" name="hora" required value="<?php echo $e($previo["hora"] ?? ""); ?>">
                            </label>
                        </div>

                        <label>
                            Lugar
                            <input type="text" name="lugar" minlength="2" maxlength="80" required placeholder="Ej.: Complejo Municipal · Cancha 2" value="<?php echo $e($previo["lugar"] ?? ""); ?>">
                        </label>

                        <label data-campo-definicion hidden>
                            ¿Cómo se definió?
                            <select name="definicion">
                                <option value="regular" <?php echo ($previo["definicion"] ?? "") !== "extra" ? "selected" : ""; ?>>Tiempo regular</option>
                                <option value="extra" <?php echo ($previo["definicion"] ?? "") === "extra" ? "selected" : ""; ?>>Alargue o penales</option>
                            </select>
                        </label>

                        <p class="resultado-formulario-aviso">Todos los datos son obligatorios. Al guardar, el partido suma en la tabla de posiciones de la liga.</p>

                        <div class="resultado-formulario-botones">
                            <button type="button" class="resultado-cancelar" data-cerrar-resultado>Cancelar</button>
                            <button type="submit" class="resultados-agregar">Guardar</button>
                        </div>
                    </form>
                <?php endif; ?>
            </dialog>
        <?php endif; ?>

        <div class="resultados-lista">
            <?php if (count($resultados_por_deporte) === 0): ?>
                <p class="resultados-vacio">Todavía no hay resultados cargados.</p>
            <?php endif; ?>
            <p class="resultados-vacio" id="sinCoincidencias" hidden>No hay resultados que coincidan con la búsqueda.</p>

            <?php foreach ($resultados_por_deporte as $deporte): ?>
                <section class="resultados-deporte" data-tabla-deporte="<?php echo $e($deporte["id"]); ?>">
                    <header class="resultados-deporte-cabecera">
                        <h2><?php echo $e($deporte["nombre"]); ?></h2>
                        <span><span data-contador-partidos><?php echo count($deporte["partidos"]); ?></span> partidos</span>
                    </header>
                    <div class="tabla-resultados-desplazable" role="region" aria-label="Resultados de <?php echo $e($deporte["nombre"]); ?>" tabindex="0">
                        <table class="tabla-resultados">
                            <thead>
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Liga</th>
                                    <th scope="col">Local</th>
                                    <th scope="col">Resultado</th>
                                    <th scope="col">Visitante</th>
                                    <?php if ($puede_cargar): ?>
                                        <th scope="col"><span class="resultados-oculto">Acciones</span></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deporte["partidos"] as $partido): ?>
                                    <tr data-buscar="<?php echo $e(implode(" ", [$partido["fecha"], $partido["liga"], $partido["torneo"], $partido["deporte"], $partido["local"]["nombre"], $partido["visitante"]["nombre"]])); ?>">
                                        <td><?php echo $e($partido["fecha"]); ?></td>
                                        <td>
                                            <span class="resultado-liga"><?php echo $e($partido["liga"]); ?></span>
                                            <span class="resultado-liga-detalle"><?php echo $e($partido["deporte"] . " · " . $partido["torneo"]); ?></span>
                                        </td>
                                        <td>
                                            <span class="resultado-equipo">
                                                <img src="<?php echo $e($partido["local"]["logo"]); ?>" alt="">
                                                <?php echo $e($partido["local"]["nombre"]); ?>
                                            </span>
                                        </td>
                                        <td><strong class="marcador-resultado"><?php echo $e($partido["marcador"]); ?></strong></td>
                                        <td>
                                            <span class="resultado-equipo">
                                                <img src="<?php echo $e($partido["visitante"]["logo"]); ?>" alt="">
                                                <?php echo $e($partido["visitante"]["nombre"]); ?>
                                            </span>
                                        </td>
                                        <?php if ($puede_cargar): ?>
                                            <td>
                                                <form method="post" action="/index.php?pagina=resultados" onsubmit="return confirm('¿Eliminar este resultado? No se puede deshacer.');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_resultados"]); ?>">
                                                    <input type="hidden" name="accion" value="eliminar_resultado">
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
    const buscador = document.getElementById("buscarResultados");
    const sinCoincidencias = document.getElementById("sinCoincidencias");

    // sin tildes ni mayusculas, asi "aguilas" encuentra "Águilas"
    const normalizar = (texto) => texto.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase().trim();

    // cada fila tiene deporte, liga, torneo, equipos y fecha para buscar
    function filtrarResultados() {
        const busqueda = normalizar(buscador.value);
        let visibles = 0;

        tablasDeporte.forEach((tabla) => {
            let filasVisibles = 0;
            tabla.querySelectorAll("tbody tr").forEach((fila) => {
                const coincide = busqueda === "" || normalizar(fila.dataset.buscar).includes(busqueda);
                fila.hidden = !coincide;
                if (coincide) filasVisibles++;
            });

            tabla.querySelector("[data-contador-partidos]").textContent = filasVisibles;
            tabla.hidden = filasVisibles === 0;
            if (!tabla.hidden) visibles++;
        });

        sinCoincidencias.hidden = visibles > 0 || tablasDeporte.length === 0;
    }

    buscador.addEventListener("input", filtrarResultados);

    // formulario de nuevo resultado (solo admin y organizador)
    (() => {
        const ventana = document.getElementById("nuevoResultado");
        if (!ventana) return;

        document.getElementById("abrirNuevoResultado").addEventListener("click", () => ventana.showModal());
        ventana.querySelectorAll("[data-cerrar-resultado]").forEach((boton) => {
            boton.addEventListener("click", () => ventana.close());
        });

        const torneo = ventana.querySelector("[data-elegir-torneo]");
        const listasEquipos = ventana.querySelectorAll("[data-equipos-liga]");

        // solo se muestran los equipos de la liga del torneo elegido
        function filtrarEquipos() {
            const liga = torneo.selectedOptions[0].dataset.liga;

            // goles en futbol, puntos en basquet, sets en voley
            const tanteador = torneo.selectedOptions[0].dataset.tanteador;
            ventana.querySelectorAll("[data-tanteador]").forEach((etiqueta) => {
                etiqueta.textContent = tanteador;
            });
            // en voley el maximo son 3 sets y en voley playa 2
            const sets = Number(torneo.selectedOptions[0].dataset.sets);
            ventana.querySelectorAll("[name=goles_local], [name=goles_visitante]").forEach((campo) => {
                campo.max = sets || 999;
            });

            // hockey sobre hielo y waterpolo: ganar en alargue o penales da menos puntos
            ventana.querySelector("[data-campo-definicion]").hidden = torneo.selectedOptions[0].dataset.definicion !== "1";

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



