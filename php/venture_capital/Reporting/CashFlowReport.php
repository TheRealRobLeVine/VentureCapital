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

class CashFlowReport extends Report {

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
		$this->htmlTable = new HTMLTable("tblCashFlow",  array("dtExportAndPDF vcReport"), $headerCells);

		$totalCashInByYear = array();
		$totalCashIn = 0;
		
		// CASH IN
		$headerCell = new stdClass();
		$headerCell->classes = array("categoryName");
		$headerCell->colspan="";
		$headerCell->data = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CASH_IN'), false, array("cellAlignLeft" , "categoryName")))->getHTML();
		$numExtraColumns = count($plan->getYears(false))+1;
		$this->htmlTable->addEmptyRowWithHeader($headerCell, $numExtraColumns);

		// Net Income from Operations (aka PreTax Income);
		$totalCashInByYear = array();

		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_NET_INCOME_FROM_OPERATIONS'), false, array("cellAlignLeft")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getPretaxIncomeByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
			$totalCashInByYear[$year] = $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($plan->getTotalPretaxIncome(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true)))->getHTML();
		$this->htmlTable->addRow($row);

		// depreciation
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_DEPRECIATION_EXPENSE_LONG'), false, array("cellAlignLeft")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $expenses->getDepreciationByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$totalCashInByYear[$year] += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($expenses->getTotalDepreciation(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		$totalAdditions = array();
		// Cash Additions, Loans & Funding
		$eiRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_EQUITY_INVESTMENTS'), false, array("cellAlignRight")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getInvestmentsByYear($year);
			$eiRow .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			if (!isset($totalAdditions[$year])) {
				$totalAdditions[$year] = 0;
			}
			$totalAdditions[$year] += $amount;
		}
		$eiRow .= (new HTMLTableCell(Globals::numberFormat($plan->getTotalInvestments(), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 

		// long term loans
		$ltlRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_LONG_TERM_LOANS'), false, array("cellAlignRight")))->getHTML();
		$totalLTContributions = 0;
		foreach($plan->getYears(false) as $year) {
			$ltLoans = $plan->getLoansAndInvestments()->getLongTermLoans();
			foreach($ltLoans as $ltLoan) {
				if (!isset($longTermLoanClosingBalancesByYear[$year])) {
					$longTermLoanClosingBalancesByYear[$year] = 0;
				}
				$longTermLoanClosingBalancesByYear[$year] += $ltLoan->getYearEndClosingOrBalanceByYear($year);
			}
			$amount = $longTermLoanClosingBalancesByYear[$year];
			$ltlRow .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$totalLTContributions += $amount;
			if (!isset($totalAdditions[$year])) {
				$totalAdditions[$year] = 0;
			}
			$totalAdditions[$year] += $amount;
		}
		$ltlRow .= (new HTMLTableCell("", false, array("cellAlignRight")))->getHTML(); 

		// short term loans
		$stlRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_SHORT_TERM_LOANS'), false, array("cellAlignRight")))->getHTML();
		$totalSTContributions = 0;
		foreach($plan->getYears(false) as $year) {
			$stLoans = $plan->getLoansAndInvestments()->getShortTermLoans();
			foreach($stLoans as $stLoan) {
/*				if (!isset($shortTermLoanClosingBalancesByYear[$year])) {
					$shortTermLoanClosingBalancesByYear[$year] = 0;
				}
				$shortTermLoanClosingBalancesByYear[$year] += $stLoan->getYearEndClosingOrBalanceByYear($year);*/
				if (!isset($shortTermLoanAdditionsByYear[$year])) {
					$shortTermLoanAdditionsByYear[$year] = 0;
				}
				$shortTermLoanAdditionsByYear[$year] += $stLoan->getAdditionsByYear($year);
			}
			$amount = $shortTermLoanAdditionsByYear[$year];
			$stlRow .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$totalSTContributions += $amount;
			if (!isset($totalAdditions[$year])) {
				$totalAdditions[$year] = 0;
			}
			$totalAdditions[$year] += $amount;
		}
		$stlRow .= (new HTMLTableCell("", false, array("cellAlignRight")))->getHTML(); 
		
		// Total Cash Additions
		$totalRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CASH_ADDITIONS_LOANS_FUNDING'), false, array("cellAlignLeft")))->getHTML();
		$planLengthTotal = 0;
		foreach($plan->getYears(false) as $year) {
			$amount = $totalAdditions[$year];
			$totalRow .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$planLengthTotal += $amount;
			$totalCashInByYear[$year] += $amount;
		}
		$totalRow .= (new HTMLTableCell(Globals::numberFormat($planLengthTotal, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		
		$this->htmlTable ->addRow($totalRow);
		$this->htmlTable ->addRow($eiRow);
		$this->htmlTable ->addRow($ltlRow);
		$this->htmlTable ->addRow($stlRow);
		
		// Total Cash In
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_CASH_IN'), false, array("cellAlignLeft", "boldHeader")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $totalCashInByYear[$year];
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "subTotal")))->getHTML(); 
			$totalCashIn += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalCashIn, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "subTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);
		
		$this->htmlTable->addEmptyRow(count($plan->getYears(false))+2);
		
		// TOTAL CASH OUT
		$headerCell = new stdClass();
		$headerCell->classes = array("categoryName");
		$headerCell->colspan="";
		$headerCell->data = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CASH_OUT'), false, array("cellAlignLeft" , "categoryName")))->getHTML();
		$numExtraColumns = count($plan->getYears(false))+1;
		$this->htmlTable->addEmptyRowWithHeader($headerCell, $numExtraColumns);

		// Capital Purchases
		$totalCapitalPurchases = 0;
		$totalCapitalPurchasesByYear = array();
		foreach($plan->getInventoryAndCapitalPurchases()->getCapitalExpenditures() as $expenditure) {
			$row = "";
			$totalForExpenditure = 0;
			$row .=   (new HTMLTableCell($expenditure->getDescription(), false, array("cellAlignRight")))->getHTML();
			foreach($plan->getYears(false) as $year) {
				$row .= (new HTMLTableCell(Globals::numberFormat($expenditure->getAmountByYear($year), Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
				$totalForExpenditure += $expenditure->getAmountByYear($year);
				if (!isset($totalCapitalPurchasesByYear[$year])) {
					$totalCapitalPurchasesByYear[$year] = 0;
				}
				$totalCapitalPurchasesByYear[$year] += $expenditure->getAmountByYear($year);
				$totalCapitalPurchases += $expenditure->getAmountByYear($year);
			}
			$row .= (new HTMLTableCell(Globals::numberFormat($totalForExpenditure, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$this->htmlTable ->addRow($row);
		}
		
		// Change in inventory (COGS * COGSBuffer)
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CHANGE_IN_INVENTORY'), false, array("cellAlignRight")))->getHTML();
		$totalChange = 0;
		$COGSBuffer = $plan->getInventoryAndCapitalPurchases()->getCOGSBuffer();
		foreach($plan->getYears(false) as $year) {
			$amount = $expenses->getCOGSByYear($year) * $COGSBuffer;
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$totalChange += $amount;
			$totalCapitalPurchasesByYear[$year] += $amount;
			$totalCapitalPurchases += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalChange, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Total Capital Purchases
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CAPITAL_PURCHASES'), false, array("cellAlignLeft" , "boldHeader")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$row .= (new HTMLTableCell(Globals::numberFormat($totalCapitalPurchasesByYear[$year], Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "categoryTotal")))->getHTML(); 
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalCapitalPurchases, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "categoryTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Loan Payments
		$totalPaymentsAndDividends = 0;
		$totalLoanPayments = 0;
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_LOAN_PAYMENTS'), false, array("cellAlignRight")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getLoansAndInvestments()->getTotalLoanPaymentsByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			if (!isset($totalPaymentsAndDividendsByYear[$year])) {
				$totalPaymentsAndDividendsByYear[$year]  = 0;
			}
			$totalPaymentsAndDividendsByYear[$year] += $amount;
			$totalLoanPayments += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalLoanPayments, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Owner's Draw & Dividends
		$totalDrawAndDividends = 0;
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_OWNERS_DRAW_AND_DIVIDENDS'), false, array("cellAlignRight")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $plan->getLoansAndInvestments()->getTotalDrawsAndDividendsByYear($year);
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
			$totalPaymentsAndDividendsByYear[$year] += $amount;
			$totalDrawAndDividends += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalDrawAndDividends, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Total Payments & Dividends
		$totalPaymentsAndDividends = 0;
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_PAYMENTS_DIVIDENDS'), false, array("cellAlignLeft" , "boldHeader")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $totalPaymentsAndDividendsByYear[$year];
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "categoryTotal")))->getHTML();
			$totalPaymentsAndDividends += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalPaymentsAndDividends, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "categoryTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		// Total Cash Out
		$totalCashOut = 0;
		$totalCashOutByYear = array();
		$row = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_CASH_OUT'), false, array("cellAlignLeft" , "boldHeader")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $totalPaymentsAndDividendsByYear[$year] + $totalCapitalPurchasesByYear[$year];
			$row .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "subTotal")))->getHTML(); 
			$totalCashOutByYear[$year] = $amount;
			$totalCashOut += $amount;
		}
		$row .= (new HTMLTableCell(Globals::numberFormat($totalCashOut, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "subTotal")))->getHTML(); 
		$this->htmlTable ->addRow($row);

		$this->htmlTable->addEmptyRow(count($plan->getYears(false))+2);

		$headerCell = new stdClass();
		$headerCell->classes = array("categoryName");
		$headerCell->colspan="";
		$headerCell->data = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CASH_FLOW_SUMMARY'), false, array("cellAlignLeft" , "categoryName")))->getHTML();
		$numExtraColumns = count($plan->getYears(false))+1;
		$this->htmlTable->addEmptyRowWithHeader($headerCell, $numExtraColumns);

		// IN
		$cashInRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_CASH_IN'), false, array("cellAlignLeft")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $totalCashInByYear[$year];
			$cashInRow .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "subTotal")))->getHTML(); 
		}
		$cashInRow .= (new HTMLTableCell(Globals::numberFormat($totalCashIn, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "subTotal")))->getHTML(); 

		// OUT
		$cashOutRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_TOTAL_CASH_OUT'), false, array("cellAlignLeft")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $totalCashOutByYear[$year];
			$cashOutRow .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "subTotal")))->getHTML(); 
		}
		$cashOutRow .= (new HTMLTableCell(Globals::numberFormat($totalCashOut, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "subTotal")))->getHTML(); 

		// CHANGE
		$cashChangeRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CASH_CHANGE'), false, array("cellAlignLeft")))->getHTML();
		foreach($plan->getYears(false) as $year) {
			$amount = $totalCashInByYear[$year] - $totalCashOutByYear[$year];
			$cashChangeRow .= (new HTMLTableCell(Globals::numberFormat($amount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "categoryTotal")))->getHTML(); 
			$cashChangeByYear[$year] = $amount;
		}
		$cashChangeRow .= (new HTMLTableCell(Globals::numberFormat($totalCashOut-$totalCashIn, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "categoryTotal")))->getHTML(); 

		// Cash - opening & closing balance
		$openingBalanceRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CASH_OPENING_BALANCE'), false, array("cellAlignLeft" , "grandTotal")))->getHTML();
		$closingBalanceRow = (new HTMLTableCell(Messages::getMessage('VC_MESSAGE_CASH_CLOSING_BALANCE'), false, array("cellAlignLeft" , "grandTotal")))->getHTML();
		$index = 1;
		foreach($plan->getYears(false) as $year) {
			if ($index++ == 1) {
				$openingAmount = $plan->getLoansAndInvestments()->getCashOpeningBalance();
			}
			else {
				$openingAmount = $closingAmountByYear[$year-1];
			}
			$closingAmount = $openingAmount + $cashChangeByYear[$year];
			$closingAmountByYear[$year] = $closingAmount;
			$openingBalanceRow .= (new HTMLTableCell(Globals::numberFormat($openingAmount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "grandTotal")))->getHTML(); 
			$closingBalanceRow .= (new HTMLTableCell(Globals::numberFormat($closingAmount, Globals::NUMBER_FORMAT_TYPE_SALES_INTEGER, true), false, array("cellAlignRight" , "grandTotal")))->getHTML(); 
		}
		$openingBalanceRow .= (new HTMLTableCell("", false, array("cellAlignRight" , "grandTotal")))->getHTML(); 
		$closingBalanceRow .= (new HTMLTableCell("", false, array("cellAlignRight" , "grandTotal")))->getHTML(); 

		$this->htmlTable ->addRow($openingBalanceRow);
		$this->htmlTable ->addRow($cashInRow);
		$this->htmlTable ->addRow($cashOutRow);
		$this->htmlTable ->addRow($cashChangeRow);
		$this->htmlTable ->addRow($closingBalanceRow);

	}

}
	
?>