
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
  // Note: batter's card attributes are split like this so we can loop through atbat result possiblities
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
    8 : "hr"
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
    7 : "hr"
  }
    // var p = {
    // "pu":0,
    // "so":1,
    // "gb":2,
    // "fb":3,
    // "bb":4,
    // "1b":5,
    // "2b":6,
    // "hr":7,
    // "c":0,
    // "ip":1
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
  var gameResults = ''; // to contain all results of simulation as json to be displayed in browser
  
  var curBatter = 0;
  var abres = ''; // current batter's result
  // 9 innings to play
  for (var i = 1; i <= 9; i++) {
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
      logResult(batters,pitchers,curBatter);
      // Next batter
      curBatter = (curBatter + 1) % 9;
      console.log("Batter "+ curBatter + " up. Inning " + i + ": " + outs + " outs.");
      return;
    }
    // Inning over. Clear the bases
    bases = [false,false,false];
  }
  
  // Transform what logResult does into gameResults*************************************************************************
  
  console.log("Score: "+score);
  console.log("Game results: "+gameResults);
  // Game over
  return gameResults;
  
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
            return pit0Map[x];
          }
        }
        console.log("Error: No result found on pitcher's chart.");
        break;
      case "b":
        for(var x = 0; x < batters[curBatter][0].length; x++) {
          inc += batters[curBatter][0][x];
          if(swing <= inc){
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
        bases[2] ? (score++, bases[2] = false): ; // runner on third scores
        bases[1] ? (bases[1] = false, bases[2] = true): ; // runner on second goes to third
        bases[0] ? bases[1] = true : bases[0] = true; // runner on first goes to second, batter occupies first
        break;
      // **********same as 1b for now!***********
      case "1b+":
        bases[2] ? (score++, bases[2] = false): ; // runner on third scores
        bases[1] ? (bases[1] = false, bases[2] = true): ; // runner on second goes to third
        bases[0] ? bases[1] = true : bases[0] = true; // runner on first goes to second, batter occupies first
        break;
      case "2b":
        bases[2] ? (score++, bases[2] = false): ; // runner on third scores
        bases[1] ? (score++, bases[1] = false): ; // runner on second scores
        bases[0] ? bases[2] = true : ; // runner goes first to third
        bases[1] = true; // batter stands on second
        break;
      case "3b":
        bases[0] ? (score++, bases[0] = false): ;
        bases[1] ? (score++, bases[1] = false): ;
        bases[2] ? (score++, bases[2] = false): ; // everybody scores
        bases[2] = true; // batter stands on third
        break;
      case "hr":
        bases[0] ? (score++, bases[0] = false): ;
        bases[1] ? (score++, bases[1] = false): ;
        bases[2] ? (score++, bases[2] = false): ;
        score++;
        break;
      default:
        console.log("Error: Can't advance runners because hittype not valid.");
    }
  }

  function logResult(batters,pitchers,curBatter){
    
  }
  
}


sim(null);

exports.sim = sim;