<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->add(function (Request $request, Response $response, $next){
    $response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
    $response = $response->withAddedHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT');
    $response = $response->withAddedHeader('Access-Control-Allow-Headers', 'Authorization');
    $response = $next($request, $response);
    return $response;
});