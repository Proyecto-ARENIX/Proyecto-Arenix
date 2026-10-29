<?php
// reglamento resumido de cada deporte que se puede usar en las ligas

require_once __DIR__ . "/deportes.php";
require_once __DIR__ . "/ligas.php";

function reglamentos_texto() {
    return [
        "futbol" => [
            "federacion" => "IFAB / FIFA",
            "enlace" => "https://www.theifab.com/laws/",
            "jugadores" => "11 por equipo (con arquero)",
            "duracion" => "2 tiempos de 45 minutos",
            "reglas" => [
                "Gana el equipo que hace más goles. Puede haber empate.",
                "Solo el arquero puede tocar la pelota con la mano, y dentro de su área.",
                "Hay fuera de juego: no se puede recibir un pase estando más adelantado que el penúltimo rival.",
                "Tarjeta amarilla de advertencia; dos amarillas o una roja son expulsión.",
            ],
        ],
        "futbol7" => [
            "federacion" => "Reglas de fútbol adaptadas por cada liga",
            "enlace" => null,
            "jugadores" => "7 por equipo (con arquero)",
            "duracion" => "La fija cada liga, normalmente 2 tiempos de 25 a 30 minutos",
            "reglas" => [
                "Se juega en una cancha más chica que la de fútbol 11.",
                "Los cambios suelen ser ilimitados y el que sale puede volver a entrar.",
                "El resto de las reglas son las del fútbol (goles, faltas y tarjetas).",
            ],
        ],
        "futsal" => [
            "federacion" => "FIFA",
            "enlace" => "https://www.fifa.com",
            "jugadores" => "5 por equipo (con arquero)",
            "duracion" => "2 tiempos de 20 minutos con reloj detenido",
            "reglas" => [
                "No hay fuera de juego y los cambios son ilimitados.",
                "El saque de banda se hace con el pie y hay 4 segundos para sacar.",
                "Desde la sexta falta acumulada en un tiempo, cada falta es un tiro libre sin barrera.",
            ],
        ],
        "basquetbol" => [
            "federacion" => "FIBA",
            "enlace" => "https://www.fiba.basketball",
            "jugadores" => "5 por equipo en cancha",
            "duracion" => "4 cuartos de 10 minutos",
            "reglas" => [
                "Una canasta vale 2 puntos, o 3 si se tira desde fuera de la línea de triple. Cada tiro libre vale 1.",
                "Hay 24 segundos para tirar al aro en cada posesión.",
                "No hay empates: se juegan suplementarios de 5 minutos hasta que gane uno.",
                "Con 5 faltas personales el jugador no puede seguir jugando.",
            ],
        ],
        "basquet3x3" => [
            "federacion" => "FIBA 3x3",
            "enlace" => "https://fiba3x3.com",
            "jugadores" => "3 por equipo en cancha y 1 suplente",
            "duracion" => "10 minutos o hasta que un equipo llegue a 21 puntos",
            "reglas" => [
                "Se juega en media cancha con un solo aro.",
                "Una canasta vale 1 punto, o 2 si se tira desde fuera del arco.",
                "Hay 12 segundos para tirar al aro en cada posesión.",
                "Si terminan empatados, gana el primero que haga 2 puntos en el alargue.",
            ],
        ],
        "voley" => [
            "federacion" => "FIVB",
            "enlace" => "https://www.fivb.com",
            "jugadores" => "6 por equipo en cancha",
            "duracion" => "Al mejor de 5 sets, sin tiempo fijo",
            "reglas" => [
                "Los sets se juegan a 25 puntos con 2 de diferencia; el quinto set es a 15.",
                "Cada equipo tiene como máximo 3 toques para pasar la pelota.",
                "Los jugadores rotan de posición cada vez que el equipo recupera el saque.",
                "El líbero solo defiende: no puede sacar ni atacar por encima de la red.",
            ],
        ],
        "voleyplaya" => [
            "federacion" => "FIVB",
            "enlace" => "https://www.fivb.com",
            "jugadores" => "2 por equipo, sin cambios",
            "duracion" => "Al mejor de 3 sets, sin tiempo fijo",
            "reglas" => [
                "Los dos primeros sets son a 21 puntos con 2 de diferencia; el tercero es a 15.",
                "Cada equipo tiene como máximo 3 toques para pasar la pelota.",
                "Se cambia de lado cada 7 puntos (cada 5 en el tercer set).",
            ],
        ],
        "rugby" => [
            "federacion" => "World Rugby",
            "enlace" => "https://www.world.rugby",
            "jugadores" => "15 por equipo",
            "duracion" => "2 tiempos de 40 minutos",
            "reglas" => [
                "Try: 5 puntos. Conversión: 2. Penal o drop: 3.",
                "La pelota solo se puede pasar con la mano hacia atrás o hacia el costado.",
                "Solo se puede tacklear al jugador que lleva la pelota, y por debajo de los hombros.",
            ],
        ],
        "rugby7" => [
            "federacion" => "World Rugby",
            "enlace" => "https://www.world.rugby",
            "jugadores" => "7 por equipo",
            "duracion" => "2 tiempos de 7 minutos (10 en las finales)",
            "reglas" => [
                "Se juega en la misma cancha que el rugby de 15 y con la misma puntuación.",
                "Las conversiones se patean de drop.",
                "Las reglas de pase y tackle son las mismas que en el rugby de 15.",
            ],
        ],
        "hockey" => [
            "federacion" => "FIH",
            "enlace" => "https://www.fih.hockey",
            "jugadores" => "11 por equipo (con arquero)",
            "duracion" => "4 cuartos de 15 minutos",
            "reglas" => [
                "Solo vale el gol si la pelota se tocó dentro del área rival.",
                "La pelota se juega solo con el lado plano del palo.",
                "Los cambios son ilimitados.",
                "Tarjeta verde: 2 minutos afuera; amarilla: 5 minutos o más; roja: expulsión.",
            ],
        ],
        "hockeyhielo" => [
            "federacion" => "IIHF",
            "enlace" => "https://www.iihf.com",
            "jugadores" => "6 por equipo en el hielo (con arquero)",
            "duracion" => "3 períodos de 20 minutos",
            "reglas" => [
                "Una penalidad menor deja al jugador 2 minutos afuera y su equipo juega con uno menos.",
                "Está prohibido despejar el disco desde campo propio hasta el fondo rival (icing).",
                "Si terminan empatados se juega un alargue y, si sigue el empate, penales.",
            ],
        ],
        "handball" => [
            "federacion" => "IHF",
            "enlace" => "https://www.ihf.info",
            "jugadores" => "7 por equipo (con arquero)",
            "duracion" => "2 tiempos de 30 minutos",
            "reglas" => [
                "Con la pelota en la mano se pueden dar hasta 3 pasos y tenerla hasta 3 segundos.",
                "Solo el arquero puede pisar el área de 6 metros.",
                "Las faltas graves se sancionan con 2 minutos de exclusión.",
            ],
        ],
        "beisbol" => [
            "federacion" => "WBSC",
            "enlace" => "https://www.wbsc.org",
            "jugadores" => "9 por equipo",
            "duracion" => "9 entradas",
            "reglas" => [
                "Cada equipo batea hasta que le hacen 3 outs; después le toca defender.",
                "3 strikes es out y 4 bolas mandan al bateador a primera base.",
                "Se anota una carrera cuando el jugador recorre las cuatro bases.",
                "No hay empates: se juegan entradas extra hasta que gane uno.",
            ],
        ],
        "softbol" => [
            "federacion" => "WBSC",
            "enlace" => "https://www.wbsc.org",
            "jugadores" => "9 por equipo",
            "duracion" => "7 entradas",
            "reglas" => [
                "El lanzador tira la pelota por debajo del hombro.",
                "La cancha y las distancias entre bases son más cortas que en el béisbol.",
                "Outs, strikes, bolas y carreras se cuentan igual que en el béisbol.",
                "No hay empates: se juegan entradas extra hasta que gane uno.",
            ],
        ],
        "waterpolo" => [
            "federacion" => "World Aquatics",
            "enlace" => "https://www.worldaquatics.com",
            "jugadores" => "7 por equipo en el agua (con arquero)",
            "duracion" => "4 cuartos de 8 minutos",
            "reglas" => [
                "Solo el arquero puede tocar la pelota con las dos manos.",
                "Hay un tiempo límite para tirar al arco en cada posesión.",
                "Una falta grave deja al jugador 20 segundos afuera; con 3 exclusiones queda fuera del partido.",
                "Si terminan empatados, se definen por penales.",
            ],
        ],
        "americano" => [
            "federacion" => "NFL",
            "enlace" => "https://operations.nfl.com/the-rules/",
            "jugadores" => "11 por equipo en cancha",
            "duracion" => "4 cuartos de 15 minutos",
            "reglas" => [
                "El ataque tiene 4 intentos (downs) para avanzar 10 yardas.",
                "Touchdown: 6 puntos. Después se patea por 1 punto extra o se intenta una jugada por 2.",
                "Gol de campo: 3 puntos. Safety: 2 puntos.",
            ],
        ],
    ];
}

// junta el texto con el nombre del deporte y como se arma la tabla en ARENIX
function listar_reglamentos() {
    $textos = reglamentos_texto();
    $reglamentos = [];

    foreach (deportes_clubes() as $id => $nombre) {
        if (!isset($textos[$id])) {
            continue;
        }
        $reglamentos[] = $textos[$id] + [
            "id" => $id,
            "nombre" => $nombre,
            "tabla" => reglas_tabla($id)["ayuda"],
        ];
    }
    return $reglamentos;
}
