import serial
import mysql.connector
import time
import re
import smtplib
from email.message import EmailMessage
import mysql.connector

# Configuración del puerto USB (Revisar en Administrador de Dispositivos de Windows)
PUERTO_COM = 'COM3' 
BAUD_RATE = 9600

def conectar_bd():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="", # Contraseña vacía en Laragon
        database="sistema_servidores"
    )

def iniciar_lectura():
    try:
        # Iniciar conexión serial
        puerto_serie = serial.Serial(PUERTO_COM, BAUD_RATE, timeout=1)
        print(f"Escuchando termómetro en {PUERTO_COM}...")
        
        while True:
            if puerto_serie.in_waiting > 0:
                linea = puerto_serie.readline().decode('utf-8', errors='ignore').strip()
                
                # Extraer el valor numérico de la cadena recibida (ej: si envía "Temp: 24.5C")
                numeros = re.findall(r"[-+]?\d*\.\d+|\d+", linea)
                
                if numeros:
                    temperatura = float(numeros[0])
                    print(f"Temperatura guardada: {temperatura}°C")
                    
                    # Guardar en la base de datos MySQL
                    conexion = conectar_bd()
                    cursor = conexion.cursor()
                    cursor.execute(
                        "INSERT INTO historial_temperaturas (temperatura, humedad) VALUES (%s, NULL)", 
                        (temperatura,)
                    )
                    conexion.commit()
                    cursor.close()
                    conexion.close()
            # (Código dentro de tu bucle while)
            cursor.execute("SELECT temp_alta, temp_apagado_servers FROM configuracion_sistema WHERE id = 1")
            umbrales = cursor.fetchone()
            temp_alta = float(umbrales[0])
            temp_apagado = float(umbrales[1])

            if temperatura >= temp_alta:
                cursor.execute("SELECT email FROM contactos")
                lista_correos = [fila[0] for fila in cursor.fetchall()]
                
                if lista_correos:
                    if temperatura >= temp_apagado:
                        asunto = "🔥 PELIGRO CRÍTICO: Servidores en riesgo de sobrecalentamiento"
                        cuerpo = f"La temperatura alcanzó los {temperatura}°C. Se ha superado el límite de apagado ({temp_apagado}°C)."
                    else:
                        asunto = "⚠️ ALERTA: Temperatura elevada en Sala de Servidores"
                        cuerpo = f"La temperatura actual es de {temperatura}°C, superando el umbral preventivo de {temp_alta}°C."
                        
                    enviar_alerta_correo(lista_correos, asunto, cuerpo)
                    
            # Pausa de 30 segundos entre guardados para no saturar la base de datos
            time.sleep(30)
            
    except serial.SerialException:
        print(f"Error: No se pudo abrir el puerto {PUERTO_COM}. Verifica la conexión.")
    except Exception as e:
        print(f"Error inesperado: {e}")

if __name__ == "__main__":
    iniciar_lectura()




# Credenciales del correo emisor
EMAIL_REMITENTE = "marianoalopez99@gmail.com"
PASSWORD_APP = "qpla aoxt kjag flsm" 

def enviar_alerta_correo(destinatarios, asunto, mensaje):
    try:
        msg = EmailMessage()
        msg['Subject'] = asunto
        msg['From'] = EMAIL_REMITENTE
        msg['To'] = ", ".join(destinatarios) # Une todos los correos separados por coma
        msg.set_content(mensaje)

        # Conexión segura al servidor SMTP de Gmail
        with smtplib.SMTP_SSL('smtp.gmail.com', 465) as servidor:
            servidor.login(EMAIL_REMITENTE, PASSWORD_APP)
            servidor.send_message(msg)
        print("Correos de alerta enviados exitosamente.")
    except Exception as e:
        print(f"Error al enviar la alerta: {e}")