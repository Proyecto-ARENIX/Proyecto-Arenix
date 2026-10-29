<?php
// deportes con sus posiciones, formaciones y habilidades
// todas las formaciones de un deporte tienen la misma cantidad de titulares

function definiciones_deportes() {
    $futbol_posiciones = [
        "POR" => "Portero",
        "LIB" => "Líbero",
        "LI"  => "Lateral izquierdo",
        "DFC" => "Defensa central",
        "LD"  => "Lateral derecho",
        "CAI" => "Carrilero izquierdo",
        "CAD" => "Carrilero derecho",
        "MCD" => "Mediocentro defensivo",
        "MC"  => "Mediocentro",
        "MI"  => "Medio izquierdo",
        "MD"  => "Medio derecho",
        "MCO" => "Mediapunta",
        "INI" => "Interior izquierdo",
        "IND" => "Interior derecho",
        "EI"  => "Extremo izquierdo",
        "ED"  => "Extremo derecho",
        "SD"  => "Segundo delantero",
        "DC"  => "Delantero centro",
    ];
    $defensa4 = ["LI", "DFC", "DFC", "LD"];
    $defensa3 = ["DFC", "DFC", "DFC"];
    $defensa5 = ["CAI", "DFC", "DFC", "DFC", "CAD"];

    $futbol_habilidades = ["Ataque", "Defensa", "Posesión", "Velocidad", "Físico", "Pase"];
    $basquet_habilidades = ["Tiro", "Rebote", "Defensa", "Velocidad", "Físico", "Pase"];
    $voley_habilidades = ["Saque", "Recepción", "Ataque", "Bloqueo", "Defensa", "Físico"];
    $rugby_habilidades = ["Ataque", "Defensa", "Scrum", "Tackle", "Físico", "Patada"];
    $beisbol_habilidades = ["Bateo", "Pitcheo", "Fildeo", "Corrido de bases", "Potencia", "Brazo"];

    $beisbol_posiciones = [
        "P"  => "Lanzador (pitcher)",
        "C"  => "Receptor (catcher)",
        "1B" => "Primera base",
        "2B" => "Segunda base",
        "3B" => "Tercera base",
        "SS" => "Campocorto",
        "LF" => "Jardinero izquierdo",
        "CF" => "Jardinero central",
        "RF" => "Jardinero derecho",
    ];
    $beisbol_formaciones = [
        "Defensiva estándar" => [["C"], ["P"], ["3B", "SS", "2B", "1B"], ["LF", "CF", "RF"]],
        "Cuadro adelantado"  => [["C"], ["3B", "P", "1B"], ["SS", "2B"], ["LF", "CF", "RF"]],
        "Shift (cuadro cargado a la derecha)" => [["C"], ["P"], ["3B", "SS"], ["2B", "1B"], ["LF", "CF", "RF"]],
    ];

    return [
        "futbol" => [
            "nombre" => "Fútbol",
            "habilidades" => $futbol_habilidades,
            "posiciones" => $futbol_posiciones,
            "formaciones" => [
                "4-4-2"            => [["POR"], $defensa4, ["MI", "MC", "MC", "MD"], ["DC", "DC"]],
                "4-3-3"            => [["POR"], $defensa4, ["MC", "MC", "MC"], ["EI", "DC", "ED"]],
                "4-2-3-1"          => [["POR"], $defensa4, ["MCD", "MCD"], ["EI", "MCO", "ED"], ["DC"]],
                "4-1-4-1"          => [["POR"], $defensa4, ["MCD"], ["MI", "MC", "MC", "MD"], ["DC"]],
                "4-4-1-1"          => [["POR"], $defensa4, ["MI", "MC", "MC", "MD"], ["SD"], ["DC"]],
                "4-3-2-1"          => [["POR"], $defensa4, ["MC", "MC", "MC"], ["MCO", "MCO"], ["DC"]],
                "4-1-2-1-2 (rombo)" => [["POR"], $defensa4, ["MCD"], ["MC", "MC"], ["MCO"], ["DC", "DC"]],
                "4-3-1-2"          => [["POR"], $defensa4, ["MC", "MC", "MC"], ["MCO"], ["DC", "DC"]],
                "4-2-2-2"          => [["POR"], $defensa4, ["MCD", "MCD"], ["MCO", "MCO"], ["DC", "DC"]],
                "4-5-1"            => [["POR"], $defensa4, ["MI", "MC", "MC", "MC", "MD"], ["DC"]],
                "4-2-4"            => [["POR"], $defensa4, ["MC", "MC"], ["EI", "DC", "DC", "ED"]],
                "4-1-3-2"          => [["POR"], $defensa4, ["MCD"], ["MI", "MC", "MD"], ["DC", "DC"]],
                "4-6-0"            => [["POR"], $defensa4, ["MCD", "MCD"], ["EI", "MCO", "MCO", "ED"]],
                "3-5-2"            => [["POR"], $defensa3, ["CAI", "MC", "MCD", "MC", "CAD"], ["DC", "DC"]],
                "3-4-3"            => [["POR"], $defensa3, ["CAI", "MC", "MC", "CAD"], ["EI", "DC", "ED"]],
                "3-4-1-2"          => [["POR"], $defensa3, ["CAI", "MC", "MC", "CAD"], ["MCO"], ["DC", "DC"]],
                "3-4-2-1"          => [["POR"], $defensa3, ["CAI", "MC", "MC", "CAD"], ["MCO", "MCO"], ["DC"]],
                "3-1-4-2"          => [["POR"], $defensa3, ["MCD"], ["CAI", "MC", "MC", "CAD"], ["DC", "DC"]],
                "3-6-1"            => [["POR"], $defensa3, ["MCD", "MCD"], ["CAI", "MCO", "MCO", "CAD"], ["DC"]],
                "3-5-1-1"          => [["POR"], $defensa3, ["CAI", "MC", "MCD", "MC", "CAD"], ["SD"], ["DC"]],
                "3-3-1-3"          => [["POR"], $defensa3, ["MI", "MCD", "MD"], ["MCO"], ["EI", "DC", "ED"]],
                "5-3-2"            => [["POR"], $defensa5, ["MC", "MC", "MC"], ["DC", "DC"]],
                "5-4-1"            => [["POR"], $defensa5, ["MI", "MC", "MC", "MD"], ["DC"]],
                "5-2-3"            => [["POR"], $defensa5, ["MC", "MC"], ["EI", "DC", "ED"]],
                "5-2-1-2"          => [["POR"], $defensa5, ["MC", "MC"], ["MCO"], ["DC", "DC"]],
                "1-4-3-2 (catenaccio)" => [["POR"], ["LIB"], $defensa4, ["MC", "MC", "MC"], ["DC", "DC"]],
                "3-2-2-3 (WM)"     => [["POR"], $defensa3, ["MC", "MC"], ["INI", "IND"], ["EI", "DC", "ED"]],
                "2-3-5 (pirámide)" => [["POR"], ["DFC", "DFC"], ["MI", "MC", "MD"], ["EI", "INI", "DC", "IND", "ED"]],
            ],
        ],

        "futbol7" => [
            "nombre" => "Fútbol 7",
            "habilidades" => $futbol_habilidades,
            "posiciones" => $futbol_posiciones,
            "formaciones" => [
                "3-2-1"   => [["POR"], ["LI", "DFC", "LD"], ["MC", "MC"], ["DC"]],
                "2-3-1"   => [["POR"], ["DFC", "DFC"], ["MI", "MC", "MD"], ["DC"]],
                "3-1-2"   => [["POR"], ["LI", "DFC", "LD"], ["MC"], ["DC", "DC"]],
                "2-1-2-1" => [["POR"], ["DFC", "DFC"], ["MCD"], ["MI", "MD"], ["DC"]],
                "3-3"     => [["POR"], ["LI", "DFC", "LD"], ["EI", "DC", "ED"]],
                "2-2-2"   => [["POR"], ["DFC", "DFC"], ["MC", "MC"], ["DC", "DC"]],
                "1-3-2"   => [["POR"], ["LIB"], ["MI", "MC", "MD"], ["DC", "DC"]],
                "2-1-3"   => [["POR"], ["DFC", "DFC"], ["MC"], ["EI", "DC", "ED"]],
            ],
        ],

        "futsal" => [
            "nombre" => "Futsal (fútbol 5)",
            "habilidades" => $futbol_habilidades,
            "posiciones" => [
                "POR" => "Portero",
                "CIE" => "Cierre",
                "ALI" => "Ala izquierdo",
                "ALD" => "Ala derecho",
                "PIV" => "Pívot",
                "UNI" => "Universal",
            ],
            "formaciones" => [
                "1-2-1 (rombo)"    => [["POR"], ["CIE"], ["ALI", "ALD"], ["PIV"]],
                "2-2 (cuadrado)"   => [["POR"], ["CIE", "CIE"], ["PIV", "PIV"]],
                "3-1"              => [["POR"], ["ALI", "CIE", "ALD"], ["PIV"]],
                "1-3"              => [["POR"], ["CIE"], ["ALI", "PIV", "ALD"]],
                "4-0 (universal)"  => [["POR"], ["UNI", "UNI", "UNI", "UNI"]],
            ],
        ],

        "basquetbol" => [
            "nombre" => "Básquetbol",
            "habilidades" => $basquet_habilidades,
            "posiciones" => [
                "BA"  => "Base",
                "ES"  => "Escolta",
                "AL"  => "Alero",
                "AP"  => "Ala-pívot",
                "PIV" => "Pívot",
            ],
            "formaciones" => [
                "1-2-2" => [["BA"], ["ES", "AL"], ["AP", "PIV"]],
                "1-3-1" => [["BA"], ["ES", "AP", "AL"], ["PIV"]],
                "2-1-2" => [["BA", "ES"], ["AP"], ["AL", "PIV"]],
                "2-3"   => [["BA", "ES"], ["AL", "AP", "PIV"]],
                "3-2"   => [["ES", "BA", "AL"], ["AP", "PIV"]],
                "1-4 (alto)" => [["BA"], ["ES", "AP", "PIV", "AL"]],
                "4-1 (cuatro abiertos)" => [["ES", "BA", "AL", "AP"], ["PIV"]],
            ],
        ],

        "basquet3x3" => [
            "nombre" => "Básquet 3x3",
            "habilidades" => $basquet_habilidades,
            "posiciones" => [
                "BA"  => "Base",
                "AL"  => "Alero",
                "PIV" => "Pívot",
            ],
            "formaciones" => [
                "1-2" => [["BA"], ["AL", "PIV"]],
                "2-1" => [["BA", "AL"], ["PIV"]],
                "3-0 (abiertos)" => [["AL", "BA", "PIV"]],
            ],
        ],

        "voley" => [
            "nombre" => "Vóley",
            "habilidades" => $voley_habilidades,
            "posiciones" => [
                "COL" => "Colocador",
                "OP"  => "Opuesto",
                "REC" => "Receptor punta",
                "CEN" => "Central",
                "LIB" => "Líbero",
                "UNI" => "Universal",
            ],
            "formaciones" => [
                "5-1"           => [["REC", "LIB", "COL"], ["REC", "CEN", "OP"]],
                "4-2"           => [["REC", "CEN", "COL"], ["REC", "CEN", "COL"]],
                "6-2"           => [["REC", "CEN", "COL"], ["REC", "CEN", "OP"]],
                "6-0 (universal)" => [["UNI", "UNI", "UNI"], ["UNI", "UNI", "UNI"]],
            ],
        ],

        "voleyplaya" => [
            "nombre" => "Vóley playa",
            "habilidades" => $voley_habilidades,
            "posiciones" => [
                "BLO" => "Bloqueador",
                "DEF" => "Defensor",
                "JIZ" => "Jugador izquierdo",
                "JDE" => "Jugador derecho",
            ],
            "formaciones" => [
                "Bloqueador y defensor" => [["DEF"], ["BLO"]],
                "Lado a lado"           => [["JIZ", "JDE"]],
            ],
        ],

        "rugby" => [
            "nombre" => "Rugby",
            "habilidades" => $rugby_habilidades,
            "posiciones" => [
                "PII" => "Pilar izquierdo",
                "HOO" => "Hooker",
                "PID" => "Pilar derecho",
                "SEL" => "Segunda línea",
                "ALA" => "Ala",
                "OCT" => "Octavo",
                "MSC" => "Medio scrum",
                "APE" => "Apertura",
                "CIN" => "Centro interno",
                "CEX" => "Centro externo",
                "WIZ" => "Wing izquierdo",
                "WDE" => "Wing derecho",
                "ZAG" => "Fullback",
            ],
            "formaciones" => [
                "8 forwards + 7 backs" => [["ZAG"], ["WIZ", "CIN", "CEX", "WDE"], ["APE"], ["MSC"], ["ALA", "OCT", "ALA"], ["SEL", "SEL"], ["PII", "HOO", "PID"]],
                "Línea de backs plana" => [["ZAG"], ["MSC"], ["WIZ", "APE", "CIN", "CEX", "WDE"], ["ALA", "OCT", "ALA"], ["SEL", "SEL"], ["PII", "HOO", "PID"]],
            ],
        ],

        "rugby7" => [
            "nombre" => "Rugby 7",
            "habilidades" => $rugby_habilidades,
            "posiciones" => [
                "PII" => "Pilar izquierdo",
                "HOO" => "Hooker",
                "PID" => "Pilar derecho",
                "MSC" => "Medio scrum",
                "APE" => "Apertura",
                "CEN" => "Centro",
                "WIN" => "Wing",
            ],
            "formaciones" => [
                "3 forwards + 4 backs" => [["CEN", "WIN"], ["APE"], ["MSC"], ["PII", "HOO", "PID"]],
                "Backs en línea"       => [["MSC", "APE", "CEN", "WIN"], ["PII", "HOO", "PID"]],
            ],
        ],

        "hockey" => [
            "nombre" => "Hockey sobre césped",
            "habilidades" => ["Ataque", "Defensa", "Técnica", "Velocidad", "Físico", "Pase"],
            "posiciones" => [
                "ARQ" => "Arquero",
                "LIB" => "Líbero",
                "DEF" => "Defensor",
                "VOL" => "Volante",
                "DEL" => "Delantero",
            ],
            "formaciones" => [
                "4-3-3"   => [["ARQ"], ["DEF", "DEF", "DEF", "DEF"], ["VOL", "VOL", "VOL"], ["DEL", "DEL", "DEL"]],
                "3-3-1-3" => [["ARQ"], ["DEF", "DEF", "DEF"], ["VOL", "VOL", "VOL"], ["VOL"], ["DEL", "DEL", "DEL"]],
                "1-3-3-3" => [["ARQ"], ["LIB"], ["DEF", "DEF", "DEF"], ["VOL", "VOL", "VOL"], ["DEL", "DEL", "DEL"]],
                "4-4-2"   => [["ARQ"], ["DEF", "DEF", "DEF", "DEF"], ["VOL", "VOL", "VOL", "VOL"], ["DEL", "DEL"]],
                "3-4-3"   => [["ARQ"], ["DEF", "DEF", "DEF"], ["VOL", "VOL", "VOL", "VOL"], ["DEL", "DEL", "DEL"]],
                "5-3-2"   => [["ARQ"], ["DEF", "DEF", "DEF", "DEF", "DEF"], ["VOL", "VOL", "VOL"], ["DEL", "DEL"]],
                "4-2-3-1" => [["ARQ"], ["DEF", "DEF", "DEF", "DEF"], ["VOL", "VOL"], ["VOL", "VOL", "VOL"], ["DEL"]],
            ],
        ],

        "hockeyhielo" => [
            "nombre" => "Hockey sobre hielo",
            "habilidades" => ["Ataque", "Defensa", "Patinaje", "Tiro", "Físico", "Pase"],
            "posiciones" => [
                "POR" => "Portero",
                "DEF" => "Defensa",
                "ALI" => "Ala izquierdo",
                "CEN" => "Centro",
                "ALD" => "Ala derecho",
            ],
            "formaciones" => [
                "2-3 (estándar)"   => [["POR"], ["DEF", "DEF"], ["ALI", "CEN", "ALD"]],
                "1-2-2 (forecheck)" => [["POR"], ["DEF", "DEF"], ["ALI", "ALD"], ["CEN"]],
                "1-3-1 (power play)" => [["POR"], ["DEF"], ["ALI", "DEF", "ALD"], ["CEN"]],
                "2-1-2"            => [["POR"], ["DEF", "DEF"], ["CEN"], ["ALI", "ALD"]],
            ],
        ],

        "handball" => [
            "nombre" => "Handball",
            "habilidades" => ["Ataque", "Defensa", "Lanzamiento", "Velocidad", "Físico", "Pase"],
            "posiciones" => [
                "POR" => "Portero",
                "EXI" => "Extremo izquierdo",
                "LAI" => "Lateral izquierdo",
                "CEN" => "Central",
                "LAD" => "Lateral derecho",
                "EXD" => "Extremo derecho",
                "PIV" => "Pivote",
            ],
            "formaciones" => [
                "6-0"   => [["POR"], ["EXI", "LAI", "CEN", "PIV", "LAD", "EXD"]],
                "5-1"   => [["POR"], ["EXI", "LAI", "PIV", "LAD", "EXD"], ["CEN"]],
                "4-2"   => [["POR"], ["EXI", "LAI", "LAD", "EXD"], ["CEN", "PIV"]],
                "3-3"   => [["POR"], ["LAI", "PIV", "LAD"], ["EXI", "CEN", "EXD"]],
                "3-2-1" => [["POR"], ["LAI", "PIV", "LAD"], ["EXI", "EXD"], ["CEN"]],
            ],
        ],

        "beisbol" => [
            "nombre" => "Béisbol",
            "habilidades" => $beisbol_habilidades,
            "posiciones" => $beisbol_posiciones,
            "formaciones" => $beisbol_formaciones,
        ],

        "softbol" => [
            "nombre" => "Softbol",
            "habilidades" => $beisbol_habilidades,
            "posiciones" => $beisbol_posiciones,
            "formaciones" => $beisbol_formaciones,
        ],

        "waterpolo" => [
            "nombre" => "Waterpolo",
            "habilidades" => ["Natación", "Tiro", "Defensa", "Pase", "Físico", "Resistencia"],
            "posiciones" => [
                "POR" => "Portero",
                "EXI" => "Extremo izquierdo",
                "LAI" => "Lateral izquierdo",
                "CEN" => "Central (punto)",
                "LAD" => "Lateral derecho",
                "EXD" => "Extremo derecho",
                "BOY" => "Boya",
            ],
            "formaciones" => [
                "3-3 (arco)"    => [["POR"], ["LAI", "CEN", "LAD"], ["EXI", "BOY", "EXD"]],
                "4-2"           => [["POR"], ["EXI", "LAI", "LAD", "EXD"], ["CEN", "BOY"]],
                "6-0 (pressing)" => [["POR"], ["EXI", "LAI", "CEN", "BOY", "LAD", "EXD"]],
            ],
        ],

        "americano" => [
            "nombre" => "Fútbol americano",
            "habilidades" => ["Pase", "Carrera", "Recepción", "Bloqueo", "Tackleo", "Físico"],
            "posiciones" => [
                "QB" => "Quarterback",
                "RB" => "Corredor (running back)",
                "FB" => "Fullback",
                "WR" => "Receptor abierto",
                "TE" => "Ala cerrada (tight end)",
                "C"  => "Centro",
                "G"  => "Guardia",
                "T"  => "Tackle",
                "DE" => "Ala defensiva",
                "DT" => "Tackle defensivo",
                "LB" => "Apoyador (linebacker)",
                "CB" => "Esquinero",
                "S"  => "Profundo (safety)",
            ],
            "formaciones" => [
                "Ataque · I-Formation" => [["RB"], ["FB"], ["QB"], ["WR", "T", "G", "C", "G", "T", "TE", "WR"]],
                "Ataque · Shotgun"     => [["QB", "RB"], ["WR"], ["WR", "T", "G", "C", "G", "T", "TE", "WR"]],
                "Ataque · Singleback"  => [["RB"], ["QB"], ["WR"], ["WR", "T", "G", "C", "G", "T", "TE", "WR"]],
                "Ataque · Pistol"      => [["RB"], ["QB"], ["TE"], ["WR", "T", "G", "C", "G", "T", "TE", "WR"]],
                "Ataque · Pro Set"     => [["RB", "FB"], ["QB"], ["WR", "T", "G", "C", "G", "T", "TE", "WR"]],
                "Defensa · 4-3"        => [["S", "S"], ["CB", "LB", "LB", "LB", "CB"], ["DE", "DT", "DT", "DE"]],
                "Defensa · 3-4"        => [["S", "S"], ["CB", "LB", "LB", "LB", "LB", "CB"], ["DE", "DT", "DE"]],
                "Defensa · 4-4"        => [["S"], ["CB", "CB"], ["LB", "LB", "LB", "LB"], ["DE", "DT", "DT", "DE"]],
                "Defensa · Nickel (4-2-5)" => [["S", "S"], ["CB", "CB", "CB"], ["LB", "LB"], ["DE", "DT", "DT", "DE"]],
                "Defensa · Dime (4-1-6)"   => [["S", "S"], ["CB", "CB", "CB", "CB"], ["LB"], ["DE", "DT", "DT", "DE"]],
            ],
        ],
    ];
}

function deportes_clubes() {
    return array_map(fn($deporte) => $deporte["nombre"], definiciones_deportes());
}

// nombre completo de cada codigo de posicion, por deporte (+ "SUP" para suplentes sin posicion)
function posiciones_por_deporte() {
    return array_map(fn($deporte) => $deporte["posiciones"] + ["SUP" => "Suplente"], definiciones_deportes());
}

// por deporte y formacion: "lineas" (cantidad por linea) y "posiciones" (codigos en orden)
function formaciones_por_deporte() {
    $resultado = [];
    foreach (definiciones_deportes() as $id => $deporte) {
        foreach ($deporte["formaciones"] as $nombre => $lineas) {
            $resultado[$id][$nombre] = [
                "lineas"     => array_map("count", $lineas),
                "posiciones" => array_merge(...$lineas),
            ];
        }
    }
    return $resultado;
}

function habilidades_por_deporte() {
    return array_map(fn($deporte) => $deporte["habilidades"], definiciones_deportes());
}

function cantidad_titulares($deporte) {
    $formaciones = formaciones_por_deporte()[$deporte];
    return count(reset($formaciones)["posiciones"]);
}
