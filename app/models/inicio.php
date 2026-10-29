<?php
// datos de la pagina de inicio: textos que se editan + lo ultimo de las ligas y noticias

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/horarios.php";
require_once __DIR__ . "/noticias.php";

// lo que se puede editar de cada cuadro de la portada (admin y organizador). se guarda en configuracion
// cada campo: [nombre, valor por defecto, largo maximo (o maximo si es numero), tipo]
function cuadros_portada() {
    $titulo = fn($por_defecto) => ["Título del cuadro", $por_defecto, 40, "texto"];
    $enlace = fn($por_defecto) => ["Texto del enlace de arriba", $por_defecto, 30, "texto"];
    $pie = fn($por_defecto) => ["Texto del enlace de abajo", $por_defecto, 40, "texto"];
    $cantidad = fn($por_defecto) => ["Cuántos mostrar", (string) $por_defecto, 6, "numero"];

    return [
        "bienvenida" => ["Bienvenida", [
            "saludo" => ["Saludo", "Bienvenido a", 40, "texto"],
            "bajada" => ["Texto debajo de ARENIX", "La plataforma integral para la gestión deportiva y la competencia.", 160, "largo"],
            "boton" => ["Texto del botón", "Explorar Enfrentamientos", 40, "texto"],
            "boton_pagina" => ["El botón lleva a", "enfrentamientos", 20, "pagina"],
        ]],
        "enfrentamientos" => ["Enfrentamientos", [
            "titulo" => $titulo("ENFRENTAMIENTOS"),
            "enlace" => $enlace("Ver todos"),
            "cantidad" => $cantidad(3),
        ]],
        "resultados" => ["Resultados", [
            "titulo" => $titulo("RESULTADOS"),
            "enlace" => $enlace("Ver todos"),
            "pie" => $pie("Ver todos los resultados"),
            "cantidad" => $cantidad(4),
        ]],
        "horarios" => ["Próximos horarios", [
            "titulo" => $titulo("PRÓXIMOS HORARIOS"),
            "enlace" => $enlace("Ver calendario"),
            "pie" => $pie("Ver todos los horarios"),
            "cantidad" => $cantidad(4),
        ]],
        "evento" => ["Eventos destacados", [
            "titulo" => $titulo("EVENTOS DESTACADOS"),
            "enlace" => $enlace("Ver todos"),
            "nombre" => ["Nombre del evento", "Final de la Copa Élite", 80, "texto"],
            "fecha" => ["Fecha y hora", "24 May • 18:00", 40, "texto"],
            "lugar" => ["Lugar", "Estadio Principal", 80, "texto"],
            "texto" => ["Descripción", "La gran final que definirá al campeón.", 200, "largo"],
            // opcional: si no se sube nada queda la que estaba
            "foto" => ["Foto del evento", "", 0, "foto"],
        ]],
        "noticias" => ["Noticias", [
            "titulo" => $titulo("NOTICIAS"),
            "enlace" => $enlace("Ver todas"),
            "cantidad" => $cantidad(2),
        ]],
        "lideres" => ["Líderes de las ligas", [
            "titulo" => $titulo("LÍDERES DE LAS LIGAS"),
            "enlace" => $enlace("Ver ligas"),
            "pie" => $pie("Ver las tablas de posiciones"),
            "cantidad" => $cantidad(5),
        ]],
    ];
}

// paginas a las que puede llevar el boton de la portada
function paginas_boton_portada() {
    return [
        "enfrentamientos" => "Ligas",
        "resultados" => "Resultados",
        "horarios" => "Horarios",
        "eventos" => "Eventos",
        "noticias" => "Noticias",
        "planes" => "Planes",
    ];
}

// textos de todos los cuadros: ["enfrentamientos" => ["titulo" => ..., ...], ...]
function textos_portada() {
    global $conexion;

    $guardados = $conexion->query("SELECT clave, valor FROM configuracion WHERE clave LIKE 'inicio\\_%'")->fetchAll(PDO::FETCH_KEY_PAIR);

    $textos = [];
    foreach (cuadros_portada() as $cuadro => [, $campos]) {
        foreach ($campos as $campo => [, $por_defecto]) {
            $textos[$cuadro][$campo] = $guardados["inicio_{$cuadro}_{$campo}"] ?? $por_defecto;
        }
    }
    if (!isset(paginas_boton_portada()[$textos["bienvenida"]["boton_pagina"]])) {
        $textos["bienvenida"]["boton_pagina"] = "enfrentamientos";
    }
    return $textos;
}

