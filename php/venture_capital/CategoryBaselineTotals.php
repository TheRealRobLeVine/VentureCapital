<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\BaselineTotals' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/BaselineTotals.php';
}
use VentureCapital\BaselineTotals;

class CategoryBaselineTotals extends BaselineTotals {

	private $categoryID;

	public function __construct($categoryID) {
		$this->categoryID = $categoryID;
	}
	
	public function getCategoryID() {
		return $this->categoryID;
	}
}

?>