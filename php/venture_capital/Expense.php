<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class Expense {

	const VC_EXPENSE_TYPE_MANUFACTURING = "Manufacturing";
	const VC_EXPENSE_TYPE_SELLING = "Selling";
	const VC_EXPENSE_TYPE_CAPITAL_EXPENDITURES = "Capital Expenditures";

	const VC_EXPENSES_FORM_KEY = 'financialplan-calculatingexpenses';
	const VC_INVENTORY_CAPITAL_PURCHASES_FORM_KEY = 'financialplan-inventorycapitalpurchases';

	const VC_EXPENSES_CONTINGENCY_UNPLANNED_FIELD_KEY =  'financialplan-calculatingexpenses_contingency_unplanned';
	const VC_EXPENSES_ADJUSTMENT_INFLATION_FIELD_KEY = 'financialplan-calculatingexpenses_adjustment_inflation';
	const VC_EXPENSES_ADJUSTMENT_TAX_RATE_FIELD_KEY = 'financialplan-calculatingexpenses_adjustment_average_tax_rate';
	const VC_EXPENSES_ADJUSTMENT_CARRYOVER_LOSS_FIELD_KEY = 'financialplan-calculatingexpenses_adjustment_carryover_losses';
	
	private $ID;
	private $description;
	private $type;
	
	public function __construct($ID, $description, $type) {
		$this->ID = $ID;
		$this->description = $description;
		$this->type = $type;
	}
	
	public function getID() {
		return $this->ID;
	}

	public function getDescription() {
		return $this->description;
	}
	
	public function getType() {
		return $this->type;
	}
	
}

?>