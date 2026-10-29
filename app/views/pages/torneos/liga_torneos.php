<?php
// pestaña torneos y tabla de una liga (se incluye desde enfrentamientos.php)

// variables que vienen de enfrentamientos.php (para que el editor no marque error)
/** @var callable $e */
/** @var array $liga_actual */
/** @var array $equipos */
/** @var array $torneos */
/** @var array $torneo_actual */
/** @var array $partidos */
/** @var array $tabla */
/** @var string $pestana */
/** @var bool $puede_administrar */
/** @var bool $vista_publica */
$nombres_equipos = array_column($equipos, null, "id");
$equipo_de = fn($id) => $nombres_equipos[$id] ?? ["nombre" => "Equipo eliminado", "logo" => ""];
$formatear_fecha = fn($fecha) => implode("/", array_reverse(explode("-", $fecha)));
$programados = array_values(array_filter($partidos, fn($p) => $p["estado"] !== "finalizado"));
$jugados = array_reverse(array_values(array_filter($partidos, fn($p) => $p["estado"] === "finalizado")));
$torneo_activo = $torneo_actual && $torneo_actual["estado"] === "activo";
$hay_lugar_torneo = $liga_actual["uso"]["torneos"] < $liga_actual["limites"]["torneos"];

// campos ocultos que llevan todos los formularios
$campos_ocultos = function ($accion, $extra = []) use ($e, $liga_actual, $torneo_actual) {
    $html = '<input type="hidden" name="csrf_token" value="' . $e($_SESSION["csrf_clubes"] ?? "") . '">'
        . '<input type="hidden" name="accion" value="' . $e($accion) . '">'
        . '<input type="hidden" name="liga_id" value="' . (int) $liga_actual["id"] . '">';
    if ($torneo_actual) {
        $html .= '<input type="hidden" name="torneo_id" value="' . (int) $torneo_actual["id"] . '">';
    }
    foreach ($extra as $nombre => $valor) {
        $html .= '<input type="hidden" name="' . $e($nombre) . '" value="' . $e($valor) . '">';
    }
    return $html;
};
$escudo = fn($equipo) => '<img class="mini_escudo" src="' . $e($equipo["logo"]) . '" alt="">';

