<?php

include 'MeterGeniusDatabaseConnection.php';

getPointScheduleDataForGraph();

# gives point schedule data in format required for graph to parse to js
function getPointScheduleDataForGraph(){

	$pointScheduleArray = getPointScheduleData();
	$graphData = array();

	foreach ($pointScheduleArray as $pointData){
	array_push($graphData, [$pointData[0].' '.$pointData[1] , $pointData[3]]);
	#echo $pointData[0].', '.$pointData[1]." # points ".$pointData[3]."<br>";
	}

	return $graphData;
}

# retrieves point schedule data from database in array format
function getPointScheduleData(){

	$pointScheduleArray = array();
	
	//$sql = "SELECT * FROM `Meter_Genius_Data`.`forecasted_point_schedule`";
	$sql = "SELECT * FROM `Meter_Genius_Data`.`forecasted_point_schedule` ORDER BY `date`,`time` asc";
	$pdo = getMySqlPDO();
	$stmt = $pdo->prepare($sql);
	
	
	
	if ($stmt->execute() and $stmt->rowCount() > 0) {
    // output data of each row
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$hourlyPoint = array($row["date"], $row["time"], $row["forecasted_price"],$row["forecasted_point_value"],$row["comed"]);
		array_push( $pointScheduleArray , $hourlyPoint);
		}
	} else {
    #echo "0 results";
	}
	
	#echo "<br>##########".sizeof($pointScheduleArray);
	return $pointScheduleArray;

}


?>