<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class PriceAndMargin {

	const VC_PRICE_AND_MARGIN_CHILD_FORM_KEY = 'nfqp3';
	const VC_PRICE_CHANNEL_FIELD_KEY = 'financialplan-pricebyproductcategorysaleschannel_sales_channel';
	const VC_PRICE_CATEGORY_FIELD_KEY = 'financialplan-pricebyproductcategorysaleschannel_product_category';
	const VC_PRICE_UNIT_PRICE_FIELD_KEY = 'financialplan-pricebyproductcategorysaleschannel_unit_price';
//	const VC_PRICE_RESELLER_MARGIN_FIELD_KEY = 'financialplan-pricebyproductcategorysaleschannel_reseller_margin';

	private $ID;
	private $channelID;
	private $categoryID;
	private $unitPrice;
//	private $resellerMargin;
	
	public function __construct($entry) {
		$this->ID = $entry->id;
		$this->channelID =Globals::get_value_from_metas_by_key($entry->metas, self::VC_PRICE_CHANNEL_FIELD_KEY);
		$this->categoryID = Globals::get_value_from_metas_by_key($entry->metas, self::VC_PRICE_CATEGORY_FIELD_KEY);
		$this->unitPrice = Globals::get_value_from_metas_by_key($entry->metas, self::VC_PRICE_UNIT_PRICE_FIELD_KEY);
//		$this->resellerMargin = Globals::get_value_from_metas_by_key($entry->metas, self::VC_PRICE_RESELLER_MARGIN_FIELD_KEY);
	}
	
	public function getID() {
		return $this->ID;
	}

	public function getChannelID() {
		return $this->channelID;
	}
	
	public function getCategoryID() {
		return $this->categoryID;
	}

	public function getUnitPrice() {
		return $this->unitPrice;
	}

	/* Number returned is a %age and can be 0 (or null) */
	public function getResellerMargin() {
//		return $this->resellerMargin;
	}

}

?>