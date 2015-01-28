<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<?php
    include '/php/scheduleGraph.php';
	
	$php_var = getPointScheduleDataForGraph();
	$test_var = json_encode($php_var);
	
	
?> 
    <head>
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<script src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"> </script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>	 	
       <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Meter Genius Points</title>
        <link rel="stylesheet" href="style.css" type="text/css">
        <script src="js/amcharts.js" type="text/javascript"></script>
        <script src="js/serial.js" type="text/javascript"></script>

    
	<style type="text/css">

		/*** The Essential Code ***/

		
	/*	#center {
			padding: 10px 20px;      
			width: 100%;
		}
		*/
		
		
		
		#right img{
			height : 40px;
			width: 50px;
			margin-top:190px;

		}

		#left img{
			height : 40px;
			width: 50px;
			margin-top:190px;
			margin-left:35px;
		}

		#footer {
			clear: both;
		}

		#curtime {
			text-align: center;
			font-weight: 300;
		}

		.header {
			text-align: center;
		}
		
		
		

		


	</style>
	
	
	
    
        <script type="text/javascript">
        	
        	var nowdate = new Date();
        	//var nowtime = nowdate.toLocaleTimeString();
        	//var nowdatestring = nowdate.toDateString();//.toLocaleDateString()
        	//console.log("##"+nowtime);
        	var nowtime ;
			var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        	console.log(months[nowdate.getMonth()],nowdate.getDate(),nowdate.getFullYear(), nowdate.getHours());
        	var hours = nowdate.getHours();
  			var ampm = hours >= 12 ? 'PM' : 'AM';
  			hours = hours % 12;
  			hours = hours ? hours : 12; // the hour '0' should be '12'
  			var mins = nowdate.getMinutes();
  			mins = mins < 10 ? '0'+mins : mins;

  			nowtime = hours+":"+mins+" "+ampm;
  			console.log("###mins :"+mins);
  			var strTime = months[nowdate.getMonth()]+" "+nowdate.getDate()+", "+nowdate.getFullYear()+", "+hours + ' ' +ampm;
  			console.log("### str time :"+strTime);
        	//var nowpoints = ; 
            var chartData = [];
            var chartData2 = [];
			//chart.dataDateFormat = "YYYY-MM-DD, JJ:NN:SS";
            generateChartData();
            //generateChart2Data();


			var onLoadDataIndex;
			var mostrecent = chartData[chartData.length-1]['date'];
			console.log(mostrecent);
			for(var i=chartData.length-1;i>=0;i--){
			 				if  (chartData[i]['date'] == strTime) {
			 					nowpoints = chartData[i]['points'];
			 					onLoadDataIndex = i;
			 					break;
			 				}
			}
			console.log("+++++++++ onLoadDataIndex "+onLoadDataIndex);



            chartData2 = chartData.slice(onLoadDataIndex-5,onLoadDataIndex+3);
            console.log("xxxxxxxx charData2 len "+chartData2.length);
            var chartCursor;

            var initialValue;
            var currentGraphValue;
            var currentGraphIndex;
            





            var chart = AmCharts.makeChart("chartdiv", {
                type: "serial",
				//theme: "dark",
                pathToImages: "images/",
                dataProvider: chartData,
                categoryField: "date",
                categoryAxis: {
				labelFrequency : 2,
                   // parseDates: true,
                    gridAlpha: 0.1,
                    minorGridEnabled: true,
					equalSpacing : true,
					//dateFormats : "YYYYDDMMJJMMSS",
					minHorizontalGap  : 10,
					autoGridCount : false,
					autoWrap : false,
                    axisColor: "#DADADA",
                    //labelsEnabled : false //to be used when labels are overlapping
                },
                valueAxes: [{
                    axisAlpha: 0.8,
                    id: "v1"
                }],
                graphs: [{
                    title: "red line",
                    id: "g1",
                    valueAxis: "v1",
                    valueField: "points",
                    bullet: "round",
                    bulletBorderColor: "#FFFFFF",
                    bulletBorderAlpha: 1,
                    lineThickness: 2,
                    lineColor: "#0352b5",
                    negativeLineColor: "#b5030d",
                    balloonText: "[[category]]<br><b><span style='font-size:14px;'>points: [[value]]</span></b>"
                }],
                //chartCursor: {
                 //   fullWidth:true,
                  //  cursorAlpha:0.1
                //},
              //  chartScrollbar: {
                //    scrollbarHeight: 40,
                  //  color: "#000000",
                   // autoGridCount: false,
					//minHorizontalGap  : 100,
					//graphLineColor : "#00FF00",
				//	graphFillColor : "#FF0000",
                  //  graph: "g1"
               // },

                mouseWheelZoomEnabled:true
            });


			chartCursor = new AmCharts.ChartCursor();
    		chartCursor.cursorAlpha = 0;
    		chartCursor.zoomable = false;
    		chartCursor.categoryBalloonEnabled = false;
    		chartCursor.fullWidth = true;
    		chartCursor.cursorAlpha = 0.1;
    		//chartCursor.enabled = false;

    		chart.addChartCursor(chartCursor);




			chart.titles = [];
			//chart.validateNow();
			//chart.dataDateFormat = "YYYY-MM-DD HH";
            chart.addListener("dataUpdated", zoomChart);


            // generate some random data, quite different range
            function generateChartData() {
				
				var myObj = JSON.parse('<?php echo $test_var; ?>');
				//myObj.reverse();
				for (var i=0; i< myObj.length; i++){
				//document.write("<br>dateTime: "+myObj[i][0]);
				//myObj[i].reverse();
				var dateString = myObj[i][0];//"2010-08-09 01:02:03"
				var reggie = /(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/;
				var dateArray = reggie.exec(dateString); 
				var dateObject = new Date((+dateArray[1]),
    					(+dateArray[2])-1, 
    					(+dateArray[3]),
    					(+dateArray[4]),
    					(+dateArray[5]),
    					(+dateArray[6])
						);
				var options = {
    			year: "numeric", month: "short",
    			day: "numeric", hour: "2-digit"
				};
				var newdate = dateObject.toLocaleDateString("en-US",options);
				console.log(dateObject.toLocaleDateString("en-US",options));
				
				chartData.push({
                        date: newdate,
                        points: myObj[i][1]
						
                    });
				//initialValue = newdate;
				}
			
            }
			

            // this method is called when chart is first inited as we listen for "dataUpdated" event
            function zoomChart() {
			//chart.clearLabels();
                // different zoom methods can be used - zoomToIndexes, zoomToDates, zoomToCategoryValues
                //chart.zoomToIndexes(chartData.length - 10, chartData.length - 1);
               chart.zoomToIndexes(onLoadDataIndex-5, onLoadDataIndex+3);
				//chart.zoomToIndexes(


            }

            // changes cursor mode from pan to select
            function setPanSelect() {
                var chartCursor = chart.chartCursor;

                if (document.getElementById("rb1").checked) {
                    chartCursor.pan = false;
                    chartCursor.zoomable = true;

                } else {
                    chartCursor.pan = true;
                }
                chart.validateNow();
            }
			
			
			
			
			function gotoNextHour(){

				

				if(currentGraphIndex<chartData.length-1){
					if(currentGraphIndex+1<=chartData.length-1){
					currentGraphIndex = currentGraphIndex+1;
					chartCursor.showCursorAt(chartData[currentGraphIndex]['date']);
					}
				}		
			}
			
			
			function gotoPreviousHour(){
				
				if(currentGraphIndex>-1){
					if(currentGraphIndex-1>-1){
					currentGraphIndex = currentGraphIndex-1;
					chartCursor.showCursorAt(chartData[currentGraphIndex]['date']);			
					}
				}

			}

			




			function graphOnLoad(){
				//console.log("### chartdata len "+chartData.length);
				//console.log("### chartdata last obj "+chartData[0]['date']);
				//initialValue = chartData[chartData.length-1]['date'];
				initialValue = strTime;
				//chart.zoomToIndexes
				chartCursor.showCursorAt(initialValue);
				currentGraphValue = initialValue;
				currentGraphIndex = i; //chartData.length-1;
			}
		  
			
        </script>
    </head>

    <body onload="graphOnLoad()">
	
	

	<div class="container">
	<div class="row clearfix">	
    <div class="header"><h3> <span class="label label-default">MeterGenius : Real Time Rewards</span></h3></div>
	<div id="curtime"><h4><div id="time">Time:</div>
	<div id="points">Points: </div></h4>
	</div>
    
	</div>
	<div class="row">
		<div id="left" class="col-md-1">
			<div>
			<a target="" href="#" title="Previous Hour" onClick="gotoPreviousHour(); return false;">
			<img src="html-icons/left.png" alt="">
			</a> 
			</div>	 
		</div>

		<div id="center" class="col-md-10">
	        <div id="chartdiv" style="width: 100%; height: 500px;"></div>		
		</div>

		
		
		<div id="right" class="col-md-1">
			<div>
			<a target="" href="" title="Next Hour" onClick="gotoNextHour(); return false;">
				<img src="html-icons/right.png" alt="">
			</a>
			</div> 
		</div>
	</div>

	<div class = "row clearfix">

	<div id="footer-wrapper">
		<div id="footer"> </div>
	</div>
	</div>
	</div>
<!--        <div id="chartdiv" style="width: 80%; height: 400px;"></div>
-->



        <!--<div style="margin-left:35px;">
            <input type="radio" checked="true" name="group" id="rb1" onclick="setPanSelect()">Select
            <input type="radio" name="group" id="rb2" onclick="setPanSelect()">Pan
		</div>-->
    </body>
<script>
document.getElementById("time").innerHTML = 'Time: ' + nowtime;
document.getElementById("points").innerHTML = 'Points: ' + nowpoints;
</script>

</html>