<?php
require_once __DIR__ . "/../models/auth.php";
require_once __DIR__ . "/../models/equipos.php";
require_once __DIR__ . "/../models/contrataciones.php";

// permisos:
// crear ligas y elegir sus admins -> organizador y admin
// dentro de una liga -> organizador, admin y los clientes que la administran
// editar un club -> lo mismo + los clientes asignados al club
// tarjetas y sanciones -> solo organizador y admin
// habilidades del equipo -> solo organizador y admin
$usuario = usuario_actual();
$puede_gestionar_ligas = usuario_puede("gestionar_ligas");
$puede_agregar_clubes = usuario_puede("gestionar_clubes");
$puede_sancionar = usuario_puede("registrar_sanciones");
$puede_editar_habilidades = usuario_puede("editar_habilidades");

$ligas = listar_ligas();
$ligas_que_administro = $usuario && $usuario["rol"] === "cliente" ? ligas_administradas($usuario["id"]) : [];

$clubes_editables = [];
if ($puede_agregar_clubes) {
    $clubes_editables = array_column(listar_equipos(), "id");
} elseif ($usuario && usuario_puede("editar_clubes")) {
    $clubes_editables = clubes_asignados($usuario["id"]);
    foreach ($ligas_que_administro as $id_liga) {
        $clubes_editables = array_merge($clubes_editables, $ligas[$id_liga]["clubes"] ?? []);
    }
    $clubes_editables = array_values(array_unique($clubes_editables));
}
$puede_editar_clubes = count($clubes_editables) > 0;

// equipos que el usuario puede eliminar: todos (organizador/admin) o los de las ligas que administra
$clubes_eliminables = [];
if ($puede_agregar_clubes) {
    $clubes_eliminables = array_column(listar_equipos(), "id");
} else {
    foreach ($ligas_que_administro as $id_liga) {
        $clubes_eliminables = array_merge($clubes_eliminables, $ligas[$id_liga]["clubes"] ?? []);
    }
}
$puede_algo = $puede_gestionar_ligas || $puede_editar_clubes || $puede_sancionar || count($ligas_que_administro) > 0;

if ($puede_algo && empty($_SESSION["csrf_clubes"])) {
    $_SESSION["csrf_clubes"] = bin2hex(random_bytes(32));
}

// direccion de la liga (con pestaña y torneo) para volver despues de cada accion
$url_liga = function ($liga_id, $extra = []) {
    return "/index.php?" . http_build_query(array_merge(["pagina" => "enfrentamientos", "liga" => $liga_id], $extra));
};

