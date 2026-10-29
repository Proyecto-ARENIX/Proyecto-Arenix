<?php
// eventos: torneos, clinicas e inscripciones que cargan admin y organizador

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/deportes.php";
require_once __DIR__ . "/clubes.php";

// los mismos deportes que se pueden usar en las ligas
function deportes_eventos() {
    return deportes_clubes();
}

function estados_evento() {
    return [
        "confirmado" => "Confirmado",
        "abierto" => "Inscripción abierta",
        "limitado" => "Cupos limitados",
        "cerrado" => "Inscripción cerrada",
        "suspendido" => "Suspendido",
    ];
}

// los de ejemplo se guardan una sola vez, si se borran no vuelven
function crear_eventos_de_ejemplo() {
    global $conexion;

    if ($conexion->query("SELECT valor FROM configuracion WHERE clave = 'eventos_demo_creados'")->fetchColumn()) {
        return;
    }
    $conexion->exec("INSERT IGNORE INTO configuracion (clave, valor) VALUES ('eventos_demo_creados', '1')");

    $dentro = fn($dias) => date("Y-m-d", strtotime("+$dias days"));
    $ejemplos = [
        ["Final de la Copa Élite", "futbol", $dentro(7), "18:00", "Final", "Estadio Principal", "Entrada libre", "confirmado"],
        ["Torneo Metropolitano", "basquetbol", $dentro(9), "09:00", "Juvenil · mixto", "Polideportivo Norte", "12 lugares", "abierto"],
        ["Festival de Vóley", "voley", $dentro(12), "10:00", "Sub-18", "Gimnasio Municipal", "6 equipos", "abierto"],
        ["Clínica de handball", "handball", $dentro(14), "14:00", "Nivel inicial", "Gimnasio del Club · Cancha 1", "8 lugares", "limitado"],
        ["Copa Regional", "rugby", $dentro(16), "15:00", "Mayores", "Campo del Parque", "16 equipos", "abierto"],
        ["Encuentro ARENIX de hockey", "hockey", $dentro(19), "09:00", "Mixto", "Cancha Municipal", "8 equipos", "abierto"],
    ];
    $insertar = $conexion->prepare("INSERT INTO eventos (nombre, deporte, fecha, hora, modalidad, sede, cupos, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($ejemplos as $ejemplo) {
        $insertar->execute($ejemplo);
    }
}

// eventos agrupados por deporte, los mas cercanos arriba
function listar_eventos() {
    global $conexion;

    crear_eventos_de_ejemplo();
    $deportes = deportes_eventos();
    $estados = estados_evento();
    $meses = ["ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC"];
    $hoy = date("Y-m-d");

    $filas = $conexion->query("SELECT * FROM eventos ORDER BY fecha, hora IS NULL, hora, id")->fetchAll(PDO::FETCH_ASSOC);

    $por_deporte = [];
    foreach ($filas as $fila) {
        $clave = $fila["deporte"];
        // los cargados con deportes que ya no estan en las ligas no se muestran
        if (!isset($deportes[$clave])) {
            continue;
        }
        if (!isset($por_deporte[$clave])) {
            $por_deporte[$clave] = ["id" => $clave, "nombre" => $deportes[$clave], "eventos" => []];
        }
        $marca = strtotime($fila["fecha"]);
        $paso = $fila["fecha"] < $hoy;
        $por_deporte[$clave]["eventos"][] = [
            "id" => (int) $fila["id"],
            "nombre" => $fila["nombre"],
            "fecha" => $fila["fecha"],
            "dia" => date("d", $marca),
            "mes" => $meses[(int) date("n", $marca) - 1],
            "hora" => $fila["hora"] ? substr($fila["hora"], 0, 5) : "",
            "modalidad" => $fila["modalidad"] ?? "",
            "sede" => $fila["sede"] ?? "",
            "cupos" => $fila["cupos"] ?? "",
            "deporte" => $por_deporte[$clave]["nombre"],
            // si ya paso la fecha se muestra como finalizado
            "estado" => $paso ? "finalizado" : $fila["estado"],
            "nombre_estado" => $paso ? "Finalizado" : ($estados[$fila["estado"]] ?? $fila["estado"]),
            // lo que esta guardado, para llenar el formulario de editar
            "estado_guardado" => $fila["estado"],
            "clave_deporte" => $clave,
        ];
    }

    // en el mismo orden que la lista de deportes
    $orden = array_flip(array_keys($deportes));
    uasort($por_deporte, fn($a, $b) => ($orden[$a["id"]] ?? 99) <=> ($orden[$b["id"]] ?? 99));
    return array_values($por_deporte);
}

