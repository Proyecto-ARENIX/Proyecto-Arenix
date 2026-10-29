<?php
require_once __DIR__ . "/database.php";

function roles_disponibles() {
    return [
        "admin" => "Administrador",
        "organizador" => "Organizador",
        "participante" => "Participante",
        "cliente" => "Cliente",
        // rol con el que se registran las cuentas nuevas; pasa a "participante" cuando un club lo agrega
        "publico" => "Público (visitante)",
    ];
}

function permisos_disponibles() {
    return [
        "crear_torneos" => "Crear torneos",
        "editar_torneos" => "Editar torneos",
        "eliminar_torneos" => "Eliminar torneos",
        "gestionar_ligas" => "Crear ligas y asignarles plan",
        "gestionar_clubes" => "Agregar clubes",
        "editar_clubes" => "Editar clubes (los clientes, solo los asignados)",
        "editar_habilidades" => "Editar habilidades de los equipos",
        "registrar_resultados" => "Registrar resultados",
        "registrar_sanciones" => "Cargar tarjetas y sanciones",
        "ver_resultados" => "Ver resultados",
        "ver_rankings" => "Ver rankings",
        "ver_jugadores" => "Ver jugadores",
        "publicar_noticias" => "Publicar noticias",
        "administrar_usuarios" => "Administrar usuarios",
    ];
}

function permisos_por_rol() {
    $solo_lectura = [
        "crear_torneos" => false,
        "editar_torneos" => false,
        "eliminar_torneos" => false,
        "gestionar_ligas" => false,
        "gestionar_clubes" => false,
        "editar_clubes" => false,
        "editar_habilidades" => false,
        "registrar_resultados" => false,
        "registrar_sanciones" => false,
        "ver_resultados" => true,
        "ver_rankings" => true,
        "ver_jugadores" => true,
        "publicar_noticias" => false,
        "administrar_usuarios" => false,
    ];
    $admin = array_fill_keys(array_keys(permisos_disponibles()), true);
    $organizador = $solo_lectura;
    $organizador["crear_torneos"] = true;
    $organizador["editar_torneos"] = true;
    $organizador["gestionar_ligas"] = true;
    $organizador["gestionar_clubes"] = true;
    $organizador["editar_clubes"] = true;
    $organizador["editar_habilidades"] = true;
    $cliente = $solo_lectura;
    $cliente["editar_clubes"] = true;
    $organizador["registrar_resultados"] = true;
    $organizador["registrar_sanciones"] = true;
    $organizador["publicar_noticias"] = true;

    return [
        "admin" => ["nombre" => "Administrador", "permisos" => $admin],
        "organizador" => ["nombre" => "Organizador", "permisos" => $organizador],
        "participante" => ["nombre" => "Participante", "permisos" => $solo_lectura],
        "cliente" => ["nombre" => "Cliente", "permisos" => $cliente],
        "publico" => ["nombre" => "Público (visitante)", "permisos" => $solo_lectura],
    ];
}

function listar_usuarios_con_rol() {
    global $conexion;

    $consulta = $conexion->query("SELECT id, nombre, correo, rol FROM usuarios ORDER BY nombre, id");
    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

function asignar_rol_usuario($usuario_id, $nuevo_rol, $administrador_id) {
    global $conexion;

    $roles = roles_disponibles();
    if (!filter_var($usuario_id, FILTER_VALIDATE_INT)) {
        return "La cuenta seleccionada no es válida.";
    }
    if (!filter_var($administrador_id, FILTER_VALIDATE_INT) || (int) $administrador_id < 1) {
        return "No tienes permiso para administrar roles.";
    }
    if (!is_string($nuevo_rol) || !isset($roles[$nuevo_rol])) {
        return "El rol seleccionado no es válido.";
    }
    if ((int) $usuario_id === (int) $administrador_id) {
        return "No puedes cambiar tu propio rol desde este panel.";
    }

    try {
        $conexion->beginTransaction();
        $usuarios = $conexion->query("SELECT id, rol FROM usuarios FOR UPDATE")->fetchAll(PDO::FETCH_ASSOC);
        $rol_actual = null;
        $cantidad_administradores = 0;
        $administrador_autorizado = false;

        foreach ($usuarios as $usuario) {
            if ((int) $usuario["id"] === (int) $usuario_id) {
                $rol_actual = $usuario["rol"];
            }
            if ((int) $usuario["id"] === (int) $administrador_id && $usuario["rol"] === "admin") {
                $administrador_autorizado = true;
            }
            if ($usuario["rol"] === "admin") {
                $cantidad_administradores++;
            }
        }

        if (!$administrador_autorizado) {
            $conexion->rollBack();
            return "No tienes permiso para administrar roles.";
        }
        if ($rol_actual === null) {
            $conexion->rollBack();
            return "No se encontró esa cuenta.";
        }
        if ($rol_actual === "admin" && $nuevo_rol !== "admin" && $cantidad_administradores <= 1) {
            $conexion->rollBack();
            return "Debe quedar al menos un administrador en el sistema.";
        }

        $actualizar = $conexion->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        $actualizar->execute([$nuevo_rol, $usuario_id]);
        $conexion->commit();

        return "ok";
    } catch (PDOException $error) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        return "No se pudo actualizar el rol. Inténtalo nuevamente.";
    }
}

