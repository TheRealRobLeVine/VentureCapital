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

class SPXReport extends Report {

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
		$this->htmlTable = new HTMLTable("tblSalesProjectionsX",  array("dtExportAndPDF vcReport"), $headerCells, "");

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