// sin id lo crea, con id lo edita
function guardar_evento($datos, $usuario_id) {
    global $conexion;

    $campo = fn($clave) => isset($datos[$clave]) && is_string($datos[$clave]) ? trim($datos[$clave]) : "";
    $id = filter_var($campo("id"), FILTER_VALIDATE_INT);
    $actual = null;
    if ($id) {
        $consulta = $conexion->prepare("SELECT fecha FROM eventos WHERE id = ?");
        $consulta->execute([$id]);
        $actual = $consulta->fetch(PDO::FETCH_ASSOC);
        if (!$actual) {
            return "No se encontró ese evento.";
        }
    }
    $nombre = $campo("nombre");
    $deporte = $campo("deporte");
    $fecha = $campo("fecha");
    $hora = $campo("hora");
    $modalidad = $campo("modalidad");
    $sede = $campo("sede");
    $cupos = $campo("cupos");
    $estado = $campo("estado");

    if (!texto_valido($nombre, 3, 80)) {
        return "El nombre del evento tiene que tener entre 3 y 80 caracteres.";
    }
    if (!isset(deportes_eventos()[$deporte])) {
        return "Elegí un deporte.";
    }
    $fecha_valida = DateTime::createFromFormat("!Y-m-d", $fecha);
    if (!$fecha_valida || $fecha_valida->format("Y-m-d") !== $fecha) {
        return "Elegí una fecha válida.";
    }
    // al editar un evento que ya paso se puede dejar su fecha, pero no pasarlo a otra fecha vieja
    if ($fecha < date("Y-m-d") && (!$actual || $fecha !== $actual["fecha"])) {
        return "La fecha del evento ya pasó.";
    }
    if ($hora !== "" && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hora)) {
        return "La hora tiene que tener el formato HH:MM.";
    }
    foreach ([[$modalidad, "La modalidad", 60], [$sede, "La sede", 80], [$cupos, "Los cupos", 40]] as [$texto, $que, $maximo]) {
        if (mb_strlen($texto, "UTF-8") > $maximo) {
            return "$que puede tener hasta $maximo caracteres.";
        }
    }
    if (!isset(estados_evento()[$estado])) {
        return "Elegí el estado del evento.";
    }

    $valores = [
        $nombre, $deporte, $fecha, $hora === "" ? null : $hora . ":00",
        $modalidad ?: null, $sede ?: null, $cupos ?: null, $estado,
    ];

    if ($actual) {
        $editar = $conexion->prepare(
            "UPDATE eventos SET nombre = ?, deporte = ?, fecha = ?, hora = ?, modalidad = ?, sede = ?, cupos = ?, estado = ? WHERE id = ?"
        );
        $editar->execute([...$valores, $id]);
        return "ok";
    }

    $insertar = $conexion->prepare(
        "INSERT INTO eventos (nombre, deporte, fecha, hora, modalidad, sede, cupos, estado, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insertar->execute([...$valores, $usuario_id]);
    return "ok";
}

function eliminar_evento($id) {
    global $conexion;

    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        return "El evento no es válido.";
    }
    $eliminar = $conexion->prepare("DELETE FROM eventos WHERE id = ?");
    $eliminar->execute([$id]);
    return $eliminar->rowCount() > 0 ? "ok" : "No se encontró ese evento.";
}
