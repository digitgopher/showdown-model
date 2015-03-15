define([
  'intern!object',
  'intern/chai!assert',
  'sim/sim'
], function (registerSuite, assert, sim) {
    registerSuite({
      name: 'sim',

      roll: function () {
        var x = [];
        var trials = 10000;
        for (var i = 0; i < trials; i++) {
          x.push(sim.roll());
          // Check that every roll is valid
          assert(x[i] <= 20, 'Roll is less than or equal to 20.');
          assert(x[i] >= 1, 'Roll is less than or equal to 20.');
        }
        var counts = {};
        for(var i = 0; i < x.length; i++) {
          var num = x[i];
          counts[num] = counts[num] ? counts[num]+1 : 1;
        }
        for(var i = 1; i <= counts.length; i++) {
          // Make sure that every valid number was produced
          assert(counts[i] > trials * .02, 'At least some rolls of '+ i +' should exist');
        }
        
        // Expect a uniform distribution: use Pearson's chi-square test for a distribution fit.
        var expected = trials / 20;
        var chiSquare = 0;
        for (var i = 1; i <= 20; i++) {
          chiSquare += (counts[i] - expected) * (counts[i] - expected) / expected;
        }
        // Chi-square statistics: p-value = 0.05, df = 19, limit = 30.144
        assert(chiSquare < 30.144, 'Chi-square test for roll uniform distribution failed at p-value = .05');
        // Chi-square statistics: p-value = 0.01, df = 19, limit = 36.191
        assert(chiSquare < 36.191, 'Chi-square test for roll uniform distribution failed at p-value = .01');
      },
      
      defenseThrow: function(){
        // defenseThrow(totalDefenseValue, totalSpeedValue), returns true when runner is thrown out
        for (var i = 0; i < 100; i++) {
          // These should all happen every time!
          assert.isTrue(sim.defenseThrow(9, 9), "Fielding check 1");
          assert.isTrue(sim.defenseThrow(8, 7), "Fielding check 2");
          assert.isFalse(sim.defenseThrow(2, 23), "Fielding check 3");
          assert.isFalse(sim.defenseThrow(3, 23), "Fielding check 4");
        }
      },
      
      result_pu: function () {
        var r;
        r = sim.result_pu(0, 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'No Defense; Pop-up with bases empty, score stays the same');
        assert.strictEqual(r[1], -1, 'No Defense; Pop-up with bases empty, first empty');
        assert.strictEqual(r[2], -1, 'No Defense; Pop-up with bases empty, second empty');
        assert.strictEqual(r[3], -1, 'No Defense; Pop-up with bases empty, third empty');
        assert.strictEqual(r[4], 1, 'No Defense; Pop-up with bases empty, outs +1');
        
        r = sim.result_pu(0, 18, [5,15,10,22,0])
        assert.strictEqual(r[0], 5, 'No Defense; Pop-up with bases full, score same');
        assert.strictEqual(r[1], 15, 'No Defense; Pop-up with bases full, first holds');
        assert.strictEqual(r[2], 10, 'No Defense; Pop-up with bases full, second holds');
        assert.strictEqual(r[3], 22, 'No Defense; Pop-up with bases full, third holds');
        assert.strictEqual(r[4], 1, 'No Defense; Pop-up with bases full, outs +1');
        
        r = sim.result_pu([5,5,5], 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'Using Defense; Pop-up with bases empty, score stays the same');
        assert.strictEqual(r[1], -1, 'Using Defense; Pop-up with bases empty, first empty');
        assert.strictEqual(r[2], -1, 'Using Defense; Pop-up with bases empty, second empty');
        assert.strictEqual(r[3], -1, 'Using Defense; Pop-up with bases empty, third empty');
        assert.strictEqual(r[4], 1, 'Using Defense; Pop-up with bases empty, outs +1');
        
        r = sim.result_pu([5,5,5], 18, [5,15,10,22,0])
        assert.strictEqual(r[0], 5, 'Using Defense; Pop-up with bases full, score same');
        assert.strictEqual(r[1], 15, 'Using Defense; Pop-up with bases full, first holds');
        assert.strictEqual(r[2], 10, 'Using Defense; Pop-up with bases full, second holds');
        assert.strictEqual(r[3], 22, 'Using Defense; Pop-up with bases full, third holds');
        assert.strictEqual(r[4], 1, 'Using Defense; Pop-up with bases full, outs +1');
      },
      
      result_so: function () {
        var r;
        r = sim.result_so(0, 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'No Defense; Strikeout with bases empty, score stays the same');
        assert.strictEqual(r[1], -1, 'No Defense; Strikeout with bases empty, first empty');
        assert.strictEqual(r[2], -1, 'No Defense; Strikeout with bases empty, second empty');
        assert.strictEqual(r[3], -1, 'No Defense; Strikeout with bases empty, third empty');
        assert.strictEqual(r[4], 1, 'No Defense; Strikeout with bases empty, outs +1');
        
        r = sim.result_so(0, 18, [5,15,10,22,0])
        assert.strictEqual(r[0], 5, 'No Defense; Strikeout with bases full, score same');
        assert.strictEqual(r[1], 15, 'No Defense; Strikeout with bases full, first holds');
        assert.strictEqual(r[2], 10, 'No Defense; Strikeout with bases full, second holds');
        assert.strictEqual(r[3], 22, 'No Defense; Strikeout with bases full, third holds');
        assert.strictEqual(r[4], 1, 'No Defense; Strikeout with bases full, outs +1');
        
        r = sim.result_so([5,5,5], 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'Using Defense; Strikeout with bases empty, score stays the same');
        assert.strictEqual(r[1], -1, 'Using Defense; Strikeout with bases empty, first empty');
        assert.strictEqual(r[2], -1, 'Using Defense; Strikeout with bases empty, second empty');
        assert.strictEqual(r[3], -1, 'Using Defense; Strikeout with bases empty, third empty');
        assert.strictEqual(r[4], 1, 'Using Defense; Strikeout with bases empty, outs +1');
        
        r = sim.result_so([5,5,5], 18, [5,15,10,22,0])
        assert.strictEqual(r[0], 5, 'Using Defense; Strikeout with bases full, score same');
        assert.strictEqual(r[1], 15, 'Using Defense; Strikeout with bases full, first holds');
        assert.strictEqual(r[2], 10, 'Using Defense; Strikeout with bases full, second holds');
        assert.strictEqual(r[3], 22, 'Using Defense; Strikeout with bases full, third holds');
        assert.strictEqual(r[4], 1, 'Using Defense; Strikeout with bases full, outs +1');
      },
      
      result_gb: function () {
        var r;
        r = sim.result_gb(0, 15, [7,-1,-1,-1,0]);
        // 8 combinations of baserunners, 3 possibilities for number of outs (24 configurations)
        // 24 configurations - using defense and not = 48 tests
        // Two outs (8 out of 24)
        
        // **NO DEFENSE**
        
        r = sim.result_gb(0, 15, [7,-1,-1,-1,1]);
        assert.strictEqual(r[0], 7, 'ca6367e1-5f34-416a-b83f-892cde852efa');
        assert.strictEqual(r[1], -1, 'bce197c2-0305-4948-9f97-ed3cb010e957');
        assert.strictEqual(r[2], -1, 'b9e3d575-9a91-4c01-b626-d77d081dd355');
        assert.strictEqual(r[3], -1, 'c27f1634-8e6a-4abc-a87c-e200a59be71a');
        assert.strictEqual(r[4], 2, '01dffd60-4eed-402e-b37d-c8d774e54ef1');
        
        r = sim.result_gb(0, 15, [7,-1,11,16,0]);
        assert.strictEqual(r[0], 7, 'b247535a-1f07-4df2-b225-57eb3b67440c');
        assert.strictEqual(r[1], -1, '4ceaabc0-0dd2-4538-a3ce-be8d96153474');
        assert.strictEqual(r[2], 11, '42081c83-dd95-4e8e-ab03-6391f24e6c41');
        assert.strictEqual(r[3], 16, '6f44d9f4-e0b9-4305-ac20-0b6c67f7d8e3');
        assert.strictEqual(r[4], 1, 'd512ae99-f047-4017-b587-dd99b512b61a');
        
        
        // **USING DEFENSE**
        // No runners on base
        r = sim.result_gb([5,5,5], 15, [7,-1,-1,-1,2]);
        assert.strictEqual(r[0], 7, '1a5935ad-4779-4b3e-bc8c-8142328b9ff9');
        assert.strictEqual(r[1], -1, 'afe3630c-029f-49b0-8cff-e5ec1352113e');
        assert.strictEqual(r[2], -1, '799cb05e-211d-4863-a4b0-1f9d160d38ac');
        assert.strictEqual(r[3], -1, 'e5827d17-e518-4057-befe-4587ddf3b8cd');
        assert.strictEqual(r[4], 3, '41f74c8e-1b3f-4a55-aab1-9fe3f69f85fd');
        
        r = sim.result_gb([5,5,5], 15, [7,-1,-1,-1,1]);
        assert.strictEqual(r[0], 7, 'b80106bf-9b00-4683-851e-b8ddaacb2c5e');
        assert.strictEqual(r[1], -1, '894b159b-f55f-47fe-ba65-d03968f34407');
        assert.strictEqual(r[2], -1, 'b5c089a9-36f2-4554-ac2e-0fe5822c8b90');
        assert.strictEqual(r[3], -1, 'ac0fe8b2-76fe-4f04-8e78-786bf96603c8');
        assert.strictEqual(r[4], 2, 'f6febce4-55ce-453d-b6b0-ab8e82be0df8');
        
        r = sim.result_gb([5,5,5], 15, [7,-1,-1,-1,0]);
        assert.strictEqual(r[0], 7, 'b876a4f6-f0bb-4721-a5b1-f7327869b808');
        assert.strictEqual(r[1], -1, 'b0aae1e9-990d-40db-8885-5cd568ddc51d');
        assert.strictEqual(r[2], -1, '0e31800d-e953-453d-bf0e-05e83aa3860b');
        assert.strictEqual(r[3], -1, '97293957-108e-4bc4-b402-5df22a76a34f');
        assert.strictEqual(r[4], 1, 'a5e2f0ea-967a-4ce2-8a2d-fcd2fd809115');
        
        // Runner on first
        r = sim.result_gb([5,5,5], 15, [7,11,-1,-1,2]);
        assert.strictEqual(r[0], 7, '3ca8f69b-e3ba-4fd2-891b-452541dec4bb');
        assert.strictEqual(r[4], 3, '66e5ec90-59ef-41de-802b-ff3d79f7aeb7');
        
        for(var i = 0; i < 100; i++){
          r = sim.result_gb([5,5,5], 15, [7,11,-1,-1,1]);
          assert.strictEqual(r[0], 7, 'cfc3ba44-9705-472e-978d-026971f76ca4');
          assert.strictEqual(r[2], -1, '02cdfe67-05a6-4fc2-a424-db87b62181f4');
          assert.strictEqual(r[3], -1, 'd262ed84-31b5-4ea8-a75f-9a423054dee1');
          if(r[4] === 2){
            assert.strictEqual(r[1], 15, '2c1f2a59-5bf7-4da0-80e8-2c6f2b748841');
          }
          else {
            assert.strictEqual(r[4], 3, 'dab764a6-01df-4ce6-ae9b-166ed44369fe');
          }
        }
        
        for(var i = 0; i < 100; i++){
          r = sim.result_gb([5,5,5], 15, [7,11,-1,-1,0]);
          assert.strictEqual(r[0], 7, 'f7850a3b-3ec3-42d4-97ec-abf4a224a615');
          assert.strictEqual(r[2], -1, 'd6156912-487a-4ae7-a411-45a229d8970c');
          assert.strictEqual(r[3], -1, 'c3cf72fb-5fbf-49a8-8ae0-3282d39e5c42');
          if(r[4] === 1){
            assert.strictEqual(r[1], 15, '67c44be8-1314-480d-820f-ae5d53fb8aa8');
          }
          else {
            assert.strictEqual(r[4], 2, 'd07c6143-0b7a-4791-ad4f-51628cc942c9');
          }
        }
        
        // Runner on second
        r = sim.result_gb([5,5,5], 15, [7,-1,16,-1,2]);
        assert.strictEqual(r[0], 7, '4e263031-107e-4508-b0c5-551cd253d9c7');
        assert.strictEqual(r[4], 3, '10b25ad6-cc3f-446f-8588-5443300916e2');
        
        r = sim.result_gb([5,5,5], 15, [7,-1,16,-1,1]);
        assert.strictEqual(r[0], 7, 'b2950d91-2cc9-471d-8db7-b5b4f0c792c3');
        assert.strictEqual(r[1], -1, 'd82ee3b9-2f80-4835-9008-baca7d6039b6');
        assert.strictEqual(r[2], -1, '8ee31d9a-7dc6-4aa8-8702-8cb65f0cb02c');
        assert.strictEqual(r[3], 16, 'f6b4df64-4cb5-4f94-a05f-eac9a49d43d3');
        assert.strictEqual(r[4], 2, '56f72aeb-8250-4a1b-bbc1-e036b4f0b729');
        
        r = sim.result_gb([5,5,5], 15, [7,-1,16,-1,0]);
        assert.strictEqual(r[0], 7, '47952dc4-3864-4ef6-91d6-b099592c4f0f');
        assert.strictEqual(r[1], -1, 'ac44c045-5e98-4f25-8509-4452c32ca8da');
        assert.strictEqual(r[2], -1, '6eae712d-73c2-4f14-8f73-bafbaf33b3f7');
        assert.strictEqual(r[3], 16, 'e194ff18-74fe-49df-abfe-39768c88b69d');
        assert.strictEqual(r[4], 1, '308bbee2-a3b8-46c5-abce-6e554f829db4');
        
        // Runner on third
        r = sim.result_gb([5,5,5], 15, [7,-1,-1,22,2]);
        assert.strictEqual(r[0], 7, 'ee149e68-ab17-4563-ad38-88871a7b6af1');
        assert.strictEqual(r[4], 3, 'cff68401-1a52-4e01-8f78-09a15603b8ab');
        
        r = sim.result_gb([5,5,5], 15, [7,-1,-1,22,1]);
        assert.strictEqual(r[0], 8, '90bfc036-18d4-4876-9fcf-7f988e63e4ac');
        assert.strictEqual(r[1], -1, '0fc9d4f7-57c5-487d-9e48-76ab99189775');
        assert.strictEqual(r[2], -1, '6e5a5d4e-7613-4dd3-aee7-b2ab98731f32');
        assert.strictEqual(r[3], -1, 'G7705d8e2-39d8-4e05-a874-914195bfef40G');
        assert.strictEqual(r[4], 2, 'G97f08406-431b-40f5-b5e5-c42c37bab2bfG');
        
        r = sim.result_gb([5,5,5], 15, [7,-1,-1,22,0]);
        assert.strictEqual(r[0], 8, 'G8ab6c319-9d2d-4706-81e9-b72a424f0d4bG');
        assert.strictEqual(r[1], -1, 'ca3997f2-995d-463e-aa88-6c1c760d10ce');
        assert.strictEqual(r[2], -1, '6cc8e9bd-b757-4aa7-9ddf-d3d2c22da085');
        assert.strictEqual(r[3], -1, 'c69c0738-6e80-4fd5-930b-cdb649d9ab32');
        assert.strictEqual(r[4], 1, '84480b54-7d92-4fe3-97f4-16d065462106');
        
        // Runners on first and second
        r = sim.result_gb([5,5,5], 15, [7,11,16,-1,2]);
        assert.strictEqual(r[0], 7, '7485f18e-4bb0-4a03-9cc6-a6aa37b353aa');
        assert.strictEqual(r[4], 3, 'ce449975-eb72-47cb-bc85-47e96befe08d');
        
        for(var i = 0; i < 100; i++){
          r = sim.result_gb([5,5,5], 15, [7,11,16,-1,1]);
          assert.strictEqual(r[0], 7, 'Score never changes. 677ef871-3694-43d3-a3cc-9753e34635c7');
          if(r[4] === 2){
            assert.strictEqual(r[1], 15, '61480f4d-3244-4753-b895-7dcddf555664');
            assert.strictEqual(r[2], -1, 'Second always vacant. 2b21bc3f-aeb9-4ca0-8d20-ddd0aa0521c7');
            assert.strictEqual(r[3], 16, '65eefdaa-35ba-4621-88f8-d6ae664a1744');
          }
          else {
            assert.strictEqual(r[4], 3, 'c9999161-e32a-4ae9-9885-d21514b14fa6');
          }
        }
        
        for(var i = 0; i < 100; i++){
          r = sim.result_gb([5,5,5], 15, [7,11,16,-1,0]);
          assert.strictEqual(r[0], 7, '394a3b03-627d-4bc5-8ba1-64ce4adadd27');
          assert.strictEqual(r[2], -1, '224d5a1c-c9a0-4c95-8162-e82614702d74');
          assert.strictEqual(r[3], 16, '0b7463be-79b9-403c-b2d4-ab20d801fa2a');
          if(r[4] === 1){
            assert.strictEqual(r[1], 15, '20558d11-ab63-433d-a9f9-5723274a57f9');
          }
          else {
            assert.strictEqual(r[4], 2, 'c76e161c-a2f2-4b1f-9dc6-774d84e34ec4');
          }
        }
        
        // Runners on first and third
        r = sim.result_gb([5,5,5], 15, [7,11,-1,16,2]);
        assert.strictEqual(r[0], 7, 'e2a2931d-0fc2-4b18-af2c-2899aeab0a13');
        assert.strictEqual(r[4], 3, '4260a896-de5a-46b2-8d12-cef7880958c5');
        
        for(var i = 0; i < 100; i++){
          r = sim.result_gb([5,5,5], 15, [7,11,-1,16,1]);
          if(r[4] === 2){
            assert.strictEqual(r[1], 15, '43424acb-cf32-4dd6-9800-cdec83fdede8');
            assert.strictEqual(r[2], -1, 'a5abfe89-8a75-43f1-b569-e731848e9fa4');
            assert.strictEqual(r[3], -1, '0869b880-3d51-4f07-a28a-0308bd2cbcac');
            assert.strictEqual(r[0], 8, 'a229bedc-2e1f-489c-8d14-2caa2b4b6298');
          }
          else {
            assert.strictEqual(r[4], 3, '568b9f45-6268-4e3b-bd95-d4d17a7ca62b');
            assert.strictEqual(r[0], 7, 'c8a65879-7c0f-44b5-af73-52054febf8d7');
          }
        }
        
        for(var i = 0; i < 100; i++){
          r = sim.result_gb([5,5,5], 15, [7,11,-1,16,0]);
          assert.strictEqual(r[0], 8, '88c5d155-859b-454f-973a-f185941993b5');
          if(r[4] === 1){
            assert.strictEqual(r[1], 15, '5eff55e0-9efe-49ed-9623-1d42ae02bfd0');
            assert.strictEqual(r[2], -1, '014ff6db-1199-454d-b697-5e5c4dfa12d2');
            assert.strictEqual(r[3], -1, '79da923d-ce64-4d71-92a2-7043bb2f2495');
          }
          else {
            assert.strictEqual(r[4], 2, '64dd4e32-093a-4655-b21c-b97ca3e1bcda');
            assert.strictEqual(r[1], -1, 'b100154e-6864-45c9-a945-28afc9dcaa8c');
            assert.strictEqual(r[2], -1, 'b74ff477-6920-412a-84cf-efbcdd2907f9');
            assert.strictEqual(r[3], -1, '38da0b50-6f18-4659-8f4c-c1d2b85b635a');
          }
        }
        
        // Runners on second and third
        r = sim.result_gb([5,5,5], 15, [7,-1,11,16,2]);
        assert.strictEqual(r[0], 7, '1c60a7e7-34c2-4912-bcdc-1559c10fa58c');
        assert.strictEqual(r[4], 3, 'c157a2f5-9615-4dd1-9dc7-3f5a1d2bf107');
        
        r = sim.result_gb([5,5,5], 15, [7,-1,11,16,1]);
        assert.strictEqual(r[0], 8, '077a67ae-73e4-4ac2-be99-54972b8e7115');
        assert.strictEqual(r[1], -1, '504f8726-80d0-4b2b-abf4-dd145bde82c7');
        assert.strictEqual(r[2], -1, 'b02a04fa-26c1-4221-8928-20e997022be2');
        assert.strictEqual(r[3], 11, '69d8acaf-d3bc-4589-a55d-c0bc7797ca48');
        assert.strictEqual(r[4], 2, 'b8a6c634-fc28-43a4-bf09-aaa328280094');
        
        r = sim.result_gb([5,5,5], 15, [7,-1,11,16,0]);
        assert.strictEqual(r[0], 8, 'cc6d8578-e63b-4550-87ac-1b2ace1e396e');
        assert.strictEqual(r[1], -1, '36e075e6-fdb8-4693-9b53-9ec773243067');
        assert.strictEqual(r[2], -1, 'e79e31f4-4dd8-4cd5-9d9e-fc6bc641924b');
        assert.strictEqual(r[3], 11, '673c6967-e3f7-4162-a0dc-c89adf2a2ce6');
        assert.strictEqual(r[4], 1, 'a0df135f-440c-4783-9602-941b75bd1aad');
        
        // Bases loaded
        r = sim.result_gb([5,5,5], 15, [7,11,16,22,2]);
        assert.strictEqual(r[0], 7, 'fc451de8-a8c2-48c1-87f3-b9346568d9d2');
        assert.strictEqual(r[4], 3, '801d82b6-eeb8-4502-95d6-9193a02a206f');
        
        for(var i = 0; i < 100; i++){
          r = sim.result_gb([5,5,5], 15, [7,11,16,22,1]);
          if(r[4] === 2){
            assert.strictEqual(r[1], 15, '5cdf0dc0-5b26-432a-9b48-070083da4c70');
            assert.strictEqual(r[2], -1, '32dc6d57-20a8-45c4-96bf-34ae4aab8ed7');
            assert.strictEqual(r[3], 16, '169064dc-c44b-4877-af1c-475534370bf8');
            assert.strictEqual(r[0], 8, '2514871d-5071-4b2e-96b0-5dc9e820c74f');
          }
          else {
            assert.strictEqual(r[4], 3, 'f9de596e-a990-4b2b-9919-d63e4507037f');
            assert.strictEqual(r[0], 7, '270e5ad6-bd01-4034-9b6b-49eb5b581308');
          }
        }
        
        for(var i = 0; i < 100; i++){
          r = sim.result_gb([5,5,5], 15, [7,11,16,22,0]);
          assert.strictEqual(r[0], 8, 'f2480e04-e55c-4906-8aed-738ba5a80135');
          if(r[4] === 1){
            assert.strictEqual(r[1], 15, 'e8c51715-e718-4012-a316-ed97b88501fb');
            assert.strictEqual(r[2], -1, '9ce704a0-0d33-4b46-8085-8007167b7ba3');
            assert.strictEqual(r[3], 16, '10ae62a3-66f5-4d24-9df2-00f765da265b');
          }
          else {
            assert.strictEqual(r[4], 2, '8c2ad5e4-3da6-4017-8192-da72bca15f84');
            assert.strictEqual(r[1], -1, 'e4fb1f9a-6f65-41b0-9e9e-01c3f0225d72');
            assert.strictEqual(r[2], -1, 'bb004f74-41ea-46f2-a9ff-58460075c9a0');
            assert.strictEqual(r[3], 16, '8247a2cb-39a3-4938-aae5-a76650a9bdfc');
          }
        }
        
      },
      
      // fb function of tests is not comprehensive like the gb one is; more of a smattering of tests.
      result_fb: function () {
        var r;
        r = sim.result_fb(0, 15, [0,-1,-1,-1,0]);
        // 8 combinations of baserunners, 3 possibilities for number of outs (24 configurations)
        // Two outs (8 out of 24), No runners on base or runner on first only, 0 or 1 out (4 out of 24), nothing happens (12 out of 24)
        
        // **NO DEFENSE**
        r = sim.result_fb(0, 15, [7,-1,11,16,0]);
        assert.strictEqual(r[0], 7, 'c76f8d51-5498-458b-87fe-ee0b758871c6');
        assert.strictEqual(r[1], -1, 'a2e74256-9326-4ba2-9122-5a086e8951a0');
        assert.strictEqual(r[2], 11, '5111ce47-0578-4107-8957-e139d2488888');
        assert.strictEqual(r[3], 16, 'ba756ef4-f60d-45cc-82f4-97a90e265748');
        assert.strictEqual(r[4], 1, '23f4f5bd-4634-41d3-9f47-008b7aedb518');
        
        r = sim.result_fb(0, 15, [7,-1,21,-1,1]);
        assert.strictEqual(r[0], 7, '38561239-f420-4ce2-aad3-3dbc9a654ee8');
        assert.strictEqual(r[1], -1, 'dfe461ed-4708-4803-ab87-3c7ecc66fef3');
        assert.strictEqual(r[2], 21, '50493652-7a52-40fa-8d4b-07e02d660c3e');
        assert.strictEqual(r[3], -1, 'a3f0f7ec-5f01-460d-930b-0cf714d615c2');
        assert.strictEqual(r[4], 2, '3e173179-b850-4d30-8a92-6a13546e58c2');
        
        // **USING DEFENSE**
        // Runner on third scores
        r = sim.result_fb([5,5,5], 15, [7,-1,11,16,0]);
        assert.strictEqual(r[0], 8, 'ccc56150-1836-4f46-90c7-2b6261041adf');
        assert.strictEqual(r[1], -1, 'fdde4ed0-256b-4c6d-8726-18bc0e108722');
        assert.strictEqual(r[2], 11, '4c6af645-9696-4b3c-bae0-35941fad5b6e');
        assert.strictEqual(r[3], -1, 'c176a065-c62d-4a75-9396-23a6593f4515');
        assert.strictEqual(r[4], 1, 'dbca2b1a-f7db-4550-9372-2a41ac05d329');
        
        // Nobody advances
        r = sim.result_fb([5,5,5], 15, [7,11,16,-1,0]);
        assert.strictEqual(r[0], 7, '1462003f-3a96-4368-9956-be782b3fe3e4');
        assert.strictEqual(r[1], 11, '767a1b6d-d6e1-4b20-b987-02b5923618bc');
        assert.strictEqual(r[2], 16, '666ba756-23ae-468e-8d02-73b196c1db83');
        assert.strictEqual(r[3], -1, 'b3f54f9b-5d0c-4a0e-afaa-96085520f2f2');
        assert.strictEqual(r[4], 1, '435c25a7-35b2-4704-a77a-d47a8a956509');
        
        // Runner on third scores
        r = sim.result_fb([5,5,5], 15, [7,-1,21,16,1]);
        assert.strictEqual(r[0], 8, 'c5927f85-c79d-4b37-b5a0-8f945ded3582');
        assert.strictEqual(r[1], -1, '93c0710c-4087-4ff1-8b9c-260ae48f0ca0');
        assert.strictEqual(r[2], 21, '580ce689-54e0-4e51-8b5f-fc17a3db4415');
        assert.strictEqual(r[3], -1, '3515c469-4ffe-4624-9ec2-e8446b8f605b');
        assert.strictEqual(r[4], 2, 'a641eccd-c51e-41a3-ad91-5ee3ffe4ff36');
        
        // Nobody advances
        r = sim.result_fb([5,5,5], 15, [7,-1,21,10,1]);
        assert.strictEqual(r[0], 7, 'b0b85879-a3f3-46df-a38a-374e75a57cca');
        assert.strictEqual(r[1], -1, '780f51cd-cf51-4f1e-9ff2-4d71cb6155a9');
        assert.strictEqual(r[2], 21, '34937912-ced8-4eff-a57e-ac58a82cd481');
        assert.strictEqual(r[3], 10, '0dcedfff-a17a-4d33-a951-8d3994c204be');
        assert.strictEqual(r[4], 2, '084da996-c6fb-4c27-8990-7892b4f6028d');
        
        // Runner on second advances
        r = sim.result_fb([5,5,5], 15, [7,-1,18,-1,1]);
        assert.strictEqual(r[0], 7, '932d9991-6e8f-4ce4-9852-26756a83605f');
        assert.strictEqual(r[1], -1, '760394a2-09d2-4358-a19f-c65dbeaec473');
        assert.strictEqual(r[2], -1, '028e93b8-c641-4404-9a37-26f5022f6b4d');
        assert.strictEqual(r[3], 18, 'cf7a00b8-58ec-4f68-bb50-37b55c03a8b5');
        assert.strictEqual(r[4], 2, '7bbe5f9d-467a-4706-9067-38102f130729');
        
      },
      
      result_bb: function () {
        var r;
        r = sim.result_bb(0, 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'Walk with bases empty, score stays the same');
        assert.strictEqual(r[1], 15, 'Walk with bases empty, runner on first');
        assert.strictEqual(r[2], -1, 'Walk with bases empty, second empty');
        assert.strictEqual(r[3], -1, 'Walk with bases empty, third empty');
        assert.strictEqual(r[4], 0, 'Walk with bases empty, outs stay the same');
        
        r = sim.result_bb(0, 18, [5,15,10,22,0])
        assert.strictEqual(r[0], 6, 'Walk with bases full, score increases by one');
        assert.strictEqual(r[1], 18, 'Walk with bases full, batter on first');
        assert.strictEqual(r[2], 15, 'Walk with bases full, runner from first to second');
        assert.strictEqual(r[3], 10, 'Walk with bases full, runner from second to third');
        assert.strictEqual(r[4], 0, 'Walk with bases full, outs stay the same');
        
        r = sim.result_bb([5,5,5], 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'Walk with bases empty, score stays the same');
        assert.strictEqual(r[1], 15, 'Walk with bases empty, runner on first');
        assert.strictEqual(r[2], -1, 'Walk with bases empty, second empty');
        assert.strictEqual(r[3], -1, 'Walk with bases empty, third empty');
        assert.strictEqual(r[4], 0, 'Walk with bases empty, outs stay the same');
        
        r = sim.result_bb([5,5,5], 18, [5,15,10,22,0])
        assert.strictEqual(r[0], 6, 'Walk with bases full, score increases by one');
        assert.strictEqual(r[1], 18, 'Walk with bases full, batter on first');
        assert.strictEqual(r[2], 15, 'Walk with bases full, runner from first to second');
        assert.strictEqual(r[3], 10, 'Walk with bases full, runner from second to third');
        assert.strictEqual(r[4], 0, 'Walk with bases full, outs stay the same');
      },
      
      result_1b: function () {
        var r;
        r = sim.result_1b(0, 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'Single with bases empty, score stays the same');
        assert.strictEqual(r[1], 15, 'Single with bases empty, runner on first');
        assert.strictEqual(r[2], -1, 'single with bases empty, second vacant');
        assert.strictEqual(r[3], -1, 'single with bases empty, third vacant');
        assert.strictEqual(r[4], 0, 'single with bases empty, outs stay the same');
        
        r = sim.result_1b(0, 18, [5,-1,10,22,0]);
        assert.strictEqual(r[0], 6, 'Single with runners on second and third, score');
        assert.strictEqual(r[1], 18, 'Single with runners on second and third, first');
        assert.strictEqual(r[2], -1, 'Single with runners on second and third, second');
        assert.strictEqual(r[3], 10, 'Single with runners on second and third, third');
        assert.strictEqual(r[4], 0, 'Single with runners on second and third, outs');
        
        r = sim.result_1b(0, 18, [5,22,-1,-1,0]);
        assert.strictEqual(r[0], 5, 'Single with runner on first, score');
        assert.strictEqual(r[1], 18, 'Single with runner on first, first');
        assert.strictEqual(r[2], 22, 'Single with runner on first, second');
        assert.strictEqual(r[3], -1, 'Single with runner on first, third');
        assert.strictEqual(r[4], 0, 'Single with runner on first, outs');
        
        r = sim.result_1b(0, 18, [5,15,10,22,0]);
        assert.strictEqual(r[0], 6, 'Single with bases loaded; score');
        assert.strictEqual(r[1], 18, 'Single with bases loaded; first');
        assert.strictEqual(r[2], 15, 'Single with bases loaded; second');
        assert.strictEqual(r[3], 10, 'Single with bases loaded; third');
        assert.strictEqual(r[4], 0, 'Single with bases loaded; outs');
        
        r = sim.result_1b([5,5,5], 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'dSingle with bases empty, score stays the same');
        assert.strictEqual(r[1], 15, 'dSingle with bases empty, runner on first');
        assert.strictEqual(r[2], -1, 'dsingle with bases empty, second vacant');
        assert.strictEqual(r[3], -1, 'dsingle with bases empty, third vacant');
        assert.strictEqual(r[4], 0, 'dsingle with bases empty, outs stay the same');
        
        r = sim.result_1b([5,5,5], 18, [5,-1,10,22,0]);
        assert.strictEqual(r[0], 7, 'dSingle with runners on second and third, score +2');
        assert.strictEqual(r[1], 18, 'dSingle with runners on second and third; always same runner on first');
        assert.strictEqual(r[2], -1, 'dSingle with runners on second and third; second always empty');
        assert.strictEqual(r[3], -1, 'dSingle with runners on second and third; third always empty');
        assert.strictEqual(r[4], 0, 'dSingle with runners on second and third, outs');
        
        r = sim.result_1b([5,5,5], 18, [5,22,-1,-1,0]);
        assert.strictEqual(r[0], 5, 'dSingle with runner on first, score');
        assert.strictEqual(r[1], 18, 'dSingle with runner on first, first');
        assert.strictEqual(r[2], 22, 'dSingle with runner on first, second');
        assert.strictEqual(r[3], -1, 'dSingle with runner on first, third');
        assert.strictEqual(r[4], 0, 'dSingle with runner on first, outs');
        
        r = sim.result_1b([5,5,5], 18, [5,15,10,22,0]);
        assert.strictEqual(r[0], 7, 'Using defense; Single with bases loaded; score +2');
        assert.strictEqual(r[1], 18, 'Using defense; Single with bases loaded; always same runner on first');
        assert.strictEqual(r[2], 15, 'Using defense; Single with bases loaded; second always contains runner from first');
        assert.strictEqual(r[3], -1, 'Using defense; Single with bases loaded; third always empty');
        assert.strictEqual(r[4], 0, 'Using defense; Single with bases loaded; outs same');
      },
      
      result_1bplus: function () {
        var r;
        r = sim.result_1bplus(0, 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'No Defense; Single plus with bases empty, score stays the same');
        assert.strictEqual(r[1], -1, 'No Defense; Single plus with bases empty, first vacant');
        assert.strictEqual(r[2], 15, 'No Defense; Single plus with bases empty, runner on second');
        assert.strictEqual(r[3], -1, 'No Defense; Single plus with bases empty, third vacant');
        assert.strictEqual(r[4], 0, 'No Defense; Single plus with bases empty, outs stay the same');
        
        r = sim.result_1bplus(0, 18, [5,-1,10,22,0]);
        assert.strictEqual(r[0], 6, 'No Defense; Single plus with runners on second and third, score +1');
        assert.strictEqual(r[1], -1, 'No Defense; Single plus with runners on second and third, first empty');
        assert.strictEqual(r[2], 18, 'No Defense; Single plus with runners on second and third, batter on second');
        assert.strictEqual(r[3], 10, 'No Defense; Single plus with runners on second and third, runner second to third');
        assert.strictEqual(r[4], 0, 'No Defense; Single plus with runners on second and third, outs stay the same');
        
        r = sim.result_1bplus(0, 18, [5,22,-1,-1,0]);
        assert.strictEqual(r[0], 5, 'No Defense; Single plus with runner on first, score same');
        assert.strictEqual(r[1], 18, 'No Defense; Single plus with runner on first, batter on first');
        assert.strictEqual(r[2], 22, 'No Defense; Single plus with runner on first, runner first to second');
        assert.strictEqual(r[3], -1, 'No Defense; Single plus with runner on first, third empty');
        assert.strictEqual(r[4], 0, 'No Defense; Single plus with runner on first, outs same');
        
        r = sim.result_1bplus(0, 18, [5,15,10,22,0]);
        assert.strictEqual(r[0], 6, 'No Defense; Single plus with bases loaded; score +1');
        assert.strictEqual(r[1], 18, 'No Defense; Single plus with bases loaded; batter on first');
        assert.strictEqual(r[2], 15, 'No Defense; Single plus with bases loaded; runner first to second');
        assert.strictEqual(r[3], 10, 'No Defense; Single plus with bases loaded; runner second to third');
        assert.strictEqual(r[4], 0, 'No Defense; Single plus with bases loaded; outs same');
        
        r = sim.result_1bplus([5,5,5], 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'Using Defense; Single plus with bases empty, score stays the same');
        assert.strictEqual(r[1], -1, 'Using Defense; Single plus with bases empty, first empty');
        assert.strictEqual(r[2], 15, 'Using Defense; Single plus with bases empty, runner on second');
        assert.strictEqual(r[3], -1, 'Using Defense; Single plus with bases empty, third vacant');
        assert.strictEqual(r[4], 0, 'Using Defense; Single plus with bases empty, outs stay the same');
        
        r = sim.result_1bplus([5,5,5], 18, [5,-1,10,22,0]);
        assert.strictEqual(r[0], 7, 'Using Defense; Single plus with runners on second and third, score +2');
        assert.strictEqual(r[1], -1, 'Using Defense; Single plus with runners on second and third; first empty');
        assert.strictEqual(r[2], 18, 'Using Defense; Single plus with runners on second and third; batter on second');
        assert.strictEqual(r[3], -1, 'Using Defense; Single plus with runners on second and third; third always empty');
        assert.strictEqual(r[4], 0, 'Using Defense; Single plus with runners on second and third, outs +1');
        
        r = sim.result_1bplus([5,5,5], 18, [5,22,-1,-1,0]);
        assert.strictEqual(r[0], 5, 'Using Defense; Single plus with runner on first, score same');
        assert.strictEqual(r[1], 18, 'Using Defense; Single plus with runner on first, batter on first');
        assert.strictEqual(r[2], 22, 'Using Defense; Single plus with runner on first, runner first to second');
        assert.strictEqual(r[3], -1, 'Using Defense; Single plus with runner on first, third empty');
        assert.strictEqual(r[4], 0, 'Using Defense; Single plus with runner on first, outs same');
        
        r = sim.result_1bplus([5,5,5], 18, [5,15,10,22,0]);
        assert.strictEqual(r[0], 7, 'Using defense; Single plus with bases loaded; score +2');
        assert.strictEqual(r[1], 18, 'Using defense; Single plus with bases loaded; batter on first');
        assert.strictEqual(r[2], 15, 'Using defense; Single plus with bases loaded; runner first to second');
        assert.strictEqual(r[3], -1, 'Using defense; Single plus with bases loaded; third always empty');
        assert.strictEqual(r[4], 0, 'Using defense; Single plus with bases loaded; if 2 runs score then no outs made');
        
      },
      
      result_2b: function () {
        var r;
        r = sim.result_2b(0, 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'No Defense; Double with bases empty, score stays the same');
        assert.strictEqual(r[1], -1, 'No Defense; Double with bases empty, first vacant');
        assert.strictEqual(r[2], 15, 'No Defense; Double with bases empty, runner on second');
        assert.strictEqual(r[3], -1, 'No Defense; Double with bases empty, third vacant');
        assert.strictEqual(r[4], 0, 'No Defense; Double with bases empty, outs stay the same');
        
        r = sim.result_2b(0, 18, [5,-1,10,22,0]);
        assert.strictEqual(r[0], 7, 'No Defense; Double with runners on second and third, score +2');
        assert.strictEqual(r[1], -1, 'No Defense; Double with runners on second and third, first empty');
        assert.strictEqual(r[2], 18, 'No Defense; Double with runners on second and third, batter on second');
        assert.strictEqual(r[3], -1, 'No Defense; Double with runners on second and third, third empty');
        assert.strictEqual(r[4], 0, 'No Defense; Double with runners on second and third, outs stay the same');
        
        r = sim.result_2b(0, 18, [5,22,-1,-1,0]);
        assert.strictEqual(r[0], 5, 'No Defense; Double with runner on first, score same');
        assert.strictEqual(r[1], -1, 'No Defense; Double with runner on first, first empty');
        assert.strictEqual(r[2], 18, 'No Defense; Double with runner on first, batter on second');
        assert.strictEqual(r[3], 22, 'No Defense; Double with runner on first, runner first to third');
        assert.strictEqual(r[4], 0, 'No Defense; Double with runner on first, outs same');
        
        r = sim.result_2b(0, 18, [5,15,10,22,0]);
        assert.strictEqual(r[0], 7, 'No Defense; Double with bases loaded; score +2');
        assert.strictEqual(r[1], -1, 'No Defense; Double with bases loaded; first empty');
        assert.strictEqual(r[2], 18, 'No Defense; Double with bases loaded; batter on second');
        assert.strictEqual(r[3], 15, 'No Defense; Double with bases loaded; runner first to third');
        assert.strictEqual(r[4], 0, 'No Defense; Double with bases loaded; outs same');
        
        r = sim.result_2b([5,5,5], 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'Using Defense; Double with bases empty, score stays the same');
        assert.strictEqual(r[1], -1, 'Using Defense; Double with bases empty, first empty');
        assert.strictEqual(r[2], 15, 'Using Defense; Double with bases empty, runner on second');
        assert.strictEqual(r[3], -1, 'Using Defense; Double with bases empty, third vacant');
        assert.strictEqual(r[4], 0, 'Using Defense; Double with bases empty, outs stay the same');
        
        r = sim.result_2b([5,5,5], 18, [5,-1,10,22,0]);
        assert.strictEqual(r[0], 7, 'Using Defense; Double with runners on second and third; score +2');
        assert.strictEqual(r[1], -1, 'Using Defense; Double with runners on second and third; first empty');
        assert.strictEqual(r[2], 18, 'Using Defense; Double with runners on second and third; batter on second');
        assert.strictEqual(r[3], -1, 'Using Defense; Double with runners on second and third; third empty');
        assert.strictEqual(r[4], 0, 'Using Defense; Double with runners on second and third, outs same');
        
        r = sim.result_2b([5,5,5], 18, [5,22,-1,-1,1]);
        assert.strictEqual(r[0], 5, 'Using Defense; Double with runners on second and third, score same');
        assert.strictEqual(r[1], -1, 'Using Defense; Double with runner on first, first empty');
        assert.strictEqual(r[2], 18, 'Using Defense; Double with runner on first, batter on second');
        assert.strictEqual(r[3], 22, 'Using Defense; Double with runner on first, runner on third');
        assert.strictEqual(r[4], 1, 'Using Defense; Double with runners on second and third, outs same');
        
        r = sim.result_2b([5,5,5], 18, [5,22,-1,-1,2]);
        assert.strictEqual(r[0], 6, 'Using Defense; Double with runners on first, score +1');
        assert.strictEqual(r[1], -1, 'Using Defense; Double with runner on first, first empty');
        assert.strictEqual(r[2], 18, 'Using Defense; Double with runner on first, batter on second');
        assert.strictEqual(r[3], -1, 'Using Defense; Double with runner on first, third empty');
        assert.strictEqual(r[4], 2, 'Using Defense; Double with runners on first, outs same');
        
        r = sim.result_2b([5,5,5], 18, [5,17,-1,-1,2]);
        assert.strictEqual(r[0], 5, 'Using Defense; Double with speed B runner on first, score same');
        assert.strictEqual(r[1], -1, 'Using Defense; Double with speed B runner on first, first empty');
        assert.strictEqual(r[2], 18, 'Using Defense; Double with speed B runner on first, batter on second');
        assert.strictEqual(r[3], 17, 'Using Defense; Double with speed B runner on first, runner on third');
        assert.strictEqual(r[4], 2, 'Using Defense; Double with speed B runner on first, outs still 2');
        
        r = sim.result_2b([5,5,5], 18, [5,20,10,22,0]);
        assert.strictEqual(r[0], 7, 'Using defense; Double with bases loaded speed A on first no outs, score same');
        assert.strictEqual(r[1], -1, 'Using defense; Double with bases loaded speed A on first no outs,; first empty');
        assert.strictEqual(r[2], 18, 'Using defense; Double with bases loaded speed A on first no outs,; batter on second');
        assert.strictEqual(r[3], 20, 'Using defense; Double with bases loaded speed A on first no outs,; first to third');
        assert.strictEqual(r[4], 0, 'Using defense; Double with bases loaded speed A on first no outs; still no outs');
        
      },
      
      result_3b: function () {
        var r;
        r = sim.result_3b(0, 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'No Defense; Triple with bases empty, score stays the same');
        assert.strictEqual(r[1], -1, 'No Defense; Triple with bases empty, first vacant');
        assert.strictEqual(r[2], -1, 'No Defense; Triple with bases empty, second vacant');
        assert.strictEqual(r[3], 15, 'No Defense; Triple with bases empty, batter on third');
        assert.strictEqual(r[4], 0, 'No Defense; Triple with bases empty, outs stay the same');
        
        r = sim.result_3b(0, 18, [5,-1,10,22,0]);
        assert.strictEqual(r[0], 7, 'No Defense; Triple with runners on second and third, score +2');
        assert.strictEqual(r[1], -1, 'No Defense; Triple with runners on second and third, first empty');
        assert.strictEqual(r[2], -1, 'No Defense; Triple with runners on second and third, second empty');
        assert.strictEqual(r[3], 18, 'No Defense; Triple with runners on second and third, batter on third');
        assert.strictEqual(r[4], 0, 'No Defense; Triple with runners on second and third, outs stay the same');
        
        r = sim.result_3b(0, 18, [5,22,-1,-1,0]);
        assert.strictEqual(r[0], 6, 'No Defense; Triple with runner on first, score +1');
        assert.strictEqual(r[1], -1, 'No Defense; Triple with runner on first, first empty');
        assert.strictEqual(r[2], -1, 'No Defense; Triple with runner on first, second empty');
        assert.strictEqual(r[3], 18, 'No Defense; Triple with runner on first, batter on third');
        assert.strictEqual(r[4], 0, 'No Defense; Triple with runner on first, outs same');
        
        r = sim.result_3b(0, 18, [5,15,10,22,0]);
        assert.strictEqual(r[0], 8, 'No Defense; Triple with bases loaded; score +3');
        assert.strictEqual(r[1], -1, 'No Defense; Triple with bases loaded; first empty');
        assert.strictEqual(r[2], -1, 'No Defense; Triple with bases loaded; second empty');
        assert.strictEqual(r[3], 18, 'No Defense; Triple with bases loaded; batter on third');
        assert.strictEqual(r[4], 0, 'No Defense; Triple with bases loaded; outs same');
        
        r = sim.result_3b([5,5,5], 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 0, 'Using Defense; Triple with bases empty, score stays the same');
        assert.strictEqual(r[1], -1, 'Using Defense; Triple with bases empty, first vacant');
        assert.strictEqual(r[2], -1, 'Using Defense; Triple with bases empty, second vacant');
        assert.strictEqual(r[3], 15, 'Using Defense; Triple with bases empty, batter on third');
        assert.strictEqual(r[4], 0, 'Using Defense; Triple with bases empty, outs stay the same');
        
        r = sim.result_3b([5,5,5], 18, [5,-1,10,22,0]);
        assert.strictEqual(r[0], 7, 'Using Defense; Triple with runners on second and third, score +2');
        assert.strictEqual(r[1], -1, 'Using Defense; Triple with runners on second and third, first empty');
        assert.strictEqual(r[2], -1, 'Using Defense; Triple with runners on second and third, second empty');
        assert.strictEqual(r[3], 18, 'Using Defense; Triple with runners on second and third, batter on third');
        assert.strictEqual(r[4], 0, 'Using Defense; Triple with runners on second and third, outs stay the same');
        
        r = sim.result_3b([5,5,5], 18, [5,22,-1,-1,0]);
        assert.strictEqual(r[0], 6, 'Using Defense; Triple with runner on first, score +1');
        assert.strictEqual(r[1], -1, 'Using Defense; Triple with runner on first, first empty');
        assert.strictEqual(r[2], -1, 'Using Defense; Triple with runner on first, second empty');
        assert.strictEqual(r[3], 18, 'Using Defense; Triple with runner on first, batter on third');
        assert.strictEqual(r[4], 0, 'Using Defense; Triple with runner on first, outs same');
        
        r = sim.result_3b([5,5,5], 18, [5,15,10,22,0]);
        assert.strictEqual(r[0], 8, 'Using Defense; Triple with bases loaded; score +3');
        assert.strictEqual(r[1], -1, 'Using Defense; Triple with bases loaded; first empty');
        assert.strictEqual(r[2], -1, 'Using Defense; Triple with bases loaded; second empty');
        assert.strictEqual(r[3], 18, 'Using Defense; Triple with bases loaded; batter on third');
        assert.strictEqual(r[4], 0, 'Using Defense; Triple with bases loaded; outs same');
      },
      
      result_hr: function () {
        var r;
        r = sim.result_hr(0, 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 1, 'No Defense; Home Run with bases empty, score +1');
        assert.strictEqual(r[1], -1, 'No Defense; Home Run with bases empty, first vacant');
        assert.strictEqual(r[2], -1, 'No Defense; Home Run with bases empty, second vacant');
        assert.strictEqual(r[3], -1, 'No Defense; Home Run with bases empty, third vacant');
        assert.strictEqual(r[4], 0, 'No Defense; Home Run with bases empty, outs stay the same');
        
        r = sim.result_hr(0, 18, [5,-1,10,22,0]);
        assert.strictEqual(r[0], 8, 'No Defense; Home Run with runners on second and third, score +3');
        assert.strictEqual(r[1], -1, 'No Defense; Home Run with runners on second and third, first empty');
        assert.strictEqual(r[2], -1, 'No Defense; Home Run with runners on second and third, second empty');
        assert.strictEqual(r[3], -1, 'No Defense; Home Run with runners on second and third, third empty');
        assert.strictEqual(r[4], 0, 'No Defense; Home Run with runners on second and third, outs stay the same');
        
        r = sim.result_hr(0, 18, [5,22,-1,-1,0]);
        assert.strictEqual(r[0], 7, 'No Defense; Home Run with runner on first, score +2');
        assert.strictEqual(r[1], -1, 'No Defense; Home Run with runner on first, first empty');
        assert.strictEqual(r[2], -1, 'No Defense; Home Run with runner on first, second empty');
        assert.strictEqual(r[3], -1, 'No Defense; Home Run with runner on first, third empty');
        assert.strictEqual(r[4], 0, 'No Defense; Home Run with runner on first, outs same');
        
        r = sim.result_hr(0, 18, [5,15,10,22,0]);
        assert.strictEqual(r[0], 9, 'No Defense; Home Run with bases loaded; score +4');
        assert.strictEqual(r[1], -1, 'No Defense; Home Run with bases loaded; first empty');
        assert.strictEqual(r[2], -1, 'No Defense; Home Run with bases loaded; second empty');
        assert.strictEqual(r[3], -1, 'No Defense; Home Run with bases loaded; third empty');
        assert.strictEqual(r[4], 0, 'No Defense; Home Run with bases loaded; outs same');
        
        r = sim.result_hr([5,5,5], 15, [0,-1,-1,-1,0]);
        assert.strictEqual(r[0], 1, 'Using Defense; Home Run with bases empty, score +1');
        assert.strictEqual(r[1], -1, 'Using Defense; Home Run with bases empty, first vacant');
        assert.strictEqual(r[2], -1, 'Using Defense; Home Run with bases empty, second vacant');
        assert.strictEqual(r[3], -1, 'Using Defense; Home Run with bases empty, third vacant');
        assert.strictEqual(r[4], 0, 'Using Defense; Home Run with bases empty, outs stay the same');
        
        r = sim.result_hr([5,5,5], 18, [5,-1,10,22,0]);
        assert.strictEqual(r[0], 8, 'Using Defense; Home Run with runners on second and third, score +3');
        assert.strictEqual(r[1], -1, 'Using Defense; Home Run with runners on second and third, first empty');
        assert.strictEqual(r[2], -1, 'Using Defense; Home Run with runners on second and third, second empty');
        assert.strictEqual(r[3], -1, 'Using Defense; Home Run with runners on second and third, third empty');
        assert.strictEqual(r[4], 0, 'Using Defense; Home Run with runners on second and third, outs stay the same');
        
        r = sim.result_hr([5,5,5], 18, [5,22,-1,-1,0]);
        assert.strictEqual(r[0], 7, 'Using Defense; Home Run with runner on first, score +2');
        assert.strictEqual(r[1], -1, 'Using Defense; Home Run with runner on first, first empty');
        assert.strictEqual(r[2], -1, 'Using Defense; Home Run with runner on first, second empty');
        assert.strictEqual(r[3], -1, 'Using Defense; Home Run with runner on first, third empty');
        assert.strictEqual(r[4], 0, 'Using Defense; Home Run with runner on first, outs same');
        
        r = sim.result_hr([5,5,5], 18, [5,15,10,22,0]);
        assert.strictEqual(r[0], 9, 'Using Defense; Home Run with bases loaded; score +4');
        assert.strictEqual(r[1], -1, 'Using Defense; Home Run with bases loaded; first empty');
        assert.strictEqual(r[2], -1, 'Using Defense; Home Run with bases loaded; second empty');
        assert.strictEqual(r[3], -1, 'Using Defense; Home Run with bases loaded; third empty');
        assert.strictEqual(r[4], 0, 'Using Defense; Home Run with bases loaded; outs same');
      }
      
    });
});