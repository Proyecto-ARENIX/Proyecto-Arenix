<?php
require_once __DIR__ . "/../../models/auth.php";

// si ya entro, lo mandamos al inicio
if (usuario_actual()) {
    header("Location: /index.php?pagina=inicio");
    exit;
}

$error = "";
$correo = "";
$paginas_protegidas = [
    "inicio", "enfrentamientos", "resultados", "horarios", "eventos",
    "noticias", "planes", "calificamos", "habilidades", "contacto", "perfil",
];
$pagina_destino = $_GET["volver"] ?? "inicio";
if (!in_array($pagina_destino, $paginas_protegidas, true)) {
    $pagina_destino = "inicio";
}
$mostrar_aviso = isset($_GET["volver"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // el campo del correo tiene un nombre distinto en cada carga (ver abajo)
    $campo_enviado = is_string($_POST["campo_correo"] ?? null) && preg_match('/^correo_[a-f0-9]{12}$/', $_POST["campo_correo"]) ? $_POST["campo_correo"] : "correo";
    $correo = is_string($_POST[$campo_enviado] ?? null) ? $_POST[$campo_enviado] : "";
    $contrasena = is_string($_POST["contrasena"] ?? null) ? $_POST["contrasena"] : "";

    $usuario = autenticar($correo, $contrasena);

    if ($usuario) {
        iniciar_sesion($usuario);
        header("Location: /index.php?pagina=" . rawurlencode($pagina_destino));
        exit;
    }
    $error = "Correo o contraseña incorrectos.";
}

// nombre al azar para que el navegador no sugiera correos de otras personas
$campo_correo = "correo_" . bin2hex(random_bytes(6));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - ARENIX</title>
    <link rel="stylesheet" href="/public/assets/css/acceso.css">
</head>
<body class="acceso-cuerpo">
    <main class="acceso-tarjeta">
        <a href="/index.php?pagina=inicio"><img class="acceso-logo" src="/public/assets/images/logo a.png" alt="ARENIX"></a>
        <h1>ARE<span>NIX</span></h1>
        <p class="acceso-subtitulo">Iniciá sesión para gestionar tu equipo</p>

        <?php if ($mostrar_aviso): ?>
            <div class="acceso-mensaje info visible">Tenés que iniciar sesión para ver esa página.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="acceso-mensaje error visible"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- sin autocompletar: en una compu compartida no se ven las cuentas de otros -->
        <form method="post" autocomplete="off">
            <input type="hidden" name="campo_correo" value="<?php echo $campo_correo; ?>">
            <div class="acceso-campo">
                <label for="correo">Correo electrónico</label>
                <div class="acceso-entrada">
                    <span class="icono">&#9993;</span>
                    <input type="email" id="correo" name="<?php echo $campo_correo; ?>" placeholder="tu@correo.com"
                           value="<?php echo htmlspecialchars($correo); ?>" autocomplete="off" required>
                </div>
            </div>

            <div class="acceso-campo">
                <label for="contrasena">Contraseña</label>
                <div class="acceso-entrada">
                    <span class="icono">&#128274;</span>
                    <!-- new-password hace que el navegador no rellene contraseñas guardadas -->
                    <input type="password" id="contrasena" name="contrasena" placeholder="Tu contraseña" autocomplete="new-password" required>
                </div>
            </div>

            <div class="acceso-fila">
                <label class="acceso-recordar">
                    <input type="checkbox" name="recordarme"> Recordarme
                </label>
                <a class="acceso-enlace" href="/index.php?pagina=recuperar-contrasena">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="acceso-boton">Iniciar sesión</button>
        </form>

        <div class="acceso-separador">o</div>

        <p class="acceso-pie">¿No tenés cuenta? <a class="acceso-enlace" href="/index.php?pagina=registro">Registrate acá</a></p>
    </main>
</body>
</html>
