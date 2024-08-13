<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\Expense' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Expense.php';
}

class DirectExpense extends Expense {

	const VC_EXPENSES_MANUFACTURING_CHILD_FORM_KEY = 'h11re';
	const VC_EXPENSES_SELLING_CHILD_FORM_KEY = '8xsdh';

	const VC_EXPENSES_MANUFACTURING_DESCRIPTION_FIELD_KEY = 'financialplan-calculatingexpenses_manufacturing_description';
	const VC_EXPENSES_MANUFACTURING_PERCENT_OF_SALES_FIELD_KEY = 'financialplan-calculatingexpenses_manufacturing_percent_of_sales';
	const VC_EXPENSES_SELLING_DESCRIPTION_FIELD_KEY = 'financialplan-calculatingexpenses_selling_description';
	const VC_EXPENSES_SELLING_PERCENT_OF_SALES_FIELD_KEY = 'financialplan-calculatingexpenses_selling_percent_of_sales';

	private $percentOfSales;
	
	public function __construct($type, $entry) {
		$ID = $entry->id;
		$this->description = "";
		$this->percentOfSales = "";
		switch($type) {
			case Expense::VC_EXPENSE_TYPE_MANUFACTURING:
				$this->description = Globals::get_value_from_metas_by_key($entry->metas, self::VC_EXPENSES_MANUFACTURING_DESCRIPTION_FIELD_KEY);
				$this->percentOfSales = Globals::get_value_from_metas_by_key($entry->metas, self::VC_EXPENSES_MANUFACTURING_PERCENT_OF_SALES_FIELD_KEY);
				break;
			case Expense::VC_EXPENSE_TYPE_SELLING:
				$this->description = Globals::get_value_from_metas_by_key($entry->metas, self::VC_EXPENSES_SELLING_DESCRIPTION_FIELD_KEY);
				$this->percentOfSales = Globals::get_value_from_metas_by_key($entry->metas, self::VC_EXPENSES_SELLING_PERCENT_OF_SALES_FIELD_KEY);
				break;
			default:
				break;
		}
		
		parent::__construct($ID, $this->description, $type);
	}
	
	public function getPercentOfSales() {
		return $this->percentOfSales;
	}
	
}

?>