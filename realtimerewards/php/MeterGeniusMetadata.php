<?php

#returns customized URL where dateformat is replaced with a string 'metergenius'
#We replace with date string to get each csv file w.r.t date
function getPJMURL(){
	$pjmURL = "http://www.pjm.com//pub/account/lmpda/metergenius-da.csv";
	return $pjmURL;
}

function getNumberOfLinksToRetrieve(){

	$numOfLinks = 5;
    return $numOfLinks;
}

?>