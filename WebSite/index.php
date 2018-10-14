<?php

require 'database.php';

if(isset($_COOKIE['token']))
{
    header('Location: ' . (userIsAdmin() ? 'admin.php' : 'user.php'));
    return;
}

?>


<?php include 'header.php'; ?>

<div class="container">
    <div class="section">

        <div class="row">
            <div class="col s12 center"><h2>Identifier-vous</h2></div>
        </div>
        <div class="row">
            <form class="col s10 offset-s1 l7 offset-l4" action="login.php" method="post">
                <div class="row">
                    <div class="input-field col s10 l7">
                        <i class="material-icons prefix">account_circle</i>
                        <input name="id" id="id" type="text" class="validate">
                        <label for="id">Identifiant</label>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s10 l7">
                        <i class="material-icons prefix">lock</i>
                        <input name="pass" id="pass" type="password" class="validate">
                        <label for="pass">Mot de passe</label>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s10 l7 center">
                        <button class="btn waves-effect waves-light submit" type="submit">Connexion</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


</div>

<?php include 'footer.php'; ?>
