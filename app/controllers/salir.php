<?php
require_once __DIR__ . "/../models/auth.php";
cerrar_sesion();
header("Location: /index.php?pagina=inicio");
exit;
