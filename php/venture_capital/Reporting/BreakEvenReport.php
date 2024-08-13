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

class BreakEvenReport extends Report {

	public function __construct($userID=null) {
		parent::__construct($userID);

		$plan = $this->getPlan();
		$forecast = $plan->getForecast();
		$expenses = $plan->getExpenses();
		
		$headerText = array("");
		foreach($plan->getYears(false) as $year) {
				$headerText[] = $year;
		}
		$headerText[] = $plan->getLength() . Messages::getMessage('VC_MESSAGE_YEAR_TOTALS_ABBREV');
		$headerCells = array();
		foreach($headerText as $header) {
			$headerCell = new stdClass();
			$headerCell->classes = array("centerHeader");
			$headerCell->colspan="";
			$headerCell->data = $header;
			$headerCells[] = $headerCell;
		}
		$this->htmlTable = new HTMLTable("tblBreakEven",  array("dtExportAndPDF vcReport"), $headerCells);

		// total sales
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_SALES'), false, array("cellAlignLeft")))->getHTML();
		$totalSales = 0;
		foreach($plan->getYears(false) as $year) {
			$sales = $forecast->getTotalSalesPerYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($sales, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML();
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getTotalSalesAllYears(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML();
		$this->htmlTable->addRow($row);

		// gross profit
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_CONTRIBUTION_GROSS_PROFIT'), false, array("cellAlignRight",)))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $forecast->getTotalSalesPerYear($year)-$expenses->getVariableExpensesByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$contributionByYear[$year] = $amount;
		}
		$totalContribution = $forecast->getTotalSalesAllYears() - $expenses->getTotalVariableExpenses();
		$row .= (new HTMLTableCell(Globals::numberFormat($totalContribution, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Net income before tax
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_NET_INCOME_BEFORE_TAXES'), false, array("cellAlignRight")))->getHTML();
		$totalNetIncomeBeforeTax = 0;
		$netIncomeBeforeTaxByYear = array();
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getOperatingProfitByYear($year) - $plan->getInterestPaidByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$netIncomeBeforeTaxByYear[$year] = $amount;
			$totalNetIncomeBeforeTax += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalNetIncomeBeforeTax, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);
		
		// Fixed Expenses + Interest
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_FIXED_EXPENSES_PLUS_INTEREST'), false, array("cellAlignRight")))->getHTML();
		$totalAmount = 0;
		$fixedExpensesPlusInterestByYear = array();
		foreach($plan->getYears(false) as $year) {
			$expense = $expenses->getFixedExpensesByYear($year);
			$interest = $plan->getInterestPaidByYear($year);
			$amount = $expense - $interest;
			$fixedExpensesPlusInterestByYear[$year] = $amount;
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$totalAmount += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalAmount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		
		// Break-even sales
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_BREAK_EVEN_SALES'), false, array("cellAlignLeft")))->getHTML();
		$totalBreakevenSales = 0;
		foreach($plan->getYears(false) as $year) {
			$amount = $forecast->getTotalSalesPerYear($year) - $netIncomeBeforeTaxByYear[$year] * ($forecast->getTotalSalesPerYear($year) / $contributionByYear[$year]);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
			$breakevenSalesByYear[$year] = $amount;
			$totalBreakevenSales += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalBreakevenSales, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Over/Under
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_OVER_UNDER_FROM_BREAK_EVEN_SALES'), false, array("cellAlignLeft", "grandTotal")))->getHTML();
		$totalOverUnder = 0;
		foreach($plan->getYears(false) as $year) {
			$amount = $forecast->getTotalSalesPerYear($year)- $breakevenSalesByYear[$year];
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "grandTotal")))->getHTML(); 
			$totalOverUnder += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalOverUnder, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "grandTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);
		
		// Blank row
		$row = (new HTMLTableCell("", false, ))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$row .= HTMLTableCell::getBlankCell();
		}
		$row .= HTMLTableCell::getBlankCell();
		$this->htmlTable->addRow($row);
		
		// Total Units
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_UNITS'), false, array("cellAlignLeft")))->getHTML();
		$totalAmount = 0;
		foreach($plan->getYears(false) as $year) {
			$amount	= $forecast->getTotalUnitsPerYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
			$totalAmount += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getTotalUnitsAllYears(), Globals::NUMBER_FORMAT_TYPE_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);
		
		// Contribution (Avg. $$/unit)
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CONTRIBUTION_AVG_PER_UNIT'), false, array("cellAlignLeft")))->getHTML();
		$contributionAvgDollarPerUnitByYear = array();
		foreach($plan->getYears(false) as $year) {
			$amount	= ($forecast->getTotalSalesPerYear($year)-$expenses->getVariableExpensesByYear($year))/$forecast->getTotalUnitsPerYear($year);
			$contributionAvgDollarPerUnitByYear [$year] = $amount;
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_PRICE_FRACTION_3, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
		}
		$totalAvgContribution = ($forecast->getTotalSalesAllYears()-$expenses->getTotalVariableExpenses())/$forecast->getTotalUnitsAllYears();
		$row .= (new HTMLTableCell(Globals::numberFormat($totalAvgContribution, Globals::NUMBER_FORMAT_TYPE_PRICE_FRACTION_3, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);
		
		// Break-Even (#units)
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_BREAK_EVEN_UNITS'), false, array("cellAlignLeft")))->getHTML();
		$totalAmount = 0;
		$breakevenUnitsByYear = array();
		foreach($plan->getYears(false) as $year) {
			$amount	= ($breakevenSalesByYear[$year]/$contributionAvgDollarPerUnitByYear[$year]) * ($contributionByYear[$year] /  $forecast->getTotalSalesPerYear($year));
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
			$breakevenUnitsByYear[$year] = $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat( ($totalBreakevenSales / $totalAvgContribution) * ( $totalContribution / $forecast->getTotalSalesAllYears()) , Globals::NUMBER_FORMAT_TYPE_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Over/Under
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_OVER_UNDER_FROM_BREAK_EVEN_UNITS'), false, array("cellAlignLeft", "grandTotal")))->getHTML();
		$totalOverUnder = 0;
		foreach($plan->getYears(false) as $year) {
			$amount	= $forecast->getTotalUnitsPerYear($year) - $breakevenUnitsByYear[$year];
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_INTEGER, true), false, array("cellAlignRight", "grandTotal")))->getHTML(); 
			$totalOverUnder += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalOverUnder, Globals::NUMBER_FORMAT_TYPE_INTEGER, true), false, array("cellAlignRight", "grandTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);
	}
}
	
?>