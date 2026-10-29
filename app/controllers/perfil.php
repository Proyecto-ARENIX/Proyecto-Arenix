<?php
require_once __DIR__ . "/../models/auth.php";

$usuario = usuario_actual();
if (!$usuario) {
    header("Location: /index.php?pagina=ingresar");
    exit;
}

if (empty($_SESSION["csrf_perfil"])) {
    $_SESSION["csrf_perfil"] = bin2hex(random_bytes(32));
}

$error = "";
$actualizado = isset($_GET["actualizado"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    if (!is_string($token) || !hash_equals($_SESSION["csrf_perfil"], $token)) {
        $error = "La solicitud venció. Actualiza la página e inténtalo de nuevo.";
    } elseif (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        $error = "Selecciona una imagen válida para subir.";
    } elseif ($_FILES["foto"]["size"] > 2 * 1024 * 1024) {
        $error = "La imagen no puede superar los 2 MB.";
    } else {
        $archivo_temporal = $_FILES["foto"]["tmp_name"];
        $informacion_imagen = @getimagesize($archivo_temporal);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipo_mime = $finfo->file($archivo_temporal);
        $extensiones_permitidas = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp",
        ];

        if (
            !$informacion_imagen ||
            !isset($extensiones_permitidas[$tipo_mime]) ||
            $informacion_imagen["mime"] !== $tipo_mime ||
            $informacion_imagen[0] > 5000 ||
            $informacion_imagen[1] > 5000 ||
            $informacion_imagen[0] * $informacion_imagen[1] > 20000000
        ) {
            $error = "Usa una imagen JPG, PNG o WEBP válida de hasta 5000 × 5000 px.";
        } else {
            $directorio_fotos = dirname(__DIR__, 2) . "/public/assets/uploads/perfiles";
            if (!is_dir($directorio_fotos) && !mkdir($directorio_fotos, 0755, true) && !is_dir($directorio_fotos)) {
                $error = "No se pudo preparar el almacenamiento de fotos.";
            } else {
                $nombre_archivo = bin2hex(random_bytes(16)) . "." . $extensiones_permitidas[$tipo_mime];
                $destino = $directorio_fotos . "/" . $nombre_archivo;

                if (move_uploaded_file($archivo_temporal, $destino)) {
                    $ruta_publica = "/public/assets/uploads/perfiles/" . $nombre_archivo;
                    guardar_foto_perfil($usuario["id"], $ruta_publica);
                    header("Location: /index.php?pagina=perfil&actualizado=1");
                    exit;
                }

                $error = "No se pudo guardar la foto. Inténtalo nuevamente.";
            }
        }
    }
}

$usuario = usuario_actual();

// acceso rapido a los clubes del usuario (cliente: los que le asignaron; participante: a los que pertenece)
require_once __DIR__ . "/../models/equipos.php";
$mis_clubes = clubes_del_usuario($usuario);
$mis_ligas = ligas_del_usuario($usuario);

require __DIR__ . "/../views/pages/perfil.php";