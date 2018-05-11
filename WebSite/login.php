<?php

session_start();

require 'database.php';

$db = getConnection();

$id = $_POST['id'];
$pass = $_POST['pass'];

$req = $db->prepare('SELECT password FROM users WHERE email = :id');
$req->bindParam(':id', $id);
$req->execute();

$hash = $req->fetch()['password'];

if (strcmp($hash, hash('sha256', $pass)) == 0) {
    try {
        $token = bin2hex(random_bytes(20));
    } catch (Exception $e) {
    }
    $req = $db->prepare('INSERT INTO tokens(token, user_email, expiry, client_id) VALUES (:token, :id, FROM_UNIXTIME(:expiry), :clientid) ON DUPLICATE KEY UPDATE token = :token');
    $expiry = time() + 604800;

    $req->bindParam(':token', $token);
    $req->bindParam(':id', $id);
    $req->bindParam(':expiry', $expiry);
    $req->bindParam(':clientid', $_COOKIE['client_id']);
    $req->execute();

    setcookie('token', $token, $expiry);
}

header('Location: index.php');

?>