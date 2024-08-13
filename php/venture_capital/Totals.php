<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class Totals  {

	private $units;
	private $sales;
	
	public function __construct($units, $sales) {
		$this->units = $units;
		$this->sales = $sales;
	}
	
	public function getUnits() {
		return $this->units;
	}

	public function getSales() {
		return $this->sales;
	}
	
	public function setUnits($units) {
		$this->units = $units;
	}
	
	public function setSales($sales) {
		$this->sales = $sales;
	}
}
	
?>