$error_club = "";
$club_guardado = $_GET["club_guardado"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // si los archivos superan post_max_size, PHP descarta todo el POST
    if (empty($_POST) && (int) ($_SERVER["CONTENT_LENGTH"] ?? 0) > 0) {
        $error_club = "Las imágenes que subiste pesan demasiado en total. Prueba con menos fotos o más livianas.";
        // por si el formulario era el de una liga (crear/editar con logo)
        $_SESSION["mensaje_liga"] = $error_club;
        $_SESSION["mensaje_liga_ok"] = false;
    } else {
        $accion = $_POST["accion"] ?? "";
        $club_id = is_string($_POST["club_id"] ?? null) ? $_POST["club_id"] : "";
        $liga_post = filter_var($_POST["liga_id"] ?? null, FILTER_VALIDATE_INT) ?: null;
        $token = $_POST["csrf_token"] ?? "";
        $token_valido = $puede_algo && is_string($token) && hash_equals($_SESSION["csrf_clubes"] ?? "", $token);
        $vencida = "La solicitud venció. Actualiza la página e inténtalo de nuevo.";

        // acciones de liga
        $acciones_liga = [
            "crear_liga"        => $puede_gestionar_ligas,
            "editar_liga"       => $puede_gestionar_ligas,
            "eliminar_liga"     => $puede_gestionar_ligas,
            "mover_equipo"      => $puede_gestionar_ligas,
            "eliminar_club"     => puede_eliminar_club($club_id),
            "imagenes_liga"     => $liga_post !== null && puede_administrar_liga($liga_post),
            "agregar_admin"     => $puede_gestionar_ligas,
            "quitar_admin"      => $puede_gestionar_ligas,
            "crear_torneo"      => $liga_post !== null && puede_administrar_liga($liga_post),
            "estado_torneo"     => $liga_post !== null && puede_administrar_liga($liga_post),
            "eliminar_torneo"   => $liga_post !== null && puede_administrar_liga($liga_post),
            "crear_partido"     => $liga_post !== null && puede_administrar_liga($liga_post),
            "cargar_resultado"  => $liga_post !== null && puede_administrar_liga($liga_post),
            "borrar_partido"    => $liga_post !== null && puede_administrar_liga($liga_post),
        ];

        if (isset($acciones_liga[$accion])) {
            if (!$acciones_liga[$accion]) {
                http_response_code(403);
                exit("No tienes permiso para hacer esto en esta liga.");
            }

            $pestana = in_array($accion, ["editar_liga", "imagenes_liga", "agregar_admin", "quitar_admin"], true) ? "admin" : "torneos";
            $torneo_volver = filter_var($_POST["torneo_id"] ?? null, FILTER_VALIDATE_INT) ?: null;
            $mensaje = $vencida;
            $ok = false;
            $liga_volver = $liga_post;

            if ($token_valido) {
                switch ($accion) {
                    case "crear_liga":
                    case "editar_liga":
                        $resultado = guardar_liga($_POST, $usuario["id"], $accion === "editar_liga" ? $liga_post : null, [
                            "logo"    => $_FILES["logo_liga"] ?? null,
                            "portada" => $_FILES["portada_liga"] ?? null,
                        ]);
                        $ok = is_array($resultado);
                        $mensaje = $ok ? ($accion === "crear_liga" ? "Liga creada." : "Cambios de la liga guardados.") : $resultado;
                        if ($ok) {
                            $liga_volver = $resultado["ok"];
                            $pestana = $accion === "crear_liga" ? "equipos" : "admin";
                            if (isset($resultado["aviso"])) {
                                $mensaje .= " Pero no se pudo guardar una imagen: " . $resultado["aviso"];
                            }
                            // si se creo desde planes contratados, el cliente pasa a administrarla
                            $contratacion_id = filter_var($_POST["contratacion_id"] ?? null, FILTER_VALIDATE_INT);
                            if ($accion === "crear_liga" && $contratacion_id) {
                                $mensaje .= vincular_contratacion($contratacion_id, $resultado["ok"], $usuario["id"]);
                            }
                        }
                        break;
                    case "eliminar_liga":
                        $nombre_eliminada = $ligas[$liga_post]["nombre"] ?? "";
                        $resultado = eliminar_liga($liga_post, $_POST["confirmacion"] ?? "");
                        $ok = is_array($resultado);
                        if ($ok) {
                            // se vuelve a la lista de ligas
                            $liga_volver = null;
                            $mensaje = "Se eliminó la liga \"$nombre_eliminada\"."
                                . ($resultado["ok"] > 0 ? " Sus " . $resultado["ok"] . " equipo(s) quedaron en \"Equipos sin liga\" para pasarlos a otra." : "");
                        } else {
                            $mensaje = $resultado;
                        }
                        break;
                    case "eliminar_club":
                        $nombre_club = buscar_equipo($club_id)["nombre"] ?? "";
                        $liga_volver = liga_de_club($club_id);
                        $pestana = "equipos";
                        $resultado = eliminar_club($club_id, $_POST["confirmacion"] ?? "", $usuario["id"]);
                        $ok = is_array($resultado);
                        $mensaje = $ok ? "Se eliminó el equipo \"$nombre_club\"." : $resultado;
                        break;
                    case "mover_equipo":
                        $resultado = mover_club_a_liga($club_id, $liga_post);
                        $ok = $resultado === "ok";
                        $mensaje = $ok ? "El equipo pasó a " . ($ligas[$liga_post]["nombre"] ?? "la liga") . "." : $resultado;
                        $liga_volver = null;
                        break;
                    case "imagenes_liga":
                        $resultado = guardar_imagenes_liga($liga_post, [
                            "logo"    => $_FILES["logo_liga"] ?? null,
                            "portada" => $_FILES["portada_liga"] ?? null,
                        ], $_POST);
                        $ok = $resultado === "ok";
                        $mensaje = $ok ? "Imágenes de la liga guardadas." : $resultado;
                        break;
                    case "agregar_admin":
                        $resultado = agregar_admin_liga($liga_post, $_POST["correo"] ?? "", $usuario["id"]);
                        $ok = $resultado === "ok";
                        $mensaje = $ok ? "Administrador agregado a la liga." : $resultado;
                        break;
                    case "quitar_admin":
                        $resultado = quitar_admin_liga($liga_post, $_POST["usuario_id"] ?? 0);
                        $ok = $resultado === "ok";
                        $mensaje = $ok ? "Administrador quitado de la liga." : $resultado;
                        break;
                    case "crear_torneo":
                        $resultado = crear_torneo($liga_post, $_POST["nombre"] ?? "");
                        $ok = is_array($resultado);
                        $mensaje = $ok ? "Torneo creado." : $resultado;
                        $torneo_volver = $ok ? $resultado["ok"] : $torneo_volver;
                        break;
                    case "estado_torneo":
                        $nuevo_estado = ($_POST["estado"] ?? "") === "activo" ? "activo" : "finalizado";
                        $resultado = cambiar_estado_torneo($liga_post, $torneo_volver, $nuevo_estado);
                        $ok = $resultado === "ok";
                        $mensaje = $ok ? ($nuevo_estado === "activo" ? "Torneo reabierto." : "Torneo finalizado.") : $resultado;
                        break;
                    case "eliminar_torneo":
                        $nombre_torneo = buscar_torneo($liga_post, $torneo_volver)["nombre"] ?? "";
                        $resultado = eliminar_torneo($liga_post, $torneo_volver);
                        $ok = $resultado === "ok";
                        $mensaje = $ok ? "Se eliminó el torneo \"$nombre_torneo\"." : $resultado;
                        // se vuelve al primer torneo de la liga
                        $torneo_volver = $ok ? null : $torneo_volver;
                        break;
                    case "crear_partido":
                        $resultado = crear_partido($liga_post, $_POST, $usuario["id"]);
                        $ok = $resultado === "ok";
                        $mensaje = $ok ? "Partido agregado al calendario." : $resultado;
                        break;
                    case "cargar_resultado":
                        $resultado = cargar_resultado($liga_post, $_POST["partido_id"] ?? 0, $_POST["goles_local"] ?? "", $_POST["goles_visitante"] ?? "", $_POST["definicion"] ?? "regular");
                        $ok = is_array($resultado);
                        $mensaje = $ok ? "Resultado guardado." : $resultado;
                        if ($ok && $resultado["ok"] > 0) {
                            $mensaje .= " Se " . ($resultado["ok"] === 1 ? "eliminó 1 aviso disciplinario que ya cumplió" : "eliminaron " . $resultado["ok"] . " avisos disciplinarios que ya cumplieron") . " sus partidos.";
                        }
                        break;
                    case "borrar_partido":
                        $resultado = borrar_partido($liga_post, $_POST["partido_id"] ?? 0);
                        $ok = $resultado === "ok";
                        $mensaje = $ok ? "Partido borrado del calendario." : $resultado;
                        break;
                }
            }

            $_SESSION["mensaje_liga"] = $mensaje;
            $_SESSION["mensaje_liga_ok"] = $ok;
            if ($liga_volver === null) {
                header("Location: /index.php?pagina=enfrentamientos");
            } else {
                header("Location: " . $url_liga($liga_volver, array_filter(["pestana" => $pestana, "torneo" => $torneo_volver])));
            }
            exit;
        }

        // acciones de la ventana del equipo: [quien puede, mensaje si sale bien]
        $acciones_modal = [
            "asignar_editor"        => [$puede_agregar_clubes, "Cliente asignado al club."],
            "quitar_editor"         => [$puede_agregar_clubes, "Cliente quitado del club."],
            "agregar_participante"  => [puede_editar_club($club_id), "Participante agregado al club."],
            "quitar_participante"   => [puede_editar_club($club_id), "Participante quitado del club."],
            "agregar_sancion"       => [$puede_sancionar, "Aviso agregado."],
            "quitar_sancion"        => [$puede_sancionar, "Aviso quitado."],
            "registrar_partido"     => [$puede_sancionar, "Partido registrado."],
        ];

        if (isset($acciones_modal[$accion])) {
            [$permitido, $mensaje_ok] = $acciones_modal[$accion];
            if (!$permitido) {
                http_response_code(403);
                exit("No tienes permiso para hacer esto en este club.");
            }

            $resultado = $vencida;
            if ($token_valido) {
                switch ($accion) {
                    case "asignar_editor":       $resultado = asignar_editor_club($club_id, $_POST["usuario_id"] ?? "", $usuario["id"]); break;
                    case "quitar_editor":        $resultado = quitar_editor_club($club_id, $_POST["usuario_id"] ?? ""); break;
                    case "agregar_participante":
                        $resultado = agregar_participante_club($club_id, $_POST["correo"] ?? "", $usuario["id"], ($_POST["confirmar_rol"] ?? "") === "1");
                        break;
                    case "quitar_participante":  $resultado = quitar_participante_club($club_id, $_POST["usuario_id"] ?? ""); break;
                    case "agregar_sancion":      $resultado = agregar_sancion($club_id, $_POST, $usuario["id"]); break;
                    case "quitar_sancion":       $resultado = quitar_sancion($club_id, $_POST["sancion_id"] ?? ""); break;
                    case "registrar_partido":
                        $eliminados = registrar_partido_jugado($club_id);
                        if (is_int($eliminados)) {
                            $resultado = "ok";
                            if ($eliminados > 0) {
                                $mensaje_ok .= " Se " . ($eliminados === 1 ? "eliminó 1 aviso que ya cumplió" : "eliminaron $eliminados avisos que ya cumplieron") . " sus partidos.";
                            }
                        } else {
                            $resultado = $eliminados;
                        }
                        break;
                }
            }

            if (is_array($resultado) && isset($resultado["confirmar"])) {
                // la cuenta es de un cliente sin club: se pregunta antes de darle el rol participante
                $_SESSION["confirmar_participante"] = [
                    "club_id" => $club_id,
                    "correo"   => trim(strtolower((string) ($_POST["correo"] ?? ""))),
                    "nombre"  => $resultado["confirmar"],
                ];
            } else {
                $_SESSION["mensaje_modal"] = $resultado === "ok" ? $mensaje_ok : $resultado;
                $_SESSION["mensaje_modal_ok"] = $resultado === "ok";
            }
            header("Location: /index.php?pagina=enfrentamientos&ver=" . urlencode($club_id));
            exit;
        }

        // agregar o editar un club
        $editando = $club_id !== "";
        $puede_crear = $liga_post !== null && ($puede_agregar_clubes || puede_administrar_liga($liga_post));
        if (($editando && !puede_editar_club($club_id)) || (!$editando && !$puede_crear)) {
            http_response_code(403);
            exit($editando ? "No tienes permiso para editar este club." : "No tienes permiso para agregar clubes a esta liga.");
        }

        if (!$token_valido) {
            $error_club = $vencida;
        } else {
            $resultado = guardar_club($_POST, $_FILES["logo"] ?? null, $usuario["id"], [
                "titular"  => $_FILES["foto_titular"] ?? null,
                "suplente" => $_FILES["foto_suplente"] ?? null,
            ]);
            if ($resultado === "ok") {
                $liga_del_club = $editando ? liga_de_club($club_id) : $liga_post;
                header("Location: " . $url_liga($liga_del_club, ["club_guardado" => $editando ? "editado" : "creado"]));
                exit;
            }
            $error_club = $resultado;
        }
    }
}

