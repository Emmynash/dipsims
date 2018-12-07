<?php
/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories(array(

	app_path().'/commands',
	app_path().'/controllers',
	app_path().'/models',
	app_path().'/database/seeds',

));

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a basic log file setup which creates a single file for logs.
|
*/

Log::useFiles(storage_path().'/logs/laravel.log');

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

App::error(function(Exception $exception, $code)
{
	Log::error($exception);
});

/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenance mode is in effect for the application.
|
*/

App::down(function()
{
	return Response::make("Be right back!", 503);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path().'/filters.php';

if(!function_exists('strptime')) {
	function strptime($sDate, $sFormat)
	{
		$aResult = array('tm_sec'   => 0,'tm_min'   => 0,'tm_hour'  => 0,'tm_mday'  => 1,'tm_mon'   => 0,'tm_year'  => 0,'tm_wday'  => 0,'tm_yday'  => 0,'unparsed' => $sDate);
		while($sFormat != "")
		{
			$nIdxFound = strpos($sFormat, '%');
			if($nIdxFound === false)
			{
				$aResult['unparsed'] = ($sFormat == $sDate) ? "" : $sDate;
				break;
			}
			$sFormatBefore = substr($sFormat, 0, $nIdxFound);
			$sDateBefore   = substr($sDate,   0, $nIdxFound);
			if($sFormatBefore != $sDateBefore) break;
			// ===== Read the value of the %x found =====
			$sFormat = substr($sFormat, $nIdxFound);
			$sDate   = substr($sDate,   $nIdxFound);
			$aResult['unparsed'] = $sDate;
			$sFormatCurrent = substr($sFormat, 0, 2);
			$sFormatAfter   = substr($sFormat, 2);
			$nValue = -1;
			$sDateAfter = "";
			switch($sFormatCurrent)
			{
				case '%S': // Seconds after the minute (0-59)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
					if(($nValue < 0) || ($nValue > 59)) return false;
					$aResult['tm_sec']  = $nValue;
					break;
				// ----------
				case '%M': // Minutes after the hour (0-59)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
					if(($nValue < 0) || ($nValue > 59)) return false;
					$aResult['tm_min']  = $nValue;
					break;
				// ----------
				case '%H': // Hour since midnight (0-23)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
					if(($nValue < 0) || ($nValue > 23)) return false;
					$aResult['tm_hour']  = $nValue;
					break;
				// ----------
				case '%d': // Day of the month (1-31)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
					if(($nValue < 1) || ($nValue > 31)) return false;
					$aResult['tm_mday']  = $nValue;
					break;
				// ----------
				case '%m': // Months since January (0-11)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
					if(($nValue < 1) || ($nValue > 12)) return false;
					$aResult['tm_mon']  = ($nValue - 1);
					break;
				// ----------
				case '%Y': // Years since 1900
					sscanf($sDate, "%4d%[^\\n]", $nValue, $sDateAfter);
					if($nValue < 1900) return false;
					$aResult['tm_year']  = ($nValue - 1900);
					break;
				// ----------
				default:
					break 2; // Break Switch and while
			} // END of case format
			// ===== Next please =====
			$sFormat = $sFormatAfter;
			$sDate   = $sDateAfter;
			$aResult['unparsed'] = $sDate;
		} // END of while($sFormat != "")
		// ===== Create the other value of the result array =====
		$nParsedDateTimestamp = mktime($aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'],
								$aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900);
		// Before PHP 5.1 return -1 when error
		if(($nParsedDateTimestamp === false)
		||($nParsedDateTimestamp === -1)) return false;
		$aResult['tm_wday'] = (int) strftime("%w", $nParsedDateTimestamp); // Days since Sunday (0-6)
		$aResult['tm_yday'] = (strftime("%j", $nParsedDateTimestamp) - 1); // Days since January 1 (0-365)
		return $aResult;
	} // END of function
}
