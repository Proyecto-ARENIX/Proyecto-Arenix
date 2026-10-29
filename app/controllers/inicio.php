<?php
require_once __DIR__ . "/../models/auth.php";
require_once __DIR__ . "/../models/inicio.php";

// admin y organizador pueden editar cada cuadro de la portada
$puede_editar_portada = usuario_puede("publicar_noticias");

if ($puede_editar_portada && empty($_SESSION["csrf_inicio"])) {
    $_SESSION["csrf_inicio"] = bin2hex(random_bytes(32));
}

$mensaje = "";
$tipo_mensaje = "";
// cuadro que hay que volver a abrir si hubo un error
$cuadro_con_error = "";

if (isset($_GET["guardado"])) {
    $mensaje = "El cuadro se actualizó.";
    $tipo_mensaje = "exito";
}

$textos = textos_portada();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $cuadro = $_POST["cuadro"] ?? "";

    if (!$puede_editar_portada) {
        http_response_code(403);
        $mensaje = "No tenés permiso para editar la portada.";
        $tipo_mensaje = "error";
    } elseif (!is_string($token) || !hash_equals($_SESSION["csrf_inicio"], $token)) {
        $mensaje = "La solicitud venció. Actualizá la página y probá de nuevo.";
        $tipo_mensaje = "error";
    } else {
        $resultado = guardar_cuadro_portada($cuadro, $_POST, $_FILES);
        if ($resultado === "ok") {
            header("Location: /index.php?pagina=inicio&guardado=1");
            exit;
        }
        $mensaje = $resultado;
        $tipo_mensaje = "error";
        if (isset($textos[$cuadro])) {
            // se vuelve a mostrar lo que se escribio
            $cuadro_con_error = $cuadro;
            $textos[$cuadro] = array_merge($textos[$cuadro], array_intersect_key($_POST, $textos[$cuadro]));
        }
    }
}

$cantidad = fn($cuadro) => (int) $textos[$cuadro]["cantidad"];
$proximos = proximos_partidos_inicio(max($cantidad("enfrentamientos"), $cantidad("horarios")));
$resultados_recientes = ultimos_resultados_inicio($cantidad("resultados"));
$noticias_recientes = array_slice(listar_noticias(), 0, $cantidad("noticias"));
$lideres = lideres_de_ligas($cantidad("lideres"));
require __DIR__ . "/../views/pages/inicio.php";
