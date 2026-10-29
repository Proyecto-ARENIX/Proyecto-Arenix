<?php
require_once __DIR__ . "/../../models/auth.php";

$error = "";
$exito = false;
$correo = "";
$telefono = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // correo y telefono tienen un nombre distinto en cada carga (ver abajo)
    $sufijo = is_string($_POST["sufijo_campos"] ?? null) && preg_match('/^[a-f0-9]{12}$/', $_POST["sufijo_campos"]) ? "_" . $_POST["sufijo_campos"] : "";
    $correo = is_string($_POST["correo" . $sufijo] ?? null) ? $_POST["correo" . $sufijo] : "";
    $telefono = is_string($_POST["telefono" . $sufijo] ?? null) ? trim($_POST["telefono" . $sufijo]) : "";
    $contrasena = is_string($_POST["contrasena"] ?? null) ? $_POST["contrasena"] : "";
    $repetir_contrasena = is_string($_POST["repetir_contrasena"] ?? null) ? $_POST["repetir_contrasena"] : "";

    $resultado = cambiar_contrasena($correo, $telefono, $contrasena, $repetir_contrasena);

    if ($resultado === "ok") {
        $exito = true;
    } else {
        $error = $resultado;
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
    <title>Recuperar contraseña - ARENIX</title>
    <link rel="stylesheet" href="/public/assets/css/acceso.css">
</head>
<body class="acceso-cuerpo">
    <main class="acceso-tarjeta">
        <a href="/index.php?pagina=inicio"><img class="acceso-logo" src="/public/assets/images/logo a.png" alt="ARENIX"></a>
        <h1>ARE<span>NIX</span></h1>
        <p class="acceso-subtitulo">Poné tu correo, tu número y una contraseña nueva</p>

        <?php if ($exito): ?>
            <div class="acceso-mensaje exito visible">
                Contraseña cambiada. Ya podés <a class="acceso-enlace" href="/index.php?pagina=ingresar">iniciar sesión</a>.
            </div>
        <?php else: ?>

            <?php if ($error): ?>
                <div class="acceso-mensaje error visible"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- sin autocompletar: en una compu compartida no se ven los datos de otros -->
            <form method="post" autocomplete="off">
                <input type="hidden" name="sufijo_campos" value="<?php echo $sufijo_campos; ?>">
                <div class="acceso-campo">
                    <label for="correo">Correo electrónico</label>
                    <div class="acceso-entrada">
                        <span class="icono">&#9993;</span>
                        <input type="email" id="correo" name="correo_<?php echo $sufijo_campos; ?>" placeholder="tu@correo.com"
                               value="<?php echo htmlspecialchars($correo); ?>" autocomplete="off" required>
                    </div>
                </div>

                <div class="acceso-campo">
                    <label for="telefono">Número de teléfono</label>
                    <div class="acceso-entrada">
                        <span class="icono" aria-hidden="true">&#9742;</span>
                        <input type="tel" id="telefono" name="telefono_<?php echo $sufijo_campos; ?>" inputmode="tel" autocomplete="off" placeholder="El que pusiste al registrarte"
                               value="<?php echo htmlspecialchars($telefono); ?>" minlength="5" maxlength="20" pattern="[0-9() +.-]{5,20}" required>
                    </div>
                    <small class="acceso-ayuda">Podés ponerlo con o sin el prefijo del país.</small>
                </div>

                <div class="acceso-campo">
                    <label for="contrasena">Contraseña nueva</label>
                    <div class="acceso-entrada">
                        <span class="icono">&#128274;</span>
                        <input type="password" id="contrasena" name="contrasena" placeholder="Mínimo 6 caracteres" autocomplete="new-password" required>
                    </div>
                </div>

                <div class="acceso-campo">
                    <label for="repetir_contrasena">Repetir contraseña nueva</label>
                    <div class="acceso-entrada">
                        <span class="icono">&#128274;</span>
                        <input type="password" id="repetir_contrasena" name="repetir_contrasena" placeholder="Repetí la contraseña" autocomplete="new-password" required>
                    </div>
                </div>

                <button type="submit" class="acceso-boton">Cambiar contraseña</button>
            </form>
        <?php endif; ?>

        <div class="acceso-separador">o</div>

        <p class="acceso-pie"><a class="acceso-enlace" href="/index.php?pagina=ingresar">Volver a iniciar sesión</a></p>
    </main>
</body>
</html>
