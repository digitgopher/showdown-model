define(["jquery"], function($) {

/********* ALL THE FORM VALIDATING & Dynamic Changing***********/
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

});