// columnas y reglas de la tabla segun el deporte de la liga
$reglas = reglas_tabla($liga_actual["deporte"]);
$tanteador = mb_strtolower($reglas["tanteador"], "UTF-8");
$maximo_tanteo = $reglas["sets"] ?: 999;
// en hockey sobre hielo y waterpolo se marca si se gano en alargue o penales
$campo_definicion = function ($actual = "regular") use ($reglas) {
    if (!$reglas["definicion"]) {
        return "";
    }
    return '<select name="definicion" aria-label="Cómo se definió">'
        . '<option value="regular"' . ($actual === "regular" ? " selected" : "") . '>Tiempo regular</option>'
        . '<option value="extra"' . ($actual === "extra" ? " selected" : "") . '>Alargue o penales</option>'
        . '</select>';
};
// resultados viejos que no siguen las reglas del deporte (por ejemplo un empate en basquet)
$mal_cargados = array_filter($jugados, fn($p) => error_marcador($liga_actual["deporte"], (int) $p["goles_local"], (int) $p["goles_visitante"]) !== null);
?>
<section class="pestana_panel panel_torneos" id="panel-torneos" data-pestana="torneos" aria-labelledby="titulo-torneos" <?php echo $pestana === "torneos" ? "" : "hidden"; ?>>

    <div class="seccion_cabecera seccion_cabecera_acciones">
        <div>
            <h2 id="titulo-torneos">Torneos</h2>
            <?php if ($vista_publica): ?>
                <p>Elige un torneo para ver su tabla de posiciones, el calendario y los resultados.</p>
            <?php else: ?>
                <p>El <?php echo $e($liga_actual["nombre_plan"]); ?> permite <?php echo (int) $liga_actual["limites"]["torneos"]; ?> torneo(s) activo(s) a la vez.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- elegir torneo -->
    <?php if (count($torneos) > 0): ?>
        <div class="lista_torneos">
            <?php foreach ($torneos as $torneo): ?>
                <a class="torneo_chip <?php echo $torneo_actual && $torneo["id"] === $torneo_actual["id"] ? "torneo_chip_activo" : ""; ?>"
                   href="/index.php?pagina=enfrentamientos&amp;liga=<?php echo (int) $liga_actual["id"]; ?>&amp;pestana=torneos&amp;torneo=<?php echo (int) $torneo["id"]; ?>">
                    <?php echo $e($torneo["nombre"]); ?>
                    <small><?php echo $torneo["estado"] === "activo" ? "En curso" : "Finalizado"; ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="sin_resultados">Esta liga todavía no tiene torneos.</p>
    <?php endif; ?>

    <?php if ($puede_administrar): ?>
        <?php if ($hay_lugar_torneo): ?>
            <form class="form_linea" method="post" action="/index.php?pagina=enfrentamientos">
                <?php echo $campos_ocultos("crear_torneo"); ?>
                <label for="nuevoTorneo" class="solo-lector">Nombre del torneo</label>
                <input id="nuevoTorneo" name="nombre" type="text" minlength="3" maxlength="60" required placeholder="Nombre del nuevo torneo (ej.: Clausura 2026)">
                <button type="submit" class="boton_agregar boton_chico">+ Crear torneo</button>
            </form>
        <?php else: ?>
            <p class="limite_alcanzado">Llegaste al límite de torneos activos del plan. Finaliza uno para crear otro<?php echo $liga_actual["plan"] !== "enterprise" ? " o cambia a un plan mayor" : ""; ?>.</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($torneo_actual): ?>
        <div class="torneo_titulo">
            <h3><?php echo $e($torneo_actual["nombre"]); ?> <span class="estado <?php echo $torneo_activo ? "estado_prog" : "estado_fin"; ?>"><?php echo $torneo_activo ? "En curso" : "Finalizado"; ?></span></h3>
            <?php if ($puede_administrar): ?>
                <div class="torneo_acciones">
                <form method="post" action="/index.php?pagina=enfrentamientos" class="form_confirmar"
                      data-confirmar="<?php echo $torneo_activo ? "¿Finalizar este torneo? Ya no se podrán agregar partidos." : "¿Reabrir este torneo?"; ?>">
                    <?php echo $campos_ocultos("estado_torneo", ["estado" => $torneo_activo ? "finalizado" : "activo"]); ?>
                    <button type="submit" class="boton_secundario boton_chico"><?php echo $torneo_activo ? "Finalizar torneo" : "Reabrir torneo"; ?></button>
                </form>
                <?php if (!$torneo_activo): ?>
                    <!-- solo se puede eliminar cuando esta finalizado -->
                    <form method="post" action="/index.php?pagina=enfrentamientos" class="form_confirmar"
                          data-confirmar="¿Eliminar el torneo &quot;<?php echo $e($torneo_actual["nombre"]); ?>&quot;? Se borran su calendario, los resultados y la tabla. No se puede deshacer.">
                        <?php echo $campos_ocultos("eliminar_torneo"); ?>
                        <button type="submit" class="boton_peligro boton_chico">Eliminar torneo</button>
                    </form>
                <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="torneo_grilla">

            <!-- tabla de posiciones -->
            <section class="bloque_torneo" aria-labelledby="titulo-tabla">
                <h4 id="titulo-tabla">Tabla de posiciones</h4>
                <div class="tabla_desplazable" tabindex="0" role="region" aria-label="Tabla de posiciones">
                    <table class="tabla_posiciones">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col" class="columna_equipo">Equipo</th>
                                <?php foreach ($reglas["columnas"] as $clave => $columna): ?>
                                    <th scope="col" title="<?php echo $e($reglas["titulos"][$clave]); ?>"><?php echo $e($columna); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tabla as $posicion => $fila): ?>
                                <tr>
                                    <td><?php echo $posicion + 1; ?></td>
                                    <td class="columna_equipo"><?php echo $escudo($fila); ?> <?php echo $e($fila["nombre"]); ?></td>
                                    <?php foreach (array_keys($reglas["columnas"]) as $clave): ?>
                                        <td <?php echo $clave === $reglas["orden"][0] ? 'class="columna_puntos"' : ""; ?>><?php echo $e(valor_tabla($fila, $clave)); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="campo_ayuda"><?php echo $e($reglas["ayuda"]); ?></p>
                <?php if (count($mal_cargados) > 0): ?>
                    <p class="limite_alcanzado">Hay <?php echo count($mal_cargados); ?> resultado(s) que no siguen las reglas de <?php echo $e($liga_actual["nombre_deporte"]); ?> (por ejemplo un empate en básquet o un marcador de vóley que no es de sets). Corregilos abajo en "Resultados" para que la tabla quede bien.</p>
                <?php endif; ?>

                <!-- estadisticas basicas (todos los planes) -->
                <?php
                    $goles = array_sum(array_map(fn($p) => $p["goles_local"] + $p["goles_visitante"], $jugados));
                    $mas_goles = $tabla ? array_reduce($tabla, fn($max, $f) => $max === null || $f["af"] > $max["af"] ? $f : $max) : null;
                    $mejor_defensa = $jugados ? array_reduce(array_filter($tabla, fn($f) => $f["pj"] > 0), fn($min, $f) => $min === null || $f["ec"] < $min["ec"] ? $f : $min) : null;
                ?>
                <h4>Estadísticas básicas</h4>
                <dl class="estadisticas_basicas">
                    <div><dt>Partidos jugados</dt><dd><?php echo count($jugados); ?></dd></div>
                    <div><dt>Partidos por jugar</dt><dd><?php echo count($programados); ?></dd></div>
                    <div><dt><?php echo $e($reglas["tanteador"]); ?> en total</dt><dd><?php echo $goles; ?></dd></div>
                    <div><dt>Promedio por partido</dt><dd><?php echo count($jugados) ? number_format($goles / count($jugados), 1, ",", "") : "—"; ?></dd></div>
                    <div><dt>Más <?php echo $e($tanteador); ?> a favor</dt><dd><?php echo $mas_goles && $mas_goles["af"] > 0 ? $e($mas_goles["nombre"]) . " (" . $mas_goles["af"] . ")" : "—"; ?></dd></div>
                    <div><dt>Menos <?php echo $e($tanteador); ?> en contra</dt><dd><?php echo $mejor_defensa ? $e($mejor_defensa["nombre"]) . " (" . $mejor_defensa["ec"] . ")" : "—"; ?></dd></div>
                </dl>
            </section>

            <!-- calendario y resultados -->
            <section class="bloque_torneo" aria-labelledby="titulo-calendario">
                <h4 id="titulo-calendario">Calendario</h4>

                <?php if (count($programados) === 0): ?>
                    <p class="campo_ayuda">No hay partidos por jugar.</p>
                <?php endif; ?>
                <ul class="lista_partidos">
                    <?php foreach ($programados as $partido):
                        $local = $equipo_de($partido["local"]);
                        $visitante = $equipo_de($partido["visitante"]);
                    ?>
                        <li class="partido">
                            <div class="partido_fecha">
                                <strong><?php echo $e($formatear_fecha($partido["fecha"])); ?></strong>
                                <span><?php echo $e($partido["hora"] ?: "Hora a confirmar"); ?><?php echo $partido["lugar"] ? " · " . $e($partido["lugar"]) : ""; ?></span>
                            </div>
                            <div class="partido_equipos">
                                <span><?php echo $escudo($local); ?> <?php echo $e($local["nombre"]); ?></span>
                                <span class="partido_vs">vs</span>
                                <span><?php echo $escudo($visitante); ?> <?php echo $e($visitante["nombre"]); ?></span>
                            </div>
                            <?php if ($puede_administrar): ?>
                                <div class="partido_acciones">
                                    <form method="post" action="/index.php?pagina=enfrentamientos" class="form_resultado">
                                        <?php echo $campos_ocultos("cargar_resultado", ["partido_id" => $partido["id"]]); ?>
                                        <input type="number" name="goles_local" min="0" max="<?php echo $maximo_tanteo; ?>" required aria-label="<?php echo $e($reglas["tanteador"]); ?> de <?php echo $e($local["nombre"]); ?>" placeholder="<?php echo $e($reglas["tanteador"]); ?>">
                                        <span>-</span>
                                        <input type="number" name="goles_visitante" min="0" max="<?php echo $maximo_tanteo; ?>" required aria-label="<?php echo $e($reglas["tanteador"]); ?> de <?php echo $e($visitante["nombre"]); ?>" placeholder="<?php echo $e($reglas["tanteador"]); ?>">
                                        <?php echo $campo_definicion(); ?>
                                        <button type="submit" class="boton_agregar boton_chico">Cargar resultado</button>
                                    </form>
                                    <form method="post" action="/index.php?pagina=enfrentamientos" class="form_confirmar" data-confirmar="¿Borrar este partido del calendario?">
                                        <?php echo $campos_ocultos("borrar_partido", ["partido_id" => $partido["id"]]); ?>
                                        <button type="submit" class="integrante_quitar" title="Borrar partido" aria-label="Borrar partido">×</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($puede_administrar && $torneo_activo): ?>
                    <?php if (count($equipos) < 2): ?>
                        <p class="campo_ayuda">Agrega al menos dos equipos a la liga para armar el calendario.</p>
                    <?php else: ?>
                        <details class="nuevo_partido">
                            <summary>+ Agregar partido al calendario</summary>
                            <form method="post" action="/index.php?pagina=enfrentamientos">
                                <?php echo $campos_ocultos("crear_partido"); ?>
                                <div class="form_aviso_campos">
                                    <div class="campo">
                                        <label for="partidoLocal">Local</label>
                                        <select id="partidoLocal" name="local" required>
                                            <?php foreach ($equipos as $equipo): ?>
                                                <option value="<?php echo $e($equipo["id"]); ?>"><?php echo $e($equipo["nombre"]); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="campo">
                                        <label for="partidoVisitante">Visitante</label>
                                        <select id="partidoVisitante" name="visitante" required>
                                            <?php foreach ($equipos as $i => $equipo): ?>
                                                <option value="<?php echo $e($equipo["id"]); ?>" <?php echo $i === 1 ? "selected" : ""; ?>><?php echo $e($equipo["nombre"]); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="campo">
                                        <label for="partidoFecha">Fecha</label>
                                        <input id="partidoFecha" name="fecha" type="date" required>
                                    </div>
                                    <div class="campo">
                                        <label for="partidoHora">Hora <small>(opcional)</small></label>
                                        <input id="partidoHora" name="hora" type="time">
                                    </div>
                                    <div class="campo campo_ancho">
                                        <label for="partidoLugar">Lugar <small>(opcional)</small></label>
                                        <input id="partidoLugar" name="lugar" type="text" maxlength="80" placeholder="Ej.: Complejo Municipal · Cancha 2">
                                    </div>
                                </div>
                                <button type="submit" class="boton_agregar boton_chico">Agregar partido</button>
                            </form>
                        </details>
                    <?php endif; ?>
                <?php endif; ?>

                <h4>Resultados</h4>
                <?php if (count($jugados) === 0): ?>
                    <p class="campo_ayuda">Todavía no se jugó ningún partido.</p>
                <?php endif; ?>
                <ul class="lista_partidos">
                    <?php foreach ($jugados as $partido):
                        $local = $equipo_de($partido["local"]);
                        $visitante = $equipo_de($partido["visitante"]);
                    ?>
                        <li class="partido partido_jugado">
                            <div class="partido_fecha">
                                <strong><?php echo $e($formatear_fecha($partido["fecha"])); ?></strong>
                                <span><?php echo $partido["lugar"] ? $e($partido["lugar"]) : "Finalizado"; ?></span>
                            </div>
                            <div class="partido_equipos">
                                <span><?php echo $escudo($local); ?> <?php echo $e($local["nombre"]); ?></span>
                                <span class="partido_marcador"><?php echo (int) $partido["goles_local"]; ?> - <?php echo (int) $partido["goles_visitante"]; ?><?php echo $partido["definicion"] === "extra" ? " <small>(alargue/pen.)</small>" : ""; ?></span>
                                <span><?php echo $escudo($visitante); ?> <?php echo $e($visitante["nombre"]); ?></span>
                            </div>
                            <?php if ($puede_administrar): ?>
                                <details class="corregir_resultado">
                                    <summary>Corregir</summary>
                                    <form method="post" action="/index.php?pagina=enfrentamientos" class="form_resultado">
                                        <?php echo $campos_ocultos("cargar_resultado", ["partido_id" => $partido["id"]]); ?>
                                        <input type="number" name="goles_local" min="0" max="<?php echo $maximo_tanteo; ?>" required value="<?php echo (int) $partido["goles_local"]; ?>" aria-label="<?php echo $e($reglas["tanteador"]); ?> de <?php echo $e($local["nombre"]); ?>">
                                        <span>-</span>
                                        <input type="number" name="goles_visitante" min="0" max="<?php echo $maximo_tanteo; ?>" required value="<?php echo (int) $partido["goles_visitante"]; ?>" aria-label="<?php echo $e($reglas["tanteador"]); ?> de <?php echo $e($visitante["nombre"]); ?>">
                                        <?php echo $campo_definicion($partido["definicion"]); ?>
                                        <button type="submit" class="boton_secundario boton_chico">Guardar</button>
                                    </form>
                                </details>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

        </div>
    <?php endif; ?>

</section>

<script>
    // formularios que piden confirmacion antes de mandarse
    document.querySelectorAll("#panel-torneos .form_confirmar").forEach(form => {
        form.addEventListener("submit", evento => {
            if (!confirm(form.dataset.confirmar)) evento.preventDefault();
        });
    });
</script>
