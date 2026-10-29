<?php
// noticias de ejemplo + las que se publican desde la pagina

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/clubes.php";

// las de ejemplo se guardan en la base una sola vez, si se borran no vuelven
function crear_noticias_de_ejemplo() {
    global $conexion;

    if ($conexion->query("SELECT valor FROM configuracion WHERE clave = 'noticias_demo_creadas'")->fetchColumn()) {
        return;
    }
    $conexion->exec("INSERT IGNORE INTO configuracion (clave, valor) VALUES ('noticias_demo_creadas', '1')");

    $ejemplos = [
        ["Competencia", "Fútbol", "Tigres FC se afirma en la cima", "El equipo sostuvo su racha y llega como líder a la próxima fecha del torneo.", "/public/assets/images/tigres-fc.png", "2026-10-10 12:00:00"],
        ["Crónica", "Fútbol", "Halcones se quedó con un final cerrado", "Un gol en el segundo tiempo resolvió un partido parejo frente a Pumas.", "/public/assets/images/halcones.png", "2026-10-10 11:00:00"],
        ["Parte médico", "Fútbol", "Guerreros espera la recuperación de su delantero", "El jugador estará fuera de las canchas durante tres semanas y será evaluado antes de volver.", "/public/assets/images/pumas.png", "2026-10-09 12:00:00"],
        ["Análisis", "Liga", "La última fecha promete cambios en la tabla", "Tres equipos llegan con posibilidades de quedarse con los primeros puestos.", "/public/assets/images/panteras.png", "2026-10-08 12:00:00"],
    ];
    $insertar = $conexion->prepare("INSERT INTO noticias (categoria, deporte, titulo, resumen, imagen, creado_en) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($ejemplos as $ejemplo) {
        $insertar->execute($ejemplo);
    }
}

function tipos_comunicado() {
    return [
        "partido" => "Comentario de partido",
        "salud" => "Parte médico",
        "disciplina" => "Disciplina",
        "aviso" => "Anuncio",
    ];
}

// los de ejemplo se guardan en la base una sola vez, despues se pueden editar
function crear_comunicados_de_ejemplo() {
    global $conexion;

    if ($conexion->query("SELECT valor FROM configuracion WHERE clave = 'comunicados_demo_creados'")->fetchColumn()) {
        return;
    }
    $conexion->exec("INSERT IGNORE INTO configuracion (clave, valor) VALUES ('comunicados_demo_creados', '1')");

    $ejemplos = [
        ["partido", "Liga Apertura", "Club Norte 2–1 Deportivo Litoral", "Club Norte sostuvo la presión en el cierre y aseguró los tres puntos en casa.", "2026-10-10 12:00:00"],
        ["salud", "Guerreros", "Baja estimada: tres semanas", "El delantero continúa su recuperación. Su regreso dependerá de una nueva evaluación médica.", "2026-10-09 12:00:00"],
        ["disciplina", "Boletín oficial", "Marcos Vera recibe una fecha de suspensión", "La sanción corresponde a la expulsión registrada en el encuentro de la última jornada.", "2026-10-09 11:00:00"],
        ["aviso", "Inscripciones", "Abierta la inscripción a la Copa Ciudad", "Los equipos pueden consultar fechas y sedes en la sección de eventos.", "2026-10-08 12:00:00"],
    ];
    $insertar = $conexion->prepare("INSERT INTO comunicados (tipo, contexto, titulo, texto, creado_en) VALUES (?, ?, ?, ?, ?)");
    foreach ($ejemplos as $ejemplo) {
        $insertar->execute($ejemplo);
    }
}

