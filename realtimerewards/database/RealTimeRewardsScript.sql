
CREATE DATABASE IF NOT EXISTS `Meter_Genius_Data`;
use `Meter_Genius_Data`;
CREATE TABLE IF NOT EXISTS `Forecasted_Point_Schedule` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `forecasted_price` double NOT NULL,
  `forecasted_point_value` double NOT NULL,
  `comed` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date` (`date`,`time`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `Price_To_Point_Ratio` (
  `id` int(10) NOT NULL ,
  `Price_Per_Unit` double NOT NULL,
  `Point_Value` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

