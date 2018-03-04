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
              <li><a href="rating.html"><i class="batch heatmap"></i><br />Rating</a></li>
              <li><a href="compare.php" class="active"><i class="batch histogram"></i><br />Compare</a></li>
              <!-- <li><a href="ranklist.php"><i class="batch Ranklist"></i><br />Ranklist</a></li> -->
            </ul>
          </div>
        </div>
      </div>
    </div>
	
    <div class="page">
	
<?php

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

$input = array("male","young","conformist","tech","low","normal","American","BC","Christian");

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
}

// print_r($finalRateList);
// echo "\n";
// print_r($tenPlaceNormalRate);
// $test = array("1"=>2, "2"=>1);
// krsort($test);
// print_r(gettype($userlist[0]));

?>


<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>


    <script type="text/javascript">

Highcharts.chart('container', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Simple Average vs Personalized Rate'
    },
    subtitle: {
        text: 'sample input: ["male","young","conformist","tech","low","normal","American","BC","Christian"]'
    },
    xAxis: {
        categories: [
            'restaurant1',
            'restaurant2',
            'restaurant3',
            'restaurant4',
            'restaurant5',
            'restaurant6',
            'restaurant7',
            'restaurant8',
            'restaurant9',
            'restaurant10'
        ],
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Rating Score (0~5)'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    series: [{
        name: 'Simple Avage',
        data: [<?php echo join($tenPlaceNormalRate,','); ?>]

    }, {
        name: 'KNN-based',
        data: [<?php echo join($finalRateList,','); ?>]

    }]
});
    </script>	
<hr>



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