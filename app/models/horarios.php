<?php
// horarios: partidos de las ligas que todavia no tienen resultado

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/ligas.php";

// partidos sin resultado agrupados por deporte, los mas cercanos arriba
function listar_horarios() {
    global $conexion;

    $equipos = array_column(listar_equipos(), null, "id");
    $deportes = deportes_clubes();
    $meses = ["ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC"];
    $hoy = date("Y-m-d");

    borrar_partidos_vencidos();
    $filas = $conexion->query(
        "SELECT p.id, p.local, p.visitante, p.fecha, p.hora, p.lugar,
                t.nombre AS torneo, l.nombre AS liga, l.deporte
         FROM partidos p
         JOIN torneos t ON t.id = p.torneo_id
         JOIN ligas l ON l.id = t.liga_id
         WHERE p.estado <> 'finalizado'
         ORDER BY p.fecha, p.hora IS NULL, p.hora, p.id"
    )->fetchAll(PDO::FETCH_ASSOC);

    // si el equipo ya no existe se muestra la clave y el logo de arenix
    $equipo = fn($clave) => [
        "nombre" => $equipos[$clave]["nombre"] ?? $clave,
        "logo" => $equipos[$clave]["logo"] ?? "/public/assets/images/logo a.png",
    ];

    $por_deporte = [];
    foreach ($filas as $fila) {
        $clave = $fila["deporte"];
        if (!isset($por_deporte[$clave])) {
            $por_deporte[$clave] = ["id" => $clave, "nombre" => $deportes[$clave] ?? $clave, "partidos" => []];
        }
        $marca = strtotime($fila["fecha"]);
        $por_deporte[$clave]["partidos"][] = [
            "id" => (int) $fila["id"],
            "fecha" => $fila["fecha"],
            "dia" => date("d", $marca),
            "mes" => $meses[(int) date("n", $marca) - 1],
            "hora" => $fila["hora"] ? substr($fila["hora"], 0, 5) : "",
            "lugar" => $fila["lugar"] ?? "",
            "liga" => $fila["liga"],
            "torneo" => $fila["torneo"],
            "deporte" => $por_deporte[$clave]["nombre"],
            "local" => $equipo($fila["local"]),
            "visitante" => $equipo($fila["visitante"]),
            // ya paso la fecha y nadie cargo el resultado
            "pendiente" => $fila["fecha"] < $hoy,
            // dia en que se borra solo si sigue sin resultado
            "se_borra" => date("d/m", strtotime($fila["fecha"] . " +" . (DIAS_PARA_BORRAR_SIN_RESULTADO + 1) . " days")),
        ];
    }

    // en el mismo orden que la lista de deportes
    $orden = array_flip(array_keys($deportes));
    uasort($por_deporte, fn($a, $b) => ($orden[$a["id"]] ?? 99) <=> ($orden[$b["id"]] ?? 99));
    return array_values($por_deporte);
}

// borra un partido sin resultado (los que tienen resultado se borran desde resultados)
function eliminar_horario($partido_id) {
    global $conexion;

    $consulta = $conexion->prepare("SELECT t.liga_id FROM partidos p JOIN torneos t ON t.id = p.torneo_id WHERE p.id = ?");
    $consulta->execute([filter_var($partido_id, FILTER_VALIDATE_INT) ?: 0]);
    $liga_id = $consulta->fetchColumn();
    if (!$liga_id) {
        return "No se encontró ese partido.";
    }
    return borrar_partido((int) $liga_id, $partido_id);
}

// agrega un partido al calendario de la liga del torneo elegido
function agregar_horario($datos, $usuario_id) {
    global $conexion;

    $torneo_id = filter_var($datos["torneo_id"] ?? "", FILTER_VALIDATE_INT);
    $consulta = $conexion->prepare("SELECT liga_id FROM torneos WHERE id = ? AND estado = 'activo'");
    $consulta->execute([$torneo_id ?: 0]);
    $liga_id = $consulta->fetchColumn();
    if (!$liga_id) {
        return "Elegí un torneo activo de una liga.";
    }

    // para cargar un partido que ya se jugo esta el formulario de resultados
    $fecha = is_string($datos["fecha"] ?? null) ? trim($datos["fecha"]) : "";
    if ($fecha !== "" && $fecha < date("Y-m-d")) {
        return "La fecha ya pasó. Si el partido ya se jugó, cargalo desde Resultados.";
    }

    $resultado = crear_partido((int) $liga_id, $datos, $usuario_id);
    if ($resultado !== "ok") {
        return $resultado;
    }
    return ["ok" => true, "liga_id" => (int) $liga_id, "torneo_id" => (int) $torneo_id];
}
