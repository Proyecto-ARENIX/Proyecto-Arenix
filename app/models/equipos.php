<?php
// equipos de ejemplo. los que se agregan van a la base (ver clubes.php)

require_once __DIR__ . "/clubes.php";

// arma la plantilla (titulares + suplentes) de forma fija para cada equipo de ejemplo
function armar_plantilla_ejemplo(int $semilla, string $deporte, string $formacion) {
    $nombres   = ["Mateo", "Lucas", "Thiago", "Benjamín", "Santiago", "Joaquín", "Bruno", "Valentín", "Tomás", "Facundo", "Nicolás", "Agustín", "Lautaro", "Franco", "Martín", "Ignacio", "Julián", "Gonzalo", "Emiliano", "Diego"];
    $apellidos = ["González", "Rodríguez", "Fernández", "López", "Martínez", "Pérez", "Gómez", "Díaz", "Sosa", "Romero", "Silva", "Acosta", "Benítez", "Medina", "Herrera", "Suárez", "Castro", "Vera", "Ríos", "Méndez"];
    $suplentes_por_deporte = [
        "futbol"     => ["POR", "DFC", "MC", "ED", "DC"],
        "basquetbol" => ["BA", "ES", "AL", "AP", "PIV", "AL", "ES"],
        "voley"      => ["COL", "CEN", "REC", "OP", "LIB", "REC"],
    ];

    $titulares = formaciones_por_deporte()[$deporte][$formacion]["posiciones"];
    $posiciones = array_merge($titulares, $suplentes_por_deporte[$deporte]);
    $jugadores = [];

    foreach ($posiciones as $i => $posicion) {
        $jugadores[] = [
            "numero"   => $i === 0 ? 1 : (($semilla * 7 + $i * 3) % 29) + 2,
            "nombre"   => $nombres[($semilla * 3 + $i * 7) % count($nombres)] . " " . $apellidos[($semilla * 5 + $i * 11) % count($apellidos)],
            "posicion" => $posicion,
            "titular"  => $i < count($titulares),
        ];
    }

    return $jugadores;
}

