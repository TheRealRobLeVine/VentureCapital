<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
if ( ! class_exists( '\VentureCapital\COGS' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/COGS.php';
}
if ( ! class_exists( '\VentureCapital\DirectExpense' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/DirectExpense.php';
}
if ( ! class_exists( '\VentureCapital\OperatingExpense' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/OperatingExpense.php';
}

use VentureCapital\COGS;
use VentureCapital\DirectExpense;
use VentureCapital\OperatingExpense;

use FrmEntry;
use FrmForm;

class Expenses {
	
	private $COGS;
	private $manufacturingExpenses;
	private $sellingExpenses;
	private $operatingExpenses;
	private $contingencyUnplanned;
	private $contingencyUnplannedByYear;
	private $inflation;
	private $taxRate;
	private $carryoverLoss;
	
	private $planYears;
	private $forecast;
	
	private $directExpensesByYear;
	private $totalDirectExpenses;
	private $operatingExpensesByYear;
	private $totalOperatingExpenses;
	private $yearlyOperatingExpense;
	private $fixedExpensesByYear;
	private $totalFixedExpenses;
	private $totalFixedExpensesByYear;
	private $totalVariableExpenses;
	private $totalVariableExpensesByYear;
	private $COGSByYear;
	private $totalCOGS;
	private $totalIngredientsCostByYear;
	private $totalLabourCostByYear;
	private $totalPackagingCostByYear;
	
	private $facilityExpenses;
	private $salaryAndBenefitExpenses;
	private $marketingAndPromotionExpenses;
	private $foodSafetyExpenses;
	private $administrativeExpenses;
	private $staffTravelExpenses;
	private $otherExpenses;
	
	private $facilityExpensesByYear;
	private $totalFacilityExpenses;
	private $administrativeExpensesByYear;
	private $totalAdministrativeExpenses;
	private $otherExpensesByYear;
	private $totalOtherExpenses;

	// loans / investments
	private $totalInterestPaidByYear;
	private $totalInterestPaid;
	
	private $depreciationByYear;
	private $totalDepreciation;
	
	private $plan;
	
	public function __construct($userID, $plan) {
		global $wpdb;

		$this->plan = $plan;
		$this->planYears = $plan->getYears(false);
		$this->forecast = $plan->getForecast();
		
		// Cost of Goods Sold
		$COGSEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(COGS::VC_COGS_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->COGS = array();
		foreach($COGSEntries as $row) {
			$entry = FrmEntry::getOne($row->id, true);
			$this->COGS[] = new COGS($entry);
		}
		$this->_setCOGSByYear();
		
		// Direct Expenses
		// Manufacturing
		$expensesEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(DirectExpense::VC_EXPENSES_MANUFACTURING_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->manufacturingExpenses = array();
		foreach($expensesEntries as $row) {
			$entry = FrmEntry::getOne($row->id, true);
			$this->manufacturingExpenses[] = new DirectExpense(Expense::VC_EXPENSE_TYPE_MANUFACTURING, $entry);
		}

		// Selling
		$expensesEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(DirectExpense::VC_EXPENSES_SELLING_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->sellingExpenses = array();
		foreach($expensesEntries as $row) {
			$entry = FrmEntry::getOne($row->id, true);
			$this->sellingExpenses[] = new DirectExpense(Expense::VC_EXPENSE_TYPE_SELLING, $entry);
		}
		
		// Contingency/Unplanned Expenses
		$formID = FrmForm::get_id_by_key(Expense::VC_EXPENSES_FORM_KEY);
		$sql = "SELECT id FROM {$wpdb->prefix}frm_items WHERE form_id = $formID AND user_id = $userID";
		$expenseFormEntryID = $wpdb->get_var($sql);
		$expenseFormEntry = FrmEntry::getOne($expenseFormEntryID, true);
		$this->contingencyUnplanned = Globals::get_value_from_metas_by_key($expenseFormEntry->metas, Expense::VC_EXPENSES_CONTINGENCY_UNPLANNED_FIELD_KEY);
		$this->inflation = Globals::get_value_from_metas_by_key($expenseFormEntry->metas, Expense::VC_EXPENSES_ADJUSTMENT_INFLATION_FIELD_KEY);
		$this->taxRate = Globals::get_value_from_metas_by_key($expenseFormEntry->metas, Expense::VC_EXPENSES_ADJUSTMENT_TAX_RATE_FIELD_KEY);
		$this->carryoverLoss = Globals::get_value_from_metas_by_key($expenseFormEntry->metas, Expense::VC_EXPENSES_ADJUSTMENT_CARRYOVER_LOSS_FIELD_KEY);
			
		// Operating Expenses
		$this->operatingExpenses = array();
		
		// Facility
		$operatingExpenses = array();
		$operatingExpensesEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(OperatingExpense::VC_OPERATING_EXPENSES_FACILITY_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		foreach($operatingExpensesEntries as $row) {
			$entry = FrmEntry::getOne($row->id, true);
			$operatingExpense = new OperatingExpense(OperatingExpense::VC_OPERATING_EXPENSE_TYPE_FACILITY, $entry, $this->inflation, $this->planYears);
			foreach($this->planYears as $year) {
				if (!isset($this->facililtyExpensesByYear[$year])) {
					$this->facililtyExpensesByYear[$year] = 0;
				}
				$this->facililtyExpensesByYear[$year] += $operatingExpense->getYearlyExpenseByYear($year);
			}
			
			//$this->totalFacilityExpenses
			$operatingExpenses[] = $operatingExpense;
		}
		$this->facilityExpenses = $operatingExpenses;
		array_push($this->operatingExpenses, $operatingExpenses);

		// Salary/Benefits (single value)
		$operatingExpense = new OperatingExpense(OperatingExpense::VC_OPERATING_EXPENSE_TYPE_SALARY_BENEFITS, $expenseFormEntry, $this->inflation, $this->planYears, Messages::getMessage('VC_MESSAGE_SALARY_AND_BENEFITS'));
		$this->salaryAndBenefitExpenses = $operatingExpense;
		array_push($this->operatingExpenses, $operatingExpense);

		// Marketing & Promotion (single value)
		$operatingExpense = new OperatingExpense(OperatingExpense::VC_OPERATING_EXPENSE_TYPE_MARKETING_PROMOTION, $expenseFormEntry, $this->inflation, $this->planYears, Messages::getMessage('VC_MESSAGE_MARKETING_AND_PROMOTION'));
		$this->marketingAndPromotionExpenses = $operatingExpense;
		array_push($this->operatingExpenses, $operatingExpense);
	
		// Food Safety & Regulatory (single value)
		$operatingExpense = new OperatingExpense(OperatingExpense::VC_OPERATING_EXPENSE_TYPE_FOOD_SAFETY_REGULATION, $expenseFormEntry, $this->inflation, $this->planYears, Messages::getMessage('VC_MESSAGE_FOOD_SAFETY_AND_REGULATORY'));
		$this->foodSafetyExpenses = $operatingExpense;
		array_push($this->operatingExpenses, $operatingExpense);
		
		// Staff & Travel (single value)
		$operatingExpense = new OperatingExpense(OperatingExpense::VC_OPERATING_EXPENSE_TYPE_STAFF_TRAVEL, $expenseFormEntry, $this->inflation, $this->planYears, Messages::getMessage('VC_MESSAGE_STAFF_AND_TRAVEL_EXPENSES'));
		$this->staffTravelExpenses = $operatingExpense;
		array_push($this->operatingExpenses, $operatingExpense);
		
		// Administrative
		$operatingExpenses = array();
		$operatingExpensesEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(OperatingExpense::VC_OPERATING_EXPENSES_ADMINISTRATIVE_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		foreach($operatingExpensesEntries as $row) {
			$entry = FrmEntry::getOne($row->id, true);
			$operatingExpenses[] = new OperatingExpense(OperatingExpense::VC_OPERATING_EXPENSE_TYPE_ADMINISTRATIVE, $entry, $this->inflation, $this->planYears);
		}
		$this->administrativeExpenses = $operatingExpenses;
		array_push($this->operatingExpenses, $operatingExpenses);
		
		// Other
		$operatingExpenses = array();
		$operatingExpensesEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(OperatingExpense::VC_OPERATING_EXPENSES_OTHER_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		foreach($operatingExpensesEntries as $row) {
			$entry = FrmEntry::getOne($row->id, true);
			$operatingExpenses[] = new OperatingExpense(OperatingExpense::VC_OPERATING_EXPENSE_TYPE_OTHER, $entry, $this->inflation, $this->planYears);
		}
		$this->otherExpenses = $operatingExpenses;
		array_push($this->operatingExpenses, $operatingExpenses);
		
		// set depreciation
		$this->_setDepreciationAmountsByYear();
		
		// Set total variable expenses = manufacturing + selling + COGS expenses
		$this->_setVariableExpensesByYear();
		
		// Set total fixed expenses = (operating expenses + (contingency/unplanned * operating expensese)
		$this->_setFixedExpensesByYear();
		
	}

	
	public function getCOGS() {
		return $this->COGS;
	}

	public function getManufacturingExpenses() {
		return $this->manufacturingExpenses;
	}

	public function getSellingExpenses() {
		return $this->sellingExpenses;
	}

	public function getFacilityExpenses() {
		return $this->facilityExpenses;
	}
	public function getSalaryAndBenefitExpenses() {
		return $this->salaryAndBenefitExpenses;
	}

	public function getMarketingAndPromotionExpenses() {
		return $this->marketingAndPromotionExpenses;
	}
	
	public function getFoodSafetyExpenses() {
		return $this->foodSafetyExpenses;
	}
	
	public function getAdministrativeExpenses() {
		return $this->administrativeExpenses;
	}
	
	public function getStaffTravelExpenses() {
		return $this->staffTravelExpenses;
	}
	
	public function getOtherExpenses() {
		return $this->otherExpenses;
	}

	public function getOperatingExpenses() {
		return $this->operatingExpenses;
	}

	public function getContingencyUnplanned() {
		return $this->contingencyUnplanned;
	}
	
	public function getInflation() {
		return $this->inflation;
	}

	public function getTaxRate() {
		return $this->taxRate;
	}

	public function getCarryoverLoss() {
		return $this->carryoverLoss;
	}

	public function getCOGSByYear($year) {
		return $this->COGSByYear[$year];
	}
	
	public function getTotalCOGS() {
		return $this->totalCOGS;
	}
	
	private function _setCOGSByYear() {
		$totalIngredientsCost = 0;
		$totalLabourCost = 0;
		$totalPackagingCost = 0;
		$this->totalCOGS = 0;
		foreach($this->planYears as $year) {
			$ingredientsCost = 0;
			$labourCost = 0;
			$packagingCost = 0;
			foreach($this->plan->getCategories() as $category) {
				$catID = $category->getID();
				$salesObj = $this->forecast->getCategoryTotalsByYear($catID, $year);
				$units = $salesObj->getUnits();
				$categoryCOGS = null;
				foreach($this->COGS as $singleCOGS) {
					if ($catID == $singleCOGS->getCategoryID()) {
						$categoryCOGS = $singleCOGS;
						break;
					}
				}
if (null == $categoryCOGS) {
	error_log("Expenses->_setCOGSByYear NULL COGS catID: $catID");				
}	
				if ($categoryCOGS != null) {
					$ingredientsCost += $categoryCOGS->getIngredientsCostPerUnit() * $units;
					$labourCost += $categoryCOGS->getLabourCostPerUnit() * $units;
					$packagingCost += $categoryCOGS->getPackagingCostPerUnit() * $units;
				}
			}
			$this->_setIndividualCOGSTotalsByYear($year, $ingredientsCost, $labourCost, $packagingCost);
			$totalIngredientsCost += $ingredientsCost;
			$totalLabourCost += $labourCost;
			$totalPackagingCost += $packagingCost;
			$this->COGSByYear[$year] = ($ingredientsCost +  $labourCost + $packagingCost);
			$this->totalCOGS += $this->COGSByYear[$year];
		}
	}

	public function getCOGSFromCategoryID($categoryID) {
		foreach($this->COGS as $singleCOGS) {
			if ($categoryID == $singleCOGS->getCategoryID()) {
				return $singleCOGS;
			}
		}
	}

	private function _setIndividualCOGSTotalsByYear($year, $ingredientsCosts, $labourCosts, $packagingCosts) {
		$this->totalIngredientsCostByYear[$year] = $ingredientsCosts;
		$this->totalLabourCostByYear[$year] = $labourCosts;
		$this->totalPackagingCostByYear[$year] = $packagingCosts;
//		$this->totalUnits[$year] = $units;
	}
	
	public function getTotalIngredientsCostByYear($year) {
		return $this->totalIngredientsCostByYear[$year];
	}
	
	public function getTotalLabourCostByYear($year) {
		return $this->totalLabourCostByYear[$year];
	}
	
	public function getTotalPackagingCostByYear($year) {
		return $this->totalPackagingCostByYear[$year];
	}

	/** Variable expenses are manufacturing, selling and COGS
	 */
	private function _setVariableExpensesByYear() {
		$this->totalVariableExpenses = 0;
		foreach($this->planYears as $year) {
			$totalVariableExpenses[$year] = 0;
			foreach($this->manufacturingExpenses as $singleExpense) {
				$totalVariableExpenses[$year] +=  ($this->forecast->getTotalSalesPerYear($year)* $singleExpense->getPercentOfSales()) / 100;
			}
			foreach($this->sellingExpenses as $singleExpense) {
				$totalVariableExpenses[$year] +=  ($this->forecast->getTotalSalesPerYear($year)* $singleExpense->getPercentOfSales()) / 100;
			}
			// add the COGS
			$totalVariableExpenses[$year] += $this->COGSByYear[$year];
			$this->variableExpensesByYear[$year] = $totalVariableExpenses[$year];
			
			$this->totalVariableExpenses += $this->variableExpensesByYear[$year];
		}
	}
	
	public function getVariableExpensesByYear($year) {
		return $this->variableExpensesByYear[$year];
	}
	
	public function getTotalVariableExpenses() {
		return $this->totalVariableExpenses;
	}

	/** Fixed expenses are the operating expenses + contingency/unplanned
	 */
	private function _setFixedExpensesByYear() {
		$this->totalFixedExpenses = 0;
		foreach($this->planYears as $year) {
			$totalFixedExpenses[$year] = 0;
			foreach($this->getFacilityExpenses() as $expense) {
				$totalFixedExpenses[$year] += $expense->getYearlyExpenseByYear($year);
			}
			foreach($this->getAdministrativeExpenses() as $expense) {
				$totalFixedExpenses[$year] += $expense->getYearlyExpenseByYear($year);
			}
			$totalFixedExpenses[$year] += $this->getSalaryAndBenefitExpenses()->getYearlyExpenseByYear($year);
			$totalFixedExpenses[$year] += $this->getMarketingAndPromotionExpenses()->getYearlyExpenseByYear($year); 
			$totalFixedExpenses[$year] += $this->getFoodSafetyExpenses()->getYearlyExpenseByYear($year);
			$totalFixedExpenses[$year] += $this->getStaffTravelExpenses()->getYearlyExpenseByYear($year);
			
			$this->fixedExpensesByYear[$year] = $totalFixedExpenses[$year];
			$this->contingencyUnplannedByYear[$year] =  $totalFixedExpenses[$year] * ($this->contingencyUnplanned/100);
			$this->fixedExpensesByYear[$year] += $this->contingencyUnplannedByYear[$year];
			
			$this->totalFixedExpenses += $this->fixedExpensesByYear[$year];
		}
	}

	public function getFixedExpensesByYear($year) {
		return $this->fixedExpensesByYear[$year];
	}

	public function getDirectExpensesByYear() {
	}
	
	private function _setDirectExpensesByYear() {
		$this->totalDirectExpenses = $total;
	}

	public function getOperatingExpensesByYear($year) {
		$this->yearlyOperatingExpense[$year];
	}
	
	public function getContingencyUnplannedByYear($year) {
		return $this->contingencyUnplannedByYear[$year];
	}
	
	/*
	 *  Operating expenses are entered as monthly numbers
	 *   Each year those numbers are bumped up by the inflation value
	 *
	 */
	private function _setOperatingExpensesByYear() {
		$this->totalOperatingExpenses = 0.0;
		foreach($this->planYears as $year) {
			$this->yearlyExpense[$year] = 0;
			$this->yearlyOperatingExpense[$year] = 0;
			foreach($this->operatingExpense as $expense) {
				$this->yearlyOperatingExpense[$year] += $expense->getYearlyExpenseByYear[$year];
				$this->totalOperatingExpenses += $this->yearlyExpense[$year];
			}
		}
	}
	
	private function _setDepreciationAmountsByYear() {
		$capitalExpenditures = $this->plan->getInventoryAndCapitalPurchases()->getCapitalExpenditures();
		$this->totalDepreciation = 0;
		foreach($this->planYears as $year) {
			$this->depreciationByYear[$year] = 0;
			foreach($capitalExpenditures as $expenditure) {
				$this->depreciationByYear[$year] += ($expenditure->getAssetAmountByYear($year) * ($expenditure->getDepreciationRate()/100));
			}
			$this->totalDepreciation += $this->depreciationByYear[$year];
		}
	}
	
	public function getDepreciationByYear($year) {
		return $this->depreciationByYear[$year];
	}
	
	public function getTotalDepreciation() {
		return $this->totalDepreciation;
	}
	
}

?>