<?php

require 'database.php';

session_start();

if(!isset($_COOKIE['token'])) {
    header('Location: index.php');
    return;
}

if(userIsAdmin()) {
    header('Location: admin.php');
    return;
}

$request = getConnection()->prepare('SELECT firstname, lastname, gender, apartment FROM users WHERE email=(SELECT user_email FROM tokens WHERE token=:token)');
$request->bindParam(':token', $_COOKIE['token']);
$request->execute();
$userInfo = $request->fetch();

$request = getConnection()->prepare('SELECT SUM(water) AS water, SUM(elec) AS elec FROM meters WHERE apartment=:apartment AND date > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MONTH)');
$request->bindParam(':apartment',$userInfo['apartment']);
$request->execute();
$meters = $request->fetch();

$request = getConnection()->prepare('SELECT setpoint FROM apartments WHERE number=:apartment');
$request->bindParam(':apartment',$userInfo['apartment']);
$request->execute();
$setpoint = $request->fetch()['setpoint'];

$water = $meters['water'] + 0;
$elec = $meters['elec'] + 0;

?>

<?php include 'header.php'; ?>

<div class="container">

    <div class="row">
        <div class="col s12 center" id="profile">
            <p>
                <a id="resident"><?php echo ($userInfo['gender'] == 0 ? 'M. ' : 'Mme. ') . strtoupper($userInfo['lastname']) . ' ' . $userInfo['firstname'] ?></a><br />
                <a id="apartment">Appartement n°<?php echo $userInfo['apartment'] ?></a>
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
                <a>Électricité</a><br />
                <i class="material-icons" id="elec-icon">flash_on</i><br/>
                <a class="info"><?php echo round($elec / 1000) ?> kWh</a><br />
                <a>(<?php echo $elec ?> Wh)</a>
            </p>
        </div>
        <div class="col s6 m3">
            <p class="energy center">
                <a>Eau</a><br />
                <i class="material-icons" id="water-icon">invert_colors</i><br/>
                <a class="info"><?php echo round($water / 1000) ?> m³</a><br />
                <a>(<?php echo $water ?> L)</a>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 center">
            <h5 class="light">Chauffage (Température de consigne)</h5>
            <a id="temp" class="info"/><?php echo $setpoint ?> °C</a>
        </div>
        <div class="col s6 offset-s3">
        <form action="#">
            <p class="range-field center">
                <input type="range" id="setpoint" min="4" step="0.5" max="28" value="<?php echo $setpoint ?>"/>
            </p>
        </form>
    </div>
    </div>
</div>

<?php include 'footer.php'; ?>
