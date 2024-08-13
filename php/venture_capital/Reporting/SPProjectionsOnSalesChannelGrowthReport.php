<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\Report' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/Report.php';
}
use VentureCapital\Report;
use stdClass;

class SPProjectionsOnSalesChannelGrowthReport extends Report {

	public function __construct($userID=null) {
		parent::__construct($userID);

		$plan = $this->getPlan();
		$expenses = $plan->getExpenses();
		$forecast = $plan->getForecast();
		$headerText = array(Messages::getMessage('VC_MESSAGE_CATEGORY'),  Messages::getMessage('VC_MESSAGE_BASELINE'));
		foreach($plan->getYears(false) as $year) {
			$headerText[] = $year;
		}
		$headerText[] = $plan->getLength() . Messages::getMessage('VC_MESSAGE_YEAR_TOTALS_ABBREV');
		$headerCells = array();
		$index = 0;
		foreach($headerText as $header) {
			$headerCell = new stdClass();
			$headerCell->classes = array("centerHeader");
			$headerCell->colspan="";
			$headerCell->data = $header;
			$headerCells[] = $headerCell;
		}
		$this->htmlTable = new HTMLTable("tblSalesProjectionsOnSalesChannelGrowth",  array("dtExportAndPDF vcReport"), $headerCells, "");

		foreach($plan->getChannels() as $channel) {
				$channelID = $channel->getID();
				$row = (new HTMLTableCell($channel->getName(), false, array("cellAlignLeft" , "categoryName")))->getHTML();
				foreach($plan->getYears(true) as $year) {
					$channelTotalByYear = $forecast->getChannelTotalsByYear($channelID, $year);
					$row .= (new HTMLTableCell(Globals::numberFormat($channelTotalByYear->getSales(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
				}
				$channelTotalByPlan = $forecast->getChannelTotalSalesByPlanLength($channelID);
				$row .= (new HTMLTableCell(Globals::numberFormat($channelTotalByPlan, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
				$this->htmlTable->addRow($row);

				$growthRateRowNeeded = true;
				$growthRateRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_GROWTH_RATE'), false, array("cellAlignRight", "growthRate" )))->getHTML();
				$growthRateRow .= HTMLTableCell::getBlankCell(array(""));
				foreach($plan->getCategories() as $category) {
					$categoryID = $category->getID();
					$total = $plan->getAverageBaselineSalesByChannelAndCategory($channelID, $categoryID);
					$row = (new HTMLTableCell($category->getName()))->getHTML();
					$totalUnits = 0;
					if (null != $total) {
						$units = $total->getUnits();
						$row .= (new HTMLTableCell(Globals::numberFormat($units, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
						foreach($plan->getYears(false) as $year) {
							$units = $units * (1 + ($total->getGrowthRateByYear($year)/100));
							$totalUnits += $units;
							$row .= (new HTMLTableCell(Globals::numberFormat($units, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
							if ($growthRateRowNeeded) {
								$growthRateRow .= (new HTMLTableCell(Globals::numberFormat($total->getGrowthRateByYear($year), Globals::NUMBER_FORMAT_TYPE_PERCENT_INTEGER, true)))->getHTML();
							}
						}
						$growthRateRowNeeded = false;
						$row .= (new HTMLTableCell(Globals::numberFormat($totalUnits, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
					}
					else {
						$row .= (new HTMLTableCell(Globals::numberFormat(0, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
						foreach($plan->getYears(false) as $year) {
							$row .= (new HTMLTableCell(Globals::numberFormat(0, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
						}
						$row .= (new HTMLTableCell(Globals::numberFormat($totalUnits, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
					}
					
					//$growthRateRow .= HTMLTableCell::getBlankCell(array(""));
					$this->htmlTable->addRow($row);
				}
				
				$growthRateRow .= HTMLTableCell::getBlankCell(array(""));
				$this->htmlTable->addRow($growthRateRow);
				// Units Subtotals
				$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_SUBTOTAL_UNITS'), false, array("cellAlignRight", "subTotal" )))->getHTML();
				$totalUnits = 0;
				foreach($plan->getYears() as $year) {
					$unitsByYear = $forecast->getChannelTotalsByYear($channelID, $year);
					$units = $unitsByYear->getUnits();
					$totalUnits += $units;
					$row .= (new HTMLTableCell(Globals::numberFormat($units, Globals::NUMBER_FORMAT_TYPE_INTEGER, true), false, array("cellAlignRight", "subTotal" )))->getHTML();
				}
				$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getChannelTotalUnitsByPlanLength($channelID), Globals::NUMBER_FORMAT_TYPE_INTEGER, true), false, array("cellAlignRight", "subTotal" )))->getHTML();
				$this->htmlTable->addRow($row);
		}

		// Total Sales
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_SALES'), false, array("cellAlignLeft", "grandTotal")))->getHTML();
		$totalSales = 0;
		foreach($plan->getYears() as $year) {
			$salesByYear = $forecast->getTotalSalesPerYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($salesByYear, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "grandTotal")))->getHTML();
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getTotalSalesAllYears(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "grandTotal")))->getHTML();
		$this->htmlTable->addRow($row);
		
		// Total Units
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_UNITS'), false, array("cellAlignLeft")))->getHTML();
		$totalUnitsByYear = 0;
		foreach($plan->getYears() as $year) {
			$unitsByYear = $forecast->getTotalUnitsPerYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($unitsByYear, Globals::NUMBER_FORMAT_TYPE_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML();
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getTotalUnitsAllYears(), Globals::NUMBER_FORMAT_TYPE_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML();
		$this->htmlTable->addRow($row);
		
		// Year over Year
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_YEAR_OVER_YEAR'), false, array("cellAlignLeft")))->getHTML();
		$row .= (new HTMLTableCell(""))->getHTML();
		$yearOverYear = array();
		$lastYear = $plan->getStartYear() + $plan->getLength() - 1;
		foreach($plan->getYears() as $year) {
			if ($year <=0) continue;
			if ($year == $plan->getStartYear()) {
				$yearOverYear[$year] = (($forecast->getTotalSalesPerYear($year) / $forecast->getTotalSalesPerYear(0))-1) * 100; 
			}
			else {
				$yearOverYear[$year] = (($forecast->getTotalSalesPerYear($year) / $forecast->getTotalSalesPerYear($year-1))-1)*100;
			}
			$row .= (new HTMLTableCell(Globals::numberFormat($yearOverYear[$year], Globals::NUMBER_FORMAT_TYPE_PERCENT_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
		}

		$yrTotalAvg = (($forecast->getTotalSalesPerYear($lastYear) /$forecast->getTotalSalesPerYear(0))-1)*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($yrTotalAvg, Globals::NUMBER_FORMAT_TYPE_PERCENT_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
		$this->htmlTable->addRow($row);
		
	}
}
	
?>