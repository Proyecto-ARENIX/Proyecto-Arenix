#!/bin/bash
# Consultas y monitoreo de la base de datos de ARENIX.

USUARIO="respaldo"
BD="arenix"
TEXTFILE="$HOME/monitoreo/textfile/base_datos.prom"

LIMITE_CONEXIONES=80
LIMITE_DISCO=80

# Muestra el resultado de una consulta como tabla
consulta() {
    mysql -u "$USUARIO" -t "$BD" -e "$1"
}

# Devuelve el resultado de una consulta sin encabezados
valor() {
    mysql -u "$USUARIO" -N -B "$BD" -e "$1"
}

# Valor de una variable de estado
estado_var() {
    valor "SHOW GLOBAL STATUS LIKE '$1'" | awk '{print $2}'
}

# Valor de una variable de configuracion
config_var() {
    valor "SHOW GLOBAL VARIABLES LIKE '$1'" | awk '{print $2}'
}

# Verifica si la base de datos responde
base_responde() {
    valor "SELECT 1" &>/dev/null
}

# Termina el script si la base no responde
requerir_base() {
    if ! base_responde; then
        echo "No se puede conectar a la base de datos '$BD' con el usuario '$USUARIO'."
        echo "Revisar que el servicio este activo (./monitoreo_bd.sh estado) y la contraseña en ~/.my.cnf"
        exit 1
    fi
}

estado() {
    echo "Estado de la base de datos:"
    echo

    servicio_activo="no"
    for s in mariadb mysqld mysql; do
        if systemctl is-active --quiet "$s" 2>/dev/null; then
            echo "Servicio $s : ACTIVO"
            servicio_activo="si"
        fi
    done
    if [[ "$servicio_activo" == "no" ]]; then
        echo "Servicio : INACTIVO (no se encontro mariadb, mysqld ni mysql activo)"
    fi

    if base_responde; then
        echo "Conexion a '$BD' : OK"
        echo "Version          : $(valor 'SELECT VERSION()')"

        segundos=$(estado_var Uptime)
        echo "Encendida hace   : $((segundos / 86400)) dias, $((segundos % 86400 / 3600)) horas, $((segundos % 3600 / 60)) minutos"
    else
        echo "Conexion a '$BD' : FALLA"
        return 1
    fi
}

conexiones() {
    requerir_base

    actuales=$(estado_var Threads_connected)
    maximo=$(config_var max_connections)
    pico=$(estado_var Max_used_connections)
    porcentaje=$((actuales * 100 / maximo))

    echo "Conexiones a la base de datos:"
    echo
    echo "Conectados ahora     : $actuales"
    echo "Maximo permitido     : $maximo"
    echo "Uso                  : $porcentaje %"
    echo "Pico desde el inicio : $pico"

    if [[ "$porcentaje" -ge "$LIMITE_CONEXIONES" ]]; then
        echo
        echo "AVISO: se esta usando mas del $LIMITE_CONEXIONES % de las conexiones permitidas."
    fi
}

procesos() {
    requerir_base

    echo "Consultas en ejecucion ahora:"
    echo

    consulta "SHOW FULL PROCESSLIST"
}

rendimiento() {
    requerir_base

    segundos=$(estado_var Uptime)
    preguntas=$(estado_var Questions)

    if [[ "$segundos" -gt 0 ]]; then
        promedio=$((preguntas / segundos))
    else
        promedio=0
    fi

    echo "Rendimiento de la base de datos:"
    echo
    echo "Consultas totales        : $preguntas"
    echo "Consultas por segundo    : $promedio (promedio)"
    echo "Consultas lentas         : $(estado_var Slow_queries)"
    echo "Conexiones fallidas      : $(estado_var Aborted_connects)"
    echo "Conexiones cortadas      : $(estado_var Aborted_clients)"
}