// ---- que se muestra: la lista de ligas o una liga ----
$ligas = listar_ligas();

// liga a mostrar: ?liga=, la del club de ?ver= o ?editar= (vienen del perfil), o la del formulario con error
$liga_actual = null;
$liga_pedida = $_GET["liga"] ?? ($_POST["liga_id"] ?? null);
if ($liga_pedida === null) {
    foreach (["ver", "editar"] as $parametro) {
        if (is_string($_GET[$parametro] ?? null)) {
            $liga_pedida = liga_de_club($_GET[$parametro]);
        }
    }
    if ($liga_pedida === null && is_string($_POST["club_id"] ?? null) && $_POST["club_id"] !== "") {
        $liga_pedida = liga_de_club($_POST["club_id"]);
    }
}
$id_pedido = filter_var($liga_pedida, FILTER_VALIDATE_INT);
if ($id_pedido !== false && isset($ligas[$id_pedido])) {
    $liga_actual = $ligas[$id_pedido];
}

$mensaje_liga = $_SESSION["mensaje_liga"] ?? "";
$mensaje_liga_ok = $_SESSION["mensaje_liga_ok"] ?? false;
$mensaje_modal = $_SESSION["mensaje_modal"] ?? "";
$mensaje_modal_ok = $_SESSION["mensaje_modal_ok"] ?? false;
$confirmar_participante = $_SESSION["confirmar_participante"] ?? null;
unset($_SESSION["mensaje_liga"], $_SESSION["mensaje_liga_ok"], $_SESSION["mensaje_modal"], $_SESSION["mensaje_modal_ok"], $_SESSION["confirmar_participante"]);

