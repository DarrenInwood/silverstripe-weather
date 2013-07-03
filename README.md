silverstripe-weather
====================

Grabs weather info from Yahoo Weather

## Overview

This plugin for the SilverStripe framework provides you with all sorts of lovely 
weather-related information from Yahoo Weather.

It is easy to set up and use.

'Tick' weather icons included are by ~xiao4 on Deviant Art:
http://xiao4.deviantart.com/art/tick-weather-icons-96294478

## Requirements

SilverStripe 2.4 or newer, untested with 3.x


## Installation

Extract to your project root, then run /dev/build?flush=1 to tell SilverStripe about your new module.

## Usage

The module adds a Weather object to all Content Controllers, allowing you to use various details in templates.

<code>
<% control Weather %>
$Title - $Temperature
<% end_control %>
</code>

The full list of details available is:

$Title - eg. "Conditions for Sunnyvale, CA at 11:55 am PDT"
$Link - Link to the full weather report on Yahoo
$Description - HTML description of the weather
$PublishedDate - When this data was measured. Can use date functions, eg. $PublishedDate.Long
$Latitude - The latitude of the place
$Longitude - The longitude of the place
$City - Name of the city for this weather report, eg. "Sunnyvale"
$Region - Region, eg. "CA"
$Country - Country, eg. "United States"
$Condition - Short description, eg. "Fair"
$Temperature - eg. "22°C"
$TemperatureInt - eg. 22
$Image - an icon representing the weather conditions.  To override these, check WeatherDecorator::$weather_image_map
$WindChill - Temperature including wind chill factor, eg. "18°C"
$WindDirection  - eg. "350°"
$WindSpeed - eg. "12kph"
$Humidity - eg. "88%"
$Visibility - eg. "40km"
$BarometricPressure - eg. "1.2kpa"
$SunriseTime - Date object representing sunrise. Use with normal date formatting functions
$SunsetTime - Date representing sunset time

There is also a list of Forecasts you can access:

<code>
<% control Weather %>
<ul>
<% control Forecasts %>
	<li>$Image {$Date.Long}: $Condition ($TemperatureLow - $TemperatureHigh)
<% end_control %>
</ul>
<% end_control %>
</code>

## Configuration

The first thing you should do is find the location code for the weather report you want. This is specified as a Yahoo WOEID code.  To find your WOEID, browse or search for your city from the Yahoo Weather home page:
http://weather.yahoo.com/

The WOEID is in the URL for the forecast page for that city. You can also get the WOEID by entering your zip code on the home page. For example, if you search for Los Angeles on the Weather home page, the forecast page for that city is http://weather.yahoo.com/united-states/california/los-angeles-2442047/. The WOEID is 2442047.

> WeatherDecorator::$default_location_code = '2502265';

Note that you can use several weather objects on the same page by specifying the WOEID in the template:

> <% control Weather(2502265) %>$Title<% end_control %>

You can set the number of seconds to cache the weather info for. Default is 3600.

> WeatherDecorator::$cache_expiry = 3600;

Units to use for returned weather measurements, options are 'c' (Celsius) or 'f' (Fahrenheit).  Using 'c' gives all values (temperature, wind speed, distances) in metric units.
Default is 'c'.

> WeatherDecorator::$temperature_unit = 'c';

