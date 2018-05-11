<?php

require 'config.php';

function getConnection(): PDO
{
    $settings = getConfig();

    $pdo = new PDO("mysql:host=" . $settings['host'] . ";dbname=" . $settings['dbname'], $settings['user'], $settings['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

function userIsAdmin()
{
    $request = getConnection()->prepare('SELECT admin FROM users WHERE email = (SELECT user_email FROM tokens WHERE token = :token)');
    $request->bindParam('token', $_COOKIE['token']);
    $request->execute();
    return $request->fetch()['admin'];
}