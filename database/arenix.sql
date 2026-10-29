-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-10-2026 a las 19:05:58
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `arenix`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clubes`
--

CREATE TABLE `clubes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `deporte` varchar(20) NOT NULL,
  `torneo` varchar(60) NOT NULL,
  `ciudad` varchar(60) NOT NULL,
  `entrenador` varchar(60) NOT NULL,
  `formacion` varchar(10) NOT NULL,
  `plan` varchar(10) NOT NULL DEFAULT 'basico',
  `habilidades` text NOT NULL,
  `jugadores` text NOT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `clave_base` varchar(40) DEFAULT NULL,
  `editado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clubes`
--

INSERT INTO `clubes` (`id`, `nombre`, `logo`, `deporte`, `torneo`, `ciudad`, `entrenador`, `formacion`, `plan`, `habilidades`, `jugadores`, `creado_por`, `creado_en`, `clave_base`, `editado_por`) VALUES
(1, 'rodrigo', '/public/assets/uploads/clubes/bf769915d99ee163ac2bc73e381868f5.png', 'futbol', 'Liga Apertura', 'solymar', 'juan', '4-2-3-1', 'basico', '[]', '[{\"numero\":1,\"nombre\":\"acskac\",\"posicion\":\"POR\",\"titular\":true},{\"numero\":2,\"nombre\":\"as\",\"posicion\":\"LD\",\"titular\":true},{\"numero\":3,\"nombre\":\"sad\",\"posicion\":\"DFC\",\"titular\":true},{\"numero\":4,\"nombre\":\"asdsa\",\"posicion\":\"DFC\",\"titular\":true},{\"numero\":5,\"nombre\":\"dsfaf\",\"posicion\":\"LI\",\"titular\":true},{\"numero\":6,\"nombre\":\"fsaf\",\"posicion\":\"MCD\",\"titular\":true},{\"numero\":7,\"nombre\":\"fa\",\"posicion\":\"MCD\",\"titular\":true},{\"numero\":8,\"nombre\":\"fagax\",\"posicion\":\"ED\",\"titular\":true},{\"numero\":9,\"nombre\":\"fd\",\"posicion\":\"MCO\",\"titular\":true},{\"numero\":10,\"nombre\":\"sddg\",\"posicion\":\"EI\",\"titular\":true},{\"numero\":11,\"nombre\":\"ere\",\"posicion\":\"DC\",\"titular\":true},{\"numero\":12,\"nombre\":\"ansfjjd\",\"posicion\":\"SUP\",\"titular\":false},{\"numero\":13,\"nombre\":\"dsf\",\"posicion\":\"SUP\",\"titular\":false},{\"numero\":14,\"nombre\":\"sd\",\"posicion\":\"SUP\",\"titular\":false},{\"numero\":15,\"nombre\":\"fds\",\"posicion\":\"SUP\",\"titular\":false},{\"numero\":16,\"nombre\":\"fsdc\",\"posicion\":\"SUP\",\"titular\":false},{\"numero\":17,\"nombre\":\"fv\",\"posicion\":\"SUP\",\"titular\":false},{\"numero\":18,\"nombre\":\"sdx\",\"posicion\":\"SUP\",\"titular\":false}]', 1, '2026-10-15 02:13:48', NULL, NULL),
(4, 'Tigres FC', '/public/assets/images/tigres-fc.png', 'futbol', 'Liga Apertura', 'Montevideo', 'Ricardo Paz', '4-3-3', 'pro', '{\"Ataque\":88,\"Defensa\":79,\"Posesión\":84,\"Velocidad\":81,\"Físico\":76,\"Pase\":86}', '[{\"numero\":1,\"nombre\":\"Benjamín Pérez\",\"posicion\":\"POR\",\"titular\":true},{\"numero\":12,\"nombre\":\"Nicolás Castro\",\"posicion\":\"LD\",\"titular\":true},{\"numero\":15,\"nombre\":\"Gonzalo Díaz\",\"posicion\":\"DFC\",\"titular\":true},{\"numero\":18,\"nombre\":\"Santiago Ríos\",\"posicion\":\"DFC\",\"titular\":true},{\"numero\":21,\"nombre\":\"Agustín Romero\",\"posicion\":\"LI\",\"titular\":true},{\"numero\":24,\"nombre\":\"Emiliano González\",\"posicion\":\"MC\",\"titular\":true},{\"numero\":27,\"nombre\":\"Joaquín Acosta\",\"posicion\":\"MC\",\"titular\":true},{\"numero\":30,\"nombre\":\"Lautaro Fernández\",\"posicion\":\"MC\",\"titular\":true},{\"numero\":4,\"nombre\":\"Diego Medina\",\"posicion\":\"ED\",\"titular\":true},{\"numero\":7,\"nombre\":\"Bruno Martínez\",\"posicion\":\"DC\",\"titular\":true},{\"numero\":10,\"nombre\":\"Franco Suárez\",\"posicion\":\"EI\",\"titular\":true},{\"numero\":13,\"nombre\":\"Mateo Gómez\",\"posicion\":\"POR\",\"titular\":false},{\"numero\":16,\"nombre\":\"Valentín Vera\",\"posicion\":\"DFC\",\"titular\":false},{\"numero\":19,\"nombre\":\"Martín Sosa\",\"posicion\":\"MC\",\"titular\":false},{\"numero\":22,\"nombre\":\"Lucas Méndez\",\"posicion\":\"ED\",\"titular\":false},{\"numero\":25,\"nombre\":\"Tomás Silva\",\"posicion\":\"DC\",\"titular\":false}]', 1, '2026-10-15 02:24:38', 'tigres-fc', 1),
(34, 'ewrewertw', '/public/assets/uploads/clubes/f5f4e867c168f245144d36aa9220c281.jpg', 'waterpolo', 'Liga Apertura', 'solymar', 'rttr', '3-3 (arco)', 'basico', '{\"Natación\":1,\"Tiro\":1,\"Defensa\":1,\"Pase\":0,\"Físico\":1,\"Resistencia\":1}', '[{\"numero\":1,\"nombre\":\"gfd\",\"posicion\":\"POR\",\"titular\":true},{\"numero\":2,\"nombre\":\"gfd\",\"posicion\":\"LAI\",\"titular\":true},{\"numero\":3,\"nombre\":\"fggf\",\"posicion\":\"CEN\",\"titular\":true},{\"numero\":4,\"nombre\":\"gfgf\",\"posicion\":\"LAD\",\"titular\":true},{\"numero\":5,\"nombre\":\"gfgf\",\"posicion\":\"EXI\",\"titular\":true},{\"numero\":6,\"nombre\":\"gfgf\",\"posicion\":\"BOY\",\"titular\":true},{\"numero\":7,\"nombre\":\"ffdg\",\"posicion\":\"EXD\",\"titular\":true}]', 1, '2026-10-15 14:43:03', NULL, NULL),
(35, 'sda', '/public/assets/uploads/clubes/b47ad5d0a9b118ca4eb40b15e3f58c81.jpg', 'waterpolo', 'Liga Apertura', 'da', 'Alberto Cabrera', '3-3 (arco)', 'basico', '{\"Natación\":1,\"Tiro\":31,\"Defensa\":100,\"Pase\":63,\"Físico\":41,\"Resistencia\":43}', '[{\"numero\":1,\"nombre\":\"wwdsf\",\"posicion\":\"POR\",\"titular\":true},{\"numero\":2,\"nombre\":\"wsfsdf\",\"posicion\":\"LAI\",\"titular\":true},{\"numero\":3,\"nombre\":\"ffsgsg\",\"posicion\":\"CEN\",\"titular\":true},{\"numero\":4,\"nombre\":\"fqwe\",\"posicion\":\"LAD\",\"titular\":true},{\"numero\":5,\"nombre\":\"spá,\",\"posicion\":\"EXI\",\"titular\":true},{\"numero\":6,\"nombre\":\"fssapp\",\"posicion\":\"BOY\",\"titular\":true},{\"numero\":7,\"nombre\":\"ddaod\",\"posicion\":\"EXD\",\"titular\":true}]', 1, '2026-10-21 03:54:54', NULL, NULL),
(36, 'a588', '/public/assets/uploads/clubes/d297bea844d54665cfd012041b69c9d8.png', 'rugby7', 'Liga Metropolitana', 'solymar', 'asds', '3 forwards', 'basico', '[]', '[{\"numero\":3,\"nombre\":\"fafd\",\"posicion\":\"CEN\",\"titular\":true},{\"numero\":12,\"nombre\":\"gsdhs\",\"posicion\":\"WIN\",\"titular\":true},{\"numero\":98,\"nombre\":\"hfdh\",\"posicion\":\"APE\",\"titular\":true},{\"numero\":51,\"nombre\":\"hdfh\",\"posicion\":\"MSC\",\"titular\":true},{\"numero\":87,\"nombre\":\"jdfh\",\"posicion\":\"PII\",\"titular\":true},{\"numero\":11,\"nombre\":\"hdfjh\",\"posicion\":\"HOO\",\"titular\":true},{\"numero\":21,\"nombre\":\"hdjd\",\"posicion\":\"PID\",\"titular\":true}]', 1, '2026-10-21 04:05:54', NULL, NULL),
(37, 'wer', '/public/assets/uploads/clubes/aacac30d2eb838f680cada73e6850444.png', 'rugby7', 'Liga Metropolitana', 'solymar', 'qeqr', '3 forwards', 'basico', '[]', '[{\"numero\":1,\"nombre\":\"qewq\",\"posicion\":\"CEN\",\"titular\":true},{\"numero\":2,\"nombre\":\"11442\",\"posicion\":\"WIN\",\"titular\":true},{\"numero\":3,\"nombre\":\"t3hrh\",\"posicion\":\"APE\",\"titular\":true},{\"numero\":4,\"nombre\":\"jeijig\",\"posicion\":\"MSC\",\"titular\":true},{\"numero\":9,\"nombre\":\"uj2r\",\"posicion\":\"PII\",\"titular\":true},{\"numero\":7,\"nombre\":\"2jjifw\",\"posicion\":\"HOO\",\"titular\":true},{\"numero\":8,\"nombre\":\"j2jir\",\"posicion\":\"PID\",\"titular\":true}]', 1, '2026-10-21 04:06:39', NULL, NULL),
(38, 'dasffdf', '/public/assets/uploads/clubes/d956f03a61bc268f1ebf2a44497cb67d.png', 'futbol', 'Liga Apertura', 'Rocha', 'juan', '4-3-2-1', 'basico', '{\"Ataque\":43,\"Defensa\":42,\"Posesión\":54,\"Velocidad\":13,\"Físico\":63,\"Pase\":65}', '[{\"numero\":1,\"nombre\":\"adas\",\"posicion\":\"POR\",\"titular\":true},{\"numero\":2,\"nombre\":\"vgss\",\"posicion\":\"LI\",\"titular\":true},{\"numero\":3,\"nombre\":\"kjtk\",\"posicion\":\"DFC\",\"titular\":true},{\"numero\":4,\"nombre\":\"2r23\",\"posicion\":\"DFC\",\"titular\":true},{\"numero\":5,\"nombre\":\"gehr\",\"posicion\":\"LD\",\"titular\":true},{\"numero\":6,\"nombre\":\"w2r23\",\"posicion\":\"MC\",\"titular\":true},{\"numero\":7,\"nombre\":\"fweg\",\"posicion\":\"MC\",\"titular\":true},{\"numero\":8,\"nombre\":\"gejy\",\"posicion\":\"MC\",\"titular\":true},{\"numero\":9,\"nombre\":\"fwefe\",\"posicion\":\"MCO\",\"titular\":true},{\"numero\":10,\"nombre\":\"fwef\",\"posicion\":\"MCO\",\"titular\":true},{\"numero\":11,\"nombre\":\"gehte\",\"posicion\":\"DC\",\"titular\":true}]', 85, '2026-10-21 17:15:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `club_editores`
--

CREATE TABLE `club_editores` (
  `club_clave` varchar(40) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `asignado_por` int(11) DEFAULT NULL,
  `asignado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `club_editores`
--

INSERT INTO `club_editores` (`club_clave`, `usuario_id`, `asignado_por`, `asignado_en`) VALUES
('club-1', 5, 1, '2026-10-15 02:48:52'),
('club-38', 85, 1, '2026-10-22 03:14:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `club_participantes`
--

CREATE TABLE `club_participantes` (
  `club_clave` varchar(40) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `agregado_por` int(11) DEFAULT NULL,
  `agregado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `club_partidos`
--

CREATE TABLE `club_partidos` (
  `club_clave` varchar(40) NOT NULL,
  `jugados` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `club_partidos`
--

INSERT INTO `club_partidos` (`club_clave`, `jugados`) VALUES
('club-1', 4),
('club-34', 2),
('club-38', 2),
('panteras', 1),
('tigres-fc', 1),
('toros', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `club_sanciones`
--

CREATE TABLE `club_sanciones` (
  `id` int(11) NOT NULL,
  `club_clave` varchar(40) NOT NULL,
  `jugador` varchar(60) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `partido` varchar(80) NOT NULL,
  `fecha` date NOT NULL,
  `detalle` varchar(120) DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `partidos_duracion` int(11) NOT NULL DEFAULT 1,
  `partido_inicio` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicados`
--

CREATE TABLE `comunicados` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `contexto` varchar(40) NOT NULL,
  `titulo` varchar(90) NOT NULL,
  `texto` varchar(300) NOT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `editado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comunicados`
--

INSERT INTO `comunicados` (`id`, `tipo`, `contexto`, `titulo`, `texto`, `creado_por`, `editado_por`, `creado_en`) VALUES
(1, 'partido', 'Liga Apertura', 'Club Norte 2–1 Deportivo Litoral', 'Club Norte sostuvo la presión en el cierre y aseguró los tres puntos en casa.', NULL, NULL, '2026-10-10 15:00:00'),
(2, 'salud', 'Guerreros', 'Baja estimada: tres semanas', 'El delantero continúa su recuperación. Su regreso dependerá de una nueva evaluación médica.', NULL, NULL, '2026-10-09 15:00:00'),
(3, 'disciplina', 'Boletín oficial', 'Marcos Vera recibe una fecha de suspensión', 'La sanción corresponde a la expulsión registrada en el encuentro de la última jornada.', NULL, NULL, '2026-10-09 14:00:00'),
(4, 'aviso', 'Inscripciones', 'Abierta la inscripción a la Copa Ciudad', 'Los equipos pueden consultar fechas y sedes en la sección de eventos.', NULL, NULL, '2026-10-08 15:00:00'),
(5, 'aviso', 'apertura', 'Ganadores de la copa', 'husoosdpfña', 1, NULL, '2026-10-20 20:07:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `clave` varchar(60) NOT NULL,
  `valor` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('comunicados_demo_creados', '1'),
('eventos_demo_creados', '1'),
('inicio_bienvenida_bajada', 'La plataforma integral para la gestión deportiva y la competencia.'),
('inicio_bienvenida_boton', 'Explorar Enfrentamientos'),
('inicio_bienvenida_boton_pagina', 'enfrentamientos'),
('inicio_bienvenida_saludo', 'Bienvenido a'),
('ligas_demo_creadas', '1'),
('noticias_demo_creadas', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contrataciones`
--

CREATE TABLE `contrataciones` (
  `id` int(11) NOT NULL,
  `plan` varchar(20) NOT NULL,
  `equipos_extra` int(11) NOT NULL DEFAULT 0,
  `jugadores_extra` int(11) NOT NULL DEFAULT 0,
  `total_mensual` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `correo` varchar(190) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `organizacion` varchar(80) NOT NULL,
  `deporte` varchar(20) NOT NULL,
  `ciudad` varchar(60) NOT NULL,
  `tarjeta_marca` varchar(20) NOT NULL,
  `tarjeta_ultimos` char(4) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pagado_prueba',
  `usuario_id` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `liga_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contrataciones`
--

INSERT INTO `contrataciones` (`id`, `plan`, `equipos_extra`, `jugadores_extra`, `total_mensual`, `nombre`, `correo`, `telefono`, `organizacion`, `deporte`, `ciudad`, `tarjeta_marca`, `tarjeta_ultimos`, `estado`, `usuario_id`, `creado_en`, `liga_id`) VALUES
(1, 'profesional', 0, 0, 99, 'aigijirt', 'projectoarenix@gmail.com', '98174747', 'Liga del Campo', 'futbol', 'mentevideo', 'visa', '4242', 'pagado_prueba', 1, '2026-10-15 05:07:47', 26),
(10, 'enterprise', 0, 0, 250, 'aigijirt', 'projectoarenix@gmail.com', '98174747', 'palta', 'waterpolo', 'Montevideo', 'visa', '4242', 'pagado_prueba', 1, '2026-10-15 14:21:04', 30),
(12, 'profesional', 8, 30, 239, 'aigijirt', 'projectoarenix@gmail.com', '098767676', 'Liga del 3ro Mr', 'futbol', 'Montevideo', 'visa', '4242', 'pagado_prueba', 1, '2026-10-15 16:54:03', NULL),
(13, 'enterprise', 1, 0, 260, 'sebastian marr', 'sebas@gmail.com', '+598 099560605', 'Liga del Campo', 'futbol', 'Montevideo', 'visa', '4242', 'pagado_prueba', 85, '2026-10-21 17:09:42', 32),
(14, 'enterprise', 0, 0, 250, 'aigijirt', 'projectoarenix@gmail.com', '+598 099560605', 'Liga del Campo', 'futbol', 'Montevideo', 'visa', '4242', 'pagado_prueba', 1, '2026-10-21 17:25:27', NULL),
(21, 'profesional', 0, 0, 99, 'aigijirt', 'projectoarenix@gmail.com', '98174747', 'Liga del Campo', 'futbol', 'Montevideo', 'visa', '4242', 'pagado_prueba', 1, '2026-10-21 19:22:01', NULL),
(22, 'enterprise', 0, 0, 250, 'aigijirt', 'projectoarenix@gmail.com', '98174747', 'Liga del Campo', 'futbol', 'Montevideo', 'visa', '4242', 'pagado_prueba', 1, '2026-10-22 03:10:23', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos_eliminados`
--

CREATE TABLE `equipos_eliminados` (
  `club_clave` varchar(40) NOT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  `eliminado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `deporte` varchar(20) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `modalidad` varchar(60) DEFAULT NULL,
  `sede` varchar(80) DEFAULT NULL,
  `cupos` varchar(40) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'abierto',
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `nombre`, `deporte`, `fecha`, `hora`, `modalidad`, `sede`, `cupos`, `estado`, `creado_por`, `creado_en`) VALUES
(1, 'Final de la Copa Élite', 'futbol', '2026-10-29', '18:00:00', 'Final', 'Estadio Principal', 'Entrada libre', 'confirmado', NULL, '2026-10-21 04:55:19'),
(2, 'Torneo Metropolitano', 'basquetbol', '2026-10-30', '09:00:00', 'Juvenil · mixto', 'Polideportivo Norte', '12 lugares', 'abierto', NULL, '2026-10-21 04:55:19'),
(3, 'Festival de Vóley', 'voley', '2026-11-02', '10:00:00', 'Sub-18', 'Gimnasio Municipal', '6 equipos', 'abierto', NULL, '2026-10-21 04:55:19'),
(4, 'Clínica técnica', 'tenis', '2026-11-04', '14:00:00', 'Nivel inicial', 'Club de Tenis · Cancha 1', '8 lugares', 'limitado', NULL, '2026-10-21 04:55:19'),
(5, 'Copa Regional', 'rugby', '2026-11-06', '15:00:00', 'Mayores', 'Campo del Parque', '16 equipos', 'abierto', NULL, '2026-10-21 04:55:19'),
(6, 'Meeting ARENIX', 'atletismo', '2026-11-09', '09:00:00', 'Pista y campo', 'Pista Municipal', '24 lugares', 'abierto', NULL, '2026-10-21 04:55:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ligas`
--

CREATE TABLE `ligas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `deporte` varchar(20) NOT NULL,
  `plan` varchar(20) NOT NULL DEFAULT 'basico',
  `equipos_extra` int(11) NOT NULL DEFAULT 0,
  `jugadores_extra` int(11) NOT NULL DEFAULT 0,
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `logo` varchar(255) DEFAULT NULL,
  `portada` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ligas`
--

INSERT INTO `ligas` (`id`, `nombre`, `deporte`, `plan`, `equipos_extra`, `jugadores_extra`, `creado_por`, `creado_en`, `logo`, `portada`) VALUES
(1, 'Liga ARENIX Fútbol', 'futbol', 'enterprise', 0, 0, NULL, '2026-10-15 03:57:35', NULL, NULL),
(2, 'Liga ARENIX Básquetbol', 'basquetbol', 'enterprise', 0, 0, NULL, '2026-10-15 03:57:35', NULL, NULL),
(3, 'Liga ARENIX Vóley', 'voley', 'enterprise', 0, 0, NULL, '2026-10-15 03:57:36', NULL, NULL),
(16, 'dfrff', 'rugby7', 'basico', 0, 0, 1, '2026-10-15 04:29:45', NULL, NULL),
(26, 'Liga del Campo', 'futbol', 'profesional', 0, 0, 1, '2026-10-15 14:15:08', '/public/assets/uploads/ligas/223938b20d746c92466c52b91aaa63f2.png', '/public/assets/uploads/ligas/6d52260e56e6f9fbc3e894afaaa6e30e.png'),
(28, 'fgnnhgf', 'softbol', 'enterprise', 0, 0, 1, '2026-10-15 14:18:34', NULL, NULL),
(30, 'palta', 'waterpolo', 'enterprise', 0, 0, 1, '2026-10-15 14:22:56', NULL, NULL),
(31, 'dalslasd', 'americano', 'basico', 0, 0, 1, '2026-10-21 04:21:48', '/public/assets/uploads/ligas/cff71a39f2b6cf9766587c39d5f177e6.png', '/public/assets/uploads/ligas/42ae0e1bed25f6e95cb570a026579b49.png'),
(33, 'kkkkkkkkkk', 'handball', 'basico', 0, 0, 1, '2026-10-22 03:21:58', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `liga_administradores`
--

CREATE TABLE `liga_administradores` (
  `liga_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `asignado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `liga_administradores`
--

INSERT INTO `liga_administradores` (`liga_id`, `usuario_id`, `asignado_por`) VALUES
(1, 6, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `liga_equipos`
--

CREATE TABLE `liga_equipos` (
  `club_clave` varchar(40) NOT NULL,
  `liga_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `liga_equipos`
--

INSERT INTO `liga_equipos` (`club_clave`, `liga_id`) VALUES
('aguilas-fc', 1),
('club-1', 1),
('club-38', 1),
('delfines', 1),
('estrellas', 1),
('halcones', 1),
('leones-united', 1),
('pumas', 1),
('raptors-sc', 1),
('tigres-fc', 1),
('panteras', 2),
('titanes', 2),
('toros', 2),
('buhos', 3),
('cobras', 3),
('guerreros', 3),
('club-36', 16),
('club-37', 16),
('club-34', 30),
('club-35', 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `categoria` varchar(30) NOT NULL,
  `deporte` varchar(30) NOT NULL,
  `titulo` varchar(90) NOT NULL,
  `resumen` varchar(300) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `categoria`, `deporte`, `titulo`, `resumen`, `imagen`, `creado_por`, `creado_en`) VALUES
(1, 'Copa Mundial', 'vavy', 'Ganadores de la copa', 'Ganaron la copa Mundia 3 a 0', '/public/assets/uploads/noticias/bb6e5e5994e8c184a69afc0938b0dec4.jpg', 1, '2026-10-20 20:04:14'),
(2, 'Competencia', 'Fútbol', 'Tigres FC se afirma en la cima', 'El equipo sostuvo su racha y llega como líder a la próxima fecha del torneo.', '/public/assets/images/tigres-fc.png', NULL, '2026-10-10 15:00:00'),
(3, 'Crónica', 'Fútbol', 'Halcones se quedó con un final cerrado', 'Un gol en el segundo tiempo resolvió un partido parejo frente a Pumas.', '/public/assets/images/halcones.png', NULL, '2026-10-10 14:00:00'),
(4, 'Parte médico', 'Fútbol', 'Guerreros espera la recuperación de su delantero', 'El jugador estará fuera de las canchas durante tres semanas y será evaluado antes de volver.', '/public/assets/images/pumas.png', NULL, '2026-10-09 15:00:00'),
(5, 'Análisis', 'Liga', 'La última fecha promete cambios en la tabla', 'Tres equipos llegan con posibilidades de quedarse con los primeros puestos.', '/public/assets/images/panteras.png', NULL, '2026-10-08 15:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `id` int(11) NOT NULL,
  `torneo_id` int(11) NOT NULL,
  `local` varchar(40) NOT NULL,
  `visitante` varchar(40) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `lugar` varchar(80) DEFAULT NULL,
  `goles_local` int(11) DEFAULT NULL,
  `goles_visitante` int(11) DEFAULT NULL,
  `estado` varchar(12) NOT NULL DEFAULT 'programado',
  `contado` tinyint(4) NOT NULL DEFAULT 0,
  `creado_por` int(11) DEFAULT NULL,
  `definicion` varchar(10) NOT NULL DEFAULT 'regular'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `partidos`
--

INSERT INTO `partidos` (`id`, `torneo_id`, `local`, `visitante`, `fecha`, `hora`, `lugar`, `goles_local`, `goles_visitante`, `estado`, `contado`, `creado_por`, `definicion`) VALUES
(1, 1, 'tigres-fc', 'halcones', '2026-10-08', '19:00:00', NULL, 2, 1, 'finalizado', 1, NULL, 'regular'),
(2, 1, 'pumas', 'estrellas', '2026-10-22', '19:00:00', NULL, 2, 1, 'finalizado', 1, NULL, 'regular'),
(3, 1, 'delfines', 'aguilas-fc', '2026-10-08', '19:00:00', NULL, 3, 2, 'finalizado', 1, NULL, 'regular'),
(4, 1, 'leones-united', 'raptors-sc', '2026-10-22', '19:00:00', NULL, 22, 1, 'finalizado', 1, NULL, 'regular'),
(5, 2, 'titanes', 'toros', '2026-10-08', '19:00:00', NULL, 2, 1, 'finalizado', 1, NULL, 'regular'),
(6, 3, 'guerreros', 'buhos', '2026-10-08', '19:00:00', NULL, 3, 1, 'finalizado', 1, NULL, 'regular'),
(10, 2, 'toros', 'panteras', '2026-10-21', NULL, NULL, 8, 8, 'finalizado', 1, 1, 'regular'),
(17, 8, 'club-36', 'club-37', '2026-12-12', '12:42:00', 'complejo jomde cancha 2', NULL, NULL, 'programado', 0, 1, 'regular');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torneos`
--

CREATE TABLE `torneos` (
  `id` int(11) NOT NULL,
  `liga_id` int(11) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `estado` varchar(12) NOT NULL DEFAULT 'activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `torneos`
--

INSERT INTO `torneos` (`id`, `liga_id`, `nombre`, `estado`, `creado_en`) VALUES
(1, 1, 'Apertura 2026 (demo)', 'activo', '2026-10-15 03:57:35'),
(2, 2, 'Apertura 2026 (demo)', 'activo', '2026-10-15 03:57:36'),
(3, 3, 'Apertura 2026 (demo)', 'activo', '2026-10-15 03:57:36'),
(6, 2, 'rodrigo', 'finalizado', '2026-10-15 04:14:54'),
(7, 30, 'rodrigo', 'activo', '2026-10-21 03:52:51'),
(8, 16, 'adas', 'activo', '2026-10-21 04:04:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `correo` varchar(190) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `rol` varchar(20) NOT NULL DEFAULT 'publico',
  `pais_telefono` char(2) DEFAULT NULL,
  `prefijo_telefono` varchar(8) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `foto`, `rol`, `pais_telefono`, `prefijo_telefono`, `telefono`) VALUES
(1, 'administrador','projectoarenix@gmail.com', '$2y$10$0sjtCJqfJJDLdCkllCUOCeJYP8vquthBVvnwDggXQ5wBXwrbQAJSC', '/public/assets/uploads/perfiles/2f8a6675b585a1391ee89ca713368b9a.jpg', 'admin', NULL, NULL, NULL),
(2, 'rodrigo', 'rodrigosuarezolivera@gmail.com', '$2y$10$MeTjkGzodSI3XsyCpdvXM.g/Aptp0h4wbBKBVBYFcax/UrPtp.V0e', NULL, 'participante', NULL, NULL, NULL),
(3, 'Leonel', 'rel4x501@gmail.com', '$2y$10$A81Ta4eGUvNEM8gkjjbc1u5QuH2LHa9hVulqYbm6PAaC4/UBi6qsK', NULL, 'participante', NULL, NULL, NULL),
(4, 'rodrigo', 'hola1324@gmail.com', '$2y$10$tHhafLeytz9jGodqTjayJ.9z/opgjQzruYgDU/fDVguJ.wQ76HfKi', NULL, 'participante', NULL, NULL, NULL),
(5, 'a231', 'sebamarr2482@gmail.com', '$2y$10$oj86VJFmAHdwHOcZvPga5.MK7YqJ.a4.oFXSrsyB7pUfdEjcgDDs.', NULL, 'cliente', NULL, NULL, NULL),
(85, 'sebastian marr', 'sebas@gmail.com', '$2y$10$ZtbyjWy.sCJUuh2FMb/mA.64UixaH3bfw4gp0kxAWErpTCnG8mA2i', NULL, 'cliente', 'UY', '+598', '099560605');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clubes`
--
ALTER TABLE `clubes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD UNIQUE KEY `clave_base` (`clave_base`);

--
-- Indices de la tabla `club_editores`
--
ALTER TABLE `club_editores`
  ADD PRIMARY KEY (`club_clave`,`usuario_id`);

--
-- Indices de la tabla `club_participantes`
--
ALTER TABLE `club_participantes`
  ADD PRIMARY KEY (`club_clave`,`usuario_id`);

--
-- Indices de la tabla `club_partidos`
--
ALTER TABLE `club_partidos`
  ADD PRIMARY KEY (`club_clave`);

--
-- Indices de la tabla `club_sanciones`
--
ALTER TABLE `club_sanciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_clave` (`club_clave`);

--
-- Indices de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `contrataciones`
--
ALTER TABLE `contrataciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `equipos_eliminados`
--
ALTER TABLE `equipos_eliminados`
  ADD PRIMARY KEY (`club_clave`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ligas`
--
ALTER TABLE `ligas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `liga_administradores`
--
ALTER TABLE `liga_administradores`
  ADD PRIMARY KEY (`liga_id`,`usuario_id`);

--
-- Indices de la tabla `liga_equipos`
--
ALTER TABLE `liga_equipos`
  ADD PRIMARY KEY (`club_clave`),
  ADD KEY `liga_id` (`liga_id`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `torneo_id` (`torneo_id`);

--
-- Indices de la tabla `torneos`
--
ALTER TABLE `torneos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `liga_id` (`liga_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clubes`
--
ALTER TABLE `clubes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `club_sanciones`
--
ALTER TABLE `club_sanciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `contrataciones`
--
ALTER TABLE `contrataciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ligas`
--
ALTER TABLE `ligas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `partidos`
--
ALTER TABLE `partidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `torneos`
--
ALTER TABLE `torneos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