$deportes = deportes_clubes();
$planes_liga = planes_liga();

// equipos sin liga (de ligas que se borraron)
$equipos_sin_liga = $puede_gestionar_ligas && !$liga_actual ? equipos_sin_liga() : [];

// planes contratados (solo los ven organizador y admin)
$contrataciones = $puede_gestionar_ligas && !$liga_actual ? contrataciones_pendientes() : [];
$historial_contrataciones = $puede_gestionar_ligas && !$liga_actual ? historial_contrataciones() : [];
$funciones_plan = funciones_plan();

$equipos = [];
$puede_administrar = false;
$vista_publica = true;
$destacados = [];
$torneos = [];
$torneo_actual = null;
$partidos = [];
$tabla = [];
$administradores_liga = [];
$pestana = "equipos";

if ($liga_actual) {
    $puede_administrar = puede_administrar_liga($liga_actual["id"]);
    $habilidades_habilitadas = plan_incluye($liga_actual["plan"], "estadisticas_avanzadas");
    $fotos_habilitadas = plan_incluye($liga_actual["plan"], "perfiles");

    // equipos de la liga, mostrados segun lo que incluye su plan
    foreach (listar_equipos() as $equipo) {
        if (!in_array($equipo["id"], $liga_actual["clubes"], true)) {
            continue;
        }
        $equipo["plan"] = $habilidades_habilitadas ? "pro" : "basico";
        if (!$fotos_habilitadas) {
            foreach ($equipo["jugadores"] as $i => $jugador) {
                unset($equipo["jugadores"][$i]["foto"]);
            }
        }
        $equipos[] = $equipo;
    }
    $deportes = [$liga_actual["deporte"] => $liga_actual["nombre_deporte"]];

    // torneos, calendario y tabla
    $torneos = torneos_de_liga($liga_actual["id"]);
    $torneo_pedido = filter_var($_GET["torneo"] ?? null, FILTER_VALIDATE_INT);
    foreach ($torneos as $torneo) {
        if ($torneo["id"] === $torneo_pedido) {
            $torneo_actual = $torneo;
        }
    }
    $torneo_actual = $torneo_actual ?? ($torneos[0] ?? null);
    if ($torneo_actual) {
        $partidos = partidos_de_torneo($torneo_actual["id"]);
        $tabla = tabla_posiciones($partidos, $equipos, $liga_actual["deporte"]);
    }

    $administradores_liga = $puede_gestionar_ligas || $puede_administrar ? administradores_de_liga($liga_actual["id"]) : [];

    // vista publica: el que no administra la liga ve equipos, torneos y tablas, sin plan ni administracion
    $vista_publica = !$puede_gestionar_ligas && !$puede_administrar;
    $pestanas_validas = $vista_publica ? ["equipos", "torneos"] : ["equipos", "torneos", "plan", "admin"];

    // destacados de la vista publica: lider, proximo partido y ultimo resultado del torneo en curso
    $torneo_en_curso = current(array_filter($torneos, fn($t) => $t["estado"] === "activo")) ?: null;
    $partidos_en_curso = $torneo_en_curso ? partidos_de_torneo($torneo_en_curso["id"]) : [];
    $tabla_en_curso = $torneo_en_curso ? tabla_posiciones($partidos_en_curso, $equipos, $liga_actual["deporte"]) : [];
    $proximos = array_values(array_filter($partidos_en_curso, fn($p) => $p["estado"] !== "finalizado"));
    $jugados_en_curso = array_values(array_filter($partidos_en_curso, fn($p) => $p["estado"] === "finalizado"));
    $destacados = [
        "torneo"  => $torneo_en_curso,
        "lider"   => ($tabla_en_curso[0]["pj"] ?? 0) > 0 ? $tabla_en_curso[0] : null,
        "proximo" => $proximos[0] ?? null,
        "ultimo"  => $jugados_en_curso ? end($jugados_en_curso) : null,
    ];
    $pestana = in_array($_GET["pestana"] ?? "", $pestanas_validas, true) ? $_GET["pestana"] : ($torneo_pedido ? "torneos" : "equipos");
}

