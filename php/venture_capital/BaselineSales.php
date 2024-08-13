<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class BaselineSales extends Sales {

	const VC_AVERAGE_BASELINE_CHILD_FORM_KEY = 'uvbxa';
	const VC_AVERAGE_BASELINE_CATEGORY_FIELD_KEY = 'financialplan-salesforecasting_sales_category';
	const VC_AVERAGE_BASELINE_CHANNEL_FIELD_KEY = 'financialplan-salesforecasting_sales_channel';
	const VC_AVERAGE_BASELINE_UNITS_FIELD_KEY = 'financialplan-salesforecasting_sales_units';
	const VC_AVERAGE_BASELINE_UNIT_PRICE_FIELD_KEY = 'financialplan-salesforecasting_sales_price_per_unit';

	private $growthRates;
	
	public function __construct($entry) {
		$this->ID = $entry->id;
		$this->channelID = Globals::get_value_from_metas_by_key($entry->metas, self::VC_AVERAGE_BASELINE_CHANNEL_FIELD_KEY);
		$this->categoryID = Globals::get_value_from_metas_by_key($entry->metas, self::VC_AVERAGE_BASELINE_CATEGORY_FIELD_KEY);
		$this->units = Globals::get_value_from_metas_by_key($entry->metas, self::VC_AVERAGE_BASELINE_UNITS_FIELD_KEY);
		$this->unitPrice = Globals::get_value_from_metas_by_key($entry->metas, self::VC_AVERAGE_BASELINE_UNIT_PRICE_FIELD_KEY);
		$this->sales = $this->units * $this->unitPrice;

		parent::__construct($this->channelID, $this->categoryID, $this->units, $this->sales, $this->unitPrice);
	}
	
	public function setGrowthRates($growthRates) {
		$this->growthRates = $growthRates;
	}
	
	public function getGrowthRateByYear($year) {
		return $this->growthRates[$year];
	}
}

?>