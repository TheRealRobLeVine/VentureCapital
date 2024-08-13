<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\ShortTermLoan' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/ShortTermLoan.php';
}
if ( ! class_exists( '\VentureCapital\LongTermLoan' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/LongTermLoan.php';
}
if ( ! class_exists( '\VentureCapital\EquityInvestment' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/EquityInvestment.php';
}

use VentureCapital\ShortTermLoan;
use VentureCapital\LongTermLoan;
use VentureCapital\EquityInvestment;
use FrmEntry;
use FrmForm;

class LoansAndInvestments {
	
	const VC_LOANS_INVESTMENTS_FORM_KEY = 'financialplan-loansinvestment';
	const VC_LOANS_INVESTMENTS_OPENING_BALANCE_FIELD_KEY = 'financialplan-loansinvestment_opening_balance';
	const VC_LOANS_INVESTMENTS_TOTAL_LOAN_PAYMENTS_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_total_loan_payments_year_';
	const VC_LOANS_INVESTMENTS_TOTAL_DRAWS_AND_DIVIDENDS_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_total_draws_and_dividends_year_';
	
	private $cashOpeningBalance;
	private $longTermLoans;
	private $shortTermLoans;
	private $equityAndInvestments;
	private $totalLoanPaymentsByYear;

	public function __construct($entry, $planYears, $userID) {

		$this->cashOpeningBalance = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_OPENING_BALANCE_FIELD_KEY);

		// Long Term Loans
		$entries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(LongTermLoan::VC_LOANS_INVESTMENTS_LONG_TERM_LOANS_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->longTermLoans = array();
		foreach($entries as $singleEntry) {
			$loanEntry = FrmEntry::getOne($singleEntry->id, true);
			$this->longTermLoans[] = new LongTermLoan($planYears, $loanEntry);
		}

		// Short Term Loans
		$entries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(ShortTermLoan::VC_LOANS_INVESTMENTS_SHORT_TERM_LOANS_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->shortTermLoans = array();
		foreach($entries as $singleEntry) {
			$loanEntry = FrmEntry::getOne($singleEntry->id, true);
			$this->shortTermLoans[] = new ShortTermLoan($planYears, $loanEntry);
		}
		
		// Total loan payments
		$index = 1;
		foreach($planYears as $year) {
			$fieldKey = self::VC_LOANS_INVESTMENTS_TOTAL_LOAN_PAYMENTS_FIELD_KEY_PREFIX . $index++;
			$this->totalLoanPaymentsByYear[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
		}
		
		// Equity & Investments
		$entries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(EquityInvestment::VC_EQIUITY_INVESTMENTS_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->equityAndInvestments = array();
		foreach($entries as $singleEntry) {
			$equityEntry = FrmEntry::getOne($singleEntry->id, true);
			$this->equityAndInvestments[] = new EquityInvestment($planYears, $equityEntry);
		}
			
		// Total draws and dividends
		$index = 1;
		foreach($planYears as $year) {
			$fieldKey = self::VC_LOANS_INVESTMENTS_TOTAL_DRAWS_AND_DIVIDENDS_FIELD_KEY_PREFIX . $index++;
			$this->totalDrawsAndDividendsByYear[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
		}
		
	}
	
	public function getCashOpeningBalance() {
		return $this->cashOpeningBalance;
	}
	
	public function getLongTermLoans() {
		return $this->longTermLoans;
	}
	
	public function getShortTermLoans() {
		return $this->shortTermLoans;
	}

	public function getEquityAndInvestments() {
		return $this->equityAndInvestments;
	}
	
	public function getTotalLoanPaymentsByYear($year) {
		return $this->totalLoanPaymentsByYear[$year];
	}
	
	public function getTotalDrawsAndDividendsByYear($year) {
		return $this->totalDrawsAndDividendsByYear[$year];
	}
}

?>