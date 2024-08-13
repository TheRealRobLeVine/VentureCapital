<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\Segment' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Segment.php';
}
use VentureCapital\Segment;

class Category extends Segment {

	const VC_CATEGORY_CHILD_FORM_KEY = '398ms';
	const VC_PLAN_CATEGORY_FIELD_KEY = 'financialplan-plansetup_category';

	const VC_CATEGORY_GROWTH_CHILD_FORM_KEY = 'lqnwq';
	const VC_CATEGORY_GROWTH_FIELD_KEY_PREFIX = 'financialplan-salesforecasting_percent_growth_sales_category_year_';
	const VC_CATEGORY_GROWTH_OBJECT_FIELD_KEY = 'financialplan-salesforecasting_growth_by_category_category';

	public function __construct($entry, $plan, $userID) {
		$ID = $entry->id;
		$name = Globals::get_value_from_metas_by_key($entry->metas, self::VC_PLAN_CATEGORY_FIELD_KEY);
		parent::__construct($ID, $name);
		self::_setGrowthRates($plan, $userID);
		$this->salesByYear = array();
	}
	
	private function _setGrowthRates($plan, $userID) {
		parent::setGrowthRates($plan,
										self::VC_CATEGORY_GROWTH_CHILD_FORM_KEY, 
										$this->ID,
										self::VC_CATEGORY_GROWTH_OBJECT_FIELD_KEY, 
										self::VC_CATEGORY_GROWTH_FIELD_KEY_PREFIX, 
										$userID);
	}
	
}
?>
