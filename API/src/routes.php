<?php

require 'helper.php';

use Slim\Http\Request;
use Slim\Http\Response;
use WebSocket\Client;

$app->get('/profile', function (Request $request, Response $response, array $args) {
    $number = get_appartment_number($this->db, $request);
    if ($number != null) {
        $req = $this->db->prepare('SELECT firstname, lastname, gender, apartment FROM users WHERE apartment = :number');
        $req->bindParam('number', $number);
        $req->execute();

        $result = $req->fetchAll();
        return $response->withJson($result);
    } else
        return $response->withStatus(401);
});

$app->get('/meters', function (Request $request, Response $response, array $args) {
    $number = get_appartment_number($this->db, $request);
    if ($number != null) {
        $req = $this->db->prepare('SELECT SUM(water) AS water, SUM(elec) AS elec FROM meters WHERE apartment=:number AND date > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MONTH)');
        $req->bindParam('number', $number);
        $req->execute();

        $result = $req->fetchAll();
        return $response->withJson($result);
    } else
        return $response->withStatus(401);
});

$app->post('/meter', function (Request $request, Response $response, array $args) {
    $number = get_appartment_number($this->db, $request);
    if ($number != null) {
        $stmt = $this->db->prepare("INSERT INTO meters(apartment, water, elec) VALUES (:apartment, :water, :elec)");
        $stmt->bindParam(':apartment', $number);
        $stmt->bindParam(':elec', $request->getParam('elec', 0));
        $stmt->bindParam(':water', $request->getParam('water', 0));
        $stmt->execute();
    } else
        return $response->withStatus(401);
});

$app->get('/setpoint', function (Request $request, Response $response, array $args) {
    $number = get_appartment_number($this->db, $request);
    if ($number != null) {
        $req = $this->db->prepare('SELECT setpoint FROM apartments WHERE number=:number');
        $req->bindParam('number', $number);
        $req->execute();
        $result = $req->fetchAll();
        return $response->withJson($result);
    } else
        return $response->withStatus(401);
});

$app->put('/setpoint', function (Request $request, Response $response, array $args) {
    $number = get_appartment_number($this->db, $request);
    if ($number != null) {
        $req = $this->db->prepare('UPDATE apartments SET setpoint=:value WHERE number=:number');
        $value = $request->getParam('value');
        $req->bindParam(':value', $value);
        $req->bindParam(':number', $number);
        $req->execute();

        $req = $this->db->prepare('SELECT id FROM devices WHERE apartment=:number');
        $req->bindParam('number', $number);
        $req->execute();
        $device_id = $req->fetch()['id'];

        $data = array(
            'id' => $device_id,
            'setpoint' => $request->getParam('value')
        );

        try {
            $client = new Client("ws://127.0.0.1:8080/server");
            $client->send(json_encode($data));
            $client->close();
        } catch (WebSocket\ConnectionException $e) {
        }
        return $response;
    } else
        return $response->withStatus(401);
});

$app->post('/login', function (Request $request, Response $response, array $args) {
    $login = $request->getHeaderLine('Authorization');

    if ($login != null) {

        $data = explode(' ', $login);

        if (count($data) >= 2 && $data[0] == 'Basic') {

            $login = explode(':', base64_decode($data[1]));

            if (count($login) >= 2) {

                $id = $login[0];
                $pass = $login[1];

                $req = $this->db->prepare('SELECT password FROM users WHERE email = :id');
                $req->bindParam(':id', $id);
                $req->execute();

                $hash = $req->fetch()['password'];

                if (strcmp($hash, hash('sha256', $pass)) == 0) {
                    $token = bin2hex(random_bytes(20));
                    $req = $this->db->prepare('INSERT INTO tokens(token, user_email, expiry, client_id) VALUES (:token, :id, FROM_UNIXTIME(:expiry), :clientid) ON DUPLICATE KEY UPDATE token = :token');
                    $expiry = time() + 604800;

                    $client_id = $request->getParam('client_id');
                    $req->bindParam(':token', $token);
                    $req->bindParam(':id', $id);
                    $req->bindParam(':expiry', $expiry);
                    $req->bindParam(':clientid', $client_id);
                    $req->execute();
                    $data = array(
                        'token' => $token,
                        'expiry' => $expiry
                    );
                    return $response->withJson($data);
                }
            }
        }
    }
    return $response->withStatus(401);
});

$app->get('/device/{id}', function (Request $request, Response $response, array $args) {
    $req = $this->db->prepare('SELECT apartment FROM devices WHERE id=:id');
    $req->bindParam('id', $args['id']);
    $req->execute();
    $result = $req->fetch();
    return $response->withJson($result);
});