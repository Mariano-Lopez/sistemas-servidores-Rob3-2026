import mysql.connector
import time
import random
import smtplib
from email.message import EmailMessage

# Configuración de Correo
EMAIL_REMITENTE = "marianoalopez99@gmail.com"
PASSWORD_APP = "qpla aoxt kjag flsm" # REEMPLAZAR AQUÍ

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

def iniciar_simulacion():
    print("Iniciando simulador de termómetro (Duración programada: 2 minutos)...")
    
    # 8 iteraciones x 15 segundos de espera = 120 segundos
    for i in range(1, 9):
        temperatura = round(random.uniform(22.0, 32.0), 1)
        print(f"\n[{i}/8] 🌡️ Temperatura leída: {temperatura}°C")
        
        conexion = conectar_bd()
        cursor = conexion.cursor()
        
        # 1. Guardar en base de datos
        cursor.execute("INSERT INTO historial_temperaturas (temperatura, humedad) VALUES (%s, NULL)", (temperatura,))
        conexion.commit()
        
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