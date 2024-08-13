<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class OperatingExpense extends Expense {

	const VC_OPERATING_EXPENSE_TYPE_FACILITY = "Facility";
	const VC_OPERATING_EXPENSE_TYPE_SALARY_BENEFITS = "Salary & Benefits";
	const VC_OPERATING_EXPENSE_TYPE_MARKETING_PROMOTION = "Marketing & Promotion";
	const VC_OPERATING_EXPENSE_TYPE_FOOD_SAFETY_REGULATION = "Food Safety & Regulation";
	const VC_OPERATING_EXPENSE_TYPE_ADMINISTRATIVE = "Administrative";
	const VC_OPERATING_EXPENSE_TYPE_STAFF_TRAVEL = "Staff Travel & Expenses";
	const VC_OPERATING_EXPENSE_TYPE_OTHER = "Other";

	const VC_OPERATING_EXPENSES_FORM_FIELD_KEY_PREFIX = 'financialplan-calculatingexpenses_';
	const VC_OPERATING_EXPENSES_DESCRIPTION_FIELD_KEY_SUFFIX = '_description';
	const VC_OPERATING_EXPENSES_MONTHLY_BENCHMARK_FIELD_KEY_SUFFFIX = '_monthly_benchmark';
	
	const VC_OPERATING_EXPENSES_FACILITY_FORM_FIELD_KEY_INNARDS = 'facility';
	const VC_OPERATING_EXPENSES_SALARY_BENEFITS_FORM_FIELD_KEY_INNARDS = 'salary_benefits';
	const VC_OPERATING_EXPENSES_MARKETING_FORM_FIELD_KEY_INNARDS = 'marketing';
	const VC_OPERATING_EXPENSES_FOOD_SAFETY_FORM_FIELD_KEY_INNARDS = 'food_safety';
	const VC_OPERATING_EXPENSES_ADMINISTRATIVE_FORM_FIELD_KEY_INNARDS = 'administrative';
	const VC_OPERATING_EXPENSES_STAFF_TRAVEL_FORM_FIELD_KEY_INNARDS = 'staff_travel';
	const VC_OPERATING_EXPENSES_OTHER_FORM_FIELD_KEY_INNARDS = 'other';
	
	const VC_OPERATING_EXPENSES_FACILITY_CHILD_FORM_KEY = 'zafuw';
	const VC_OPERATING_EXPENSES_SALARY_BENEFITS_CHILD_FORM_KEY = 'pajo0';
	const VC_OPERATING_EXPENSES_MARKETING_CHILD_FORM_KEY = '6rc02';
	const VC_OPERATING_EXPENSES_FOOD_SAFETY_CHILD_FORM_KEY = 'ftbrq';
	const VC_OPERATING_EXPENSES_ADMINISTRATIVE_CHILD_FORM_KEY = '7j7k2';
	const VC_OPERATING_EXPENSES_STAFF_TRAVEL_CHILD_FORM_KEY = 'kn4yu';
	const VC_OPERATING_EXPENSES_OTHER_CHILD_FORM_KEY = 'dh9wp';

	private $monthlyBenchmark;
	private $yearlyExpenses; // array indexed by plan years
	
	public function __construct($type, $entry, $inflationRate, $planYears, $description=null) {
		switch ($type) {
			case self::VC_OPERATING_EXPENSE_TYPE_FACILITY:
				$innards = self::VC_OPERATING_EXPENSES_FACILITY_FORM_FIELD_KEY_INNARDS;
				break;
			case self::VC_OPERATING_EXPENSE_TYPE_SALARY_BENEFITS:
				$innards = self::VC_OPERATING_EXPENSES_SALARY_BENEFITS_FORM_FIELD_KEY_INNARDS;
				break;
			case self::VC_OPERATING_EXPENSE_TYPE_MARKETING_PROMOTION:
				$innards = self::VC_OPERATING_EXPENSES_MARKETING_FORM_FIELD_KEY_INNARDS;
				break;
			case self::VC_OPERATING_EXPENSE_TYPE_FOOD_SAFETY_REGULATION:
				$innards = self::VC_OPERATING_EXPENSES_FOOD_SAFETY_FORM_FIELD_KEY_INNARDS;
				break;
			case self::VC_OPERATING_EXPENSE_TYPE_ADMINISTRATIVE:
				$innards = self::VC_OPERATING_EXPENSES_ADMINISTRATIVE_FORM_FIELD_KEY_INNARDS;
				break;
			case self::VC_OPERATING_EXPENSE_TYPE_STAFF_TRAVEL:
				$innards = self::VC_OPERATING_EXPENSES_STAFF_TRAVEL_FORM_FIELD_KEY_INNARDS;
				break;
			case self::VC_OPERATING_EXPENSE_TYPE_OTHER:
				$innards = self::VC_OPERATING_EXPENSES_OTHER_FORM_FIELD_KEY_INNARDS;
				break;
		}
		$this->ID = $entry->id;
		$this->type = $type;
		if (null == $description) {
			$this->description = Globals::get_value_from_metas_by_key($entry->metas, self::VC_OPERATING_EXPENSES_FORM_FIELD_KEY_PREFIX .  $innards . self::VC_OPERATING_EXPENSES_DESCRIPTION_FIELD_KEY_SUFFIX);
		}
		else {
			$this->description = $description;
		}
		$this->monthlyBenchmark = Globals::get_value_from_metas_by_key($entry->metas, self::VC_OPERATING_EXPENSES_FORM_FIELD_KEY_PREFIX .  $innards . self::VC_OPERATING_EXPENSES_MONTHLY_BENCHMARK_FIELD_KEY_SUFFFIX);
		$this->_setYearlyExpenseByYear($planYears, $inflationRate);
	}
	
	public function getMonthlyBenchmark() {
		return $this->monthlyBenchmark;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function getYearlyExpenseByYear($year) {
		return $this->yearlyExpenses[$year];
	}
	
	private function _setYearlyExpenseByYear($planYears, $inflationRate) {
		$i=0;
		foreach($planYears as $year) {
			if ($i++ == 0) {
				$this->yearlyExpenses[$year] = $this->monthlyBenchmark * 12;
			}
			else {
				$this->yearlyExpenses[$year] = $this->yearlyExpenses[$year-1] * (1 + $inflationRate/100);
			}
		}
	}
}

?>