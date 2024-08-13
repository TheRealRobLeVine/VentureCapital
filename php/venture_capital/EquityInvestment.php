<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

use FrmField;

class EquityInvestment extends AssetLiability {

	const VC_EQIUITY_INVESTMENTS_CHILD_FORM_KEY = 'fhkm8';
	
	const VC_LOANS_INVESTMENTS_EQUITY_INVESTMENT_IDENTIFIER_FIELD_KEY = 'financialplan-loansinvestment_equity_investment_identifier';
	const VC_LOANS_INVESTMENTS_EQUITY_INVESTMENT_LOAN_DESCRIPTION_FIELD_KEY = 'financialplan-loansinvestment_equity_investment_description';
	const VC_LOANS_INVESTMENTS_EQUITY_INVESTMENT_SHARE_FIELD_KEY = 'financialplan-loansinvestment_equity_investment_share';
	const VC_LOANS_INVESTMENTS_EQUITY_INVESTMENT_CONTRIBUTION_FIELD_KEY_PREFIX = 'financialplan-loansinvestment_equity_investment_contribution_year_';
                                                                                                                                                              
	private $share;
	private $investmentContributions;
	
	public function __construct($planYears, $entry) {
		$this->ID = $entry->id;
		$this->share = Globals::get_value_from_metas_by_key($entry->metas, self::VC_LOANS_INVESTMENTS_EQUITY_INVESTMENT_SHARE_FIELD_KEY);

		$iIndex = 1;
		foreach($planYears as $year) {
			$fieldKey =  self::VC_LOANS_INVESTMENTS_EQUITY_INVESTMENT_CONTRIBUTION_FIELD_KEY_PREFIX . $iIndex++;
			$fieldID = FrmField::get_id_by_key($fieldKey);
			$this->investmentContributions[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
		}
	}
	
	public function getShare() {
		return $this->share;
	}
	
	public function getInvestmentContributions() {
		return $this->investmentContributions;
	}

	public function getInvestmentContributionsByYear($year) {
		return $this->investmentContributions[$year];
	}
}
	
?>