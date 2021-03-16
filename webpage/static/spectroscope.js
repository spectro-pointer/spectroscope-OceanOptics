var myInterval;
var intTime = 1000;
var newIntTime = 0;
var auto_en = true;
var stop_graph = false;
var getData = jQuery.get("/data");
var g;
getData.done(function(results) {
    var data_x_y = results.data;
    g = new Dygraph(document.getElementById("spectrum"), data_x_y,
                        {
                          drawPoints: true,
                          showRoller: true,
                          valueRange: [0.0, results.max_intensity*1.1],
                          labels: ['Time', 'Random']
                        });
});

function updateWebConfig(integration_time){

    //############################################
    //This if statement control if the interval for updating the Chart must change
    clearInterval(myInterval);
    intTime = integration_time;
    myInterval = setInterval(function() {
        $(".progress-bar").animate({width: "0%"}, 0);
        updateChart();
        $(".progress-bar").animate({width: "100%"}, integration_time);
    }, integration_time);
}

function updateChart(){
    var updatedData = jQuery.get('/data');
    updatedData.done(function(results){
        var data_x_y = results.data;
        //############################################
        //Changes data in graph
        data_x_y = results.data;
        g.updateOptions( { 'file': data_x_y } );
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
        newIntTime = 1000; //Hardcoded value
    }

    if (intTime != newIntTime){
        updateWebConfig(newIntTime);
    }
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

function disableIntegrationFactor() {
    document.getElementById("integration_factor").disabled = true;
}

function disableThreshold() {
    document.getElementById("threshold").disabled = true;
}

function disableElement() {
    document.getElementById("save_spectrum").disabled = true;
}

function enableElement() {
    document.getElementById("save_spectrum").disabled = false;
}

function enableIntegrationFactor() {
    document.getElementById("integration_factor").disabled = false;
}

function enableThreshold() {
    document.getElementById("threshold").disabled = false;
}

function hideProgressBar() {
    document.getElementById("progress_div").style.visibility = "hidden";
}

function showProgressBar() {
    document.getElementById("progress_div").style.visibility = "visible";
}

document.getElementById("automatic").addEventListener("click", function() {
    disableElement();
    select_mode("automatic");
    auto_en = true;
    hideProgressBar();
    disableIntegrationFactor();
    disableThreshold();
}, false);

document.getElementById("manual").addEventListener("click", function() {
    enableElement();
    select_mode("manual");
    auto_en = false;
    showProgressBar();
    enableIntegrationFactor();
    enableThreshold();
}, false);

document.getElementById("save_spectrum").addEventListener("click", function() {
    save_spectrum();
}, false);

document.getElementById("stop_graph").addEventListener("click", function() {
    stop_graph = !stop_graph;
    if (stop_graph == true){
        $("#stop_graph").addClass('button-clicked');
        enableElement();
        hideProgressBar();
    }
    else{
        $("#stop_graph").addClass('button-not-clicked');
        if (auto_en == true){
            disableElement();
        }
        else{
            showProgressBar();
        }
    }
    stop_graph_toggle();
}, false);

$(document).ready(function () {
    var getConfig   = jQuery.get("/web_config");
    getConfig.done(function(results){
        //############################################
        //This variable control the enable/disable button of save_spectrum
        auto_en = results.auto_en;
        intTime = results.integration_time*1000
        if (auto_en == true){
            hideProgressBar();
            disableElement();
            disableIntegrationFactor();
            disableThreshold();
        }
        else{
            showProgressBar();
            enableElement();
            enableIntegrationFactor();
            enableThreshold();
        }
        updateWebConfig(intTime);
        updateChart();
    });
});

