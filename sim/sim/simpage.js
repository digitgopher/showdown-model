//$(window).load(function() {
define(["jquery","sim/simrun"], function($, simulation) {

/********* ALL THE FORM VALIDATING ***********/
  // Pass the classname of a set of chart selects, and it will ensure that they add to 20
  function chartValidateSum(classname) {
    var sum = 0;
    $("select."+classname).each(function(){
      sum += Number($(this).val());
    });
    if(sum == 20){
      $("select."+classname).css("outline", "inherit");
    }
    else if(sum > 20){
      $("select."+classname).css("outline", "2px solid #CF0000");
    }
    else {
      $("select."+classname).css("outline", "2px solid #FF7D7D");
    }
    console.log(sum);
  }
  // So we can make 9 of these in a loop we need to make a closure!
  function addchartValidateSum(name){
    return $("select."+name).change(function() {
      chartValidateSum(name);
    });
  }
  
  // Do all the charts add to 20? Then yes, you can press the run simulation button!
  // Pass a list of all classname sets that have to add to 20.
  function chartValidateAll(listOfClassnames) {
    // Initialize
    var equalsTwenty = true;
    var sum = 0;
    for (var i = 0; i < listOfClassnames.length; i++){
      sum = 0;
      $("select."+listOfClassnames[i]).each(function(){
        sum += Number($(this).val());
      });
      if(sum != 20){
        $("#run-simulation").prop("disabled",true);
        //console.log(sum);
        return;
        //console.log("00000000000000000");
      }
    }
    // They all equal 20
    $("#run-simulation").prop("disabled",false);
    //console.log(sum);
  }
  
  // Add validators as needed
  function setValidation(numBatters,individualSum20Listeners,batChartClassNames){
    for (var i = 0; i < batChartClassNames.length; i++){
      individualSum20Listeners[i] = addchartValidateSum(batChartClassNames[i]);
    }
    $("select.p1c, select.b1c, select.b2c, select.b3c, select.b4c, select.b5c, select.b6c, select.b7c, select.b8c, select.b9c").change(function() {
      chartValidateAll(batChartClassNames);
    });
  }

  // Initialize validators
  // Making them global on purpose so they can be deleted and reset
  individualSum20Listeners = [];
  batChartClassNames = ["p1c","b1c","b2c","b3c","b4c","b5c","b6c","b7c","b8c","b9c"];
  setValidation(9,individualSum20Listeners,batChartClassNames);
  
  // Change form on user selections
  $("#use-one-bat").change(function() {
    $(".last-eight-batters").toggle();
    $(".lineup-order").toggle();
  });
  $("#use-defense").change(function() {
    $(".defense-input").toggle();
    $(".speed-input").toggle();
  });
  
  // Add the validator to a single group:
  // $("select[name='b1c']").change(function() {
    // chartValidateSum("b1c");
  // });

/********* EVERYTHING THAT HAPPENS WHEN THE RUN SIMULATION BUTTON IS CLICKED ***********/
  $('#run-simulation').on("click", function() {
    // Collect all the input data
    var inputValues = $('#inputForm').serializeArray();
    var batters = []; // Contains a set of (key = batter name, value = batter object) objects
    var batter = {}; // Contains the attributes of a batter and their values
    var pitchers = [];
    // Note: defense is treated the same way as a player for getting the input (set off with a player-name value)
    var defense = {}; // Contains the raw defense array
    var pitcher = {};
    //x.push(inputValues[0].value);
    // The first name parameter we expect
    var curGroup = 'p1c';
    var track = 0;
    var curValue = '';
    var previous_name = '';
    // Create a custom data structure
    for (var i = 0; i < inputValues.length; i++){
      var n = inputValues[i].name;
      var v = inputValues[i].value;
      // New player
      if(n == "player-name"){
        // Push old player and make new one
        batter["name"] = previous_name;
        batters.push(batter);
        batter = {};
        // Save current name
        previous_name = v;
        // Track the order of input sets, as objects are unordered
        batter["order"] = track++;
      }
      else{
        batter[n] = v;
      }
      
    }
    // Push last batter as algorithm ends without finishing the job.
    batters.push(batter);
    // Get rid of first ghost object (product of the algorithm)
    batters.shift();
    // Find and separate the pitcher
    pitchers.push(batters.shift());
    // Separate defense
    defense = batters.pop();
    
    // If only the first is input and the simulation is to be run against that batter only, then fill'er up with batter[0]!
    if ($('#use-one-bat')[0].checked){
      batters = new Array(batters.shift());
      for (var i = 1; i <= 8; i++) {
        batters.push(batters[0]);
      }
    }
    // Throw away defense if it is not checked
    var defenseProcessed = []; // Has, in order, cather throwing, infield, and outfield values
    if ($('#use-defense')[0].checked){
      defenseProcessed.push(Number(defense["c"]));
      defenseProcessed.push(Number(defense["1b"]) + Number(defense["2b"]) + Number(defense["ss"]) + Number(defense["3b"]));
      defenseProcessed.push(Number(defense["of1"]) + Number(defense["of2"]) + Number(defense["of3"]));
    }
    else{
      defenseProcessed = 0;
    }

    // Now we have all the input data, print stuff out to confirm:
    
    // Run the simulation and get results
    var runInstance = simulation.run(batters, pitchers, defenseProcessed);
    var results = $.parseJSON(runInstance);
    var score = results.pop();
    var presult = results.pop();
    // console.log(results);
    // console.log(presult);
    // console.log(score);
    
    // Prep display
    $(".results-before").contents().filter(function(){ return this.nodeType == 3; }).remove();
    $(".results-before").addClass('results-after');
    $(".results-before").removeClass('results-before');
    
    // Display score
    var scorehtml = "Average number of runs <strong>this</strong> team will score agains <strong>this</strong> pitcher in a 9 inning game: <div class=\"score-val\">"+ score["Score"] + "</div>";
    $(".score-results").empty().html(scorehtml);
    
    // Build batter results
    // TODO: un-hardcode the titles in all the table headings...
    var btbl = "<table><colgroup><col class=\"out-cols\" span=\"3\"><col class=\"ob-cols\" span=\"6\"><col class=\"obp-col\"></colgroup><thead><th>SO</th><th>GB</th><th>FB</th><th>BB</th><th>1B</th><th>1B+</th><th>2B</th><th>3B</th><th>HR</th><th>OBP</th></thead>";
    var odd_even = false;
    $.each(results, function() {
      var tbl_row = "";
      $.each(this, function(k , v) {
          tbl_row += "<td>"+v+"</td>";
      })
      btbl += "<tr class=\""+( odd_even ? "odd" : "even")+"\">"+tbl_row+"</tr>";
      odd_even = !odd_even;
    });
    btbl += "</table>";
    //console.log(btbl);
    // Display batter results
    $(".bat-results").empty().html(btbl);
    
    // Build single pitcher results
    var ptbl = "<table><colgroup><col class=\"out-cols\" span=\"4\"><col class=\"ob-cols\" span=\"4\"><col class=\"obp-col\"></colgroup><thead><th>PU</th><th>SO</th><th>GB</th><th>FB</th><th>BB</th><th>1B</th><th>2B</th><th>HR</th><th>OBP</th></thead>";
    var tbl_row = "";
    $.each(presult, function(k , v) {
      return tbl_row += "<td>"+v+"</td>";
    });
    ptbl += "<tr>"+tbl_row+"</tr></table>";
    // Display pitcher results
    $(".pit-results").empty().html(ptbl);
    

  });
  

});