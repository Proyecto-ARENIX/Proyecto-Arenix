<?php
// se carga desde app/controllers/contratar.php
// (los @var son para que el editor no marque error)
/** @var array|null $usuario */
/** @var array $planes */
/** @var array $deportes */
/** @var string $error */
/** @var array|null $contratacion */
/** @var bool $paso_a_cliente */
/** @var string $plan_elegido */
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
$viejo = fn($clave, $defecto = "") => isset($_POST[$clave]) && is_string($_POST[$clave]) ? $_POST[$clave] : $defecto;
$marcas = marcas_tarjeta();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENIX | Contratar plan</title>

    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/reglas.css">
    <link rel="stylesheet" href="/public/assets/css/planes.css">
    <link rel="stylesheet" href="/public/assets/css/contratar.css">
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

    <main class="contratar">

    <?php if ($contratacion): ?>
        <!-- comprobante -->
        <?php $plan_comprado = $planes[$contratacion["plan"]] ?? ["nombre" => $contratacion["plan"], "color" => "azul"]; ?>
        <section class="comprobante plan_<?php echo $e($plan_comprado["color"]); ?>" aria-labelledby="titulo-comprobante">
            <span class="comprobante_icono" aria-hidden="true"></span>
            <h1 id="titulo-comprobante">¡Pago aprobado!</h1>
            <p class="comprobante_prueba">Modo de prueba: no se realizó ningún cobro real.</p>
            <?php if ($paso_a_cliente): ?>
                <p class="comprobante_rol">Tu cuenta ahora tiene el rol <strong>Cliente</strong>.</p>
            <?php endif; ?>

            <dl class="comprobante_datos">
                <div><dt>Orden</dt><dd>#<?php echo (int) $contratacion["id"]; ?></dd></div>
                <div><dt>Plan</dt><dd><?php echo $e($plan_comprado["nombre"]); ?></dd></div>
                <div><dt>Total</dt><dd>USD <?php echo (int) $contratacion["total_mensual"]; ?>/mes</dd></div>
                <div><dt>Tarjeta</dt><dd><?php echo $e(($marcas[$contratacion["tarjeta_marca"]] ?? "Tarjeta") . " •••• " . $contratacion["tarjeta_ultimos"]); ?></dd></div>
                <div><dt>Liga / organización</dt><dd><?php echo $e($contratacion["organizacion"]); ?></dd></div>
                <?php if ($contratacion["equipos_extra"] > 0 || $contratacion["jugadores_extra"] > 0): ?>
                    <div><dt>Extras</dt><dd><?php echo (int) $contratacion["equipos_extra"]; ?> equipos · <?php echo (int) $contratacion["jugadores_extra"]; ?> jugadores</dd></div>
                <?php endif; ?>
            </dl>

            <div class="comprobante_pasos">
                <h2>¿Y ahora?</h2>
                <ol>
                    <li>Un organizador de ARENIX crea tu liga con el plan que contrataste.</li>
                    <li>Te da acceso como administrador de la liga con el correo que pusiste al contratar.<?php echo $usuario ? "" : " Si todavía no tienes cuenta, regístrate con ese correo."; ?></li>
                    <li>Vas a ver tu liga en <strong>Mi perfil</strong> para cargar equipos, torneos y resultados.</li>
                </ol>
            </div>

            <div class="comprobante_acciones">
                <a class="plan_boton" href="/index.php?pagina=enfrentamientos">Ver las ligas</a>
                <?php if (!$usuario): ?>
                    <a class="boton_contorno" href="/index.php?pagina=registro">Crear mi cuenta</a>
                <?php else: ?>
                    <a class="boton_contorno" href="/index.php?pagina=perfil">Ir a mi perfil</a>
                <?php endif; ?>
            </div>
        </section>

    <?php else: ?>
        <!-- formulario -->
        <header class="planes_cabecera contratar_cabecera">
            <p class="planes_etiqueta">CONTRATAR</p>
            <h1>Completa tus datos y el pago</h1>
            <p>Elige el plan, cuéntanos de tu liga y paga con tarjeta. Puedes cambiar de plan cuando quieras.</p>
        </header>

        <form class="contratar_grilla" method="post" action="/index.php?pagina=contratar" id="formContratar" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION["csrf_contratar"]); ?>">

            <div class="contratar_pasos">

                <?php if ($error !== ""): ?>
                    <p class="contratar_error" role="alert"><?php echo $e($error); ?></p>
                <?php endif; ?>

                <!-- paso 1: plan -->
                <section class="paso" aria-labelledby="paso-plan">
                    <h2 id="paso-plan"><span class="paso_numero">1</span> Tu plan</h2>
                    <div class="opciones_plan">
                        <?php foreach ($planes as $id_plan => $plan): ?>
                            <label class="opcion_plan plan_<?php echo $e($plan["color"]); ?>">
                                <input type="radio" name="plan" value="<?php echo $e($id_plan); ?>" data-precio="<?php echo (int) $plan["precio"]; ?>" data-nombre="<?php echo $e($plan["nombre"]); ?>"
                                    <?php echo $id_plan === $plan_elegido ? "checked" : ""; ?> required>
                                <span class="opcion_plan_nombre"><?php echo $e($plan["nombre"]); ?></span>
                                <span class="opcion_plan_precio">USD <?php echo (int) $plan["precio"]; ?><small>/mes</small></span>
                                <span class="opcion_plan_detalle"><?php echo $e($plan["limites"]["Equipos"]); ?> equipos · <?php echo $e($plan["limites"]["Torneos activos"]); ?> torneo(s)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="campos_dobles">
                        <div class="campo_contratar">
                            <label for="equiposExtra">Equipos adicionales <small>(USD <?php echo PRECIO_EQUIPO_EXTRA; ?>/mes c/u)</small></label>
                            <input id="equiposExtra" name="equipos_extra" type="number" min="0" max="1000" value="<?php echo $e($viejo("equipos_extra", "0")); ?>">
                        </div>
                        <div class="campo_contratar">
                            <label for="jugadoresExtra">Jugadores adicionales <small>(USD <?php echo PRECIO_JUGADOR_EXTRA; ?>/mes c/u)</small></label>
                            <input id="jugadoresExtra" name="jugadores_extra" type="number" min="0" max="1000" value="<?php echo $e($viejo("jugadores_extra", "0")); ?>">
                        </div>
                    </div>
                    <p class="ayuda_contratar">Cada plan ya incluye 10 jugadores adicionales gratis.</p>
                </section>

                <!-- paso 2: datos -->
                <section class="paso" aria-labelledby="paso-datos">
                    <h2 id="paso-datos"><span class="paso_numero">2</span> Tus datos</h2>
                    <div class="campos_dobles">
                        <div class="campo_contratar">
                            <label for="datoNombre">Nombre y apellido</label>
                            <input id="datoNombre" name="nombre" type="text" maxlength="80" autocomplete="name" required value="<?php echo $e($viejo("nombre", $usuario["nombre"] ?? "")); ?>">
                        </div>
                        <div class="campo_contratar">
                            <label for="datoCorreo">Correo electrónico</label>
                            <input id="datoCorreo" name="correo" type="email" maxlength="190" autocomplete="email" required value="<?php echo $e($viejo("correo", $usuario["correo"] ?? "")); ?>">
                        </div>
                        <div class="campo_contratar">
                            <label for="datoTelefono">Teléfono</label>
                            <input id="datoTelefono" name="telefono" type="tel" maxlength="30" autocomplete="tel" required placeholder="+598 99 123 456"
                                   value="<?php echo $e($viejo("telefono", $usuario && !empty($usuario["telefono"]) ? $usuario["prefijo_telefono"] . " " . $usuario["telefono"] : "")); ?>">
                        </div>
                        <div class="campo_contratar">
                            <label for="datoCiudad">Ciudad</label>
                            <input id="datoCiudad" name="ciudad" type="text" maxlength="60" autocomplete="address-level2" required value="<?php echo $e($viejo("ciudad")); ?>">
                        </div>
                        <div class="campo_contratar">
                            <label for="datoOrganizacion">Nombre de tu liga u organización</label>
                            <input id="datoOrganizacion" name="organizacion" type="text" maxlength="80" required placeholder="Ej.: Liga Metropolitana" value="<?php echo $e($viejo("organizacion")); ?>">
                        </div>
                        <div class="campo_contratar">
                            <label for="datoDeporte">Deporte</label>
                            <select id="datoDeporte" name="deporte" required>
                                <?php foreach ($deportes as $id_deporte => $nombre_deporte): ?>
                                    <option value="<?php echo $e($id_deporte); ?>" <?php echo $viejo("deporte") === $id_deporte ? "selected" : ""; ?>><?php echo $e($nombre_deporte); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- paso 3: pago -->
                <section class="paso" aria-labelledby="paso-pago">
                    <h2 id="paso-pago"><span class="paso_numero">3</span> Pago con tarjeta</h2>
                    <p class="modo_prueba">
                        <strong>Modo de prueba:</strong> no se realiza ningún cobro. Prueba con la tarjeta <code>4242 4242 4242 4242</code>, cualquier vencimiento futuro y cualquier código.
                    </p>

                    <div class="pago_grilla">
                        <!-- tarjeta que se va completando -->
                        <div class="tarjeta_visual" id="tarjetaVisual" aria-hidden="true">
                            <div class="tarjeta_frente">
                                <span class="tarjeta_chip"></span>
                                <span class="tarjeta_marca" id="tarjetaMarcaVisual">ARENIX</span>
                                <span class="tarjeta_numero" id="tarjetaNumeroVisual">•••• •••• •••• ••••</span>
                                <span class="tarjeta_pie">
                                    <span><small>Titular</small><b id="tarjetaTitularVisual">NOMBRE APELLIDO</b></span>
                                    <span><small>Vence</small><b id="tarjetaVenceVisual">MM/AA</b></span>
                                </span>
                            </div>
                            <div class="tarjeta_dorso">
                                <span class="tarjeta_banda"></span>
                                <span class="tarjeta_cvv"><small>Código</small><b id="tarjetaCvvVisual">•••</b></span>
                            </div>
                        </div>

                        <div class="pago_campos">
                            <!-- numero y cvv sin name, asi no llegan al servidor -->
                            <div class="campo_contratar">
                                <label for="tarjetaNumero">Número de tarjeta</label>
                                <input id="tarjetaNumero" type="text" inputmode="numeric" autocomplete="cc-number" maxlength="23" placeholder="1234 5678 9012 3456" required>
                            </div>
                            <div class="campo_contratar">
                                <label for="tarjetaTitular">Nombre en la tarjeta</label>
                                <input id="tarjetaTitular" name="tarjeta_titular" type="text" maxlength="60" autocomplete="cc-name" required value="<?php echo $e($viejo("tarjeta_titular")); ?>">
                            </div>
                            <div class="campos_dobles">
                                <div class="campo_contratar">
                                    <label for="tarjetaVence">Vencimiento</label>
                                    <input id="tarjetaVence" name="tarjeta_vencimiento" type="text" inputmode="numeric" autocomplete="cc-exp" maxlength="5" placeholder="MM/AA" required value="<?php echo $e($viejo("tarjeta_vencimiento")); ?>">
                                </div>
                                <div class="campo_contratar">
                                    <label for="tarjetaCvv">Código de seguridad</label>
                                    <input id="tarjetaCvv" type="password" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="123" required>
                                </div>
                            </div>
                            <input type="hidden" name="tarjeta_marca" id="tarjetaMarca">
                            <input type="hidden" name="tarjeta_ultimos" id="tarjetaUltimos">
                            <p class="ayuda_contratar candado">El número completo y el código de seguridad no se guardan ni se envían a ARENIX.</p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- resumen -->
            <aside class="contratar_resumen" aria-labelledby="titulo-resumen">
                <h2 id="titulo-resumen">Resumen</h2>
                <dl>
                    <div><dt id="resumenPlan">Plan</dt><dd id="resumenPrecioPlan">USD 0</dd></div>
                    <div id="filaEquiposExtra" hidden><dt id="resumenEquipos"></dt><dd id="resumenPrecioEquipos"></dd></div>
                    <div id="filaJugadoresExtra" hidden><dt id="resumenJugadores"></dt><dd id="resumenPrecioJugadores"></dd></div>
                </dl>
                <p class="resumen_total"><span>Total por mes</span><strong id="resumenTotal">USD 0</strong></p>

                <label class="acepto">
                    <input type="checkbox" id="aceptoTerminos">
                    Acepto los <a href="/public/assets/docs/Términos y Condiciones de Uso.pdf" target="_blank" rel="noopener">Términos y Condiciones</a>
                </label>
                <p class="contratar_error_js" id="errorPago" role="alert" hidden></p>
                <button type="submit" class="plan_boton boton_pagar" id="botonPagar">Pagar</button>
                <p class="ayuda_contratar">Se renueva cada mes. Puedes cancelar cuando quieras.</p>
            </aside>
        </form>
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

    <?php if (!$contratacion): ?>
    <script>
        (function () {
            const form = document.getElementById("formContratar");
            const PRECIO_EQUIPO = <?php echo PRECIO_EQUIPO_EXTRA; ?>;
            const PRECIO_JUGADOR = <?php echo PRECIO_JUGADOR_EXTRA; ?>;
            const MARCAS = <?php echo json_encode($marcas, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;
            const $ = id => document.getElementById(id);
            const entero = input => Math.max(0, Math.min(1000, parseInt(input.value, 10) || 0));

            // resumen y total
            function actualizarResumen() {
                const plan = form.querySelector('input[name="plan"]:checked');
                const precio = Number(plan.dataset.precio);
                const equipos = entero($("equiposExtra"));
                const jugadores = entero($("jugadoresExtra"));
                const total = precio + equipos * PRECIO_EQUIPO + jugadores * PRECIO_JUGADOR;

                $("resumenPlan").textContent = plan.dataset.nombre;
                $("resumenPrecioPlan").textContent = "USD " + precio;
                $("filaEquiposExtra").hidden = equipos === 0;
                $("resumenEquipos").textContent = equipos + (equipos === 1 ? " equipo adicional" : " equipos adicionales");
                $("resumenPrecioEquipos").textContent = "USD " + equipos * PRECIO_EQUIPO;
                $("filaJugadoresExtra").hidden = jugadores === 0;
                $("resumenJugadores").textContent = jugadores + (jugadores === 1 ? " jugador adicional" : " jugadores adicionales");
                $("resumenPrecioJugadores").textContent = "USD " + jugadores * PRECIO_JUGADOR;
                $("resumenTotal").textContent = "USD " + total;
                $("botonPagar").textContent = "Pagar USD " + total + "/mes";
            }
            form.querySelectorAll('input[name="plan"], #equiposExtra, #jugadoresExtra').forEach(c => c.addEventListener("input", actualizarResumen));
            form.querySelectorAll('input[name="plan"]').forEach(c => c.addEventListener("change", actualizarResumen));
            actualizarResumen();

            // tarjeta
            const soloDigitos = texto => texto.replace(/\D/g, "");

            function marcaDe(numero) {
                if (/^4/.test(numero)) return "visa";
                if (/^(5[1-5]|2(2[2-9]|[3-6]\d|7[01]|720))/.test(numero)) return "mastercard";
                if (/^3[47]/.test(numero)) return "amex";
                if (/^589892/.test(numero)) return "oca";
                return numero.length >= 4 ? "otra" : "";
            }

            // luhn: detecta numeros mal escritos
            function numeroValido(numero) {
                if (numero.length < 13 || numero.length > 19) return false;
                let suma = 0;
                [...numero].reverse().forEach((d, i) => {
                    let n = Number(d);
                    if (i % 2 === 1) { n *= 2; if (n > 9) n -= 9; }
                    suma += n;
                });
                return suma % 10 === 0;
            }

            function vencimientoValido(texto) {
                const partes = /^(0[1-9]|1[0-2])\/(\d{2})$/.exec(texto);
                if (!partes) return false;
                const fin = new Date(2000 + Number(partes[2]), Number(partes[1]), 0, 23, 59, 59);
                return fin >= new Date();
            }

            $("tarjetaNumero").addEventListener("input", evento => {
                const numero = soloDigitos(evento.target.value).slice(0, 19);
                const marca = marcaDe(numero);
                // amex: 4-6-5; el resto: grupos de 4
                evento.target.value = marca === "amex"
                    ? [numero.slice(0, 4), numero.slice(4, 10), numero.slice(10, 15)].filter(Boolean).join(" ")
                    : numero.replace(/(\d{4})(?=\d)/g, "$1 ");
                $("tarjetaNumeroVisual").textContent = (evento.target.value + " •••• •••• •••• ••••").slice(0, 19) || "•••• •••• •••• ••••";
                $("tarjetaMarcaVisual").textContent = marca ? MARCAS[marca] : "ARENIX";
                $("tarjetaVisual").dataset.marca = marca;
                $("tarjetaCvv").maxLength = marca === "amex" ? 4 : 3;
            });

            $("tarjetaTitular").addEventListener("input", evento => {
                $("tarjetaTitularVisual").textContent = evento.target.value.toUpperCase() || "NOMBRE APELLIDO";
            });

            $("tarjetaVence").addEventListener("input", evento => {
                let digitos = soloDigitos(evento.target.value).slice(0, 4);
                if (digitos.length === 1 && Number(digitos) > 1) digitos = "0" + digitos;
                evento.target.value = digitos.length > 2 ? digitos.slice(0, 2) + "/" + digitos.slice(2) : digitos;
                $("tarjetaVenceVisual").textContent = evento.target.value || "MM/AA";
            });

            // al escribir el codigo se da vuelta la tarjeta
            $("tarjetaCvv").addEventListener("focus", () => $("tarjetaVisual").classList.add("girada"));
            $("tarjetaCvv").addEventListener("blur", () => $("tarjetaVisual").classList.remove("girada"));
            $("tarjetaCvv").addEventListener("input", evento => {
                evento.target.value = soloDigitos(evento.target.value);
                $("tarjetaCvvVisual").textContent = "•".repeat(evento.target.value.length) || "•••";
            });

            // enviar
            form.addEventListener("submit", evento => {
                const numero = soloDigitos($("tarjetaNumero").value);
                const marca = marcaDe(numero);
                const cvv = $("tarjetaCvv").value;
                let error = "";

                if (!form.checkValidity()) error = "Completa todos los datos marcados.";
                else if (!numeroValido(numero)) error = "El número de la tarjeta no es válido.";
                else if (!vencimientoValido($("tarjetaVence").value)) error = "Revisa el vencimiento (MM/AA, no puede estar vencida).";
                else if (cvv.length !== (marca === "amex" ? 4 : 3)) error = "El código de seguridad tiene " + (marca === "amex" ? 4 : 3) + " dígitos.";
                else if (!$("aceptoTerminos").checked) error = "Tienes que aceptar los Términos y Condiciones.";

                if (error) {
                    evento.preventDefault();
                    $("errorPago").textContent = error;
                    $("errorPago").hidden = false;
                    form.reportValidity();
                    return;
                }

                // al servidor solo van la marca y los ultimos 4 digitos
                $("tarjetaMarca").value = marca;
                $("tarjetaUltimos").value = numero.slice(-4);
                $("botonPagar").disabled = true;
                $("botonPagar").textContent = "Procesando pago…";
            });
        })();
    </script>
    <?php endif; ?>

</body>
</html>
