<?php

namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\Sales' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Sales.php';
}
use VentureCapital\Sales;

use FrmField;
use FrmEntry;
use FrmForm;

class Segment {

	protected $ID;
	protected $name;
	protected $growthRates;  // array of growth numbers by year
	protected $salesByYear; // array indexed by 0 (baseline) and year.  Stores the units sold and total sales amount

	public function __construct($ID, $name) {
		$this->ID = $ID;
		$this->name = $name;
	}
	
	public function getID() {
		return $this->ID;
	}

	public function getName() {
		return $this->name;
	}
	
	/* returns an array, e.g., array("2025"=>10, "2026"=>15....) */
	public function getGrowthRates() {
		return $this->growthRates;
	}

	/**
	* 	setGrowth
	*
	*  Sets the growth for each year for a given channel or category
	*
	*   $plan - plan object
	*   $childFormKey - key of the form that holds the repeater data
	*   $segmentID - channel or category ID
	*   $growthIDSegmentFieldKey - key of the field that holds the segment iD
	*   $growthFieldPrefix - field key prefix to allow looping over the years, e.g., financialplan-salesforecasting_percent_growth_sales_channel_year_
	*   $userID - 
	*/
	function setGrowthRates($plan, $childFormKey, $segmentID, $growthIDSegmentFieldKey, $growthFieldPrefix, $userID) {
		$growthEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key($childFormKey), 'it.user_id' => $userID), '', '', true);
		$growthRates = array();
		$segmentIDFieldID = FrmField::get_id_by_key($growthIDSegmentFieldKey);
		foreach ($growthEntries as $growthEntry) {
			$iIndex = 1;
			if ($growthEntry->metas[$segmentIDFieldID] == $segmentID) {
				for($i=$plan->getStartYear();$i<($plan->getStartYear()+$plan->getLength());$i++) {
					$fieldKey =  $growthFieldPrefix . $iIndex++;
					$fieldID = FrmField::get_id_by_key($fieldKey);
					$growthRates[$i] = $growthEntry->metas[$fieldID];
				}
			}
		}
		$this->growthRates = $growthRates;
	}

	/**
	* 	setSalesByYear
	*
	*  Sets the sales for the segment (category/channel) for each year either by setting values or adding to what's already there
	*
	*   $units - number of units sold
	*   $sales - sales (units * price)
	*   $year
	*
	*/
	public function setSalesByYear($units, $sales, $year) {
		$salesObj = new Sales($units, $sales);
		if (isset($this->salesByYear[$year])) {
			$units += $this->salesByYear[$year]->getUnits();
			$sales += $this->salesByYear[$year]->getSales();
			$salesObj->setUnits($units);
			$salesObj->setSales($sales);
		}
		$this->salesByYear[$year] = $salesObj;
	}
	
	public function getSalesByYear() {
		return $this->salesByYear;
	}

	public function getSalesBySingleYear($year) {
		return $this->salesByYear[$year];
	}
}

?>