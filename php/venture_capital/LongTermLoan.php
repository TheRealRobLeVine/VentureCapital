<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\Loan' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Loan.php';
}
use VentureCapital\Loan;

class LongTermLoan extends Loan {

	const VC_LOANS_INVESTMENTS_LONG_TERM_LOANS_CHILD_FORM_KEY = 'f4pfq';

	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_IDENTIFIER_FIELD_KEY = 'financialplan-loansinvestment_long_term_loan_identifier';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_DESCRIPTION_FIELD_KEY = 'financialplan-loansinvestment_long_term_loan_description';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_PRINCIPAL_BALANCE_FIELD_KEY = 'financialplan-loansinvestment_long_term_loan_principal_balance';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_INTEREST_RATE_FIELD_KEY = 'financialplan-loansinvestment_long_term_loan_interest_rate';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_ANNUAL_PAYMENT_FIELD_KEY = 'financialplan-loansinvestment_long_term_loan_annual_payment';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_START_YEAR_FIELD_KEY = 'financialplan-loansinvestment_long_term_loan_start_year';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_END_YEAR_FIELD_KEY = 'financialplan-loansinvestment_long_term_loan_end_year';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_LUMP_SUM_PAYMENT_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_long_term_loan_lump_sum_payments_year_';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_LUMP_SUM_PAYMENT_TOTAL_FIELD_KEY = 'financialplan-loansinvestment_long_term_loan_lump_sum_payments_years_total';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_INTEREST_PAID_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_long_term_loan_interest_paid_year_';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_CONTRIBUTIONS_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_long_term_loan_additions_year_';
	const VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_CLOSING_TOTAL_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_long_term_closing_total_year_';

	private $principalBalance;
	private $interestRate;
	private $annualPayment;
	private $startYear;
	private $endYear;
	private $interestPaid;
	private $lumpSumPayments;
	private $lumpSumPaymentsTotal;
	private $totalInterestPaid;

	public function __construct($planYears, $entry) {
		$this->ID = $entry->id;
		$this->identifier = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_IDENTIFIER_FIELD_KEY);
		$this->description = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_DESCRIPTION_FIELD_KEY);
		$this->principalBalance = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_PRINCIPAL_BALANCE_FIELD_KEY);
		$this->interestRate = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_INTEREST_RATE_FIELD_KEY);
		$this->annualPayment = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_ANNUAL_PAYMENT_FIELD_KEY);
		$this->startYear = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_START_YEAR_FIELD_KEY);
		$this->endYear = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_END_YEAR_FIELD_KEY);
		
		$iIndex = 1;
		$this->totalInterestPaid = 0;
		foreach($planYears as $year) {
			$fieldKey =  self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_LUMP_SUM_PAYMENT_FIELD_KEY_PREFIX . $iIndex;
			$this->lumpSumPayments[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
			$iIndex++;
		}

		parent::__construct($planYears, 
											$entry, 
											self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_INTEREST_PAID_FIELD_KEY_PREFIX, 
											self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_CONTRIBUTIONS_FIELD_KEY_PREFIX,
											self::VC_LOANS_INVESTMENTS_LONG_TERM_LOAN_CLOSING_TOTAL_FIELD_KEY_PREFIX);
	}
	
	public function getPrincipalBalance() {
		return $this->principalBalance;
	}
	
	public function getInterestRate() {
		return $this->interestRate;
	}
	
	public function getAnnualPayment() {
		return $this->annualPayment;
	}
	
	public function getStartYear() {
		return $this->startYear;
	}
	
	public function getEndYear() {
		return $this->endYear;
	}
	
	public function getLumpSumPayments() {
		return $this->lumpSumPayments;
	}
	
	public function getLumpSumPaymentsTotal() {
		return $this->lumpSumPaymentsTotal;
	}
	
}
?>