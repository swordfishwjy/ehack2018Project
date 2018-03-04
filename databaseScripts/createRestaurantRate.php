<?php

// assume 100 users, randomly rate for 10 restaurant

$users = 100;
$restaurants = 10;
$dbhost = "localhost";
$dbuser = "root";
$password = "";
$dbname = "restaurant";
$tableName = "userReview";

$conn = mysqli_connect($dbhost, $dbuser, $password, $dbname);
if(!$conn){
	die('could not conenct: ' . mysqli_connect_error());
}


for($x = 1; $x < ($users+1); $x++){
	for($y = 1; $y < ($restaurants+1); $y++){
		$randNum = rand(1,3);
		if($randNum > 1){
			$rate = rand(0,5);
			mysqli_query($conn, "INSERT INTO $tableName(user, place, rate) VALUES($x, $y, $rate)");
		}
	}	
}

?>