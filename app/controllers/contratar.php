<?php
// contratar un plan: datos + pago con tarjeta (en modo prueba, ver models/contrataciones.php)
require_once __DIR__ . "/../models/auth.php";
require_once __DIR__ . "/../models/contrataciones.php";

$usuario = usuario_actual();
if (empty($_SESSION["csrf_contratar"])) {
    $_SESSION["csrf_contratar"] = bin2hex(random_bytes(32));
}

$planes = planes_contratables();
$deportes = deportes_clubes();
$error = "";
$contratacion = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    if (!is_string($token) || !hash_equals($_SESSION["csrf_contratar"], $token)) {
        $error = "La solicitud venció. Actualiza la página e inténtalo de nuevo.";
    } else {
        $resultado = crear_contratacion($_POST, $usuario["id"] ?? null);
        if (is_array($resultado)) {
            // solo quien hizo la contratacion puede ver su comprobante
            $_SESSION["contrataciones_hechas"][] = $resultado["ok"];
            // para avisar en el comprobante que la cuenta ya es cliente
            if ($resultado["rol_cambiado"]) {
                $_SESSION["paso_a_cliente"] = $resultado["ok"];
            }
            header("Location: /index.php?pagina=contratar&orden=" . $resultado["ok"]);
            exit;
        }
        $error = $resultado;
    }
}

// comprobante de una contratacion recien hecha
$orden = filter_var($_GET["orden"] ?? null, FILTER_VALIDATE_INT);
if ($orden && in_array($orden, $_SESSION["contrataciones_hechas"] ?? [], true)) {
    $contratacion = buscar_contratacion($orden);
}
$paso_a_cliente = $contratacion && ($_SESSION["paso_a_cliente"] ?? null) === $orden;

// plan elegido: el del formulario (si hubo error), el del boton de Planes, o el Profesional
$plan_elegido = $_POST["plan"] ?? ($_GET["plan"] ?? "profesional");
if (!is_string($plan_elegido) || !isset($planes[$plan_elegido])) {
    $plan_elegido = "profesional";
}

require __DIR__ . "/../views/pages/contratar.php";
