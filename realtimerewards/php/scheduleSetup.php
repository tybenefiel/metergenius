<?php

include 'MeterGeniusDatabaseConnection.php';
include 'MeterGeniusMetadata.php';



# creates rewards tables on the database without any data
function createRewardsDatabase(){

	# Executing sql create table script from file
	$url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$url .= $_SERVER['SERVER_NAME'];
	$url .= htmlspecialchars($_SERVER['REQUEST_URI']);
	$dir = "http://localhost/realtimerewards".'/database/RealTimeRewardsScript.sql';
	#echo $dir;
	$query = file_get_contents($dir);
	#echo "query is ". $query;
	$pdo = getMySqlPDO();
	$stmt = $pdo->prepare($query);
	var_dump($stmt);

	if ($stmt->execute())
     echo "Rewards tables creation success<br>";
	else 
     echo "Rewards tables creation Fail";

}

#inserts metadata on price-to-point table from script
function insertPriceToPointMetadata(){

	# Executing sql create table script from file
	$url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$url .= $_SERVER['SERVER_NAME'];
	$url .= htmlspecialchars($_SERVER['REQUEST_URI']);
	$dir = "http://localhost/realtimerewards".'/database/PriceToPointMetadataScript.sql';

	$query = file_get_contents($dir);
	#echo $query;
	$pdo = getMySqlPDO();
	$stmt = $pdo->prepare($query);
	var_dump($stmt);
	if ($stmt->execute())
     echo "Inserting pre-defined data on Price-to-Point table success<br>";
	else 
     echo "Insert on Price to Point table Fail";

}


	
# inserts Rewards data until previous day
function insertPreviousRewardsData(){
	
	#use below to get the links from rss feed xml : 1
	#$dateLinksArray = getPreviousRSSFeedLinks();
	
	#use below to get the links from function that forms the links from custom URL :2
	$dateLinksArray = getPJMDateLinks();
	
	$rowsInserted=0;
	
	#foreach(array_keys($dateLinksArray) as $date) { #: 1
	foreach($dateLinksArray as $dateLink){ #:2
	
	#$link = $dateLinksArray[$date]; #:1
	$link = $dateLink[1];
	$url = file_get_contents($link);
	
	$data = str_getcsv($url,"\n");  #data is array of lines
	
	$forecastedPriceData = getForecastedPriceData($dateLink[0],$data[2],$data[3]); 
	#contains 24 date,time and prices forecasted in array
	
	$comEdValues = getComEdLMPValues($data[15]); 
	# contains 24 TotalLMP values of COMED. COMED row is always the 15th row in the csv array
	
	$calculatedPointValues = getCalculatedPointValues($forecastedPriceData,$comEdValues); 
	#contains 24 forecastedPoints,COMED #lmp values
	
	$insertForecastQuery = getForecastQuery($forecastedPriceData,$comEdValues,$calculatedPointValues);
	#returns sql insert query
	
	#break;#only for 1 url
	
	$pdo = getMySqlPDO();
	$stmt = $pdo->prepare($insertForecastQuery);
	
	if ($stmt->execute())
		$rowsInserted = $rowsInserted + $stmt->rowCount();
	else 
		echo "Insert forecasted table Fail";
	 	
	}
    
	echo "forecasted table inserted with ".$rowsInserted." rows";

}

function getForecastedPriceData($date,$times,$prices){

   $forecastPriceData = array();
   $timedPriceData = array();
   
   $timeArray = explode(",",ltrim(rtrim($times,','),'Start of Day Ahead Energy Price Data,'));
   $priceArray = explode(",",ltrim(rtrim($prices,','),","));
   
   for($num = 0 ; $num<=23 ; $num++) {
	$convertedTime = intval($timeArray[$num])-100;
	$convertedTime = (string)$convertedTime;
   
	if ($convertedTime < 1000)
		$convertedTime = "0".$convertedTime;

	if ($convertedTime == 00)
		$convertedTime = "0000";
   
	$timedPriceData = [$date, (string)$convertedTime, $priceArray[$num]]; 
	array_push( $forecastPriceData , $timedPriceData);
   }
   
   return $forecastPriceData;
}

function getComEdLMPValues($comEdString){
    
	$comedArray = array();
	
	if (strpos($comEdString,'COMED') !== false) {
    $comData = explode(",", ltrim(rtrim($comEdString,','),','));
	
		for ($a=7 ; $a<77 ; $a++) { #getting LMP values based on column numbers
			array_push( $comedArray , $comData[$a]);
			$a=$a+2;
		}    
	}
	#echo sizeof($comedArray);
	return $comedArray;
}

