var token = window.localStorage.getItem('token');

if (token == null)
    window.location = 'index.html';

function setTemp(temp) {
    $('#temp').html(temp + " °C");
}

$.ajax({
    url: getURLBase() + '/setpoint',
    method: 'get',
    data: {'token': token},
    async: false,
    success: function (msg) {
        var temp = msg[0]['setpoint'];
        $('#setpoint').val(temp);
        setTemp(temp);
    }
});

$.ajax({
    url: getURLBase() + '/meters',
    method: 'get',
    data: {'token': token},
    async: false,
    success: function (msg) {
        $('#electricity').html(msg[0]['elec'] + ' kWh');
        $('#water').html(msg[0]['water'] + ' m³');
    }
});

$('#disconnect').click(function () {
    window.localStorage.removeItem('token');
    window.location = 'index.html';
});

$("#setpoint").bind('input', function () {
    $.ajax({
        url: getURLBase() + '/setpoint',
        method: 'post',
        data: {'token': token, 'value': $('#setpoint').val()},
        success: function (msg) {
            setTemp($('#setpoint').val());
        }
    });
});