var myChart;
var myInterval;
var intTime = 1000;
var newIntTime = 1000;
var auto_en = true;
var stop_graph = false;
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
                borderWidth: 1,
                backgroundColor: "black",
                fill: false,
            }],
        },
        options: {
            animation: {
                duration: 100,
            },
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
            $(".progress-bar").animate({width: "0%"}, 0);
            updateChart();
            $(".progress-bar").animate({width: "100%"}, integration_time);
            //console.log("INTERVAL 2",integration_time);
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

function stop_graph_toggle(){
    jQuery.post('/stop_graph',{
        state : stop_graph
    });
}

function disableElement() {
    //console.log("DISABLE BUTTON");
    document.getElementById("save_spectrum").disabled = true;
}

function enableElement() {
    //console.log("ENABLE BUTTON");
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

document.getElementById("stop_graph").addEventListener("click", function() {
    stop_graph = !stop_graph;
    if (stop_graph == true){
        enableElement();
        $("#stop_graph").addClass('button-clicked');
    }
    else{
        if (auto_en == true){
            disableElement();
        }
        $("#stop_graph").addClass('button-not-clicked');
    }
    stop_graph_toggle();
    //console.log("STOP_GRAPH",stop_graph);
}, false);

$(document).ready(function () {
    var getConfig   = jQuery.get("/web_config");
    getConfig.done(function(results){
        //############################################
        //This variable control the enable/disable button of save_spectrum
        //console.log(results.auto_en);
        auto_en = results.auto_en;
        intTime = results.integration_time*1000
        if (auto_en == true){
            disableElement();
            //console.log("DISABLE");
        }
        else{
            enableElement();
            //console.log("ENABLE");
        }
        updateChart();
    });
});

