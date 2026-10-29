<?php
// clubes que se agregan desde la pagina (se guardan en la base)

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/equipos.php";
require_once __DIR__ . "/deportes.php";
require_once __DIR__ . "/ligas.php";

// mismo formato que equipos.php. si tiene clave_base es un equipo de ejemplo editado
function listar_clubes_bd() {
    global $conexion;

    $clubes = $conexion->query("SELECT * FROM clubes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $formaciones = formaciones_por_deporte();
    $deportes = deportes_clubes();
    $equipos = [];

    foreach ($clubes as $club) {
        if (!isset($formaciones[$club["deporte"]])) {
            continue;
        }
        // si la formacion ya no existe se usa la primera
        if (!isset($formaciones[$club["deporte"]][$club["formacion"]])) {
            $club["formacion"] = array_key_first($formaciones[$club["deporte"]]);
        }

        $equipos[] = [
            "id"             => $club["clave_base"] ?: "club-" . $club["id"],
            "clave_base"     => $club["clave_base"],
            "nombre"         => $club["nombre"],
            "logo"           => $club["logo"],
            "deporte"        => $club["deporte"],
            "nombre_deporte" => $deportes[$club["deporte"]],
            "torneo"         => $club["torneo"],
            "ciudad"         => $club["ciudad"],
            "entrenador"     => $club["entrenador"],
            "formacion"      => $club["formacion"],
            "lineas"         => $formaciones[$club["deporte"]][$club["formacion"]]["lineas"],
            "plan"           => $club["plan"],
            "habilidades"         => json_decode($club["habilidades"], true) ?: [],
            "jugadores"      => json_decode($club["jugadores"], true) ?: [],
        ];
    }

    return $equipos;
}

// filas de jugadores del formulario (se guarda la clave para encontrar su foto)
function leer_filas_jugadores($filas) {
    if (!is_array($filas)) {
        return [];
    }

    $jugadores = [];
    foreach ($filas as $clave => $fila) {
        if (!is_array($fila)) {
            continue;
        }
        $texto = fn($campo) => isset($fila[$campo]) && is_string($fila[$campo]) ? trim(preg_replace('/\s+/u', " ", $fila[$campo])) : "";
        $numero = $texto("numero");

        $jugadores[$clave] = [
            "clave"    => $clave,
            "numero"   => $numero === "" ? null : (ctype_digit($numero) ? (int) $numero : -1),
            "nombre"   => $texto("nombre"),
            "posicion" => $texto("posicion"),
            "foto"     => $texto("foto"),
        ];
    }

    return $jugadores;
}

function texto_valido($texto, $minimo = 2, $maximo = 60) {
    return is_string($texto) && mb_strlen(trim($texto), "UTF-8") >= $minimo && mb_strlen(trim($texto), "UTF-8") <= $maximo;
}

// sube una imagen, devuelve ["ruta"] o ["error"]
function subir_imagen_club($archivo, $carpeta = "clubes", $que = "El logo") {
    if (!is_array($archivo) || !is_int($archivo["error"] ?? null) || $archivo["error"] !== UPLOAD_ERR_OK) {
        return ["error" => "No se pudo subir la imagen. Inténtalo nuevamente."];
    }
    if ($archivo["size"] > 2 * 1024 * 1024) {
        return ["error" => "$que no puede superar los 2 MB."];
    }

    $informacion_imagen = @getimagesize($archivo["tmp_name"]);
    $tipo_mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo["tmp_name"]);
    $extensiones_permitidas = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
    ];

    if (
        !$informacion_imagen ||
        !isset($extensiones_permitidas[$tipo_mime]) ||
        $informacion_imagen["mime"] !== $tipo_mime ||
        $informacion_imagen[0] > 5000 ||
        $informacion_imagen[1] > 5000
    ) {
        return ["error" => "Usa una imagen JPG, PNG o WEBP válida de hasta 5000 × 5000 px."];
    }

    $directorio = dirname(__DIR__, 2) . "/public/assets/uploads/" . $carpeta;
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
        return ["error" => "No se pudo preparar el almacenamiento de imágenes."];
    }

    $nombre_archivo = bin2hex(random_bytes(16)) . "." . $extensiones_permitidas[$tipo_mime];
    if (!move_uploaded_file($archivo["tmp_name"], $directorio . "/" . $nombre_archivo)) {
        return ["error" => "No se pudo guardar la imagen. Inténtalo nuevamente."];
    }

    return ["ruta" => "/public/assets/uploads/" . $carpeta . "/" . $nombre_archivo];
}

