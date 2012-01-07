#!/usr/bin/php
<?
	$time_start = array_sum(explode(' ', microtime()));

include ('/var/www/jpgraph/jpgraph.php'); 
include ('/var/www/jpgraph/jpgraph_line.php'); 
include ('/var/www/jpgraph/jpgraph_scatter.php'); 
include ('/var/www/weather/weatherdesc.php'); 

$dbHost="ENTERHOSTNAMEHERE";
$dbUser="ENTERUSERNAMEHERE";
$dbPass="ENTERPASSWORDHERE";

function prettydate ($my_unpretty_date) {
	@list($my_date, $my_time) = split (" ", $my_unpretty_date);
	@list($my_year, $my_month, $my_day) = split ("-", $my_date);
	@list($my_hour, $my_minute, $my_second) = split (":", $my_time);
	
	if ($my_day == date("d")) {
		$theDay = "Today";
	} else {
		$theDay = "Yesterday";
	}

	if ($my_hour > 12) {
		$theHour = $my_hour - 12;
		$theAMPM = "PM";
	} else {
		$theHour = $my_hour;
		$theAMPM = "AM";
	}
	
	if ($my_hour = 0) {
		$theHour = 12;
		$theAMPM = "AM";
	}

	$pretty_date = "$theDay @ $theHour:$my_minute $theAMPM";
	
	return ($pretty_date);
}

// OK!  This is going to be fizzle.

	$link = mysql_connect($dbHost, $dbUser, $dbPass)
	   or die('Could not connect: ' . mysql_error());
	mysql_select_db('weather') or die('Could not select database');

// Getting current conditions
	$query = 'SELECT * FROM currentobs ORDER BY weather_stamp DESC LIMIT 1';
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	$row = mysql_fetch_object($result);
	
// ****************** BEGIN EXTREME SELECT/ASSIGNMENTS ****************************/

// FIRST SELECT YESTERDAY'S INFO
	$query = 'SELECT MAX(out_temp) as high_temp, MIN(out_temp) as low_temp, MAX(wind_speed) as high_windgust, MAX(con_rate) as high_rain, MAX(avg_wind_speed) as high_avgwind, MIN(avg_wind_speed) as low_avgwind, MAX(barometer) as high_barometer, MIN(barometer) as low_barometer, MAX(dewpoint) as high_dewpoint, MIN(dewpoint) as low_dewpoint, MAX(out_hum) as high_hum, MIN(out_hum) as low_hum, MAX(in_temp) as high_intemp, MIN(in_temp) as low_intemp FROM currentobs WHERE weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE();';
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	$yesterday_extreme_row = mysql_fetch_object($result);

// Then select TODAY's info.
	$query = 'SELECT MAX(out_temp) as high_temp, MIN(out_temp) as low_temp, MAX(wind_speed) as high_windgust, MAX(con_rate) as high_rain, MAX(avg_wind_speed) as high_avgwind, MIN(avg_wind_speed) as low_avgwind, MAX(barometer) as high_barometer, MIN(barometer) as low_barometer, MAX(dewpoint) as high_dewpoint, MIN(dewpoint) as low_dewpoint, MAX(out_hum) as high_hum, MIN(out_hum) as low_hum, MAX(in_temp) as high_intemp, MIN(in_temp) as low_intemp FROM currentobs WHERE weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY';
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	$today_extreme_row = mysql_fetch_object($result);

