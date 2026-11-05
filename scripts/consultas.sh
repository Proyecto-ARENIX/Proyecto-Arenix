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
        echo "=== $grupo ==="

        integrantes=$(getent group "$grupo" | awk -F: '{print $4}')

        if [[ -z "$integrantes" ]]; then
            echo "(sin usuarios)"
        else
            IFS=',' read -ra lista <<< "$integrantes"

            for usuario in "${lista[@]}"; do
                [[ -n "$usuario" ]] && echo "$usuario"
            done
        fi
    done
}

contar_usuarios_por_rol() {
    echo "Cantidad de usuarios por rol:"
    echo

    for grupo in "${GRUPOS_ROLES[@]}"; do
        integrantes=$(getent group "$grupo" | awk -F: '{print $4}')

        if [[ -z "$integrantes" ]]; then
            cantidad=0
        else
            cantidad=$(echo "$integrantes" | tr ',' '\n' | wc -l)
        fi

        echo "$grupo : $cantidad"
    done
}

mostrar_usuario() {
    local usuario="$1"

    if ! id "$usuario" &>/dev/null; then
        echo "El usuario no existe."
        return 1
    fi

    echo "Información del usuario:"
    echo

    id "$usuario"
    echo
    groups "$usuario"
}

usuarios_bloqueados() {
    echo "Usuarios bloqueados:"
    echo

    passwd -Sa | awk '$2=="L" {print $1}'
}

usuarios_conectados() {
    echo "Usuarios conectados actualmente:"
    echo

    who
}

ultimos_accesos() {
    echo "Últimos accesos al sistema:"
    echo

    last | head -20
}

reporte_general() {
    echo "=================================="
    echo " REPORTE GENERAL DEL SISTEMA"
    echo "=================================="
    echo

    echo "Fecha:"
    date

    echo
    contar_usuarios_por_rol

    echo
    echo "Usuarios conectados:"
    who

    echo
    echo "Usuarios bloqueados:"
    passwd -Sa | awk '$2=="L" {print $1}'
}

exportar_reporte() {
    archivo="reporte_arenix_$(date +%Y%m%d).txt"

    {
        echo "REPORTE DEL SISTEMA ARENIX"
        echo "Generado: $(date)"
        echo

        echo "USUARIOS POR ROL"
        contar_usuarios_por_rol

        echo
        echo "USUARIOS CONECTADOS"
        who

        echo
        echo "USUARIOS BLOQUEADOS"
        passwd -Sa | awk '$2=="L" {print $1}'
    } > "$archivo"

    echo "Reporte exportado: $archivo"
}

mostrar_ayuda() {
    echo "Uso:
    $0 roles
    $0 usuarios
    $0 conteo
    $0 usuario <nombre>
    $0 bloqueados
    $0 conectados
    $0 accesos
    $0 reporte
    $0 exportar"
}

main() {
    case "${1:-}" in
        roles)
            listar_roles
            ;;
        usuarios)
            listar_usuarios_por_rol
            ;;
        conteo)
            contar_usuarios_por_rol
            ;;
        usuario)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            mostrar_usuario "$2"
            ;;
        bloqueados)
            usuarios_bloqueados
            ;;
        conectados)
            usuarios_conectados
            ;;
        accesos)
            ultimos_accesos
            ;;
        reporte)
            reporte_general
            ;;
        exportar)
            exportar_reporte
            ;;
        *)
            mostrar_ayuda
            exit 1
            ;;
    esac
}

main "$@"