// borra una imagen subida (no las de assets)
function borrar_imagen_subida($ruta) {
    if (is_string($ruta) && preg_match('#^/public/assets/uploads/(clubes|jugadores|ligas|noticias|portada)/[a-f0-9]{32}\.(jpg|png|webp)$#', $ruta)) {
        @unlink(dirname(__DIR__, 2) . $ruta);
    }
}

// guarda el club en la base
function persistir_club($club_id, $actual, array $fila, $usuario_id) {
    global $conexion;

    $valores = [
        $fila["nombre"],
        $fila["logo"],
        $fila["deporte"],
        $fila["torneo"],
        $fila["ciudad"],
        $fila["entrenador"],
        $fila["formacion"],
        $fila["plan"],
        json_encode($fila["habilidades"], JSON_UNESCAPED_UNICODE),
        json_encode($fila["jugadores"], JSON_UNESCAPED_UNICODE),
    ];
    $columnas = "nombre = ?, logo = ?, deporte = ?, torneo = ?, ciudad = ?, entrenador = ?, formacion = ?, plan = ?, habilidades = ?, jugadores = ?, editado_por = ?";

    if ($actual && preg_match('/^club-(\d+)$/', $club_id, $partes)) {
        $consulta = $conexion->prepare("UPDATE clubes SET $columnas WHERE id = ?");
        $consulta->execute([...$valores, $usuario_id, (int) $partes[1]]);
    } elseif ($actual && !empty($actual["clave_base"])) {
        $consulta = $conexion->prepare("UPDATE clubes SET $columnas WHERE clave_base = ?");
        $consulta->execute([...$valores, $usuario_id, $club_id]);
    } else {
        $consulta = $conexion->prepare(
            "INSERT INTO clubes (nombre, logo, deporte, torneo, ciudad, entrenador, formacion, plan, habilidades, jugadores, creado_por, editado_por, clave_base)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $consulta->execute([...$valores, $usuario_id, $actual ? $usuario_id : null, $actual ? $club_id : null]);
        return $actual ? $club_id : "club-" . $conexion->lastInsertId();
    }
    return $club_id;
}

function buscar_equipo($club_id) {
    foreach (listar_equipos() as $equipo) {
        if ($equipo["id"] === $club_id) {
            return $equipo;
        }
    }
    return null;
}


// ---- quien puede editar cada club ----

// el cliente: los clubes asignados y los de sus ligas
function puede_editar_club($club_id) {
    if (usuario_puede("gestionar_clubes")) {
        return true;
    }
    $usuario = usuario_actual();
    if (!$usuario || !usuario_puede("editar_clubes")) {
        return false;
    }
    $liga_id = liga_de_club($club_id);
    return in_array($club_id, clubes_asignados($usuario["id"]), true)
        || ($liga_id !== null && puede_administrar_liga($liga_id));
}

function clubes_asignados($usuario_id) {
    global $conexion;

    $consulta = $conexion->prepare("SELECT club_clave FROM club_editores WHERE usuario_id = ?");
    $consulta->execute([$usuario_id]);
    return $consulta->fetchAll(PDO::FETCH_COLUMN);
}

// clientes que se pueden asignar a un club
function clientes_disponibles() {
    global $conexion;

    return $conexion->query("SELECT id, nombre, correo FROM usuarios WHERE rol = 'cliente' ORDER BY nombre, id")->fetchAll(PDO::FETCH_ASSOC);
}

function editores_por_club() {
    global $conexion;

    $filas = $conexion->query(
        "SELECT ce.club_clave, u.id, u.nombre, u.correo
         FROM club_editores ce JOIN usuarios u ON u.id = ce.usuario_id
         WHERE u.rol = 'cliente'
         ORDER BY u.nombre"
    )->fetchAll(PDO::FETCH_ASSOC);

    $editores = [];
    foreach ($filas as $fila) {
        $editores[$fila["club_clave"]][] = ["id" => (int) $fila["id"], "nombre" => $fila["nombre"], "correo" => $fila["correo"]];
    }
    return $editores;
}

function asignar_editor_club($club_id, $usuario_id, $asignado_por) {
    global $conexion;

    if (!is_string($club_id) || !buscar_equipo($club_id)) {
        return "No se encontró el club.";
    }
    $usuario_id = filter_var($usuario_id, FILTER_VALIDATE_INT);
    $es_cliente = false;
    foreach (clientes_disponibles() as $cliente) {
        if ((int) $cliente["id"] === $usuario_id) {
            $es_cliente = true;
        }
    }
    if (!$es_cliente) {
        return "Elige un cliente válido.";
    }

    $insertar = $conexion->prepare("INSERT IGNORE INTO club_editores (club_clave, usuario_id, asignado_por) VALUES (?, ?, ?)");
    $insertar->execute([$club_id, $usuario_id, $asignado_por]);
    return "ok";
}

function quitar_editor_club($club_id, $usuario_id) {
    global $conexion;

    $usuario_id = filter_var($usuario_id, FILTER_VALIDATE_INT);
    if (!is_string($club_id) || $usuario_id === false) {
        return "Datos no válidos.";
    }

    $borrar = $conexion->prepare("DELETE FROM club_editores WHERE club_clave = ? AND usuario_id = ?");
    $borrar->execute([$club_id, $usuario_id]);
    return "ok";
}


// tapa parte del correo (ju•••@gmail.com)
function ocultar_correo($correo) {
    $partes = explode("@", (string) $correo, 2);
    if (count($partes) !== 2) {
        return "";
    }
    return mb_substr($partes[0], 0, 2, "UTF-8") . "•••@" . $partes[1];
}


// ---- participantes de cada club ----

function participantes_por_club() {
    global $conexion;

    $filas = $conexion->query(
        "SELECT cp.club_clave, u.id, u.nombre, u.correo
         FROM club_participantes cp JOIN usuarios u ON u.id = cp.usuario_id
         WHERE u.rol = 'participante'
         ORDER BY u.nombre"
    )->fetchAll(PDO::FETCH_ASSOC);

    $participantes = [];
    foreach ($filas as $fila) {
        $participantes[$fila["club_clave"]][] = ["id" => (int) $fila["id"], "nombre" => $fila["nombre"], "correo" => ocultar_correo($fila["correo"])];
    }
    return $participantes;
}

// agrega al club la cuenta de ese correo
function agregar_participante_club($club_id, $correo, $agregado_por, $confirmar_rol = false) {
    global $conexion;

    if (!is_string($club_id) || !buscar_equipo($club_id)) {
        return "No se encontró el club.";
    }
    $correo = is_string($correo) ? trim(strtolower($correo)) : "";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return "Escribe un correo válido.";
    }

    $consulta = $conexion->prepare("SELECT id, nombre, rol FROM usuarios WHERE correo = ?");
    $consulta->execute([$correo]);
    $cuenta = $consulta->fetch(PDO::FETCH_ASSOC);
    if (!$cuenta) {
        return "No hay ninguna cuenta con ese correo. Revisa que esté bien escrito y que la persona esté registrada en ARENIX.";
    }
    $usuario_id = (int) $cuenta["id"];

    if ($cuenta["rol"] === "admin" || $cuenta["rol"] === "organizador") {
        return "Esa cuenta es de un organizador o administrador y no se puede unir como participante.";
    }

    if ($cuenta["rol"] === "cliente") {
        // un cliente no puede sumar a otro cliente, solo organizador o admin
        if (!usuario_puede("gestionar_clubes")) {
            return "Esa cuenta es de un cliente y no la puedes agregar como participante. Pídeselo a un organizador o administrador.";
        }
        if (count(clubes_asignados($usuario_id)) > 0) {
            return "Esa cuenta es de un cliente que maneja un club, así que no se le puede cambiar el rol.";
        }
        if (!$confirmar_rol) {
            return ["confirmar" => $cuenta["nombre"]];
        }
    } elseif ($cuenta["rol"] !== "participante" && $cuenta["rol"] !== "publico") {
        return "Esa cuenta no se puede unir como participante.";
    }

    // si ya hay una transaccion abierta se usa esa
    $transaccion_propia = !$conexion->inTransaction();
    try {
        if ($transaccion_propia) {
            $conexion->beginTransaction();
        }

        if ($cuenta["rol"] === "cliente") {
            // se vuelve a chequear por las dudas
            $cambiar = $conexion->prepare(
                "UPDATE usuarios SET rol = 'participante'
                 WHERE id = ? AND rol = 'cliente'
                   AND NOT EXISTS (SELECT 1 FROM club_editores WHERE usuario_id = ?)"
            );
            $cambiar->execute([$usuario_id, $usuario_id]);
            if ($cambiar->rowCount() === 0) {
                if ($transaccion_propia) {
                    $conexion->rollBack();
                }
                return "No se pudo cambiar el rol de esa cuenta. Actualiza la página e inténtalo de nuevo.";
            }
        } elseif ($cuenta["rol"] === "publico") {
            // un visitante que se une a un club pasa a ser participante
            $conexion->prepare("UPDATE usuarios SET rol = 'participante' WHERE id = ? AND rol = 'publico'")->execute([$usuario_id]);
        }

        $insertar = $conexion->prepare("INSERT IGNORE INTO club_participantes (club_clave, usuario_id, agregado_por) VALUES (?, ?, ?)");
        $insertar->execute([$club_id, $usuario_id, $agregado_por]);
        if ($transaccion_propia) {
            $conexion->commit();
        }
    } catch (PDOException $error) {
        if ($transaccion_propia && $conexion->inTransaction()) {
            $conexion->rollBack();
        }
        return "No se pudo agregar al participante. Inténtalo nuevamente.";
    }

    return $insertar->rowCount() > 0 ? "ok" : "Esa persona ya forma parte del club.";
}

