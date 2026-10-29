<?php
// variables que vienen de controllers/inicio.php (para que el editor no marque error)
/** @var string $mensaje */
/** @var string $tipo_mensaje */
/** @var bool $puede_editar_portada */
/** @var array $textos */
/** @var array $proximos */
/** @var array $resultados_recientes */
/** @var array $noticias_recientes */
/** @var array $lideres */
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");

// boton "editar" de cada cuadro (solo admin y organizador)
$boton_editar = fn($cuadro) => $puede_editar_portada
    ? '<button type="button" class="ficha-editar" data-editar-cuadro="' . $e($cuadro) . '" title="Editar este cuadro">Editar</button>'
    : "";

// cabecera de cada cuadro: titulo, boton editar y enlace
$cabeza = fn($cuadro, $pagina) => '<header class="ficha-cabeza"><h3>' . $e($textos[$cuadro]["titulo"]) . '</h3>'
    . '<div class="ficha-acciones">' . $boton_editar($cuadro)
    . '<a href="/index.php?pagina=' . $e($pagina) . '">' . $e($textos[$cuadro]["enlace"]) . '</a></div></header>';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENIX</title>

    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/reglas.css">
    <link rel="stylesheet" href="/public/assets/css/inicio.css">
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
            <a href="/index.php?pagina=planes">
                <img src="/public/assets/images/logo_planes_transparente.png" alt="">
                <span>PLANES</span>
            </a>
        </div>

        <?php require __DIR__ . "/../partials/menu-perfil.php"; ?>

    </nav>

    <!-- contenido -->
    <main class="inicio">

        <?php if ($mensaje !== ""): ?>
            <p class="inicio-mensaje inicio-<?php echo $tipo_mensaje; ?>"><?php echo $e($mensaje); ?></p>
        <?php endif; ?>

        <!-- bienvenida -->
        <section class="bienvenida">
            <?php echo $boton_editar("bienvenida"); ?>
            <div class="bienvenida-texto">
                <p class="bienvenida-hola"><?php echo $e($textos["bienvenida"]["saludo"]); ?></p>
                <h2 class="bienvenida-titulo">ARE<span>NIX</span></h2>
                <p class="bienvenida-bajada"><?php echo $e($textos["bienvenida"]["bajada"]); ?></p>
                <a href="/index.php?pagina=<?php echo $e($textos["bienvenida"]["boton_pagina"]); ?>" class="boton-principal"><?php echo $e($textos["bienvenida"]["boton"]); ?> &#8250;</a>
            </div>
            <div class="bienvenida-imagen">
                <img src="/public/assets/images/logo a.png" alt="ARENIX">
            </div>
        </section>

        <!-- fila 1 -->
        <section class="panel">

            <!-- Enfrentamientos -->
            <article class="ficha">
                <?php echo $cabeza("enfrentamientos", "enfrentamientos"); ?>

                <?php if (count($proximos) === 0): ?>
                    <p class="ficha-vacia">No hay partidos programados.</p>
                <?php endif; ?>
                <?php foreach (array_slice($proximos, 0, (int) $textos["enfrentamientos"]["cantidad"]) as $partido): ?>
                    <div class="vs-fila">
                        <div class="equipo">
                            <img class="escudo" src="<?php echo $e($partido["local"]["logo"]); ?>" alt="">
                            <span><?php echo $e($partido["local"]["nombre"]); ?></span>
                        </div>
                        <div class="vs-info">
                            <small><?php echo $e($partido["liga"]); ?></small>
                            <strong>VS</strong>
                            <small><?php echo $e(ucfirst(mb_strtolower($partido["mes"], "UTF-8")) . " " . $partido["dia"] . ($partido["hora"] ? " • " . $partido["hora"] : "")); ?></small>
                            <small><?php echo $e($partido["lugar"] ?: "Lugar a confirmar"); ?></small>
                        </div>
                        <div class="equipo equipo-der">
                            <span><?php echo $e($partido["visitante"]["nombre"]); ?></span>
                            <img class="escudo" src="<?php echo $e($partido["visitante"]["logo"]); ?>" alt="">
                        </div>
                    </div>
                <?php endforeach; ?>
            </article>

            <!-- Resultados -->
            <article class="ficha">
                <?php echo $cabeza("resultados", "resultados"); ?>

                <?php if (count($resultados_recientes) === 0): ?>
                    <p class="ficha-vacia">Todavía no hay resultados cargados.</p>
                <?php endif; ?>
                <?php foreach ($resultados_recientes as $partido): ?>
                    <div class="res-fila" title="<?php echo $e($partido["liga"]); ?>">
                        <span class="fecha"><?php echo $e($partido["dia"]); ?><br><?php echo $e($partido["mes"]); ?></span>
                        <span class="res-eq"><img class="escudo mini" src="<?php echo $e($partido["local"]["logo"]); ?>" alt=""> <?php echo $e($partido["local"]["nombre"]); ?></span>
                        <span class="marcador"><?php echo $e($partido["marcador"]); ?></span>
                        <span class="res-eq der"><?php echo $e($partido["visitante"]["nombre"]); ?> <img class="escudo mini" src="<?php echo $e($partido["visitante"]["logo"]); ?>" alt=""></span>
                    </div>
                <?php endforeach; ?>

                <a href="/index.php?pagina=resultados" class="ficha-pie"><?php echo $e($textos["resultados"]["pie"]); ?></a>
            </article>

            <!-- Próximos Horarios -->
            <article class="ficha">
                <?php echo $cabeza("horarios", "horarios"); ?>

                <?php if (count($proximos) === 0): ?>
                    <p class="ficha-vacia">No hay horarios cargados.</p>
                <?php endif; ?>
                <?php foreach (array_slice($proximos, 0, (int) $textos["horarios"]["cantidad"]) as $partido): ?>
                    <div class="hora-fila">
                        <span class="cuando"><?php echo $e($partido["dia"] . " " . ucfirst(mb_strtolower($partido["mes"], "UTF-8")) . " • " . ($partido["hora"] ?: "hora a confirmar") . " · " . $partido["deporte"]); ?></span>
                        <span class="par">
                            <img class="escudo mini" src="<?php echo $e($partido["local"]["logo"]); ?>" alt=""> <?php echo $e($partido["local"]["nombre"]); ?>
                            <em>vs</em>
                            <?php echo $e($partido["visitante"]["nombre"]); ?> <img class="escudo mini" src="<?php echo $e($partido["visitante"]["logo"]); ?>" alt="">
                        </span>
                    </div>
                <?php endforeach; ?>

                <a href="/index.php?pagina=horarios" class="ficha-pie"><?php echo $e($textos["horarios"]["pie"]); ?></a>
            </article>
        </section>

        <!-- fila 2 -->
        <section class="panel">

            <!-- Eventos destacados -->
            <article class="ficha">
                <?php echo $cabeza("evento", "eventos"); ?>

                <div class="evento">
                    <div class="evento-imagen">
                        <?php if ($textos["evento"]["foto"] !== ""): ?>
                            <img src="<?php echo $e($textos["evento"]["foto"]); ?>" alt="<?php echo $e($textos["evento"]["nombre"]); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="evento-info">
                        <h4><?php echo $e($textos["evento"]["nombre"]); ?></h4>
                        <p class="evento-datos"><?php echo $e($textos["evento"]["fecha"]); ?><br><?php echo $e($textos["evento"]["lugar"]); ?></p>
                        <p><?php echo $e($textos["evento"]["texto"]); ?></p>
                    </div>
                </div>
            </article>

            <!-- Noticias -->
            <article class="ficha">
                <?php echo $cabeza("noticias", "noticias"); ?>

                <?php if (count($noticias_recientes) === 0): ?>
                    <p class="ficha-vacia">Todavía no hay noticias.</p>
                <?php endif; ?>
                <?php foreach ($noticias_recientes as $noticia): ?>
                    <a href="/index.php?pagina=noticias" class="noticia">
                        <div class="noticia-imagen"><img src="<?php echo $e($noticia["imagen"]); ?>" alt=""></div>
                        <div class="noticia-info">
                            <h4><?php echo $e($noticia["titulo"]); ?></h4>
                            <p><?php echo $e($noticia["resumen"]); ?></p>
                            <small><?php echo $e($noticia["categoria"] . " · " . $noticia["fecha"]); ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </article>

            <!-- lideres de cada liga -->
            <article class="ficha">
                <?php echo $cabeza("lideres", "enfrentamientos"); ?>

                <?php if (count($lideres) === 0): ?>
                    <p class="ficha-vacia">Todavía no hay partidos jugados en los torneos en curso.</p>
                <?php endif; ?>
                <ol class="ranking">
                    <?php foreach ($lideres as $lider): ?>
                        <li>
                            <img class="escudo mini" src="<?php echo $e($lider["logo"]); ?>" alt="">
                            <span class="nombre">
                                <?php echo $e($lider["nombre"]); ?>
                                <small><?php echo $e($lider["liga"] . " · " . $lider["deporte"]); ?></small>
                            </span>
                            <span class="nota"><?php echo $e($lider["detalle"]); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <a href="/index.php?pagina=enfrentamientos" class="ficha-pie"><?php echo $e($textos["lideres"]["pie"]); ?></a>
            </article>
        </section>

        <?php if ($puede_editar_portada): ?>
            <!-- una ventana por cuadro, se abre con su boton "editar" -->
            <?php foreach (cuadros_portada() as $cuadro => [$nombre_cuadro, $campos]): ?>
                <dialog class="inicio-formulario" data-ventana-cuadro="<?php echo $cuadro; ?>" <?php echo $cuadro_con_error === $cuadro ? "data-abrir" : ""; ?>>
                    <form method="post" action="/index.php?pagina=inicio" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_inicio"]); ?>">
                        <input type="hidden" name="cuadro" value="<?php echo $cuadro; ?>">
                        <h3>Editar: <?php echo $e($nombre_cuadro); ?></h3>
                        <?php if (isset($campos["cantidad"])): ?>
                            <p class="inicio-formulario-ayuda">Lo que aparece en el cuadro se actualiza solo con lo que se carga en la plataforma. Acá se cambian los textos y cuántos se muestran.</p>
                        <?php elseif ($cuadro === "evento"): ?>
                            <p class="inicio-formulario-ayuda">Este cuadro no se actualiza solo: muestra lo que pongas acá hasta que lo vuelvas a cambiar.</p>
                        <?php endif; ?>

                        <?php foreach ($campos as $campo => [$nombre, , $maximo, $tipo]): ?>
                            <label>
                                <?php echo $e($nombre); ?>
                                <?php if ($tipo === "foto"): ?>
                                    <?php if ($textos[$cuadro][$campo] !== ""): ?>
                                        <img class="inicio-foto-actual" src="<?php echo $e($textos[$cuadro][$campo]); ?>" alt="Foto actual">
                                    <?php endif; ?>
                                    <input type="file" name="<?php echo $campo; ?>" accept="image/jpeg,image/png,image/webp">
                                    <small class="inicio-formulario-ayuda">JPG, PNG o WEBP de hasta 2 MB. Si no elegís ninguna, queda la que está.</small>
                                </label>
                                <?php if ($textos[$cuadro][$campo] !== ""): ?>
                                    <label class="inicio-casilla">
                                        <input type="checkbox" name="quitar_<?php echo $campo; ?>" value="1"> Quitar la foto
                                    </label>
                                <?php endif; ?>
                                <?php continue; ?>
                                <?php elseif ($tipo === "pagina"): ?>
                                    <select name="<?php echo $campo; ?>" required>
                                        <?php foreach (paginas_boton_portada() as $pagina => $titulo): ?>
                                            <option value="<?php echo $pagina; ?>" <?php echo $textos[$cuadro][$campo] === $pagina ? "selected" : ""; ?>><?php echo $e($titulo); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($tipo === "numero"): ?>
                                    <input type="number" name="<?php echo $campo; ?>" min="1" max="<?php echo $maximo; ?>" required value="<?php echo $e($textos[$cuadro][$campo]); ?>">
                                <?php elseif ($tipo === "largo"): ?>
                                    <textarea name="<?php echo $campo; ?>" rows="2" maxlength="<?php echo $maximo; ?>" required><?php echo $e($textos[$cuadro][$campo]); ?></textarea>
                                <?php else: ?>
                                    <input type="text" name="<?php echo $campo; ?>" maxlength="<?php echo $maximo; ?>" required value="<?php echo $e($textos[$cuadro][$campo]); ?>">
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>

                        <div class="inicio-formulario-botones">
                            <button type="button" class="inicio-cancelar" data-cerrar-cuadro>Cancelar</button>
                            <button type="submit" class="boton-principal">Guardar</button>
                        </div>
                    </form>
                </dialog>
            <?php endforeach; ?>
        <?php endif; ?>

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
    // cada boton "editar" abre la ventana de su cuadro (solo admin y organizador)
    document.querySelectorAll("[data-editar-cuadro]").forEach((boton) => {
        const ventana = document.querySelector('[data-ventana-cuadro="' + boton.dataset.editarCuadro + '"]');
        if (ventana) boton.addEventListener("click", () => ventana.showModal());
    });

    document.querySelectorAll("[data-ventana-cuadro]").forEach((ventana) => {
        ventana.querySelector("[data-cerrar-cuadro]").addEventListener("click", () => ventana.close());
        // si hubo un error al guardar, se vuelve a abrir con lo que ya se escribio
        if (ventana.hasAttribute("data-abrir")) ventana.showModal();
    });
</script>
</body>
</html>



