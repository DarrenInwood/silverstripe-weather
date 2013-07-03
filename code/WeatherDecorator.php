<?php

/**
 * Enables you to use Weather-related functions in your Templates.
 * http://developer.yahoo.com/weather/
 *
 * Using <% control Weather %><% end_control %> will use the default weather
 * location code, which should be set up in _config.php using 
 * WeatherDecorator::$default_location_code.  Location codes can be 
 
 *
 * @package Weather
 * @author Darren Inwood, Chrometoaster New Media Ltd
 */

class WeatherDecorator extends Extension {

    /**
     * Number of seconds to cache results for.
     * @static
     */
    public static $cache_expiry = 3600;
    
    /**
     * Units to use for returned weather measurements, options are 'c' (Celsius)
     * or 'f' (Fahrenheit).  Using 'c' gives all values in metric units.
     * @static
     */    
    public static $temperature_unit = 'c';

    public static $default_location_code = '2502265';

    public static $weather_image_map = array(
		0	=>  'tstorm3.png',	// tornado
		1	=>	'tstorm3.png',	// tropical storm
		2	=>	'tstorm3.png',	// hurricane
		3	=>	array('tstorm2.png','tstorm2_night.png'),	// severe thunderstorms
		4	=>	array('tstorm1.png','tstorm1_night.png'),	// thunderstorms
		5	=>	'sleet.jpg',	// mixed rain and snow
		6	=>	'sleet.jpg',	// mixed rain and sleet
		7	=>	'sleet.jpg',	// mixed snow and sleet
		8	=>	'sleet.jpg',	// freezing drizzle
		9	=>	array('shower1.png', 'shower1_night.png'),	// drizzle
		10	=>	'sleet.jpg',	// freezing rain
		11	=>	array('shower2.png', 'shower2_night.png'),	// showers
		12	=>	array('shower2.png', 'shower2_night.png'),	// showers
		13	=>	array('snow1.png', 'snow1_night.png'),	// snow flurries
		14	=>	array('snow1.png', 'snow1_night.png'),	// light snow showers
		15	=>	array('snow2.png', 'snow2_night.png'),	// blowing snow
		16	=>	array('snow3.png', 'snow3_night.png'),	// snow
		17	=>	'sleet.png',	// hail
		18	=>	'sleet.jpg',	// sleet
		19	=>	array('mist.png', 'mist_night.png'),	// dust
		20	=>	array('fog.png', 'fog_night.png'),	// foggy
		21	=>	array('mist.png', 'mist_night.png'),	// haze
		22	=>	array('mist.png', 'mist_night.png'),	// smoky
		23	=>	array('sun.png', 'sun_night.png'),	// blustery
		24	=>	array('sun.png', 'sun_night.png'),	// windy
		25	=>	array('sun.png', 'sun_night.png'),	// cold
		26	=>	array('cloudy4.png', 'cloudy4_night.png'),	// cloudy
		27	=>	array('cloudy3.png', 'cloudy3_night.png'),	// mostly cloudy (night)
		28	=>	array('cloudy3.png', 'cloudy3_night.png'),	// mostly cloudy (day)
		29	=>	array('cloudy2.png', 'cloudy2_night.png'),	// partly cloudy (night)
		30	=>	array('cloudy2.png', 'cloudy2_night.png'),	// partly cloudy (day)
		31	=>	array('sun.png', 'sun_night.png'),	// clear (night)
		32	=>	array('sun.png', 'sun_night.png'),	// sunny
		33	=>	array('sun.png', 'sun_night.png'),	// fair (night)
		34	=>	array('sun.png', 'sun_night.png'),	// fair (day)
		35	=>	'sleet.jpg',	// mixed rain and hail
		36	=>	array('sun.png', 'sun_night.png'),	// hot
		37	=>	array('tstorm1.png','tstorm1_night.png'),	// isolated thunderstorms
		38	=>	array('tstorm1.png','tstorm1_night.png'),	// scattered thunderstorms
		39	=>	array('tstorm1.png','tstorm1_night.png'),	// scattered thunderstorms
		40	=>	array('shower1.png', 'shower1_night.png'),	// scattered showers
		41	=>	'snow5.jpg',	// heavy snow
		42	=>	array('snow2.png', 'snow2_night.png'),	// scattered snow showers
		43	=>	'snow5.jpg',	// heavy snow
		44	=>	array('cloudy2.png', 'cloudy2_night.png'),	// partly cloudy
		45	=>	array('tstorm2.png','tstorm2_night.png'),	// thundershowers
		46	=>	array('snow2.png', 'snow2_night.png'),	// snow showers
		47	=>	array('tstorm1.png','tstorm1_night.png'),	// isolated thundershowers
		3200	=>	'dunno.png'	// not available
	);

    private static $weather_api_server = 'http://weather.yahooapis.com/forecastrss';