// guarda solo los campos del cuadro que se edito
function guardar_cuadro_portada($cuadro, $datos, $archivos = []) {
    global $conexion;

    $cuadros = cuadros_portada();
    if (!is_string($cuadro) || !isset($cuadros[$cuadro])) {
        return "Ese cuadro no existe.";
    }

    $limpios = [];
    $foto_vieja = null;
    $foto_subida = null;
    foreach ($cuadros[$cuadro][1] as $campo => [$nombre, , $maximo, $tipo]) {
        if ($tipo === "foto") {
            $actual = textos_portada()[$cuadro][$campo];
            $archivo = $archivos[$campo] ?? null;
            if (is_array($archivo) && ($archivo["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $subida = subir_imagen_club($archivo, "portada", "La foto");
                if (isset($subida["error"])) {
                    return $subida["error"];
                }
                $foto_subida = $subida["ruta"];
                $limpios["inicio_{$cuadro}_{$campo}"] = $foto_subida;
                $foto_vieja = $actual;
            } elseif (($datos["quitar_" . $campo] ?? "") === "1") {
                $limpios["inicio_{$cuadro}_{$campo}"] = "";
                $foto_vieja = $actual;
            }
            continue;
        }

        $valor = isset($datos[$campo]) && is_string($datos[$campo]) ? trim($datos[$campo]) : "";
        if ($valor === "") {
            return "Completá el campo \"$nombre\".";
        }
        if ($tipo === "numero") {
            if (!filter_var($valor, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => $maximo]])) {
                return "\"$nombre\" tiene que ser un número del 1 al $maximo.";
            }
        } elseif ($tipo === "pagina") {
            if (!isset(paginas_boton_portada()[$valor])) {
                return "Elegí a qué página lleva el botón.";
            }
        } elseif (mb_strlen($valor, "UTF-8") > $maximo) {
            return "\"$nombre\" puede tener hasta $maximo caracteres.";
        }
        $limpios["inicio_{$cuadro}_{$campo}"] = $valor;
    }

    $guardar = $conexion->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    foreach ($limpios as $clave => $valor) {
        $guardar->execute([$clave, $valor]);
    }
    // la foto anterior ya no se usa
    if ($foto_vieja && $foto_vieja !== $foto_subida) {
        borrar_imagen_subida($foto_vieja);
    }
    return "ok";
}

// proximos partidos (los que todavia no pasaron), del mas cercano al mas lejano
function proximos_partidos_inicio($cantidad) {
    $partidos = [];
    foreach (listar_horarios() as $deporte) {
        foreach ($deporte["partidos"] as $partido) {
            if (!$partido["pendiente"]) {
                $partidos[] = $partido;
            }
        }
    }
    usort($partidos, fn($a, $b) => [$a["fecha"], $a["hora"] ?: "99"] <=> [$b["fecha"], $b["hora"] ?: "99"]);
    return array_slice($partidos, 0, $cantidad);
}

// ultimos resultados de todas las ligas
function ultimos_resultados_inicio($cantidad) {
    global $conexion;

    $equipos = array_column(listar_equipos(), null, "id");
    $meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    $consulta = $conexion->prepare(
        "SELECT p.local, p.visitante, p.fecha, p.goles_local, p.goles_visitante, l.nombre AS liga
         FROM partidos p JOIN torneos t ON t.id = p.torneo_id JOIN ligas l ON l.id = t.liga_id
         WHERE p.estado = 'finalizado' ORDER BY p.fecha DESC, p.id DESC LIMIT ?"
    );
    $consulta->bindValue(1, (int) $cantidad, PDO::PARAM_INT);
    $consulta->execute();

    $equipo = fn($clave) => [
        "nombre" => $equipos[$clave]["nombre"] ?? $clave,
        "logo" => $equipos[$clave]["logo"] ?? "/public/assets/images/logo a.png",
    ];
    return array_map(fn($fila) => [
        "dia" => date("d", strtotime($fila["fecha"])),
        "mes" => $meses[(int) date("n", strtotime($fila["fecha"])) - 1],
        "liga" => $fila["liga"],
        "local" => $equipo($fila["local"]),
        "visitante" => $equipo($fila["visitante"]),
        "marcador" => $fila["goles_local"] . " - " . $fila["goles_visitante"],
    ], $consulta->fetchAll(PDO::FETCH_ASSOC));
}

// el primero de la tabla del torneo en curso de cada liga
function lideres_de_ligas($cantidad) {
    $equipos = listar_equipos();
    $lideres = [];

    foreach (listar_ligas() as $liga) {
        $torneo = current(array_filter(torneos_de_liga($liga["id"]), fn($t) => $t["estado"] === "activo"));
        if (!$torneo) {
            continue;
        }
        $de_la_liga = array_values(array_filter($equipos, fn($equipo) => in_array($equipo["id"], $liga["clubes"], true)));
        $tabla = tabla_posiciones(partidos_de_torneo($torneo["id"]), $de_la_liga, $liga["deporte"]);
        if (!isset($tabla[0]) || $tabla[0]["pj"] === 0) {
            continue;
        }

        // lo que ordena la tabla de ese deporte: puntos, ganados o porcentaje
        $orden = reglas_tabla($liga["deporte"])["orden"][0];
        $detalle = [
            "pts" => $tabla[0]["pts"] . " pts",
            "g" => ($tabla[0]["g"] + $tabla[0]["ge"]) . " G",
            "pct" => valor_tabla($tabla[0], "pct"),
        ][$orden];

        $lideres[] = [
            "liga" => $liga["nombre"],
            "liga_id" => $liga["id"],
            "deporte" => $liga["nombre_deporte"],
            "nombre" => $tabla[0]["nombre"],
            "logo" => $tabla[0]["logo"],
            "detalle" => $detalle,
            "pj" => $tabla[0]["pj"],
        ];
    }
    return array_slice($lideres, 0, $cantidad);
}
