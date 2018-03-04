<?php

// calcluate the avgRate for each tagContent, each place

$users = 100;
$restaurants = 10;
$dbhost = "localhost";
$dbuser = "root";
$password = "";
$dbname = "restaurant";
$tableName = "tagAvgRate";

$conn = mysqli_connect($dbhost, $dbuser, $password, $dbname);
if(!$conn){
	die('could not conenct: ' . mysqli_connect_error());
}


$tag = array("sex", "age", "personality", "interest", "budget", "weight", "race", "academy", "religion");
$tagCont = array("male","female","young","adult","old","hardworking","conformist","tech","business","education",
	"low","medium","high","fat","normal","thin","American","Asian","African","BC","MS","DR","No","Catholic","Christian");

$number = count($tagCont);

// loop for restaurant
for($r = 1; $r<=$restaurants; $r++){
	$tagCount = array();
	// 置零
	for ($i = 0; $i < $number; $i++){
		array_push($tagCount, 0);
	}
	$tagRate = array();
	for ($i = 0; $i < $number; $i++){
		array_push($tagRate, 0);
	}

	$result = mysqli_query($conn, "SELECT user FROM userReview WHERE place = $r");
	$userArray = array();
	while($row = mysqli_fetch_assoc($result)){
		array_push($userArray, $row["user"]);
	}
	// for($x = 0; $x < count($userArray); $x++) {
	//     echo $userArray[$x];
	//     echo "<br>";
	// }
	$userArrayLenth = count($userArray);
	//对于一个place的一个reviewer的各种tag进行遍历
	for ($j=0; $j < $userArrayLenth ; $j++) { 
		for ($t=0; $t < $number; $t++) {
			$userID = $userArray[$j];
			$result = mysqli_query($conn, "SELECT * FROM userTag WHERE user = $userID");
			while($row = mysqli_fetch_assoc($result)){
				for ($y=0; $y < count($tag); $y++) { 
					if($tagCont[$t] == $row[$tag[$y]]){
						$tagCount[$t]+= 1;
						$result1 = mysqli_query($conn, "SELECT rate FROM userReview WHERE user = $userID and place = $r");
						while($row1 = mysqli_fetch_assoc($result1)){
							$tagRate[$t] += $row1["rate"];
						}
					}
				}
			}
		}
	}
	$tagAvgRate = array();
	for ($i=0; $i < $number; $i++) { 
		array_push($tagAvgRate,round($tagRate[$i]/$tagCount[$i], 2));
	}
	for ($i=0; $i < $number ; $i++) { 
		mysqli_query($conn, "INSERT INTO tagAvgRate(tagContent, place, rate) VALUES('$tagCont[$i]', $r, $tagAvgRate[$i])");
	}
}

?>