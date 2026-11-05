#!/bin/bash
# Gestion general del servidor de ARENIX.

SERVICIOS=(sshd firewalld httpd mariadb mysqld crond chronyd auditd)

LIMITE_RECURSOS=80
CARPETA_SCRIPTS="$(dirname "$(readlink -f "$0")")"

requerir_root() {
    if [[ "$EUID" -ne 0 ]]; then
        echo "Este script debe ejecutarse como root."
        exit 1
    fi
}

# Verifica si un servicio esta instalado
servicio_instalado() {
    systemctl list-unit-files "$1.service" 2>/dev/null | grep -q "^$1.service"
}

estado() {
    echo "Estado del servidor:"
    echo

    echo "Nombre      : $(hostname)"
    echo "Sistema     : $(grep PRETTY_NAME /etc/os-release | cut -d'"' -f2)"
    echo "Kernel      : $(uname -r)"
    echo "Encendido   : $(uptime -p)"
    echo "Fecha       : $(date)"
    echo "IP          : $(hostname -I | awk '{print $1}')"
    echo "Carga (1/5/15 min) : $(awk '{print $1, $2, $3}' /proc/loadavg)"
}

recursos() {
    echo "Uso de recursos:"
    echo

    nucleos=$(nproc)
    carga=$(awk '{print $1}' /proc/loadavg)
    memoria=$(free | awk '/^Mem:/ {printf "%d", $3 * 100 / $2}')
    disco=$(df --output=pcent / | tail -1 | tr -dc '0-9')

    echo "Nucleos de CPU : $nucleos (carga actual: $carga)"
    echo "Memoria usada  : $memoria %"
    echo "Disco (/)      : $disco %"

    if [[ "$memoria" -ge "$LIMITE_RECURSOS" ]]; then
        echo "AVISO: la memoria esta usada por encima del $LIMITE_RECURSOS %."
    fi

    if [[ "$disco" -ge "$LIMITE_RECURSOS" ]]; then
        echo "AVISO: el disco esta usado por encima del $LIMITE_RECURSOS %."
    fi

    echo
    echo "Los 5 procesos que mas memoria usan:"
    ps -eo pid,user,%mem,%cpu,comm --sort=-%mem | head -6
}

servicios() {
    echo "Servicios de ARENIX:"
    echo

    for s in "${SERVICIOS[@]}"; do
        if servicio_instalado "$s"; then
            if systemctl is-active --quiet "$s"; then
                echo "$s : ACTIVO"
            else
                echo "$s : INACTIVO"
            fi
        fi
    done
}

controlar_servicio() {
    local accion="$1"
    local servicio="$2"
    local permitido="no"

    for s in "${SERVICIOS[@]}"; do
        if [[ "$s" == "$servicio" ]]; then
            permitido="si"
        fi
    done

    if [[ "$permitido" == "no" ]]; then
        echo "Servicio no permitido. Los servicios disponibles son: ${SERVICIOS[*]}"
        return 1
    fi

    if ! servicio_instalado "$servicio"; then
        echo "El servicio '$servicio' no esta instalado."
        return 1
    fi

    if [[ "$accion" == "stop" && "$servicio" == "sshd" ]]; then
        echo "No se puede detener sshd: se perderia el acceso remoto al servidor."
        return 1
    fi

    systemctl "$accion" "$servicio"

    if systemctl is-active --quiet "$servicio"; then
        echo "$servicio : ACTIVO"
    else
        echo "$servicio : INACTIVO"
    fi
}

puertos() {
    echo "Puertos en escucha:"
    echo

    ss -tulpn
}

firewall() {
    echo "Firewall:"
    echo

    echo "Estado: $(firewall-cmd --state)"
    echo
    firewall-cmd --list-all
}

red() {
    echo "Red:"
    echo

    ip -brief address
    echo
    echo "Puerta de enlace: $(ip route | awk '/default/ {print $3}')"

    if ping -c 1 -W 2 8.8.8.8 &>/dev/null; then
        echo "Conexion a Internet: OK"
    else
        echo "Conexion a Internet: SIN RESPUESTA"
    fi
}

