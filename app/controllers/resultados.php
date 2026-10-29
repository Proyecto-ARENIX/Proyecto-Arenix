<?php
require_once __DIR__ . "/../models/auth.php";
require_once __DIR__ . "/../models/resultados.php";

// solo admin y organizador cargan y borran resultados
$puede_cargar = usuario_puede("registrar_resultados");

if ($puede_cargar && empty($_SESSION["csrf_resultados"])) {
    $_SESSION["csrf_resultados"] = bin2hex(random_bytes(32));
}

$mensaje = "";
$tipo_mensaje = "";
$formulario_con_error = false;

// link a la tabla de la liga donde se cargo el resultado
$enlace_tabla = null;

if (isset($_GET["agregado"])) {
    $mensaje = "El resultado se guardó y la tabla de posiciones se actualizó.";
    $tipo_mensaje = "exito";
    $liga_id = filter_input(INPUT_GET, "liga", FILTER_VALIDATE_INT);
    $torneo_id = filter_input(INPUT_GET, "torneo", FILTER_VALIDATE_INT);
    if ($liga_id && $torneo_id) {
        $enlace_tabla = "/index.php?pagina=enfrentamientos&liga=" . $liga_id . "&pestana=torneos&torneo=" . $torneo_id;
    }
} elseif (isset($_GET["eliminado"])) {
    $mensaje = "El resultado se eliminó.";
    $tipo_mensaje = "exito";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $accion = $_POST["accion"] ?? "";

    if (!$puede_cargar) {
        http_response_code(403);
        $mensaje = "No tenés permiso para cargar resultados.";
        $tipo_mensaje = "error";
    } elseif (!is_string($token) || !hash_equals($_SESSION["csrf_resultados"], $token)) {
        $mensaje = "La solicitud venció. Actualizá la página y probá de nuevo.";
        $tipo_mensaje = "error";
    } else {
        if ($accion === "eliminar_resultado") {
            $resultado = eliminar_resultado($_POST["id"] ?? "");
            $volver = "eliminado=1";
        } else {
            $resultado = agregar_resultado($_POST, usuario_actual()["id"]);
            $formulario_con_error = true;
            if (is_array($resultado)) {
                $volver = "agregado=1&liga=" . $resultado["liga_id"] . "&torneo=" . $resultado["torneo_id"];
                $resultado = "ok";
            }
        }

        if ($resultado === "ok") {
            header("Location: /index.php?pagina=resultados&" . $volver);
            exit;
        }
        $mensaje = $resultado;
        $tipo_mensaje = "error";
    }
}

$resultados_por_deporte = listar_resultados();
$ligas_formulario = $puede_cargar ? ligas_para_resultados() : [];
require __DIR__ . "/../views/pages/resultados.php";
