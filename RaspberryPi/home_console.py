#!/usr/bin/python3

import signal
from time import sleep
import RPi.GPIO as GPIO
import requests
import os
import websockets
import asyncio
import logging

# Initialisation des constantes
api_rest_url = "http://api.unisvertcite.ovh/"
api_websocket_url = "ws://api.unisvertcite.ovh:8080/"
pin_elec = 5
pin_water = 6
pid_file_path = "/run/home_console.pid"
device_id = "58ae18d9-222f-11e8-a4e8-78929c4cab12"

# Initialisation des variables
water = 0
elec = 0


# Fonction d'interruption quand le compteur d'eau envoie une impulsion
def on_water_tick(gpio_id):
    global water
    water += 1


# Fonction d'interruption quand le compteur électrique envoie une impulsion
def on_elec_tick(gpio_id):
    global elec
    elec += 1


# Fonction d'interruption quand un programme externe envoie un signal
def signal_handler(signum, frame):
    if signum == 42:
        global device_id
        global water
        global elec
        #Envoi des relevés au serveur
        requests.post(api_rest_url + "meter", data={'deviceid': device_id, 'elec': elec, 'water': water})
        #Réinitialisation du comptage
        elec = 0
        water = 0


# Fonction de communication avec le module temps réel de l'API
async def websocket_connection(uri):
    async with websockets.connect(uri) as websocket:
        await websocket.send(device_id)
        responce = await websocket.recv()
        if responce == "YES":
            while True:
                setpoint = await websocket.recv()
                print(setpoint)

# Début du programme principal

# Enregistrement de l'identifiant de processus dans un fichier
pid_file = open(pid_file_path, "w")
pid_file.write(str(os.getpid()))
pid_file.close()

# Configuration des entrées/soties
GPIO.setmode(GPIO.BCM)
GPIO.setup(pin_elec, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)
GPIO.setup(pin_water, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)

# Configuration des interruptions
signal.signal(42, signal_handler)
GPIO.add_event_detect(pin_elec, GPIO.RISING, callback=on_water_tick)
GPIO.add_event_detect(pin_water, GPIO.RISING, callback=on_elec_tick)

while True:
    # Lancer la connection au module temp réel et attendre la fin de la connection
    try:
        asyncio.get_event_loop().run_until_complete(websocket_connection(api_websocket_url))
    except:
        pass
    sleep(5)


