#!/usr/bin/python3

import asyncio
import websockets
import requests
import json
import logging
from logging.handlers import RotatingFileHandler

# Déclaration des constantes
api_rest_url = "http://api.unisvertcite.ovh/"

# Déclaration des variables
connections = dict()

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger('UVC WebSocket API')
formatter = logging.Formatter('%(asctime)s ::  %(timedate)s :: %(levelname)s :: %(message)s')
file_handler = RotatingFileHandler('activity.log', 'a', 1000000, 1)
file_handler.setLevel(logging.INFO)
file_handler.setFormatter(formatter)
logger.addHandler(file_handler)


async def on_message(socket, path):
    if path == '/':  # Quand un Rapberry Pi se connecte
        # Identification
        id = await socket.recv()
        data = json.loads(requests.get(api_rest_url + 'device/' + id).text)
        if data is None:
            await socket.send('NOPE')
        else:
            await socket.send('YES')
            connections[id] = socket
            logger.info("{} connected {}".format(id, socket.remote_address))
            # Maintenir la connection
            while True:
                try:
                    await asyncio.wait_for(socket.recv(), timeout=20)
                except asyncio.TimeoutError:
                    pong_waiter = await socket.ping()
                    await asyncio.wait_for(pong_waiter, timeout=10)
                except websockets.exceptions.ConnectionClosed:
                    del connections[id]
                    logger.info("{} disconnected".format(id))
                    break
    elif path == '/server':  # Quand le module REST de l'API se connecte
        if socket.remote_address[0] == '127.0.0.1':
            msg = await socket.recv()
            data = json.loads(msg)
            if data['id'] in connections:
                con = connections[data['id']]
                await con.send(data['setpoint'])
                logger.info("{}°C sent to {}".format(data['setpoint'], data['id']))


# Début du programme principal

asyncio.get_event_loop().run_until_complete(websockets.serve(on_message, '0.0.0.0', 8080))
asyncio.get_event_loop().run_forever()
