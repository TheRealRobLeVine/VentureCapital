<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class COGS {

	const VC_COGS_CHILD_FORM_KEY = 'rw72j';
	const VC_COGS_CATEGORY_FIELD_KEY = 'financialplan-calculatingexpenses_category';
	const VC_COGS_INGREDIENTS_COST_PER_UNIT_FIELD_KEY = 'financialplan-calculatingexpenses_ingredients_cost_per_unit';
	const VC_COGS_LABOUR_COST_PER_UNIT_FIELD_KEY = 'financialplan-calculatingexpenses_labour_cost_per_unit';
	const VC_COGS_PACKAGING_COST_PER_UNIT_FIELD_KEY = 'financialplan-calculatingexpenses_packaging_cost_per_unit';

	private $ID;
	private $categoryID;
	private $ingredientsCostPerUnit;
	private $labourCostPerUnit;
	private $packagingCostPerUnit;
	private $unitCost;
	private $totalIngredientsCostByYear;  // array indexed by year where [0] = baseline
	private $totalLabourCostByYear; // array indexed by year where [0] = baseline
	private $totalPackagingCostByYear; // array indexed by year where [0] = baseline
	private $totalUnitsByYear; // array indexed by year where [0] = baseline
	
	public function __construct($entry) {
		$this->ID = $entry->id;
		$this->categoryID = Globals::get_value_from_metas_by_key($entry->metas, self::VC_COGS_CATEGORY_FIELD_KEY);
		$this->ingredientsCostPerUnit = Globals::get_value_from_metas_by_key($entry->metas, self::VC_COGS_INGREDIENTS_COST_PER_UNIT_FIELD_KEY);
		$this->labourCostPerUnit = Globals::get_value_from_metas_by_key($entry->metas, self::VC_COGS_LABOUR_COST_PER_UNIT_FIELD_KEY);
		$this->packagingCostPerUnit = Globals::get_value_from_metas_by_key($entry->metas, self::VC_COGS_PACKAGING_COST_PER_UNIT_FIELD_KEY);
		$this->unitCost = $this->ingredientsCostPerUnit + $this->labourCostPerUnit + $this->packagingCostPerUnit;
		
	}
	
	public function getID() {
		return $this->ID;
	}

	public function getCategoryID() {
		return $this->categoryID;
	}

	public function getIngredientsCostPerUnit() {
		return $this->ingredientsCostPerUnit;
	}

	public function getLabourCostPerUnit() {
		return $this->labourCostPerUnit;
	}

	public function getPackagingCostPerUnit() {
		return $this->packagingCostPerUnit;
	}

	public function getUnitCost() {
		return $this->unitCost;
	}
}

?>