function listar_comunicados() {
    global $conexion;

    crear_comunicados_de_ejemplo();
    $tipos = tipos_comunicado();
    $filas = $conexion->query("SELECT * FROM comunicados ORDER BY creado_en DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $comunicados = [];

    foreach ($filas as $fila) {
        $comunicados[] = [
            "id" => (int) $fila["id"],
            "tipo" => $fila["tipo"],
            "etiqueta" => $tipos[$fila["tipo"]] ?? "Anuncio",
            "fecha" => fecha_noticia($fila["creado_en"], false) . " · " . mb_strtoupper($fila["contexto"], "UTF-8"),
            "contexto" => $fila["contexto"],
            "titulo" => $fila["titulo"],
            "texto" => $fila["texto"],
        ];
    }

    return $comunicados;
}

// sin id lo crea, con id lo edita
function guardar_comunicado($datos, $usuario_id) {
    global $conexion;

    $id = filter_var($datos["id"] ?? "", FILTER_VALIDATE_INT);
    $tipo = $datos["tipo"] ?? "";
    $contexto = trim($datos["contexto"] ?? "");
    $titulo = trim($datos["titulo"] ?? "");
    $texto = trim($datos["texto"] ?? "");

    if (!is_string($tipo) || !isset(tipos_comunicado()[$tipo])) {
        return "Elegí un tipo de comunicado.";
    }
    if (!texto_valido($contexto, 2, 40)) {
        return "El contexto tiene que tener entre 2 y 40 caracteres.";
    }
    if (!texto_valido($titulo, 5, 90)) {
        return "El título tiene que tener entre 5 y 90 caracteres.";
    }
    if (!texto_valido($texto, 10, 300)) {
        return "El texto tiene que tener entre 10 y 300 caracteres.";
    }

    if ($id) {
        $editar = $conexion->prepare("UPDATE comunicados SET tipo = ?, contexto = ?, titulo = ?, texto = ?, editado_por = ? WHERE id = ?");
        $editar->execute([$tipo, $contexto, $titulo, $texto, $usuario_id, $id]);

        $existe = $conexion->prepare("SELECT id FROM comunicados WHERE id = ?");
        $existe->execute([$id]);
        return $existe->fetch() ? "ok" : "No se encontró ese comunicado.";
    }

    $insertar = $conexion->prepare("INSERT INTO comunicados (tipo, contexto, titulo, texto, creado_por) VALUES (?, ?, ?, ?, ?)");
    $insertar->execute([$tipo, $contexto, $titulo, $texto, $usuario_id]);

    return "ok";
}

// "2026-10-20 18:30:00" -> "20 OCT 2026" (o "20 OCT" sin el año)
function fecha_noticia($fecha, $con_anio = true) {
    $meses = ["ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC"];
    $marca = strtotime($fecha);
    $texto = date("d", $marca) . " " . $meses[(int) date("n", $marca) - 1];
    return $con_anio ? $texto . " " . date("Y", $marca) : $texto;
}

// las mas nuevas arriba
function listar_noticias() {
    global $conexion;

    crear_noticias_de_ejemplo();
    $filas = $conexion->query("SELECT * FROM noticias ORDER BY creado_en DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $noticias = [];

    foreach ($filas as $fila) {
        $noticias[] = [
            "id" => (int) $fila["id"],
            "categoria" => $fila["categoria"],
            "deporte" => $fila["deporte"],
            "fecha" => fecha_noticia($fila["creado_en"]),
            "titulo" => $fila["titulo"],
            "resumen" => $fila["resumen"],
            "imagen" => $fila["imagen"] ?: "/public/assets/images/logo a.png",
            "alt" => $fila["titulo"],
        ];
    }

    return $noticias;
}

function eliminar_noticia($id) {
    global $conexion;

    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        return "La noticia no es válida.";
    }

    $consulta = $conexion->prepare("SELECT imagen FROM noticias WHERE id = ?");
    $consulta->execute([$id]);
    $noticia = $consulta->fetch(PDO::FETCH_ASSOC);
    if (!$noticia) {
        return "No se encontró esa noticia.";
    }

    $eliminar = $conexion->prepare("DELETE FROM noticias WHERE id = ?");
    $eliminar->execute([$id]);
    // las imagenes de assets no se tocan, solo las subidas
    borrar_imagen_subida($noticia["imagen"]);

    return "ok";
}

function eliminar_comunicado($id) {
    global $conexion;

    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        return "El comunicado no es válido.";
    }

    $eliminar = $conexion->prepare("DELETE FROM comunicados WHERE id = ?");
    $eliminar->execute([$id]);

    return $eliminar->rowCount() > 0 ? "ok" : "No se encontró ese comunicado.";
}

function publicar_noticia($datos, $archivo, $usuario_id) {
    global $conexion;

    $categoria = trim($datos["categoria"] ?? "");
    $deporte = trim($datos["deporte"] ?? "");
    $titulo = trim($datos["titulo"] ?? "");
    $resumen = trim($datos["resumen"] ?? "");

    if (!texto_valido($categoria, 2, 30)) {
        return "La categoría tiene que tener entre 2 y 30 caracteres.";
    }
    if (!texto_valido($deporte, 2, 30)) {
        return "El deporte tiene que tener entre 2 y 30 caracteres.";
    }
    if (!texto_valido($titulo, 5, 90)) {
        return "El título tiene que tener entre 5 y 90 caracteres.";
    }
    if (!texto_valido($resumen, 10, 300)) {
        return "El resumen tiene que tener entre 10 y 300 caracteres.";
    }

    // la imagen es opcional
    $imagen = null;
    if (is_array($archivo) && ($archivo["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $subida = subir_imagen_club($archivo, "noticias", "La imagen");
        if (isset($subida["error"])) {
            return $subida["error"];
        }
        $imagen = $subida["ruta"];
    }

    $insertar = $conexion->prepare(
        "INSERT INTO noticias (categoria, deporte, titulo, resumen, imagen, creado_por) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $insertar->execute([$categoria, $deporte, $titulo, $resumen, $imagen, $usuario_id]);

    return "ok";
}
