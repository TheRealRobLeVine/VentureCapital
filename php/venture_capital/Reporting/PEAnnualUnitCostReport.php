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

class PEAnnualUnitCostReport extends Report {

	public function __construct($userID=null) {
		parent::__construct($userID);

		$plan = $this->getPlan();
		$expenses = $plan->getExpenses();
		$forecast = $plan->getForecast();
		
		$headerText = array();
		$headerText[] = "";
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
		$this->htmlTable = new HTMLTable("tblProductionEconomicsAnnualUnitCost",  array("dtExportAndPDF vcReport"), $headerCells);

		foreach($plan->getCategories() as $category) {
				$categoryID = $category->getID();
				$headerCell = new stdClass();
				$headerCell->classes = array("cellAlignLeft", "categoryName");
				$headerCell->colspan="";
				$headerCell->data = (new HTMLTableCell($category->getName(), false, array("cellAlignLeft" , "categoryName")))->getHTML();

				$this->htmlTable->addEmptyRowWithHeader( $headerCell, 6 );
				
				$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_UNITS_SOLD'), false, array("cellAlignLeft")))->getHTML();
				$row2 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS'), false, array("cellAlignLeft")))->getHTML();
				$singleCOGS = $expenses->getCOGSFromCategoryID($categoryID);
				$totalUnits = 0;
				$totalCOGS = 0;
				foreach($plan->getYears(false) as $year) {
					$yearTotals = $forecast->getCategoryTotalsByYear($categoryID, $year);
					$units = $yearTotals->getUnits();
					$totalUnits += $units;
					$row .= (new HTMLTableCell(Globals::numberFormat($units, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
					$COGS = $units * (($singleCOGS) ? $singleCOGS->getUnitCost() : 0);
					$totalCOGS += $COGS;
					$row2 .= (new HTMLTableCell(Globals::numberFormat($COGS, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
				}
				$row .= (new HTMLTableCell(Globals::numberFormat($totalUnits, Globals::NUMBER_FORMAT_TYPE_INTEGER, true)))->getHTML();
				$row2 .= (new HTMLTableCell(Globals::numberFormat($totalCOGS, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
				$this->htmlTable->addRow($row);
				$this->htmlTable->addRow($row2);
		}
	}
}
	
?>