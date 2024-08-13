<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\AssetLiability' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/AssetLiability.php';
}
use VentureCapital\AssetLiability;

class Loan extends AssetLiability {

	private $interestPaidByYear;
	private $totalInterestPaid;
	private $totalContributions;
	private $contributionsByYear;
	private $totalYearEndClosingOrBalance;
	private $yearEndClosingOrBalanceByYear;

	public function __construct($planYears, 
													$entry, 
													$interestPrefix, 
													$contributionPrefix,
													$closingTotalPrefix) {
		$iIndex = 1;
		$this->totalInterestPaid = 0;
		$this->totalContributions = 0;
		foreach($planYears as $year) {
			$fieldKey =  $interestPrefix . $iIndex;
			$this->interestPaidByYear[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
			$this->totalInterestPaid += $this->interestPaidByYear[$year];
			$fieldKey =  $contributionPrefix . $iIndex;
			$this->contributionsByYear[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
			$this->totalContributions += $this->contributionsByYear[$year];
			$fieldKey =  $closingTotalPrefix . $iIndex;
			$amount = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
			$amount = ($amount < 0) ? 0 : $amount;
			$this->yearEndClosingOrBalanceByYear[$year] = $amount;
			$this->totalYearEndClosingOrBalance += $this->yearEndClosingOrBalanceByYear[$year];
			$iIndex++;
		}

	}
	
	
	public function getInterestPaidByYear($year) {
		return $this->interestPaidByYear[$year];
	}

	public function getInterestPaid() {
		return $this->interestPaid;
	}

	public function getTotalInterestPaid() {
		return $this->totalInterestPaid;
	}
	
	public function getContributionsByYear($year) {
		return $this->contributionsByYear[$year];
	}

	public function getTotalContributions() {
		return $this->totalContribution;
	}

	public function getYearEndClosingOrBalanceByYear($year) {
//echo "GET closing balance by year: " . $this->yearEndClosingOrBalanceByYear[$year] . "<br>";			
		return $this->yearEndClosingOrBalanceByYear[$year];
	}

	public function getTotalYearEndClosingOrBalance() {
		return $this->totalYearEndClosingOrBalance;
	}
}
?>