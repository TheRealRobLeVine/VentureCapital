<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\CapitalExpenditure' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/CapitalExpenditure.php';
}

use VentureCapital\CapitalExpenditure;
use FrmEntry;
use FrmForm;

class InventoryAndCapitalPurchases {

	const VC_INVENTORY_CAPITAL_PURCHASES_FORM_KEY = 'financialplan-inventorycapitalpurchases';
	
	const VC_INVENTORY_CAPITAL_PURCHASES_STARTING_INVENTORY_FIELD_KEY = 'financialplan-inventorycapitalpurchases_starting_inventory';
	const VC_INVENTORY_CAPITAL_PURCHASES_COGS_BUFFER_FIELD_KEY = 'financialplan-inventorycapitalpurchases_cogs_buffer';
	const VC_INVENTORY_CAPITAL_PURCHASES_ACCUMULATED_DEPRECIATION_FIELD_KEY = 'financialplan-inventorycapitalpurchases_accum_depreciation';
	const VC_INVENTORY_CAPITAL_PURCHASES_TOTAL_PURCHASES_DIRECT_LABOUR_FIELD_KEY_PREFIX = 'financialplan_inventorycapitalpurchases_purchases_labour_year_';

	private $ID;
	private $startingInventory;
	private $COGSBuffer;
	private $accumulatedDepreciation;
//	private $purchasesAndDirectLabourAmountsByYear;
	private $capitalExpeditures;
	private $planYears;

	public function __construct($entry, $planYears, $userID) {
		$this->ID = $entry->id;
		$this->startingInventory = Globals::get_value_from_metas_by_key($entry->metas, self::VC_INVENTORY_CAPITAL_PURCHASES_STARTING_INVENTORY_FIELD_KEY);
		if (null == $this->startingInventory) {
			$this->startingInventory = 0;
		}
		$this->COGSBuffer = Globals::get_value_from_metas_by_key($entry->metas, self::VC_INVENTORY_CAPITAL_PURCHASES_COGS_BUFFER_FIELD_KEY);
		if (null == $this->COGSBuffer) {
			$this->COGSBuffer = 0;
		}
		$this->accumulatedDepreciation = Globals::get_value_from_metas_by_key($entry->metas, self::VC_INVENTORY_CAPITAL_PURCHASES_ACCUMULATED_DEPRECIATION_FIELD_KEY);

		// Total Purchases and Direct Labour
/*		$index = 1;
		foreach($planYears as $year) {
			$fieldKey = self::VC_INVENTORY_CAPITAL_PURCHASES_TOTAL_PURCHASES_DIRECT_LABOUR_FIELD_KEY_PREFIX . $index++;
			$this->purchasesAndDirectLabourAmountsByYear[$year] = Globals::get_value_from_metas_by_key($entry->metas, $fieldKey);
		}
		*/
		
		// Capital Expenditures
		$expenditureEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(CapitalExpenditure::VC_CAPITAL_EXPENDITURE_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->capitalExpeditures = array();
		foreach($expenditureEntries as $expenditureEntry) {
			$entry = FrmEntry::getOne($expenditureEntry->id, true);
			$this->capitalExpeditures[] = new CapitalExpenditure($entry, $planYears);
		}
	
	}
	
	public function getID() {
		return $this->ID;
	}

	public function getStartingInventory() {
		return $this->startingInventory;
	}
	
	public function getCOGSBuffer() {
		return $this->COGSBuffer;
	}
	
	public function getAccumulatedDepreciation() {
		return $this->accumulatedDepreciation;
	}
	
/*	public function getPurchasesAndDirectLabourAmountsByYear($year) {
		return $this->purchasesAndDirectLabourAmountsByYear[$year];
	}
*/	
	public function getCapitalExpenditures() {
		return $this->capitalExpeditures;
	}
	
}

?>