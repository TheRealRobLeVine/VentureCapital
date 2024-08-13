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

class PEProductionAndCOGSSummaryReport extends Report {

	public function __construct($userID=null) {
		parent::__construct($userID);

		$plan = $this->getPlan();
		$expenses = $plan->getExpenses();
		$forecast = $plan->getForecast();
		
		$headerText = array();
		$headerText[] = "";
		foreach($plan->getYears(false) as $year) {
			$headerText[] = $year;
			$headerText[] = Messages::getMessage('VC_MESSAGE_PERCENT_OF_SALES_ABBREV');
		}
		$headerText[] = $plan->getLength() . Messages::getMessage('VC_MESSAGE_YEAR_TOTALS_ABBREV');
		$headerText[] = Messages::getMessage('VC_MESSAGE_PERCENT_OF_SALES_ABBREV');
		$headerCells = array();
		$index = 0;
		foreach($headerText as $header) {
			$headerCell = new stdClass();
			$headerCell->classes = array("centerHeader");
			$headerCell->colspan="";
			$headerCell->data = $header;
			$headerCells[] = $headerCell;
		}
		$this->htmlTable = new HTMLTable("tblProductionEconomicsProductionAndCOGSSummary",  array("dtExportAndPDF vcReport"), $headerCells);

		$row0 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_UNITS_SOLD'), false, array("cellAlignLeft")))->getHTML();
		$row1 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS_INGREDIENTS'), false, array("cellAlignLeft")))->getHTML();
		$row2 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS_LABOUR'), false, array("cellAlignLeft")))->getHTML();
		$row3 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS_PACKAGING'), false, array("cellAlignLeft")))->getHTML();
		$totalRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_COGS'), false, array("cellAlignLeft")))->getHTML();
		
		$totalUnits = 0;
		$totalIngredientsCost = 0;
		$totalLabourCost = 0;
		$totalPackagingCost = 0;
		foreach($plan->getYears(false) as $year) {
			$units = $forecast->getTotalUnitsPerYear($year);
			$totalUnits += $units;
			$row0 .= (new HTMLTableCell(Globals::numberFormat($units, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
			$ingredientsCost = 0;
			$labourCost = 0;
			$packagingCost = 0;
			foreach($plan->getCategories() as $category) {
				$catID = $category->getID();
				$salesObj = $forecast->getCategoryTotalsByYear($catID, $year);
				$units = $salesObj->getUnits();
				$categoryCOGS = null;
				foreach($expenses->getCOGS() as $singleCOGS) {
					if ($catID == $singleCOGS->getCategoryID()) {
						$categoryCOGS = $singleCOGS;
						break;
					}
				}
				if (null != $categoryCOGS) {
					$ingredientsCost += $categoryCOGS->getIngredientsCostPerUnit() * $units;
					$labourCost += $categoryCOGS->getLabourCostPerUnit() * $units;
					$packagingCost += $categoryCOGS->getPackagingCostPerUnit() * $units;
				}
			}
			$totalIngredientsCost += $ingredientsCost;
			$totalLabourCost += $labourCost;
			$totalPackagingCost += $packagingCost;
			
			$row0 .= HTMLTableCell::getBlankCell(array(""));
			$row1 .= (new HTMLTableCell(Globals::numberFormat($ingredientsCost, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$pct = ($ingredientsCost / $forecast->getTotalSalesPerYear($year)) * 100;
			$row1 .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			$row2 .= (new HTMLTableCell(Globals::numberFormat($labourCost, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$pct = ($labourCost / $forecast->getTotalSalesPerYear($year)) * 100;
			$row2 .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			$row3 .= (new HTMLTableCell(Globals::numberFormat($packagingCost, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$pct = ($packagingCost / $forecast->getTotalSalesPerYear($year)) * 100;
			$row3 .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			$totalRow .= (new HTMLTableCell(Globals::numberFormat($expenses->getCOGSByYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$pct = ($expenses->getCOGSByYear($year) / $forecast->getTotalSalesPerYear($year)) * 100;
			$totalRow .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
		}
		$row0 .= (new HTMLTableCell(Globals::numberFormat($totalUnits, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
		$row0 .= HTMLTableCell::getBlankCell(array(""));
		$row1 .= (new HTMLTableCell(Globals::numberFormat($totalIngredientsCost, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
		$pct = ($totalIngredientsCost / $forecast->getTotalSalesAllYears()) * 100;
		$row1 .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
		$row2 .= (new HTMLTableCell(Globals::numberFormat($totalLabourCost, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
		$pct = ($totalLabourCost / $forecast->getTotalSalesAllYears()) * 100;
		$row2 .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
		$row3 .= (new HTMLTableCell(Globals::numberFormat($totalPackagingCost, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
		$pct = ($totalPackagingCost / $forecast->getTotalSalesAllYears()) * 100;
		$row3 .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
		$totalRow .= (new HTMLTableCell(Globals::numberFormat($expenses->getTotalCOGS(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
		$pct = ($expenses->getTotalCOGS() / $forecast->getTotalSalesAllYears()) * 100;
		$totalRow .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
		
		$this->htmlTable->addRow($row0);
		$this->htmlTable->addRow($row1);
		$this->htmlTable->addRow($row2);
		$this->htmlTable->addRow($row3);
		$this->htmlTable->addRow($totalRow);
	}
}
	
?>