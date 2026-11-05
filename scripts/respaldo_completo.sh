#!/bin/bash
# Respaldo completo de ARENIX: base de datos + archivos del sitio.
#
# RPO (maximo de datos que se puede perder): 24 horas -> un respaldo por dia.
# RTO (tiempo maximo para recuperar el sistema): 1 hora -> ver el comando "verificar".

BD="arenix"
USUARIO="respaldo"                       # la contraseña esta en ~/.my.cnf
CARPETA_WEB="/var/www/html"              # archivos del sitio a respaldar
CARPETA_RESPALDOS="/var/backups/arenix"
DIAS_GUARDAR=30

# Destino en la nube configurado con "rclone config" (cuenta de la empresa).
# Dejar vacio para no subir nada.
REMOTO_RCLONE="arenix-empresa:respaldos"

requerir_root() {
    if [[ "$EUID" -ne 0 ]]; then
        echo "Este script debe ejecutarse como root."
        exit 1
    fi
}

registrar() {
    echo "$1"
    logger -t arenix-respaldo "$1"
}

respaldar_bd() {
    local destino="$1"

    # --single-transaction: respalda sin bloquear la base mientras se usa
    # --quick: escribe el archivo de a una fila, sin llenar la memoria
    # --databases: respalda solo la base indicada (incluye CREATE DATABASE)
    mysqldump -u "$USUARIO" --single-transaction --quick --routines \
        --databases "$BD" > "$destino/base_datos.sql"
}

respaldar_todo() {
    local fecha temporal archivo

    fecha=$(date +%Y%m%d_%H%M)
    archivo="$CARPETA_RESPALDOS/arenix_$fecha.tar.gz"

    umask 077
    mkdir -p "$CARPETA_RESPALDOS"
    temporal=$(mktemp -d)

    registrar "Iniciando respaldo..."

    if ! respaldar_bd "$temporal"; then
        registrar "ERROR: fallo el respaldo de la base de datos '$BD'."
        rm -rf "$temporal"
        return 1
    fi

    # Agrupa y comprime todo en un solo archivo .tar.gz
    if ! tar -czf "$archivo" -C "$temporal" . \
            -C "$(dirname "$CARPETA_WEB")" "$(basename "$CARPETA_WEB")"; then
        registrar "ERROR: no se pudo crear $archivo"
        rm -rf "$temporal"
        return 1
    fi

    rm -rf "$temporal"
    registrar "Respaldo creado: $archivo ($(du -h "$archivo" | awk '{print $1}'))"

    subir_nube "$archivo"
    limpiar
}

subir_nube() {
    local archivo="$1"

    if [[ -z "$REMOTO_RCLONE" ]]; then
        return 0
    fi

    if ! command -v rclone &>/dev/null; then
        echo "AVISO: rclone no esta instalado, no se sube a la nube."
        return 1
    fi

    if rclone copy "$archivo" "$REMOTO_RCLONE"; then
        registrar "Copia subida a $REMOTO_RCLONE"
    else
        registrar "ERROR: no se pudo subir el respaldo a $REMOTO_RCLONE"
        return 1
    fi
}

# Borra los respaldos con mas de $DIAS_GUARDAR dias
limpiar() {
    find "$CARPETA_RESPALDOS" -name "arenix_*.tar.gz" -mtime +"$DIAS_GUARDAR" -delete

    if [[ -n "$REMOTO_RCLONE" ]] && command -v rclone &>/dev/null; then
        rclone delete "$REMOTO_RCLONE" --min-age "${DIAS_GUARDAR}d"
    fi

    echo "Respaldos con mas de $DIAS_GUARDAR dias eliminados."
}

listar() {
    echo "Respaldos guardados en $CARPETA_RESPALDOS:"
    echo

    ls -lh "$CARPETA_RESPALDOS"/arenix_*.tar.gz 2>/dev/null || echo "(ninguno)"
}

# Comprueba que el ultimo respaldo no este danado
verificar() {
    local ultimo

    ultimo=$(ls -t "$CARPETA_RESPALDOS"/arenix_*.tar.gz 2>/dev/null | head -1)

    if [[ -z "$ultimo" ]]; then
        echo "No hay respaldos para verificar."
        return 1
    fi

    echo "Verificando $ultimo ..."

    if tar -tzf "$ultimo" &>/dev/null; then
        echo "Respaldo OK ($(date -r "$ultimo" '+%d/%m/%Y %H:%M'))."
    else
        echo "ERROR: el respaldo esta danado."
        return 1
    fi
}

restaurar() {
    local archivo="$1"
    local temporal

    if [[ ! -f "$archivo" ]]; then
        echo "El archivo '$archivo' no existe."
        return 1
    fi

    echo "ATENCION: se va a reemplazar la base '$BD' y los archivos de $CARPETA_WEB."
    read -rp "Escribir SI para continuar: " confirmacion

    if [[ "$confirmacion" != "SI" ]]; then
        echo "Operación cancelada."
        return 0
    fi

    temporal=$(mktemp -d)
    tar -xzf "$archivo" -C "$temporal"

    # root entra a MariaDB por socket, sin contraseña
    mysql < "$temporal/base_datos.sql" || { echo "ERROR al restaurar la base."; rm -rf "$temporal"; return 1; }

    cp -a "$temporal/$(basename "$CARPETA_WEB")/." "$CARPETA_WEB/"

    rm -rf "$temporal"
    registrar "Restauracion completada desde $archivo"
}

mostrar_ayuda() {
    echo "Uso: $0 <comando>

    respaldar              respaldo completo (base de datos + sitio), lo sube y limpia los viejos
    listar                 respaldos guardados
    verificar              comprueba que el ultimo respaldo no este danado
    limpiar                borra respaldos de mas de $DIAS_GUARDAR dias
    restaurar <archivo>    restaura un respaldo

Para que se ejecute solo todos los dias a las 2:00 (sudo crontab -e):
    0 2 * * * $(readlink -f "$0") respaldar >> /var/log/arenix_respaldo.log 2>&1"
}

main() {
    requerir_root

    case "${1:-}" in
        respaldar)  respaldar_todo ;;
        listar)     listar ;;
        verificar)  verificar ;;
        limpiar)    limpiar ;;
        restaurar)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            restaurar "$2"
            ;;
        *)
            mostrar_ayuda
            exit 1
            ;;
    esac
}

main "$@"
