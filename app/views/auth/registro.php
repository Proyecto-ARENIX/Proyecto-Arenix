<?php
require_once __DIR__ . "/../../models/auth.php";

if (usuario_actual()) {
    header("Location: /index.php?pagina=inicio");
    exit;
}

$error = "";
$exito = false;
$nombre = "";
$correo = "";
$pais_telefono = "AR";
$telefono = "";
$paises_telefonicos = paises_con_codigo_telefonico();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // nombre, correo y telefono tienen un nombre distinto en cada carga (ver abajo)
    $sufijo = isset($_POST["sufijo_campos"]) && is_string($_POST["sufijo_campos"]) && preg_match('/^[a-f0-9]{12}$/', $_POST["sufijo_campos"]) ? "_" . $_POST["sufijo_campos"] : "";
    $nombre = isset($_POST["nombre" . $sufijo]) && is_string($_POST["nombre" . $sufijo]) ? trim($_POST["nombre" . $sufijo]) : "";
    $correo = isset($_POST["correo" . $sufijo]) && is_string($_POST["correo" . $sufijo]) ? trim($_POST["correo" . $sufijo]) : "";
    $pais_telefono = $_POST["pais_telefono"] ?? "";
    $telefono = isset($_POST["telefono" . $sufijo]) && is_string($_POST["telefono" . $sufijo]) ? trim($_POST["telefono" . $sufijo]) : "";
    $contrasena = isset($_POST["contrasena"]) && is_string($_POST["contrasena"]) ? $_POST["contrasena"] : "";
    $repetir_contrasena = isset($_POST["repetir_contrasena"]) && is_string($_POST["repetir_contrasena"]) ? $_POST["repetir_contrasena"] : "";

    if ($contrasena !== $repetir_contrasena) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $resultado = registrar_usuario($nombre, $correo, $contrasena, $pais_telefono, $telefono);

        if ($resultado === "ok") {
            $exito = true;
        } else {
            $error = $resultado;
        }
    }
}

// nombres al azar para que el navegador no sugiera datos de otras personas
$sufijo_campos = bin2hex(random_bytes(6));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - ARENIX</title>
    <link rel="stylesheet" href="/public/assets/css/acceso.css">
</head>
<body class="acceso-cuerpo registro-cuerpo">
    <main class="acceso-tarjeta acceso-tarjeta-registro">
        <a href="/index.php?pagina=inicio"><img class="acceso-logo" src="/public/assets/images/logo a.png" alt="ARENIX"></a>
        <h1>ARE<span>NIX</span></h1>
        <p class="acceso-subtitulo">Creá tu cuenta para gestionar tu equipo</p>

        <?php if ($exito): ?>
            <div class="acceso-mensaje exito visible">
                Listo, cuenta creada. Ya podés <a class="acceso-enlace" href="/index.php?pagina=ingresar">iniciar sesión</a>.
            </div>
        <?php else: ?>

            <?php if ($error): ?>
                <div class="acceso-mensaje error visible"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- sin autocompletar: en una compu compartida no se ven los datos de otros -->
            <form method="post" autocomplete="off">
                <input type="hidden" name="sufijo_campos" value="<?php echo $sufijo_campos; ?>">
                <div class="acceso-campo">
                    <label for="nombre">Nombre</label>
                    <div class="acceso-entrada">
                        <span class="icono">&#128100;</span>
                        <input type="text" id="nombre" name="nombre_<?php echo $sufijo_campos; ?>" placeholder="Tu nombre"
                               value="<?php echo htmlspecialchars($nombre); ?>" autocomplete="off" required>
                    </div>
                </div>

                <div class="acceso-campo">
                    <label for="correo">Correo electrónico</label>
                    <div class="acceso-entrada">
                        <span class="icono">&#9993;</span>
                        <input type="email" id="correo" name="correo_<?php echo $sufijo_campos; ?>" placeholder="tu@correo.com"
                               value="<?php echo htmlspecialchars($correo); ?>" autocomplete="off" required>
                    </div>
                </div>

                <div class="acceso-campo">
                    <label for="pais_telefono">País y prefijo telefónico</label>
                    <select class="acceso-selector" id="pais_telefono" name="pais_telefono" required>
                        <?php foreach ($paises_telefonicos as $codigo_iso => $prefijo): ?>
                            <option value="<?php echo htmlspecialchars($codigo_iso, ENT_QUOTES, "UTF-8"); ?>" data-prefijo="<?php echo htmlspecialchars($prefijo, ENT_QUOTES, "UTF-8"); ?>"<?php echo $pais_telefono === $codigo_iso ? " selected" : ""; ?>><?php echo htmlspecialchars($codigo_iso . " (" . $prefijo . ")", ENT_QUOTES, "UTF-8"); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="acceso-campo">
                    <label for="telefono">Número de teléfono</label>
                    <div class="acceso-entrada">
                        <span class="icono" aria-hidden="true">+</span>
                        <input type="tel" id="telefono" name="telefono_<?php echo $sufijo_campos; ?>" inputmode="tel" autocomplete="off" placeholder="Tu número sin el prefijo" value="<?php echo htmlspecialchars($telefono, ENT_QUOTES, "UTF-8"); ?>" minlength="5" maxlength="20" pattern="[0-9() .-]{5,20}" required>
                    </div>
                    <small class="acceso-ayuda">El prefijo se pone solo según el país.</small>
                </div>

                <div class="acceso-campo">
                    <label for="contrasena">Contraseña</label>
                    <div class="acceso-entrada">
                        <span class="icono">&#128274;</span>
                        <input type="password" id="contrasena" name="contrasena" placeholder="Mínimo 6 caracteres" autocomplete="new-password" required>
                    </div>
                </div>

                <div class="acceso-campo">
                    <label for="repetir_contrasena">Repetir contraseña</label>
                    <div class="acceso-entrada">
                        <span class="icono">&#128274;</span>
                        <input type="password" id="repetir_contrasena" name="repetir_contrasena" placeholder="Repetí la contraseña" autocomplete="new-password" required>
                    </div>
                </div>

                <button type="submit" class="acceso-boton">Crear cuenta</button>
            </form>
        <?php endif; ?>

        <div class="acceso-separador">o</div>

        <p class="acceso-pie">¿Ya tenés cuenta? <a class="acceso-enlace" href="/index.php?pagina=ingresar">Iniciá sesión</a></p>
    </main>
    <script>
        const selectorPais = document.getElementById("pais_telefono");
        const numeroTelefono = document.getElementById("telefono");
        const prefijoTelefono = document.querySelector("label[for='telefono'] + .acceso-entrada .icono");

        function actualizarPrefijoTelefono() {
            prefijoTelefono.textContent = selectorPais.selectedOptions[0].dataset.prefijo;
        }

        selectorPais.addEventListener("change", actualizarPrefijoTelefono);
        actualizarPrefijoTelefono();

        if (typeof Intl.DisplayNames === "function") {
            const nombresPaises = new Intl.DisplayNames(["es"], { type: "region" });
            Array.from(selectorPais.options)
                .sort((primero, segundo) => nombresPaises.of(primero.value).localeCompare(nombresPaises.of(segundo.value), "es"))
                .forEach((opcion) => {
                    opcion.textContent = `${nombresPaises.of(opcion.value)} (${opcion.dataset.prefijo})`;
                    selectorPais.appendChild(opcion);
                });
        }
    </script>
</body>
</html>
