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

class IncomeProjectionReport extends Report {

	private $forecast;
	private $totalsByYear;
	
	public function __construct($userID=null) {
		parent::__construct($userID);

		$plan = $this->getPlan();
		$forecast = $plan->getForecast();
		$this->forecast = $forecast;
		
		$headerText = array("");
		foreach($plan->getYears(false) as $year) {
			$headerText[] = $year;
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
		$this->htmlTable = new HTMLTable("tblIncomeProjection",  array("dtExportAndPDF vcReport"), $headerCells, null);

		$this->totalsByYear[] = array(); 
		foreach($plan->getChannels() as $channel) {
			$channelID = $channel->getID();
			$channelName = $channel->getName();
			$row = (new HTMLTableCell($channelName, false, array("cellAlignLeft")))->getHTML();
			
			foreach($plan->getYears(false) as $year) {
				$salesObj = $forecast->getChannelTotalsByYear($channelID, $year);
				$row .= (new HTMLTableCell(Globals::numberFormat($salesObj->getSales(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			}
			
			$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getChannelTotalSalesByPlanLength($channelID), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
			$percentOfSales =  ($forecast->getChannelTotalSalesByPlanLength($channelID) / $forecast->getTotalSalesAllYears())*100;
			$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML(); 
			$this->htmlTable->addRow($row);
		}
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_SALES'), false, array("cellAlignLeft")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getTotalSalesPerYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getTotalSalesAllYears(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
		$row .= (new HTMLTableCell(Globals::numberFormat("100.0", Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML(); 
		$this->htmlTable->addRow($row);
		
		// Expenses
		$expenses = $plan->getExpenses();
		
		// Direct Expenses Header
		$this->_addHeaderRow(Messages::getMessage('VC_MESSAGE_DIRECT_EXPENSES'));
		
		// Manufacturing
		$manufacturingExpenses = $expenses->getManufacturingExpenses();
		$this->_outputExpenseType($manufacturingExpenses, Messages::getMessage('VC_MESSAGE_MANUFACTURING_EXPENSES'));
		
		// Selling
		$sellingExpenses = $expenses->getSellingExpenses();
		$this->_outputExpenseType($sellingExpenses, Messages::getMessage('VC_MESSAGE_SELLING_EXPENSES'));


		// COGS
		$row0 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS'), false, array("cellAlignLeft")))->getHTML();
		$totalCostAcrossPlanLength = 0;
		foreach($plan->getYears(false) as $year) {
			$row0 .= (new HTMLTableCell(Globals::numberFormat($expenses->getCOGSByYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true),  false, array("cellAlignRight", "categoryTotal")))->getHTML();
		}
		$row0 .= (new HTMLTableCell(Globals::numberFormat($expenses->getTotalCOGS(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
		$percentOfSales = ($expenses->getTotalCOGS() / $forecast->getTotalSalesAllYears())*100;
		$row0 .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML();
		
		$row1 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS_INGREDIENTS'), false, array("cellAlignLeft", "subIndent")))->getHTML();
		$row2 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS_LABOUR'), false, array("cellAlignLeft", "subIndent")))->getHTML();
		$row3 = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_COGS_PACKAGING'), false, array("cellAlignLeft", "subIndent")))->getHTML();
		$totalIngredientsCost = 0;
		$totalLabourCost = 0;
		$totalPackagingCost = 0;
		foreach($plan->getYears(false) as $year) {
			$row1 .= (new HTMLTableCell(Globals::numberFormat($expenses->getTotalIngredientsCostByYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$row2 .= (new HTMLTableCell(Globals::numberFormat($expenses->getTotalLabourCostByYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$row3 .= (new HTMLTableCell(Globals::numberFormat($expenses->getTotalPackagingCostByYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
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
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_VARIABLE_EXPENSES'), false, array("cellAlignLeft",)))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$row .= (new HTMLTableCell(Globals::numberFormat($expenses->getVariableExpensesByYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal", "boldHeader")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($expenses->getTotalVariableExpenses(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal", "boldHeader")))->getHTML(); 
		$percentOfSales = ($expenses->getTotalVariableExpenses() / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "boldHeader")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_GROSS_PROFIT'), false, array("cellAlignLeft",)))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getTotalSalesPerYear($year)-$expenses->getVariableExpensesByYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "grandTotal")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($forecast->getTotalSalesAllYears()-$expenses->getTotalVariableExpenses(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "grandTotal")))->getHTML(); 
		$percentOfSales = (($forecast->getTotalSalesAllYears()-$expenses->getTotalVariableExpenses()) / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);
		
		$this->_addHeaderRow(Messages::getMessage('VC_MESSAGE_OPERATING_EXPENSES'));

		$this->_outputOperatingExpense($expenses->getFacilityExpenses(), Messages::getMessage('VC_MESSAGE_FACILITY_EXPENSES'));
		$this->_outputOperatingExpense($expenses->getSalaryAndBenefitExpenses(), null);
		$this->_outputOperatingExpense($expenses->getMarketingAndPromotionExpenses(), null);
		$this->_outputOperatingExpense($expenses->getFoodSafetyExpenses(), null);
		$this->_outputOperatingExpense($expenses->getAdministrativeExpenses(), Messages::getMessage('VC_MESSAGE_ADMINISTRATIVE_EXPENSES'));
		$this->_outputOperatingExpense($expenses->getStaffTravelExpenses(), null);
		
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CONTINGENCY_UNPLANNED_EXPENSES'), false, array("cellAlignLeft",)))->getHTML();
		$totalContingencyUnplannedExpenses = 0;
		foreach($plan->getYears(false) as $year) {
			$expense = $expenses->getContingencyUnplannedByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($expense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
			$totalContingencyUnplannedExpenses += $expense;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalContingencyUnplannedExpenses, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
		$percentOfSales = ($totalContingencyUnplannedExpenses / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// total fixed expenses
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_FIXED_EXPENSES'), false, array("cellAlignLeft", "boldHeader")))->getHTML();
		$totalFixedExpenses = 0;
		foreach($plan->getYears(false) as $year) {
			$expense = $expenses->getFixedExpensesByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($expense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
			$totalFixedExpenses += $expense;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalFixedExpenses, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
		$percentOfSales = ($totalFixedExpenses / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);
		
		// Operating Profit
		//  Total Sales (that year) - Total Variable Expense (that year) - total Fixed Expense (that year)
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_OPERATING_PROFIT'), false, array("cellAlignLeft", "boldHeader", "grandTotal")))->getHTML();
		$totalProfit = 0;
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getOperatingProfitByYear($year); //$forecast->getTotalSalesPerYear($year) - $expenses->getVariableExpensesByYear($year) - $expenses->getFixedExpensesByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($plan->getTotalOperatingProfit(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
		$percentOfSales = ($totalProfit / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);
		
		// Interest Expense
		//  Total Sales (that year) - Total Variable Expense (that year) - total Fixed Expense (that year)
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_INTEREST_EXPENSE'), false, array("cellAlignLeft")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getInterestPaidByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($plan->getTotalInterestPaid(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$percentOfSales = ($plan->getTotalInterestPaid() / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Depreciation Expense
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_DEPRECIATION_EXPENSE'), false, array("cellAlignLeft")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $expenses->getDepreciationByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($expenses->getTotalDepreciation(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$percentOfSales = ($expenses->getTotalDepreciation() / $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Pre Tax Income
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_PRE_TAX_INCOME'), false, array("cellAlignLeft", "boldHeader", "grandTotal")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getPretaxIncomeByYear($year); 
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($plan->getTotalPretaxIncome(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
		$percentOfSales = ($plan->getTotalPretaxIncome()/ $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Taxes payable
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TAXES_PAYABLE'), false, array("cellAlignLeft")))->getHTML();
		$totalProfit = 0;
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getTaxesPayableByYear($year); 
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($plan->getTotalTaxesPayable(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$percentOfSales = ($plan->getTotalTaxesPayable()/ $forecast->getTotalSalesAllYears())*100;
		$row .= (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Net Profit After Taxes
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_NET_PROFIT_AFTER_TAXES'), false, array("cellAlignLeft", "boldHeader", "grandTotal")))->getHTML();
		$totalProfit = 0;
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getNetProfitAfterTaxesByYear($year); 
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($plan->getTotalNetProfitAfterTaxes(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "boldHeader", "grandTotal")))->getHTML(); 
		$percentOfSales = ($plan->getTotalNetProfitAfterTaxes()/ $forecast->getTotalSalesAllYears())*100;
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
			}
			$row .= (new HTMLTableCell(Globals::numberFormat($totalExpense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
			$percentOfSales = $singleExpense->getPercentOfSales();
			$totalPercentOfSales += $percentOfSales;
			$row .=  (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			$expenseRows[] = $row;
		}
		// row for the totals
		$totalRow = (new HTMLTableCell($topRowHeader, false, array("cellAlignLeft")))->getHTML();
		$totalTotal = 0;
		foreach($plan->getYears(false) as $year) {
			$total = $totalExpensesByYear[$year];
			$totalTotal += $total;
			$totalRow .= (new HTMLTableCell(Globals::numberFormat($total , Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
		}
		$totalRow .= (new HTMLTableCell(Globals::numberFormat($totalTotal , Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
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
		
		// if it's a single expense, it's using monthly benchmarks
		if (null == $topRowHeader || !is_array($expenses)) {
			$singleExpense = $expenses;
			$expenseDesc = $singleExpense->getDescription();
			$row = (new HTMLTableCell($expenseDesc, false, array("cellAlignLeft")))->getHTML();
			$totalExpense = 0;
			foreach($plan->getYears(false) as $year) {
				$expense = $singleExpense->getYearlyExpenseByYear($year);
				$totalExpense += $expense;
				$row .= (new HTMLTableCell(Globals::numberFormat($expense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
			}
			$row .= (new HTMLTableCell(Globals::numberFormat($totalExpense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML(); 
			$percentOfSales = ($totalExpense / $this->forecast->getTotalSalesAllYears()) * 100;
			$row .=  (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
			$expenseRows[] = $row;
		}
		else { 
			foreach($expenses as $singleExpense) {
				$expenseDesc = $singleExpense->getDescription();
				$row = (new HTMLTableCell($expenseDesc, false, array("cellAlignRight")))->getHTML();
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
				}
				$row .= (new HTMLTableCell(Globals::numberFormat($totalExpense, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML(); 
				$percentOfSales = ($totalExpense / $this->forecast->getTotalSalesAllYears()) * 100;
				$totalPercentOfSales += $percentOfSales;
				$row .=  (new HTMLTableCell(Globals::numberFormat($percentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true)))->getHTML();
				$expenseRows[] = $row; 
			}
			// row for the totals
			$totalRow = (new HTMLTableCell($topRowHeader, false, array("cellAlignLeft")))->getHTML();
			$totalTotal = 0;
			foreach($plan->getYears(false) as $year) {
				$total = $totalExpensesByYear[$year];
				$totalTotal += $total;
				$totalRow .= (new HTMLTableCell(Globals::numberFormat($total , Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
			}
			$totalRow .= (new HTMLTableCell(Globals::numberFormat($totalTotal , Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "categoryTotal")))->getHTML();
			$totalRow .= (new HTMLTableCell(Globals::numberFormat($totalPercentOfSales, Globals::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL, true), false, array("cellAlignRight")))->getHTML();
			$this->htmlTable->addRow($totalRow);
		}
		foreach($expenseRows as $expenseRow) {
			$this->htmlTable->addRow($expenseRow);
		}
	}

	private function _addHeaderRow($text) {
		$plan = $this->getPlan();
		$row = (new HTMLTableCell($text, false, array("cellAlignLeft", "categoryName")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$row .= HTMLTableCell::getBlankCell(array("categoryName"));
		}
		$row .= HTMLTableCell::getBlankCell(array("categoryName"));
		$row .= HTMLTableCell::getBlankCell(array("categoryName"));
		$this->htmlTable->addRow($row);
	}
	
}
	
?>