<?php
// ligas: cada una tiene un plan que pone los limites (equipos, jugadores, torneos y admins)

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/equipos.php";

// jugadores gratis que trae cada plan
const JUGADORES_GRATIS_PLAN = 10;

// limites de cada plan (igual que en la pagina de planes)
function planes_liga() {
    return [
        "basico"      => ["nombre" => "Plan Básico",      "nivel" => 1, "equipos" => 8,  "jugadores" => 224,  "torneos" => 1, "administradores" => 1],
        "profesional" => ["nombre" => "Plan Profesional", "nivel" => 2, "equipos" => 20, "jugadores" => 560,  "torneos" => 3, "administradores" => 3],
        "enterprise"  => ["nombre" => "Plan Enterprise",  "nivel" => 3, "equipos" => 50, "jugadores" => 1400, "torneos" => 8, "administradores" => 10],
    ];
}

// funciones: clave => [nombre, plan minimo, si ya anda]
function funciones_plan() {
    return [
        "equipos"                => ["Registro de equipos y jugadores", "basico", true],
        "calendario"             => ["Calendario de partidos", "basico", true],
        "resultados"             => ["Registro de resultados", "basico", true],
        "tabla"                  => ["Tabla de posiciones", "basico", true],
        "estadisticas_basicas"   => ["Estadísticas básicas", "basico", true],
        "perfiles"               => ["Perfiles individuales de jugadores (con foto)", "profesional", true],
        "multiples_torneos"      => ["Gestión de múltiples torneos", "profesional", true],
        "noticias"               => ["Tablón de noticias", "profesional", false],
        "personalizacion"        => ["Personalización básica", "profesional", false],
        "estadisticas_avanzadas" => ["Estadísticas avanzadas completas (habilidades de cada equipo)", "enterprise", true],
        "perfiles_completos"     => ["Perfiles completos de jugadores", "enterprise", false],
        "biometricos"            => ["Datos biométricos", "enterprise", false],
        "reportes"               => ["Reportes avanzados", "enterprise", false],
        "veo"                    => ["Veo Cam 3 proporcionada por Arenix mediante contrato", "enterprise", false],
        "grabaciones"            => ["Integración, almacenamiento y organización de grabaciones", "enterprise", false],
    ];
}

function plan_incluye($plan, $funcion) {
    $planes = planes_liga();
    $funciones = funciones_plan();
    if (!isset($planes[$plan], $funciones[$funcion])) {
        return false;
    }
    return $planes[$plan]["nivel"] >= $planes[$funciones[$funcion][1]]["nivel"];
}


// ---- ligas y sus equipos ----

// club => liga. la primera vez crea las ligas de ejemplo
function mapa_liga_equipos($recargar = false) {
    global $conexion;
    static $mapa = null;

    if ($mapa === null || $recargar) {
        crear_ligas_demo();
        $mapa = [];
        foreach ($conexion->query("SELECT club_clave, liga_id FROM liga_equipos") as $fila) {
            $mapa[$fila["club_clave"]] = (int) $fila["liga_id"];
        }
    }
    return $mapa;
}

function liga_de_club($club_id) {
    return mapa_liga_equipos()[$club_id] ?? null;
}

// todas las ligas con lo que usan y sus limites
function listar_ligas() {
    global $conexion;

    $mapa = mapa_liga_equipos();
    $jugadores_por_club = [];
    foreach (listar_equipos() as $equipo) {
        $jugadores_por_club[$equipo["id"]] = count($equipo["jugadores"]);
    }

    $torneos_activos = [];
    foreach ($conexion->query("SELECT liga_id, COUNT(*) AS n FROM torneos WHERE estado = 'activo' GROUP BY liga_id") as $fila) {
        $torneos_activos[(int) $fila["liga_id"]] = (int) $fila["n"];
    }
    $administradores = [];
    foreach ($conexion->query(
        "SELECT la.liga_id, COUNT(*) AS n FROM liga_administradores la JOIN usuarios u ON u.id = la.usuario_id
         WHERE u.rol = 'cliente' GROUP BY la.liga_id"
    ) as $fila) {
        $administradores[(int) $fila["liga_id"]] = (int) $fila["n"];
    }

    $planes = planes_liga();
    $deportes = deportes_clubes();
    $ligas = [];
    foreach ($conexion->query("SELECT * FROM ligas ORDER BY nombre") as $fila) {
        $id = (int) $fila["id"];
        $plan = isset($planes[$fila["plan"]]) ? $fila["plan"] : "basico";
        $clubes = array_keys($mapa, $id, true);

        $ligas[$id] = [
            "id"              => $id,
            "nombre"          => $fila["nombre"],
            "logo"            => $fila["logo"] ?? null,
            "portada"         => $fila["portada"] ?? null,
            "iniciales"       => iniciales_liga($fila["nombre"]),
            "deporte"         => $fila["deporte"],
            "nombre_deporte"  => $deportes[$fila["deporte"]] ?? $fila["deporte"],
            "plan"            => $plan,
            "nombre_plan"     => $planes[$plan]["nombre"],
            "equipos_extra"   => (int) $fila["equipos_extra"],
            "jugadores_extra" => (int) $fila["jugadores_extra"],
            "clubes"          => $clubes,
            "uso" => [
                "equipos"         => count($clubes),
                "jugadores"       => array_sum(array_map(fn($c) => $jugadores_por_club[$c] ?? 0, $clubes)),
                "torneos"         => $torneos_activos[$id] ?? 0,
                "administradores" => $administradores[$id] ?? 0,
            ],
            "limites" => [
                "equipos"         => $planes[$plan]["equipos"] + (int) $fila["equipos_extra"],
                "jugadores"       => $planes[$plan]["jugadores"] + JUGADORES_GRATIS_PLAN + (int) $fila["jugadores_extra"],
                "torneos"         => $planes[$plan]["torneos"],
                "administradores" => $planes[$plan]["administradores"],
            ],
        ];
    }
    return $ligas;
}

