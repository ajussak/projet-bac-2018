<?php


function get_number_from_token(\PDO $db, string $token)
{
    $request = $db->prepare("SELECT apartment FROM users WHERE email = (SELECT user_email FROM tokens WHERE token = :token);");
    $request->bindParam("token", $token);
    $request->execute();
    return $request->fetch()['apartment'];
}

function get_number_from_deviceid(\PDO $db, string $id)
{
    $req = $db->prepare('SELECT apartment FROM devices WHERE id=:id');
    $req->bindParam('id', $id);
    $req->execute();
    return $req->fetch()['apartment'];
}

function is_admin(\PDO $db, string $token): bool
{
    $request = $db->prepare("SELECT admin FROM users WHERE email = (SELECT user_email FROM tokens WHERE token = :token);");
    $request->bindParam("token", $token);
    $request->execute();

    $result = $request->fetch();

    return $result != null ? $result : false;
}

?>