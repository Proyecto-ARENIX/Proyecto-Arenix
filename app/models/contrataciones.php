<?php
// contratacion de planes
// OJO: esta en modo prueba, no cobra nada. el numero de tarjeta y el cvv nunca llegan al servidor,
// solo la marca y los ultimos 4. para cobrar de verdad hay que conectar mercado pago o stripe en procesar_pago()

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/deportes.php";

const PRECIO_EQUIPO_EXTRA = 10;
const PRECIO_JUGADOR_EXTRA = 2;

// planes con su precio
function planes_contratables() {
    $planes = [];
    foreach ((require __DIR__ . "/planes.php")["planes"] as $plan) {
        $planes[$plan["id"]] = $plan;
    }
    return $planes;
}

function total_mensual($plan_id, $equipos_extra, $jugadores_extra) {
    $precio = planes_contratables()[$plan_id]["precio"] ?? 0;
    return $precio + $equipos_extra * PRECIO_EQUIPO_EXTRA + $jugadores_extra * PRECIO_JUGADOR_EXTRA;
}

function marcas_tarjeta() {
    return ["visa" => "Visa", "mastercard" => "Mastercard", "amex" => "American Express", "oca" => "OCA", "otra" => "Tarjeta"];
}

// aca va la pasarela de pago (mercado pago o stripe). por ahora aprueba siempre
function procesar_pago($total, $datos_tarjeta) {
    return ["aprobado" => true, "estado" => "pagado_prueba"];
}

// quien contrata un plan pasa a cliente. admin y organizador no se tocan (no se les baja el rol)
function pasar_a_cliente($usuario_id) {
    global $conexion;

    $cambiar = $conexion->prepare("UPDATE usuarios SET rol = 'cliente' WHERE id = ? AND rol IN ('publico', 'participante')");
    $cambiar->execute([(int) $usuario_id]);
    return $cambiar->rowCount() > 0;
}

function pasar_a_cliente_por_correo($correo) {
    global $conexion;

    $consulta = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $consulta->execute([strtolower(trim((string) $correo))]);
    $id = $consulta->fetchColumn();
    return $id ? pasar_a_cliente($id) : false;
}

