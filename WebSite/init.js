(function($){
    $(function(){

        $('.sidenav').sidenav();

    }); // end of document ready
})(jQuery); // end of jQuery name space

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

$("#setpoint").bind('input', function () {
    $.ajax({
        url: 'http://api.unisvertcite.ovh/setpoint',
        method: 'put',
        headers: { 'Authorization': 'Bearer ' + getCookie('token')},
        data: {'value': $('#setpoint').val()},
        success: function (msg) {
            $('#temp').html($('#setpoint').val() + ' °C');
        }
    });
});

window.onload = function() {

    function renderChart(id, dataPoints, unit)
    {
        var chart = new CanvasJS.Chart(id, {
            animationEnabled: true,
            theme: "light2",
            axisY: {
                titleFontSize: 24,
                suffix: " " + unit,
                minimum: 0
            },
            data: [{
                type: "line",
                yValueFormatString: "#0.## " + unit,
                dataPoints: dataPoints
            }]
        });
        chart.render();
    }

    function addData(data) {
        var elecPoints = [];
        var waterPoints = [];

        for (var i = 0; i < data.length; i++) {
            var date = new Date(data[i]['date']);
            elecPoints.push({
                x: date,
                y: data[i]['elec'] / 1000
            });
            waterPoints.push({
                x: date,
                y: data[i]['water'] / 1000
            });
        }
        renderChart('water-chart', waterPoints, ' m³');
        renderChart('electricity-chart', elecPoints, ' kWh');
    }

    $.getJSON(window.location.origin + "/chart.php", addData);

};