#!/usr/bin/python3

import asyncio
import websockets
import requests
import json

# Déclaration des constantes
api_rest_url = "http://api.unisvertcite.ovh/"

# Déclaration des variables
connections = dict()

async def on_message(socket, path):
    if(path == '/'): # Quand un Rapberry Pi se connecte
        #Identification
        id = await socket.recv()
        data = json.loads(requests.get(api_rest_url + 'device/' + id).text)
        if(data == None):
            await socket.send('NOPE')
        else:
            await socket.send('YES')
            connections[id] = socket
            # Maintenir la connection
            while True:
                try:
                    await asyncio.wait_for(socket.recv(), timeout=20)
                except asyncio.TimeoutError:
                    try:
                        pong_waiter = await socket.ping()
                        await asyncio.wait_for(pong_waiter, timeout=10)
                    except (asyncio.TimeoutError, websockets.exceptions.ConnectionClosed):
                        del connections[id]
                        break
    elif(path == '/server'): # Quand la le module REST de l'API se connecte
        if(socket.remote_address[0] == '127.0.0.1'): # Vérification si la connection vient bien du serveur du module REST
            msg = await socket.recv()
            data = json.loads(msg)
            if(data['id'] in connections):
                con = connections[data['id']]
                await con.send(data['setpoint'])

# Début du programme principal

asyncio.get_event_loop().run_until_complete(websockets.serve(on_message, '0.0.0.0', 8080))
asyncio.get_event_loop().run_forever()
