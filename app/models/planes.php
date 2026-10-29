<?php
// planes (precio en dolares por mes)

return [
    "planes" => [
        [
            "id"          => "basico",
            "nombre"      => "Plan Básico",
            "precio"      => 59,
            "color"       => "verde",
            "resumen"     => "Para ligas y torneos que empiezan.",
            "limites"     => [
                "Equipos"            => "Hasta 8",
                "Jugadores"          => "Hasta 224 + 10 plazas adicionales",
                "Torneos activos"    => "1",
                "Administradores"    => "1",
            ],
            "incluye"     => [
                "Registro de equipos y jugadores",
                "Calendario de partidos",
                "Registro de resultados",
                "Tabla de posiciones",
                "Estadísticas básicas",
            ],
        ],
        [
            "id"          => "profesional",
            "nombre"      => "Plan Profesional",
            "precio"      => 99,
            "color"       => "azul",
            "resumen"     => "Para organizaciones con varios torneos a la vez.",
            "limites"     => [
                "Equipos"            => "Hasta 20",
                "Jugadores"          => "Hasta 560 + 10 plazas adicionales",
                "Torneos activos"    => "Hasta 3",
                "Administradores"    => "Hasta 3",
            ],
            "incluye"     => [
                "Perfiles individuales de jugadores",
                "Estadísticas básicas",
                "Tablón de noticias",
                "Gestión de múltiples torneos",
                "Personalización básica",
            ],
        ],
        [
            "id"          => "enterprise",
            "nombre"      => "Plan Enterprise",
            "precio"      => 250,
            "color"       => "violeta",
            "resumen"     => "Para clubes y ligas que quieren el análisis completo.",
            "limites"     => [
                "Equipos"            => "Hasta 50",
                "Jugadores"          => "Hasta 1.400 + 10 plazas adicionales",
                "Torneos activos"    => "Hasta 8",
                "Administradores"    => "Hasta 10",
            ],
            "incluye"     => [
                "Estadísticas avanzadas completas",
                "Perfiles completos de jugadores",
                "Datos biométricos",
                "Reportes avanzados",
                "Veo Cam 3 proporcionada por Arenix mediante contrato",
                "Integración de las grabaciones de partidos con Arenix",
                "Almacenamiento y organización de grabaciones",
            ],
        ],
    ],

    "extras" => [
        ["nombre" => "Equipo adicional", "precio" => "USD 10/mes"],
        ["nombre" => "Jugador adicional", "precio" => "USD 2/mes"],
        ["nombre" => "10 jugadores adicionales", "precio" => "Gratis en cada plan"],
    ],
];
