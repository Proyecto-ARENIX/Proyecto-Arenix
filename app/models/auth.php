<?php
// cuentas: registro, inicio de sesión y cambio de contraseña

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/roles.php";
require_once __DIR__ . "/paises-telefono.php";

session_start();

// datos del usuario que entró (null si no hay nadie)
function usuario_actual() {
    static $usuario_cargado = false;
    static $usuario_actual = null;

    if (empty($_SESSION["usuario_id"])) {
        return null;
    }

    if ($usuario_cargado) {
        return $usuario_actual;
    }

    global $conexion;
    $consulta = $conexion->prepare("SELECT id, nombre, correo, foto, rol, pais_telefono, prefijo_telefono, telefono FROM usuarios WHERE id = ?");
    $consulta->execute([$_SESSION["usuario_id"]]);
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION = [];
        session_destroy();
        return null;
    }

    $_SESSION["usuario_nombre"] = $usuario["nombre"];
    $_SESSION["usuario_correo"] = $usuario["correo"];
    $_SESSION["usuario_foto"] = $usuario["foto"];
    $_SESSION["usuario_rol"] = $usuario["rol"];

    $usuario_actual = [
        "id"     => $_SESSION["usuario_id"],
        "nombre" => $_SESSION["usuario_nombre"],
        "correo"  => $_SESSION["usuario_correo"],
        "foto"   => $_SESSION["usuario_foto"] ?? null,
        "rol"    => $_SESSION["usuario_rol"],
        "pais_telefono" => $usuario["pais_telefono"],
        "prefijo_telefono" => $usuario["prefijo_telefono"],
        "telefono" => $usuario["telefono"],
    ];
    $usuario_cargado = true;

    return $usuario_actual;
}

function iniciar_sesion($usuario) {
    session_regenerate_id(true);
    $_SESSION["usuario_id"]     = $usuario["id"];
    $_SESSION["usuario_nombre"] = $usuario["nombre"];
    $_SESSION["usuario_correo"]  = $usuario["correo"];
    $_SESSION["usuario_foto"]   = $usuario["foto"] ?? null;
    $_SESSION["usuario_rol"]    = $usuario["rol"] ?? "publico";
}

function usuario_tiene_rol($roles) {
    $usuario = usuario_actual();
    if (!$usuario) {
        return false;
    }

    if ($usuario["rol"] === "admin") {
        return true;
    }

    return in_array($usuario["rol"], (array) $roles, true);
}

function usuario_puede($permiso) {
    $usuario = usuario_actual();
    if (!is_string($permiso)) {
        return false;
    }

    $permisos_por_rol = permisos_por_rol();
    $rol = $usuario ? $usuario["rol"] : "publico";
    return !empty($permisos_por_rol[$rol]["permisos"][$permiso]);
}

function es_administrador() {
    $usuario = usuario_actual();
    return $usuario && $usuario["rol"] === "admin";
}

function guardar_foto_perfil($usuario_id, $ruta_foto) {
    global $conexion;

    $actualizar = $conexion->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
    $actualizar->execute([$ruta_foto, $usuario_id]);
    $_SESSION["usuario_foto"] = $ruta_foto;
}

function cerrar_sesion() {
    session_destroy();
}

function registrar_usuario($nombre, $correo, $contrasena, $pais_telefono, $telefono) {
    global $conexion;

    if (!is_string($nombre) || !is_string($correo) || !is_string($contrasena) || !is_string($telefono)) {
        return "Revisá los datos.";
    }

    $nombre = trim($nombre);
    $correo  = trim(strtolower($correo));
    $telefono = trim($telefono);
    $paises_telefonicos = paises_con_codigo_telefonico();

    if (strlen($nombre) < 2) {
        return "Poné tu nombre.";
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return "El correo no es válido.";
    }
    if (strlen($contrasena) < 6) {
        return "La contraseña tiene que tener 6 caracteres o más.";
    }
    if (!is_string($pais_telefono) || !isset($paises_telefonicos[$pais_telefono])) {
        return "Elegí el país del teléfono.";
    }
    if (!preg_match('/^[0-9() .-]+$/', $telefono)) {
        return "El teléfono solo puede contener números, espacios y guiones.";
    }
    $telefono = preg_replace('/\D+/', '', $telefono);
    $prefijo_telefono = $paises_telefonicos[$pais_telefono];
    $digitos_prefijo = preg_replace('/\D+/', '', $prefijo_telefono);
    if (strlen($telefono) < 5 || strlen($telefono) + strlen($digitos_prefijo) > 15) {
        return "El número no es válido (con el prefijo no puede pasar de 15 dígitos).";
    }

    $consulta = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $consulta->execute([$correo]);
    if ($consulta->fetch()) {
        return "Ya existe una cuenta con ese correo.";
    }

    // las cuentas nuevas arrancan como publico, pasan a participante cuando un club las agrega.
    // si ese correo ya habia comprado un plan, arranca como cliente
    $compro = $conexion->prepare("SELECT 1 FROM contrataciones WHERE correo = ? LIMIT 1");
    $compro->execute([$correo]);
    $rol_inicial = $compro->fetchColumn() ? "cliente" : "publico";

    $clave_segura = password_hash($contrasena, PASSWORD_DEFAULT);
    $insertar = $conexion->prepare(
        "INSERT INTO usuarios (nombre, correo, contrasena, rol, pais_telefono, prefijo_telefono, telefono) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $insertar->execute([$nombre, $correo, $clave_segura, $rol_inicial, $pais_telefono, $prefijo_telefono, $telefono]);

    return "ok";
}

function autenticar($correo, $contrasena) {
    global $conexion;

    $correo = trim(strtolower($correo));

    $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $consulta->execute([$correo]);
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if (
        !$usuario ||
        !isset(roles_disponibles()[$usuario["rol"]]) ||
        !password_verify($contrasena, $usuario["contrasena"])
    ) {
        return null;
    }

    return $usuario;
}

function cambiar_contrasena($correo, $telefono, $nueva, $repetir) {
    global $conexion;

    $correo = trim(strtolower($correo));
    $telefono = preg_replace('/\D+/', '', $telefono);

    if (strlen($nueva) < 6) {
        return "La contraseña tiene que tener 6 caracteres o más.";
    }
    if ($nueva !== $repetir) {
        return "Las contraseñas no coinciden.";
    }

    $consulta = $conexion->prepare("SELECT prefijo_telefono, telefono FROM usuarios WHERE correo = ?");
    $consulta->execute([$correo]);
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    // se acepta el número con o sin el prefijo del país
    $guardado = (string) ($usuario["telefono"] ?? "");
    $prefijo = preg_replace('/\D+/', '', (string) ($usuario["prefijo_telefono"] ?? ""));
    if (
        !$usuario ||
        $guardado === "" ||
        ($telefono !== $guardado && $telefono !== $prefijo . $guardado)
    ) {
        return "El correo y el número no coinciden con ninguna cuenta.";
    }

    $clave_segura = password_hash($nueva, PASSWORD_DEFAULT);
    $actualizar = $conexion->prepare("UPDATE usuarios SET contrasena = ? WHERE correo = ?");
    $actualizar->execute([$clave_segura, $correo]);

    return "ok";
}