function quitar_participante_club($club_id, $usuario_id) {
    global $conexion;

    $usuario_id = filter_var($usuario_id, FILTER_VALIDATE_INT);
    if (!is_string($club_id) || $usuario_id === false) {
        return "Datos no válidos.";
    }

    $borrar = $conexion->prepare("DELETE FROM club_participantes WHERE club_clave = ? AND usuario_id = ?");
    $borrar->execute([$club_id, $usuario_id]);
    return "ok";
}


// ---- avisos (amarillas y rojas) ----

function tipos_sancion() {
    return [
        "amarilla"       => "Amarilla",
        "doble_amarilla" => "Doble amarilla (expulsado)",
        "roja"           => "Roja directa (expulsado)",
    ];
}

// duracion maxima de un aviso, en partidos
const MAX_PARTIDOS_SANCION = 20;

// partidos jugados de cada club
function partidos_jugados_por_club() {
    global $conexion;

    $partidos = [];
    foreach ($conexion->query("SELECT club_clave, jugados FROM club_partidos") as $fila) {
        $partidos[$fila["club_clave"]] = (int) $fila["jugados"];
    }
    return $partidos;
}

// avisos que siguen vigentes, del mas nuevo al mas viejo
function sanciones_por_club() {
    global $conexion;

    $filas = $conexion->query(
        "SELECT s.id, s.club_clave, s.jugador, s.tipo, s.partido, s.fecha, s.detalle,
                s.partidos_duracion AS duracion,
                s.partidos_duracion - (COALESCE(p.jugados, 0) - s.partido_inicio) AS restantes
         FROM club_sanciones s LEFT JOIN club_partidos p ON p.club_clave = s.club_clave
         HAVING restantes > 0
         ORDER BY s.fecha DESC, s.id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $sanciones = [];
    foreach ($filas as $fila) {
        $club = $fila["club_clave"];
        unset($fila["club_clave"]);
        foreach (["id", "duracion", "restantes"] as $numero) {
            $fila[$numero] = (int) $fila[$numero];
        }
        $sanciones[$club][] = $fila;
    }
    return $sanciones;
}

// borra los avisos que ya cumplieron sus partidos; devuelve cuantos borro
function eliminar_sanciones_vencidas($club_id = null) {
    global $conexion;

    $sql = "DELETE s FROM club_sanciones s LEFT JOIN club_partidos p ON p.club_clave = s.club_clave
            WHERE COALESCE(p.jugados, 0) - s.partido_inicio >= s.partidos_duracion";
    if ($club_id === null) {
        return $conexion->exec($sql);
    }
    $borrar = $conexion->prepare($sql . " AND s.club_clave = ?");
    $borrar->execute([$club_id]);
    return $borrar->rowCount();
}

// suma un partido jugado y borra los avisos que vencen
function registrar_partido_jugado($club_id) {
    global $conexion;

    if (!is_string($club_id) || !buscar_equipo($club_id)) {
        return "No se encontró el club.";
    }

    $sumar = $conexion->prepare("INSERT INTO club_partidos (club_clave, jugados) VALUES (?, 1) ON DUPLICATE KEY UPDATE jugados = jugados + 1");
    $sumar->execute([$club_id]);
    return eliminar_sanciones_vencidas($club_id);
}

function agregar_sancion($club_id, $datos, $creado_por) {
    global $conexion;

    $equipo = is_string($club_id) ? buscar_equipo($club_id) : null;
    if (!$equipo) {
        return "No se encontró el club.";
    }

    $campo = fn($clave) => isset($datos[$clave]) && is_string($datos[$clave]) ? trim($datos[$clave]) : "";
    $jugador = $campo("jugador");
    $tipo = $campo("tipo");
    $partido = $campo("partido");
    $fecha = $campo("fecha");
    $detalle = $campo("detalle");

    if (!in_array($jugador, array_column($equipo["jugadores"], "nombre"), true)) {
        return "Elige un jugador del club.";
    }
    if (!isset(tipos_sancion()[$tipo])) {
        return "Elige el tipo de aviso (amarilla o expulsión).";
    }
    if (!texto_valido($partido, 3, 80)) {
        return "Escribe en qué partido fue (por ejemplo: \"vs Halcones · Fecha 7\").";
    }
    $fecha_valida = DateTime::createFromFormat("!Y-m-d", $fecha);
    if (!$fecha_valida || $fecha_valida->format("Y-m-d") !== $fecha || $fecha_valida > new DateTime("+1 day")) {
        return "Elige una fecha válida (no puede ser futura).";
    }
    if (mb_strlen($detalle, "UTF-8") > 120) {
        return "El detalle puede tener hasta 120 caracteres.";
    }
    $duracion = filter_var($datos["duracion"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => MAX_PARTIDOS_SANCION]]);
    if ($duracion === false) {
        return "La duración tiene que ser de 1 a " . MAX_PARTIDOS_SANCION . " partidos.";
    }

    // se cuenta desde los partidos que el club ya jugo
    $inicio = partidos_jugados_por_club()[$club_id] ?? 0;

    $insertar = $conexion->prepare(
        "INSERT INTO club_sanciones (club_clave, jugador, tipo, partido, fecha, detalle, partidos_duracion, partido_inicio, creado_por)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insertar->execute([$club_id, $jugador, $tipo, $partido, $fecha, $detalle === "" ? null : $detalle, $duracion, $inicio, $creado_por]);
    return "ok";
}

function quitar_sancion($club_id, $sancion_id) {
    global $conexion;

    $sancion_id = filter_var($sancion_id, FILTER_VALIDATE_INT);
    if (!is_string($club_id) || $sancion_id === false) {
        return "Datos no válidos.";
    }

    // solo se borra si el aviso es de ese club
    $borrar = $conexion->prepare("DELETE FROM club_sanciones WHERE id = ? AND club_clave = ?");
    $borrar->execute([$sancion_id, $club_id]);
    return $borrar->rowCount() > 0 ? "ok" : "No se encontró el aviso.";
}


// clubes del usuario para el perfil (cliente: los asignados, participante: donde juega)

function clubes_del_usuario($usuario) {
    global $conexion;

    if (!$usuario) {
        return [];
    }

    if ($usuario["rol"] === "cliente") {
        $claves = clubes_asignados($usuario["id"]);
    } elseif ($usuario["rol"] === "participante") {
        $consulta = $conexion->prepare("SELECT club_clave FROM club_participantes WHERE usuario_id = ?");
        $consulta->execute([$usuario["id"]]);
        $claves = $consulta->fetchAll(PDO::FETCH_COLUMN);
    } else {
        return [];
    }

    if (!$claves) {
        return [];
    }

    $sanciones = sanciones_por_club();
    $clubes = [];
    foreach (listar_equipos() as $equipo) {
        if (in_array($equipo["id"], $claves, true)) {
            $avisos = $sanciones[$equipo["id"]] ?? [];
            $clubes[] = [
                "id"          => $equipo["id"],
                "nombre"      => $equipo["nombre"],
                "logo"        => $equipo["logo"],
                "detalle"     => $equipo["nombre_deporte"] . " · " . $equipo["torneo"],
                "editable"    => $usuario["rol"] === "cliente",
                "amarillas"   => count(array_filter($avisos, fn($a) => $a["tipo"] === "amarilla")),
                "expulsiones" => count(array_filter($avisos, fn($a) => $a["tipo"] !== "amarilla")),
            ];
        }
    }
    return $clubes;
}


// saca el archivo de foto_titular[3] como si fuera uno solo
function archivo_de_lista($lista, $clave) {
    if (!is_array($lista) || !isset($lista["error"][$clave]) || $lista["error"][$clave] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    return [
        "name"     => $lista["name"][$clave] ?? "",
        "tmp_name" => $lista["tmp_name"][$clave] ?? "",
        "error"    => $lista["error"][$clave],
        "size"     => $lista["size"][$clave] ?? 0,
    ];
}

// crea un club en una liga (con liga_id) o edita uno (con club_id)
function guardar_club($datos, $archivo_logo, $usuario_id, $archivos_fotos = []) {
    $deportes = deportes_clubes();
    $formaciones = formaciones_por_deporte();

    $campo = fn($clave) => isset($datos[$clave]) && is_string($datos[$clave]) ? trim($datos[$clave]) : "";

    $club_id    = $campo("club_id");
    $nombre     = $campo("nombre");
    $deporte    = $campo("deporte");
    $torneo     = $campo("torneo");
    $ciudad     = $campo("ciudad");
    $entrenador = $campo("entrenador");
    $formacion  = $campo("formacion");

    // club que se esta editando y su liga
    $actual = null;
    if ($club_id !== "") {
        $actual = buscar_equipo($club_id);
        if (!$actual) {
            return "No se encontró el club que querés editar.";
        }
        $liga_id = liga_de_club($club_id);
        $liga = $liga_id !== null ? buscar_liga($liga_id) : null;
    } else {
        $liga = buscar_liga($campo("liga_id"));
        if (!$liga) {
            return "Elige la liga del club.";
        }
    }

    // el plan se guarda en la liga; en el club queda el que ya tenia
    $plan = $actual["plan"] ?? "basico";
    $habilidades_habilitadas = $liga ? plan_incluye($liga["plan"], "estadisticas_avanzadas") : true;
    $fotos_habilitadas = $liga ? plan_incluye($liga["plan"], "perfiles") : true;

    if (!texto_valido($nombre)) {
        return "Ingresa el nombre del club (2 a 60 caracteres).";
    }
    if (!isset($deportes[$deporte])) {
        return "Selecciona un deporte válido.";
    }
    if ($liga && $deporte !== $liga["deporte"]) {
        return "Los equipos de " . $liga["nombre"] . " tienen que ser de " . $liga["nombre_deporte"] . ".";
    }
    if (!$actual && $liga["uso"]["equipos"] >= $liga["limites"]["equipos"]) {
        return "La liga llegó al límite de " . $liga["limites"]["equipos"] . " equipos de su plan (" . $liga["nombre_plan"] . "). "
            . "Se pueden sumar equipos adicionales (USD 10/mes) o cambiar a un plan mayor.";
    }
    if (!texto_valido($torneo)) {
        return "Ingresa el torneo (2 a 60 caracteres).";
    }
    if (!texto_valido($ciudad)) {
        return "Ingresa la ciudad (2 a 60 caracteres).";
    }
    if (!texto_valido($entrenador)) {
        return "Ingresa el nombre del entrenador (2 a 60 caracteres).";
    }
    if (!isset($formaciones[$deporte][$formacion])) {
        return "Selecciona una formación válida para ese deporte.";
    }
    // habilidades solo con enterprise y solo las cargan organizador y admin, si no se dejan las que tenia
    $habilidades = $actual["habilidades"] ?? [];
    if ($habilidades_habilitadas && usuario_puede("editar_habilidades")) {
        $habilidades = [];
        $valores = is_array($datos["habilidades"] ?? null) ? $datos["habilidades"] : [];
        foreach (habilidades_por_deporte()[$deporte] as $i => $nombre_habilidad) {
            $valor = filter_var($valores[$i] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 100]]);
            if ($valor === false) {
                return "La habilidad \"$nombre_habilidad\" debe ser un número de 0 a 100.";
            }
            $habilidades[$nombre_habilidad] = $valor;
        }
    }

    // jugadores: un titular por cada posicion de la formacion, y suplentes con la posicion elegida
    $posiciones = $formaciones[$deporte][$formacion]["posiciones"];
    $nombres_posiciones = posiciones_por_deporte()[$deporte];

    $filas_titulares = leer_filas_jugadores($datos["titulares"] ?? []);
    $titulares = [];
    foreach ($posiciones as $i => $codigo) {
        if (!isset($filas_titulares[$i]) || $filas_titulares[$i]["nombre"] === "") {
            return "Completa el jugador de la posición " . $nombres_posiciones[$codigo] . " ($codigo).";
        }
        $titulares[] = array_merge($filas_titulares[$i], ["posicion" => $codigo, "tipo" => "titular"]);
    }

    $suplentes = [];
    foreach (leer_filas_jugadores($datos["suplentes"] ?? []) as $fila) {
        if ($fila["nombre"] !== "") {
            $suplentes[] = array_merge($fila, ["tipo" => "suplente"]);
        }
    }
    if (count($suplentes) > 15) {
        return "Puedes cargar hasta 15 suplentes.";
    }
    foreach ($suplentes as $jugador) {
        if (!isset($nombres_posiciones[$jugador["posicion"]])) {
            return "Elige una posición válida para el suplente " . $jugador["nombre"] . ".";
        }
    }

    $lista = array_merge($titulares, $suplentes);
    $numeros_usados = [];
    foreach ($lista as $jugador) {
        if (!texto_valido($jugador["nombre"])) {
            return "Revisa el nombre de \"" . $jugador["nombre"] . "\" (2 a 60 caracteres).";
        }
        if ($jugador["numero"] !== null) {
            if ($jugador["numero"] < 1 || $jugador["numero"] > 99) {
                return "El número de " . $jugador["nombre"] . " tiene que ser de 1 a 99.";
            }
            if (in_array($jugador["numero"], $numeros_usados, true)) {
                return "El número " . $jugador["numero"] . " está repetido.";
            }
            $numeros_usados[] = $jugador["numero"];
        }
    }

    // limite de jugadores de la liga: los de los otros equipos + los de este
    if ($liga) {
        $otros = 0;
        foreach (listar_equipos() as $equipo) {
            if ($equipo["id"] !== $club_id && in_array($equipo["id"], $liga["clubes"], true)) {
                $otros += count($equipo["jugadores"]);
            }
        }
        if ($otros + count($lista) > $liga["limites"]["jugadores"]) {
            $libres = max(0, $liga["limites"]["jugadores"] - $otros);
            return "La liga tiene lugar para $libres jugador(es) más en este equipo (límite de " . $liga["limites"]["jugadores"]
                . " del " . $liga["nombre_plan"] . "). Se pueden sumar jugadores adicionales (USD 2/mes) o cambiar a un plan mayor.";
        }
    }

    // nombre repetido (sin contar al club que se esta editando)
    foreach (listar_equipos() as $equipo) {
        if ($equipo["id"] !== $club_id && mb_strtolower($equipo["nombre"], "UTF-8") === mb_strtolower($nombre, "UTF-8")) {
            return "Ya existe un club con ese nombre.";
        }
    }

    // logo: obligatorio al crear; al editar, si no se sube uno se mantiene el actual
    $sin_logo = !is_array($archivo_logo) || ($archivo_logo["error"] ?? null) === UPLOAD_ERR_NO_FILE;
    if ($sin_logo && !$actual) {
        return "Sube el logo del club.";
    }

    // ya esta todo bien, se suben las imagenes
    $subidas = [];
    $deshacer = function ($mensaje) use (&$subidas) {
        foreach ($subidas as $ruta) {
            borrar_imagen_subida($ruta);
        }
        return $mensaje;
    };

    if ($sin_logo) {
        $logo = $actual["logo"];
    } else {
        $subida = subir_imagen_club($archivo_logo);
        if (isset($subida["error"])) {
            return $subida["error"];
        }
        $logo = $subidas[] = $subida["ruta"];
    }

    // foto: la que tenia, una nueva o ninguna. si el plan no tiene fotos se dejan las que habia
    $fotos_actuales = array_values(array_filter(array_column($actual["jugadores"] ?? [], "foto")));
    $fotos_por_nombre = [];
    foreach ($actual["jugadores"] ?? [] as $jugador_actual) {
        if (!empty($jugador_actual["foto"])) {
            $fotos_por_nombre[mb_strtolower($jugador_actual["nombre"], "UTF-8")] = $jugador_actual["foto"];
        }
    }
    $fotos_usadas = [];
    $jugadores = [];
    $siguiente = 1;

    foreach ($lista as $i => $jugador) {
        $numero = $jugador["numero"];
        if ($numero === null) {
            while (in_array($siguiente, $numeros_usados, true)) {
                $siguiente++;
            }
            $numero = $siguiente;
            $numeros_usados[] = $numero;
        }

        $nuevo = [
            "numero"   => $numero,
            "nombre"   => $jugador["nombre"],
            "posicion" => $jugador["posicion"],
            "titular"  => $jugador["tipo"] === "titular",
        ];

        $archivo = $fotos_habilitadas ? archivo_de_lista($archivos_fotos[$jugador["tipo"]] ?? null, $jugador["clave"]) : null;
        $foto_por_nombre = $fotos_por_nombre[mb_strtolower($jugador["nombre"], "UTF-8")] ?? null;
        if (!$fotos_habilitadas) {
            if ($foto_por_nombre && !in_array($foto_por_nombre, $fotos_usadas, true)) {
                $nuevo["foto"] = $fotos_usadas[] = $foto_por_nombre;
            }
        } elseif ($archivo) {
            $subida = subir_imagen_club($archivo, "jugadores", "La foto");
            if (isset($subida["error"])) {
                return $deshacer("Foto de " . $jugador["nombre"] . ": " . $subida["error"]);
            }
            $nuevo["foto"] = $subidas[] = $subida["ruta"];
        } elseif (in_array($jugador["foto"], $fotos_actuales, true) && !in_array($jugador["foto"], $fotos_usadas, true)) {
            $nuevo["foto"] = $jugador["foto"];
            $fotos_usadas[] = $jugador["foto"];
        }

        $jugadores[] = $nuevo;
    }

    $fila = compact("nombre", "logo", "deporte", "torneo", "ciudad", "entrenador", "formacion", "plan", "habilidades", "jugadores");

    try {
        $id_guardado = persistir_club($actual ? $club_id : null, $actual, $fila, $usuario_id);
        if (!$actual) {
            asignar_club_a_liga($id_guardado, $liga["id"]);
        }
    } catch (PDOException $error) {
        return $deshacer("No se pudo guardar el club. Inténtalo nuevamente.");
    }

    // se borran el logo anterior (si se cambio) y las fotos que se quitaron o reemplazaron
    if ($actual && $logo !== $actual["logo"]) {
        borrar_imagen_subida($actual["logo"]);
    }
    foreach ($fotos_actuales as $foto) {
        if (!in_array($foto, $fotos_usadas, true)) {
            borrar_imagen_subida($foto);
        }
    }

    return "ok";
}

