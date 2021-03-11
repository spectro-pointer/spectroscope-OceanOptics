var myChart;
var myInterval;
var intTime = 1000;
var newIntTime = 1000;
var auto_en = true;
var getData = jQuery.get("/data");
getData.done(function(results) {
    var ctx = document.getElementById('spectrum').getContext('2d');
    var xAxe = results.xAxe;
    var yAxe = results.yAxe;
    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: xAxe,
            datasets: [{
                label: '',
                data: yAxe,
                pointRadius: 0,
                borderColor: "black",
                backgroundColor: "black",
                fill: false,
            }],
        },
        options: {
            events: ['click'],
            scales: {
                xAxes: [{
                            ticks: {
                                max: 0,   //TODO Change by jinja variable
                                min: 1,  //TODO Change by jinja variable
                                stepSize: 1 //TODO Change by jinja variable
                            }
                        }],
                yAxes: [{
                            ticks: {
                                max: 70000,     //TODO Change by jinja variable
                                min: 0,         //TODO Change by jinja variable
                                stepSize: 10000//10000 //TODO Change by jinja variable
                            }
                        }],
          }
        }
    });
});

function addData(chart, label, data) {
    chart.data.labels = label;
    chart.data.datasets.forEach((dataset) => {
        dataset.data = data;
    });
    chart.update();
}

function removeData(chart) {
    chart.data.labels.pop();
    chart.data.datasets.forEach((dataset) => {
        dataset.data.pop();
    });
    chart.update(0);
}

function updateWebConfig(integration_time){

    //############################################
    //This if statement control if the interval for updating the Chart must change
    clearInterval(myInterval);
    //console.log("CONF "+auto_en+" "+intTime+" "+integration_time);
    intTime = integration_time;
    //console.log("CONF "+auto_en+" "+intTime+" "+integration_time);
    myInterval = setInterval(function() {
            updateChart();
    }, integration_time);
}

function updateChart(){
    var updatedData = jQuery.get('/data');
    /*removeData(myChart);*/
    updatedData.done(function(results){
        var xAxe = results.xAxe;
        var yAxe = results.yAxe;
        //############################################
        //Changes data in graph
        addData(myChart,xAxe,yAxe);
        //console.log("OUT "+auto_en+" "+intTime);
    });

    //If auto_en == false, then the manual mode is selected
    if(auto_en == false){
        var getConfig   = jQuery.get("/web_config");
        getConfig.done(function(results){
            newIntTime = results.integration_time*1000;
        });
    }
    //If auto_en == true, then the automatic mode is selected
    else{
        newIntTime = 1000; //TODO Hardcoded value
    }

    if (intTime != newIntTime){
        updateWebConfig(newIntTime);
    }
    //console.log("IN  "+auto_en+" "+intTime+" "+newIntTime);

}

/*This function selects between automatic mode and manual mode*/
function select_mode(mode){
    jQuery.post('/select_mode', {
            select_mode: mode
    });
}

function save_spectrum(){
    jQuery.post('/save_spectrum');
}

function disableElement() {
  document.getElementById("save_spectrum").disabled = true;
}

function enableElement() {
  document.getElementById("save_spectrum").disabled = false;
}

document.getElementById("automatic").addEventListener("click", function() {
    disableElement();
    select_mode("automatic");
    auto_en = true;
}, false);

document.getElementById("manual").addEventListener("click", function() {
    enableElement();
    select_mode("manual");
    auto_en = false;
}, false);

document.getElementById("save_spectrum").addEventListener("click", function() {
    save_spectrum();
}, false);

$(document).ready(function () {
    var getConfig   = jQuery.get("/web_config");
    getConfig.done(function(results){
        //############################################
        //This variable control the enable/disable button of save_spectrum
        //console.log(results.auto_en);
        if (results.auto_en == true){
            disableElement();
            //console.log("DISABLE");
        }
        else{
            enableElement();
            //console.log("ENABLE");
        }

        //############################################
        //Sets the first integration time
        intTime = results.integration_time*1000
        myInterval = setInterval(function() {
            updateChart();
        }, intTime);
        //console.log("HERE "+intTime);
    });
});

