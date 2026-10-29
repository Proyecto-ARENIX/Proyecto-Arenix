<?php
// ARENIX - todas las paginas pasan por aca (index.php?pagina=...)

require_once __DIR__ . "/app/models/auth.php";

$paginas = [
    "inicio"               => __DIR__ . "/app/controllers/inicio.php",
    "enfrentamientos"      => __DIR__ . "/app/controllers/torneos.php",
    "resultados"           => __DIR__ . "/app/controllers/resultados.php",
    "horarios"             => __DIR__ . "/app/controllers/horarios.php",
    "eventos"              => __DIR__ . "/app/controllers/eventos.php",
    "noticias"             => __DIR__ . "/app/controllers/noticias.php",
    "planes"               => __DIR__ . "/app/views/pages/planes.php",
    "contratar"            => __DIR__ . "/app/controllers/contratar.php",
    "calificamos"          => __DIR__ . "/app/views/pages/planes.php", 
    "habilidades"          => __DIR__ . "/app/views/pages/habilidades-jugador.php",
    "reglamentos"          => __DIR__ . "/app/views/pages/reglamentos.php",
    "contacto"             => __DIR__ . "/app/views/pages/contacto.php",
    "perfil"               => __DIR__ . "/app/controllers/perfil.php",
    "permisos"             => __DIR__ . "/app/controllers/permisos.php",
    "ingresar"             => __DIR__ . "/app/views/auth/ingresar.php",
    "registro"             => __DIR__ . "/app/views/auth/registro.php",
    "recuperar-contrasena" => __DIR__ . "/app/views/auth/recuperar-contrasena.php",
];

$pagina = $_GET["pagina"] ?? "inicio";

if (!isset($paginas[$pagina])) {
    http_response_code(404);
    $pagina = "inicio";
}

require $paginas[$pagina];