function getPriceToPointValues(){

$pricePointArray = array();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Meter_Genius_Data";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "SELECT Price_Per_Unit, Point_Value FROM price_to_point_ratio";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
		
		$pricePointVal = array($row["Price_Per_Unit"], $row["Point_Value"]);
		array_push( $pricePointArray , $pricePointVal);
    }
} else {
    echo "0 results";
}
$conn->close();

return $pricePointArray;

}

function getCalculatedPointValues($forecastedPriceData,$comEdValues){

	$priceToPointValues = getPriceToPointValues();#returns array of price to point values
	$calculatedPointValues = array();
	$pricePoint = 0;
	$valuePoint = 0;
	$index1 = 0;#for forecastedPrices
	
	$forecastedPriceFloatVal = 0;
	settype($forecastedPriceFloatVal , "float");
	$ratioFloatVal = 0;
	settype($ratioFloatVal, "float");
	
	$temp2 = 0;
	settype($temp2, "float");
	
	foreach ($comEdValues as $lmpValue){
		foreach ($priceToPointValues as $pricePoint) {			
			if ($lmpValue < $pricePoint[0]){
			$temp2 = $pricePoint[1];
			break;
			} 		
		}
		$index1++;
		#$ratioFloatVal = $lmpValue/$temp2;
		#echo "<br>#####comed= ".$lmpValue." and ratio of".$lmpValue." /".$temp2." = ".$ratioFloatVal;
		$forecastedPriceFloatVal = $forecastedPriceData[$index1-1][2];
		
		$point = $temp2;
		#echo "<br>For lmpValue:".$lmpValue." :- product of forecasted Price ".$forecastedPriceFloatVal." and price-to-point ratio: ".$temp2." = ".$point;
		#echo "<br>@@ rounded point value = ".round($point);
		array_push( $calculatedPointValues , round($point));	
		
	}
	return $calculatedPointValues;
	}

#builds insert query for forecasted_point_schedule table	
function getForecastQuery($forecastedPriceData,$comEdValues,$calculatedPointValues){


#echo "#".sizeof($forecastedPriceData)."##".sizeof($comEdValues)."###".sizeof($calculatedPointValues);

	$values = '';
	
	for ($i=0 ; $i<=23 ; $i++){  
		$values = $values." (str_to_date('".$forecastedPriceData[$i][0]."','%Y%m%d'),str_to_date('".$forecastedPriceData[$i][1]."','%H')," . $forecastedPriceData[$i][2] .",".$calculatedPointValues[$i].",".$comEdValues[$i]."), ";
		
	}
	
	$values = rtrim($values, ', ');
 $insertQuery = "INSERT INTO `Meter_Genius_Data`.`Forecasted_Point_Schedule` (`date`, `time`, `forecasted_price`,`forecasted_point_value`,`comed`) VALUES ".$values . ";";

#echo $insertQuery;

return $insertQuery;
}	





# returns pjm feed data for links having csv files from previous days
function getPJMDateLinks(){
	# we will replace with 'metergenius' text in the link with valid date string
	$pjmURL = getPJMURL();
	$datesWithLinks = array();
	$todaysDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

	$numOfLinks = getNumberOfLinksToRetrieve();
	
	for ($linkCount=0; $linkCount<$numOfLinks; $linkCount++) {
	$urlInitString = "metergenius";
	$prev_date = date('Y-m-d', strtotime($todaysDate .' -1 day'));
	$prev_date_str = str_replace("-","",$prev_date);
	$todaysDate = $prev_date ;
	$link = str_replace($urlInitString,$prev_date_str,$pjmURL);
	$urlInitString = $prev_date_str;
	
	$dateLink = array();
	$dateLink = [$prev_date_str, $link];
	
	array_push($datesWithLinks,$dateLink);	
	}
	
	return $datesWithLinks;
}




# get valid url links in array(date and url) format from rss feed xml
function getPreviousRSSFeedLinks(){
$feed = file_get_contents("http://www.pjm.com/pub/account/lmpda/rss.xml");
$xml = simplexml_load_string($feed);
$dateLinksArray = array();
if ($xml === false) {
    echo "Failed loading XML: ";
		foreach(libxml_get_errors() as $error) {
        echo "<br>", $error->message;
		}
	} else {	
	for($i=0; $i<10; $i++){
	#eliminating residual csv data from rss feed here
	$title = $xml->channel->item[$i]->title;
	$link = $xml->channel->item[$i]->link;
		if (strpos($title, "residual")>0) {
		#do nothing
		} else {
		$title = str_replace("Day-Ahead LMP Data: ","",$title);
		$title = str_replace("-da.csv","",$title);
		$dateLinksArray[$title] = $link;
		}
	}	
}
return $dateLinksArray;
}







#returns array of date values in Y-m-d format
/*function getDateValues(){

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$prev_date = date('Y-m-d', strtotime($date .' -1 day'));
$next_date = date('Y-m-d', strtotime($date .' +1 day'));

echo $date;
}*/


?>