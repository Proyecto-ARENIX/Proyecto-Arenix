<?php
require_once __DIR__ . "/../../models/deportes.php";

// solo organizadores y admins cargan y borran mediciones
$puede_editar = usuario_tiene_rol("organizador");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENIX | Habilidades del Jugador</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/reglas.css">
    <link rel="stylesheet" href="/public/assets/css/habilidades.css">
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

    <main>

        <div class="lab-fisica">

            <header class="cabecera-lab">
                <p class="ruta-lab"><a href="/index.php?pagina=inicio">ARENIX</a> · <a href="/index.php?pagina=resultados">Resultados</a> · módulo de laboratorio</p>
                <h1>Habilidades del jugador: análisis físico del rendimiento</h1>
                <p class="bajada">Prueba de velocidad sobre una distancia conocida, aplicable a cualquier disciplina: fútbol, básquetbol, vóley, rugby, handball y más. Se ingresan masa, distancia y tiempo de cada repetición, y el módulo calcula velocidad, aceleración, fuerza, trabajo, energía cinética y potencia aplicando el modelo de movimiento rectilíneo uniformemente acelerado con partida desde el reposo. En la implementación final, estas mediciones se obtienen del equipo <strong>Veo Analytics 2</strong>: la cámara Veo Cam 3 filma la cancha completa y su IA calcula automáticamente velocidad máxima, distancia recorrida y sprints de cada jugador, sin GPS ni chalecos.</p>
                <div class="pista">
                    <span class="salida">salida</span>
                    <span class="llegada">llegada</span>
                    <div class="carril"></div>
                    <span class="marca" style="left:0">0 m</span>
                    <span class="marca" style="left:25%">5</span>
                    <span class="marca" style="left:50%">10</span>
                    <span class="marca" style="left:75%">15</span>
                    <span class="marca" style="left:100%">20 m</span>
                </div>
            </header>

            <div class="lab-contenido">

                <section class="veo-demo">
                    <h2>Cómo mide Veo AI</h2>
                    <p class="sub">La cámara Veo Cam 3 filma todo el campo y su inteligencia artificial detecta y sigue a cada jugador automáticamente, marcándolo en video para calcular su velocidad, distancia recorrida y sprints sin sensores encima.</p>
                    <figure>
                        <figcaption>Veo AI identifica a cada jugador (marcado en verde) y sigue su movimiento cuadro a cuadro para obtener sus métricas.</figcaption>
                        <img src="/public/assets/videos/VeoAI.gif" alt="Demostración de Veo AI: cada jugador queda resaltado en verde mientras el sistema sigue su posición y calcula sus métricas físicas en tiempo real.">
                    </figure>
                </section>

                <?php if ($puede_editar): ?>
                <section>
                    <h2>Nueva medición</h2>
                    <p class="sub">Cargá una fila por cada repetición corrida. Si un mismo jugador o jugadora corre varias veces, se ingresan varias mediciones con el mismo nombre. El deporte es solo un dato descriptivo: la prueba física es la misma para todas las disciplinas. La carga manual es temporal: la idea es reemplazarla por una importación directa desde Veo Analytics 2.</p>
                    <form id="form" novalidate>
                        <div class="campos">
                            <div>
                                <label for="nombre">Participante</label>
                                <input id="nombre" type="text" autocomplete="off" placeholder="Nombre y apellido">
                            </div>
                            <div>
                                <label for="deporte">Deporte</label>
                                <select id="deporte">
                                    <option value="" disabled selected>Elegir...</option>
                                    <?php foreach (deportes_clubes() as $nombre_deporte): ?>
                                        <option value="<?php echo htmlspecialchars($nombre_deporte, ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($nombre_deporte, ENT_QUOTES, "UTF-8"); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="masa">Masa <span class="u">kg</span></label>
                                <input id="masa" type="number" step="0.1" min="1" placeholder="70">
                            </div>
                            <div>
                                <label for="distancia">Distancia <span class="u">m</span></label>
                                <input id="distancia" type="number" step="0.1" min="1" value="20">
                            </div>
                            <div>
                                <label for="tiempo">Tiempo <span class="u">s</span></label>
                                <input id="tiempo" type="number" step="0.01" min="0.1" placeholder="3.40">
                            </div>
                        </div>
                        <div class="acciones">
                            <button type="submit">Calcular y guardar</button>
                            <button type="button" class="fantasma" id="veo" title="Integración en desarrollo">Cargar datos de Veo</button>
                            <button type="button" class="fantasma" id="csv">Exportar CSV</button>
                            <button type="button" class="fantasma" id="limpiar">Borrar todo</button>
                        </div>
                        <p class="aviso" id="aviso" role="status"></p>
                    </form>
                </section>
                <?php endif; ?>

                <section>
                    <h2>Registros</h2>
                    <p class="sub">Resultados calculados para cada repetición.</p>
                    <div id="zonaTabla"></div>
                    <?php if (!$puede_editar): ?>
                        <div class="acciones">
                            <button type="button" class="fantasma" id="csv">Exportar CSV</button>
                        </div>
                        <p class="aviso" id="aviso" role="status"></p>
                    <?php endif; ?>
                </section>

                <section>
                    <h2>Ranking por potencia</h2>
                    <p class="sub">Mejor repetición de cada participante, ordenada por potencia mecánica desarrollada.</p>
                    <div id="zonaRanking"></div>
                </section>

                <section>
                    <h2>Gráficos</h2>
                    <p class="sub">Comparación entre participantes y evolución a lo largo de las repeticiones.</p>
                    <div class="graficos">
                        <figure>
                            <figcaption>Potencia máxima por participante</figcaption>
                            <div id="graficoBarras"></div>
                        </figure>
                        <figure>
                            <figcaption>Velocidad media por repetición</figcaption>
                            <div id="graficoLineas"></div>
                            <div class="leyenda" id="leyenda"></div>
                        </figure>
                    </div>
                </section>

                <section>
                    <h2>Modelo</h2>
                    <p class="sub">Ecuaciones utilizadas en los cálculos.</p>
                    <div class="modelo">
                        <div class="formulas">
                            <dl>
                                <dt>Velocidad inicial</dt><dd>v₀ = 0</dd>
                                <dt>Desplazamiento</dt><dd>d = v₀ · t + a · t² / 2</dd>
                                <dt>Velocidad media</dt><dd>vÌ„ = d / t</dd>
                                <dt>Aceleración</dt><dd>a = 2d / t²</dd>
                                <dt>Velocidad final</dt><dd>v = √(2 · a · d)</dd>
                                <dt>Fuerza neta</dt><dd>F = m · a</dd>
                                <dt>Trabajo mecánico</dt><dd>W = F · d</dd>
                                <dt>Energía cinética</dt><dd>Ec = W</dd>
                                <dt>Potencia media</dt><dd>P = W / t</dd>
                            </dl>
                        </div>
                    </div>
                </section>

            </div>

            <p class="lab-pie">ARENIX · módulo de análisis físico del rendimiento</p>

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
    (function(){
        "use strict";

        // almacenamiento (memoria + localStorage si está disponible)
        var CLAVE = "arenix_fisica_v1";
        var PUEDE_EDITAR = <?php echo $puede_editar ? "true" : "false"; ?>;

        function datosEjemplo(){
            return [
                { id:"d1", nombre:"Bruno Figueroa",  deporte:"Fútbol",     masa:74.0, distancia:20, tiempo:3.42 },
                { id:"d2", nombre:"Bruno Figueroa",  deporte:"Fútbol",     masa:74.0, distancia:20, tiempo:3.35 },
                { id:"d3", nombre:"Bruno Figueroa",  deporte:"Fútbol",     masa:74.0, distancia:20, tiempo:3.51 },
                { id:"d4", nombre:"Camila Suárez",   deporte:"Básquetbol", masa:82.0, distancia:20, tiempo:3.55 },
                { id:"d5", nombre:"Camila Suárez",   deporte:"Básquetbol", masa:82.0, distancia:20, tiempo:3.60 },
                { id:"d6", nombre:"Camila Suárez",   deporte:"Básquetbol", masa:82.0, distancia:20, tiempo:3.48 },
                { id:"d7", nombre:"Valentina Ríos",  deporte:"Vóley",      masa:63.5, distancia:20, tiempo:3.70 },
                { id:"d8", nombre:"Valentina Ríos",  deporte:"Vóley",      masa:63.5, distancia:20, tiempo:3.65 },
                { id:"d9", nombre:"Valentina Ríos",  deporte:"Vóley",      masa:63.5, distancia:20, tiempo:3.78 },
                { id:"d10", nombre:"Diego Palacios", deporte:"Rugby",      masa:96.0, distancia:20, tiempo:3.95 },
                { id:"d11", nombre:"Diego Palacios", deporte:"Rugby",      masa:96.0, distancia:20, tiempo:3.88 },
                { id:"d12", nombre:"Diego Palacios", deporte:"Rugby",      masa:96.0, distancia:20, tiempo:4.02 }
            ];
        }

        var registros = [];
        var huboGuardado = false;
        try {
            var guardado = window.localStorage.getItem(CLAVE);
            if (guardado) { registros = JSON.parse(guardado) || []; huboGuardado = true; }
        } catch (e) { registros = []; }

        if (!huboGuardado) registros = datosEjemplo();

        function guardar(){
            try { window.localStorage.setItem(CLAVE, JSON.stringify(registros)); } catch (e) {}
        }

        // modelo físico
        // siempre parte del reposo
        var V_INICIAL = 0;

        function calcular(m, d, t){
            var vMedia = d / t;
            var a = 2 * (d - V_INICIAL * t) / (t * t);
            var vFinal = Math.sqrt(2 * a * d);
            var F = m * a;
            var W = F * d;
            var Ec = W;
            var P = W / t;
            return { v0:V_INICIAL, vMedia:vMedia, vFinal:vFinal, a:a, F:F, W:W, Ec:Ec, P:P };
        }

        function n(x, dec){ return x.toFixed(dec === undefined ? 2 : dec); }

        var PALETA = ["#1d8fff","#ff5a5a","#7ee08a","#f0b429","#8b5cf6","#22d3ee"];
        function colorDe(nombre){
            var nombres = participantes();
            var i = nombres.indexOf(nombre);
            return PALETA[(i < 0 ? 0 : i) % PALETA.length];
        }
        function participantes(){
            var vistos = [];
            registros.forEach(function(r){ if (vistos.indexOf(r.nombre) === -1) vistos.push(r.nombre); });
            return vistos;
        }

        // tabla
        function dibujarTabla(){
            var zona = document.getElementById("zonaTabla");
            if (!registros.length){
                zona.innerHTML = PUEDE_EDITAR
                    ? '<p class="vacio">Todavía no hay mediciones. Cargá la primera con el formulario de arriba, o probá el módulo con datos de ejemplo.</p>'
                    : '<p class="vacio">Todavía no hay mediciones cargadas.</p>';
                return;
            }
            var filas = registros.map(function(r){
                var c = calcular(r.masa, r.distancia, r.tiempo);
                return "<tr>"
                    + "<td>" + escapar(r.nombre) + "</td>"
                    + "<td><span class='deporte-etiqueta'>" + escapar(r.deporte || "Otro") + "</span></td>"
                    + "<td>" + n(r.masa,1) + "</td>"
                    + "<td>" + n(r.distancia,1) + "</td>"
                    + "<td>" + n(r.tiempo,2) + "</td>"
                    + "<td>" + n(c.v0) + "</td>"
                    + "<td>" + n(c.vMedia) + "</td>"
                    + "<td>" + n(c.vFinal) + "</td>"
                    + "<td>" + n(c.a) + "</td>"
                    + "<td>" + n(c.F,1) + "</td>"
                    + "<td>" + n(c.W,0) + "</td>"
                    + "<td>" + n(c.Ec,0) + "</td>"
                    + "<td>" + n(c.P,0) + "</td>"
                    + (PUEDE_EDITAR ? '<td><button class="borrar" data-id="' + r.id + '" aria-label="Borrar medición de ' + escapar(r.nombre) + '">borrar</button></td>' : "")
                    + "</tr>";
            }).join("");

            zona.innerHTML = '<div class="desplazable"><table>'
                + "<thead><tr>"
                + "<th>Participante</th><th>Deporte</th><th>m (kg)</th><th>d (m)</th><th>t (s)</th>"
                + "<th>v₀ (m/s)</th><th>vÌ„ (m/s)</th><th>v (m/s)</th><th>a (m/s²)</th><th>F (N)</th>"
                + "<th>W (J)</th><th>Ec (J)</th><th>P (W)</th>" + (PUEDE_EDITAR ? "<th></th>" : "")
                + "</tr></thead><tbody>" + filas + "</tbody></table></div>";
        }

        function escapar(s){
            return String(s).replace(/[&<>"']/g, function(c){
                return ({ "&":"&amp;", "<":"&lt;", ">":"&gt;", '"':"&quot;", "'":"&#39;" })[c];
            });
        }

        // ranking
        function mejoresPorParticipante(){
            var mapa = {};
            registros.forEach(function(r){
                var c = calcular(r.masa, r.distancia, r.tiempo);
                if (!mapa[r.nombre] || c.P > mapa[r.nombre].P){
                    mapa[r.nombre] = { nombre:r.nombre, deporte:r.deporte, P:c.P, vMedia:c.vMedia, tiempo:r.tiempo };
                }
            });
            return Object.keys(mapa).map(function(k){ return mapa[k]; })
                .sort(function(a,b){ return b.P - a.P; });
        }

        function dibujarRanking(){
            var zona = document.getElementById("zonaRanking");
            var lista = mejoresPorParticipante();
            if (!lista.length){
                zona.innerHTML = '<p class="vacio">El ranking se arma solo, a medida que cargás mediciones.</p>';
                return;
            }
            zona.innerHTML = '<ol class="ranking">' + lista.map(function(x){
                return "<li>"
                    + "<span class='nombre'>" + escapar(x.nombre) + "</span>"
                    + "<span class='deporte-etiqueta'>" + escapar(x.deporte || "Otro") + "</span>"
                    + "<span class='dato'>" + n(x.P,0) + "<span>W</span></span>"
                    + "<span class='dato'>" + n(x.tiempo,2) + "<span>s</span></span></li>";
            }).join("") + "</ol>";
        }

        // gráfico de barras
        function dibujarBarras(){
            var zona = document.getElementById("graficoBarras");
            var lista = mejoresPorParticipante();
            if (!lista.length){ zona.innerHTML = '<p class="vacio">Sin datos.</p>'; return; }

            var alturaFila = 38, ancho = 520, izq = 130, der = 64;
            var alto = lista.length * alturaFila + 20;
            var max = Math.max.apply(null, lista.map(function(x){ return x.P; }));
            var util = ancho - izq - der;

            var s = '<svg viewBox="0 0 ' + ancho + " " + alto + '" role="img" aria-label="Potencia máxima por participante">';
            lista.forEach(function(x, i){
                var y = i * alturaFila + 10;
                var w = Math.max(2, (x.P / max) * util);
                s += '<text x="' + (izq - 10) + '" y="' + (y + 17) + '" text-anchor="end" font-size="14" font-family="Source Sans 3, sans-serif" fill="#dfe1e6">' + escapar(x.nombre) + "</text>";
                s += '<rect x="' + izq + '" y="' + y + '" width="' + w + '" height="24" fill="' + colorDe(x.nombre) + '"></rect>';
                s += '<text x="' + (izq + w + 8) + '" y="' + (y + 17) + '" font-size="14" font-family="Barlow Condensed, sans-serif" fill="#8b93a7">' + n(x.P,0) + " W</text>";
            });
            s += "</svg>";
            zona.innerHTML = s;
        }

        // gráfico de líneas
        function dibujarLineas(){
            var zona = document.getElementById("graficoLineas");
            var leyenda = document.getElementById("leyenda");
            var nombres = participantes();
            if (!nombres.length){ zona.innerHTML = '<p class="vacio">Sin datos.</p>'; leyenda.innerHTML = ""; return; }

            var series = nombres.map(function(nom){
                var puntos = registros.filter(function(r){ return r.nombre === nom; })
                    .map(function(r){ return calcular(r.masa, r.distancia, r.tiempo).vMedia; });
                return { nombre:nom, puntos:puntos };
            });

            var maxRep = Math.max.apply(null, series.map(function(s){ return s.puntos.length; }));
            var maxV = Math.max.apply(null, series.reduce(function(acc,s){ return acc.concat(s.puntos); }, [1]));
            maxV = Math.ceil(maxV * 1.15);

            var ancho = 520, alto = 300, izq = 48, abajo = 38, arriba = 14, der = 14;
            var ux = ancho - izq - der, uy = alto - arriba - abajo;

            function px(i){ return izq + (maxRep <= 1 ? ux/2 : (i / (maxRep - 1)) * ux); }
            function py(v){ return arriba + uy - (v / maxV) * uy; }

            var s = '<svg viewBox="0 0 ' + ancho + " " + alto + '" role="img" aria-label="Velocidad media por repetición">';
            for (var g = 0; g <= 4; g++){
                var val = maxV * g / 4, y = py(val);
                s += '<line x1="' + izq + '" y1="' + y + '" x2="' + (ancho - der) + '" y2="' + y + '" stroke="#1b2c47"></line>';
                s += '<text x="' + (izq - 8) + '" y="' + (y + 4) + '" text-anchor="end" font-size="12" font-family="Source Sans 3, sans-serif" fill="#8b93a7">' + n(val,1) + "</text>";
            }
            for (var r0 = 0; r0 < maxRep; r0++){
                s += '<text x="' + px(r0) + '" y="' + (alto - 14) + '" text-anchor="middle" font-size="12" font-family="Source Sans 3, sans-serif" fill="#8b93a7">rep ' + (r0 + 1) + "</text>";
            }
            s += '<text x="14" y="' + (arriba + 4) + '" font-size="12" font-family="Source Sans 3, sans-serif" fill="#8b93a7">m/s</text>';

            series.forEach(function(serie){
                var color = colorDe(serie.nombre);
                var d = serie.puntos.map(function(v, i){ return (i ? "L" : "M") + px(i) + " " + py(v); }).join(" ");
                if (serie.puntos.length > 1){
                    s += '<path d="' + d + '" fill="none" stroke="' + color + '" stroke-width="2.5"></path>';
                }
                serie.puntos.forEach(function(v, i){
                    s += '<circle cx="' + px(i) + '" cy="' + py(v) + '" r="4.5" fill="' + color + '"></circle>';
                });
            });
            s += "</svg>";
            zona.innerHTML = s;

            leyenda.innerHTML = series.map(function(serie){
                return "<span><i style='background:" + colorDe(serie.nombre) + "'></i>" + escapar(serie.nombre) + "</span>";
            }).join("");
        }

        // render general
        function render(){
            dibujarTabla(); dibujarRanking(); dibujarBarras(); dibujarLineas();
        }

        // eventos
        var aviso = document.getElementById("aviso");

        if (PUEDE_EDITAR) {
        document.getElementById("form").addEventListener("submit", function(ev){
            ev.preventDefault();
            var nombre = document.getElementById("nombre").value.trim();
            var deporte = document.getElementById("deporte").value;
            var masa = parseFloat(document.getElementById("masa").value);
            var distancia = parseFloat(document.getElementById("distancia").value);
            var tiempo = parseFloat(document.getElementById("tiempo").value);

            if (!nombre){ aviso.textContent = "Falta el nombre del participante."; return; }
            if (!deporte){ aviso.textContent = "Elegí el deporte del participante."; return; }
            if (!(masa > 0)){ aviso.textContent = "La masa tiene que ser un número mayor que cero."; return; }
            if (!(distancia > 0)){ aviso.textContent = "La distancia tiene que ser un número mayor que cero."; return; }
            if (!(tiempo > 0)){ aviso.textContent = "El tiempo tiene que ser un número mayor que cero."; return; }

            registros.push({
                id: Date.now() + "-" + Math.random().toString(16).slice(2,7),
                nombre: nombre, deporte: deporte, masa: masa, distancia: distancia, tiempo: tiempo
            });
            guardar(); render();
            aviso.textContent = "";
            document.getElementById("tiempo").value = "";
            document.getElementById("tiempo").focus();
        });

        document.getElementById("zonaTabla").addEventListener("click", function(ev){
            var b = ev.target.closest(".borrar");
            if (!b) return;
            registros = registros.filter(function(r){ return r.id !== b.getAttribute("data-id"); });
            guardar(); render();
        });

        document.getElementById("limpiar").addEventListener("click", function(){
            if (!registros.length) return;
            if (!window.confirm("Se borran todas las mediciones cargadas. ¿Seguir?")) return;
            registros = []; guardar(); render();
        });

        document.getElementById("veo").addEventListener("click", function(){
            aviso.textContent = "Integración con Veo Analytics 2 en desarrollo: por ahora, las mediciones se cargan a mano.";
        });
        }

        document.getElementById("csv").addEventListener("click", function(){
            if (!registros.length){ aviso.textContent = "No hay mediciones para exportar."; return; }
            var lineas = ["participante,deporte,masa_kg,distancia_m,tiempo_s,v_inicial_ms,v_media_ms,v_final_ms,aceleracion_ms2,fuerza_N,trabajo_J,energia_J,potencia_W"];
            registros.forEach(function(r){
                var c = calcular(r.masa, r.distancia, r.tiempo);
                lineas.push([
                    '"' + r.nombre.replace(/"/g,'""') + '"', '"' + (r.deporte || "Otro").replace(/"/g,'""') + '"', r.masa, r.distancia, r.tiempo,
                    n(c.v0), n(c.vMedia), n(c.vFinal), n(c.a), n(c.F,1), n(c.W,0), n(c.Ec,0), n(c.P,0)
                ].join(","));
            });
            var blob = new Blob([lineas.join("\n")], { type:"text/csv;charset=utf-8" });
            var a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "arenix-mediciones.csv";
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            URL.revokeObjectURL(a.href);
            aviso.textContent = "";
        });

        render();
    })();
    </script>

</body>
</html>