function cambiar_contrasena_usuario($usuario_id, $nueva_contrasena, $confirmar_contrasena, $administrador_id) {
    global $conexion;

    if (!filter_var($usuario_id, FILTER_VALIDATE_INT) || (int) $usuario_id < 1) {
        return "La cuenta seleccionada no es válida.";
    }
    if (!filter_var($administrador_id, FILTER_VALIDATE_INT) || (int) $administrador_id < 1) {
        return "No tienes permiso para administrar usuarios.";
    }
    if (!is_string($nueva_contrasena) || strlen($nueva_contrasena) < 6) {
        return "La contraseña debe tener al menos 6 caracteres.";
    }
    if (!is_string($confirmar_contrasena) || $nueva_contrasena !== $confirmar_contrasena) {
        return "Las contraseñas no coinciden.";
    }

    try {
        $conexion->beginTransaction();
        $usuarios = $conexion->query("SELECT id, rol FROM usuarios FOR UPDATE")->fetchAll(PDO::FETCH_ASSOC);
        $administrador_autorizado = false;
        $usuario_encontrado = false;

        foreach ($usuarios as $usuario) {
            if ((int) $usuario["id"] === (int) $administrador_id && $usuario["rol"] === "admin") {
                $administrador_autorizado = true;
            }
            if ((int) $usuario["id"] === (int) $usuario_id) {
                $usuario_encontrado = true;
            }
        }

        if (!$administrador_autorizado) {
            $conexion->rollBack();
            return "No tienes permiso para administrar usuarios.";
        }
        if (!$usuario_encontrado) {
            $conexion->rollBack();
            return "No se encontró esa cuenta.";
        }

        $clave_segura = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
        $actualizar = $conexion->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
        $actualizar->execute([$clave_segura, $usuario_id]);
        $conexion->commit();

        return "ok";
    } catch (PDOException $error) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        return "No se pudo cambiar la contraseña. Inténtalo nuevamente.";
    }
}

function eliminar_usuario_admin($usuario_id, $administrador_id) {
    global $conexion;

    if (!filter_var($usuario_id, FILTER_VALIDATE_INT) || (int) $usuario_id < 1) {
        return "La cuenta seleccionada no es válida.";
    }
    if (!filter_var($administrador_id, FILTER_VALIDATE_INT) || (int) $administrador_id < 1) {
        return "No tienes permiso para administrar usuarios.";
    }
    if ((int) $usuario_id === (int) $administrador_id) {
        return "No puedes eliminar tu propia cuenta desde este panel.";
    }

    try {
        $conexion->beginTransaction();
        $usuarios = $conexion->query("SELECT id, rol FROM usuarios FOR UPDATE")->fetchAll(PDO::FETCH_ASSOC);
        $administrador_autorizado = false;
        $usuario_encontrado = false;
        $cantidad_administradores = 0;
        $usuario_es_administrador = false;

        foreach ($usuarios as $usuario) {
            if ((int) $usuario["id"] === (int) $administrador_id && $usuario["rol"] === "admin") {
                $administrador_autorizado = true;
            }
            if ((int) $usuario["id"] === (int) $usuario_id) {
                $usuario_encontrado = true;
                $usuario_es_administrador = $usuario["rol"] === "admin";
            }
            if ($usuario["rol"] === "admin") {
                $cantidad_administradores++;
            }
        }

        if (!$administrador_autorizado) {
            $conexion->rollBack();
            return "No tienes permiso para administrar usuarios.";
        }
        if (!$usuario_encontrado) {
            $conexion->rollBack();
            return "No se encontró esa cuenta.";
        }
        if ($usuario_es_administrador && $cantidad_administradores <= 1) {
            $conexion->rollBack();
            return "Debe quedar al menos un administrador en el sistema.";
        }

        $eliminar = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
        $eliminar->execute([$usuario_id]);
        $conexion->commit();

        return "ok";
    } catch (PDOException $error) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        return "No se pudo eliminar la cuenta. Puede que tenga información asociada.";
    }
}