tamano() {
    requerir_base

    echo "Tamaño de la base '$BD' por tabla:"
    echo

    consulta "SELECT table_name AS tabla,
                     ROUND((data_length + index_length) / 1024 / 1024, 2) AS tamano_mb,
                     table_rows AS filas_aprox
              FROM information_schema.tables
              WHERE table_schema = '$BD'
              ORDER BY (data_length + index_length) DESC"

    total=$(valor "SELECT IFNULL(ROUND(SUM(data_length + index_length) / 1024 / 1024, 2), 0)
                   FROM information_schema.tables
                   WHERE table_schema = '$BD'")
    echo
    echo "Tamaño total: $total MB"
}

# Cantidad exacta de filas de cada tabla
filas() {
    requerir_base

    echo "Cantidad de filas por tabla:"
    echo

    for tabla in $(valor "SHOW TABLES"); do
        cantidad=$(valor "SELECT COUNT(*) FROM \`$tabla\`")
        echo "$tabla : $cantidad"
    done
}

espacio() {
    requerir_base

    carpeta=$(valor "SELECT @@datadir")

    echo "Espacio en disco de la base de datos:"
    echo
    echo "Carpeta de datos : $carpeta"
    echo "Ocupa            : $(du -sh "$carpeta" 2>/dev/null | awk '{print $1}')  (si sale vacio, ejecutar con sudo)"
    echo
    df -h "$carpeta"

    usado=$(df --output=pcent "$carpeta" | tail -1 | tr -dc '0-9')
    if [[ "$usado" -ge "$LIMITE_DISCO" ]]; then
        echo
        echo "AVISO: el disco esta usado al $usado %."
    fi
}

usuarios() {
    requerir_base

    echo "Usuarios registrados por rol:"
    echo

    consulta "SELECT rol, COUNT(*) AS cantidad
              FROM usuarios
              GROUP BY rol
              ORDER BY cantidad DESC"
}

recientes() {
    requerir_base

    echo "Ultimos 10 usuarios registrados:"
    echo

    # No se consulta la contrasena a proposito
    consulta "SELECT id, nombre, correo, rol
              FROM usuarios
              ORDER BY id DESC
              LIMIT 10"
}

buscar() {
    requerir_base

    if [[ -z "${1:-}" ]]; then
        echo "Falta el texto a buscar. Ejemplo: $0 buscar rodrigo"
        exit 1
    fi

    # Se sacan comillas y barras para que el texto no rompa la consulta
    texto="${1//[\'\\]/}"

    echo "Usuarios que contienen '$texto' en el nombre o el correo:"
    echo

    consulta "SELECT id, nombre, correo, rol
              FROM usuarios
              WHERE nombre LIKE '%$texto%' OR correo LIKE '%$texto%'
              ORDER BY id"
}

reporte_general() {
    echo "=================================="
    echo " REPORTE DE LA BASE DE DATOS ARENIX"
    echo "=================================="
    echo
    echo "Fecha: $(date)"
    echo

    estado || return 1
    echo; conexiones
    echo; rendimiento
    echo; tamano
    echo; espacio
    echo; usuarios
    echo; recientes
}

exportar_reporte() {
    archivo="reporte_bd_arenix_$(date +%Y%m%d_%H%M).txt"

    umask 077
    reporte_general > "$archivo"

    echo "Reporte exportado: $archivo"
}

# Guarda valores en un archivo para Prometheus/Grafana
metricas() {
    mkdir -p "$(dirname "$TEXTFILE")"

    if base_responde; then
        activa=1
        conectados=$(estado_var Threads_connected)
        maximo=$(config_var max_connections)
        lentas=$(estado_var Slow_queries)
        tamano_mb=$(valor "SELECT IFNULL(ROUND(SUM(data_length + index_length) / 1024 / 1024, 2), 0)
                           FROM information_schema.tables
                           WHERE table_schema = '$BD'")
    else
        activa=0
    fi

    {
        echo "# HELP arenix_bd_activa 1 si la base de datos responde, 0 si no"
        echo "# TYPE arenix_bd_activa gauge"
        echo "arenix_bd_activa $activa"

        if [[ "$activa" -eq 1 ]]; then
            echo "# HELP arenix_bd_conexiones Conexiones abiertas ahora"
            echo "# TYPE arenix_bd_conexiones gauge"
            echo "arenix_bd_conexiones $conectados"
            echo "# HELP arenix_bd_conexiones_maximo Conexiones maximas permitidas"
            echo "# TYPE arenix_bd_conexiones_maximo gauge"
            echo "arenix_bd_conexiones_maximo $maximo"
            echo "# HELP arenix_bd_consultas_lentas Consultas lentas desde que se encendio la base"
            echo "# TYPE arenix_bd_consultas_lentas counter"
            echo "arenix_bd_consultas_lentas $lentas"
            echo "# HELP arenix_bd_tamano_mb Tamaño de la base en MB"
            echo "# TYPE arenix_bd_tamano_mb gauge"
            echo "arenix_bd_tamano_mb $tamano_mb"
        fi
    } > "$TEXTFILE.tmp"

    mv "$TEXTFILE.tmp" "$TEXTFILE"
    echo "Metricas guardadas en $TEXTFILE"
}

mostrar_ayuda() {
    echo "Uso: $0 <comando>

Monitoreo:
    estado        servicio, conexion, version y tiempo encendida
    conexiones    conexiones actuales y maximas
    procesos      consultas que se estan ejecutando ahora
    rendimiento   consultas por segundo, lentas y conexiones fallidas
    tamano        tamaño de la base y de cada tabla
    filas         cantidad exacta de filas de cada tabla
    espacio       espacio en disco usado por la base

Consultas de datos:
    usuarios      usuarios registrados por rol
    recientes     ultimos 10 usuarios registrados
    buscar <txt>  busca usuarios por nombre o correo

Reportes:
    reporte       resumen completo en pantalla
    exportar      guarda el resumen completo en un archivo
    metricas      guarda valores para Prometheus/Grafana

Tip: para verlo actualizandose cada 5 segundos:  watch -n 5 $0 conexiones"
}

main() {
    case "${1:-}" in
        estado)      estado ;;
        conexiones)  conexiones ;;
        procesos)    procesos ;;
        rendimiento) rendimiento ;;
        tamano)      tamano ;;
        filas)       filas ;;
        espacio)     espacio ;;
        usuarios)    usuarios ;;
        recientes)   recientes ;;
        buscar)      buscar "${2:-}" ;;
        reporte)     reporte_general ;;
        exportar)    exportar_reporte ;;
        metricas)    metricas ;;
        *)
            mostrar_ayuda
            exit 1
            ;;
    esac
}

main "$@"
