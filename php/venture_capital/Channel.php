<?php

namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\Segment' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Segment.php';
}

use VentureCapital\Segment;

class Channel extends Segment {

	const VC_CHANNEL_CHILD_FORM_KEY = 'yl07v';
	const VC_PLAN_CHANNEL_FIELD_KEY = 'financialplan-plansetup_channel';

	const VC_CHANNEL_GROWTH_CHILD_FORM_KEY = 'n6kck';
	const VC_CHANNEL_GROWTH_FIELD_KEY_PREFIX = 'financialplan-salesforecasting_percent_growth_sales_channel_year_';
	const VC_CHANNEL_GROWTH_OBJECT_FIELD_KEY = 'financialplan-salesforecasting_growth_by_channel_channel';
	
	public function __construct($entry, $plan, $userID) {
		$ID = $entry->id;
		$name =Globals::get_value_from_metas_by_key($entry->metas, self::VC_PLAN_CHANNEL_FIELD_KEY);
		parent::__construct($ID, $name);
		self::_setGrowthRates($plan, $userID);
	}

	private function _setGrowthRates($plan, $userID) {
		parent::setGrowthRates($plan, 
										self::VC_CHANNEL_GROWTH_CHILD_FORM_KEY, 
										$this->ID,
										self::VC_CHANNEL_GROWTH_OBJECT_FIELD_KEY, 
										self::VC_CHANNEL_GROWTH_FIELD_KEY_PREFIX, 
										$userID);
	}
}

?>