// iniciales si no tiene logo (salteando liga, copa, del, etc)
function iniciales_liga($nombre) {
    $comunes = ["liga", "copa", "torneo", "campeonato", "de", "del", "la", "el", "los", "las", "y"];
    $palabras = preg_split('/\s+/u', preg_replace('/\(.*?\)/u', "", (string) $nombre), -1, PREG_SPLIT_NO_EMPTY);
    $importantes = array_values(array_filter($palabras, fn($p) => !in_array(mb_strtolower($p, "UTF-8"), $comunes, true)));
    if (count($importantes) === 0) {
        $importantes = $palabras;
    }

    if (count($importantes) === 1) {
        $iniciales = mb_substr($importantes[0], 0, 2, "UTF-8");
    } else {
        $iniciales = mb_substr($importantes[0], 0, 1, "UTF-8") . mb_substr($importantes[1], 0, 1, "UTF-8");
    }
    return mb_strtoupper($iniciales, "UTF-8") ?: "L";
}

function buscar_liga($liga_id) {
    $liga_id = filter_var($liga_id, FILTER_VALIDATE_INT);
    return $liga_id === false ? null : (listar_ligas()[$liga_id] ?? null);
}

// crea una liga (sin $liga_id) o la edita
function guardar_liga($datos, $usuario_id, $liga_id = null, $archivo_logo = null) {
    global $conexion;

    $campo = fn($clave) => isset($datos[$clave]) && is_string($datos[$clave]) ? trim($datos[$clave]) : "";
    $nombre = $campo("nombre");
    $deporte = $campo("deporte");
    $plan = $campo("plan");
    $extra = fn($clave) => filter_var($datos[$clave] ?? 0, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1000]]);
    $equipos_extra = $campo("equipos_extra") === "" ? 0 : $extra("equipos_extra");
    $jugadores_extra = $campo("jugadores_extra") === "" ? 0 : $extra("jugadores_extra");

    $actual = $liga_id !== null ? buscar_liga($liga_id) : null;
    if ($liga_id !== null && !$actual) {
        return "No se encontró la liga.";
    }

    if (!texto_valido($nombre, 3, 60)) {
        return "Escribe el nombre de la liga (3 a 60 caracteres).";
    }
    if (!isset(planes_liga()[$plan])) {
        return "Elige un plan válido.";
    }
    if ($equipos_extra === false || $jugadores_extra === false) {
        return "Los equipos y jugadores adicionales tienen que ser números de 0 a 1000.";
    }
    if ($actual) {
        // el deporte no se cambia porque los equipos son de ese deporte
        $deporte = $actual["deporte"];
    } elseif (!isset(deportes_clubes()[$deporte])) {
        return "Elige un deporte válido.";
    }

    $existe = $conexion->prepare("SELECT id FROM ligas WHERE nombre = ? AND id <> ?");
    $existe->execute([$nombre, $actual ? $actual["id"] : 0]);
    if ($existe->fetch()) {
        return "Ya existe una liga con ese nombre.";
    }

    if ($actual) {
        $consulta = $conexion->prepare("UPDATE ligas SET nombre = ?, plan = ?, equipos_extra = ?, jugadores_extra = ? WHERE id = ?");
        $consulta->execute([$nombre, $plan, $equipos_extra, $jugadores_extra, $actual["id"]]);
        return ["ok" => $actual["id"]];
    }

    $consulta = $conexion->prepare("INSERT INTO ligas (nombre, deporte, plan, equipos_extra, jugadores_extra, creado_por) VALUES (?, ?, ?, ?, ?, ?)");
    $consulta->execute([$nombre, $deporte, $plan, $equipos_extra, $jugadores_extra, $usuario_id]);
    $id = (int) $conexion->lastInsertId();

    // si falla una imagen la liga se crea igual
    $hay_imagenes = is_array($archivo_logo) && count(array_filter($archivo_logo, fn($a) => is_array($a) && ($a["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE)) > 0;
    if ($hay_imagenes) {
        $imagenes = guardar_imagenes_liga($id, $archivo_logo, []);
        if ($imagenes !== "ok") {
            return ["ok" => $id, "aviso" => $imagenes];
        }
    }
    return ["ok" => $id];
}

// logo y portada de la liga
function guardar_imagenes_liga($liga_id, $archivos, $datos) {
    global $conexion;

    $liga = buscar_liga($liga_id);
    if (!$liga) {
        return "No se encontró la liga.";
    }

    $nuevas = [];
    $subidas = [];
    foreach (["logo" => "El logo", "portada" => "La foto de portada"] as $columna => $que) {
        $archivo = $archivos[$columna] ?? null;
        $hay_archivo = is_array($archivo) && ($archivo["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        if ($hay_archivo) {
            $subida = subir_imagen_club($archivo, "ligas", $que);
            if (isset($subida["error"])) {
                foreach ($subidas as $ruta) {
                    borrar_imagen_subida($ruta);
                }
                return $subida["error"];
            }
            $nuevas[$columna] = $subidas[] = $subida["ruta"];
        } elseif (($datos["quitar_" . $columna] ?? "") === "1") {
            $nuevas[$columna] = null;
        }
    }

    if (count($nuevas) === 0) {
        return "Elige una imagen para subir (o marca la que quieres quitar).";
    }

    try {
        $sets = implode(", ", array_map(fn($columna) => "$columna = ?", array_keys($nuevas)));
        $consulta = $conexion->prepare("UPDATE ligas SET $sets WHERE id = ?");
        $consulta->execute([...array_values($nuevas), $liga["id"]]);
    } catch (PDOException $error) {
        foreach ($subidas as $ruta) {
            borrar_imagen_subida($ruta);
        }
        return "No se pudieron guardar las imágenes. Inténtalo nuevamente.";
    }

    // borrar las imagenes viejas
    foreach ($nuevas as $columna => $ruta) {
        if ($liga[$columna] && $liga[$columna] !== $ruta) {
            borrar_imagen_subida($liga[$columna]);
        }
    }
    return "ok";
}

// borra la liga con sus torneos, partidos y admins. los equipos quedan sin liga
function eliminar_liga($liga_id, $confirmacion) {
    global $conexion;

    $liga = buscar_liga($liga_id);
    if (!$liga) {
        return "No se encontró la liga.";
    }
    $normalizar = fn($texto) => mb_strtolower(trim(preg_replace('/\s+/u', " ", (string) $texto)), "UTF-8");
    if ($normalizar($confirmacion) !== $normalizar($liga["nombre"])) {
        return "Para eliminar la liga escribe su nombre exactamente: " . $liga["nombre"];
    }

    $transaccion_propia = !$conexion->inTransaction();
    try {
        if ($transaccion_propia) {
            $conexion->beginTransaction();
        }
        $id = $liga["id"];
        $conexion->prepare("DELETE p FROM partidos p JOIN torneos t ON t.id = p.torneo_id WHERE t.liga_id = ?")->execute([$id]);
        $conexion->prepare("DELETE FROM torneos WHERE liga_id = ?")->execute([$id]);
        $conexion->prepare("DELETE FROM liga_administradores WHERE liga_id = ?")->execute([$id]);
        $conexion->prepare("DELETE FROM liga_equipos WHERE liga_id = ?")->execute([$id]);
        $conexion->prepare("DELETE FROM ligas WHERE id = ?")->execute([$id]);
        if ($transaccion_propia) {
            $conexion->commit();
        }
    } catch (PDOException $error) {
        if ($transaccion_propia && $conexion->inTransaction()) {
            $conexion->rollBack();
        }
        return "No se pudo eliminar la liga. Inténtalo nuevamente.";
    }

    borrar_imagen_subida($liga["logo"]);
    borrar_imagen_subida($liga["portada"]);
    mapa_liga_equipos(true);
    return ["ok" => count($liga["clubes"])];
}

// equipos sin liga (de ligas que se borraron)
function equipos_sin_liga() {
    $mapa = mapa_liga_equipos();
    return array_values(array_filter(listar_equipos(), fn($equipo) => !isset($mapa[$equipo["id"]])));
}

// el cliente solo puede borrar equipos de sus ligas
function puede_eliminar_club($club_id) {
    if (usuario_puede("gestionar_clubes")) {
        return true;
    }
    $liga_id = is_string($club_id) ? liga_de_club($club_id) : null;
    return $liga_id !== null && puede_administrar_liga($liga_id);
}

// borra un equipo con todo lo suyo
function eliminar_club($club_id, $confirmacion, $usuario_id) {
    global $conexion;

    $equipo = is_string($club_id) ? buscar_equipo($club_id) : null;
    if (!$equipo) {
        return "No se encontró el equipo.";
    }
    $normalizar = fn($texto) => mb_strtolower(trim(preg_replace('/\s+/u', " ", (string) $texto)), "UTF-8");
    if ($normalizar($confirmacion) !== $normalizar($equipo["nombre"])) {
        return "Para eliminar el equipo escribe su nombre exactamente: " . $equipo["nombre"];
    }

    $id = $equipo["id"];
    $liga_id = liga_de_club($id);
    $transaccion_propia = !$conexion->inTransaction();
    try {
        if ($transaccion_propia) {
            $conexion->beginTransaction();
        }
        $conexion->prepare("DELETE FROM partidos WHERE local = ? OR visitante = ?")->execute([$id, $id]);
        foreach (["liga_equipos", "club_editores", "club_participantes", "club_sanciones", "club_partidos"] as $tabla) {
            $conexion->prepare("DELETE FROM $tabla WHERE club_clave = ?")->execute([$id]);
        }
        if (preg_match('/^club-(\d+)$/', $id, $partes)) {
            $conexion->prepare("DELETE FROM clubes WHERE id = ?")->execute([(int) $partes[1]]);
        } else {
            // si es de ejemplo se marca como eliminado
            $conexion->prepare("DELETE FROM clubes WHERE clave_base = ?")->execute([$id]);
            $conexion->prepare("INSERT IGNORE INTO equipos_eliminados (club_clave, eliminado_por) VALUES (?, ?)")->execute([$id, $usuario_id]);
        }
        if ($transaccion_propia) {
            $conexion->commit();
        }
    } catch (PDOException $error) {
        if ($transaccion_propia && $conexion->inTransaction()) {
            $conexion->rollBack();
        }
        return "No se pudo eliminar el equipo. Inténtalo nuevamente.";
    }

    // las imagenes fijas de assets no se tocan
    borrar_imagen_subida($equipo["logo"]);
    foreach ($equipo["jugadores"] as $jugador) {
        borrar_imagen_subida($jugador["foto"] ?? null);
    }
    mapa_liga_equipos(true);
    return ["ok" => $liga_id];
}

// pasar un equipo sin liga a otra del mismo deporte
function mover_club_a_liga($club_id, $liga_id) {
    $equipo = is_string($club_id) ? buscar_equipo($club_id) : null;
    $liga = buscar_liga($liga_id);
    if (!$equipo) {
        return "No se encontró el equipo.";
    }
    if (!$liga) {
        return "Elige una liga.";
    }
    if (liga_de_club($equipo["id"]) !== null) {
        return "Ese equipo ya está en una liga.";
    }
    if ($equipo["deporte"] !== $liga["deporte"]) {
        return "El equipo es de " . $equipo["nombre_deporte"] . " y la liga de " . $liga["nombre_deporte"] . ".";
    }
    if ($liga["uso"]["equipos"] >= $liga["limites"]["equipos"]) {
        return "La liga llegó al límite de " . $liga["limites"]["equipos"] . " equipos de su plan.";
    }
    if ($liga["uso"]["jugadores"] + count($equipo["jugadores"]) > $liga["limites"]["jugadores"]) {
        return "La liga no tiene lugar para los " . count($equipo["jugadores"]) . " jugadores del equipo (límite de " . $liga["limites"]["jugadores"] . ").";
    }

    asignar_club_a_liga($equipo["id"], $liga["id"]);
    return "ok";
}

function asignar_club_a_liga($club_id, $liga_id) {
    global $conexion;

    $consulta = $conexion->prepare("INSERT INTO liga_equipos (club_clave, liga_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE liga_id = VALUES(liga_id)");
    $consulta->execute([$club_id, $liga_id]);
    mapa_liga_equipos(true);
}

// ligas de ejemplo, una por deporte, con un torneo y algunos partidos
function crear_ligas_demo() {
    global $conexion;
    static $revisado = false;

    if ($revisado) {
        return;
    }
    $revisado = true;

    // solo una vez, si las borran no vuelven
    if ($conexion->query("SELECT valor FROM configuracion WHERE clave = 'ligas_demo_creadas'")->fetchColumn()) {
        return;
    }
    $conexion->exec("INSERT IGNORE INTO configuracion (clave, valor) VALUES ('ligas_demo_creadas', '1')");
    if ((int) $conexion->query("SELECT COUNT(*) FROM ligas")->fetchColumn() > 0) {
        return;
    }

    $deportes = deportes_clubes();
    $por_deporte = [];
    foreach (listar_equipos() as $equipo) {
        $por_deporte[$equipo["deporte"]][] = $equipo["id"];
    }

    $crear_liga = $conexion->prepare("INSERT INTO ligas (nombre, deporte, plan) VALUES (?, ?, 'enterprise')");
    $asignar = $conexion->prepare("INSERT IGNORE INTO liga_equipos (club_clave, liga_id) VALUES (?, ?)");
    $crear_torneo = $conexion->prepare("INSERT INTO torneos (liga_id, nombre) VALUES (?, 'Apertura 2026 (demo)')");
    $crear_partido = $conexion->prepare(
        "INSERT INTO partidos (torneo_id, local, visitante, fecha, hora, goles_local, goles_visitante, estado, contado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)"
    );

    foreach ($por_deporte as $deporte => $clubes) {
        $crear_liga->execute(["Liga ARENIX " . ($deportes[$deporte] ?? $deporte) . " (demo)", $deporte]);
        $liga_id = (int) $conexion->lastInsertId();
        foreach ($clubes as $club) {
            $asignar->execute([$club, $liga_id]);
        }

        // los partidos jugados de ejemplo quedan contados asi no vencen avisos reales
        if (count($clubes) < 2) {
            continue;
        }
        $crear_torneo->execute([$liga_id]);
        $torneo_id = (int) $conexion->lastInsertId();
        // marcadores que tengan sentido para cada deporte
        $resultados = [
            "basquetbol" => [[78, 71], [64, 69], [85, 80], [70, 74]],
            "voley"      => [[3, 1], [2, 3], [3, 0], [1, 3]],
        ][$deporte] ?? [[2, 1], [0, 0], [3, 2], [1, 3]];
        for ($i = 0; $i + 1 < count($clubes) && $i < 8; $i += 2) {
            $jugado = intdiv($i, 2) % 2 === 0;
            [$gl, $gv] = $resultados[intdiv($i, 2) % 4];
            $crear_partido->execute([
                $torneo_id, $clubes[$i], $clubes[$i + 1],
                date("Y-m-d", strtotime($jugado ? "-7 days" : "+7 days")), "19:00:00",
                $jugado ? $gl : null, $jugado ? $gv : null, $jugado ? "finalizado" : "programado",
            ]);
        }
    }
}


// ---- admins de cada liga (clientes) ----

function ligas_administradas($usuario_id) {
    global $conexion;

    $consulta = $conexion->prepare("SELECT liga_id FROM liga_administradores WHERE usuario_id = ?");
    $consulta->execute([$usuario_id]);
    return array_map("intval", $consulta->fetchAll(PDO::FETCH_COLUMN));
}

// el cliente solo ve las que administra
function puede_administrar_liga($liga_id) {
    if (usuario_puede("gestionar_ligas")) {
        return true;
    }
    $usuario = usuario_actual();
    return $usuario && $usuario["rol"] === "cliente" && in_array((int) $liga_id, ligas_administradas($usuario["id"]), true);
}

function administradores_de_liga($liga_id) {
    global $conexion;

    $consulta = $conexion->prepare(
        "SELECT u.id, u.nombre, u.correo FROM liga_administradores la JOIN usuarios u ON u.id = la.usuario_id
         WHERE la.liga_id = ? AND u.rol = 'cliente' ORDER BY u.nombre"
    );
    $consulta->execute([$liga_id]);
    return array_map(fn($f) => ["id" => (int) $f["id"], "nombre" => $f["nombre"], "correo" => ocultar_correo($f["correo"])], $consulta->fetchAll(PDO::FETCH_ASSOC));
}

function agregar_admin_liga($liga_id, $correo, $asignado_por) {
    global $conexion;

    $liga = buscar_liga($liga_id);
    if (!$liga) {
        return "No se encontró la liga.";
    }
    $correo = is_string($correo) ? trim(strtolower($correo)) : "";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return "Escribe un correo válido.";
    }
    $consulta = $conexion->prepare("SELECT id, rol FROM usuarios WHERE correo = ?");
    $consulta->execute([$correo]);
    $cuenta = $consulta->fetch(PDO::FETCH_ASSOC);
    if (!$cuenta) {
        return "No hay ninguna cuenta con ese correo.";
    }
    if ($cuenta["rol"] !== "cliente") {
        return "Solo una cuenta con rol Cliente puede administrar una liga. Asígnale ese rol desde la página de permisos.";
    }
    if ($liga["uso"]["administradores"] >= $liga["limites"]["administradores"]) {
        return "El " . $liga["nombre_plan"] . " permite hasta " . $liga["limites"]["administradores"] . " administrador(es). Cambia a un plan mayor para sumar más.";
    }

    $insertar = $conexion->prepare("INSERT IGNORE INTO liga_administradores (liga_id, usuario_id, asignado_por) VALUES (?, ?, ?)");
    $insertar->execute([$liga["id"], (int) $cuenta["id"], $asignado_por]);
    return $insertar->rowCount() > 0 ? "ok" : "Esa cuenta ya administra la liga.";
}

function quitar_admin_liga($liga_id, $usuario_id) {
    global $conexion;

    $borrar = $conexion->prepare("DELETE FROM liga_administradores WHERE liga_id = ? AND usuario_id = ?");
    $borrar->execute([(int) $liga_id, (int) $usuario_id]);
    return $borrar->rowCount() > 0 ? "ok" : "Esa cuenta no administraba la liga.";
}


// ---- torneos, calendario, resultados y tabla ----

function torneos_de_liga($liga_id) {
    global $conexion;

    $consulta = $conexion->prepare("SELECT id, nombre, estado FROM torneos WHERE liga_id = ? ORDER BY estado = 'activo' DESC, id DESC");
    $consulta->execute([$liga_id]);
    return array_map(fn($f) => ["id" => (int) $f["id"], "nombre" => $f["nombre"], "estado" => $f["estado"]], $consulta->fetchAll(PDO::FETCH_ASSOC));
}

function buscar_torneo($liga_id, $torneo_id) {
    foreach (torneos_de_liga($liga_id) as $torneo) {
        if ($torneo["id"] === (int) $torneo_id) {
            return $torneo;
        }
    }
    return null;
}

function crear_torneo($liga_id, $nombre) {
    global $conexion;

    $liga = buscar_liga($liga_id);
    if (!$liga) {
        return "No se encontró la liga.";
    }
    $nombre = is_string($nombre) ? trim($nombre) : "";
    if (!texto_valido($nombre, 3, 60)) {
        return "Escribe el nombre del torneo (3 a 60 caracteres).";
    }
    if ($liga["uso"]["torneos"] >= $liga["limites"]["torneos"]) {
        return "El " . $liga["nombre_plan"] . " permite " . $liga["limites"]["torneos"] . " torneo(s) activo(s) a la vez. Finaliza uno o cambia a un plan mayor.";
    }

    $consulta = $conexion->prepare("INSERT INTO torneos (liga_id, nombre) VALUES (?, ?)");
    $consulta->execute([$liga["id"], $nombre]);
    return ["ok" => (int) $conexion->lastInsertId()];
}

// finalizar libera un lugar de torneo activo; reabrir necesita lugar libre
function cambiar_estado_torneo($liga_id, $torneo_id, $estado) {
    global $conexion;

    $liga = buscar_liga($liga_id);
    $torneo = $liga ? buscar_torneo($liga["id"], $torneo_id) : null;
    if (!$torneo) {
        return "No se encontró el torneo.";
    }
    if (!in_array($estado, ["activo", "finalizado"], true) || $torneo["estado"] === $estado) {
        return "Ese cambio no es válido.";
    }
    if ($estado === "activo" && $liga["uso"]["torneos"] >= $liga["limites"]["torneos"]) {
        return "El " . $liga["nombre_plan"] . " permite " . $liga["limites"]["torneos"] . " torneo(s) activo(s) a la vez.";
    }

    $consulta = $conexion->prepare("UPDATE torneos SET estado = ? WHERE id = ?");
    $consulta->execute([$estado, $torneo["id"]]);
    return "ok";
}

// borra un torneo finalizado con sus partidos y resultados
function eliminar_torneo($liga_id, $torneo_id) {
    global $conexion;

    $torneo = buscar_torneo($liga_id, $torneo_id);
    if (!$torneo) {
        return "No se encontró el torneo.";
    }
    if ($torneo["estado"] !== "finalizado") {
        return "Solo se puede eliminar un torneo finalizado.";
    }

    $transaccion_propia = !$conexion->inTransaction();
    try {
        if ($transaccion_propia) {
            $conexion->beginTransaction();
        }
        $conexion->prepare("DELETE FROM partidos WHERE torneo_id = ?")->execute([$torneo["id"]]);
        $conexion->prepare("DELETE FROM torneos WHERE id = ?")->execute([$torneo["id"]]);
        if ($transaccion_propia) {
            $conexion->commit();
        }
    } catch (PDOException $error) {
        if ($transaccion_propia && $conexion->inTransaction()) {
            $conexion->rollBack();
        }
        return "No se pudo eliminar el torneo. Inténtalo nuevamente.";
    }
    return "ok";
}

// dias que espera un partido sin resultado antes de borrarse solo
const DIAS_PARA_BORRAR_SIN_RESULTADO = 30;

// borra los partidos que pasaron 30 dias de su fecha y nadie les cargo el resultado
function borrar_partidos_vencidos() {
    global $conexion;
    static $hecho = false;

    if ($hecho) {
        return;
    }
    $hecho = true;
    $limite = date("Y-m-d", strtotime("-" . DIAS_PARA_BORRAR_SIN_RESULTADO . " days"));
    $borrar = $conexion->prepare("DELETE FROM partidos WHERE estado <> 'finalizado' AND fecha < ?");
    $borrar->execute([$limite]);
}

function partidos_de_torneo($torneo_id) {
    global $conexion;

    borrar_partidos_vencidos();

    $consulta = $conexion->prepare("SELECT * FROM partidos WHERE torneo_id = ? ORDER BY fecha, hora, id");
    $consulta->execute([$torneo_id]);
    return array_map(function ($f) {
        return [
            "id"              => (int) $f["id"],
            "local"           => $f["local"],
            "visitante"       => $f["visitante"],
            "fecha"           => $f["fecha"],
            "hora"            => $f["hora"] ? substr($f["hora"], 0, 5) : "",
            "lugar"           => $f["lugar"] ?? "",
            "goles_local"     => $f["goles_local"] === null ? null : (int) $f["goles_local"],
            "goles_visitante" => $f["goles_visitante"] === null ? null : (int) $f["goles_visitante"],
            "estado"          => $f["estado"],
            "definicion"      => $f["definicion"] ?? "regular",
        ];
    }, $consulta->fetchAll(PDO::FETCH_ASSOC));
}

function crear_partido($liga_id, $datos, $creado_por) {
    global $conexion;

    $liga = buscar_liga($liga_id);
    if (!$liga) {
        return "No se encontró la liga.";
    }
    $campo = fn($clave) => isset($datos[$clave]) && is_string($datos[$clave]) ? trim($datos[$clave]) : "";
    $torneo = buscar_torneo($liga["id"], $campo("torneo_id"));
    $local = $campo("local");
    $visitante = $campo("visitante");
    $fecha = $campo("fecha");
    $hora = $campo("hora");
    $lugar = $campo("lugar");

    if (!$torneo || $torneo["estado"] !== "activo") {
        return "Elige un torneo activo de la liga.";
    }
    if (!in_array($local, $liga["clubes"], true) || !in_array($visitante, $liga["clubes"], true)) {
        return "Elige dos equipos de la liga.";
    }
    if ($local === $visitante) {
        return "El local y el visitante tienen que ser equipos distintos.";
    }
    $fecha_valida = DateTime::createFromFormat("!Y-m-d", $fecha);
    if (!$fecha_valida || $fecha_valida->format("Y-m-d") !== $fecha) {
        return "Elige una fecha válida.";
    }
    if ($hora !== "" && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hora)) {
        return "La hora tiene que tener el formato HH:MM.";
    }
    if (mb_strlen($lugar, "UTF-8") > 80) {
        return "El lugar puede tener hasta 80 caracteres.";
    }

    $consulta = $conexion->prepare("INSERT INTO partidos (torneo_id, local, visitante, fecha, hora, lugar, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $consulta->execute([$torneo["id"], $local, $visitante, $fecha, $hora === "" ? null : $hora . ":00", $lugar === "" ? null : $lugar, $creado_por]);
    return "ok";
}

// devuelve el partido si es de un torneo de esa liga
function partido_de_liga($liga_id, $partido_id) {
    global $conexion;

    $consulta = $conexion->prepare(
        "SELECT p.*, t.estado AS estado_torneo FROM partidos p JOIN torneos t ON t.id = p.torneo_id WHERE p.id = ? AND t.liga_id = ?"
    );
    $consulta->execute([(int) $partido_id, (int) $liga_id]);
    return $consulta->fetch(PDO::FETCH_ASSOC) ?: null;
}

// guarda o corrige el resultado. la primera vez cuenta como partido jugado (vence avisos)
function cargar_resultado($liga_id, $partido_id, $goles_local, $goles_visitante, $definicion = "regular") {
    global $conexion;

    $partido = partido_de_liga($liga_id, $partido_id);
    if (!$partido) {
        return "No se encontró el partido.";
    }
    $opciones = ["options" => ["min_range" => 0, "max_range" => 999]];
    $gl = filter_var($goles_local, FILTER_VALIDATE_INT, $opciones);
    $gv = filter_var($goles_visitante, FILTER_VALIDATE_INT, $opciones);
    if ($gl === false || $gv === false) {
        return "El resultado tiene que ser un número de 0 a 999 para cada equipo.";
    }
    $liga = buscar_liga($liga_id);
    $error = error_marcador($liga["deporte"], $gl, $gv);
    if ($error) {
        return $error;
    }
    // solo hockey sobre hielo y waterpolo distinguen si se gano en alargue o penales
    $definicion = reglas_tabla($liga["deporte"])["definicion"] && $definicion === "extra" ? "extra" : "regular";

    $consulta = $conexion->prepare("UPDATE partidos SET goles_local = ?, goles_visitante = ?, definicion = ?, estado = 'finalizado', contado = 1 WHERE id = ?");
    $consulta->execute([$gl, $gv, $definicion, $partido["id"]]);

    $eliminados = 0;
    if (!(int) $partido["contado"]) {
        foreach ([$partido["local"], $partido["visitante"]] as $club) {
            $resultado = registrar_partido_jugado($club);
            $eliminados += is_int($resultado) ? $resultado : 0;
        }
    }
    return ["ok" => $eliminados];
}

// solo se borran partidos sin resultado
function borrar_partido($liga_id, $partido_id) {
    global $conexion;

    $partido = partido_de_liga($liga_id, $partido_id);
    if (!$partido) {
        return "No se encontró el partido.";
    }
    if ($partido["estado"] === "finalizado") {
        return "Un partido con resultado no se puede borrar (puedes corregir el resultado).";
    }
    $borrar = $conexion->prepare("DELETE FROM partidos WHERE id = ?");
    $borrar->execute([$partido["id"]]);
    return "ok";
}

// ---- tabla de posiciones de cada deporte ----

// cada deporte usa las reglas de su federacion:
// futbol, futbol 7, futsal y hockey (fifa / fih): 3 ganar, 1 empatar
// handball (ihf): 2 ganar, 1 empatar
// basquet (fiba): 2 ganar, 1 perder, sin empates. 3x3: se ordena por ganados y promedio de puntos
// voley (fivb): sets a 3. 3-0 o 3-1 da 3, 3-2 da 2 y el que pierde 2-3 se lleva 1
// voley playa (fivb): sets a 2. 2 ganar, 1 perder
// rugby (world rugby): 4 ganar, 2 empatar, +1 al que pierde por 7 o menos
// hockey sobre hielo (iihf) y waterpolo: 3 ganar en tiempo regular, 2 ganar en alargue o penales,
//   1 perder en alargue o penales. no hay empates
// beisbol y softbol: porcentaje de ganados y juegos detras del lider
// futbol americano (nfl): porcentaje de ganados, el empate cuenta medio
function reglas_tabla($deporte) {
    $familias = [
        "futbol" => "futbol", "futbol7" => "futbol", "futsal" => "futbol", "hockey" => "futbol",
        "handball" => "handball",
        "basquetbol" => "basquet", "basquet3x3" => "basquet3x3",
        "voley" => "voley", "voleyplaya" => "voleyplaya",
        "rugby" => "rugby", "rugby7" => "rugby",
        "hockeyhielo" => "definido", "waterpolo" => "definido",
        "beisbol" => "beisbol", "softbol" => "beisbol",
        "americano" => "americano",
    ];
    $familia = $familias[$deporte] ?? "futbol";

    $tanteo = fn($nombre, $sigla) => [
        "af" => [$sigla . "F", ucfirst($nombre) . " a favor"],
        "ec" => [$sigla . "C", ucfirst($nombre) . " en contra"],
        "dif" => ["DIF", "Diferencia de " . $nombre],
    ];
    $basicas = ["pj" => ["PJ", "Partidos jugados"], "g" => ["G", "Ganados"], "e" => ["E", "Empatados"], "p" => ["P", "Perdidos"]];
    $sin_empate = ["pj" => $basicas["pj"], "g" => $basicas["g"], "p" => $basicas["p"]];
    $pts = ["pts" => ["PTS", "Puntos"]];

    $reglas = [
        "futbol" => [
            "tanteador" => "Goles",
            "columnas" => $basicas + ["af" => ["GF", "Goles a favor"], "ec" => ["GC", "Goles en contra"], "dif" => ["DG", "Diferencia de gol"]] + $pts,
            "orden" => ["pts", "dif", "af"],
            "ayuda" => "3 puntos por partido ganado y 1 por empatado. Si hay empate en puntos, desempata la diferencia de gol y después los goles a favor.",
        ],
        "handball" => [
            "tanteador" => "Goles",
            "columnas" => $basicas + ["af" => ["GF", "Goles a favor"], "ec" => ["GC", "Goles en contra"], "dif" => ["DG", "Diferencia de gol"]] + $pts,
            "orden" => ["pts", "dif", "af"],
            "ayuda" => "Reglas IHF: 2 puntos por partido ganado y 1 por empatado. Si hay empate en puntos, desempata la diferencia de gol y después los goles a favor.",
        ],
        "basquet" => [
            "tanteador" => "Puntos",
            "empate" => false,
            "columnas" => $sin_empate + $tanteo("puntos", "P") + ["pts" => ["PTS", "Puntos de clasificación"]],
            "orden" => ["pts", "dif", "af"],
            "ayuda" => "Reglas FIBA: 2 puntos por partido ganado y 1 por perdido (no hay empates). Si hay empate en puntos, desempata la diferencia de puntos y después los puntos a favor.",
        ],
        "basquet3x3" => [
            "tanteador" => "Puntos",
            "empate" => false,
            "columnas" => $sin_empate + $tanteo("puntos", "P") + ["prom" => ["PROM", "Promedio de puntos por partido"]],
            "orden" => ["g", "prom"],
            "ayuda" => "Reglas FIBA 3x3: no hay empates. La tabla se ordena por partidos ganados y después por promedio de puntos anotados.",
        ],
        "voley" => [
            "tanteador" => "Sets",
            "empate" => false,
            "sets" => 3,
            "columnas" => $sin_empate + ["af" => ["SG", "Sets ganados"], "ec" => ["SP", "Sets perdidos"], "cociente" => ["COC", "Cociente de sets (ganados / perdidos)"]] + $pts,
            "orden" => ["g", "pts", "cociente"],
            "ayuda" => "Reglas FIVB: ganar 3-0 o 3-1 da 3 puntos; ganar 3-2 da 2 y el que pierde 2-3 se lleva 1. La tabla se ordena por partidos ganados, después por puntos y después por cociente de sets.",
        ],
        "voleyplaya" => [
            "tanteador" => "Sets",
            "empate" => false,
            "sets" => 2,
            "columnas" => $sin_empate + ["af" => ["SG", "Sets ganados"], "ec" => ["SP", "Sets perdidos"], "cociente" => ["COC", "Cociente de sets (ganados / perdidos)"]] + $pts,
            "orden" => ["pts", "cociente"],
            "ayuda" => "Reglas FIVB de vóley playa: se juega a 2 sets ganados. 2 puntos por partido ganado y 1 por perdido. Si hay empate en puntos, desempata el cociente de sets.",
        ],
        "rugby" => [
            "tanteador" => "Puntos",
            "columnas" => $basicas + $tanteo("puntos", "P") + ["bonus" => ["BD", "Bonus defensivo (perder por 7 o menos)"]] + $pts,
            "orden" => ["pts", "dif", "af"],
            "ayuda" => "Reglas World Rugby: 4 puntos por partido ganado, 2 por empatado y 1 de bonus al que pierde por 7 o menos. Si hay empate en puntos, desempata la diferencia de puntos y después los puntos a favor.",
        ],
        "definido" => [
            "tanteador" => "Goles",
            "empate" => false,
            "definicion" => true,
            "columnas" => ["pj" => ["PJ", "Partidos jugados"], "g" => ["G", "Ganados en tiempo regular"], "ge" => ["GA", "Ganados en alargue o penales"],
                "pe" => ["PA", "Perdidos en alargue o penales"], "p" => ["P", "Perdidos en tiempo regular"],
                "af" => ["GF", "Goles a favor"], "ec" => ["GC", "Goles en contra"], "dif" => ["DG", "Diferencia de gol"]] + $pts,
            "orden" => ["pts", "dif", "af"],
            "ayuda" => "No hay empates. Ganar en tiempo regular da 3 puntos, ganar en alargue o penales da 2 y perder en alargue o penales da 1. Si hay empate en puntos, desempata la diferencia de gol.",
        ],
        "beisbol" => [
            "tanteador" => "Carreras",
            "empate" => false,
            "columnas" => $sin_empate + ["af" => ["CA", "Carreras anotadas"], "ec" => ["CR", "Carreras recibidas"], "pct" => ["PCT", "Porcentaje de partidos ganados"], "jd" => ["JD", "Juegos detrás del líder"]],
            "orden" => ["pct", "g"],
            "ayuda" => "No hay empates (se juegan entradas extra). La tabla se ordena por porcentaje de partidos ganados. JD son los juegos que le faltan para alcanzar al líder.",
        ],
        "americano" => [
            "tanteador" => "Puntos",
            "columnas" => $basicas + $tanteo("puntos", "P") + ["pct" => ["PCT", "Porcentaje de ganados (el empate cuenta medio)"]],
            "orden" => ["pct", "dif"],
            "ayuda" => "Como en la NFL: la tabla se ordena por porcentaje de partidos ganados, donde el empate cuenta como medio partido ganado. Si hay empate, desempata la diferencia de puntos.",
        ],
    ];

    $regla = $reglas[$familia] + ["familia" => $familia, "empate" => true, "sets" => null, "definicion" => false];
    $regla["titulos"] = array_map(fn($columna) => $columna[1], $regla["columnas"]);
    $regla["columnas"] = array_map(fn($columna) => $columna[0], $regla["columnas"]);
    return $regla;
}

// revisa que el marcador tenga sentido para el deporte. devuelve el error o null
function error_marcador($deporte, $local, $visitante) {
    $regla = reglas_tabla($deporte);

    if ($regla["sets"]) {
        $sets = $regla["sets"];
        if (max($local, $visitante) !== $sets || min($local, $visitante) >= $sets) {
            return "El resultado son los sets: el ganador tiene $sets y el otro menos (por ejemplo $sets-1).";
        }
        return null;
    }
    if (!$regla["empate"] && $local === $visitante) {
        $motivos = [
            "basquet" => "En básquet no hay empates: se juegan suplementarios hasta que gane uno.",
            "basquet3x3" => "En básquet 3x3 no hay empates: se juega un alargue hasta que gane uno.",
            "definido" => "No hay empates: se define en alargue o penales. Cargá el resultado final con el gol del ganador y marcá que se definió en alargue o penales.",
            "beisbol" => "No hay empates: se juegan entradas extra hasta que gane uno.",
        ];
        return $motivos[$regla["familia"]] ?? "En este deporte no hay empates.";
    }
    return null;
}

// puntos que se lleva un equipo en un partido
function puntos_partido($regla, $a_favor, $en_contra, $en_alargue = false) {
    $gano = $a_favor > $en_contra;
    $empato = $a_favor === $en_contra;

    switch ($regla["familia"]) {
        case "handball":
            return $gano ? 2 : ($empato ? 1 : 0);
        case "basquet":
        case "voleyplaya":
            return $gano ? 2 : 1;
        case "voley":
            // 3-2: 2 al ganador y 1 al perdedor
            $cerrado = abs($a_favor - $en_contra) === 1;
            return $gano ? ($cerrado ? 2 : 3) : ($cerrado ? 1 : 0);
        case "rugby":
            if ($gano) {
                return 4;
            }
            return $empato ? 2 : ($en_contra - $a_favor <= 7 ? 1 : 0);
        case "definido":
            return $gano ? ($en_alargue ? 2 : 3) : ($en_alargue ? 1 : 0);
        case "basquet3x3":
        case "beisbol":
        case "americano":
            // estos se ordenan por ganados o porcentaje, no por puntos
            return 0;
        default:
            return $gano ? 3 : ($empato ? 1 : 0);
    }
}

function tabla_posiciones($partidos, $equipos_liga, $deporte = "futbol") {
    $regla = reglas_tabla($deporte);
    $tabla = [];
    foreach ($equipos_liga as $equipo) {
        $tabla[$equipo["id"]] = ["id" => $equipo["id"], "nombre" => $equipo["nombre"], "logo" => $equipo["logo"],
            "pj" => 0, "g" => 0, "e" => 0, "p" => 0, "ge" => 0, "pe" => 0, "af" => 0, "ec" => 0, "dif" => 0,
            "bonus" => 0, "cociente" => 0, "prom" => 0, "pct" => 0, "jd" => 0, "pts" => 0];
    }

    foreach ($partidos as $partido) {
        if ($partido["estado"] !== "finalizado" || !isset($tabla[$partido["local"]], $tabla[$partido["visitante"]])) {
            continue;
        }
        $en_alargue = $regla["definicion"] && ($partido["definicion"] ?? "regular") === "extra";

        foreach ([[$partido["local"], $partido["goles_local"], $partido["goles_visitante"]],
                  [$partido["visitante"], $partido["goles_visitante"], $partido["goles_local"]]] as [$club, $a_favor, $en_contra]) {
            $fila = &$tabla[$club];
            $fila["pj"]++;
            $fila["af"] += $a_favor;
            $fila["ec"] += $en_contra;
            if ($a_favor > $en_contra) {
                $fila[$en_alargue ? "ge" : "g"]++;
            } elseif ($a_favor === $en_contra) {
                $fila["e"]++;
            } else {
                $fila[$en_alargue ? "pe" : "p"]++;
                if ($regla["familia"] === "rugby" && $en_contra - $a_favor <= 7) {
                    $fila["bonus"]++;
                }
            }
            $fila["pts"] += puntos_partido($regla, $a_favor, $en_contra, $en_alargue);
            unset($fila);
        }
    }

    foreach ($tabla as &$fila) {
        $fila["dif"] = $fila["af"] - $fila["ec"];
        // sin sets perdidos el cociente seria infinito, se usa los sets ganados
        $fila["cociente"] = $fila["ec"] > 0 ? round($fila["af"] / $fila["ec"], 3) : (float) $fila["af"];
        $fila["prom"] = $fila["pj"] > 0 ? round($fila["af"] / $fila["pj"], 1) : 0.0;
        $ganados = $fila["g"] + $fila["ge"] + ($regla["familia"] === "americano" ? $fila["e"] / 2 : 0);
        $fila["pct"] = $fila["pj"] > 0 ? round($ganados / $fila["pj"], 3) : 0.0;
    }
    unset($fila);

    $orden = $regla["orden"];
    usort($tabla, function ($a, $b) use ($orden) {
        foreach ($orden as $clave) {
            if ($a[$clave] != $b[$clave]) {
                return $b[$clave] <=> $a[$clave];
            }
        }
        return strcmp($a["nombre"], $b["nombre"]);
    });

    // juegos detras del lider (beisbol)
    if (isset($tabla[0])) {
        $lider = $tabla[0];
        foreach ($tabla as &$fila) {
            $fila["jd"] = (($lider["g"] - $fila["g"]) + ($fila["p"] - $lider["p"])) / 2;
        }
        unset($fila);
    }
    return $tabla;
}

// como se muestra un valor de la tabla
function valor_tabla($fila, $clave) {
    switch ($clave) {
        case "dif":
            return $fila["dif"] > 0 ? "+" . $fila["dif"] : (string) $fila["dif"];
        case "cociente":
            return $fila["pj"] > 0 ? number_format($fila["cociente"], 2, ",", "") : "—";
        case "prom":
            return $fila["pj"] > 0 ? number_format($fila["prom"], 1, ",", "") : "—";
        case "pct":
            // como en el beisbol: ,667 (1,000 si gano todo)
            return $fila["pj"] > 0 ? ltrim(number_format($fila["pct"], 3, ",", ""), "0") : "—";
        case "jd":
            return $fila["jd"] == 0 ? "—" : str_replace(".", ",", (string) $fila["jd"]);
        default:
            return (string) $fila[$clave];
    }
}

// ligas que administra un usuario (para el perfil)
function ligas_del_usuario($usuario) {
    if (!$usuario || $usuario["rol"] !== "cliente") {
        return [];
    }
    $ids = ligas_administradas($usuario["id"]);
    return array_values(array_filter(listar_ligas(), fn($liga) => in_array($liga["id"], $ids, true)));
}
