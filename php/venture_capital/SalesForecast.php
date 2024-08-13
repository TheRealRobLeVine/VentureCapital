<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\Totals' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Totals.php';
}
use VentureCapital\Totals;

class SalesForecast {
	const VC_SALES_FORECAST_FORM_KEY = 'financialplan-salesforecasting';
	const VC_GROW_BY_FIELD_KEY = 'financialplan-salesforecasting_growth_by';
	
	private $salesByYear; // 0-based array of sales objects
	private $totalSalesForYear;
	private $totalUnitsForYear;
	private $totalSalesAllYears; // not including baseline
	private $totalUnitsAllYears; // not including baseline
	
	private $categoryTotalsByYear;
	private $channelTotalsByYear;
	
	private $categoryTotalSalesByPlanLength;
	private $channelTotalSalesByPlanLength;
	private $categoryTotalUnitsByPlanLength;
	private $channelTotalUnitsByPlanLength;
	
	public function __construct() {
		$this->salesByYear = array();
	}
	
	public function setSalesByYear($sales, $year) {
		$this->salesByYear[$year] = $sales;
	}
	
	public function getSalesByYear($year) {
		return $this->salesByYear[$year];
	}
	
	/**
	 *  setTotals - sets totals for each category, each channel, across each year and across all years (not including year 0, the baseline)
	 *
	 *
	 */
	public function setTotals($plan) {
		$this->totalSalesAllYears = 0; // not including baseline
		$this->totalUnitsAllYears = 0; // not including baseline
		foreach($plan->getYears() as $year) {
			$this->totalSalesForYear[$year] = 0;
			$this->totalUnitsForYear[$year] = 0;
			foreach($this->getSalesByYear($year) as $sales) {
				$this->totalSalesForYear[$year] += $sales->getSales();
				$this->totalUnitsForYear[$year] += $sales->getUnits();
			}
			if ($year > 0) {
				$this->totalSalesAllYears += $this->totalSalesForYear[$year] ;
				$this->totalUnitsAllYears += $this->totalUnitsForYear[$year] ;
			}
		}
		
		// channels
		foreach($plan->getChannels() as $channel) {
			$channelID = $channel->getID();
			$this->channelTotalSalesByPlanLength[$channelID] = 0;
			$this->channelTotalUnitsByPlanLength[$channelID] = 0;
			foreach($plan->getYears() as $year) {
				$units = 0;
				$sales = 0;
				foreach($this->getSalesByYear($year) as $salesObj) {
					if ($channelID == $salesObj->getChannelID()) {
						$sales += $salesObj->getSales();
						$units += $salesObj->getUnits();
						if ($year > 0) { // exclude baseline
							$this->channelTotalSalesByPlanLength[$channelID] += $salesObj->getSales();
							$this->channelTotalUnitsByPlanLength[$channelID] += $salesObj->getUnits();
						}
					}
				}
				$this->channelTotalsByYear[$channelID][$year] = new Totals($units, $sales);
			}
		}

		// categories
		foreach($plan->getCategories() as $category) {
			$categoryID = $category->getID();
			$this->categoryTotalSalesByPlanLength[$categoryID] = 0;
			$this->categoryTotalUnitsByPlanLength[$categoryID] = 0;
			foreach($plan->getYears() as $year) {
				$units = 0;
				$sales = 0;
				foreach($this->getSalesByYear($year) as $salesObj) {
					if ($categoryID == $salesObj->getCategoryID()) {
						$sales += $salesObj->getSales();
						$units += $salesObj->getUnits();
						if ($year > 0) { // exclude baseline
							$this->categoryTotalSalesByPlanLength[$categoryID] += $salesObj->getSales();
							$this->categoryTotalUnitsByPlanLength[$categoryID] += $salesObj->getUnits();
						}
					}
				}
				$this->categoryTotalsByYear[$categoryID][$year] = new Totals($units, $sales);
			}
		}
		
		foreach($plan->getYears() as $year) {
		}
	}
	
	/* total across a single channel for a year */
	public function getChannelTotalsByYear($channelID, $year) {
		return $this->channelTotalsByYear[$channelID][$year] ;
	}
	
	/* total across a single category for a year */
	public function getCategoryTotalsByYear($categoryID, $year) {
		return $this->categoryTotalsByYear[$categoryID][$year] ;
	}
	
	/* total across a single category for the entire plan length */
	public function getCategoryTotalSalesByPlanLength($categoryID) {
			return $this->categoryTotalSalesByPlanLength[$categoryID];
	}
	public function getCategoryTotalUnitsByPlanLength($categoryID) {
			return $this->categoryTotalUnitsByPlanLength[$categoryID];
	}
	public function getChannelTotalSalesByPlanLength($channelID) {
			return $this->channelTotalSalesByPlanLength[$channelID];
	}
	public function getChannelTotalUnitsByPlanLength($channelID) {
			return $this->channelTotalUnitsByPlanLength[$channelID];
	}
	
	public function getTotalSalesPerYear($year) {
		return $this->totalSalesForYear[$year];
	}

	public function getTotalUnitsPerYear($year) {
		return $this->totalUnitsForYear[$year];
	}

	public function getTotalSalesAllYears() {
		return $this->totalSalesAllYears;
	}

	public function getTotalUnitsAllYears() {
		return $this->totalUnitsAllYears;
	}
}

?>