<?php
// pestaña plan: que incluye el plan de la liga y sus limites

// variables que vienen de enfrentamientos.php (para que el editor no marque error)
/** @var callable $e */
/** @var array $liga_actual */
/** @var array $planes_liga */
/** @var array $funciones_plan */
/** @var string $pestana */
$plan_liga = $planes_liga[$liga_actual["plan"]];
$precios = array_column((require __DIR__ . "/../../../models/planes.php")["planes"], "precio", "id");
$plan_minimo = fn($clave) => $planes_liga[$funciones_plan[$clave][1]]["nombre"];
?>
<section class="pestana_panel panel_plan" id="panel-plan" data-pestana="plan" aria-labelledby="titulo-plan" <?php echo $pestana === "plan" ? "" : "hidden"; ?>>

    <div class="seccion_cabecera seccion_cabecera_acciones">
        <div>
            <h2 id="titulo-plan">Plan de la liga: <?php echo $e($plan_liga["nombre"]); ?></h2>
            <p>USD <?php echo (int) ($precios[$liga_actual["plan"]] ?? 0); ?>/mes · <a href="/index.php?pagina=planes">Comparar planes</a></p>
        </div>
    </div>

    <div class="plan_liga_grilla">
        <section class="bloque_torneo" aria-labelledby="titulo-limites">
            <h4 id="titulo-limites">Límites</h4>
            <dl class="estadisticas_basicas">
                <div><dt>Equipos</dt><dd><?php echo (int) $liga_actual["limites"]["equipos"]; ?>
                    <?php if ($liga_actual["equipos_extra"] > 0): ?><small>(incluye <?php echo (int) $liga_actual["equipos_extra"]; ?> adicionales)</small><?php endif; ?></dd></div>
                <div><dt>Jugadores</dt><dd><?php echo (int) $liga_actual["limites"]["jugadores"]; ?>
                    <small>(<?php echo (int) $plan_liga["jugadores"]; ?> + 10 gratis<?php echo $liga_actual["jugadores_extra"] > 0 ? " + " . (int) $liga_actual["jugadores_extra"] . " adicionales" : ""; ?>)</small></dd></div>
                <div><dt>Torneos activos</dt><dd><?php echo (int) $liga_actual["limites"]["torneos"]; ?></dd></div>
                <div><dt>Administradores</dt><dd><?php echo (int) $liga_actual["limites"]["administradores"]; ?></dd></div>
            </dl>
            <p class="campo_ayuda">Extras para cualquier plan: equipo adicional USD 10/mes · jugador adicional USD 2/mes.</p>
        </section>

        <section class="bloque_torneo" aria-labelledby="titulo-funciones">
            <h4 id="titulo-funciones">Qué incluye</h4>
            <ul class="funciones_plan">
                <?php foreach ($funciones_plan as $clave => [$nombre, $minimo, $disponible]):
                    $incluida = plan_incluye($liga_actual["plan"], $clave);
                ?>
                    <li class="<?php echo $incluida ? "funcion_incluida" : "funcion_bloqueada"; ?>">
                        <span class="funcion_icono" aria-hidden="true"></span>
                        <span class="funcion_nombre"><?php echo $e($nombre); ?></span>
                        <?php if (!$incluida): ?>
                            <small>Desde el <?php echo $e($plan_minimo($clave)); ?></small>
                        <?php elseif (!$disponible): ?>
                            <small>Incluido · próximamente</small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>

</section>
