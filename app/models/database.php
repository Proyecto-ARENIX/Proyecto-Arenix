<?php
// en docker los datos vienen del .env, en xampp se usan los de siempre
$host = getenv("DB_HOST") ?: "127.0.0.1";
$usuario_bd = getenv("DB_USER") ?: "root";
$clave_bd = getenv("DB_PASS") ?: "";
$nombre_bd = getenv("DB_NAME") ?: "arenix";

try {
    $conexion = new PDO("mysql:host=$host;charset=utf8mb4", $usuario_bd, $clave_bd);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conexion->exec("CREATE DATABASE IF NOT EXISTS $nombre_bd");
    $conexion->exec("USE $nombre_bd");

    $conexion->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id       INT AUTO_INCREMENT PRIMARY KEY,
            nombre   VARCHAR(80)  NOT NULL,
            correo     VARCHAR(190) NOT NULL UNIQUE,
            contrasena VARCHAR(255) NOT NULL,
            foto     VARCHAR(255) NULL,
            rol      VARCHAR(20) NOT NULL DEFAULT 'publico',
            pais_telefono CHAR(2) NULL,
            prefijo_telefono VARCHAR(8) NULL,
            telefono VARCHAR(20) NULL
        )
    ");

    $conexion->exec("
        CREATE TABLE IF NOT EXISTS clubes (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            nombre     VARCHAR(60)  NOT NULL UNIQUE,
            logo       VARCHAR(255) NOT NULL,
            deporte    VARCHAR(20)  NOT NULL,
            torneo     VARCHAR(60)  NOT NULL,
            ciudad     VARCHAR(60)  NOT NULL,
            entrenador VARCHAR(60)  NOT NULL,
            formacion  VARCHAR(10)  NOT NULL,
            plan       VARCHAR(10)  NOT NULL DEFAULT 'basico',
            habilidades TEXT NOT NULL,
            jugadores  TEXT NOT NULL,
            creado_por INT NULL,
            creado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // clientes que pueden editar cada club (club_clave es club-5 o tigres-fc)
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS club_editores (
            club_clave  VARCHAR(40) NOT NULL,
            usuario_id  INT NOT NULL,
            asignado_por INT NULL,
            asignado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (club_clave, usuario_id)
        )
    ");

    // participantes (cuentas con rol participante) que pertenecen a cada club
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS club_participantes (
            club_clave   VARCHAR(40) NOT NULL,
            usuario_id   INT NOT NULL,
            agregado_por INT NULL,
            agregado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (club_clave, usuario_id)
        )
    ");

    // avisos disciplinarios: amarillas y expulsiones de los jugadores de cada club
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS club_sanciones (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            club_clave VARCHAR(40) NOT NULL,
            jugador    VARCHAR(60) NOT NULL,
            tipo       VARCHAR(20) NOT NULL,
            partido    VARCHAR(80) NOT NULL,
            fecha      DATE NOT NULL,
            detalle    VARCHAR(120) NULL,
            creado_por INT NULL,
            creado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (club_clave)
        )
    ");

    // cuantos partidos dura cada aviso y desde que partido se cuenta
    foreach ([
        "partidos_duracion" => "INT NOT NULL DEFAULT 1",
        "partido_inicio"    => "INT NOT NULL DEFAULT 0",
    ] as $nombre_columna => $tipo_columna) {
        $columna = $conexion->query("SHOW COLUMNS FROM club_sanciones LIKE '$nombre_columna'")->fetch(PDO::FETCH_ASSOC);
        if (!$columna) {
            $conexion->exec("ALTER TABLE club_sanciones ADD COLUMN $nombre_columna $tipo_columna");
        }
    }

    // partidos jugados por cada club (los registra un organizador; sirven para vencer los avisos)
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS club_partidos (
            club_clave VARCHAR(40) NOT NULL PRIMARY KEY,
            jugados    INT NOT NULL DEFAULT 0
        )
    ");

    // datos sueltos de la plataforma (por ejemplo: si ya se crearon las ligas de ejemplo)
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS configuracion (
            clave VARCHAR(60) NOT NULL PRIMARY KEY,
            valor VARCHAR(255) NULL
        )
    ");

    // planes contratados (de la tarjeta solo se guarda la marca y los ultimos 4)
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS contrataciones (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            plan            VARCHAR(20) NOT NULL,
            equipos_extra   INT NOT NULL DEFAULT 0,
            jugadores_extra INT NOT NULL DEFAULT 0,
            total_mensual   INT NOT NULL,
            nombre          VARCHAR(80) NOT NULL,
            correo          VARCHAR(190) NOT NULL,
            telefono        VARCHAR(30) NOT NULL,
            organizacion    VARCHAR(80) NOT NULL,
            deporte         VARCHAR(20) NOT NULL,
            ciudad          VARCHAR(60) NOT NULL,
            tarjeta_marca   VARCHAR(20) NOT NULL,
            tarjeta_ultimos CHAR(4) NOT NULL,
            estado          VARCHAR(20) NOT NULL DEFAULT 'pagado_prueba',
            usuario_id      INT NULL,
            creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // liga que se creo para cada contratacion (null = pendiente)
    if (!$conexion->query("SHOW COLUMNS FROM contrataciones LIKE 'liga_id'")->fetch(PDO::FETCH_ASSOC)) {
        $conexion->exec("ALTER TABLE contrataciones ADD COLUMN liga_id INT NULL");
    }

    // noticias que publican los admin y organizadores
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS noticias (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            categoria  VARCHAR(30)  NOT NULL,
            deporte    VARCHAR(30)  NOT NULL,
            titulo     VARCHAR(90)  NOT NULL,
            resumen    VARCHAR(300) NOT NULL,
            imagen     VARCHAR(255) NULL,
            creado_por INT NULL,
            creado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // comunicados del carrusel de noticias (tipo: partido, salud, disciplina o aviso)
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS comunicados (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            tipo        VARCHAR(20)  NOT NULL,
            contexto    VARCHAR(40)  NOT NULL,
            titulo      VARCHAR(90)  NOT NULL,
            texto       VARCHAR(300) NOT NULL,
            creado_por  INT NULL,
            editado_por INT NULL,
            creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // eventos de la pagina de eventos (torneos, clinicas, inscripciones). los cargan admin y organizador
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS eventos (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            nombre     VARCHAR(80)  NOT NULL,
            deporte    VARCHAR(20)  NOT NULL,
            fecha      DATE         NOT NULL,
            hora       TIME         NULL,
            modalidad  VARCHAR(60)  NULL,
            sede       VARCHAR(80)  NULL,
            cupos      VARCHAR(40)  NULL,
            estado     VARCHAR(20)  NOT NULL DEFAULT 'abierto',
            creado_por INT NULL,
            creado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // equipos de ejemplo (equipos.php) que se eliminaron: no se vuelven a mostrar
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS equipos_eliminados (
            club_clave    VARCHAR(40) NOT NULL PRIMARY KEY,
            eliminado_por INT NULL,
            eliminado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // ligas
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS ligas (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            nombre          VARCHAR(60) NOT NULL UNIQUE,
            deporte         VARCHAR(20) NOT NULL,
            plan            VARCHAR(20) NOT NULL DEFAULT 'basico',
            equipos_extra   INT NOT NULL DEFAULT 0,
            jugadores_extra INT NOT NULL DEFAULT 0,
            creado_por      INT NULL,
            creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // logo y foto de portada de cada liga
    foreach (["logo", "portada"] as $columna_imagen) {
        $existe_columna = $conexion->query("SHOW COLUMNS FROM ligas LIKE '$columna_imagen'")->fetch(PDO::FETCH_ASSOC);
        if (!$existe_columna) {
            $conexion->exec("ALTER TABLE ligas ADD COLUMN $columna_imagen VARCHAR(255) NULL");
        }
    }

    // a que liga pertenece cada equipo (un equipo esta en una sola liga)
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS liga_equipos (
            club_clave VARCHAR(40) NOT NULL PRIMARY KEY,
            liga_id    INT NOT NULL,
            INDEX (liga_id)
        )
    ");

    // clientes que administran cada liga (la cantidad la limita el plan)
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS liga_administradores (
            liga_id      INT NOT NULL,
            usuario_id   INT NOT NULL,
            asignado_por INT NULL,
            PRIMARY KEY (liga_id, usuario_id)
        )
    ");

    // torneos de cada liga (la cantidad de activos la limita el plan)
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS torneos (
            id        INT AUTO_INCREMENT PRIMARY KEY,
            liga_id   INT NOT NULL,
            nombre    VARCHAR(60) NOT NULL,
            estado    VARCHAR(12) NOT NULL DEFAULT 'activo',
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (liga_id)
        )
    ");

    // calendario y resultados. contado = ya se sumo a los partidos jugados de cada club (vence avisos)
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS partidos (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            torneo_id       INT NOT NULL,
            local           VARCHAR(40) NOT NULL,
            visitante       VARCHAR(40) NOT NULL,
            fecha           DATE NOT NULL,
            hora            TIME NULL,
            lugar           VARCHAR(80) NULL,
            goles_local     INT NULL,
            goles_visitante INT NULL,
            estado          VARCHAR(12) NOT NULL DEFAULT 'programado',
            contado         TINYINT NOT NULL DEFAULT 0,
            creado_por      INT NULL,
            INDEX (torneo_id)
        )
    ");

    // como termino el partido: regular o extra (alargue o penales, para hockey sobre hielo y waterpolo)
    if (!$conexion->query("SHOW COLUMNS FROM partidos LIKE 'definicion'")->fetch(PDO::FETCH_ASSOC)) {
        $conexion->exec("ALTER TABLE partidos ADD COLUMN definicion VARCHAR(10) NOT NULL DEFAULT 'regular'");
    }

    // clave_base: si es un equipo de ejemplo editado
    foreach ([
        "clave_base"  => "VARCHAR(40) NULL UNIQUE",
        "editado_por" => "INT NULL",
    ] as $nombre_columna => $tipo_columna) {
        $columna = $conexion->query("SHOW COLUMNS FROM clubes LIKE '$nombre_columna'")->fetch(PDO::FETCH_ASSOC);
        if (!$columna) {
            $conexion->exec("ALTER TABLE clubes ADD COLUMN $nombre_columna $tipo_columna");
        }
    }

    $columna_foto =$conexion->query("SHOW COLUMNS FROM usuarios LIKE 'foto'")->fetch(PDO::FETCH_ASSOC);
    if (!$columna_foto) {
        $conexion->exec("ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) NULL");
    }

    // columnas que antes estaban en ingles (se renombran sin perder los datos)
    foreach ([
        ["usuarios", "email", "correo VARCHAR(190) NOT NULL"],
        ["usuarios", "password", "contrasena VARCHAR(255) NOT NULL"],
        ["contrataciones", "email", "correo VARCHAR(190) NOT NULL"],
        ["clubes", "skills", "habilidades TEXT NOT NULL"],
    ] as [$tabla, $vieja, $nueva]) {
        if ($conexion->query("SHOW COLUMNS FROM $tabla LIKE '$vieja'")->fetch()) {
            $conexion->exec("ALTER TABLE $tabla CHANGE $vieja $nueva");
        }
    }

    // las cuentas nuevas son "publico" (visitante); "participante" es solo para quien forma parte de un club
    $columna_rol = $conexion->query("SHOW COLUMNS FROM usuarios LIKE 'rol'")->fetch(PDO::FETCH_ASSOC);
    if (!$columna_rol) {
        $conexion->exec("ALTER TABLE usuarios ADD COLUMN rol VARCHAR(20) NOT NULL DEFAULT 'publico'");
    } elseif ($columna_rol["Default"] !== "publico") {
        $conexion->exec("ALTER TABLE usuarios ALTER COLUMN rol SET DEFAULT 'publico'");
    }
    $conexion->exec("UPDATE usuarios SET rol = 'publico' WHERE rol IS NULL OR rol NOT IN ('admin', 'organizador', 'participante', 'cliente', 'publico')");

    foreach ([
        "pais_telefono" => "CHAR(2) NULL",
        "prefijo_telefono" => "VARCHAR(8) NULL",
        "telefono" => "VARCHAR(20) NULL",
    ] as $nombre_columna => $tipo_columna) {
        $columna = $conexion->query("SHOW COLUMNS FROM usuarios LIKE '$nombre_columna'")->fetch(PDO::FETCH_ASSOC);
        if (!$columna) {
            $conexion->exec("ALTER TABLE usuarios ADD COLUMN $nombre_columna $tipo_columna");
        }
    }
} catch (PDOException $error) {
    exit("Error al conectar con la base de datos. ¿Encendiste MySQL en XAMPP?");
}
