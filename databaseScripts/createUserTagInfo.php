<?php

// assume 100 users, randomly assign attribute to each tag of 9 tags
// tags: sex, age, personality, interest, budget, weight, race, academy, religion

$users = 100;
$restaurants = 10;
$dbhost = "localhost";
$dbuser = "root";
$password = "";
$dbname = "restaurant";
$tableName = "userTag";

$conn = mysqli_connect($dbhost, $dbuser, $password, $dbname);
if(!$conn){
	die('could not conenct: ' . mysqli_connect_error());
}


for($x = 1; $x < ($users+1); $x++){
	$random = rand(1,2);
	if($random == 1){
		$sex = "male";
	}else{
		$sex = "female";
	}

	$random = rand(1,3);
	if($random == 1){
		$age = "young";
	}elseif ($random == 2) {
		$age = "adult";
	}else{
		$age = "old";
	}

	$random = rand(1,2);
	if($random == 1){
		$personality = "hardworking";
	}else{
		$personality = "conformist";
	}

	$random = rand(1,3);
	if($random == 1){
		$interest = "tech";
	}elseif ($random == 2) {
		$interest = "business";
	}else{
		$interest = "education";
	}

	$random = rand(1,3);
	if($random == 1){
		$budget = "low";
	}elseif ($random == 2) {
		$budget = "medium";
	}else{
		$budget = "high";
	}

	$random = rand(1,3);
	if($random == 1){
		$weight = "fat";
	}elseif ($random == 2) {
		$weight = "normal";
	}else{
		$weight = "thin";
	}

	$random = rand(1,3);
	if($random == 1){
		$race = "American";
	}elseif ($random == 2) { 
		$race = "Asian";
	}else{
		$race = "African";
	}

	$random = rand(1,3);
	if($random == 1){
		$academy = "BC";
	}elseif ($random == 2) {
		$academy = "MS";
	}else{
		$academy = "DR";
	}

	$random = rand(1,3);
	if($random == 1){
		$religion = "No";
	}elseif ($random == 2) {
		$religion = "Catholic";
	}else{
		$religion = "Christian";
	}

	mysqli_query($conn, "INSERT INTO $tableName(user, sex, age, personality, interest, budget, weight, race, academy, religion) VALUES($x, '$sex', '$age','$personality','$interest', '$budget', '$weight','$race','$academy', '$religion')");
}


?>