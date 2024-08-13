<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class AssetLiability {

	private $ID;
	protected $identifier;
	protected $description;
	
	public function __construct() {
	}
	
	public function getID() {
		return $this->ID;
	}

	public function getIdentifier() {
		return $this->identifier;
	}
	
	public function getDescription() {
		return $this->description;
	}
}
?>