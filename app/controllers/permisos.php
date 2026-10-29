<?php
require_once __DIR__ . "/../models/auth.php";

if (!es_administrador()) {
    http_response_code(403);
    exit("No tienes permiso para administrar usuarios.");
}

if (empty($_SESSION["csrf_permisos"])) {
    $_SESSION["csrf_permisos"] = bin2hex(random_bytes(32));
}

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    if (!is_string($token) || !hash_equals($_SESSION["csrf_permisos"], $token)) {
        $mensaje = "La solicitud venció. Actualiza la página e inténtalo de nuevo.";
        $tipo_mensaje = "error";
    } else {
        $accion = $_POST["accion"] ?? "";
        $usuario_id = filter_input(INPUT_POST, "usuario_id", FILTER_VALIDATE_INT);
        $administrador_id = usuario_actual()["id"];

        if ($accion === "cambiar_rol") {
            $resultado = asignar_rol_usuario($usuario_id, $_POST["rol"] ?? "", $administrador_id);
            $mensaje_exito = "El rol se actualizó correctamente.";
        } elseif ($accion === "cambiar_contrasena") {
            $resultado = cambiar_contrasena_usuario(
                $usuario_id,
                $_POST["nueva_contrasena"] ?? "",
                $_POST["confirmar_contrasena"] ?? "",
                $administrador_id
            );
            $mensaje_exito = "La contraseña se cambió correctamente.";
        } elseif ($accion === "eliminar_usuario") {
            $resultado = eliminar_usuario_admin($usuario_id, $administrador_id);
            $mensaje_exito = "La cuenta se eliminó correctamente.";
        } else {
            $resultado = "La acción solicitada no es válida.";
            $mensaje_exito = "";
        }

        $mensaje = $resultado === "ok" ? $mensaje_exito : $resultado;
        $tipo_mensaje = $resultado === "ok" ? "exito" : "error";
    }
}

$usuarios = listar_usuarios_con_rol();
$roles = roles_disponibles();
$matriz_permisos = permisos_por_rol();
$permisos = permisos_disponibles();
require __DIR__ . "/../views/pages/permisos.php";