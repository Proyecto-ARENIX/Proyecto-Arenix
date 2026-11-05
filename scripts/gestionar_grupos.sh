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
    if [[ "$EUID" -ne 0 ]]; then
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

listar_grupos() {
    echo "Grupos de ARENIX:"
    echo

    for grupo in "${GRUPOS_ROLES[@]}"; do
        if getent group "$grupo" >/dev/null; then
            echo "✓ $grupo"
        else
            echo "✗ $grupo (no existe)"
        fi
    done
}

verificar_grupo() {
    local grupo="$1"

    if getent group "$grupo" >/dev/null; then
        echo "El grupo '$grupo' existe."
    else
        echo "El grupo '$grupo' no existe."
    fi
}

eliminar_grupo() {
    local grupo="$1"

    if ! getent group "$grupo" >/dev/null; then
        echo "El grupo '$grupo' no existe."
        return 1
    fi

    read -rp "¿Eliminar el grupo '$grupo'? (s/n): " confirmacion

    if [[ "$confirmacion" =~ ^[Ss]$ ]]; then
        groupdel "$grupo"
        echo "Grupo eliminado: $grupo"
    else
        echo "Operación cancelada."
    fi
}

mostrar_ayuda() {
    echo "Uso:
    $0 init
    $0 listar
    $0 verificar <grupo>
    $0 eliminar <grupo>

Ejemplos:
    $0 init
    $0 listar
    $0 verificar arenix_admin
    $0 eliminar arenix_cliente"
}

main() {
    requerir_root

    case "${1:-}" in
        init)
            crear_grupos
            ;;
        listar)
            listar_grupos
            ;;
        verificar)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            verificar_grupo "$2"
            ;;
        eliminar)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            eliminar_grupo "$2"
            ;;
        *)
            mostrar_ayuda
            exit 1
            ;;
    esac
}

main "$@"