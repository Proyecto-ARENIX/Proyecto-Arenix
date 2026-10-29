<?php
$servidor = "localhost";
$usuario = "root";
$clave = "";
$base_datos = "arenix";

$conexion = mysqli_connect($servidor , $usuario , $clave , $base_datos);

if (mysqli_connect_errno()) {
die("ERROR DE CONEXION: " . mysqli_connect_error());
}

mysqli_set_charset($conexion , "utf8mb4");

echo "<h3>Conexion Metodo 1: MySQLi Procedural </h3>";
echo "Conexion exitosa a la base de datos <br>";
echo "Base de datos: " . $base_datos . "<br>";
echo "Servidor: " . $servidor . "<br>";
echo "Version del servidor MySQL: " . mysqli_get_server_info($conexion) . "<br>"
;

mysqli_close($conexion);
echo "Conexion cerrada correctamente";
?>
