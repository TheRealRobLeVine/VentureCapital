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

class ProductionEconomicsSalesMarginReport extends Report {

	public function __construct($userID=null) {
		parent::__construct($userID);

		$plan = $this->getPlan();
		$expenses = $plan->getExpenses();
		$COGS = $expenses->getCOGS();
		
		$headerText = array(Messages::getMessage('VC_MESSAGE_PRODUCT_CATEGORY'), Messages::getMessage('VC_MESSAGE_UNIT_COST'));
		foreach($plan->getChannels(false) as $channel) {
			$headerText[] = $channel->getName();
		}
		$headerCells = array();
		$index = 0;
		foreach($headerText as $header) {
			$headerCell = new stdClass();
			$headerCell->classes = array("centerHeader");
			$headerCell->colspan="";
			$headerCell->data = $header;
			$headerCells[] = $headerCell;
		}

		$this->htmlTable = new HTMLTable("tblProductionEconomicsSalesMargin",  array("dtExportAndPDF vcReport"), $headerCells);
		$numCategories = count($plan->getCategories());
		foreach($plan->getCategories() as $category) {
				$categoryID = $category->getID();
				$row = (new HTMLTableCell($category->getName(), false, array("cellAlignLeft")))->getHTML();
				$singleCOGS = $this->_getCOGSFromCategoryID($COGS, $categoryID);
				$row .= (new HTMLTableCell(Globals::numberFormat($singleCOGS->getUnitCost(), Globals::NUMBER_FORMAT_TYPE_PRICE_FRACTION, true)))->getHTML();
				
				foreach($plan->getChannels(false) as $channel) {
					$channelID = $channel->getID();
					$priceAndMargin = $plan->getPriceAndMarginByCategoryAndChannel($categoryID, $channelID);
					$price = $priceAndMargin->getUnitPrice();
					$margin = (($price - $singleCOGS->getUnitCost()) / $price)*100;
					if (!isset($totalMarginByChannel[$channelID])) {
						$totalMarginByChannel[$channelID] = 0;
					}
					$totalMarginByChannel[$channelID] += $margin;
					$row .= (new HTMLTableCell(Globals::numberFormat($margin, Globals::NUMBER_FORMAT_TYPE_PERCENT_INTEGER, true)))->getHTML();
				}
				$this->htmlTable->addRow($row);
		}
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_AVERAGE_MARGIN_BY_CHANNEL'), false, array("cellAlignLeft", "subTotal")))->getHTML();
		$row .= HTMLTableCell::getBlankCell(array("subTotal"));
		foreach($plan->getChannels(false) as $channel) {
			$channelID = $channel->getID();
			$margin = $totalMarginByChannel[$channelID] / $numCategories;
			$row .= (new HTMLTableCell(Globals::numberFormat($margin, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "subTotal")))->getHTML();
		}
		$this->htmlTable->addRow($row);
		
	}

	private function _getCOGSFromCategoryID($COGS, $categoryID) {
		foreach($COGS as $singleCOGS) {
			if ($categoryID == $singleCOGS->getCategoryID()) {
				return $singleCOGS;
			}
		}
	}

	private function _getUnitPriceByCategoryAndChannel($COGS, $categoryID) {
		foreach($COGS as $singleCOGS) {
			if ($categoryID == $singleCOGS->getCategoryID()) {
				return $singleCOGS;
			}
		}
	}

}
	
?>