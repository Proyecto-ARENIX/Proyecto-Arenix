#!/bin/bash

ROL_ADMINISTRADOR="arenix_admin"
ROL_ORGANIZADOR="arenix_organizador"
ROL_PARTICIPANTE="arenix_participante"
ROL_CLIENTE="arenix_cliente"

GRUPOS_ROLES=(
    "$ROL_ADMINISTRADOR"
    "$ROL_ORGANIZADOR"
    "$ROL_PARTICIPANTE"
    "$ROL_CLIENTE"
)

requerir_root() {
    if [[ "${EUID}" -ne 0 ]]; then
        echo "Este script debe ejecutarse como root."
        exit 1
    fi
}

crear_grupos() {
    for grupo in "${GRUPOS_ROLES[@]}"; do
        if ! getent group "$grupo" >/dev/null; then
            groupadd "$grupo"
            echo "Grupo creado: $grupo"
        else
            echo "Grupo ya existe: $grupo"
        fi
    done
}

rol_a_grupo() {
    case "$1" in
        admin) echo "$ROL_ADMINISTRADOR" ;;
        organizador)         echo "$ROL_ORGANIZADOR" ;;
        participante)        echo "$ROL_PARTICIPANTE" ;;
        cliente)             echo "$ROL_CLIENTE" ;;
        *)
        echo "Rol inválido: $1" >&2
        return 1
        ;;
    esac
}

crear_usuario() {
    local nombre_usuario="$1"
    local rol="$2"
    local grupo

    grupo="$(rol_a_grupo "$rol")"

    if id "$nombre_usuario" >/dev/null 2>&1; then
        echo "El usuario '$nombre_usuario' ya existe."
        return 1
    fi

    useradd -m -s /bin/bash -c "Usuario Arenix ($rol)" "$nombre_usuario"
    usermod -aG "$grupo" "$nombre_usuario"

    if [[ "$grupo" == "$ROL_ADMINISTRADOR" ]]; then
        usermod -aG wheel "$nombre_usuario"
    fi

    echo
    read -rsp "Contraseña temporal para $nombre_usuario: " contrasena_temporal
    echo
    echo "$nombre_usuario:$contrasena_temporal" | chpasswd
    chage -d 0 "$nombre_usuario"

    echo "Usuario creado: $nombre_usuario -> $grupo"
}

eliminar_usuario() {
    local nombre_usuario="$1"

    if ! id "$nombre_usuario" >/dev/null 2>&1; then
        echo "El usuario '$nombre_usuario' no existe."
        return 1
    fi

    userdel -r "$nombre_usuario"
    echo "Usuario eliminado: $nombre_usuario"
}

listar_roles() {
    echo "Roles disponibles:"
    echo " - Admin"
    echo " - Organizador"
    echo " - Participante"
    echo " - Cliente"
}

listar_usuarios_por_rol() {
    for grupo in "${GRUPOS_ROLES[@]}"; do
        echo
        echo "== $grupo =="
        integrantes="$(getent group "$grupo" | awk -F: '{print $4}')"

        if [[ -z "${integrantes}" ]]; then
            echo "(sin usuarios)"
        else
            IFS=',' read -ra lista <<< "$integrantes"
            for usuario in "${lista[@]}"; do
            [[ -n "$usuario" ]] && echo "$usuario"
            done
        fi
    done
}

mostrar_ayuda() {
    echo "Uso:
        $0 init
        $0 crear <usuario> <rol>
        $0 eliminar <usuario>
        $0 lista-roles
        $0 lista-usuarios

        Ejemplos:
        $0 init
        $0 crear juan admin
        $0 crear maria organizador
        $0 crear lucas participante
        $0 crear sofia cliente"
}

main() {
    requerir_root

    case "${1:-}" in
        init)
            crear_grupos
        ;;
    crear)
        [[ $# -eq 3 ]] || { mostrar_ayuda; exit 1; }
        crear_grupos
        crear_usuario "$2" "$3"
        ;;
    eliminar)
        [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
        eliminar_usuario "$2"
        ;;
    lista-roles)
        listar_roles
        ;;
    lista-usuarios)
      listar_usuarios_por_rol
      ;;
    *)
        mostrar_ayuda
        exit 1
        ;;
    esac
}

main "$@"