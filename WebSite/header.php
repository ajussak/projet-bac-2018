<?php

if(!isset($_COOKIE['client_id']))
{
    setcookie('client_id', rand() + rand(), time() + (10 * 365 * 24 * 60 * 60));
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
    <title>UVC : Mon énergie</title>

    <!-- CSS  -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css">
    <link href="style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
</head>
<body>
<nav class="light-blue lighten-1" role="navigation">
    <div class="nav-wrapper container">
        <a id="logo-container" href="#" class="brand-logo">Unis Vert Cité : Mon énergie</a>
        <?php if(isset($_COOKIE['token'])) : ?>
        <ul class="right hide-on-med-and-down">
            <li><a href="logout.php">Se déconnecter</a></li>
        </ul>


        <ul id="nav-mobile" class="sidenav">
            <li><a href="logout.php">Se déconnecter</a></li>
        </ul>

        <a href="#" data-target="nav-mobile" class="sidenav-trigger"><i class="material-icons">menu</i></a>
        <?php endif; ?>
    </div>
</nav>
