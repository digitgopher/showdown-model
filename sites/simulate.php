<!DOCTYPE html>
<!--
Simulate MLB Showdown games and give the results!   
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Run Simulation</title>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" /> -->
        <!-- <link rel="stylesheet" href="jqueryform.css"/>-->
        <!-- <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>-->
        <script src="jqueryform.js"></script>
    </head>
    <body>
        <h1>MLB Form. Build client side.</h1>
        <div id="container">
        </div>

        <?php
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            foreach ($_POST as $key => $value) {
                echo $key." ".$value.'<br/>';
            }

//                        if($_POST['style'] != "placemark" && $_POST['style'] != "circle"){
//                                echo "Please submit correct data.";
//                        }
        }
        ?>
    </body>
</html>