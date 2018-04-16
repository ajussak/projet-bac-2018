var token = window.localStorage.getItem('token');

if (token == null)
    window.location = 'index.html';

$.ajaxSetup({
    headers: { 'Authorization': 'Bearer ' + token }
});

function setTemp(temp) {
    $('#temp').html(temp + " °C");
}


function updateData() {
    $.ajax({
        url: getURLBase() + '/setpoint',
        success: function (msg) {
            var temp = msg[0]['setpoint'];
            $('#setpoint').val(temp);
            setTemp(temp);
        }
    });

    $.ajax({
        url: getURLBase() + '/meters',
        success: function (msg) {
            $('#electricity').html(msg[0]['elec'] + ' kWh');
            $('#water').html(msg[0]['water'] + ' m³');
        }
    });

    $.ajax({
        url: getURLBase() + '/profile',
        success: function (msg) {
            $('#apartment').html('Appartement n°' + msg[0]['apartment']);
            var gender = msg[0]['gender'] ? 'M. ' : 'Mme. ';
            $('#resident').html(gender + msg[0]['lastname'].toUpperCase() + ' ' + msg[0]['firstname']);
        }
    })
}

$('#disconnect').click(function () {
    window.localStorage.removeItem('token');
    window.location = 'index.html';
});

$("#setpoint").bind('input', function () {
    $.ajax({
        url: getURLBase() + '/setpoint',
        method: 'put',
        data: {'value': $('#setpoint').val()},
        success: function (msg) {
            setTemp($('#setpoint').val());
        }
    });
});

$("#refresh").click(function () {
    updateData();
});

updateData();