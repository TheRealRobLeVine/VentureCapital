<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\HTMLTable' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/HTMLTable.php';
}
if ( ! class_exists( '\VentureCapital\Plan' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Plan.php';
}

use VentureCapital\HTMLTable;
use VentureCapital\Plan;	

use FrmForm;
use FrmEntry;

class Report {

	const VC_PLAN_FORM_KEY = "financialplan-plansetup";
	const VC_PLAN_START_YEAR_FIELD_KEY = 'financialplan-plansetup_start_year';
	const VC_PLAN_LENGTH_FIELD_KEY = 'financialplan-plansetup_plan_length';
	
	private $plan;
	protected $htmlTable;
	
	public function __construct($userID=null) {
		
		global $wpdb;
		if (null == $userID) {
			$userID = get_current_user_id();
		}
		
		$formID = FrmForm::get_id_by_key(self::VC_PLAN_FORM_KEY);
		$sql = "SELECT id FROM {$wpdb->prefix}frm_items WHERE form_id = $formID AND user_id = $userID";
		$entryID = $wpdb->get_var($sql);
		$entry = FrmEntry::getOne($entryID, true);
		$planStartYear = $entry->metas[self::VC_PLAN_START_YEAR_FIELD_KEY];
		$planLength = $entry->metas[self::VC_PLAN_LENGTH_FIELD_KEY];
		$planLength = substr($planLength, 0, strpos($planLength, ' ')); // strip off the text after the number of years

		$this->plan = new Plan($entry->id, $planStartYear, $planLength, $userID);
	}
	
	function getPlan() {
		return $this->plan;
	}
	
    function getTitle(){
		return $this->title;
    }

    function getDescription(){
		return $this->description;
    }
	
	function getHTMLTable() {
		return $this->htmlTable;
	}

	public function output() {
		return $this->htmlTable->output();
	}
}
	
?>