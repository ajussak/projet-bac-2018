var rand = function () {
    return Math.random().toString(36).substr(2); // remove `0.`
};

var client_id = window.localStorage.getItem('client_id');

if (client_id == null) {
    client_id = rand() + rand();
    window.localStorage.setItem('client_id', client_id);
}

if (window.localStorage.getItem('token') != null)
    window.location = 'panel.html';

$('#action').click(function () {

    var email = $('#login').val();
    var pass = $('#password').val();

    $.ajax(
        {
            url: getURLBase() + '/login',
            method: 'post',
            data: {'id': email, 'password': pass, 'client_id': client_id},
            success: function (result, statut) {
                window.localStorage.setItem('token', result['token']);
                window.location = 'panel.html';
            },
            error: function (result, statut) {
                if (result['status'] == 403) {
                    $("#error").html('Identifiants invalides.');
                }
                else {
                    $("#error").html('Une erreur est survenue.');
                }
            }
        });
});