<!-- Author: Jianyu Wnag; Yang Huang; Hanqing Zhao
Date: 03.04.2018
Event: 2018 ehacks -->
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1" />
    <meta name="description" content="description of your site" />
    <meta name="author" content="author of the site" />
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <title>Real Restaurant Rating</title>
    <link rel="stylesheet" href="css/bootstrap.css" />
    <link rel="stylesheet" href="css/bootstrap-responsive.css" />
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900,100italic,300italic,400italic,700italic,900italic" />
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/toastr.css" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/jquery.knob.js"></script>
    <script src="http://d3js.org/d3.v3.min.js"></script>
    <script src="js/jquery.sparkline.min.js"></script>
    <script src="js/toastr.js"></script>
    <script src="js/jquery.tablesorter.min.js"></script>
    <script src="js/jquery.peity.min.js"></script>
    <script src="js/fullcalendar.min.js"></script>
    <script src="js/gcal.js"></script>
    <script src="js/setup.js"></script>
    

    
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
  <body>
    <script src="js/highcharts.js"></script>
    <script src="js/exporting.js"></script>
    <div id="in-nav">
      <div class="container">
        <div class="row">
          <div class="span12">
            <ul class="pull-right">
            </ul><h4>KNN-based <strong>Restaurant Rating</strong></h4></a>
          </div>
        </div>
      </div>
    </div>
    <div id="in-sub-nav">
      <div class="container">
        <div class="row">
          <div class="span12">
            <ul>
              <li><a href="index.html"><i class="batch home"></i><br />
Home</a></li>
              <li><a href="rating.html" class="active"><i class="batch heatmap"></i><br />Rating</a></li>
              <li><a href="compare.php"><i class="batch histogram"></i><br />Compare</a></li>
              <!-- <li><a href="ranklist.php"><i class="batch Ranklist"></i><br />Ranklist</a></li> -->
            </ul>
          </div>
        </div>
      </div>
    </div>

    <?php

// $input = array("male","young","conformist","tech","low","fat","American","BC","No");
   $input = array();

  array_push($input, $_POST['SexOptions']);
  array_push($input, $_POST['AgeOptions']);
  array_push($input, $_POST['PerOptions']);
  array_push($input, $_POST['IntOptions']);
  array_push($input, $_POST['BudOptions']);
  array_push($input, $_POST['WeiOptions']);
  array_push($input, $_POST['RacOptions']);
  array_push($input, $_POST['Aca-options']);
  array_push($input, $_POST['Rel-options']);

  $chooseRest = intval($_POST['restaurantNo']);
  // print_r($input);

// calculate our KNN-based rating result, using user's input tag information

$users = 100;
$restaurants = 10;
$dbhost = "localhost";
$dbuser = "root";
$password = "";
$dbname = "restaurant";
$firstK = 6;
$finalRateList = array();

$conn = mysqli_connect($dbhost, $dbuser, $password, $dbname);
if(!$conn){
  die('could not conenct: ' . mysqli_connect_error());
}

// calcuate the normal average rate for ten places.
$tenPlaceNormalRate = array();
for ($i=1; $i <=$restaurants; $i++) { 
  $temp = 0.0;
  $counter = 0.0;
  $result = mysqli_query($conn, "SELECT rate FROM userReview WHERE place = $i");
  while($row = mysqli_fetch_assoc($result)){
    $counter++;
    $temp += $row["rate"];
  }
  array_push($tenPlaceNormalRate, round($temp/$counter,2));
 } 


$tag = array("sex", "age", "personality", "interest", "budget", "weight", "race", "academy", "religion");

$number = count($tag);



// store the tag rates for ten places
$tenPlaceTagRate = array();

for ($i=1; $i <= $restaurants ; $i++) { 
  // $result = mysqli_query($conn, "SELECT tagContent, rate FROM tagAvgRate WHERE place = $i");
  // while($row = mysqli_fetch_assoc($result)){
  //  for ($j=0; $j < $number; $j++) { 
      
  //  }
  // }
  $eachPlaceTagRate = array();
  for ($j=0; $j < $number; $j++) { 
    $result = mysqli_query($conn, "SELECT rate FROM tagAvgRate WHERE place = $i and tagContent = '$input[$j]'");
    while($row = mysqli_fetch_assoc($result)){
      $eachPlaceTagRate[$j] = $row["rate"];
    }
  }
  array_push($tenPlaceTagRate, $eachPlaceTagRate);
}
// print_r(count($tenPlaceTagRate));