// equipos de ejemplo que vienen con la plataforma
function equipos_base() {
    $equipos = [
        ["id" => "tigres-fc",     "nombre" => "Tigres FC",     "logo" => "tigres-fc.png",     "deporte" => "futbol",     "torneo" => "Liga Apertura",      "ciudad" => "Montevideo",     "entrenador" => "Ricardo Paz",      "formacion" => "4-3-3",   "plan" => "pro",    "habilidades" => ["Ataque" => 88, "Defensa" => 79, "Posesión" => 84, "Velocidad" => 81, "Físico" => 76, "Pase" => 86]],
        ["id" => "halcones",      "nombre" => "Halcones",      "logo" => "halcones.png",      "deporte" => "futbol",     "torneo" => "Liga Apertura",      "ciudad" => "Canelones",      "entrenador" => "Andrés Molina",    "formacion" => "4-4-2",   "plan" => "pro",    "habilidades" => ["Ataque" => 82, "Defensa" => 74, "Posesión" => 70, "Velocidad" => 89, "Físico" => 72, "Pase" => 75]],
        ["id" => "pumas",         "nombre" => "Pumas",         "logo" => "pumas.png",         "deporte" => "futbol",     "torneo" => "Liga Apertura",      "ciudad" => "Maldonado",      "entrenador" => "Sergio Ibarra",    "formacion" => "4-2-3-1", "plan" => "basico", "habilidades" => []],
        ["id" => "estrellas",     "nombre" => "Estrellas",     "logo" => "estrellas.png",     "deporte" => "futbol",     "torneo" => "Liga Apertura",      "ciudad" => "Salto",          "entrenador" => "Pablo Rivero",     "formacion" => "4-3-3",   "plan" => "pro",    "habilidades" => ["Ataque" => 75, "Defensa" => 83, "Posesión" => 78, "Velocidad" => 71, "Físico" => 85, "Pase" => 77]],
        ["id" => "delfines",      "nombre" => "Delfines",      "logo" => "delfines.png",      "deporte" => "futbol",     "torneo" => "Liga Apertura",      "ciudad" => "Punta del Este", "entrenador" => "Hugo Cardozo",     "formacion" => "3-5-2",   "plan" => "basico", "habilidades" => []],
        ["id" => "aguilas-fc",    "nombre" => "Águilas FC",    "logo" => "aguilas-fc.png",    "deporte" => "futbol",     "torneo" => "Copa Ciudad",        "ciudad" => "Colonia",        "entrenador" => "Fernando Luna",    "formacion" => "4-3-3",   "plan" => "pro",    "habilidades" => ["Ataque" => 84, "Defensa" => 72, "Posesión" => 81, "Velocidad" => 86, "Físico" => 70, "Pase" => 83]],
        ["id" => "leones-united", "nombre" => "Leones United", "logo" => "leones-united.png", "deporte" => "futbol",     "torneo" => "Copa Ciudad",        "ciudad" => "Florida",        "entrenador" => "Gustavo Ferreira", "formacion" => "3-5-2",   "plan" => "pro",    "habilidades" => ["Ataque" => 80, "Defensa" => 78, "Posesión" => 85, "Velocidad" => 74, "Físico" => 80, "Pase" => 87]],
        ["id" => "raptors-sc",    "nombre" => "Raptors SC",    "logo" => "raptors-sc.png",    "deporte" => "futbol",     "torneo" => "Copa Ciudad",        "ciudad" => "Mercedes",       "entrenador" => "Claudio Viera",    "formacion" => "4-2-3-1", "plan" => "basico", "habilidades" => []],
        ["id" => "titanes",       "nombre" => "Titanes",       "logo" => "titanes.png",       "deporte" => "basquetbol", "torneo" => "Liga Metropolitana", "ciudad" => "Rocha",          "entrenador" => "Alberto Cabrera",  "formacion" => "1-2-2",   "plan" => "pro",    "habilidades" => ["Tiro" => 83, "Rebote" => 79, "Defensa" => 81, "Velocidad" => 77, "Físico" => 86, "Pase" => 74]],
        ["id" => "toros",         "nombre" => "Toros",         "logo" => "toros.png",         "deporte" => "basquetbol", "torneo" => "Liga Metropolitana", "ciudad" => "Paysandú",       "entrenador" => "Marcelo Duarte",   "formacion" => "1-2-2",   "plan" => "pro",    "habilidades" => ["Tiro" => 76, "Rebote" => 88, "Defensa" => 84, "Velocidad" => 70, "Físico" => 91, "Pase" => 72]],
        ["id" => "panteras",      "nombre" => "Panteras",      "logo" => "panteras.png",      "deporte" => "basquetbol", "torneo" => "Copa Ciudad",        "ciudad" => "Durazno",        "entrenador" => "Raúl Benítez",     "formacion" => "1-2-2",   "plan" => "basico", "habilidades" => []],
        ["id" => "guerreros",     "nombre" => "Guerreros",     "logo" => "guerreros.png",     "deporte" => "voley",      "torneo" => "Torneo Apertura",    "ciudad" => "Minas",          "entrenador" => "Javier Núñez",     "formacion" => "5-1",     "plan" => "pro",    "habilidades" => ["Saque" => 82, "Recepción" => 78, "Ataque" => 85, "Bloqueo" => 88, "Defensa" => 76, "Físico" => 84]],
        ["id" => "buhos",         "nombre" => "Búhos",         "logo" => "buhos.png",         "deporte" => "voley",      "torneo" => "Torneo Apertura",    "ciudad" => "Rivera",         "entrenador" => "Daniel Ortiz",     "formacion" => "5-1",     "plan" => "basico", "habilidades" => []],
        ["id" => "cobras",        "nombre" => "Cobras",        "logo" => "cobras.png",        "deporte" => "voley",      "torneo" => "Copa Ciudad",        "ciudad" => "Tacuarembó",     "entrenador" => "Esteban Rojas",    "formacion" => "5-1",     "plan" => "basico", "habilidades" => []],
    ];

    $deportes = deportes_clubes();
    $formaciones = formaciones_por_deporte();

    foreach ($equipos as $i => $equipo) {
        $equipos[$i]["logo"] = "/public/assets/images/" . $equipo["logo"];
        $equipos[$i]["nombre_deporte"] = $deportes[$equipo["deporte"]];
        $equipos[$i]["lineas"] = $formaciones[$equipo["deporte"]][$equipo["formacion"]]["lineas"];
        $equipos[$i]["jugadores"] = armar_plantilla_ejemplo($i + 1, $equipo["deporte"], $equipo["formacion"]);
    }

    return $equipos;
}

// todos los equipos: los de ejemplo (o su version editada) + los agregados a la base
function listar_equipos() {
    global $conexion;

    $editados = [];
    $nuevos = [];
    foreach (listar_clubes_bd() as $club) {
        if ($club["clave_base"]) {
            $editados[$club["clave_base"]] = $club;
        } else {
            $nuevos[] = $club;
        }
    }

    // los equipos de ejemplo que se eliminaron no se muestran
    $eliminados = $conexion->query("SELECT club_clave FROM equipos_eliminados")->fetchAll(PDO::FETCH_COLUMN);
    $base = array_filter(equipos_base(), fn($equipo) => !in_array($equipo["id"], $eliminados, true));

    $base = array_map(fn($equipo) => $editados[$equipo["id"]] ?? $equipo, $base);
    return array_merge(array_values($base), $nuevos);
}
