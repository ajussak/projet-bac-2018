#!/usr/bin/python3

import signal
from time import sleep
import RPi.GPIO as GPIO
import requests
import os
import websockets
import asyncio
import logging
from logging.handlers import RotatingFileHandler
from openzwave.network import ZWaveNetwork
from openzwave.option import ZWaveOption
import curses

# Initialisation des constantes
api_rest_url = "http://api.unisvertcite.ovh/"
api_websocket_url = "ws://api.unisvertcite.ovh:8080/"
pin_elec = 5
pin_water = 6
pid_file_path = "/run/home_console.pid"
device_id = "58ae18d9-222f-11e8-a4e8-78929c4cab12"
device = "/dev/ttyACM0"

# Initialisation des variables
water = 0
elec = 0

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger('UVC Home Console')
formatter = logging.Formatter('%(asctime)s ::  %(timedate)s :: %(levelname)s :: %(message)s')
file_handler = RotatingFileHandler('activity.log', 'a', 1000000, 1)
file_handler.setLevel(logging.INFO)
file_handler.setFormatter(formatter)
logger.addHandler(file_handler)

stdscr = curses.initscr()


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
        # Envoi des relevés au serveur
        requests.post(api_rest_url + "meter", data={'elec': elec, 'water': water},
                      headers={'Authorization': 'Device ' + device_id})
        # Réinitialisation du comptage
        elec = 0
        water = 0
        logger.info('Report send')


def send_setpoint(setpoint):
    for node in network.nodes.values():
        if node.generic == 8:
            node.set_thermostat_heating(float(setpoint))


# Fonction de communication avec le module temps réel de l'API
async def websocket_connection(uri):
    async with websockets.connect(uri) as websocket:
        await websocket.send(device_id)
        response = await websocket.recv()
        if response == "YES":
            logger.info('WebSocket : Connected')
            while True:
                setpoint = await websocket.recv()
                logger.info('Setpoint : ' + setpoint)
                send_setpoint(setpoint)
        else:
            logger.error('WebSocket : Authentication failed')


# Début du programme principal

# Enregistrement de l'identifiant de processus dans un fichier
pid_file = open(pid_file_path, "w")
pid_file.write(str(os.getpid()))
pid_file.close()

# Configuration des entrées/soties
GPIO.setmode(GPIO.BCM)
GPIO.setup(pin_elec, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.setup(pin_water, GPIO.IN, pull_up_down=GPIO.PUD_UP)

# Configuration des interruptions
signal.signal(42, signal_handler)
GPIO.add_event_detect(pin_elec, GPIO.FALLING, callback=on_elec_tick)
GPIO.add_event_detect(pin_water, GPIO.FALLING, callback=on_water_tick)

# Initialisation du ZWave
options = ZWaveOption(device, config_path="/usr/local/lib/python3.5/dist-packages/python_openzwave/ozw_config/",
                      user_path=".", cmd_line="")
options.set_console_output(False)
options.lock()
network = ZWaveNetwork(options, autostart=False)
network.start()


def main(stdscr):
    global elec
    global water
    stdscr.clear()
    while True:
        stdscr.move(0, 0)
        stdscr.clrtoeol()
        stdscr.addstr(0, 0, 'Electricity : {} W/h'.format(elec))
        stdscr.move(1, 0)
        stdscr.clrtoeol()
        stdscr.addstr(1, 0, 'Water : {} L'.format(water))
        stdscr.refresh()


curses.wrapper(main)


while True:
    # Lancer la connection au module temp réel et attendre la fin de la connection
    try:
        logger.info('Connecting...')
        asyncio.get_event_loop().run_until_complete(websocket_connection(api_websocket_url))
    except Exception as e:
        logger.error(e)
    sleep(5)