//calculate the knn rating for place 1, k =6
for ($p=1; $p <=$restaurants ; $p++) { 


  // step1: initiate $trainingArray
  $trainingArray = array(); // store tags rate for all the place1's reviewers
  $userlist = array(); // store key: "userID"
  $tagRatelist = array(); // store value" "[Rate1,Rate2......Rate 9]"
  $onelineTagRate = array();
  $result = mysqli_query($conn, "SELECT * FROM userPlaceTag WHERE place = $p");
  while($row = mysqli_fetch_assoc($result)){
    array_push($userlist, strval($row["user"]));
    array_push($onelineTagRate, $row["sex"]);
    array_push($onelineTagRate, $row["age"]);
    array_push($onelineTagRate, $row["personality"]);
    array_push($onelineTagRate, $row["interest"]);
    array_push($onelineTagRate, $row["budget"]);
    array_push($onelineTagRate, $row["weight"]);
    array_push($onelineTagRate, $row["race"]);
    array_push($onelineTagRate, $row["academy"]);
    array_push($onelineTagRate, $row["religion"]);
    array_push($tagRatelist, $onelineTagRate);
    $onelineTagRate = array();
  }
  $trainingArray = array_combine($userlist, $tagRatelist);


  // step2: start calcuate the distances from input to all user.
  $currentInput = $tenPlaceTagRate[$p-1];
  $distanceList = array();
  $userNum = count($userlist);
  for ($i=0; $i < $userNum; $i++) { // all users
    $distanceCounter = 0;
    for ($j=0; $j < $number; $j++) { //all tags
      $distanceCounter += pow(($currentInput[$j]-$trainingArray[$userlist[$i]][$j]),2);
    }
    array_push($distanceList, $distanceCounter);
  }
  // store all the users' distance calculation result. ("userID"=>distance)
  $trainingDistrances = array_combine($userlist, $distanceList);
  asort($trainingDistrances); //对一个input针对于一个place的所有user计算的distance进行从小到大排名

  // step3: get the first K=6 users' actual rating value
  $kUserlist = array();
  $finalRate_Sum = 0;
  for ($k=0; $k < $firstK; $k++) {
    $currentUserID = array_keys($trainingDistrances)[$k];
    $result = mysqli_query($conn, "SELECT rate FROM userReview WHERE place = $p and user = $currentUserID");
    while($row = mysqli_fetch_assoc($result)){
      $finalRate_Sum += $row["rate"];
    }
  }
  $finalRate = round($finalRate_Sum/$firstK, 2);
  // echo $finalRate;
  array_push($finalRateList, $finalRate);
  $sortFinalRateList = rsort($finalRateList);
}

// print_r($finalRateList);
// echo "\n";
// print_r($tenPlaceNormalRate);
// $test = array("1"=>2, "2"=>1);
// krsort($test);
// print_r(gettype($userlist[0]));

?>
  