actualizaciones() {
    echo "Actualizaciones disponibles:"
    echo

    dnf check-update
    if [[ $? -eq 0 ]]; then
        echo "El sistema esta al dia."
    fi
}

actualizar() {
    read -rp "¿Instalar todas las actualizaciones? (s/n): " confirmacion

    if [[ "$confirmacion" =~ ^[Ss]$ ]]; then
        dnf upgrade -y
        echo "Actualizacion terminada."
    else
        echo "Operación cancelada."
    fi
}

logs() {
    echo "Ultimos errores del sistema:"
    echo

    journalctl -p err -n 20 --no-pager
}

accesos_fallidos() {
    echo "Ultimos intentos de acceso fallidos por SSH:"
    echo

    intentos=$(grep -E "Failed password|Invalid user" /var/log/secure 2>/dev/null | tail -10)

    if [[ -z "$intentos" ]]; then
        echo "(ninguno)"
    else
        echo "$intentos"
    fi
}

respaldo() {
    echo "Ejecutando respaldo completo..."

    "$CARPETA_SCRIPTS/respaldo_completo.sh"
}

reiniciar_equipo() {
    echo "ATENCION: se va a reiniciar el servidor y se cortaran todas las conexiones."
    read -rp "Escribir SI para continuar: " confirmacion

    if [[ "$confirmacion" == "SI" ]]; then
        systemctl reboot
    else
        echo "Operación cancelada."
    fi
}

reporte_general() {
    echo "=================================="
    echo " REPORTE DEL SERVIDOR ARENIX"
    echo "=================================="
    echo

    estado
    echo; recursos
    echo; servicios
    echo; puertos
    echo; accesos_fallidos
}

exportar_reporte() {
    archivo="reporte_servidor_arenix_$(date +%Y%m%d_%H%M).txt"

    umask 077
    reporte_general > "$archivo"

    echo "Reporte exportado: $archivo"
}

mostrar_ayuda() {
    echo "Uso: $0 <comando>

Informacion:
    estado             datos generales del servidor
    recursos           CPU, memoria, disco y procesos que mas consumen
    servicios          estado de los servicios de ARENIX
    puertos            puertos en escucha
    firewall           configuracion del firewall
    red                direcciones IP y conexion a Internet
    logs               ultimos errores del sistema
    accesos-fallidos   ultimos intentos de acceso fallidos por SSH

Acciones:
    iniciar <servicio>      inicia un servicio
    detener <servicio>      detiene un servicio
    reiniciar <servicio>    reinicia un servicio
    actualizaciones         muestra las actualizaciones disponibles
    actualizar              instala las actualizaciones
    respaldo                ejecuta un respaldo completo
    reiniciar-equipo        reinicia el servidor

Reportes:
    reporte            resumen completo en pantalla
    exportar           guarda el resumen completo en un archivo

Servicios disponibles: ${SERVICIOS[*]}"
}

main() {
    requerir_root

    case "${1:-}" in
        estado)           estado ;;
        recursos)         recursos ;;
        servicios)        servicios ;;
        puertos)          puertos ;;
        firewall)         firewall ;;
        red)              red ;;
        logs)             logs ;;
        accesos-fallidos) accesos_fallidos ;;
        iniciar)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            controlar_servicio start "$2"
            ;;
        detener)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            controlar_servicio stop "$2"
            ;;
        reiniciar)
            [[ $# -eq 2 ]] || { mostrar_ayuda; exit 1; }
            controlar_servicio restart "$2"
            ;;
        actualizaciones)  actualizaciones ;;
        actualizar)       actualizar ;;
        respaldo)         respaldo ;;
        reiniciar-equipo) reiniciar_equipo ;;
        reporte)          reporte_general ;;
        exportar)         exportar_reporte ;;
        *)
            mostrar_ayuda
            exit 1
            ;;
    esac
}

main "$@"