// devuelve ["ok" => id, "rol_cambiado" => bool] o el error
function crear_contratacion($datos, $usuario_id) {
    global $conexion;

    $campo = fn($clave) => isset($datos[$clave]) && is_string($datos[$clave]) ? trim($datos[$clave]) : "";
    $plan = $campo("plan");
    $extra = fn($clave) => $campo($clave) === "" ? 0 : filter_var($campo($clave), FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1000]]);
    $equipos_extra = $extra("equipos_extra");
    $jugadores_extra = $extra("jugadores_extra");
    $nombre = $campo("nombre");
    $correo = strtolower($campo("correo"));
    $telefono = $campo("telefono");
    $organizacion = $campo("organizacion");
    $deporte = $campo("deporte");
    $ciudad = $campo("ciudad");
    $marca = $campo("tarjeta_marca");
    $ultimos = $campo("tarjeta_ultimos");
    $vencimiento = $campo("tarjeta_vencimiento");
    $titular = $campo("tarjeta_titular");

    if (!isset(planes_contratables()[$plan])) {
        return "Elige un plan.";
    }
    if ($equipos_extra === false || $jugadores_extra === false) {
        return "Los equipos y jugadores adicionales tienen que ser números de 0 a 1000.";
    }
    if (mb_strlen($nombre, "UTF-8") < 3 || mb_strlen($nombre, "UTF-8") > 80) {
        return "Escribe tu nombre y apellido.";
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 190) {
        return "Escribe un correo válido.";
    }
    if (!preg_match('/^\+?[0-9 ()-]{6,30}$/', $telefono)) {
        return "Escribe un teléfono válido.";
    }
    if (mb_strlen($organizacion, "UTF-8") < 3 || mb_strlen($organizacion, "UTF-8") > 80) {
        return "Escribe el nombre de tu liga, club u organización.";
    }
    if (!isset(deportes_clubes()[$deporte])) {
        return "Elige el deporte.";
    }
    if (mb_strlen($ciudad, "UTF-8") < 2 || mb_strlen($ciudad, "UTF-8") > 60) {
        return "Escribe tu ciudad.";
    }

    // de la tarjeta solo llegan la marca, los ultimos 4 y el vencimiento
    if (!isset(marcas_tarjeta()[$marca]) || !preg_match('/^\d{4}$/', $ultimos)) {
        return "Revisa el número de la tarjeta.";
    }
    if (mb_strlen($titular, "UTF-8") < 3 || mb_strlen($titular, "UTF-8") > 60) {
        return "Escribe el nombre como figura en la tarjeta.";
    }
    if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $vencimiento, $partes)) {
        return "El vencimiento tiene que tener el formato MM/AA.";
    }
    $ultimo_dia = (new DateTime("20" . $partes[2] . "-" . $partes[1] . "-01"))->modify("last day of this month");
    if ($ultimo_dia < new DateTime("today")) {
        return "La tarjeta está vencida.";
    }

    $total = total_mensual($plan, $equipos_extra, $jugadores_extra);
    $pago = procesar_pago($total, ["marca" => $marca, "ultimos" => $ultimos]);
    if (!$pago["aprobado"]) {
        return "El pago no fue aprobado. Prueba con otra tarjeta.";
    }

    $insertar = $conexion->prepare(
        "INSERT INTO contrataciones (plan, equipos_extra, jugadores_extra, total_mensual, nombre, correo, telefono, organizacion, deporte, ciudad,
                                     tarjeta_marca, tarjeta_ultimos, estado, usuario_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insertar->execute([$plan, $equipos_extra, $jugadores_extra, $total, $nombre, $correo, $telefono, $organizacion, $deporte, $ciudad,
                        $marca, $ultimos, $pago["estado"], $usuario_id]);
    $id = (int) $conexion->lastInsertId();

    // la cuenta que compro (o la del correo si compro sin iniciar sesion) pasa a cliente
    $rol_cambiado = $usuario_id ? pasar_a_cliente($usuario_id) : pasar_a_cliente_por_correo($correo);

    return ["ok" => $id, "rol_cambiado" => $rol_cambiado];
}

function buscar_contratacion($id) {
    global $conexion;
    $consulta = $conexion->prepare("SELECT * FROM contrataciones WHERE id = ?");
    $consulta->execute([(int) $id]);
    return $consulta->fetch(PDO::FETCH_ASSOC) ?: null;
}

// las que todavia no tienen liga (las del numerito del menu)
function contrataciones_pendientes() {
    global $conexion;
    return $conexion->query("SELECT * FROM contrataciones WHERE liga_id IS NULL ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
}

// historial: las que ya tienen su liga
function historial_contrataciones($limite = 200) {
    global $conexion;
    return $conexion->query(
        "SELECT c.*, l.nombre AS nombre_liga FROM contrataciones c LEFT JOIN ligas l ON l.id = c.liga_id
         WHERE c.liga_id IS NOT NULL ORDER BY c.id DESC LIMIT " . (int) $limite
    )->fetchAll(PDO::FETCH_ASSOC);
}

// al crear la liga se marca la contratacion y se agrega al cliente como admin
function vincular_contratacion($contratacion_id, $liga_id, $asignado_por) {
    global $conexion;

    $orden = buscar_contratacion($contratacion_id);
    if (!$orden || $orden["liga_id"] !== null) {
        return "";
    }
    $marcar = $conexion->prepare("UPDATE contrataciones SET liga_id = ? WHERE id = ? AND liga_id IS NULL");
    $marcar->execute([(int) $liga_id, (int) $orden["id"]]);

    // por si la cuenta del correo todavia no era cliente (se registro despues o compro con otra cuenta)
    pasar_a_cliente_por_correo($orden["correo"]);
    $admin = agregar_admin_liga($liga_id, $orden["correo"], $asignado_por);
    return $admin === "ok"
        ? " " . $orden["nombre"] . " (" . $orden["correo"] . ") quedó como administrador de la liga."
        : " No se pudo sumar a " . $orden["correo"] . " como administrador: " . $admin;
}
