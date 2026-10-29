<?php
require_once __DIR__ . "/../models/auth.php";
require_once __DIR__ . "/../models/eventos.php";

// solo admin y organizador agregan y borran eventos
$puede_editar = usuario_puede("publicar_noticias");

if ($puede_editar && empty($_SESSION["csrf_eventos"])) {
    $_SESSION["csrf_eventos"] = bin2hex(random_bytes(32));
}

$mensaje = "";
$tipo_mensaje = "";
$formulario_con_error = false;

if (isset($_GET["agregado"])) {
    $mensaje = "El evento se agregó.";
    $tipo_mensaje = "exito";
} elseif (isset($_GET["editado"])) {
    $mensaje = "El evento se actualizó.";
    $tipo_mensaje = "exito";
} elseif (isset($_GET["eliminado"])) {
    $mensaje = "El evento se eliminó.";
    $tipo_mensaje = "exito";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (!$puede_editar) {
        http_response_code(403);
        $mensaje = "No tenés permiso para editar eventos.";
        $tipo_mensaje = "error";
    } elseif (!is_string($token) || !hash_equals($_SESSION["csrf_eventos"], $token)) {
        $mensaje = "La solicitud venció. Actualizá la página y probá de nuevo.";
        $tipo_mensaje = "error";
    } else {
        if (($_POST["accion"] ?? "") === "eliminar_evento") {
            $resultado = eliminar_evento($_POST["id"] ?? "");
            $volver = "eliminado=1";
        } else {
            $resultado = guardar_evento($_POST, usuario_actual()["id"]);
            $volver = !empty($_POST["id"]) ? "editado=1" : "agregado=1";
            $formulario_con_error = true;
        }

        if ($resultado === "ok") {
            header("Location: /index.php?pagina=eventos&" . $volver);
            exit;
        }
        $mensaje = $resultado;
        $tipo_mensaje = "error";
    }
}

$eventos_por_deporte = listar_eventos();
require __DIR__ . "/../views/pages/eventos.php";
