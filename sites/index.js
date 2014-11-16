// Create a server and do work!
var express = require('express');
var bodyParser = require('body-parser');
var simulation = require('./simulation');
var app = express();

//app.use(express.bodyParser());
app.use(express.static(__dirname ));
//app.use(bodyParser.json()); // to support JSON-encoded bodies
app.use(bodyParser.urlencoded({ extended: true})); // extended: true  allows for a JSON-like experience
//app.use(express.static(path.join(__dirname, './'))); 

// app.post('/', function(req, res) {
  // if (req.body.length > 1e6) { // 1MB - Ain't nobody got time for that
      // req.connection.destroy();
      // response.writeHead(413, 'Request Entity Too Large', {'Content-Type': 'text/html'});
      // response.end('<!doctype html><html><head><title>413</title></head><body>413: Request Entity Too Large</body></html>');
  // }
  // // console.log(req.body);
  // // res.json(req.body);
  // res.send(simulation.sim(req.body));
// });

app.get('/', function (req, res){
  res.sendFile('page.html', {root: __dirname});
});

// app.get('/simulate', function (req, res){
  // res.sendFile('page.html', {root: __dirname});
// });

app.get('/runsim', function(req, res){
  console.log(req.query);
  //console.log("1111");
  res.json(simulation.sim(req.query));
});

// app.post('/', function (req, res) {
  // res.send('Received POST ');
// })

var server = app.listen(8008, function (){
  console.log('Sever running at localhost:8008')
});