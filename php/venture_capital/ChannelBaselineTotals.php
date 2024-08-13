<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\BaselineTotals' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/BaselineTotals.php';
}
use VentureCapital\BaselineTotals;

class ChannelBaselineTotals extends BaselineTotals {

	private $channelID;

	public function __construct($channelID) {
		$this->channelID = $channelID;
	}
	
	public function getChannelID() {
		return $this->channelID;
	}
}

?>