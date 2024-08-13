<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class Sales  {

	private $channelID;
	private $categoryID;
	
	private $units;
	private $unitPrice;
	private $sales; // amount (units * price)
	
	public function __construct($channelID, $categoryID, $units, $sales, $unitPrice=null) {
		$this->channelID = $channelID;
		$this->categoryID = $categoryID;
		$this->units = $units;
		$this->sales = $sales;
		$this->unitPrice = $unitPrice;
	}
	
	public function getChannelID() {
		return $this->channelID;
	}
	
	public function getCategoryID() {
		return $this->categoryID;
	}

	public function getUnits() {
		return $this->units;
	}

	public function setUnitPrice($unitPrice) {
		return $this->unitPrice = $unitPrice;
	}

	public function getUnitPrice() {
		return $this->unitPrice;
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
	
	// debugging
	public function output() {
//		echo "channel id: " . $this->channelID . "<br>";
//		echo "category id: " . $this->categoryID . "<br>";
		echo "units: " . $this->units . "<br>";
//		echo "sales: " . $this->sales . "<br>";
		
	}
}
	
?>