<?php

require 'database.php';

if (!isset($_COOKIE['token'])) {
    header('Location: index.php');
    return;
}

if (!userIsAdmin()) {
    header('Location: user.php');
    return;
}

$request_users = getConnection()->prepare('SELECT firstname, lastname, gender, apartment FROM users WHERE admin=0 ORDER BY apartment');
$request_users->execute();

$request_setpoint = getConnection()->prepare('SELECT AVG(setpoint) AS setpoint FROM apartments');
$request_setpoint->execute();

include 'header.php'; ?>

<div class="container">

    <div class="section">
        <div class="row">
            <div class="col l6 s12 center">
                <h5>Consommation électrique de la résidence</h5>
                <div id="electricity-chart" style="height: 400px; width: 100%;"></div>
            </div>
            <div class="col l6 s12 center">
                <h5>Consommation d'eau de la résidence</h5>
                <div id="water-chart" style="height: 400px; width: 100%;"></div>
            </div>
        </div>
    </div>
    <div class="divider"></div>
    <div class="section">
        <div class="row">
            <div class="col s12 center">
                <h5>Température de consigne moyenne du chauffage</h5>
                <a class="info" style="font-size: 42px"><?php echo round($request_setpoint->fetch()['setpoint'], 1) ?> °C</a>
            </div>
        </div>
    </div>
    <div class="divider"></div>
    <div class="section">
        <div class="row">
            <?php foreach ($request_users as $row):

                $rqt = getConnection()->prepare('SELECT SUM(water) AS water, SUM(elec) AS elec FROM meters WHERE apartment=:apartment AND date > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MONTH)');
                $rqt->bindParam(':apartment', $row['apartment']);
                $rqt->execute();

                $meters = $rqt->fetch();

                $rqt = getConnection()->prepare('SELECT setpoint FROM apartments WHERE number=:apartment');
                $rqt->bindParam(':apartment', $row['apartment']);
                $rqt->execute();

                $setpoint = $rqt->fetch()['setpoint'];

                ?>
                <div class="col l4 s12 user-data">
                    <div class="row">
                        <div class="col s12 center" id="profile">
                            <p>
                                <a id="resident"><?php echo ($row['gender'] == 0 ? 'M. ' : 'Mme. ') . strtoupper($row['lastname']) . ' ' . $row['firstname'] ?></a><br/>
                                <a id="apartment">Appartement n°<?php echo $row['apartment'] ?></a>
                            </p>
                        </div>
                    </div>

                    <div class="row s12 center">
                        <h5 class="light">Relevés énergétiques</h5>
                        <p>Des 30 derniers jours</p>
                    </div>

                    <div class="row">
                        <div class="col s6 m3 offset-m3">
                            <p class="energy center">
                                <a>Électricité</a><br/>
                                <i class="material-icons" id="elec-icon">flash_on</i><br/>
                                <a class="info"><?php echo round($meters['elec'] / 1000) ?> kWh</a><br/>
                            </p>
                        </div>
                        <div class="col s6 m3">
                            <p class="energy center">
                                <a>Eau</a><br/>
                                <i class="material-icons" id="water-icon">invert_colors</i><br/>
                                <a class="info"><?php echo round($meters['water'] / 1000) ?> m³</a><br/>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12 center">
                            <h5 class="light">Chauffage (Température de consigne)</h5>
                            <a id="temp" class="info"/><?php echo $setpoint ?> °C</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'footer.php' ?>
