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

class BalanceSheetReport extends Report {

	public function __construct($userID=null) {
		parent::__construct($userID);

		$plan = $this->getPlan();
		$forecast = $plan->getForecast();
		$expenses = $plan->getExpenses();
		
		$headerText = array("");
		$headerText[] = Messages::getMessage("VC_MESSAGE_CURRENT");
		foreach($plan->getYears(false) as $year) {
				$headerText[] = $year;
		}
		$headerCells = array();
		foreach($headerText as $header) {
			$headerCell = new stdClass();
			$headerCell->classes = array("centerHeader");
			$headerCell->colspan="";
			$headerCell->data = $header;
			$headerCells[] = $headerCell;
		}
		$this->htmlTable = new HTMLTable("tblBalanceSheet",  array("dtExportAndPDF vcReport"), $headerCells, null);

		// ASSETS header
		$COGSBuffer = $plan->getInventoryAndCapitalPurchases()->getCOGSBuffer();

		$headerCell = new stdClass();
		$headerCell->classes = array("categoryName");
		$headerCell->colspan="";
		$headerCell->data = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_ASSETS'), false, array("cellAlignLeft", "categoryName")))->getHTML();
		$numExtraColumns = count($plan->getYears(false))+1;
		$this->htmlTable->addEmptyRowWithHeader($headerCell, $numExtraColumns);

		$index = 1;
		foreach($plan->getYears(false) as $year) {
			$totalCashInByYear[$year] = $plan->getPretaxIncomeByYear($year) +
							$expenses->getDepreciationByYear($year) +
							$plan->getInvestmentsByYear($year);
			$ltLoans = $plan->getLoansAndInvestments()->getLongTermLoans();
			foreach($ltLoans as $ltLoan) {
				$totalCashInByYear[$year] += $ltLoan->getYearEndClosingOrBalanceByYear($year);
			}
			$stLoans = $plan->getLoansAndInvestments()->getShortTermLoans();
			foreach($stLoans as $stLoan) {
				$totalCashInByYear[$year] += $stLoan->getYearEndClosingOrBalanceByYear($year);
			}
			$totalExpenditures = 0;
			foreach($plan->getInventoryAndCapitalPurchases()->getCapitalExpenditures() as $expenditure) {
				$totalExpenditures += $expenditure->getAmountByYear($year);
			}
			$changeInInventory =  $expenses->getCOGSByYear($year) * $COGSBuffer;
			$totalCashOutByYear[$year] = $totalExpenditures + $changeInInventory +
							$plan->getLoansAndInvestments()->getTotalLoanPaymentsByYear($year) +
							$plan->getLoansAndInvestments()->getTotalDrawsAndDividendsByYear($year);

			if ($index++ == 1) {
				$openingBalanceByYear[$year] = $plan->getLoansAndInvestments()->getCashOpeningBalance();
			}
			else {
				$openingBalanceByYear[$year] = $closingBalanceByYear[$year-1];
			}
			$closingBalanceByYear[$year] = $openingBalanceByYear[$year] + $totalCashInByYear[$year] - $totalCashOutByYear[$year];
		}

		$currentValueTotal = 0;
		// Cash Add Other Working Capital (aka closing balance)
		$index = 1;
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CASH_AND_OTHER_WORKING_CAPITAL'), false, array("cellAlignLeft")))->getHTML();
		$row .= (new HTMLTableCell(Globals::numberFormat($plan->getLoansAndInvestments()->getCashOpeningBalance(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$currentValueTotal += $plan->getLoansAndInvestments()->getCashOpeningBalance();
		$cashAndOtherWorkingCapital = array();
		foreach($plan->getYears(false) as $year) {
			$amount = $closingBalanceByYear[$year];
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$cashAndOtherWorkingCapital[$year] = $amount;
			if (!isset($totalAssetsByYear[$year])) {
				$totalAssetsByYear[$year] = 0;
			}
			$totalAssetsByYear[$year] += $amount;
		}
		$this->htmlTable ->addRow($row);
		
		// Capital Purchases
		$totalCapitalPurchases = 0;
		$totalCapitalPurchasesByYear = array();
		$accumulatedDepreciationByYear = array();
		foreach($plan->getInventoryAndCapitalPurchases()->getCapitalExpenditures() as $expenditure) {
			$row = (new HTMLTableCell($expenditure->getDescription(), false, array("cellAlignLeft")))->getHTML();
			$row .= (new HTMLTableCell(Globals::numberFormat($expenditure->getCurrentValue(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$currentValueTotal += $expenditure->getCurrentValue();
			foreach($plan->getYears(false) as $year) {
				$row .= (new HTMLTableCell(Globals::numberFormat($expenditure->getAssetAmountByYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
				if (!isset($totalCapitalPurchasesByYear[$year])) {
					$totalCapitalPurchasesByYear[$year] = 0;
				}
				$totalCapitalPurchasesByYear[$year] += $expenditure->getAssetAmountByYear($year);
				$totalAssetsByYear[$year] += $expenditure->getAssetAmountByYear($year);
				if (!isset($accumulatedDepreciationByYear[$year])) {
					$accumulatedDepreciationByYear[$year] = 0;
				}
				$accumulatedDepreciationByYear[$year] += ($expenditure->getAssetAmountByYear($year) * ($expenditure->getDepreciationRate()/100));
			}
			$this->htmlTable ->addRow($row);
		}

		// Inventory
		$initialInventory = $plan->getInventoryAndCapitalPurchases()->getStartingInventory();
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_INVENTORY'), false, array("cellAlignLeft")))->getHTML();
		$row .= (new HTMLTableCell(Globals::numberFormat($initialInventory, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$currentValueTotal += $initialInventory;
		$index = 1;
		foreach($plan->getYears(false) as $year) {
			if ($index++ == 1) {
				$inventoryByYear[$year]  = $initialInventory + ($expenses->getCOGSByYear($year) * $COGSBuffer);
			}
			else {
				$inventoryByYear[$year]  = $inventoryByYear[$year-1] + ($expenses->getCOGSByYear($year) * $COGSBuffer);
			}
			$totalAssetsByYear[$year] += $inventoryByYear[$year];
			$row .= (new HTMLTableCell(Globals::numberFormat($inventoryByYear[$year], Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		}
		$this->htmlTable ->addRow($row);
		
		// Accumulated Depreciation
		$startVal = $plan->getInventoryAndCapitalPurchases()->getAccumulatedDepreciation();
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_ACCUMULATED_DEPRECIATION'), false, array("cellAlignLeft")))->getHTML();
		$row .= (new HTMLTableCell(Globals::numberFormat(-$startVal, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$currentValueTotal += -$startVal;
		$index = 1;
		foreach($plan->getYears(false) as $year) {
			if ($index++ == 1) {
				$amount = -($accumulatedDepreciationByYear[$year] + $startVal);
				$accumulatedDepreciationByYear[$year] += $startVal;
			}
			else {
				$amount = -($accumulatedDepreciationByYear[$year-1] + $accumulatedDepreciationByYear[$year]);
				$accumulatedDepreciationByYear[$year] += $accumulatedDepreciationByYear[$year-1];
			}
			$totalAssetsByYear[$year] += $amount;
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		}
		$this->htmlTable ->addRow($row);

		// TOTAL ASSETS
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_ASSETS'), false, array("cellAlignLeft")))->getHTML();
		$row .= (new HTMLTableCell(Globals::numberFormat($currentValueTotal, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
		$currentTotalAssets = $currentValueTotal;
		foreach($plan->getYears(false) as $year) {
			$amount = $totalAssetsByYear[$year];
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
		}
		$this->htmlTable ->addRow($row);

		$this->htmlTable->addEmptyRow(count($plan->getYears(false))+2);

		// LIABILITIES header
		$headerCell = new stdClass();
		$headerCell->classes = array("categoryName");
		$headerCell->colspan="";
		$headerCell->data = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_LIABILITIES'), false, array("cellAlignLeft", "categoryName")))->getHTML();
		$numExtraColumns = count($plan->getYears(false))+1;
		$this->htmlTable->addEmptyRowWithHeader($headerCell, $numExtraColumns);
		
		$totalLiabilitiesByYear = array();
		
		// Long Term Loans
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_LONG_TERM_LOANS'), false, array("cellAlignLeft")))->getHTML();
		$index = 1;
		foreach($plan->getYears(false) as $year) {
			$amount = 0;
			foreach($plan->getLoansAndInvestments()->getLongTermLoans() as $ltLoan) {
				$amount += $ltLoan->getYearEndClosingOrBalanceByYear($year);
			}
			// the "Current" column is just the first year
			if ($index++ == 1) {
				$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			}
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			if (!isset($totalLiabilitiesByYear[$year])) {
				$totalLiabilitiesByYear[$year] = 0;
			}
			$totalLiabilitiesByYear[$year] += $amount;
		}
		$this->htmlTable ->addRow($row);
		
		// Short Term Loans
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_SHORT_TERM_LOANS'), false, array("cellAlignLeft")))->getHTML();
		$index = 1;
		foreach($plan->getYears(false) as $year) {
			$amount = 0;
			foreach($plan->getLoansAndInvestments()->getShortTermLoans() as $stLoan) {
				$amount += $stLoan->getYearEndClosingOrBalanceByYear($year);
			}
			// the "Current" column is just the first year
			if ($index++ == 1) {
				$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			}
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			if (!isset($totalLiabilitiesByYear[$year])) {
				$totalLiabilitiesByYear[$year] = 0;
			}
			$totalLiabilitiesByYear[$year] += $amount;
		}
		$this->htmlTable ->addRow($row);

		// TOTAL LIABILITIES
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_LIABILITIES'), false, array("cellAlignLeft")))->getHTML();
		$index = 1;
		foreach($plan->getYears(false) as $year) {
			$amount = $totalLiabilitiesByYear[$year];
			if ($index++ == 1) {
				$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
				$currentTotalLiabilities = $amount;
			}
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
		}
		$this->htmlTable ->addRow($row);

		// EQUITY header
		$headerCell = new stdClass();
		$headerCell->classes = array("categoryName");
		$headerCell->colspan="";
		$headerCell->data = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_EQUITY'), false, array("cellAlignLeft" , "categoryName")))->getHTML();
		$numExtraColumns = count($plan->getYears(false))+1;
		$this->htmlTable->addEmptyRowWithHeader($headerCell, $numExtraColumns);

		// Retained Earnings (first slot is carryover loss, after that it's year's pretax income + previous year's retained earnings - year's total draw and dividends
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_RETAINED_EARNINGS'), false, array("cellAlignLeft")))->getHTML();
		$currentRetainedEarnings = -$expenses->getCarryoverLoss();
		$row .= (new HTMLTableCell(Globals::numberFormat($currentRetainedEarnings, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$index = 1;
		$retainedEarningsByYear = array();
		foreach($plan->getYears(false) as $year) {
			if ($index++ == 1) {
				$amount = $plan->getPretaxIncomeByYear($year) +$currentRetainedEarnings - $plan->getLoansAndInvestments()->getTotalDrawsAndDividendsByYear($year);
			}
			else {
				$amount = $plan->getPretaxIncomeByYear($year) + $retainedEarningsByYear[$year-1]  - $plan->getLoansAndInvestments()->getTotalDrawsAndDividendsByYear($year);
			}
			$retainedEarningsByYear[$year] = $amount;
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		}
		$this->htmlTable ->addRow($row);
		
		// Total Equity
		$totalEquityByYear = array();
		$totalEquityRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_EQUITY'), false, array("cellAlignLeft")))->getHTML();
		$currentTotalEquity = $currentTotalAssets - $currentTotalLiabilities;
		$totalEquityRow .= (new HTMLTableCell(Globals::numberFormat($currentTotalEquity, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
		$totalEquity = 0;
		foreach($plan->getYears(false) as $year) {
			$amount = $totalAssetsByYear[$year] - $totalLiabilitiesByYear[$year];
			$totalEquityRow .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "subTotal")))->getHTML(); 
			$totalEquityByYear[$year] = $amount;
			$totalEquity += $amount;
		}

		// Shareholder & Investor Equity
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_SHAREHOLDER_AND_INVESTOR_EQUITY'), false, array("cellAlignLeft")))->getHTML();
		$amount = $currentTotalEquity - $currentRetainedEarnings;
		$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$total = 0;
		foreach($plan->getYears(false) as $year) {
			$amount = $totalEquityByYear[$year] - $retainedEarningsByYear[$year];
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$total += $amount;
		}
		$this->htmlTable ->addRow($row);

		$this->htmlTable ->addRow($totalEquityRow);
		
		// Total Equity & Liabilities
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_LIABILITIES_AND_EQUITY'), false, array("cellAlignLeft", "grandTotal")))->getHTML();
		$amount = $currentTotalLiabilities + $currentTotalEquity;
		$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "grandTotal")))->getHTML(); 
		foreach($plan->getYears(false) as $year) {
			$amount = $totalLiabilitiesByYear[$year] + $totalEquityByYear[$year];
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight", "grandTotal")))->getHTML(); 
		}
		$this->htmlTable->addEmptyRow(count($plan->getYears(false))+2);

		$this->htmlTable ->addRow($row);
	}

}
	
?>

