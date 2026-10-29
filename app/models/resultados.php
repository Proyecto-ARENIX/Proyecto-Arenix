<?php
// resultados de los partidos jugados en las ligas

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/ligas.php";

// partidos finalizados agrupados por deporte, los mas nuevos arriba
function listar_resultados() {
    global $conexion;

    $equipos = array_column(listar_equipos(), null, "id");
    $deportes = deportes_clubes();
    $filas = $conexion->query(
        "SELECT p.id, p.local, p.visitante, p.fecha, p.goles_local, p.goles_visitante, p.definicion,
                t.nombre AS torneo, l.nombre AS liga, l.deporte
         FROM partidos p
         JOIN torneos t ON t.id = p.torneo_id
         JOIN ligas l ON l.id = t.liga_id
         WHERE p.estado = 'finalizado'
         ORDER BY p.fecha DESC, p.id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    // si el equipo ya no existe se muestra la clave y el logo de arenix
    $equipo = fn($clave) => [
        "nombre" => $equipos[$clave]["nombre"] ?? $clave,
        "logo" => $equipos[$clave]["logo"] ?? "/public/assets/images/logo a.png",
    ];

    $por_deporte = [];
    foreach ($deportes as $clave => $nombre) {
        $por_deporte[$clave] = ["id" => $clave, "nombre" => $nombre, "partidos" => []];
    }

    foreach ($filas as $fila) {
        if (!isset($por_deporte[$fila["deporte"]])) {
            $por_deporte[$fila["deporte"]] = ["id" => $fila["deporte"], "nombre" => $fila["deporte"], "partidos" => []];
        }
        $por_deporte[$fila["deporte"]]["partidos"][] = [
            "id" => (int) $fila["id"],
            "fecha" => date("d/m/Y", strtotime($fila["fecha"])),
            "liga" => $fila["liga"],
            "torneo" => $fila["torneo"],
            "deporte" => $por_deporte[$fila["deporte"]]["nombre"],
            "local" => $equipo($fila["local"]),
            "visitante" => $equipo($fila["visitante"]),
            "marcador" => $fila["goles_local"] . " - " . $fila["goles_visitante"] . ($fila["definicion"] === "extra" ? " (alargue/pen.)" : ""),
        ];
    }

    // solo los deportes que tienen algun resultado
    return array_values(array_filter($por_deporte, fn($deporte) => count($deporte["partidos"]) > 0));
}

// para el formulario: ligas con sus torneos activos y sus equipos
function ligas_para_resultados() {
    $equipos = array_column(listar_equipos(), null, "id");
    $ligas = [];

    foreach (listar_ligas() as $liga) {
        $torneos = array_values(array_filter(torneos_de_liga($liga["id"]), fn($torneo) => $torneo["estado"] === "activo"));
        $clubes = array_values(array_filter(array_map(fn($clave) => $equipos[$clave] ?? null, $liga["clubes"])));
        if (count($torneos) === 0 || count($clubes) < 2) {
            continue;
        }
        $ligas[] = [
            "id" => $liga["id"],
            "nombre" => $liga["nombre"],
            "deporte" => $liga["nombre_deporte"],
            "tanteador" => reglas_tabla($liga["deporte"])["tanteador"],
            "sets" => reglas_tabla($liga["deporte"])["sets"],
            "definicion" => reglas_tabla($liga["deporte"])["definicion"],
            "torneos" => $torneos,
            "equipos" => array_map(fn($club) => ["id" => $club["id"], "nombre" => $club["nombre"]], $clubes),
        ];
    }
    return $ligas;
}

// carga un partido ya jugado con su resultado. si falta algun dato no se guarda
function agregar_resultado($datos, $usuario_id) {
    global $conexion;

    $campo = fn($clave) => isset($datos[$clave]) && is_string($datos[$clave]) ? trim($datos[$clave]) : "";

    $obligatorios = [
        "torneo_id" => "liga y torneo",
        "local" => "equipo local",
        "goles_local" => "goles del local",
        "visitante" => "equipo visitante",
        "goles_visitante" => "goles del visitante",
        "fecha" => "fecha",
        "hora" => "hora",
        "lugar" => "lugar",
    ];
    $faltan = array_values(array_filter($obligatorios, fn($clave) => $campo($clave) === "", ARRAY_FILTER_USE_KEY));
    if (count($faltan) > 0) {
        return "No se guardó: falta completar " . implode(", ", $faltan) . ".";
    }

    $torneo_id = filter_var($campo("torneo_id"), FILTER_VALIDATE_INT);
    $local = $campo("local");
    $visitante = $campo("visitante");
    $fecha = $campo("fecha");
    $hora = $campo("hora");
    $lugar = $campo("lugar");
    $opciones = ["options" => ["min_range" => 0, "max_range" => 999]];
    $goles_local = filter_var($campo("goles_local"), FILTER_VALIDATE_INT, $opciones);
    $goles_visitante = filter_var($campo("goles_visitante"), FILTER_VALIDATE_INT, $opciones);

    $consulta = $conexion->prepare("SELECT liga_id FROM torneos WHERE id = ? AND estado = 'activo'");
    $consulta->execute([$torneo_id ?: 0]);
    $liga = buscar_liga($consulta->fetchColumn());
    if (!$liga) {
        return "Elegí un torneo activo de una liga.";
    }
    if (!in_array($local, $liga["clubes"], true) || !in_array($visitante, $liga["clubes"], true)) {
        return "Los dos equipos tienen que ser de " . $liga["nombre"] . ".";
    }
    if ($local === $visitante) {
        return "El local y el visitante tienen que ser equipos distintos.";
    }
    $fecha_valida = DateTime::createFromFormat("!Y-m-d", $fecha);
    if (!$fecha_valida || $fecha_valida->format("Y-m-d") !== $fecha) {
        return "Elegí una fecha válida.";
    }
    if ($fecha > date("Y-m-d")) {
        return "Un partido jugado no puede tener fecha futura. Para eso agregalo al calendario de la liga.";
    }
    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hora)) {
        return "La hora tiene que tener el formato HH:MM.";
    }
    if (!texto_valido($lugar, 2, 80)) {
        return "El lugar tiene que tener entre 2 y 80 caracteres.";
    }
    if ($goles_local === false || $goles_visitante === false) {
        return "El resultado tiene que ser un número de 0 a 999 para cada equipo.";
    }
    $error = error_marcador($liga["deporte"], $goles_local, $goles_visitante);
    if ($error) {
        return $error;
    }

    // solo hockey sobre hielo y waterpolo distinguen si se gano en alargue o penales
    $definicion = reglas_tabla($liga["deporte"])["definicion"] && $campo("definicion") === "extra" ? "extra" : "regular";

    $insertar = $conexion->prepare(
        "INSERT INTO partidos (torneo_id, local, visitante, fecha, hora, lugar, goles_local, goles_visitante, definicion, estado, contado, creado_por)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'finalizado', 1, ?)"
    );
    $insertar->execute([$torneo_id, $local, $visitante, $fecha, $hora . ":00", $lugar, $goles_local, $goles_visitante, $definicion, $usuario_id]);

    // cuenta como partido jugado para los dos (vence avisos)
    registrar_partido_jugado($local);
    registrar_partido_jugado($visitante);

    // para mandar al usuario a la tabla de esa liga
    return ["ok" => true, "liga_id" => $liga["id"], "liga" => $liga["nombre"], "torneo_id" => (int) $torneo_id];
}

function eliminar_resultado($partido_id) {
    global $conexion;

    if (!filter_var($partido_id, FILTER_VALIDATE_INT)) {
        return "El resultado no es válido.";
    }

    $eliminar = $conexion->prepare("DELETE FROM partidos WHERE id = ? AND estado = 'finalizado'");
    $eliminar->execute([$partido_id]);

    return $eliminar->rowCount() > 0 ? "ok" : "No se encontró ese resultado.";
}
