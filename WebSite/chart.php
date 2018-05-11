<?php

require 'database.php';

if(userIsAdmin()) {

    $request = getConnection()->prepare('SELECT SUM(water) AS water, SUM(elec) AS elec, DATE(date) AS date FROM meters GROUP BY DATE(date)');
    $request->execute();

    echo json_encode($request->fetchAll());
}
