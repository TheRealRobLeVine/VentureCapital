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

class PLProjectionReport extends Report {

	private $forecast;
	private $totalsByYear;
	
	public function __construct($userID=null) {
		parent::__construct($userID);

		$plan = $this->getPlan();
		$forecast = $plan->getForecast();
		$this->forecast = $forecast;
		
		$headerText = array("", "");
		foreach($plan->getYears(false) as $year) {
			$headerText[] = $year;
			$headerText[] = Messages::getMessage('VC_MESSAGE_PERCENT_OF_SALES_ABBREV');
		}
		$headerText[] = $plan->getLength() . Messages::getMessage('VC_MESSAGE_YEAR_TOTALS_ABBREV');
		$headerText[] = Messages::getMessage('VC_MESSAGE_PERCENT_OF_SALES_ABBREV');
		$headerCells = array();
		foreach($headerText as $header) {
			$headerCell = new stdClass();
			$headerCell->classes = array("centerHeader");
			$headerCell->colspan="";
			$headerCell->data = $header;
			$headerCells[] = $headerCell;
		}
		$this->htmlTable = new HTMLTable("tbPLProjection",  array("dtExportAndPDF vcReport"), $headerCells);

		
		$this->totalsByYear[] = array(); 
		foreach($plan->getChannels() as $channel) {
			$channelID = $channel->getID();
			$channelName = $channel->getName();
			$row = (new HTMLTableCell($channelName, false, array("cellAlignLeft")))->getHTML();
			$row .= HTMLTableCell::getBlankCell(array(""));
			
			foreach($plan->getYears(false) as $year) {
				$salesObj = $forecast->getChannelTotalsByYear($channelID, $year);
				$amount = $salesObj->getSales();
				$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
				$percentOfSales =  ($amount /$forecast->getTotalSalesPerYear($year))*100;
				$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			}
			
			$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getChannelTotalSalesByPlanLength($channelID), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
			$percentOfSales =  ($forecast->getChannelTotalSalesByPlanLength($channelID) / $forecast->getTotalSalesAllYears())*100;
			$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML(); 
			$this->htmlTable->addRow($row);
		}
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_SALES'), false, array("cellAlignLeft")))->getHTML();
		$row .= HTMLTableCell::getBlankCell(array(""));

		foreach($plan->getYears(false) as $year) {
			$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getTotalSalesPerYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
			$percentOfSales = ($forecast->getTotalSalesPerYear($year) / $forecast->getTotalSalesAllYears())*100;
			$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getTotalSalesAllYears(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
		$row .= (new HTMLTableCell(Globals::numberFormat("100.0", Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML(); 
		$this->htmlTable->addRow($row);
		
		// Expenses
		$expenses = $plan->getExpenses();
		
		// Direct Expenses Header
		$this->_addHeaderRow(Messages::getMessage('VC_MESSAGE_DIRECT_EXPENSES'), Messages::getMessage('VC_MESSAGE_PERCENT_OF_SALES_ABBREV'));
		
		// Manufacturing
		$manufacturingExpenses = $expenses->getManufacturingExpenses();
		$this->_outputExpenseType($manufacturingExpenses, Messages::getMessage('VC_MESSAGE_MANUFACTURING_EXPENSES'));
		
		// Selling
		$sellingExpenses = $expenses->getSellingExpenses();
		$this->_outputExpenseType($sellingExpenses, Messages::getMessage('VC_MESSAGE_SELLING_EXPENSES'));

		// COGS
		$row0 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS'), false, array("cellAlignLeft")))->getHTML();
		$row0 .= HTMLTableCell::getBlankCell(array(""));
		
		$totalCostAcrossPlanLength = 0;
		foreach($plan->getYears(false) as $year) {
			$amount = $expenses->getCOGSByYear($year);
			$row0 .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true),  false, array("cellAlignRight")))->getHTML();
			$percentOfSales = (($amount /  $forecast->getTotalSalesPerYear($year))*100);
			$row0 .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true),  false, array("cellAlignRight")))->getHTML();
		}
		$row0 .= (new HTMLTableCell(Globals::numberFormat($expenses->getTotalCOGS(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML();
		$percentOfSales = ($expenses->getTotalCOGS() / $forecast->getTotalSalesAllYears())*100;
		$row0 .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML();
		
		$row1 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS_INGREDIENTS'), false, array("cellAlignLeft", "subIndent")))->getHTML();
		$row1 .= HTMLTableCell::getBlankCell(array(""));
		$row2 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS_LABOUR'), false, array("cellAlignLeft", "subIndent")))->getHTML();
		$row2 .= HTMLTableCell::getBlankCell(array(""));
		$row3 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS_PACKAGING'), false, array("cellAlignLeft", "subIndent")))->getHTML();
		$row3 .= HTMLTableCell::getBlankCell(array(""));
		$totalIngredientsCost = 0;
		$totalLabourCost = 0;

		$totalPackagingCost = 0;
		foreach($plan->getYears(false) as $year) {
			$expenses->getTotalIngredientsCostByYear($year);
			$row1 .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$percentOfSales = (($amount /  $forecast->getTotalSalesPerYear($year))*100);
			$row1 .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true),  false, array("cellAlignRight")))->getHTML();
			
			$amount = $expenses->getTotalLabourCostByYear($year);
			$percentOfSales = (($amount /  $forecast->getTotalSalesPerYear($year))*100);
			$row2 .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$row2 .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true),  false, array("cellAlignRight")))->getHTML();
			
			$amount = $expenses->getTotalPackagingCostByYear($year);
			$percentOfSales = (($amount /  $forecast->getTotalSalesPerYear($year))*100);
			$row3 .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$row3 .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true),  false, array("cellAlignRight")))->getHTML();

			$totalIngredientsCost += $expenses->getTotalIngredientsCostByYear($year);
			$totalLabourCost += $expenses->getTotalLabourCostByYear($year);
			$totalPackagingCost += $expenses->getTotalPackagingCostByYear($year);
		}
		$row1 .= (new HTMLTableCell(Globals::numberFormat($totalIngredientsCost, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, false)))->getHTML();
		$percentOfSales = ($totalIngredientsCost / $forecast->getTotalSalesAllYears())*100;
		$row1 .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false))->getHTML();
		$row2 .= (new HTMLTableCell(Globals::numberFormat($totalLabourCost, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
		$percentOfSales = ($totalLabourCost / $forecast->getTotalSalesAllYears())*100;
		$row2 .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false))->getHTML();
		$row3 .= (new HTMLTableCell(Globals::numberFormat($totalPackagingCost, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
		$percentOfSales = ($totalPackagingCost / $forecast->getTotalSalesAllYears())*100;
		$row3 .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false))->getHTML();

		$this->htmlTable ->addRow($row0);
		$this->htmlTable ->addRow($row1);
		$this->htmlTable ->addRow($row2);
		$this->htmlTable ->addRow($row3);
		
		// Total Variable expenses
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_VARIABLE_EXPENSES'), false, array("cellAlignLeft","boldHeader")))->getHTML();
		$row .= HTMLTableCell::getBlankCell(array(""));
		foreach($plan->getYears(false) as $year) {
			$amount = $expenses->getVariableExpensesByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal", "boldHeader")))->getHTML(); 
			$percentOfSales = ($amount / $forecast->getTotalSalesPerYear($year))*100;
			$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "subTotal","boldHeader")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($expenses->getTotalVariableExpenses(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal", "boldHeader")))->getHTML(); 
		$percentOfSales = ($expenses->getTotalVariableExpenses() / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "subTotal", "boldHeader")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Operating Expenses
		$this->_addHeaderRow(Messages::getMessage('VC_MESSAGE_OPERATING_EXPENSES'), Messages::getMessage('VC_MESSAGE_MONTHLY_BENCHMARK'));

		$totalBenchmark = 0;
		$totalBenchmark += $this->_outputOperatingExpense($expenses->getFacilityExpenses(), Messages::getMessage('VC_MESSAGE_FACILITY_EXPENSES'));
		$totalBenchmark += $this->_outputOperatingExpense($expenses->getSalaryAndBenefitExpenses(), null);
		$totalBenchmark += $this->_outputOperatingExpense($expenses->getMarketingAndPromotionExpenses(), null);
		$totalBenchmark += $this->_outputOperatingExpense($expenses->getFoodSafetyExpenses(), null);
		$totalBenchmark += $this->_outputOperatingExpense($expenses->getAdministrativeExpenses(), Messages::getMessage('VC_MESSAGE_ADMINISTRATIVE_EXPENSES'));
		$totalBenchmark += $this->_outputOperatingExpense($expenses->getStaffTravelExpenses(), null);
		
		// Contingency/Unplanned
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CONTINGENCY_UNPLANNED_EXPENSES'), false, array("cellAlignLeft",)))->getHTML();
		$row .= (new HTMLTableCell(Globals::numberFormat($expenses->getContingencyUnplanned(), Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
		$totalContingencyUnplannedExpenses = 0;
		foreach($plan->getYears(false) as $year) {
			$expense = $expenses->getContingencyUnplannedByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($expense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
			$percentOfSales = ($expense / $forecast->getTotalSalesPerYear($year))*100;
			$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
			$totalContingencyUnplannedExpenses += $expense;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalContingencyUnplannedExpenses, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
		$percentOfSales = ($totalContingencyUnplannedExpenses / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// total fixed expenses
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_FIXED_EXPENSES'), false, array("cellAlignLeft", "boldHeader")))->getHTML();
		$benchmarksPlusContingency = $totalBenchmark * (1 + ($expenses->getContingencyUnplanned()/100));
		$row .= (new HTMLTableCell(Globals::numberFormat($benchmarksPlusContingency, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
		
		$totalFixedExpenses = 0;
		foreach($plan->getYears(false) as $year) {
			$expense = $expenses->getFixedExpensesByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($expense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
			$percentOfSales = ($expense / $forecast->getTotalSalesPerYear($year))*100;
			$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
			$totalFixedExpenses += $expense;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalFixedExpenses, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
		$percentOfSales = ($totalFixedExpenses / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);
		
		// Operating Profit
		//  Total Sales (that year) - Total Variable Expense (that year) - total Fixed Expense (that year)
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_OPERATING_PROFIT'), false, array("cellAlignLeft", "boldHeader", "grandTotal")))->getHTML();
		$row .= HTMLTableCell::getBlankCell(array(""));
		$totalProfit = 0;
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getOperatingProfitByYear($year); 
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
			$percentOfSales = ($amount / $forecast->getTotalSalesPerYear($year))*100;
			$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "grandTotal")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($plan->getTotalOperatingProfit(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
		$percentOfSales = ($plan->getTotalOperatingProfit() / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);
		
	}

	private function _outputExpenseType($expenses, $topRowHeader) {
		$plan = $this->getPlan();
		$forecast = $plan->getForecast();
		$expenseRows = array();
		$totalPercentOfSales = 0;
		
		foreach($expenses as $singleExpense) {
			$expenseDesc = $singleExpense->getDescription();
			$row = (new HTMLTableCell($expenseDesc, false, array("cellAlignLeft")))->getHTML();
			$row .= (new HTMLTableCell(Globals::numberFormat($singleExpense->getPercentOfSales(), Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			$totalExpense = 0;
			foreach($plan->getYears(false) as $year) {
				$expense = ($forecast->getTotalSalesPerYear($year)* $singleExpense->getPercentOfSales()) / 100;
				$totalExpense += $expense;
				if (!isset($totalExpensesByYear[$year])) {
					$totalExpensesByYear[$year] = $expense;
				}
				else {
					$totalExpensesByYear[$year] += $expense;
				}
				$row .= (new HTMLTableCell(Globals::numberFormat($expense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
				$row .=  (new HTMLTableCell(Globals::numberFormat($singleExpense->getPercentOfSales(), Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			}
			$row .= (new HTMLTableCell(Globals::numberFormat($totalExpense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
			$percentOfSales = $singleExpense->getPercentOfSales();
			$totalPercentOfSales += $percentOfSales;
			$row .=  (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			$expenseRows[] = $row;
		}

		// row for the totals
		$totalRow = (new HTMLTableCell($topRowHeader, false, array("cellAlignLeft")))->getHTML();
		$totalRow .= (new HTMLTableCell(Globals::numberFormat($totalPercentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML();
		$totalTotal = 0;
		foreach($plan->getYears(false) as $year) {
			$total = $totalExpensesByYear[$year];
			$totalTotal += $total;
			$totalRow .= (new HTMLTableCell(Globals::numberFormat($total , Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML();
			$totalRow .=  (new HTMLTableCell(Globals::numberFormat($totalPercentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
		}
		$totalRow .= (new HTMLTableCell(Globals::numberFormat($totalTotal , Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML();
		$totalRow .= (new HTMLTableCell(Globals::numberFormat($totalPercentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML();
		$this->htmlTable->addRow($totalRow);
		foreach($expenseRows as $expenseRow) {
			$this->htmlTable->addRow($expenseRow);
		}
		
    }

	private function _outputOperatingExpense($expenses, $topRowHeader) {
		$plan = $this->getPlan();
		$forecast = $plan->getForecast();
		$expenseRows = array();
		$totalPercentOfSales = 0;
		$totalBenchmark = 0;
		
		// if it's a single expense, it's using monthly benchmarks
		if (null == $topRowHeader || !is_array($expenses)) {
			$singleExpense = $expenses;
			$expenseDesc = $singleExpense->getDescription();
			$row = (new HTMLTableCell($expenseDesc, false, array("cellAlignLeft")))->getHTML();
			$benchmark = $singleExpense->getMonthlyBenchmark();
			$totalBenchmark += $benchmark;
			$row .= (new HTMLTableCell(Globals::numberFormat($benchmark, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$totalExpense = 0;
			foreach($plan->getYears(false) as $year) {
				$expense = $singleExpense->getYearlyExpenseByYear($year);
				$totalExpense += $expense;
				$row .= (new HTMLTableCell(Globals::numberFormat($expense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
				$percentOfSales = (($expense /  $forecast->getTotalSalesPerYear($year))*100);
				$row .=  (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			}
			$row .= (new HTMLTableCell(Globals::numberFormat($totalExpense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$percentOfSales = ($totalExpense / $this->forecast->getTotalSalesAllYears()) * 100;
			$row .=  (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			$expenseRows[] = $row;
		}
		else { 
			foreach($expenses as $singleExpense) {
				$expenseDesc = $singleExpense->getDescription();
				$row = (new HTMLTableCell($expenseDesc, false, array("cellAlignRight")))->getHTML();
				$benchmark = $singleExpense->getMonthlyBenchmark();
				$totalBenchmark += $benchmark;
				$row .= (new HTMLTableCell(Globals::numberFormat($benchmark, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
				$totalExpense = 0;
				foreach($plan->getYears(false) as $year) {
					$expense = $singleExpense->getYearlyExpenseByYear($year);
					$totalExpense += $expense;
					if (!isset($totalExpensesByYear[$year])) {
						$totalExpensesByYear[$year] = $expense;
					}
					else {
						$totalExpensesByYear[$year] += $expense;
					}
					$row .= (new HTMLTableCell(Globals::numberFormat($expense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
					$percentOfSales = (($expense /  $forecast->getTotalSalesPerYear($year))*100);
					$row .=  (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
				}
				$row .= (new HTMLTableCell(Globals::numberFormat($totalExpense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
				$percentOfSales = ($totalExpense / $this->forecast->getTotalSalesAllYears()) * 100;
				$totalPercentOfSales += $percentOfSales;
				$row .=  (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
				$expenseRows[] = $row; 
			}
			// row for the totals
			$totalRow = (new HTMLTableCell($topRowHeader, false, array("cellAlignLeft")))->getHTML();
			$totalRow .= (new HTMLTableCell(Globals::numberFormat($totalBenchmark , Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
			$totalTotal = 0;
			foreach($plan->getYears(false) as $year) {
				$total = $totalExpensesByYear[$year];
				$totalTotal += $total;
				$totalRow .= (new HTMLTableCell(Globals::numberFormat($total , Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
				$percentOfSales = (($total /  $forecast->getTotalSalesPerYear($year))*100);
				$totalRow .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
			}
			$totalRow .= (new HTMLTableCell(Globals::numberFormat($totalTotal , Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
			$totalRow .= (new HTMLTableCell(Globals::numberFormat($totalPercentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
			$this->htmlTable->addRow($totalRow);
		}
		foreach($expenseRows as $expenseRow) {
			$this->htmlTable->addRow($expenseRow);
		}
		
		return $totalBenchmark;
	}

	private function _addHeaderRow($text, $secondColumnText) {
		$plan = $this->getPlan();
		$row = (new HTMLTableCell($text, false, array("cellAlignLeft", "categoryName")))->getHTML();
		$row .= (new HTMLTableCell($secondColumnText, false, array("cellAlignLeft", "categoryName")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$row .= HTMLTableCell::getBlankCell(array("categoryName"));
			$row .= HTMLTableCell::getBlankCell(array("categoryName"));
		}
		$row .= HTMLTableCell::getBlankCell(array("categoryName"));
		$row .= HTMLTableCell::getBlankCell(array("categoryName"));
		$this->htmlTable->addRow($row);
	}
	
}
	
?>