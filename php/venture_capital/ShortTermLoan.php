<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\Loan' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Loan.php';
}
use VentureCapital\Loan;

class ShortTermLoan extends Loan {

	const VC_LOANS_INVESTMENTS_SHORT_TERM_LOANS_CHILD_FORM_KEY = 'r4ep2';
	
	const VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_IDENTIFIER_FIELD_KEY = 'financialplan-loansinvestment_short_term_loan_identifier';
	const VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_DESCRIPTION_FIELD_KEY = 'financialplan-loansinvestment_short_term_loan_description';
	const VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_ADDITIONS_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_short_term_loan_additions_year_';
	const VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_ADDITIONS_TOTAL_FIELD_KEY = 'financialplan-loansinvestment_short_term_loan_additions_total';
	const VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_REPAYMENTS_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_short_term_loan_repayments_year_';
	const VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_REPAYMENTS_TOTAL_FIELD_KEY = 'financialplan-loansinvestment_short_term_loan_repayments_total';
	const VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_INTEREST_PAID_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_short_term_loan_interest_paid_year_';
	const VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_CONTRIBUTIONS_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_short_term_loan_additions_year_';
	const VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_YEAR_END_BALANCE_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_short_term_year_end_balance_year_';

	private $additions;
	private $additionsTotal;
	private $repayments;
	private $repaymentsTotal;
	
	public function __construct($planYears, $entry) {
		$this->ID = $entry->id;
		$this->identifier = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_IDENTIFIER_FIELD_KEY);
		$this->description = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_DESCRIPTION_FIELD_KEY);
		
		$iIndex = 1;
		foreach($planYears as $year) {
			$fieldKey =  self::VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_ADDITIONS_FIELD_KEY_PREFIX . $iIndex;
			$this->additions[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
			$fieldKey =  self::VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_REPAYMENTS_FIELD_KEY_PREFIX . $iIndex;
			$this->repayments[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
			$iIndex++;
		}

		parent::__construct($planYears, 
											$entry, 
											self::VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_INTEREST_PAID_FIELD_KEY_PREFIX, 
											self::VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_CONTRIBUTIONS_FIELD_KEY_PREFIX,
											self::VC_LOANS_INVESTMENTS_SHORT_TERM_LOAN_YEAR_END_BALANCE_FIELD_KEY_PREFIX) ;
	}
	
	public function getAdditions() {
		return $this->additions;
	}
	
	public function getAdditionsByYear($year) {
		return $this->additions[$year];
	}
	
	public function getAdditionsTotal() {
		return $this->additionsTotal();
	}
	
	public function getRepayments() {
		return $this->repayments;
	}
	
	public function getRepaymentsTotal() {
		return $this->repaymentsTotal;
	}

}

?>

