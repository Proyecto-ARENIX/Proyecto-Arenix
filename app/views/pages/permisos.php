<?php
$usuarios = $usuarios ?? [];
$roles = $roles ?? roles_disponibles();
$matriz_permisos = $matriz_permisos ?? permisos_por_rol();
$permisos = $permisos ?? permisos_disponibles();
$mensaje = $mensaje ?? "";
$tipo_mensaje = $tipo_mensaje ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar roles - ARENIX</title>
    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <link rel="stylesheet" href="/public/assets/css/permisos.css">
</head>
<body class="permisos-body">
    <header class="permisos-barra">
        <a class="permisos-marca" href="/index.php?pagina=inicio">ARE<span>NIX</span></a>
        <a href="/index.php?pagina=inicio">Volver al inicio</a>
    </header>

    <main class="permisos-pagina">
        <header class="permisos-encabezado">
            <div>
                <p>ADMINISTRACIÓN</p>
                <h1>Roles de usuario</h1>
                <span>Solo un Administrador puede asignar roles. El rol Público corresponde a visitantes sin sesión y no se asigna a cuentas.</span>
            </div>
            <strong><?php echo count($usuarios); ?> cuentas</strong>
        </header>

        <?php if ($mensaje): ?>
            <p class="permisos-mensaje permisos-<?php echo htmlspecialchars($tipo_mensaje, ENT_QUOTES, "UTF-8"); ?>" role="status">
                <?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?>
            </p>
        <?php endif; ?>

        <section class="permisos-matriz" aria-labelledby="permisos-matriz-titulo">
            <h2 id="permisos-matriz-titulo">Permisos por rol</h2>
            <div class="permisos-tabla-desplazable" role="region" aria-label="Permisos asignados a cada rol" tabindex="0">
                <table class="permisos-tabla permisos-tabla-matriz">
                    <thead>
                        <tr>
                            <th>Permiso</th>
                            <?php foreach ($matriz_permisos as $rol): ?>
                                <th><?php echo htmlspecialchars($rol["nombre"], ENT_QUOTES, "UTF-8"); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($permisos as $clave_permiso => $nombre_permiso): ?>
                            <tr>
                                <th scope="row"><?php echo htmlspecialchars($nombre_permiso, ENT_QUOTES, "UTF-8"); ?></th>
                                <?php foreach ($matriz_permisos as $rol): ?>
                                    <td class="<?php echo !empty($rol["permisos"][$clave_permiso]) ? "permiso-concedido" : "permiso-denegado"; ?>">
                                        <?php echo !empty($rol["permisos"][$clave_permiso]) ? "Sí" : "No"; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <h2 class="permisos-usuarios-titulo">Cuentas y roles</h2>
        <div class="permisos-busqueda">
            <label for="buscar-cuenta">Buscar usuario</label>
            <input id="buscar-cuenta" type="search" placeholder="Nombre, correo o rol" autocomplete="off">
            <span id="resultado-busqueda" role="status" aria-live="polite"><?php echo count($usuarios); ?> cuentas</span>
        </div>
        <div class="permisos-tabla-desplazable" role="region" aria-label="Cuentas y roles" tabindex="0">
            <table class="permisos-tabla">
                <thead>
                    <tr><th>Usuario</th><th>Correo</th><th>Rol actual</th><th>Administrar cuenta</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr data-usuario-fila data-busqueda="<?php echo htmlspecialchars($usuario["nombre"] . " " . $usuario["correo"] . " " . ($roles[$usuario["rol"]] ?? "Público"), ENT_QUOTES, "UTF-8"); ?>">
                            <td data-label="Usuario"><?php echo htmlspecialchars($usuario["nombre"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td data-label="Correo"><?php echo htmlspecialchars($usuario["correo"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td data-label="Rol actual"><span class="permiso-rol rol-<?php echo htmlspecialchars($usuario["rol"], ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($roles[$usuario["rol"]] ?? "Público", ENT_QUOTES, "UTF-8"); ?></span></td>
                            <td data-label="Administrar cuenta">
                                <div class="permiso-acciones">
                                    <?php if ((int) $usuario["id"] === (int) usuario_actual()["id"]): ?>
                                        <span class="permiso-propio">Tu cuenta: no puedes cambiar tu rol aquí.</span>
                                    <?php else: ?>
                                        <form class="permiso-form" method="post">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_permisos"], ENT_QUOTES, "UTF-8"); ?>">
                                            <input type="hidden" name="usuario_id" value="<?php echo (int) $usuario["id"]; ?>">
                                            <input type="hidden" name="accion" value="cambiar_rol">
                                            <label class="oculto-visual" for="rol-<?php echo (int) $usuario["id"]; ?>">Rol para <?php echo htmlspecialchars($usuario["nombre"], ENT_QUOTES, "UTF-8"); ?></label>
                                            <select id="rol-<?php echo (int) $usuario["id"]; ?>" name="rol">
                                                <?php foreach ($roles as $valor_rol => $nombre_rol): ?>
                                                    <option value="<?php echo htmlspecialchars($valor_rol, ENT_QUOTES, "UTF-8"); ?>"<?php echo $usuario["rol"] === $valor_rol ? " selected" : ""; ?>><?php echo htmlspecialchars($nombre_rol, ENT_QUOTES, "UTF-8"); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit">Guardar rol</button>
                                        </form>
                                    <?php endif; ?>
                                    <details class="permiso-cuenta-detalles">
                                        <summary>Cambiar contraseña</summary>
                                        <form class="permiso-seguridad-form" method="post">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_permisos"], ENT_QUOTES, "UTF-8"); ?>">
                                            <input type="hidden" name="usuario_id" value="<?php echo (int) $usuario["id"]; ?>">
                                            <input type="hidden" name="accion" value="cambiar_contrasena">
                                            <label for="contrasena-<?php echo (int) $usuario["id"]; ?>">Nueva contraseña</label>
                                            <input id="contrasena-<?php echo (int) $usuario["id"]; ?>" type="password" name="nueva_contrasena" minlength="6" autocomplete="new-password" required>
                                            <label for="confirmar-<?php echo (int) $usuario["id"]; ?>">Confirmar contraseña</label>
                                            <input id="confirmar-<?php echo (int) $usuario["id"]; ?>" type="password" name="confirmar_contrasena" minlength="6" autocomplete="new-password" required>
                                            <button type="submit">Cambiar contraseña</button>
                                        </form>
                                    </details>
                                    <?php if ((int) $usuario["id"] === (int) usuario_actual()["id"]): ?>
                                        <span class="permiso-propio">No puedes eliminar tu propia cuenta desde aquí.</span>
                                    <?php else: ?>
                                        <form class="permiso-eliminar-form" method="post" onsubmit="return confirm('¿Eliminar permanentemente esta cuenta? Esta acción no se puede deshacer.');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_permisos"], ENT_QUOTES, "UTF-8"); ?>">
                                            <input type="hidden" name="usuario_id" value="<?php echo (int) $usuario["id"]; ?>">
                                            <input type="hidden" name="accion" value="eliminar_usuario">
                                            <button type="submit">Eliminar cuenta</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="sin-resultados" hidden>
                        <td colspan="4">No se encontraron cuentas que coincidan con la búsqueda.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
    <script>
        const buscarCuenta = document.getElementById("buscar-cuenta");
        const filasCuenta = Array.from(document.querySelectorAll("[data-usuario-fila]"));
        const mensajeSinResultados = document.getElementById("sin-resultados");
        const resultadoBusqueda = document.getElementById("resultado-busqueda");

        function normalizarBusqueda(texto) {
            return texto.toLocaleLowerCase("es").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        }

        buscarCuenta.addEventListener("input", () => {
            const consulta = normalizarBusqueda(buscarCuenta.value.trim());
            let visibles = 0;

            filasCuenta.forEach((fila) => {
                const coincide = normalizarBusqueda(fila.dataset.busqueda).includes(consulta);
                fila.hidden = !coincide;
                if (coincide) visibles++;
            });

            mensajeSinResultados.hidden = visibles !== 0;
            resultadoBusqueda.textContent = consulta
                ? `${visibles} de ${filasCuenta.length} cuentas`
                : `${filasCuenta.length} cuentas`;
        });
    </script>
</body>
</html>