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

class PEUnitCostAnalysisReport extends Report {

	public function __construct($userID=null) {
		parent::__construct($userID);

		$plan = $this->getPlan();
		$expenses = $plan->getExpenses();
		
		$headerText = array("", Messages::getMessage('VC_MESSAGE_COGS_INGREDIENTS'), 
												Messages::getMessage('VC_MESSAGE_COGS_LABOUR'), 
												Messages::getMessage('VC_MESSAGE_COGS_PACKAGING'), 
												Messages::getMessage('VC_MESSAGE_TOTAL_HEADER'));
		$headerCells = array();
		$index = 0;
		foreach($headerText as $header) {
			$headerCell = new stdClass();
			$headerCell->classes = array("centerHeader");
			$headerCell->colspan="";
			$headerCell->data = $header;
			$headerCells[] = $headerCell;
		}

		$this->htmlTable = new HTMLTable("tblProductionEconomicsUnitCostAnalysis",  array("dtExportAndPDF vcReport"), $headerCells);
		foreach($plan->getCategories() as $category) {
				$categoryID = $category->getID();
				$headerCell = new stdClass();
				$headerCell->classes = array("cellAlignLeft", "categoryName");
				$headerCell->colspan="";
				$headerCell->data = (new HTMLTableCell($category->getName(), false, array("cellAlignLeft" , "categoryName")))->getHTML();

				$this->htmlTable->addEmptyRowWithHeader( $headerCell, 4);
				
				$singleCOGS = $expenses->getCOGSFromCategoryID($categoryID);
				if (null != $singleCOGS) {
					$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_UNIT_COST'), false, array("cellAlignLeft")))->getHTML();
					$row .= (new HTMLTableCell(Globals::numberFormat($singleCOGS->getIngredientsCostPerUnit(), Globals::NUMBER_FORMAT_TYPE_PRICE_FRACTION, true)))->getHTML();
					$row .= (new HTMLTableCell(Globals::numberFormat($singleCOGS->getLabourCostPerUnit(), Globals::NUMBER_FORMAT_TYPE_PRICE_FRACTION, true)))->getHTML();
					$row .= (new HTMLTableCell(Globals::numberFormat($singleCOGS->getPackagingCostPerUnit(), Globals::NUMBER_FORMAT_TYPE_PRICE_FRACTION, true)))->getHTML();
					$row .= (new HTMLTableCell(Globals::numberFormat($singleCOGS->getUnitCost(), Globals::NUMBER_FORMAT_TYPE_PRICE_FRACTION, true)))->getHTML();
					
					$this->htmlTable->addRow($row);
					
					$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_PERCENT_OF_COST'), false, array("cellAlignLeft")))->getHTML();
					$pct = ($singleCOGS->getIngredientsCostPerUnit() / $singleCOGS->getUnitCost()) * 100;
					$row .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
					$pct = ($singleCOGS->getLabourCostPerUnit() / $singleCOGS->getUnitCost()) * 100;
					$row .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
					$pct = ($singleCOGS->getPackagingCostPerUnit() / $singleCOGS->getUnitCost()) * 100;
					$row .= (new HTMLTableCell(Globals::numberFormat($pct, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
					$row .= HTMLTableCell::getBlankCell(array("blackedOut"));
					$this->htmlTable->addRow($row);
				}
				else {
					$headerCell = new stdClass();
					$headerCell->classes = array("cellAlignLeft");
					$headerCell->colspan="";
					$headerCell->data =Messages::getMessage('VC_MESSAGE_UNIT_COST');
					$this->htmlTable->addEmptyRowWithHeader($headerCell, 8);
					$headerCell->data =Messages::getMessage('VC_MESSAGE_PERCENT_OF_COST');
					$this->htmlTable->addEmptyRowWithHeader($headerCell, 8);
				}
		}
	}
}
	
?>