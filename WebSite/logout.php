<?php

require 'database.php';

$result = getConnection()->prepare('DELETE FROM tokens WHERE token=:token');
$result->bindParam(':token', $_COOKIE['token']);
$result->execute();

setcookie('token', '', time() - 5);

header('Location: index.php');