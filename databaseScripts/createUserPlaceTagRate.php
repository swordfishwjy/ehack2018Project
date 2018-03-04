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
$tagNumber = count($tag);
$tagCont = array("male","female","young","adult","old","hardworking","conformist","tech","business","education",
	"low","medium","high","fat","normal","thin","American","Asian","African","BC","MS","DR","No","Catholic","Christian");




for ($i=1; $i <= $users; $i++) { 
	for ($j=1; $j <= $restaurants ; $j++) { 
		# code...
		// check whether a user has reviewed a place
		$userlist = array();
		$result = mysqli_query($conn, "SELECT user FROM userReview WHERE place = $j");
		while($row = mysqli_fetch_assoc($result)){
			array_push($userlist, $row["user"]);
		}
		
		if(in_array($i,$userlist)){
			$currentRate = array();
			for ($n = 0; $n < $tagNumber; $n++){
				array_push($currentRate, 0);
			}
			$result = mysqli_query($conn, "SELECT * FROM userTag WHERE  user = $i");
			while($row = mysqli_fetch_assoc($result)){
				for ($x=0; $x < $tagNumber; $x++) { 
					$currentTagContent = $row[$tag[$x]];
					$result1 = mysqli_query($conn, "SELECT rate FROM tagAvgRate WHERE  tagContent = '$currentTagContent' and place = $j");
					while($row1 = mysqli_fetch_assoc($result1)){
						$currentRate[$x] = $row1["rate"];
						// mysqli_query($conn, "INSERT INTO (tagContent, place, rate) VALUES('$tagCont[$i]', $r, $tagAvgRate[$i])");
					}
				}
			}
			mysqli_query($conn, "INSERT INTO userPlaceTag(user,place,sex,age,personality,interest,budget,weight,
				race,academy,religion) VALUES($i, $j,'$currentRate[0]','$currentRate[1]','$currentRate[2]',
				'$currentRate[3]','$currentRate[4]','$currentRate[5]','$currentRate[6]','$currentRate[7]',
				'$currentRate[8]')");
		}

		
		
	}
}

?>