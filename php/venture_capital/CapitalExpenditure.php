<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

use FrmField;

class CapitalExpenditure {

	const VC_CAPITAL_EXPENDITURE_CHILD_FORM_KEY = 'w07ys';
	
	const VC_INVENTORY_CAPITAL_PURCHASES_FORM_KEY = 'financialplan-inventorycapitalpurchases';
	
	const VC_CAPITAL_EXPENDITURE_DESCRIPTION_FIELD_KEY = 'financialplan-inventorycapitalpurchases_expenditure_description';
	const VC_CAPITAL_EXPENDITURE_AMOUNT_FIELD_KEY_PREFIX = 'financialplan-inventorycapitalpurchases_expenditure_year_';
	const VC_CAPITAL_EXPENDITURE_DEPRECIATION_RATE_FIELD_KEY = 'financialplan-inventorycapitalpurchases_expenditure_depreciation';
	const VC_CAPITAL_EXPENDITURE_CURRENT_VALUE_FIELD_KEY = 'financialplan-inventorycapitalpurchases_expenditure_current_value';
	
	private $ID;
	private $description;
	private $assetAmountByYear;
	private $amountByYear;
	private $depreciationRate;
	private $currentValue;
	
	public function __construct($entry, $planYears) {
		$this->ID = $entry->id;
		$this->description = Globals::get_value_from_metas_by_key($entry->metas, self::VC_CAPITAL_EXPENDITURE_DESCRIPTION_FIELD_KEY);
		$index = 1;
		$this->currentValue = Globals::get_value_from_metas_by_key($entry->metas, self::VC_CAPITAL_EXPENDITURE_CURRENT_VALUE_FIELD_KEY);
		foreach($planYears as $year) {
			$fieldKey = self::VC_CAPITAL_EXPENDITURE_AMOUNT_FIELD_KEY_PREFIX . $index;
			$this->assetAmountByYear[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
			$this->amountByYear[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
			if ($index == 1) {
				$this->assetAmountByYear[$year] += $this->currentValue;
			}
			else {
				$this->assetAmountByYear[$year] += $this->assetAmountByYear[$year-1];
			}
			$index++;
		}
		$this->depreciationRate = Globals::get_value_from_metas_by_key($entry->metas, self::VC_CAPITAL_EXPENDITURE_DEPRECIATION_RATE_FIELD_KEY);
	}
	
	public function getID() {
		return $this->ID;
	}

	public function getDescription() {
		return $this->description;
	}
	
	public function getDepreciationRate() {
		return $this->depreciationRate;
	}

	public function getCurrentValue() {
		return $this->currentValue;
	}
	
	public function getAmountByYear($year) {
		return $this->amountByYear[$year];
	}

	public function getAssetAmountByYear($year) {
		return $this->assetAmountByYear[$year];
	}
}

?>