$puede_agregar_en_liga = $liga_actual && ($puede_agregar_clubes || $puede_administrar);
$puede_usar_form_club = $liga_actual && ($puede_agregar_en_liga || $puede_editar_clubes);

// para organizadores y administradores: clientes y quienes pueden editar cada club
$clientes = $puede_agregar_clubes ? clientes_disponibles() : [];
$editores = $puede_agregar_clubes ? editores_por_club() : [];

// participantes: solo de los clubes que el usuario puede editar
$participantes_club = $puede_editar_clubes
    ? array_intersect_key(participantes_por_club(), array_flip($clubes_editables))
    : [];

// avisos disciplinarios: los ve todo el mundo (solo los que siguen vigentes)
$sanciones = sanciones_por_club();
$partidos_jugados = partidos_jugados_por_club();
$tipos_sancion = tipos_sancion();

// limites de subida de PHP, para avisar antes de mandar el formulario
$a_bytes = function ($valor) {
    $valor = trim((string) $valor);
    $numero = (int) $valor;
    switch (strtolower(substr($valor, -1))) {
        case "g": return $numero * 1024 * 1024 * 1024;
        case "m": return $numero * 1024 * 1024;
        case "k": return $numero * 1024;
        default:  return $numero;
    }
};
$limites_subida = [
    "total"    => $a_bytes(ini_get("post_max_size")),
    "archivos" => (int) ini_get("max_file_uploads"),
];

require __DIR__ . "/../views/pages/enfrentamientos.php";
