<?php
require_once __DIR__ . "/../models/auth.php";
require_once __DIR__ . "/../models/horarios.php";
require_once __DIR__ . "/../models/resultados.php";

// solo admin y organizador agregan partidos desde aca
$puede_agregar = usuario_puede("registrar_resultados");

if ($puede_agregar && empty($_SESSION["csrf_horarios"])) {
    $_SESSION["csrf_horarios"] = bin2hex(random_bytes(32));
}

$mensaje = "";
$tipo_mensaje = "";
$formulario_con_error = false;
$enlace_calendario = null;

if (isset($_GET["agregado"])) {
    $mensaje = "El partido se agregó al calendario.";
    $tipo_mensaje = "exito";
    $liga_id = filter_input(INPUT_GET, "liga", FILTER_VALIDATE_INT);
    $torneo_id = filter_input(INPUT_GET, "torneo", FILTER_VALIDATE_INT);
    if ($liga_id && $torneo_id) {
        $enlace_calendario = "/index.php?pagina=enfrentamientos&liga=" . $liga_id . "&pestana=torneos&torneo=" . $torneo_id;
    }
} elseif (isset($_GET["eliminado"])) {
    $mensaje = "El partido se eliminó del calendario.";
    $tipo_mensaje = "exito";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (!$puede_agregar) {
        http_response_code(403);
        $mensaje = "No tenés permiso para agregar partidos.";
        $tipo_mensaje = "error";
    } elseif (!is_string($token) || !hash_equals($_SESSION["csrf_horarios"], $token)) {
        $mensaje = "La solicitud venció. Actualizá la página y probá de nuevo.";
        $tipo_mensaje = "error";
    } elseif (($_POST["accion"] ?? "") === "eliminar_horario") {
        $resultado = eliminar_horario($_POST["id"] ?? "");
        if ($resultado === "ok") {
            header("Location: /index.php?pagina=horarios&eliminado=1");
            exit;
        }
        $mensaje = $resultado;
        $tipo_mensaje = "error";
    } else {
        $resultado = agregar_horario($_POST, usuario_actual()["id"]);

        if (is_array($resultado)) {
            header("Location: /index.php?pagina=horarios&agregado=1&liga=" . $resultado["liga_id"] . "&torneo=" . $resultado["torneo_id"]);
            exit;
        }
        $mensaje = $resultado;
        $tipo_mensaje = "error";
        $formulario_con_error = true;
    }
}

$horarios_por_deporte = listar_horarios();
// mismas ligas que el formulario de resultados: con torneo activo y al menos dos equipos
$ligas_formulario = $puede_agregar ? ligas_para_resultados() : [];
require __DIR__ . "/../views/pages/horarios.php";
