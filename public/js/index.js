/* globals $, columnChart, google, lineChart, pieChart, stackedColumnChart, areaChart */

$(function () {
    "use strict";
    if (typeof google !== 'undefined') {
        // do google charts:
        google.setOnLoadCallback(drawChart);
    } else {
        // do chart JS stuff.
        drawChart();
    }

    $.getJSON('json/tour').success(function (data) {
        var tour = new Tour({steps: data.steps, template: data.template});
        // Initialize the tour
        tour.init();
        // Start the tour
        tour.start();
    });


});

function drawChart() {
    "use strict";
    areaChart('chart/account/frontpage', 'accounts-chart');
    pieChart('chart/bill/frontpage', 'bills-chart');
    stackedColumnChart('chart/budget/frontpage', 'budgets-chart');
    columnChart('chart/category/frontpage', 'categories-chart');


    getBoxAmounts();
}

function getBoxAmounts() {
    "use strict";
    var boxes = ['in', 'out', 'bills-unpaid', 'bills-paid'];
    for (var x in boxes) {
        var box = boxes[x];
        $.getJSON('/json/box/' + box).success(putData).fail(failData);
    }
}

function putData(data) {
    "use strict";
    $('#box-' + data.box).html(data.amount);
}

function failData() {
    "use strict";
    console.log('Failed to get box!');
}