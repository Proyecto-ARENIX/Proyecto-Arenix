<?php
require_once __DIR__ . "/../../models/reglamentos.php";

$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
$reglamentos = listar_reglamentos();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENIX | Reglamentos</title>

    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/reglas.css">
    <link rel="stylesheet" href="/public/assets/css/reglamentos.css">
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

    <!-- contenido -->
    <main class="reglamentos-pagina">
        <header class="reglamentos-encabezado">
            <p class="reglamentos-antetitulo">Ligas</p>
            <h1>Reglamentos</h1>
            <p>Las reglas básicas de cada deporte que se puede jugar en las ligas de ARENIX y cómo se arma su tabla de posiciones.</p>
        </header>

        <nav class="reglamentos-indice" aria-label="Deportes">
            <?php foreach ($reglamentos as $reglamento): ?>
                <a href="#<?php echo $e($reglamento["id"]); ?>"><?php echo $e($reglamento["nombre"]); ?></a>
            <?php endforeach; ?>
        </nav>

        <?php foreach ($reglamentos as $reglamento): ?>
            <section class="reglamento" id="<?php echo $e($reglamento["id"]); ?>">
                <header class="reglamento-cabecera">
                    <h2><?php echo $e($reglamento["nombre"]); ?></h2>
                    <?php if ($reglamento["enlace"]): ?>
                        <a href="<?php echo $e($reglamento["enlace"]); ?>" target="_blank" rel="noopener">Reglamento oficial · <?php echo $e($reglamento["federacion"]); ?></a>
                    <?php else: ?>
                        <span><?php echo $e($reglamento["federacion"]); ?></span>
                    <?php endif; ?>
                </header>

                <dl class="reglamento-datos">
                    <div><dt>Jugadores</dt><dd><?php echo $e($reglamento["jugadores"]); ?></dd></div>
                    <div><dt>Duración</dt><dd><?php echo $e($reglamento["duracion"]); ?></dd></div>
                </dl>

                <ul class="reglamento-reglas">
                    <?php foreach ($reglamento["reglas"] as $regla): ?>
                        <li><?php echo $e($regla); ?></li>
                    <?php endforeach; ?>
                </ul>

                <p class="reglamento-tabla"><strong>Tabla de posiciones en ARENIX:</strong> <?php echo $e($reglamento["tabla"]); ?></p>
            </section>
        <?php endforeach; ?>
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
