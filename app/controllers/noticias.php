<?php
require_once __DIR__ . "/../models/auth.php";
require_once __DIR__ . "/../models/noticias.php";

// solo admin y organizador pueden publicar
$puede_publicar = usuario_puede("publicar_noticias");

if ($puede_publicar && empty($_SESSION["csrf_noticias"])) {
    $_SESSION["csrf_noticias"] = bin2hex(random_bytes(32));
}

$mensaje = "";
$tipo_mensaje = "";
// que formulario hay que volver a abrir si hubo un error
$formulario_con_error = "";

if (isset($_GET["publicada"])) {
    $mensaje = "La noticia se publicó.";
    $tipo_mensaje = "exito";
} elseif (isset($_GET["comunicado"])) {
    $mensaje = "El comunicado se guardó.";
    $tipo_mensaje = "exito";
} elseif (isset($_GET["eliminado"])) {
    $mensaje = "Se eliminó correctamente.";
    $tipo_mensaje = "exito";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $accion = $_POST["accion"] ?? "";

    if (!$puede_publicar) {
        http_response_code(403);
        $mensaje = "No tenés permiso para publicar noticias.";
        $tipo_mensaje = "error";
    } elseif (!is_string($token) || !hash_equals($_SESSION["csrf_noticias"], $token)) {
        $mensaje = "La solicitud venció. Actualizá la página y probá de nuevo.";
        $tipo_mensaje = "error";
    } else {
        if ($accion === "eliminar_noticia") {
            $resultado = eliminar_noticia($_POST["id"] ?? "");
            $volver = "eliminado=1";
        } elseif ($accion === "eliminar_comunicado") {
            $resultado = eliminar_comunicado($_POST["id"] ?? "");
            $volver = "eliminado=1";
        } elseif ($accion === "guardar_comunicado") {
            $resultado = guardar_comunicado($_POST, usuario_actual()["id"]);
            $volver = "comunicado=1";
            $formulario_con_error = "comunicado";
        } else {
            $resultado = publicar_noticia($_POST, $_FILES["imagen"] ?? null, usuario_actual()["id"]);
            $volver = "publicada=1";
            $formulario_con_error = "noticia";
        }

        if ($resultado === "ok") {
            header("Location: /index.php?pagina=noticias&" . $volver);
            exit;
        }
        $mensaje = $resultado;
        $tipo_mensaje = "error";
    }
}

$contenido_noticias = [
    "noticias" => listar_noticias(),
    "comunicados" => listar_comunicados(),
];
$tipos_comunicado = tipos_comunicado();
require __DIR__ . "/../views/pages/noticias.php";
