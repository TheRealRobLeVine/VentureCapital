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

class SPProjectionsOnCategoryGrowthReport extends Report {

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
		$this->htmlTable = new HTMLTable("tblSalesProjectionsCategorySalesOnChannelGrowth",  array("dtExportAndPDF vcReport"), $headerCells, "");

		foreach($plan->getCategories() as $category) {
			$categoryID = $category->getID();
			$row = (new HTMLTableCell($category->getName(), false, array("cellAlignLeft" , "categoryName")))->getHTML();
			foreach($plan->getYears(true) as $year) {
				$totalByYear = $forecast->getCategoryTotalsByYear($categoryID, $year);
				$row .= (new HTMLTableCell(Globals::numberFormat($totalByYear->getSales(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "categoryName")))->getHTML();
			}
			$channelTotalByPlan = $forecast->getCategoryTotalSalesByPlanLength($categoryID);
			$row .= (new HTMLTableCell(Globals::numberFormat($channelTotalByPlan, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "categoryName")))->getHTML();
			$this->htmlTable->addRow($row);

			foreach($plan->getChannels() as $channel) {
					$channelID = $channel->getID();
					$row = (new HTMLTableCell($channel->getName(), false, array("cellAlignRight" )))->getHTML();
					$sales = $plan->getAverageBaselineSalesByChannelAndCategory($channelID, $categoryID);
					$totalUnits = 0;
					if (null != $sales) {
						$units = $sales->getUnits();
						$totalUnits += $units;
						$row .= (new HTMLTableCell(Globals::numberFormat($units, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
						foreach($plan->getYears(false) as $year) {
							$units = ($units * (1+($sales->getGrowthRateByYear($year)/100)));
							$totalUnits += $units;
							$row .= (new HTMLTableCell(Globals::numberFormat($units, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
						}
					}
					else {
						foreach($plan->getYears(true) as $year) {
							$row .= (new HTMLTableCell(Globals::numberFormat(0, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
						}
					}
					$row .= (new HTMLTableCell(Globals::numberFormat($totalUnits, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
					$this->htmlTable->addRow($row);
			}
			
/*			$salesRow = (new HTMLTableCell($category->getName()))->getHTML();
			$unitsRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_UNITS'), false, array("cellAlignRight", "allCaps")))->getHTML();
			foreach($plan->getYears(true) as $year) {
				$salesByYear = $forecast->getCategoryTotalsByYear($categoryID, $year);
				$sales = $salesByYear->getSales();
				$units = $salesByYear->getUnits();
				$salesRow .= (new HTMLTableCell(Globals::numberFormat($sales, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
				$unitsRow .= (new HTMLTableCell(Globals::numberFormat($units, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
			}
			$salesRow .= (new HTMLTableCell(Globals::numberFormat($forecast->getCategoryTotalSalesByPlanLength($categoryID), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$unitsRow .= (new HTMLTableCell(Globals::numberFormat($forecast->getCategoryTotalUnitsByPlanLength($categoryID), Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
			$this->htmlTable->addRow($salesRow);
			$this->htmlTable->addRow($unitsRow);
*/			
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
			
	}
}
	
?>