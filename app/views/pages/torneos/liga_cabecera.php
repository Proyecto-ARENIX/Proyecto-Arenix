<?php
// cabecera de la liga con las pestañas

// variables que vienen de enfrentamientos.php (para que el editor no marque error)
/** @var callable $e */
/** @var array $liga_actual */
/** @var array $destacados */
/** @var array $equipos */
/** @var string $pestana */
/** @var bool $vista_publica */
$ve_limites = !$vista_publica;
$medidores = [
    "equipos"         => "Equipos",
    "jugadores"       => "Jugadores",
    "torneos"         => "Torneos activos",
    "administradores" => "Administradores",
];
$pestanas = ["equipos" => "Equipos", "torneos" => "Torneos y tabla"];
if ($ve_limites) {
    $pestanas += ["plan" => "Plan", "admin" => "Administración"];
}
$nombres_equipos_cabecera = array_column($equipos, null, "id");
$equipo_cabecera = fn($id) => $nombres_equipos_cabecera[$id] ?? ["nombre" => "—", "logo" => ""];
$fecha_corta = fn($fecha) => implode("/", array_slice(array_reverse(explode("-", $fecha)), 0, 2));
?>
<header class="liga_cabecera <?php echo $vista_publica ? "liga_publica liga_neutra" : "liga_" . $e($liga_actual["plan"]); ?> <?php echo !empty($liga_actual["portada"]) ? "liga_cabecera_con_portada" : ""; ?>">
    <?php if (!empty($liga_actual["portada"])): ?>
        <!-- portada de fondo -->
        <img class="liga_cabecera_portada" src="<?php echo $e($liga_actual["portada"]); ?>" alt="">
    <?php endif; ?>
    <div class="liga_cabecera_barra">
        <a class="liga_volver" href="/index.php?pagina=enfrentamientos">‹ Todas las ligas</a>
        <?php if ($ve_limites): ?>
            <button type="button" class="liga_cambiar_fotos" data-ir="admin">Cambiar logo y portada</button>
        <?php endif; ?>
    </div>
    <div class="liga_titulo">
        <?php if (!empty($liga_actual["logo"])): ?>
            <img class="liga_logo liga_logo_grande" src="<?php echo $e($liga_actual["logo"]); ?>" alt="">
        <?php else: ?>
            <span class="liga_logo liga_logo_grande liga_logo_iniciales" aria-hidden="true"><?php echo $e($liga_actual["iniciales"]); ?></span>
        <?php endif; ?>
        <div class="liga_titulo_texto">
            <h1><?php echo $e($liga_actual["nombre"]); ?></h1>
            <p class="liga_datos">
                <span><?php echo $e($liga_actual["nombre_deporte"]); ?></span>
                <span><?php echo (int) $liga_actual["uso"]["equipos"]; ?> equipos</span>
                <?php if (!empty($destacados["torneo"])): ?>
                    <span class="liga_dato_torneo"><?php echo $e($destacados["torneo"]["nombre"]); ?> · en curso</span>
                <?php endif; ?>
            </p>
        </div>
        <?php if ($ve_limites): ?>
            <a class="liga_plan_insignia" href="/index.php?pagina=planes" title="Ver qué incluye cada plan"><?php echo $e($liga_actual["nombre_plan"]); ?></a>
        <?php endif; ?>
    </div>

    <?php if ($vista_publica && (!empty($destacados["lider"]) || !empty($destacados["proximo"]) || !empty($destacados["ultimo"]))): ?>
        <!-- destacados: lider, proximo partido y ultimo resultado -->
        <div class="liga_destacados">
            <?php if (!empty($destacados["lider"])): ?>
                <div class="destacado_tarjeta destacado_lider">
                    <span class="destacado_titulo">Líder</span>
                    <span class="destacado_equipo">
                        <img class="mini_escudo" src="<?php echo $e($destacados["lider"]["logo"]); ?>" alt="">
                        <strong><?php echo $e($destacados["lider"]["nombre"]); ?></strong>
                    </span>
                    <?php
                        // lo que ordena la tabla de ese deporte: puntos, ganados o porcentaje
                        $orden_lider = reglas_tabla($liga_actual["deporte"])["orden"][0];
                        $detalle_lider = [
                            "pts" => (int) $destacados["lider"]["pts"] . " puntos",
                            "g" => ($destacados["lider"]["g"] + $destacados["lider"]["ge"]) . " ganados",
                            "pct" => valor_tabla($destacados["lider"], "pct") . " de ganados",
                        ][$orden_lider];
                    ?>
                    <span class="destacado_detalle"><?php echo $e($detalle_lider); ?> · <?php echo (int) $destacados["lider"]["pj"]; ?> PJ</span>
                </div>
            <?php endif; ?>
            <?php if (!empty($destacados["proximo"])):
                $local_proximo = $equipo_cabecera($destacados["proximo"]["local"]);
                $visitante_proximo = $equipo_cabecera($destacados["proximo"]["visitante"]);
            ?>
                <div class="destacado_tarjeta">
                    <span class="destacado_titulo">Próximo partido</span>
                    <span class="destacado_partido">
                        <img class="mini_escudo" src="<?php echo $e($local_proximo["logo"]); ?>" alt="">
                        <strong><?php echo $e($local_proximo["nombre"]); ?></strong>
                        <em>vs</em>
                        <strong><?php echo $e($visitante_proximo["nombre"]); ?></strong>
                        <img class="mini_escudo" src="<?php echo $e($visitante_proximo["logo"]); ?>" alt="">
                    </span>
                    <span class="destacado_detalle"><?php echo $e($fecha_corta($destacados["proximo"]["fecha"])); ?><?php echo $destacados["proximo"]["hora"] ? " · " . $e($destacados["proximo"]["hora"]) : ""; ?><?php echo $destacados["proximo"]["lugar"] ? " · " . $e($destacados["proximo"]["lugar"]) : ""; ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($destacados["ultimo"])):
                $local_ultimo = $equipo_cabecera($destacados["ultimo"]["local"]);
                $visitante_ultimo = $equipo_cabecera($destacados["ultimo"]["visitante"]);
            ?>
                <div class="destacado_tarjeta">
                    <span class="destacado_titulo">Último resultado</span>
                    <span class="destacado_partido">
                        <img class="mini_escudo" src="<?php echo $e($local_ultimo["logo"]); ?>" alt="">
                        <strong><?php echo $e($local_ultimo["nombre"]); ?></strong>
                        <b class="destacado_marcador"><?php echo (int) $destacados["ultimo"]["goles_local"]; ?> - <?php echo (int) $destacados["ultimo"]["goles_visitante"]; ?></b>
                        <strong><?php echo $e($visitante_ultimo["nombre"]); ?></strong>
                        <img class="mini_escudo" src="<?php echo $e($visitante_ultimo["logo"]); ?>" alt="">
                    </span>
                    <span class="destacado_detalle"><?php echo $e($fecha_corta($destacados["ultimo"]["fecha"])); ?></span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($ve_limites): ?>
        <!-- cuanto usa del plan (solo admins) -->
        <div class="liga_medidores">
            <?php foreach ($medidores as $clave => $nombre):
                $uso = $liga_actual["uso"][$clave];
                $limite = max(1, $liga_actual["limites"][$clave]);
                $porcentaje = min(100, round($uso * 100 / $limite));
            ?>
                <div class="medidor <?php echo $uso >= $limite ? "medidor_lleno" : ""; ?>">
                    <span class="medidor_nombre"><?php echo $e($nombre); ?></span>
                    <span class="medidor_valor"><?php echo (int) $uso; ?> <small>/ <?php echo (int) $liga_actual["limites"][$clave]; ?></small></span>
                    <span class="medidor_barra"><span style="width: <?php echo (int) $porcentaje; ?>%"></span></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <nav class="liga_pestanas" aria-label="Secciones de la liga">
        <?php foreach ($pestanas as $clave => $nombre): ?>
            <button type="button" class="liga_pestana" data-ir="<?php echo $e($clave); ?>" aria-controls="panel-<?php echo $e($clave); ?>" aria-selected="<?php echo $pestana === $clave ? "true" : "false"; ?>">
                <?php echo $e($nombre); ?>
            </button>
        <?php endforeach; ?>
    </nav>
</header>

<script>
    // pestañas: se guarda en la direccion (?pestana=) para volver a la misma
    document.querySelectorAll(".liga_pestana, .liga_cambiar_fotos").forEach(boton => {
        boton.addEventListener("click", () => {
            const elegida = boton.dataset.ir;
            if (boton.classList.contains("liga_cambiar_fotos")) {
                document.querySelector('.liga_pestana[data-ir="admin"]').click();
                document.getElementById("bloqueImagenesLiga").scrollIntoView({ behavior: "smooth", block: "center" });
                return;
            }
            document.querySelectorAll(".liga_pestana").forEach(b => b.setAttribute("aria-selected", b === boton ? "true" : "false"));
            document.querySelectorAll(".pestana_panel").forEach(panel => panel.hidden = panel.dataset.pestana !== elegida);
            const direccion = new URL(location.href);
            direccion.searchParams.set("pestana", elegida);
            history.replaceState(null, "", direccion);
        });
    });
</script>
