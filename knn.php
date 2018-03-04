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
	// 	for ($j=0; $j < $number; $j++) { 
			
	// 	}
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

// echo join($tenPlaceNormalRate,',');
print_r($finalRateList);
echo "\n";
print_r($tenPlaceNormalRate);
// $test = array("1"=>2, "2"=>1);
// krsort($test);
// print_r(gettype($userlist[0]));

?>