<div style="width: 100%; overflow: hidden;">
<div style="width: 600px; float: left;">
<p></p>
<form action="rating.php" method="post">
        <div class="control-group">
            <h6 class="text-primary F-label">Sex:</h6>&nbsp;&nbsp;
            <div class="btn-group " data-toggle="buttons">
                <label class="btn btn-primary active">
                    <input type="radio" name="SexOptions" id="SexOption1" autocomplete="off" value="male" checked/>Male
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="SexOptions" id="SexOption2" autocomplete="off" value="female" />Female
                </label>
            </div>
        </div>

        <div class="control-group">
            <h6 class="text-primary F-label">Age:</h6>&nbsp;&nbsp;
            <div class="btn-group control-group" data-toggle="buttons">
                <label class="btn btn-primary active">
                    <input type="radio" name="AgeOptions" id="AgeOption1" autocomplete="off" value="young" checked/>Young
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="AgeOptions" id="AgeOption2" autocomplete="off" value="adult"/>Adult
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="AgeOptions" id="AgeOption3" autocomplete="off" value="old"/>Old
                </label>
            </div>
        </div>

        <div class="control-group">
            <h6 class="text-primary F-label">Personality:</h6>&nbsp;&nbsp;
            <div class="btn-group control-group" data-toggle="buttons">
                <label class="btn btn-primary active">
                    <input type="radio" name="PerOptions" id="PerOption1" autocomplete="off" value="hardworking" checked/>Hardworking
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="PerOptions" id="PerOption2" autocomplete="off" value="conformist" />Conformist
                </label>
            </div>
        </div>

        <div class="control-group">
            <h6 class="text-primary F-label">Interest:</h6>&nbsp;&nbsp;
            <div class="btn-group control-group" data-toggle="buttons">
                <label class="btn btn-primary active">
                    <input type="radio" name="IntOptions" id="IntOption1" autocomplete="off" value="tech" checked/>Tech
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="IntOptions" id="IntOption2" autocomplete="off" value="business"/>Business
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="IntOptions" id="IntOption3" autocomplete="off" value="education" />Education
                </label>
            </div>
        </div>

        <div class="control-group">
            <h6 class="text-primary F-label">Budget:</h6>&nbsp;&nbsp;
            <div class="btn-group control-group" data-toggle="buttons">
                <label class="btn btn-primary active">
                    <input type="radio" name="BudOptions" id="BudOption1" autocomplete="off" value="low" checked/>Low
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="BudOptions" id="BudOption2" autocomplete="off" value="medium" />Medium
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="BudOptions" id="BudOption3" autocomplete="off" value="High" />High
                </label>
            </div>
        </div>

        <div class="control-group">
            <h6 class="text-primary F-label">Weight:</h6>&nbsp;&nbsp;
            <div class="btn-group control-group" data-toggle="buttons">
                <label class="btn btn-primary active">
                    <input type="radio" name="WeiOptions" id="WeiOption1" autocomplete="off" value="fat" checked/>Fat
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="WeiOptions" id="WeiOption2" autocomplete="off" value="normal" />Normal
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="WeiOptions" id="WeiOption3" autocomplete="off" value="thin" />Thin
                </label>
            </div>
        </div>

        <div class="control-group">
            <h6 class="text-primary F-label">Race:</h6>&nbsp;&nbsp;
            <div class="btn-group control-group" data-toggle="buttons">
                <label class="btn btn-primary active">
                    <input type="radio" name="RacOptions" id="RacOption1" autocomplete="off" value="American" checked/>American
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="RacOptions" id="RacOption2" autocomplete="off" value="Asian" />Asian
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="RacOptions" id="RacOption3" autocomplete="off" value="African" />African
                </label>
            </div>
        </div>

        <div class="control-group">
            <h6 class="text-primary F-label">Academy:</h6>&nbsp;&nbsp;
            <div class="btn-group control-group" data-toggle="buttons">
                <label class="btn btn-primary active">
                    <input type="radio" name="Aca-options" id="Aca-option1" autocomplete="off" value="BC" checked/>BC
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="Aca-options" id="Aca-option2" autocomplete="off" value="MS" />MS
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="Aca-options" id="Aca-option3" autocomplete="off" value="DR" />DR
                </label>
            </div>
        </div>

        <div class="control-group">
            <h6 class="text-primary F-label">Religion:</h6>&nbsp;&nbsp;
            <div class="btn-group control-group" data-toggle="buttons">
                <label class="btn btn-primary active">
                    <input type="radio" name="Rel-options" id="Rel-option1" autocomplete="off" value="No" checked/>No
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="Rel-options" id="Rel-option2" autocomplete="off" value="Catholic" />Catholic
                </label>
                <label class="btn btn-primary">
                    <input type="radio" name="Rel-options" id="Rel-option3" autocomplete="off" value="Christian" />Christian
                </label>
            </div>
        </div>
        
       Which Restaurant: (1~10)<br>
        <input type="text" name="restaurantNo" id = "restaurantNo" ><br>
        <input class="btn btn-primary" type="submit" name="submit" value="Submit" />
    </form>
</div>

<br></br>
<br></br>
<div style="margin-left: 620px;">
        <br><br>
        <p style="font-size:40px;">Restaurant No.<?php echo $chooseRest; ?> Rate for you:</p>
        <br>
        <br>
        <p style="font-size:66px; color: Tomato;" align="justify"> <?php echo $finalRateList[$chooseRest-1]; ?></p>

        <br>
        <br>
        <!-- <p style="font-size:40px;">Top 3 restaurant recommand for you.:</p>
        <ul style="list-style-type:circle;font-size:40px; color: Tomato;">
          <li></li>
          <li>Tea</li>
          <li>Milk</li>
        </ul>  -->
</div>

<!-- <div style="width: 100%; overflow: hidden;">
    <div style="width: 600px; float: left;"> Left </div>
    <div style="margin-left: 620px;"> Right </div>
</div> -->

<!-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++-->
</div>


    <footer>
      <div class="container">
        <div class="row">
          <div class="span12">
            <p>&copy; Copyright 2018 ehacks</p>
          </div>
        </div>
      </div>
    </footer>
  </body>
  <script src="js/d3-setup.js"></script><script>protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://'; address = protocol + window.location.host + window.location.pathname + '/ws'; socket = new WebSocket(address);
socket.onmessage = function(msg) { msg.data == 'reload' && window.location.reload() }</script>
</html>