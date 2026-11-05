#!/bin/bash

ROL_ADMINISTRADOR="arenix_admin"
ROL_ORGANIZADOR="arenix_organizador"
ROL_PARTICIPANTE="arenix_participante"
ROL_CLIENTE="arenix_cliente"

requerir_root() {
    if [[ "$EUID" -ne 0 ]]; then
        echo "Este script debe ejecutarse como root."
        exit 1
    fi
}

rol_a_grupo() {
    case "$1" in
        admin)          echo "$ROL_ADMINISTRADOR" ;;
        organizador)    echo "$ROL_ORGANIZADOR" ;;
        participante)   echo "$ROL_PARTICIPANTE" ;;
        cliente)        echo "$ROL_CLIENTE" ;;
        *)
            echo "Rol inválido." >&2
            return 1
            ;;
    esac
}

crear_usuario() {
    local usuario="$1"
    local rol="$2"
    local grupo

    grupo=$(rol_a_grupo "$rol") || return 1

    if id "$usuario" &>/dev/null; then
        echo "El usuario '$usuario' ya existe."
        return 1
    fi

    useradd -m -s /bin/bash -c "Usuario ARENIX ($rol)" "$usuario"
    usermod -aG "$grupo" "$usuario"

    if [[ "$grupo" == "$ROL_ADMINISTRADOR" ]]; then
        usermod -aG wheel "$usuario"
    fi

    echo
    read -rsp "Contraseña temporal: " contrasena
    echo

    echo "$usuario:$contrasena" | chpasswd
    chage -d 0 "$usuario"

    echo "Usuario creado correctamente."
}

eliminar_usuario() {
    local usuario="$1"

    if ! id "$usuario" &>/dev/null; then
        echo "El usuario no existe."
        return 1
    fi

    userdel -r "$usuario"

    echo "Usuario eliminado."
}

cambiar_rol() {
    local usuario="$1"
    local nuevo_rol="$2"
    local grupo

    grupo=$(rol_a_grupo "$nuevo_rol") || return 1

    if ! id "$usuario" &>/dev/null; then
        echo "El usuario no existe."
        return 1
    fi

    usermod -G "$grupo" "$usuario"

    if [[ "$grupo" == "$ROL_ADMINISTRADOR" ]]; then
        usermod -aG wheel "$usuario"
    else
        gpasswd -d "$usuario" wheel &>/dev/null
    fi

    echo "Rol actualizado."
}

bloquear_usuario() {
    local usuario="$1"

    if ! id "$usuario" &>/dev/null; then
        echo "El usuario no existe."
        return 1
    fi

    passwd -l "$usuario"

    echo "Cuenta bloqueada."
}

desbloquear_usuario() {
    local usuario="$1"

    if ! id "$usuario" &>/dev/null; then
        echo "El usuario no existe."
        return 1
    fi

    passwd -u "$usuario"

    echo "Cuenta desbloqueada."
}

restablecer_contrasena() {
    local usuario="$1"

    if ! id "$usuario" &>/dev/null; then
        echo "El usuario no existe."
        return 1
    fi

    passwd "$usuario"
    chage -d 0 "$usuario"

    echo "Contraseña actualizada."
}

info_usuario() {
    local usuario="$1"

    if ! id "$usuario" &>/dev/null; then
        echo "El usuario no existe."
        return 1
    fi

    echo "Información del usuario:"
    echo

    id "$usuario"
    echo
    chage -l "$usuario"
}

mostrar_ayuda() {
    echo "Uso:
    $0 crear <usuario> <rol>
    $0 eliminar <usuario>
    $0 cambiar-rol <usuario> <rol>
    $0 bloquear <usuario>
    $0 desbloquear <usuario>
    $0 resetear-pass <usuario>
    $0 info <usuario>

Roles disponibles:
    admin
    organizador
    participante
    cliente"
}

main() {
    requerir_root

    case "${1:-}" in
        crear)
            [[ $# -eq 3 ]] || { mostrar_ayuda; exit 1; }
            crear_usuario "$2" "$3"
            ;;
        eliminar)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            eliminar_usuario "$2"
            ;;
        cambiar-rol)
            [[ $# -eq 3 ]] || { mostrar_ayuda; exit 1; }
            cambiar_rol "$2" "$3"
            ;;
        bloquear)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            bloquear_usuario "$2"
            ;;
        desbloquear)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            desbloquear_usuario "$2"
            ;;
        resetear-pass)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            restablecer_contrasena "$2"
            ;;
        info)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            info_usuario "$2"
            ;;
        *)
            mostrar_ayuda
            exit 1
            ;;
    esac
}

main "$@"