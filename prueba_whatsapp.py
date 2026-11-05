import requests
import json

# 1. Reemplaza con tus datos de Meta
TOKEN = "EAAYU0cenYDQBS2WvgBkU33KJ65SxiazOcojsnnD0VPyZC41qDjXy3VZAjNffaVa1nIumZAYrH1vmap0qHeyudA8oaI9sGDKYyssE7KZBp6dywcgIxUeaMVScvgqo0HVkPfHeM1HgaVcD8KYzyaXOvukmhFN23HNZAuxEpNpm4fQbQj4VblM1zxgMb3h9a9GNpq6Nt0ySomxQsromfDg8GtTbIRWitT1LxSIEgXjYVivQEkW8SdZBM3PoalSWG35l8n7Bf3tfBe8NjV4OcSUuqgoZBEZD"
ID_NUMERO_REMITENTE = "1351108121429387"

# Tu número personal con código de país (ej. Argentina: 549351...) sin el + ni espacios
NUMERO_DESTINO = "543513606326" 

url = f"https://graph.facebook.com/v20.0/{ID_NUMERO_REMITENTE}/messages"

headers = {
    "Authorization": f"Bearer {TOKEN}",
    "Content-Type": "application/json"
}

# Meta obliga a usar una "plantilla" preaprobada para el primer mensaje.
# Por defecto, todas las cuentas traen la plantilla "hello_world".
data = {
    "messaging_product": "whatsapp",
    "to": NUMERO_DESTINO,
    "type": "template",
    "template": {
        "name": "hello_world",
        "language": {
            "code": "en_US"
        }
    }
}

respuesta = requests.post(url, headers=headers, data=json.dumps(data))

print("Código de estado:", respuesta.status_code)
print("Respuesta de Meta:", respuesta.json())