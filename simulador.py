import mysql.connector
import time
import random
import smtplib
from email.message import EmailMessage
#Configuracion de pings a servidores
import subprocess
import platform


# Configuración de Correo
EMAIL_REMITENTE = "marianoalopez99@gmail.com"
PASSWORD_APP = "qpla aoxt kjag flsm" # REEMPLAZAR AQUÍ

# Configuración de Pings (en segundos)
# 120 = 2 minutos | 3600 = 1 hora
INTERVALO_PING = 20

def conectar_bd():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="sistema_servidores"
    )

def enviar_alerta_correo(destinatarios, asunto, mensaje):
    try:
        msg = EmailMessage()
        msg['Subject'] = asunto
        msg['From'] = EMAIL_REMITENTE
        msg['To'] = ", ".join(destinatarios)
        msg.set_content(mensaje)

        with smtplib.SMTP_SSL('smtp.gmail.com', 465) as servidor:
            servidor.login(EMAIL_REMITENTE, PASSWORD_APP)
            servidor.send_message(msg)
        print(f"✉️ Correos enviados a: {destinatarios}")
    except Exception as e:
        print(f"Error al enviar la alerta: {e}")

def verificar_ping(ip_servidor):
    parametro = "-n" if platform.system().lower() == "windows" else "-c"
    # Ejecuta 1 ping esperando máximo 1000 milisegundos (1 segundo)
    comando = ["ping", parametro, "1", "-w", "1000", ip_servidor]
    resultado = subprocess.run(comando, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    return resultado.returncode == 0

def obtener_intervalos_segundos(cursor):
    cursor.execute("""
        SELECT 
            intervalo_temp_valor, intervalo_temp_unidad,
            intervalo_ping_valor, intervalo_ping_unidad
        FROM configuracion_sistema WHERE id = 1
    """)
    row = cursor.fetchone()
    
    if not row:
        return 15, 30

    temp_val, temp_uni, ping_val, ping_uni = row
    factores = {'segundos': 1, 'minutos': 60, 'horas': 3600}

    seg_temp = temp_val * factores.get(temp_uni, 1)
    seg_ping = ping_val * factores.get(ping_uni, 1)

    return seg_temp, seg_ping



def iniciar_simulacion():
    print("Iniciando simulador de termómetro (Duración programada: 2 minutos)...")

    ultimo_ping = 0
    # 8 iteraciones x 15 segundos de espera = 120 segundos
    for i in range(1, 9):
        temperatura = round(random.uniform(22.0, 32.0), 1)
        print(f"\n[{i}/8] 🌡️ Temperatura leída: {temperatura}°C")
        
        conexion = conectar_bd()
        cursor = conexion.cursor()
        
        # 1. Guardar en base de datos
        cursor.execute("INSERT INTO historial_temperaturas (temperatura, humedad) VALUES (%s, NULL)", (temperatura,))
        conexion.commit()

        # --- Control de tiempo para los Pings ---
        tiempo_actual = time.time()
        if (tiempo_actual - ultimo_ping) >= INTERVALO_PING:
            print("⏱️ Ejecutando pings de rutina a los servidores...")
            
            cursor.execute("SELECT id, ip FROM servidores")
            servidores_db = cursor.fetchall()
            
            for srv_id, ip in servidores_db:
                estado_actual = "Activo" if verificar_ping(ip) else "Apagado"
                # Actualizamos el estado y forzamos el guardado de la hora exacta
                cursor.execute("UPDATE servidores SET estado = %s, ultima_verificacion = NOW() WHERE id = %s", (estado_actual, srv_id))
                    
            conexion.commit()
            ultimo_ping = tiempo_actual # Reiniciamos el cronómetro
        # ----------------------------------------

        
        # 2. Revisar umbrales
        cursor.execute("SELECT temp_alta, temp_apagado_servers FROM configuracion_sistema WHERE id = 1")
        umbrales = cursor.fetchone()
        temp_alta = float(umbrales[0])
        temp_apagado = float(umbrales[1])
        
        # 3. Lógica de alertas
        if temperatura >= temp_alta:
            cursor.execute("SELECT email FROM contactos")
            correos = [fila[0] for fila in cursor.fetchall()]
            
            if correos:
                if temperatura >= temp_apagado:
                    asunto = "🔥 PELIGRO CRÍTICO: Servidores en riesgo de sobrecalentamiento"
                    cuerpo = f"La temperatura alcanzó los {temperatura}°C. Se superó el límite de apagado ({temp_apagado}°C)."
                else:
                    asunto = "⚠️ ALERTA: Temperatura elevada en Sala de Servidores"
                    cuerpo = f"La temperatura actual es de {temperatura}°C, superando el umbral preventivo de {temp_alta}°C."
                    
                enviar_alerta_correo(correos, asunto, cuerpo)
        
        cursor.close()
        conexion.close()
        
        if i < 8:
            print("Esperando 15 segundos para la próxima lectura...")
            time.sleep(15)
            
    print("\n✅ Simulación de 2 minutos finalizada automáticamente.")

if __name__ == "__main__":
    iniciar_simulacion()