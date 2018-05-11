<?php


function get_appartment_number(\PDO $db, \Slim\Http\Request $request)
{
    $auth = $request->getHeaderLine('Authorization');
    if($auth != null)
    {
        $data = explode(' ', $auth);

        if(count($data) >= 2) {
            if ($data[0] == 'Bearer') {
                $req = $db->prepare("SELECT apartment FROM users WHERE email = (SELECT user_email FROM tokens WHERE token = :token);");
                $req->bindParam("token", $data[1]);
                $req->execute();
                return $req->fetch()['apartment'];
            } else if ($data[0] == 'Device') {
                $req = $db->prepare("SELECT apartment FROM devices WHERE id = :id");
                $req->bindParam("id", $data[1]);
                $req->execute();
                return $req->fetch()['apartment'];
            }
        }
    }
    return null;
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