<?php
// variables que vienen de controllers/noticias.php (para que el editor no marque error)
/** @var string $mensaje */
/** @var string $tipo_mensaje */
/** @var string $formulario_con_error */
/** @var bool $puede_publicar */
/** @var array $contenido_noticias */
/** @var array $tipos_comunicado */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENIX | Noticias</title>

    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/reglas.css">
    <link rel="stylesheet" href="/public/assets/css/noticias.css">
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
            <a href="/index.php?pagina=noticias" class="activo">
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
    <main class="noticias-pagina">
        <?php if ($mensaje !== ""): ?>
            <p class="noticias-mensaje noticias-<?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></p>
        <?php endif; ?>

        <div class="noticias-columnas">
            <section class="noticias-seccion" aria-labelledby="titulo-noticias">
                <header class="noticias-seccion-encabezado">
                    <h2 id="titulo-noticias">Últimas noticias</h2>
                    <div class="noticias-acciones">
                        <span><?php echo count($contenido_noticias["noticias"]); ?> publicaciones</span>
                        <?php if ($puede_publicar): ?>
                            <button class="noticias-agregar" type="button" id="abrirNuevaNoticia">+ Agregar noticia</button>
                        <?php endif; ?>
                    </div>
                </header>

                <?php if ($puede_publicar): ?>
                    <?php $previo = $formulario_con_error === "noticia" ? $_POST : []; ?>
                    <dialog class="noticia-formulario" id="nuevaNoticia" <?php echo $formulario_con_error === "noticia" ? "data-abrir" : ""; ?>>
                        <form method="post" action="/index.php?pagina=noticias" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_noticias"], ENT_QUOTES, "UTF-8"); ?>">
                            <input type="hidden" name="accion" value="publicar_noticia">

                            <h3>Nueva noticia</h3>

                            <div class="noticia-formulario-fila">
                                <label>
                                    Categoría
                                    <input type="text" name="categoria" maxlength="30" required placeholder="Crónica, Análisis..." value="<?php echo htmlspecialchars($previo["categoria"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                                </label>
                                <label>
                                    Deporte
                                    <input type="text" name="deporte" maxlength="30" required placeholder="Fútbol" value="<?php echo htmlspecialchars($previo["deporte"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                                </label>
                            </div>

                            <label>
                                Título
                                <input type="text" name="titulo" maxlength="90" required value="<?php echo htmlspecialchars($previo["titulo"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
                            </label>

                            <label>
                                Resumen
                                <textarea name="resumen" rows="4" maxlength="300" required><?php echo htmlspecialchars($previo["resumen"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>
                            </label>

                            <label>
                                Imagen (opcional, JPG, PNG o WEBP hasta 2 MB)
                                <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp">
                            </label>

                            <div class="noticia-formulario-botones">
                                <button type="button" class="noticia-cancelar" id="cerrarNuevaNoticia">Cancelar</button>
                                <button type="submit" class="noticias-agregar">Publicar</button>
                            </div>
                        </form>
                    </dialog>
                <?php endif; ?>

                <?php if (count($contenido_noticias["noticias"]) === 0): ?>
                    <p class="noticias-vacio">Todavía no hay noticias.</p>
                <?php endif; ?>

                <div class="noticias-grilla">
                    <?php foreach ($contenido_noticias["noticias"] as $noticia): ?>
                        <article class="noticia-ficha">
                            <div class="noticia-visual">
                                <span class="noticia-deporte"><?php echo htmlspecialchars($noticia["deporte"], ENT_QUOTES, "UTF-8"); ?></span>
                                <?php if ($puede_publicar): ?>
                                    <form class="noticia-eliminar" method="post" action="/index.php?pagina=noticias" onsubmit="return confirm('¿Eliminar esta noticia? No se puede deshacer.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_noticias"], ENT_QUOTES, "UTF-8"); ?>">
                                        <input type="hidden" name="accion" value="eliminar_noticia">
                                        <input type="hidden" name="id" value="<?php echo $noticia["id"]; ?>">
                                        <button type="submit">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                                <img src="<?php echo htmlspecialchars($noticia["imagen"], ENT_QUOTES, "UTF-8"); ?>" alt="<?php echo htmlspecialchars($noticia["alt"], ENT_QUOTES, "UTF-8"); ?>">
                            </div>
                            <div class="noticia-contenido">
                                <div class="noticia-meta">
                                    <span><?php echo htmlspecialchars($noticia["categoria"], ENT_QUOTES, "UTF-8"); ?></span>
                                    <time><?php echo htmlspecialchars($noticia["fecha"], ENT_QUOTES, "UTF-8"); ?></time>
                                </div>
                                <h3><?php echo htmlspecialchars($noticia["titulo"], ENT_QUOTES, "UTF-8"); ?></h3>
                                <p><?php echo htmlspecialchars($noticia["resumen"], ENT_QUOTES, "UTF-8"); ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="noticias-comunicados" aria-labelledby="titulo-comunicados">
                <header class="noticias-seccion-encabezado">
                    <div>
                        <p class="noticias-antetitulo">INFORMACIÓN DEL TORNEO</p>
                        <h2 id="titulo-comunicados">Comunicados</h2>
                    </div>
                    <div class="noticias-acciones">
                        <span><?php echo count($contenido_noticias["comunicados"]); ?> avisos</span>
                        <?php if ($puede_publicar): ?>
                            <button class="noticias-agregar" type="button" id="abrirNuevoComunicado">+ Agregar</button>
                        <?php endif; ?>
                    </div>
                </header>

                <?php if ($puede_publicar): ?>
                    <?php $con_error = $formulario_con_error === "comunicado"; ?>
                    <dialog class="noticia-formulario" id="formularioComunicado" <?php echo $con_error ? "data-abrir" : ""; ?>>
                        <form method="post" action="/index.php?pagina=noticias">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_noticias"], ENT_QUOTES, "UTF-8"); ?>">
                            <input type="hidden" name="accion" value="guardar_comunicado">
                            <input type="hidden" name="id" value="<?php echo $con_error ? htmlspecialchars($_POST["id"] ?? "", ENT_QUOTES, "UTF-8") : ""; ?>">

                            <h3 data-titulo-formulario><?php echo $con_error && !empty($_POST["id"]) ? "Editar comunicado" : "Nuevo comunicado"; ?></h3>

                            <div class="noticia-formulario-fila">
                                <label>
                                    Tipo
                                    <select name="tipo" required>
                                        <?php foreach ($tipos_comunicado as $clave_tipo => $nombre_tipo): ?>
                                            <option value="<?php echo $clave_tipo; ?>" <?php echo $con_error && ($_POST["tipo"] ?? "") === $clave_tipo ? "selected" : ""; ?>><?php echo htmlspecialchars($nombre_tipo, ENT_QUOTES, "UTF-8"); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>
                                    Contexto
                                    <input type="text" name="contexto" maxlength="40" required placeholder="Liga Apertura" value="<?php echo $con_error ? htmlspecialchars($_POST["contexto"] ?? "", ENT_QUOTES, "UTF-8") : ""; ?>">
                                </label>
                            </div>

                            <label>
                                Título
                                <input type="text" name="titulo" maxlength="90" required value="<?php echo $con_error ? htmlspecialchars($_POST["titulo"] ?? "", ENT_QUOTES, "UTF-8") : ""; ?>">
                            </label>

                            <label>
                                Texto
                                <textarea name="texto" rows="4" maxlength="300" required><?php echo $con_error ? htmlspecialchars($_POST["texto"] ?? "", ENT_QUOTES, "UTF-8") : ""; ?></textarea>
                            </label>

                            <div class="noticia-formulario-botones">
                                <button type="button" class="noticia-cancelar" data-cerrar-comunicado>Cancelar</button>
                                <button type="submit" class="noticias-agregar">Guardar</button>
                            </div>
                        </form>
                    </dialog>
                <?php endif; ?>

                <?php if (count($contenido_noticias["comunicados"]) === 0): ?>
                    <p class="noticias-vacio">No hay comunicados por ahora.</p>
                <?php else: ?>
                <div class="comunicados-carrusel" data-comunicados-carousel>
                    <div class="comunicados-diapositivas" aria-live="off">
                        <?php foreach ($contenido_noticias["comunicados"] as $indice => $comunicado): ?>
                            <article
                                class="comunicado comunicado-<?php echo htmlspecialchars($comunicado["tipo"], ENT_QUOTES, "UTF-8"); ?><?php echo $indice === 0 ? " activo" : ""; ?>"
                                data-comunicado-slide
                                aria-roledescription="diapositiva"
                                aria-label="Comunicado <?php echo $indice + 1; ?> de <?php echo count($contenido_noticias["comunicados"]); ?>"
                                <?php echo $indice > 0 ? "hidden" : ""; ?>>
                                <div class="comunicado-meta">
                                    <span><?php echo htmlspecialchars($comunicado["etiqueta"], ENT_QUOTES, "UTF-8"); ?></span>
                                    <time><?php echo htmlspecialchars($comunicado["fecha"], ENT_QUOTES, "UTF-8"); ?></time>
                                </div>
                                <h3><?php echo htmlspecialchars($comunicado["titulo"], ENT_QUOTES, "UTF-8"); ?></h3>
                                <p><?php echo htmlspecialchars($comunicado["texto"], ENT_QUOTES, "UTF-8"); ?></p>
                                <?php if ($puede_publicar): ?>
                                    <div class="comunicado-botones">
                                    <button
                                        class="comunicado-editar"
                                        type="button"
                                        data-editar-comunicado
                                        data-id="<?php echo $comunicado["id"]; ?>"
                                        data-tipo="<?php echo htmlspecialchars($comunicado["tipo"], ENT_QUOTES, "UTF-8"); ?>"
                                        data-contexto="<?php echo htmlspecialchars($comunicado["contexto"], ENT_QUOTES, "UTF-8"); ?>"
                                        data-titulo="<?php echo htmlspecialchars($comunicado["titulo"], ENT_QUOTES, "UTF-8"); ?>"
                                        data-texto="<?php echo htmlspecialchars($comunicado["texto"], ENT_QUOTES, "UTF-8"); ?>">Editar</button>
                                    <form method="post" action="/index.php?pagina=noticias" onsubmit="return confirm('¿Eliminar este comunicado? No se puede deshacer.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_noticias"], ENT_QUOTES, "UTF-8"); ?>">
                                        <input type="hidden" name="accion" value="eliminar_comunicado">
                                        <input type="hidden" name="id" value="<?php echo $comunicado["id"]; ?>">
                                        <button class="comunicado-editar comunicado-eliminar" type="submit">Eliminar</button>
                                    </form>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="comunicados-controles" aria-label="Controles de comunicados">
                        <button class="comunicado-flecha" type="button" data-comunicado-anterior aria-label="Comunicado anterior" title="Anterior">&#8592;</button>
                        <div class="comunicados-puntos" role="group" aria-label="Elegir comunicado">
                            <?php foreach ($contenido_noticias["comunicados"] as $indice => $comunicado): ?>
                                <button
                                    class="comunicado-punto<?php echo $indice === 0 ? " activo" : ""; ?>"
                                    type="button"
                                    data-comunicado-ir="<?php echo $indice; ?>"
                                    aria-label="Mostrar comunicado <?php echo $indice + 1; ?>"
                                    aria-current="<?php echo $indice === 0 ? "true" : "false"; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <span class="comunicado-contador"><span data-comunicado-contador>01</span> / <?php echo count($contenido_noticias["comunicados"]); ?></span>
                        <button class="comunicado-pausa" type="button" data-comunicado-pausa aria-pressed="false">Pausar</button>
                        <button class="comunicado-flecha" type="button" data-comunicado-siguiente aria-label="Siguiente comunicado" title="Siguiente">&#8594;</button>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
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
    // formulario de nueva noticia (solo existe si el usuario puede publicar)
    (() => {
        const ventana = document.getElementById("nuevaNoticia");
        if (!ventana) return;

        document.getElementById("abrirNuevaNoticia").addEventListener("click", () => ventana.showModal());
        document.getElementById("cerrarNuevaNoticia").addEventListener("click", () => ventana.close());

        // si hubo un error al publicar, se vuelve a abrir con lo que ya se escribio
        if (ventana.hasAttribute("data-abrir")) ventana.showModal();
    })();

    // el mismo formulario sirve para agregar y para editar comunicados
    (() => {
        const ventana = document.getElementById("formularioComunicado");
        if (!ventana) return;

        const formulario = ventana.querySelector("form");
        const titulo = ventana.querySelector("[data-titulo-formulario]");

        function abrir(datos) {
            formulario.elements.id.value = datos.id || "";
            formulario.elements.tipo.value = datos.tipo || "aviso";
            formulario.elements.contexto.value = datos.contexto || "";
            formulario.elements.titulo.value = datos.titulo || "";
            formulario.elements.texto.value = datos.texto || "";
            titulo.textContent = datos.id ? "Editar comunicado" : "Nuevo comunicado";
            ventana.showModal();
        }

        document.getElementById("abrirNuevoComunicado").addEventListener("click", () => abrir({}));
        ventana.querySelector("[data-cerrar-comunicado]").addEventListener("click", () => ventana.close());

        document.querySelectorAll("[data-editar-comunicado]").forEach((boton) => {
            boton.addEventListener("click", () => abrir(boton.dataset));
        });

        if (ventana.hasAttribute("data-abrir")) ventana.showModal();
    })();

    (() => {
        const carousel = document.querySelector("[data-comunicados-carousel]");
        if (!carousel) return;

        const slides = Array.from(carousel.querySelectorAll("[data-comunicado-slide]"));
        if (slides.length === 0) return;
        const dots = Array.from(carousel.querySelectorAll("[data-comunicado-ir]"));
        const counter = carousel.querySelector("[data-comunicado-contador]");
        const botonAnterior = carousel.querySelector("[data-comunicado-anterior]");
        const botonSiguiente = carousel.querySelector("[data-comunicado-siguiente]");
        const botonPausa = carousel.querySelector("[data-comunicado-pausa]");
        const menosMovimiento = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        let indiceActivo = 0;
        let timer = null;
        let pausadoAMano = false;
        let conMouse = false;
        let conFoco = false;

        function mostrarComunicado(index) {
            indiceActivo = (index + slides.length) % slides.length;

            slides.forEach((slide, indiceComunicado) => {
                const active = indiceComunicado === indiceActivo;
                slide.hidden = !active;
                slide.classList.toggle("activo", active);
            });

            dots.forEach((dot, indicePunto) => {
                const active = indicePunto === indiceActivo;
                dot.classList.toggle("activo", active);
                dot.setAttribute("aria-current", active ? "true" : "false");
            });

            counter.textContent = String(indiceActivo + 1).padStart(2, "0");
        }

        function detenerRotacion() {
            window.clearInterval(timer);
            timer = null;
        }

        function iniciarRotacion() {
            detenerRotacion();
            if (menosMovimiento || pausadoAMano || conMouse || conFoco || document.hidden) return;
            timer = window.setInterval(() => mostrarComunicado(indiceActivo + 1), 6500);
        }

        botonAnterior.addEventListener("click", () => {
            mostrarComunicado(indiceActivo - 1);
            iniciarRotacion();
        });

        botonSiguiente.addEventListener("click", () => {
            mostrarComunicado(indiceActivo + 1);
            iniciarRotacion();
        });

        dots.forEach((dot) => {
            dot.addEventListener("click", () => {
                mostrarComunicado(Number(dot.dataset.comunicadoIr));
                iniciarRotacion();
            });
        });

        botonPausa.addEventListener("click", () => {
            pausadoAMano = !pausadoAMano;
            botonPausa.textContent = pausadoAMano ? "Reanudar" : "Pausar";
            botonPausa.setAttribute("aria-pressed", pausadoAMano ? "true" : "false");
            iniciarRotacion();
        });

        carousel.addEventListener("mouseenter", () => {
            conMouse = true;
            detenerRotacion();
        });

        carousel.addEventListener("mouseleave", () => {
            conMouse = false;
            iniciarRotacion();
        });

        carousel.addEventListener("focusin", () => {
            conFoco = true;
            detenerRotacion();
        });

        carousel.addEventListener("focusout", (event) => {
            if (!carousel.contains(event.relatedTarget)) {
                conFoco = false;
                iniciarRotacion();
            }
        });

        document.addEventListener("visibilitychange", iniciarRotacion);
        iniciarRotacion();
    })();
</script>
</body>
</html>



