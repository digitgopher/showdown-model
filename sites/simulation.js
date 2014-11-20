
function sim(json){
  // json.name , json["name"] both work
  var startTime = Date.now();
  var endTime = Date.now();
  //return "startTime:"+startTime+",jsonname:"+json.name;
  
  // *****
  // Code up the logic
  // *****
  
  // Usage
  // Get first batter: batters[0]
  // Get first batter's chart: batters[0][0]
  // Get first batter's other attributes: batters[0][1]
  // Note: batter's card attributes are split like this so we can loop through atbat result possibilities
  // Example: batters[curBatter][1][0] == onbase value
  // Using arrays because objects aren't ordered!
  var batters = [
    [[2,2,2,3,5,1,2,1,2],[8,15,"c",6]],
    [[2,2,2,3,5,1,2,1,2],[8,15,"1b",0]],
    [[2,2,2,3,5,1,2,1,2],[8,15,"2b",3]],
    [[2,2,2,3,5,1,2,1,2],[8,15,"ss",3]],
    [[2,2,2,3,5,1,2,1,2],[8,15,"3b",1]],
    [[2,2,2,3,5,1,2,1,2],[8,15,"of",1]],
    [[2,2,2,3,5,1,2,1,2],[8,15,"of",1]],
    [[2,2,2,3,5,1,2,1,2],[8,15,"of",1]],
    [[2,2,2,3,5,1,2,1,2],[8,15,"dh",""]]
  ]
  var bat0Map= {
    0 : "so",
    1 : "gb",
    2 : "fb",
    3 : "bb",
    4 : "1b",
    5 : "1b+",
    6 : "2b",
    7 : "3b",
    8 : "hr",
    // We have to track every result even though you can only get results on certain charts!
    // Hence results are mapped to trackable values.
    "so" : 0,
    "gb" : 1,
    "fb" : 2,
    "bb" : 3,
    "1b" : 4,
    "1b+": 5,
    "2b" : 6,
    "3b" : 7,
    "hr" : 8,
    "pu" : 2 // batter sees a flyball
  }
  var bat1Map= {
    0 : "ob",
    1 : "sp",
    2 : "pos",
    3 : "f"
  }
  
  var pitchers = [
    [[2,5,5,4,2,1,1,0],[4,""]]
  ]
  var pit0Map= {
    0 : "pu",
    1 : "so",
    2 : "gb",
    3 : "fb",
    4 : "bb",
    5 : "1b",
    6 : "2b",
    7 : "hr",
    // We have to track every result even though you can only get results on certain charts!
    // Hence results are mapped to trackable values.
    "pu" : 0,
    "so" : 1,
    "gb" : 2,
    "fb" : 3,
    "bb" : 4,
    "1b" : 5,
    "2b" : 6,
    "hr" : 7,
    "1b+": 5, // pitcher sees a normal single
    "3b" : 6  // pitcher sees a double
  }

  // var inningMap = {
    // 1 : "1st",
    // 2 : "2nd",
    // 3 : "3rd",
    // 4 : "4th",
    // 5 : "5th",
    // 6 : "6th",
    // 7 : "7th",
    // 8 : "8th",
    // 9 : "9th"
  // }
  
  // // check that all charts add up to 20
  // for (var i = 0; i < batters.length; i++) {
    // var sum = 0;
    // for (var f = 0; f < batters[i][0].length; f++) {
      // sum += batters[i][0][f];
    // }
    // console.log(sum);
  // }
  
  // Nobody is on base to start with!
  var bases = [false,false,false];
  var score = 0;
  
  // A place to hold the raw results of the simulation
  var batterTallies = [
    [[0,0,0,0,0,0,0,0,0],[0,0/*How many steals*/,0/*Fielding representation*/]],
    [[0,0,0,0,0,0,0,0,0],[0,0,0]],
    [[0,0,0,0,0,0,0,0,0],[0,0,0]],
    [[0,0,0,0,0,0,0,0,0],[0,0,0]],
    [[0,0,0,0,0,0,0,0,0],[0,0,0]],
    [[0,0,0,0,0,0,0,0,0],[0,0,0]],
    [[0,0,0,0,0,0,0,0,0],[0,0,0]],
    [[0,0,0,0,0,0,0,0,0],[0,0,0]],
    [[0,0,0,0,0,0,0,0,0],[0,0,0]]
  ]
  
  // Don't know what the group pitcherTallies[0][1] is for yet
  var pitcherTallies = [
    [[0,0,0,0,0,0,0,0],[0,0]]
  ]
  
  var results = new Array(); // to contain all results of simulation as json to be displayed in browser
  
  var curBatter = 0;
  var abres = ''; // current batter's result
  
  
  // TODO: Run the simulation 30 times and then get averages.
  
  
  // We can play as many innings as we want, in multiples of 9 to keep things even.
  var numGames = 1000
  for (var i = 1; i <= 9*numGames; i++) {
    var outs = 0;
    // end inning after 3 outs
    while(outs < 3){
      abres = getResultAtBat();
      //console.log(abres);
      // Out result
      if(abres == "pu" || abres == "so" || abres == "gb" || abres == "fb"){
        outs++;
      }
      // Not an out result
      else{
        advanceRunners();
      }
      // Save statistics for display
      logResult();
      // Next batter
      curBatter = (curBatter + 1) % 9;
      if(outs < 3){
        //console.log("Batter "+ curBatter + " up. Inning " + i + ": " + outs + " outs.");
      }
      else{
        //console.log("Inning over. Moving to the "+ (i+1));
      }
      //return;
    }
    // Inning over. Clear the bases
    bases = [false,false,false];
  }
  console.log(pitcherTallies);
  console.log(batterTallies);
  // Transform what logResult does into the statistics that will be output
  transformRawResultsToStatistics();
  
  console.log("Simulation results: ");
  console.log(results);
  // Game over
  return results;
  
  // functions
  
  function getResultAtBat(){
    // Which chart?
    var pitch = Math.ceil(Math.random() * 20);
    if(pitch + pitchers[0][1][0] > batters[curBatter][1][0]){ // pitcher has advantage!
      return getSwingResult("p");
    }
    else{ // batter has advantage!
      return getSwingResult("b");
    }
    
  }
  
  function getSwingResult(chart){
    var swing = Math.ceil(Math.random() * 20);
    var inc = 0;
    switch(chart){
      case "p":
        for(var x = 0; x < pitchers[0][0].length; x++) {
          inc += pitchers[0][0][x];
          if(swing <= inc){
            //console.log(pit0Map[x]);
            return pit0Map[x];
          }
        }
        console.log("Error: No result found on pitcher's chart.");
        break;
      case "b":
        for(var x = 0; x < batters[curBatter][0].length; x++) {
          inc += batters[curBatter][0][x];
          if(swing <= inc){
            //console.log(bat0Map[x]);
            return bat0Map[x];
          }
        }
        console.log("Error: No result found on batter's chart.");
        break;
      default:
        console.log("Error: don't know whose chart to look at.");
    }
  }

  // Also keeps the score
  function advanceRunners(){
    switch(abres){
      case "bb":
        bases[0] ? // check if runner on first
          bases[1] ? // check if runner on second
            bases[2] ? // check if runner on third
              score++ // bases loaded, one runner scores!
            : bases[2] = true // nobody on third, move the runner from second to third
          : bases[1] = true // nobody on second, move the runner on first to second
        : bases[0] = true; // nobody was on first to begin with
        break;
      case "1b":
        if(bases[2]){ // runner on third scores
          score++;
          bases[2] = false;
        }
        if(bases[1]){ // runner on second goes to third
          bases[1] = false;
          bases[2] = true;
        }
        if(bases[0]){ // runner on first goes to second, batter occupies first
          bases[1] = true;
        }else{
          bases[0] = true;
        }
        break;
      // **********1b and 1b+ are the same for now!***********
      case "1b+":
        if(bases[2]){ // runner on third scores
          score++;
          bases[2] = false;
        }
        if(bases[1]){ // runner on second goes to third
          bases[1] = false;
          bases[2] = true;
        }
        if(bases[0]){ // runner on first goes to second, batter occupies first
          bases[1] = true;
        }else{
          bases[0] = true;
        }
        break;
      case "2b":
        if(bases[2]){ // runner on third scores
          score++;
          bases[2] = false;
        }
        if(bases[1]){ // runner on second scores
          score++;
          bases[1] = false;
        }
        if(bases[0]){ // runner goes first to third
          bases[2] = true;
        }
        bases[1] = true; // batter stands on second
        break;
      case "3b":
        if(bases[0]){ // everybody scores!
          score++;
          bases[0] = false;
        }
        if(bases[1]){
          score++;
          bases[1] = false;
        }
        if(bases[2]){
          score++;
          bases[2] = false;
        }
        bases[2] = true; // and the batter stands on third
        break;
      case "hr":
        if(bases[0]){ // everybody scores!
          score++;
          bases[0] = false;
        }
        if(bases[1]){
          score++;
          bases[1] = false;
        }
        if(bases[2]){
          score++;
          bases[2] = false;
        }
        score++;
        break;
      default:
        console.log("Error: Can't advance runners because hittype not valid.");
    }
  }

  function logResult(){
    //console.log(abres);
    pitcherTallies[0][0][pit0Map[abres]]++;
    batterTallies[curBatter][0][bat0Map[abres]]++;
  }
  
  // build 'results'
  // results has 11 items:
  // The first 9 are batters, then pitcher, then score
  function transformRawResultsToStatistics(){
    // Names to send back
    var batStatList = {"SO/PA": 0, "GB/PA": 0, "FB/PA": 0, "BB/PA": 0, "1B/PA": 0, "1B+/PA": 0, "2B/PA": 0, "3B/PA": 0, "HR/PA": 0, "OBP": 0}; // remember PU are eaten up by FB
    var pitStatList = ["PU/PA", "SO/PA", "GB/PA", "FB/PA", "BB/PA", "1B/PA", "2B/PA", "HR/PA", "OBP"]; // remember 1B+ and 3B are eaten up by 1B and 3B
    console.log("Score: "+score);
    // Access a specific raw value with batterTallies[batterIndex][0][resultOfAtbat]
    // Compute each (resultOfAtbat/Plate Appearances), as well as OBP
    
    // Initialize
    // for (var i = 0; i < batters.length; i++) {
      // results.push({"SO/PA": 0, "GB/PA": 0, "FB/PA": 0, "BB/PA": 0, "1B/PA": 0, "1B+/PA": 0, "2B/PA": 0, "3B/PA": 0, "HR/PA": 0, "OBP": 0});
    // }
    results.push({"SO/PA": 0, "GB/PA": 0, "FB/PA": 0, "BB/PA": 0, "1B/PA": 0, "1B+/PA": 0, "2B/PA": 0, "3B/PA": 0, "HR/PA": 0, "OBP": 0});
    results[0]["GB/PA"] = batterTallies[0][0][bat0Map["gb"]] / batterTallies[0][0].reduce(function(a, b) {return a + b;});
    
  }
  
}


sim(null);

exports.sim = sim;