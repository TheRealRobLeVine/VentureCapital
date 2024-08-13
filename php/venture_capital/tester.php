<?php
namespace VentureCapital;


	$path = preg_replace('/wp-content.*$/','',__DIR__);
	require($path . '/wp-load.php');

/*	use Locale;
	
	$currentLocale = setlocale(LC_ALL, 0);
	$region = Locale::getRegion($currentLocale);
	$language = Locale::getPrimaryLanguage($currentLocale);

	echo "region: $region & language $language<br";*/
	if ( ! class_exists( '\VentureCapital\SalesForecast' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/SalesForecast.php';
	}

	use VentureCapital\SalesForecast;
	
	$report = new SalesForecast("Sales Forecast", "This is a sales forecast report");
	$report->output();
	
?>