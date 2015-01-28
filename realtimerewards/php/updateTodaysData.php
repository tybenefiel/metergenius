 <?php 

//include 'MeterGeniusDatabaseConnection.php';
include 'scheduleSetup.php';

updateTodaysRewardsData();

# returns pjm feed data for links having csv files from previous days
function getPJMTodaysLink(){
	# we will replace with 'metergenius' text in the link with valid date string
	$pjmURL = getPJMURL();
	$datesWithLink = array();
	$todaysDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

	$numOfLinks = 1;
	
	
	$urlInitString = "metergenius";

	//$prev_date = date('Y-m-d', strtotime($todaysDate .' -1 day'));
	$todays_date_str = str_replace("-","",$todaysDate);
	//$todaysDate = $prev_date ;
	$link = str_replace($urlInitString,$todays_date_str,$pjmURL);
	//$urlInitString = $prev_date_str;
	
	$dateLink = array();
	$dateLink = [$todays_date_str, $link];
	
	array_push($datesWithLink,$dateLink);	
	
	
	return $datesWithLink;
}





function updateTodaysRewardsData(){
	#use below to get the links from rss feed xml : 1
	#$dateLinksArray = getPreviousRSSFeedLinks();
	#use below to get the links from function that forms the links from custom URL :2
	$dateLinkArray = getPJMTodaysLink();	
	$rowsInserted=0;
	//var_dump($dateLinkArray);
	$link = $dateLinkArray[0][1];
	$url = file_get_contents($link);
	$data = str_getcsv($url,"\n");  #data is array of lines
	$forecastedPriceData = getForecastedPriceData($dateLinkArray[0],$data[2],$data[3]);
	//var_dump($forecastedPriceData); 
	#contains 24 date,time and prices forecasted in array
	$comEdValues = getComEdLMPValues($data[15]);
	//var_dump($comEdValues); 
	# contains 24 TotalLMP values of COMED. COMED row is always the 15th row in the csv array
	$calculatedPointValues = getCalculatedPointValues($forecastedPriceData,$comEdValues); 
	//var_dump($calculatedPointValues);
	#contains 24 forecastedPoints,COMED #lmp values



$values = '';
	
	for ($i=0 ; $i<=23 ; $i++){  
	//	var_dump($forecastedPriceData[$i][0][0]);
		$values = $values." (str_to_date('".$forecastedPriceData[$i][0][0]."','%Y%m%d'),str_to_date('".$forecastedPriceData[$i][1]."','%H')," . $forecastedPriceData[$i][2] .",".$calculatedPointValues[$i].",".$comEdValues[$i]."), ";
		
	}
	
	$values = rtrim($values, ', ');
 $insertQuery = "INSERT INTO `Meter_Genius_Data`.`Forecasted_Point_Schedule` (`date`, `time`, `forecasted_price`,`forecasted_point_value`,`comed`) VALUES ".$values . ";";


#echo $insertQuery;




//	$insertForecastQuery = getForecastQuery($forecastedPriceData,$comEdValues,$calculatedPointValues);
	
	$pdo = getMySqlPDO();
	$stmt = $pdo->prepare($insertQuery);
	
	if ($stmt->execute())
		$rowsInserted = $rowsInserted + $stmt->rowCount();
	else 
		echo "Insert forecasted table Fail";
	 	
    
	echo "forecast table inserted todays data with ".$rowsInserted." rows";


}



 ?>