// Fixed the ghettoness, thank jeebus for subselects.  Well, still pretty 
// ghetto.  Like to select stamp next to it, but correlated subqueries 
// can be hairy with aggregates.

	$query = "SELECT (SELECT weather_stamp FROM currentobs WHERE out_temp = '" . $yesterday_extreme_row->high_temp . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_high_temp_stamp, (SELECT weather_stamp FROM currentobs WHERE out_temp = '" . $yesterday_extreme_row->low_temp . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_low_temp_stamp, (SELECT weather_stamp FROM currentobs WHERE wind_speed = '" . $yesterday_extreme_row->high_windgust . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_high_windgust_stamp, (SELECT weather_stamp FROM currentobs WHERE con_rate = '" . $yesterday_extreme_row->high_rain . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_high_rainrate_stamp, (SELECT weather_stamp FROM currentobs WHERE avg_wind_speed = '" . $yesterday_extreme_row->high_avgwind . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_high_avgwind_stamp, (SELECT weather_stamp FROM currentobs WHERE avg_wind_speed = '" . $yesterday_extreme_row->low_avgwind . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_low_avgwind_stamp, (SELECT weather_stamp FROM currentobs WHERE barometer = '" . $yesterday_extreme_row->high_barometer . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_high_barometer_stamp, (SELECT weather_stamp FROM currentobs WHERE barometer = '" . $yesterday_extreme_row->low_barometer . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_low_barometer_stamp, (SELECT weather_stamp FROM currentobs WHERE dewpoint = '" . $yesterday_extreme_row->high_dewpoint . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_high_dewpoint_stamp, (SELECT weather_stamp FROM currentobs WHERE dewpoint = '" . $yesterday_extreme_row->low_dewpoint . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_low_dewpoint_stamp, (SELECT weather_stamp FROM currentobs WHERE out_hum = '" . $yesterday_extreme_row->high_hum . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_high_hum_stamp, (SELECT weather_stamp FROM currentobs WHERE out_hum = '" . $yesterday_extreme_row->low_hum . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_low_hum_stamp, (SELECT weather_stamp FROM currentobs WHERE in_temp = '" . $yesterday_extreme_row->high_intemp . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_high_intemp_stamp, (SELECT weather_stamp FROM currentobs WHERE in_temp = '" . $yesterday_extreme_row->low_intemp . "' AND weather_stamp > CURDATE() - INTERVAL 1 DAY AND weather_stamp < CURDATE() ORDER BY weather_stamp DESC LIMIT 1) as yesterday_low_intemp_stamp;";
	$extreme_result = mysql_query($query) or die('Query failed: ' . mysql_error());
	$yesterday_high_temp_stamp = mysql_result($extreme_result, 0, "yesterday_high_temp_stamp");
	$yesterday_low_temp_stamp = mysql_result($extreme_result, 0, "yesterday_low_temp_stamp");
	$yesterday_high_windgust_stamp = mysql_result($extreme_result, 0, "yesterday_high_windgust_stamp");
	$yesterday_high_rainrate_stamp = mysql_result($extreme_result, 0, "yesterday_high_rainrate_stamp");
	$yesterday_high_avgwind_stamp = mysql_result($extreme_result, 0, "yesterday_high_avgwind_stamp");
	$yesterday_low_avgwind_stamp = mysql_result($extreme_result, 0, "yesterday_low_avgwind_stamp");
	$yesterday_high_barometer_stamp = mysql_result($extreme_result, 0, "yesterday_high_barometer_stamp");
	$yesterday_low_barometer_stamp = mysql_result($extreme_result, 0, "yesterday_low_barometer_stamp");
	$yesterday_high_dewpoint_stamp = mysql_result($extreme_result, 0, "yesterday_high_dewpoint_stamp");
	$yesterday_low_dewpoint_stamp = mysql_result($extreme_result, 0, "yesterday_low_dewpoint_stamp");
	$yesterday_high_hum_stamp = mysql_result($extreme_result, 0, "yesterday_high_hum_stamp");
	$yesterday_low_hum_stamp = mysql_result($extreme_result, 0, "yesterday_low_hum_stamp");
	$yesterday_high_intemp_stamp = mysql_result($extreme_result, 0, "yesterday_high_intemp_stamp");
	$yesterday_low_intemp_stamp = mysql_result($extreme_result, 0, "yesterday_low_intemp_stamp");

	$query = "SELECT (SELECT weather_stamp FROM currentobs WHERE out_temp = '" . $today_extreme_row->high_temp . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_high_temp_stamp, (SELECT weather_stamp FROM currentobs WHERE out_temp = '" . $today_extreme_row->low_temp . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_low_temp_stamp, (SELECT weather_stamp FROM currentobs WHERE wind_speed = '" . $today_extreme_row->high_windgust . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_high_windgust_stamp,  (SELECT weather_stamp FROM currentobs WHERE con_rate = '" . $today_extreme_row->high_rain . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_high_rainrate_stamp, (SELECT weather_stamp FROM currentobs WHERE avg_wind_speed = '" . $today_extreme_row->high_avgwind . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_high_avgwind_stamp, (SELECT weather_stamp FROM currentobs WHERE avg_wind_speed = '" . $today_extreme_row->low_avgwind . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_low_avgwind_stamp, (SELECT weather_stamp FROM currentobs WHERE barometer = '" . $today_extreme_row->high_barometer . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_high_barometer_stamp, (SELECT weather_stamp FROM currentobs WHERE barometer = '" . $today_extreme_row->low_barometer . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_low_barometer_stamp, (SELECT weather_stamp FROM currentobs WHERE dewpoint = '" . $today_extreme_row->high_dewpoint . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_high_dewpoint_stamp, (SELECT weather_stamp FROM currentobs WHERE dewpoint = '" . $today_extreme_row->low_dewpoint . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_low_dewpoint_stamp, (SELECT weather_stamp FROM currentobs WHERE out_hum = '" . $today_extreme_row->high_hum . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_high_hum_stamp, (SELECT weather_stamp FROM currentobs WHERE out_hum = '" . $today_extreme_row->low_hum . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_low_hum_stamp, (SELECT weather_stamp FROM currentobs WHERE in_temp = '" . $today_extreme_row->high_intemp . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_high_intemp_stamp, (SELECT weather_stamp FROM currentobs WHERE in_temp = '" . $today_extreme_row->low_intemp . "' AND weather_stamp > CURDATE() AND weather_stamp < CURDATE() + INTERVAL 1 DAY ORDER BY weather_stamp DESC LIMIT 1) as today_low_intemp_stamp;";
	$extreme_result = mysql_query($query) or die('Query failed: ' . mysql_error());
	$today_high_temp_stamp = mysql_result($extreme_result, 0, "today_high_temp_stamp");
	$today_low_temp_stamp = mysql_result($extreme_result, 0, "today_low_temp_stamp");
	$today_high_windgust_stamp = mysql_result($extreme_result, 0, "today_high_windgust_stamp");
	$today_high_rainrate_stamp = mysql_result($extreme_result, 0, "today_high_rainrate_stamp");
	$today_high_avgwind_stamp = mysql_result($extreme_result, 0, "today_high_avgwind_stamp");
	$today_low_avgwind_stamp = mysql_result($extreme_result, 0, "today_low_avgwind_stamp");
	$today_high_barometer_stamp = mysql_result($extreme_result, 0, "today_high_barometer_stamp");
	$today_low_barometer_stamp = mysql_result($extreme_result, 0, "today_low_barometer_stamp");
	$today_high_dewpoint_stamp = mysql_result($extreme_result, 0, "today_high_dewpoint_stamp");
	$today_low_dewpoint_stamp = mysql_result($extreme_result, 0, "today_low_dewpoint_stamp");
	$today_high_hum_stamp = mysql_result($extreme_result, 0, "today_high_hum_stamp");
	$today_low_hum_stamp = mysql_result($extreme_result, 0, "today_low_hum_stamp");
	$today_high_intemp_stamp = mysql_result($extreme_result, 0, "today_high_intemp_stamp");
	$today_low_intemp_stamp = mysql_result($extreme_result, 0, "today_low_intemp_stamp");



// ****************** END EXTREME SELECT/ASSIGNMENTS ****************************/

// THis is a fancy way of converting degrees to a direction.
	  if($row->comp_dir < 12) {
	  	$compdir = "N";
	  }	elseif ($row->comp_dir > 11 && $row->comp_dir < 35) {
	  	$compdir = "NNE";
	  } elseif($row->comp_dir > 34 && $row->comp_dir < 57) {
	  	$compdir = "NE";
	  } elseif($row->comp_dir > 56 && $row->comp_dir < 79) {
	  	$compdir = "ENE";
	  } elseif($row->comp_dir > 78 && $row->comp_dir < 102) {
	  	$compdir = "E";
	  } elseif($row->comp_dir > 101 && $row->comp_dir < 124) {
	  	$compdir = "ESE";
	  } elseif($row->comp_dir > 123 && $row->comp_dir < 147) {
	  	$compdir = "SE";
	  } elseif($row->comp_dir > 146 && $row->comp_dir < 169) {
	  	$compdir = "SSE";
	  } elseif($row->comp_dir > 168 && $row->comp_dir < 192) {
	  	$compdir = "S";
	  } elseif($row->comp_dir > 191 && $row->comp_dir < 214) {
	  	$compdir = "SSW";
	  } elseif($row->comp_dir > 213 && $row->comp_dir < 237) {
	  	$compdir = "SW";
	  } elseif($row->comp_dir > 236 && $row->comp_dir < 259) {
	  	$compdir = "WSW";
	  } elseif($row->comp_dir > 258 && $row->comp_dir < 282) {
	  	$compdir = "W";
	  } elseif($row->comp_dir > 281 && $row->comp_dir < 304) {
	  	$compdir = "WNW";
	  } elseif($row->comp_dir > 303 && $row->comp_dir < 327) {
	  	$compdir = "NW";
	  } elseif($row->comp_dir > 326 && $row->comp_dir < 349) {
	  	$compdir = "NNW";
	  } elseif($row->comp_dir > 348) {
	  	$compdir = "N";
	  }

	if ($row->out_temp > 80) {
		$calc_heat_index = round(-42.379+2.04901523*$row->out_temp+10.14333127*$row->out_hum-0.22475541*$row->out_temp*$row->out_hum - 6.83783*pow(10, -3) * pow($row->out_temp, 2) - 5.481717*pow(10, -2)*pow($row->out_hum, 2)+1.22874*pow(10, -3) * pow($row->out_temp, 2) * $row->out_hum+8.5282* pow(10, -4)* $row->out_temp * pow($row->out_hum, 2) - 1.99*pow(10, -6) * pow($row->out_temp, 2) * pow($row->out_hum,2), 1);
 
		if ($calc_heat_index > $row->out_temp) {
			$calc_heat_index_text = "($calc_heat_index&deg;F Heat Index)"; 
		} else {
			$calc_heat_index_text = "";
		}
	} else {
		$calc_heat_index = "";
		$calc_heat_index_text = "";
	}

	if ($row->out_temp < 60) {
		#$calc_wind_chill = round(0.0817*(3.71*sqrt($row->avg_wind_speed)+5.81 - 0.25 * $row->avg_wind_speed)*($row->out_temp-91.4) + 91.4, 1);
		$calc_wind_chill = round(35.74 + (0.6215*$row->out_temp) - (35.75 * pow($row->avg_wind_speed, 0.16)) + (0.4275 * $row->out_temp * pow($row->avg_wind_speed, 0.16)), 1);

		if ($calc_wind_chill < $row->out_temp) {
			$calc_wind_chill_text = "($calc_wind_chill&deg;F Wind Chill)"; 
		} else {
			$calc_wind_chill_text = "";
		}
	} else {
		$calc_wind_chill = "";
		$calc_wind_chill_text = "";
	}

	$output = "<html><head><meta http-equiv=\"refresh\" content=\"60\"><title>Brando's Weather Station</title><link rel=\"shortcut icon\" href=\"/favicon.ico\" /><link rel=\"stylesheet\" media=\"all\" type=\"text/css\" href=\"picpopup.css\" /></head><body bgcolor=#ffffff>\n";
	$output .= "<center><h1>Weather Conditions at the Williams House</h1></center>\n";
	$output .= "<center><table border=0><tr><td>\n";
	$output .= "<p><b>Last Updated:</b> ". date("d M Y H:i:s T", time()) . "<br>\n";
	$output .= "To see 24-hour high & low times, move your mouse over the value you want details on.<br><!-- Wind Chill: $calc_wind_chill-->\n";
		
// Compiling current conditions:
	$output .= "<table border=0 cellpadding=0 cellspacing=0 bgcolor=#000000>\n";
	$output .= "<tr>\n";
	$output .= "<th bgcolor=#000000 width=1></th>\n";
	$output .= "<th bgcolor=#000099 width=160><font color=#ffffff>Statistic</font></th>\n";
	$output .= "<th bgcolor=#000000 width=1></th>\n";
	$output .= "<th bgcolor=#000099 width=213><font color=#ffffff>Current Values</font></th>\n";
	$output .= "<th bgcolor=#000000 width=1></th>\n";
	$output .= "<th bgcolor=#000099 width=200><font color=#ffffff>&nbsp;&nbsp;Today's High/Low&nbsp;&nbsp;</font></th>\n";
	$output .= "<th bgcolor=#000000 width=1></th>\n";
	$output .= "<th bgcolor=#000099 width=200><font color=#ffffff>&nbsp;&nbsp;Yesterday's High/Low&nbsp;&nbsp;</font></th>\n";
	$output .= "<th bgcolor=#000000 width=1></th>\n";
	$output .= "</tr>\n";
	$output .= "<tr><td height=1 colspan=9></td></tr>\n";

		$output .= "<tr><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;Temperature:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "<div id=\"pic\"><a class=\"p1\" href=\"#\"><font color=\"#000000\">&nbsp;$row->out_temp&deg;F $calc_heat_index_text $calc_wind_chill_text</font><img src=\"temperature_2hr.png\" alt=\"2hr Popup\" width=250 height=190 class=\"large\"></a></div>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_high_temp_stamp) . "\">$today_extreme_row->high_temp&deg;F</acronym> / \n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_low_temp_stamp) . "\">$today_extreme_row->low_temp&deg;F</acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_high_temp_stamp) . "\">$yesterday_extreme_row->high_temp&deg;F</acronym> / \n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_low_temp_stamp) . "\">$yesterday_extreme_row->low_temp&deg;F</acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td>\n";
		$output .= "</td></tr>\n";
		$output .= "<tr><td height=1 colspan=9></td></tr>\n";
		$output .= "<tr><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;Dewpoint:\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;$row->dewpoint&deg;F \n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_high_dewpoint_stamp) . "\">$today_extreme_row->high_dewpoint&deg;F </acronym> / \n";
	$output.= "&nbsp;<acronym title=\"" . prettydate($today_low_dewpoint_stamp) . "\">$today_extreme_row->low_dewpoint&deg;F </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_high_dewpoint_stamp) . "\">$yesterday_extreme_row->high_dewpoint&deg;F </acronym> / \n";
	$output.= "&nbsp;<acronym title=\"" . prettydate($yesterday_low_dewpoint_stamp) . "\">$yesterday_extreme_row->low_dewpoint&deg;F </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td>\n";
		$output .= "</td></tr>\n";
		$output .= "<tr><td height=1 colspan=9></td></tr>\n";
		$output .= "<tr><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;Humidity:\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "<div id=\"pic\"><a class=\"p1\" href=\"#\"><font color=\"#000000\">&nbsp;$row->out_hum%</font><img src=\"humidity_2hr.png\" alt=\"2hr Popup\" width=250 height=190 class=\"large\"></a></div>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_high_hum_stamp) . "\">$today_extreme_row->high_hum% </acronym> / \n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_low_hum_stamp) . "\">$today_extreme_row->low_hum% </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_high_hum_stamp) . "\">$yesterday_extreme_row->high_hum% </acronym> / \n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_low_hum_stamp) . "\">$yesterday_extreme_row->low_hum% </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td>\n";
		$output .= "</td></tr>\n";
		$output .= "<tr><td height=1 colspan=9></td></tr>\n";
		$output .= "<tr><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;Wind Speed:\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;$row->avg_wind_speed MPH from the $compdir&nbsp;&nbsp;&nbsp;\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_high_avgwind_stamp) . "\">$today_extreme_row->high_avgwind MPH </acronym> / \n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_low_avgwind_stamp) . "\">$today_extreme_row->low_avgwind MPH </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_high_avgwind_stamp) . "\">$yesterday_extreme_row->high_avgwind MPH </acronym> / \n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_low_avgwind_stamp) . "\">$yesterday_extreme_row->low_avgwind MPH </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td>\n";
		$output .= "</td></tr>\n";
		$output .= "<tr><td height=1 colspan=9></td></tr>\n";
		$output .= "<tr><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;Wind Gust <font size=-2>(High only)</font>:\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
		$output .= "&nbsp;$row->wind_speed MPH\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_high_windgust_stamp) . "\">$today_extreme_row->high_windgust MPH </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_high_windgust_stamp) . "\">$yesterday_extreme_row->high_windgust MPH </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td>\n";
		$output .= "</td></tr>\n";
		$output .= "<tr><td height=1 colspan=9></td></tr>\n";
		$output .= "<tr><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;Barometric Pressure:\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "<div id=\"pic2\"><a class=\"p1\" href=\"#\"><font color=\"#000000\">&nbsp;$row->barometer\" and " . $weatherArray["$row->trend"] . "&nbsp;&nbsp;</font><img src=\"barometer_2hr.png\" alt=\"2hr Popup\" width=250 height=190 class=\"large\"></a></div>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_high_barometer_stamp) . "\">$today_extreme_row->high_barometer\" </acronym> / \n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_low_barometer_stamp) . "\">$today_extreme_row->low_barometer\" </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_high_barometer_stamp) . "\">$yesterday_extreme_row->high_barometer\" </acronym> / \n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_low_barometer_stamp) . "\">$yesterday_extreme_row->low_barometer\" </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td>\n";
		$output .= "</td></tr>\n";
		$output .= "<tr><td height=1 colspan=9></td></tr>\n";
		$output .= "<tr><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;Inside Temperature:\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;$row->in_temp&deg;F\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_high_intemp_stamp) . "\">$today_extreme_row->high_intemp&deg;F </acronym> / \n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_low_intemp_stamp) . "\">$today_extreme_row->low_intemp&deg;F </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_high_intemp_stamp) . "\">$yesterday_extreme_row->high_intemp&deg;F </acronym> / \n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_low_intemp_stamp) . "\">$yesterday_extreme_row->low_intemp&deg;F </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td>\n";
		$output .= "</td></tr>\n";
		$output .= "<tr><td height=1 colspan=9></td></tr>\n";
		$output .= "<tr><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;Rainfall <font size=-2>(High only)</font>:\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF>\n";
	$output .= "&nbsp;$row->con_rate in/hr\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($today_high_rainrate_stamp) . "\">$today_extreme_row->high_rain in/hr </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td bgcolor=#FFFFFF align=middle>\n";
	$output .= "&nbsp;<acronym title=\"" . prettydate($yesterday_high_rainrate_stamp) . "\">$yesterday_extreme_row->high_rain in/hr </acronym>\n";
		$output .= "</td><td bgcolor=#000000 width=1></td><td>\n";
		$output .= "</td></tr>\n";
		$output .= "<tr><td height=1 colspan=9></td></tr>\n";
		$output .= "</table>\n";
	$output .= "<br>\n";


		$output .= "<br><b>Rain Today:</b> " . $row->day_rain . " in.<br>\n";
		$output .= "<b>Rain This Month:</b> " . $row->mon_rain . " in.<br>\n";
		$output .= "<b>Rain This Year:</b> " . $row->year_rain . " in.<br>\n";
		$output .= "<b>Current Forecast:</b> " . $forecastArray[$row->forecast_rule];
		$output .= "</td></tr></table></center>\n";

		$output .= "<center><h3>Current Radar (Base Reflectivity)</h3><a href=\"http://www.srh.noaa.gov/ridge/radar.php?rid=FWS&product=NCR&overlay=11101111&loop=yes\"><img src=\"http://www.findu.com/cgi-bin/radar-find.cgi?call=N5BX\" border=0></a><br><font size=\"-2\">(Click on radar to see it in motion)</font>\n";

		$output .= "<br><br><h3><a href=\"http://forecast.weather.gov/MapClick.php?lat=33.02853639193054&lon=-97.06352233886719&site=fwd&smap=1&marine=0&unit=0&lg=en\">National Weather Service Forecast</a></h3><? include ('forecast.html');?>\n";
		$output .= "<br><br><img src=temperature.png border=0>\n";
		$output .= "<br><br><img src=humidity.png border=0>\n";
		$output .= "<br><br><img src=barometer.png border=0>\n";
		$output .= "<br><br><img src=rainrate.png border=0>\n";
		$output .= "<br><br><img src=windgraph.png border=0>\n";
		$output .= "<br><br><img src=winddirgraph.png border=0></center><br><br>\n";
	
// Free resultset
	mysql_free_result($result);

// Now time to create some graphs:
	$query = 'SELECT DATE_FORMAT(weather_stamp,"%I:%i %p") as weather_time, avg_wind_speed, wind_speed, comp_dir, out_temp, dewpoint, barometer, out_hum, con_rate FROM currentobs WHERE weather_stamp < NOW() and weather_stamp > SUBDATE(NOW(), INTERVAL 12 HOUR) ORDER BY weather_stamp;';
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());

	$timecount=0;
//build graph data.
	while ($row = mysql_fetch_object($result)) {
		$avg_spd[] = $row->avg_wind_speed;
		$gust[] = $row->wind_speed;
		
		$timecount++;

		$time[] = $row->weather_time;
		$comp_dir[] = $row->comp_dir;
		$out_temp[] = $row->out_temp;
		$dewpoint[] = $row->dewpoint;
		$barometer[] = $row->barometer;
		$humidity[] = $row->out_hum;
		$rain_rate[] = $row->con_rate;
	}
	mysql_free_result($result);

// test...
	$query = 'SELECT DATE_FORMAT(weather_stamp,"%I:%i %p") as weather_time, out_temp,out_hum, barometer FROM currentobs WHERE weather_stamp < SUBDATE(NOW(), INTERVAL 24 HOUR) AND weather_stamp > SUBDATE(NOW(), INTERVAL 36 HOUR) ORDER BY weather_stamp;';
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());

//build secondary graph data.
	$second_row_count = 0;
	while ($row = mysql_fetch_object($result)) {
		$yest_time[] = $row->weather_time;
		$yest_out_temp[] = $row->out_temp;
		$yest_out_hum[] = $row->out_hum;
		$yest_barometer[] = $row->barometer;
		$second_row_count++;
	}

	mysql_free_result($result);
// Closing connection
	mysql_close($link);

// Now we need to get our slices for the last 2 hours for certain arrays.
	$time_2hr = array_slice($time, -120);
	$out_temp_2hr = array_slice($out_temp, -120);
	$humidity_2hr = array_slice($humidity, -120);
	$barometer_2hr = array_slice($barometer, -120);

	
// Here is wind speed and gusts over the past 12 hours:
	$gust_graph = new Graph(850, 190);     
	$gust_graph->SetScale("linlin",0,0,0,min(719,$timecount-1)); 
	$gust_graph->legend->Pos(.005,0.5,"right","center");
	$gust_graph->legend->SetFont(FF_FONT1,FS_BOLD);
	$gust_graph->SetClipping();
	
// Setup margin and titles 
	$gust_graph->img->SetMargin(40,120,20,60); 
	$gust_graph->title->Set("Wind Speed over the past 12 hours"); 
	$gust_graph->yaxis->title->Set("Speed in MPH"); 
	$gust_graph->xaxis->SetTickLabels($time);
	$gust_graph->ygrid->Show("true","true");
	$gust_graph->xgrid->Show("true","true");
	$gust_graph->ygrid->SetLineStyle("solid");
	$gust_graph->ygrid->SetWeight(2);
	$gust_graph->xaxis->SetLabelAngle(90);
	$gust_graph->title->SetFont(FF_FONT1,FS_BOLD); 
	$gust_graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
	$gust_graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

	$avgspdPlot = new LinePlot($avg_spd);
	$gustPlot = new ScatterPlot($gust);
	$gustPlot->mark->SetColor("red"); 
	$gustPlot->mark->SetFillColor("red"); 
	$gustPlot->mark->SetType(MARK_FILLEDCIRCLE);
	$gustPlot->mark->SetSize(2);

	$avgspdPlot->SetFillColor("green");
	$avgspdPlot->SetLegend("Wind Speed");
	$gustPlot->SetLegend("Wind Gust");

	$gust_graph->Add($avgspdPlot);
	$gust_graph->Add($gustPlot);
	$gust_graph->Stroke("windgraph.png");

// Here is wind direction over the past 12 hours:
	$wind_graph = new Graph(850, 190);     
	$wind_graph->SetScale("linlin",0,360,0,min(719,$timecount-1)); 
	$wind_graph->legend->Pos(.005,0.5,"right","center");
	$wind_graph->legend->SetFont(FF_FONT1,FS_BOLD);
	$wind_graph->SetClipping();

// Setup margin and titles 
	$wind_graph->img->SetMargin(40,120,20,60); 
	$wind_graph->title->Set("Wind Direction over the past 12 hours"); 
	$wind_graph->yaxis->title->Set("Degrees"); 
	$wind_graph->yaxis->scale->ticks->Set(90,45);
	$wind_graph->xaxis->SetTickLabels($time);
	$wind_graph->ygrid->Show("true","true");
	$wind_graph->xgrid->Show("true","true");
	$wind_graph->ygrid->SetLineStyle("solid");
	$wind_graph->ygrid->SetWeight(2);
	$wind_graph->xaxis->SetLabelAngle(90);
	$wind_graph->title->SetFont(FF_FONT1,FS_BOLD); 
	$wind_graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
	$wind_graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 


	
	$dirPlot = new ScatterPlot($comp_dir);

	$dirPlot->mark->SetColor("darkgreen"); 
	$dirPlot->mark->SetType(MARK_FILLEDCIRCLE);
	$dirPlot->mark->SetSize(2);
	$dirPlot->SetLegend("Wind Dir");

	$wind_graph->Add($dirPlot);
	$wind_graph->Stroke("winddirgraph.png");


// Here is temperature/dewpoint over the past 12 hours:
	$temp_graph = new Graph(850, 190);     
	$temp_graph->SetScale("linlin",0,0,0,min(719,$timecount-1)); 
	$temp_graph->legend->Pos(.005,0.5,"right","center");
	$temp_graph->legend->SetFont(FF_FONT1,FS_BOLD);
	$temp_graph->legend->SetReverse();
	$temp_graph->SetClipping();
	
	$temp_graph->SetAxisStyle(AXSTYLE_BOXOUT); 


// Setup margin and titles 
	$temp_graph->img->SetMargin(40,120,20,60); 
	$temp_graph->title->Set("Temperature and Dewpoint over the past 12 hours"); 
	$temp_graph->yaxis->title->Set("Degrees Farenheit"); 
	$temp_graph->xaxis->SetTickLabels($time);
	$temp_graph->ygrid->Show("true","true");
	$temp_graph->xgrid->Show("true","true");
	$temp_graph->ygrid->SetLineStyle("solid");
	$temp_graph->ygrid->SetWeight(2);
	$temp_graph->xaxis->SetLabelAngle(90);
	$temp_graph->title->SetFont(FF_FONT1,FS_BOLD); 
	$temp_graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
	$temp_graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

	$tempPlot = new LinePlot($out_temp);
	$tempPlot->SetColor("red:0.9");
	$tempPlot->SetLegend("Temp");
	$dptPlot = new LinePlot($dewpoint);
	$dptPlot->SetColor("darkgreen");
	$dptPlot->SetLegend("Dewpoint");
	
	if ($second_row_count > 0) {
		$yestPlot = new LinePlot($yest_out_temp);
		$yestPlot->SetColor("darksalmon");
		$yestPlot->SetLegend("YestTemp");
		
		$temp_graph->Add($yestPlot);
	}

	$temp_graph->Add($dptPlot);
	$temp_graph->Add($tempPlot);
	$temp_graph->Stroke("temperature.png");


// Here is temperature over the past 2 hours:
	$temp2hr_graph = new Graph(250, 190);     
	$temp2hr_graph->SetScale("linlin",0,0,0,min(719,120)); 
	$temp2hr_graph->legend->Pos(0.02,0.5,"right","center");
	$temp2hr_graph->legend->SetFont(FF_FONT1,FS_BOLD);
	$temp2hr_graph->legend->SetReverse();

// Setup margin and titles 
	$temp2hr_graph->img->SetMargin(50,80,20,60); 
	$temp2hr_graph->title->Set("Temperature over the past 2 hours"); 
	$temp2hr_graph->yaxis->title->Set("Deg Farenheit"); 
	$temp2hr_graph->yaxis->SetTitleMargin(30);
	$temp2hr_graph->xaxis->SetTickLabels($time_2hr);
	$temp2hr_graph->ygrid->Show("true","true");
	$temp2hr_graph->xgrid->Show("true","true");
	$temp2hr_graph->ygrid->SetLineStyle("solid");
	$temp2hr_graph->ygrid->SetWeight(2);
	$temp2hr_graph->xaxis->SetLabelAngle(90);
	$temp2hr_graph->title->SetFont(FF_FONT1,FS_BOLD); 
	$temp2hr_graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
	$temp2hr_graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

	$temp2hrPlot = new LinePlot($out_temp_2hr);
	$temp2hrPlot->SetColor("red:0.9");
	$temp2hrPlot->SetLegend("Temp");
	
	$temp2hr_graph->Add($temp2hrPlot);
	$temp2hr_graph->Stroke("temperature_2hr.png");

	
// Here is rain rate over the past 12 hours:
	$rain_graph = new Graph(850, 190);     
	$rain_graph->SetScale("linlin",0,0,0,min(719,$timecount-1)); 
	$rain_graph->legend->Pos(.005,0.5,"right","center");
	$rain_graph->legend->SetFont(FF_FONT1,FS_BOLD);
	$rain_graph->SetClipping();

// Setup margin and titles 
	$rain_graph->img->SetMargin(40,120,20,60); 
	$rain_graph->title->Set("Rain rate over the past 12 hours"); 
	$rain_graph->yaxis->title->Set("inches per hour"); 
	$rain_graph->xaxis->SetTickLabels($time);
	$rain_graph->ygrid->Show("true","true");
	$rain_graph->xgrid->Show("true","true");
	$rain_graph->ygrid->SetLineStyle("solid");
	$rain_graph->ygrid->SetWeight(2);
	$rain_graph->xaxis->SetLabelAngle(90);
	$rain_graph->title->SetFont(FF_FONT1,FS_BOLD); 
	$rain_graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
	$rain_graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

	$rainPlot = new LinePlot($rain_rate);
	$rainPlot->SetColor("darkblue");
	$rainPlot->SetLegend("Rain Rate");
	

	$rain_graph->Add($rainPlot);
	$rain_graph->Stroke("rainrate.png");
	

// Here is humidity over the past 12 hours:
	$humidity_graph = new Graph(850, 190);     
	$humidity_graph->SetScale("linlin",0,0,0,min(719,$timecount-1)); 
	$humidity_graph->legend->Pos(.005,0.5,"right","center");
	$humidity_graph->legend->SetFont(FF_FONT1,FS_BOLD);
	$humidity_graph->legend->SetReverse();
	$humidity_graph->SetClipping();

// Setup margin and titles 
	$humidity_graph->img->SetMargin(40,120,20,60); 
	$humidity_graph->title->Set("Humidity over the past 12 hours"); 
	$humidity_graph->yaxis->title->Set("% Humidity"); 
	$humidity_graph->xaxis->SetTickLabels($time);
	$humidity_graph->ygrid->Show("true","true");
	$humidity_graph->xgrid->Show("true","true");
	$humidity_graph->ygrid->SetLineStyle("solid");
	$humidity_graph->ygrid->SetWeight(2);
	$humidity_graph->xaxis->SetLabelAngle(90);
	$humidity_graph->title->SetFont(FF_FONT1,FS_BOLD); 
	$humidity_graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
	$humidity_graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

	$humPlot = new LinePlot($humidity);
	$humPlot->SetColor("darkblue");
	$humPlot->SetLegend("Humidity");
	
	if ($second_row_count > 0) {
		$yesthumPlot = new LinePlot($yest_out_hum);
		$yesthumPlot->SetColor("red:1.6");
		$yesthumPlot->SetLegend("Yesterday");
	
		$humidity_graph->Add($yesthumPlot);
	}
	
	$humidity_graph->Add($humPlot);
	$humidity_graph->Stroke("humidity.png");
	
	
// Here is humidity over the past 2 hours:
	$humidity2hr_graph = new Graph(250, 190);     
	$humidity2hr_graph->SetScale("linlin",0,0,0,min(719,120)); 
	$humidity2hr_graph->legend->Pos(.02,0.5,"right","center");
	$humidity2hr_graph->legend->SetFont(FF_FONT1,FS_BOLD);
	$humidity2hr_graph->legend->SetReverse();

// Setup margin and titles 
	$humidity2hr_graph->img->SetMargin(40,80,20,60); 
	$humidity2hr_graph->title->Set("Humidity over the past 2 hours"); 
	$humidity2hr_graph->yaxis->title->Set("% Humidity"); 
	$humidity2hr_graph->xaxis->SetTickLabels($time_2hr);
	$humidity2hr_graph->ygrid->Show("true","true");
	$humidity2hr_graph->xgrid->Show("true","true");
	$humidity2hr_graph->ygrid->SetLineStyle("solid");
	$humidity2hr_graph->ygrid->SetWeight(2);
	$humidity2hr_graph->xaxis->SetLabelAngle(90);
	$humidity2hr_graph->title->SetFont(FF_FONT1,FS_BOLD); 
	$humidity2hr_graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
	$humidity2hr_graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

	$hum2hrPlot = new LinePlot($humidity_2hr);
	$hum2hrPlot->SetColor("darkblue");
	$hum2hrPlot->SetLegend("Hum");
	

	$humidity2hr_graph->Add($hum2hrPlot);
	$humidity2hr_graph->Stroke("humidity_2hr.png");
	
	
// Here is barometer over the past 12 hours:
	$barometer_graph = new Graph(850, 190);     
	$barometer_graph->SetScale("linlin",0,0,0,min(719,$timecount-1)); 
	$barometer_graph->legend->Pos(.005,0.5,"right","center");
	$barometer_graph->legend->SetFont(FF_FONT1,FS_BOLD);
	$barometer_graph->legend->SetReverse();
	$barometer_graph->SetClipping();

// Setup margin and titles 
	$barometer_graph->img->SetMargin(60,120,20,60); 
	$barometer_graph->title->Set("Barometric Pressure over the past 12 hours"); 
	$barometer_graph->yaxis->title->Set("inches of Hg"); 
	$barometer_graph->yaxis->SetTitleMargin(43); 
	$barometer_graph->xaxis->SetTickLabels($time);
	$barometer_graph->ygrid->Show("true","true");
	$barometer_graph->xgrid->Show("true","true");
	$barometer_graph->ygrid->SetLineStyle("solid");
	$barometer_graph->ygrid->SetWeight(2);
	$barometer_graph->xaxis->SetLabelAngle(90);
	$barometer_graph->title->SetFont(FF_FONT1,FS_BOLD); 
	$barometer_graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
	$barometer_graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

	$barPlot = new LinePlot($barometer);
	$barPlot->SetColor("darkblue");
	$barPlot->SetLegend("in. Hg");
	
	if ($second_row_count > 0) {
		$yestbarPlot = new LinePlot($yest_barometer);
		$yestbarPlot->SetColor("red:1.6");
		$yestbarPlot->SetLegend("Yesterday");

		$barometer_graph->Add($yestbarPlot);
	}

	$barometer_graph->Add($barPlot);
	$barometer_graph->Stroke("barometer.png");


// Here is barometer over the past 2 hours:
	$barometer2hr_graph = new Graph(250, 190);     
	$barometer2hr_graph->SetScale("linlin",0,0,0,min(719,120)); 
	$barometer2hr_graph->legend->Pos(.02,0.5,"right","center");
	$barometer2hr_graph->legend->SetFont(FF_FONT1,FS_BOLD);
	$barometer2hr_graph->legend->SetReverse();
	$barometer_graph->SetY2Scale("lin",0,0); 
	$barometer_graph->SetClipping();

// Setup margin and titles 
	$barometer2hr_graph->img->SetMargin(60,80,20,60); 
	$barometer2hr_graph->title->Set("Barometer over the past 2 hours"); 
	$barometer2hr_graph->yaxis->title->Set("inches of Hg"); 
	$barometer2hr_graph->yaxis->SetTitleMargin(43); 
	$barometer2hr_graph->xaxis->SetTickLabels($time_2hr);
	$barometer2hr_graph->ygrid->Show("true","true");
	$barometer2hr_graph->xgrid->Show("true","true");
	$barometer2hr_graph->ygrid->SetLineStyle("solid");
	$barometer2hr_graph->ygrid->SetWeight(2);
	$barometer2hr_graph->xaxis->SetLabelAngle(90);
	$barometer2hr_graph->title->SetFont(FF_FONT1,FS_BOLD); 
	$barometer2hr_graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
	$barometer2hr_graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

	$bar2hrPlot = new LinePlot($barometer_2hr);
	$bar2hrPlot->SetColor("darkblue");
	$bar2hrPlot->SetLegend("in Hg");

	$barometer2hr_graph->Add($bar2hrPlot);
	$barometer2hr_graph->Stroke("barometer_2hr.png");

  $execute_time = (array_sum(explode(' ', microtime()))) - $time_start;
	$output .= "<center><hr><font size=\"-1\">Created by WXBrando: <a href=\"http://www.brandolabs.com/wxbrando\">http://www.brandolabs.com/wxbrando</a>.<br>Copyright 2005-".date("Y")." Branden R. Williams (N5BX)<br>Time to execute: " . number_format($execute_time, 2) . " seconds.<br></center>\n";
	$output .= "</body></html>\n";


// Lets quickly output our HTML
	$outfile = fopen ("index.php", "w");
	fwrite($outfile, $output);
	fclose($outfile);
?>
