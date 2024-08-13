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

class SalesForecastReport extends Report {

	public function __construct($userID=null) {
		parent::__construct($userID);

		$plan = $this->getPlan();

		$growByChannel = $plan->isGrowByChannel();
		
		$baselineSales = $plan->getAverageBaselineSales();
		$baselineTotals = $plan->getChannelBaselineTotals();
		
		$colHeaderMsg = ($growByChannel) ? 'VC_MESSAGE_CHANNEL' : 'VC_MESSAGE_CATEGORY';
		$headerText = array(Messages::getMessage($colHeaderMsg), Messages::getMessage('VC_MESSAGE_BASELINE'));
		foreach($plan->getYears(false) as $year) {
			$headerText[] = $year;
		}
		$headerText[] = $plan->getLength() . Messages::getMessage('VC_MESSAGE_YEAR_TOTALS_ABBREV');
		$headerCells = array();
		$index = 0;
		foreach($headerText as $header) {
			$headerCell = new stdClass();
			if ($index++ == 0) {
				$headerCell->classes = "";
			}
			else {
				$headerCell->classes = array("centerHeader");
			}
			$headerCell->colspan="";
			$headerCell->data = $header;
			$headerCells[] = $headerCell;
		}
		$this->htmlTable = new HTMLTable("tblSalesForecast",  array("dtExportAndPDF vcReport"), $headerCells);
		$allTotalBaselineUnits = 0;
		$forecast = $plan->getForecast();
		$yearTotals = array();
		if ($growByChannel) {
			foreach($plan->getChannels() as $channel) {
				$channelID = $channel->getID();
				// Sales numbers
				$cell = new HTMLTableCell($channel->getName(), false, array("cellAlignLeft"));
				$row = $cell->getHTML();
				foreach($plan->getYears() as $year) {
					$salesByYear = $forecast->getChannelTotalsByYear($channelID, $year);
					$sales = $salesByYear->getSales();
					$cell = new HTMLTableCell(Globals::numberFormat($sales, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true));
					$row .= $cell->getHTML();
				}
				$cell = new HTMLTableCell(Globals::numberFormat($forecast->getChannelTotalSalesByPlanLength($channelID), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true));
				$row .= $cell->getHTML();
				$this->htmlTable->addRow($row);
				
				// Units Subtotals
				$cell = new HTMLTableCell(Messages::getMessage('VC_MESSAGE_SUBTOTAL_UNITS'));
				$row = $cell->getHTML();
				foreach($plan->getYears() as $year) {
					$unitsByYear = $forecast->getChannelTotalsByYear($channelID, $year);
					$units = $unitsByYear->getUnits();
					$cell = new HTMLTableCell(Globals::numberFormat($units, Globals::NUMBER_FORMAT_TYPE_INTEGER, true));
					$row .= $cell->getHTML();
				}
				$cell = new HTMLTableCell(Globals::numberFormat($forecast->getChannelTotalUnitsByPlanLength($channelID), Globals::NUMBER_FORMAT_TYPE_INTEGER, true));
				$row .= $cell->getHTML();
				$this->htmlTable->addRow($row);
			}
		}
		else {
			foreach($plan->getCategories() as $category) {
				$categoryID = $category->getID();
				// Sales numbers
				$cell = new HTMLTableCell($category->getName(), false, array("cellAlignLeft"));
				$row = $cell->getHTML();
				foreach($plan->getYears() as $year) {
					$salesByYear = $forecast->getCategoryTotalsByYear($categoryID, $year);
					$sales = $salesByYear->getSales();
					$cell = new HTMLTableCell(Globals::numberFormat($sales, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true));
					$row .= $cell->getHTML();
				}
				$cell = new HTMLTableCell(Globals::numberFormat($forecast->getCategoryTotalSalesByPlanLength($categoryID), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true));
				$row .= $cell->getHTML();
				$this->htmlTable->addRow($row);
				
				// Units Subtotals
				$cell = new HTMLTableCell(Messages::getMessage('VC_MESSAGE_SUBTOTAL_UNITS'));
				$row = $cell->getHTML();
				foreach($plan->getYears() as $year) {
					$unitsByYear = $forecast->getCategoryTotalsByYear($categoryID, $year);
					$units = $unitsByYear->getUnits();
					$cell = new HTMLTableCell(Globals::numberFormat($units, Globals::NUMBER_FORMAT_TYPE_INTEGER, true));
					$row .= $cell->getHTML();
				}
				$cell = new HTMLTableCell(Globals::numberFormat($forecast->getCategoryTotalUnitsByPlanLength($categoryID), Globals::NUMBER_FORMAT_TYPE_INTEGER, true));
				$row .= $cell->getHTML();
				$this->htmlTable->addRow($row);
			}
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