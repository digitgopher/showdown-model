define([
  'intern!object',
  'intern/chai!assert',
  'sim/simrun'
], function (registerSuite, assert, sim) {
    registerSuite({
      name: 'sim',

      roll: function () {
        var x = sim.roll();
        assert(x <= 20, 'Roll is less than or equal to 20.');
        assert(x >= 1, 'Roll is less than or equal to 20.');
      },
      
      result_bb: function () {
        assert(sim.result_bb(false, 15, [0,-1,-1,-1])[0] === 0, 'Walk with bases empty, score stays the same');
        assert(sim.result_bb(false, 15, [0,-1,-1,-1])[1] === 15, 'Bases empty');
        assert(sim.result_bb(false, 15, [0,-1,-1,-1])[2] === -1, 'Bases empty');
        assert(sim.result_bb(false, 15, [0,-1,-1,-1])[3] === -1, 'Bases empty');
        
        assert(sim.result_bb(false, 18, [5,15,10,22])[0] === 6, 'full');
        assert(sim.result_bb(false, 18, [5,15,10,22])[1] === 18, 'full');
        assert(sim.result_bb(false, 18, [5,15,10,22])[2] === 15, 'full');
        assert(sim.result_bb(false, 18, [5,15,10,22])[3] === 10, 'full');
      },
      
      result_1b: function () {
        assert(sim.result_1b(false, 15, [0,-1,-1,-1])[0] === 0, 'Single with bases empty, score stays the same');
        assert(sim.result_1b(false, 15, [0,-1,-1,-1])[1] === 15, 'Bases empty');
        assert(sim.result_1b(false, 15, [0,-1,-1,-1])[2] === -1, 'Bases empty');
        assert(sim.result_1b(false, 15, [0,-1,-1,-1])[3] === -1, 'Bases empty');
        
        assert(sim.result_1b(false, 18, [5,-1,10,22])[0] === 6, 'Single with runners on second and third.');
        assert(sim.result_1b(false, 18, [5,-1,10,22])[1] === 18, 'full');
        assert(sim.result_1b(false, 18, [5,-1,10,22])[2] === -1, 'full');
        assert(sim.result_1b(false, 18, [5,-1,10,22])[3] === 10, 'full');
        
        assert(sim.result_1b(false, 18, [5,22,-1,-1])[0] === 5, 'Single with runner on first.');
        assert(sim.result_1b(false, 18, [5,22,-1,-1])[1] === 18, 'full');
        assert(sim.result_1b(false, 18, [5,22,-1,-1])[2] === 22, 'full');
        assert(sim.result_1b(false, 18, [5,22,-1,-1])[3] === -1, 'full');
        
        assert(sim.result_1b(false, 18, [5,15,10,22])[0] === 6, 'full');
        assert(sim.result_1b(false, 18, [5,15,10,22])[1] === 18, 'full');
        assert(sim.result_1b(false, 18, [5,15,10,22])[2] === 15, 'full');
        assert(sim.result_1b(false, 18, [5,15,10,22])[3] === 10, 'full');
      },
      
      result_1bplus: function () {
        assert(sim.result_1bplus(false, 15, [0,-1,-1,-1])[0] === 0, 'Single plus with bases empty, score stays the same');
        assert(sim.result_1bplus(false, 15, [0,-1,-1,-1])[1] === -1, 'Bases empty');
        assert(sim.result_1bplus(false, 15, [0,-1,-1,-1])[2] === 15, 'Bases empty');
        assert(sim.result_1bplus(false, 15, [0,-1,-1,-1])[3] === -1, 'Bases empty');
        
        assert(sim.result_1bplus(false, 18, [5,-1,10,22])[0] === 6, 'Single plus with runners on second and third.');
        assert(sim.result_1bplus(false, 18, [5,-1,10,22])[1] === -1, 'full');
        assert(sim.result_1bplus(false, 18, [5,-1,10,22])[2] === 18, 'full');
        assert(sim.result_1bplus(false, 18, [5,-1,10,22])[3] === 10, 'full');
        
        assert(sim.result_1bplus(false, 18, [5,22,-1,-1])[0] === 5, 'Single plus with runner on first.');
        assert(sim.result_1bplus(false, 18, [5,22,-1,-1])[1] === 18, 'full');
        assert(sim.result_1bplus(false, 18, [5,22,-1,-1])[2] === 22, 'full');
        assert(sim.result_1bplus(false, 18, [5,22,-1,-1])[3] === -1, 'full');
        
        assert(sim.result_1bplus(false, 18, [5,15,10,22])[0] === 6, 'full');
        assert(sim.result_1bplus(false, 18, [5,15,10,22])[1] === 18, 'full');
        assert(sim.result_1bplus(false, 18, [5,15,10,22])[2] === 15, 'full');
        assert(sim.result_1bplus(false, 18, [5,15,10,22])[3] === 10, 'full');
      },
      
      result_2b: function () {
        assert(sim.result_2b(false, 15, [0,-1,-1,-1])[0] === 0, 'Double with bases empty, score stays the same');
        assert(sim.result_2b(false, 15, [0,-1,-1,-1])[1] === -1, 'Bases empty');
        assert(sim.result_2b(false, 15, [0,-1,-1,-1])[2] === 15, 'Bases empty');
        assert(sim.result_2b(false, 15, [0,-1,-1,-1])[3] === -1, 'Bases empty');
        
        assert(sim.result_2b(false, 18, [5,-1,10,22])[0] === 7, 'Double with runners on second and third.');
        assert(sim.result_2b(false, 18, [5,-1,10,22])[1] === -1, 'full');
        assert(sim.result_2b(false, 18, [5,-1,10,22])[2] === 18, 'full');
        assert(sim.result_2b(false, 18, [5,-1,10,22])[3] === -1, 'full');
        
        assert(sim.result_2b(false, 18, [5,22,-1,-1])[0] === 5, 'Double with runner on first.');
        assert(sim.result_2b(false, 18, [5,22,-1,-1])[1] === -1, 'full');
        assert(sim.result_2b(false, 18, [5,22,-1,-1])[2] === 18, 'full');
        assert(sim.result_2b(false, 18, [5,22,-1,-1])[3] === 22, 'full');
        
        assert(sim.result_2b(false, 18, [5,15,10,22])[0] === 7, 'full');
        assert(sim.result_2b(false, 18, [5,15,10,22])[1] === -1, 'full');
        assert(sim.result_2b(false, 18, [5,15,10,22])[2] === 18, 'full');
        assert(sim.result_2b(false, 18, [5,15,10,22])[3] === 15, 'full');
      },
      
      result_3b: function () {
        assert(sim.result_3b(false, 15, [0,-1,-1,-1])[0] === 0, 'Triple with bases empty, score stays the same');
        assert(sim.result_3b(false, 15, [0,-1,-1,-1])[1] === -1, 'Bases empty');
        assert(sim.result_3b(false, 15, [0,-1,-1,-1])[2] === -1, 'Bases empty');
        assert(sim.result_3b(false, 15, [0,-1,-1,-1])[3] === 15, 'Bases empty');
        
        assert(sim.result_3b(false, 18, [5,-1,10,22])[0] === 7, 'Triple with runners on second and third.');
        assert(sim.result_3b(false, 18, [5,-1,10,22])[1] === -1, 'full');
        assert(sim.result_3b(false, 18, [5,-1,10,22])[2] === -1, 'full');
        assert(sim.result_3b(false, 18, [5,-1,10,22])[3] === 18, 'full');
        
        assert(sim.result_3b(false, 18, [5,22,-1,-1])[0] === 6, 'Triple with runner on first.');
        assert(sim.result_3b(false, 18, [5,22,-1,-1])[1] === -1, 'full');
        assert(sim.result_3b(false, 18, [5,22,-1,-1])[2] === -1, 'full');
        assert(sim.result_3b(false, 18, [5,22,-1,-1])[3] === 18, 'full');
        
        assert(sim.result_3b(false, 18, [5,15,10,22])[0] === 8, 'Triple');
        assert(sim.result_3b(false, 18, [5,15,10,22])[1] === -1, 'full');
        assert(sim.result_3b(false, 18, [5,15,10,22])[2] === -1, 'full');
        assert(sim.result_3b(false, 18, [5,15,10,22])[3] === 18, 'full');
      },
      
      result_hr: function () {
        assert(sim.result_hr(false, 15, [0,-1,-1,-1])[0] === 1, 'HR with bases empty, score stays the same');
        assert(sim.result_hr(false, 15, [0,-1,-1,-1])[1] === -1, 'Bases empty');
        assert(sim.result_hr(false, 15, [0,-1,-1,-1])[2] === -1, 'Bases empty');
        assert(sim.result_hr(false, 15, [0,-1,-1,-1])[3] === -1, 'Bases empty');
        
        assert(sim.result_hr(false, 18, [5,-1,10,22])[0] === 8, 'HR with runners on second and third.');
        assert(sim.result_hr(false, 18, [5,-1,10,22])[1] === -1, 'full');
        assert(sim.result_hr(false, 18, [5,-1,10,22])[2] === -1, 'full');
        assert(sim.result_hr(false, 18, [5,-1,10,22])[3] === -1, 'full');
        
        assert(sim.result_hr(false, 18, [5,22,-1,-1])[0] === 7, 'HR with runner on first.');
        assert(sim.result_hr(false, 18, [5,22,-1,-1])[1] === -1, 'full');
        assert(sim.result_hr(false, 18, [5,22,-1,-1])[2] === -1, 'full');
        assert(sim.result_hr(false, 18, [5,22,-1,-1])[3] === -1, 'full');
        
        assert(sim.result_hr(false, 18, [5,15,10,22])[0] === 9, 'HR');
        assert(sim.result_hr(false, 18, [5,15,10,22])[1] === -1, 'full');
        assert(sim.result_hr(false, 18, [5,15,10,22])[2] === -1, 'full');
        assert(sim.result_hr(false, 18, [5,15,10,22])[3] === -1, 'full');
      }
    });
});