    /**
     * Returns a Weather object for use in templates.
     *
     * @param   $location   String  Location code from http://weather.yahoo.com/
     */
    public static function Weather($location_code=null) {
        if ( $location_code === null ) $location_code = self::$default_location_code;

        // http://doc.silverstripe.org/restfulservice
        $rss = new RestfulService(
            self::$weather_api_server,
            self::$cache_expiry
        );
        $rss->setQueryString(array(
            'w' => $location_code,
            'u' => self::$temperature_unit
        ));
        $xml = $rss->request();

        $units_temp         = (string)$xml->xpath_one('//channel/yweather:units/@temperature');
        $units_distance     = (string)$xml->xpath_one('//channel/yweather:units/@distance');
        $units_pressure     = (string)$xml->xpath_one('//channel/yweather:units/@pressure');
        $units_speed        = (string)$xml->xpath_one('//channel/yweather:units/@speed');
        $units_angle        = '°';

        $time = time();
        $sunrise = strtotime((string)$xml->xpath_one('//channel/yweather:astronomy/@sunrise'));
        $sunset = strtotime((string)$xml->xpath_one('//channel/yweather:astronomy/@sunset'));

        $weather = new DataObject();
      
        $weather->Title             = (string)$xml->xpath_one('//channel/item/title');
        $weather->Link              = (string)$xml->xpath_one('//channel/item/link');
        $weather->Description       = (string)$xml->xpath_one('//channel/item/description'); // HTML
        $weather->PublishedDate     = self::convertDate((string)$xml->xpath_one('//channel/item/pubDate')); // Date

        $weather->Latitude          = (int)$xml->xpath_one('//channel/item/geo:lat');
        $weather->Latitude          = (int)$xml->xpath_one('//channel/item/geo:long');

        $weather->City              = (string)$xml->xpath_one('//channel/yweather:location/@city');
        $weather->Region            = (string)$xml->xpath_one('//channel/yweather:location/@region');
        $weather->Country           = (string)$xml->xpath_one('//channel/yweather:location/@country');

        // Current conditions
        $weather->Condition         = (string)$xml->xpath_one('//channel/item/yweather:condition/@text'); // Short description.
        $weather->Temperature       = (string)$xml->xpath_one('//channel/item/yweather:condition/@temp').'°'.$units_temp;
        $weather->TemperatureInt    = (int)(string)$xml->xpath_one('//channel/item/yweather:condition/@temp');
        $weather->Image             = self::getimageForWeatherCode(
            (int)$xml->xpath_one('//channel/item/yweather:condition/@code'), 
            ($sunrise < $time && $time < $sunset ) 
        );  // Image

        $weather->WindChill         = (string)$xml->xpath_one('//channel/yweather:wind/@chill').'°'.$units_temp;
        $weather->WindDirection     = (string)$xml->xpath_one('//channel/yweather:wind/@direction').$units_angle;
        $weather->WindSpeed         = (string)$xml->xpath_one('//channel/yweather:wind/@speed').$units_speed;

        $weather->Humidity          = (string)$xml->xpath_one('//channel/yweather:atmosphere/@humidity').'%';
        $weather->Visibility        = (string)$xml->xpath_one('//channel/yweather:atmosphere/@visibility').$units_distance;
        $weather->BarometricPressure = (string)$xml->xpath_one('//channel/yweather:atmosphere/@pressure').$units_pressure;
        $rising_states = array(
            '0' => _t('Weather.RisingStateSteady', 'Steady'), 
            '1' => _t('Weather.RisingStateRising', 'Rising'), 
            '2' => _t('Weather.RisingStateFalling', 'Falling')
        );
        $rising_state = (string)$xml->xpath_one('//channel/yweather:atmosphere/@rising');
        $weather->RisingState       = isset($rising_states[$rising_state]) ? $rising_states[$rising_state] : null;

        $weather->SunriseTime       = self::convertDate((string)$xml->xpath_one('//channel/yweather:astronomy/@sunrise')); // Date
        $weather->SunsetTime        = self::convertDate((string)$xml->xpath_one('//channel/yweather:astronomy/@sunset')); // Date

        $weather->FeedTitle         = (string)$xml->xpath_one('//channel/title');
        $weather->FeedLink          = (string)$xml->xpath_one('//channel/link');
        $weather->FeedDescription   = (string)$xml->xpath_one('//channel/description');        

        $forecasts = new DataObjectSet();

        foreach( $xml->xpath('//yweather:forecast') as $item ) {
            $forecast = new DataObject();
            $forecast->Date             = self::convertDate((string)$item->date);  // Date
            $forecast->TemperatureLow   = (string)$item->low;
            $forecast->TemperatureHigh  = (string)$item->high;
            $forecast->Condition        = (string)$item->text;   // Short description.
            
            $forecast->Image            = self::getImageForWeatherCode(
                (int)$item->code, 
                true // Always use day icon for forecasts
            );   // Image
            $forecasts->push($forecast);
        }

        $weather->Forecasts = $forecasts;

        return $weather;
    }

    /**
     * Converts a Yahoo weather condition code (integer 0-47 or 3200) into an
     * Image object.
     *
     * Looks in the theme directory for a folder called 'weather-images', and 
     * falls back to the default images suppplied with the module if the
     * directory isn't there.
     *
     * See http://developer.yahoo.com/weather/ for a full list of codes.
     *
     * @param   $code   Integer     The Yahoo weather condition code to convert.
     * @return  Image   An Image object representing an icon.
     */
    private static function getImageForWeatherCode($code, $isDay) {
        $dirname = 'themes/'.SSViewer::current_theme().'/weather-images/';
        if ( ! is_dir(dirname(dirname(dirname(__FILE__))).'/'.$dirname) ) {
            $dirname = 'weatherservice/weather-images/';
        }
        $filename = isset(self::$weather_image_map[(int)$code]) 
            ? self::$weather_image_map[(int)$code] 
            : $weather_image_map[3200];     // Use 'not available' if no matching code
        // Choose night/day
        if ( is_array($filename) && count($filename) > 1 ) {
            $filename = $isDay ? $filename[0] : $filename[1];
        }
        $image = new Image();
        $image->ID = -1;
        $image->setFilename($dirname.$filename);
        return $image;
    }

    /**
     * Converts a date in the format given by Yahoo to a SS Date object.
     */
    private static function convertDate($string) {
        $date = new Date();
        $date->setValue($string);
        return $date;
    }
}

