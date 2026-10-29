<?php
$datos_planes = require __DIR__ . "/../../models/planes.php";
$planes = $datos_planes["planes"];
$extras = $datos_planes["extras"];
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENIX | Planes</title>

    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/reglas.css">
    <link rel="stylesheet" href="/public/assets/css/planes.css">
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
            <a href="/index.php?pagina=eventos">
                <img src="/public/assets/images/nav-eventos.png" alt="">
                <span>EVENTOS</span>
            </a>
            <a href="/index.php?pagina=noticias">
                <img src="/public/assets/images/nav-noticias.png" alt="">
                <span>NOTICIAS</span>
            </a>
            <a href="/index.php?pagina=planes" class="activo">
                <img src="/public/assets/images/logo_planes_transparente.png" alt="">
                <span>PLANES</span>
            </a>
        </div>

        <?php require __DIR__ . "/../partials/menu-perfil.php"; ?>

    </nav>

    <!-- contenido -->
    <main class="planes">

        <header class="planes_cabecera">
            <p class="planes_etiqueta">PLANES ARENIX</p>
            <h1>Elige el plan para tu liga o club</h1>
            <p>Todos los planes se pagan por mes e incluyen <strong>10 jugadores adicionales gratis</strong>. Si necesitas más, sumas equipos o jugadores extra cuando quieras.</p>
        </header>

        <section class="planes_lista" aria-label="Planes disponibles">
            <?php foreach ($planes as $plan): ?>
                <article class="plan_tarjeta plan_<?php echo $e($plan["color"]); ?>" aria-labelledby="plan-<?php echo $e($plan["id"]); ?>">
                    <header class="plan_tarjeta_cabecera">
                        <span class="plan_punto" aria-hidden="true"></span>
                        <h2 id="plan-<?php echo $e($plan["id"]); ?>"><?php echo $e($plan["nombre"]); ?></h2>
                        <p class="plan_resumen"><?php echo $e($plan["resumen"]); ?></p>
                        <p class="plan_precio">
                            <span class="plan_moneda">USD</span>
                            <strong><?php echo (int) $plan["precio"]; ?></strong>
                            <span class="plan_periodo">/mes</span>
                        </p>
                    </header>

                    <dl class="plan_limites">
                        <?php foreach ($plan["limites"] as $nombre => $valor): ?>
                            <div>
                                <dt><?php echo $e($nombre); ?></dt>
                                <dd><?php echo $e($valor); ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>

                    <ul class="plan_incluye">
                        <?php foreach ($plan["incluye"] as $item): ?>
                            <li><?php echo $e($item); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <a class="plan_boton" href="/index.php?pagina=contratar&amp;plan=<?php echo urlencode($plan["id"]); ?>">Quiero el <?php echo $e($plan["nombre"]); ?></a>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="planes_extras" aria-labelledby="titulo-extras">
            <h2 id="titulo-extras">Extras</h2>
            <p>Para cualquier plan, cuando te quedes sin lugar.</p>
            <ul>
                <?php foreach ($extras as $extra): ?>
                    <li>
                        <span><?php echo $e($extra["nombre"]); ?></span>
                        <strong><?php echo $e($extra["precio"]); ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

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

</body>
</html>
