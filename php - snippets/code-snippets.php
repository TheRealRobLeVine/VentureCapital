<?php

/**
 * Build Sales Forecast
 */
add_action('frm_after_create_entry', 'afterPricing', 30, 2);
	add_action('frm_after_update_entry', 'afterPricing', 30, 2);
	
	function afterPricing($entry_id, $form_id) {
		if ($form_id == FrmForm::get_id_by_key("financialplan-plansetup")) {
				createSalesForecastEntry();
		}
	}

	/**
	* 	createSalesForecastEntry
	*
	*  Creates a sales forecast entry if one doesn't already exist
	*
	*/
	function createSalesForecastEntry() {
		global $wpdb;
		$userID = get_current_user_id();
		$salesForecastFormID = FrmForm::get_id_by_key("financialplan-salesforecasting");
		$salesForecastUserIDFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_user_id");
		$salesForecastParticipantFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_admin_participant");
		$salesForecastCoachFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_admin_coach");
		$salesForecastFirstNameFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_admin_first_name");
		$salesForecastLastNameFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_admin_last_name");
		$salesForecastUserGroupFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_admin_user_group");
		$salesForecastPlanLengthFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_admin_plan_length");
		$salesForecastAdminAdministrationFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_admin_administration");
		$salesForecasts = FrmEntry::getAll(array('it.form_id' => $salesForecastFormID, 'it.user_id' => $userID), '', '', true);
		if ((count($salesForecasts) <= 0)) {

			// get the information from the user reg form
			$formID = FrmForm::get_id_by_key("userregistrationsteptwo");
			$sql = "SELECT id FROM {$wpdb->prefix}frm_items WHERE form_id = $formID AND user_id = $userID";
			$entryID = $wpdb->get_var($sql);
			$userRegEntry = FrmEntry::getOne($entryID, true);
			 
			$formID = FrmForm::get_id_by_key("financialplan-plansetup");
			$sql = "SELECT id FROM {$wpdb->prefix}frm_items WHERE form_id = $formID AND user_id = $userID";
			$entryID = $wpdb->get_var($sql);
			$planEntry = FrmEntry::getOne($entryID, true);
			
			$values = array(
				$salesForecastUserIDFieldID => $userID,
				$salesForecastParticipantFieldID => $userRegEntry->metas["userregistrationsteptwo_last_name"], // "participant" = last name??
				$salesForecastCoachFieldID => $userRegEntry->metas["userregistrationsteptwo_coach"],
				$salesForecastFirstNameFieldID => $userRegEntry->metas["userregistrationsteptwo_first_name"],
				$salesForecastLastNameFieldID => $userRegEntry->metas["userregistrationsteptwo_last_name"],
				$salesForecastUserGroupFieldID => $userRegEntry->metas["userregistrationsteptwo_referral_source"],
				$salesForecastPlanLengthFieldID => $planEntry->metas["financialplan-plansetup_plan_length"],
				$salesForecastAdminAdministrationFieldID => 'a:2:{i:0;s:29:"Venture Capital Administrator";i:1;s:13:"The Webmaster";}'
			);
			$newForecastEntryID = FrmEntry::create(array(
				  'form_id' => $salesForecastFormID, 
				  'item_key' => FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_items', 'item_key' ),
				  'frm_user_id' => $userID,
  			  	  'item_meta' => $values
				));
		}
	}

/**
 * Remove Dropdown Duplicates
 */
add_filter('frm_data_sort', 'frm_remove_duplicates', 21, 2);
function frm_remove_duplicates( $options, $atts ) {
	$droplistIDs = array(1559, 1560, 1573, 1591, 1616);
    if ( in_array($atts['dynamic_field']['id'], $droplistIDs)) {
		$options = array_unique( $options );
    }
    return $options;
}

/**
 * Add Entry ID to User After Registration
 *
 * Sets the entry ID into a field in the user entry, after registration.
 */
add_action('frm_after_create_entry', 'frmAddEntryIDToUser', 42, 2);
function frmAddEntryIDToUser($entry_id, $form_id){
   if ( $form_id == FrmForm::get_id_by_key('user-registration') ) {
     FrmEntryMeta::add_entry_meta( $entry_id, FrmField::get_id_by_key('user-registration_entry_id'), "", $entry_id);
   }
}

/**
 * Formidable Include Meta Keys
 *
 * See https://formidableforms.com/knowledgebase/frm_include_meta_keys/
 * This hook makes it so when an entry object is retrieved, like this:
 *  
 * $entry = FrmEntry::getOne( $id, true)
 * then the $entry-&gt;metas includes the field values by ID and also by key. Normally when retrieving an entry object, the $entry -&gt; metas would only include the field values by ID.
 */
add_filter('frm_include_meta_keys', '__return_true');

/**
 * Reports
 */
if ( ! class_exists( '\VentureCapital\SalesForecastReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/SalesForecastReport.php';
	}
	use VentureCapital\SalesForecastReport;
	if ( ! class_exists( '\VentureCapital\BreakEvenReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/BreakEvenReport.php';
	}
	use VentureCapital\BreakEvenReport;
	if ( ! class_exists( '\VentureCapital\IncomeProjectionReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/IncomeProjectionReport.php';
	}
	use VentureCapital\IncomeProjectionReport;
	if ( ! class_exists( '\VentureCapital\CashFlowReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/CashFlowReport.php';
	}
	use VentureCapital\CashFlowReport;
	if ( ! class_exists( '\VentureCapital\BalanceSheetReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/BalanceSheetReport.php';
	}
	use VentureCapital\BalanceSheetReport;
	if ( ! class_exists( '\VentureCapital\PLProjectionReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/PLProjectionReport.php';
	}
	use VentureCapital\PLProjectionReport;
	if ( ! class_exists( '\VentureCapital\PESalesMarginReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/PESalesMarginReport.php';
	}
	use VentureCapital\PESalesMarginReport;
	if ( ! class_exists( '\VentureCapital\PEUnitCostAnalysisReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/PEUnitCostAnalysisReport.php';
	}
	use VentureCapital\PEUnitCostAnalysisReport;
	if ( ! class_exists( '\VentureCapital\PEAnnualUnitCostReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/PEAnnualUnitCostReport.php';
	}
	use VentureCapital\PEAnnualUnitCostReport;
	if ( ! class_exists( '\VentureCapital\PEProductionAndCOGSSummaryReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/PEProductionAndCOGSSummaryReport.php';
	}
	use VentureCapital\PEProductionAndCOGSSummaryReport;
	if ( ! class_exists( '\VentureCapital\SPProjectionsOnSalesChannelGrowthReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/SPProjectionsOnSalesChannelGrowthReport.php';
	}
	use VentureCapital\SPProjectionsOnSalesChannelGrowthReport;
	if ( ! class_exists( '\VentureCapital\SPCategorySalesOnChannelGrowthReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/SPCategorySalesOnChannelGrowthReport.php';
	}
	use VentureCapital\SPCategorySalesOnChannelGrowthReport;
	if ( ! class_exists( '\VentureCapital\SPProjectionsOnCategoryGrowthReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/SPProjectionsOnCategoryGrowthReport.php';
	}
	use VentureCapital\SPProjectionsOnCategoryGrowthReport;
	if ( ! class_exists( '\VentureCapital\SPXReport' ) ) {
		require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/SPXReport.php';
	}
	use VentureCapital\SPXReport;

	add_shortcode('sales-forecast-report', 'salesForecastReport');

	function salesForecastReport() {
		$report = new SalesForecastReport();
		$report->output();
	}

	add_shortcode('break-even-report', 'breakEvenReport');

	function breakEvenReport() {
		$report = new BreakEvenReport();
		$report->output();
	}

	add_shortcode('income-projection-report', 'incomeProjectionReport');

	function incomeProjectionReport() {
		$report = new IncomeProjectionReport();
		$report->output();
	}

	add_shortcode('cash-flow-report', 'cashFlowReport');

	function cashFlowReport() {
		$report = new CashFlowReport();
		$report->output();
	}

	add_shortcode('balance-sheet-report', 'balanceSheetReport');

	function balanceSheetReport() {
		$report = new BalanceSheetReport();
		$report->output();
	}

	add_shortcode('pl-projection-report', 'PLProjectionReport');

	function PLProjectionReport() {
		$report = new PLProjectionReport();
		$report->output();
	}

	add_shortcode('pe-salesmargin-report', 'PESalesMarginReport');

	function PESalesMarginReport() {
		$report = new PESalesMarginReport();
		$report->output();
	}

	add_shortcode('pe-unitcostanalysis-report', 'PEUnitCostAnalysisReport');

	function PEUnitCostAnalysisReport() {
		$report = new PEUnitCostAnalysisReport();
		$report->output();
	}

	add_shortcode('pe-annualunitcost-report', 'PEAnnualUnitCostReport');

	function PEAnnualUnitCostReport() {
		$report = new PEAnnualUnitCostReport();
		$report->output();
	}

	add_shortcode('pe-productionandcogssummary-report', 'PEProductionAndCOGSSummaryReport');

	function PEProductionAndCOGSSummaryReport() {
		$report = new PEProductionAndCOGSSummaryReport();
		$report->output();
	}

	add_shortcode('sp-projectionsaleschannelgrowth-report', 'SPProjectionsOnSalesChannelGrowthReport');

	function SPProjectionsOnSalesChannelGrowthReport() {
		$report = new SPProjectionsOnSalesChannelGrowthReport();
		$report->output();
	}

	add_shortcode('sp-categorysalesonchannelgrowth-report', 'SPCategorySalesOnChannelGrowthReport');

	function SPCategorySalesOnChannelGrowthReport() {
		$report = new SPCategorySalesOnChannelGrowthReport();
		$report->output();
	}

	add_shortcode('sp-projectionsoncategorygrowth-report', 'SPProjectionsOnCategoryGrowthReport');

	function SPProjectionsOnCategoryGrowthReport() {
		$report = new SPProjectionsOnCategoryGrowthReport();
		$report->output();
	}

	add_shortcode('sp-x-report', 'SPXReport');

	function SPXReport() {
		$report = new SPXReport();
		$report->output();
	}

/**
 * DataTables
 */
function register_datatables() {
	wp_register_style( 'datatables', 'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css', null, null );
	wp_register_style( 'datatables-buttons', 'https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css');
	wp_register_script( 'datatables', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js', null, null, true );
	wp_register_script( 'pdfmake', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', null, null, true );
	wp_register_script( 'pdfmake-fonts', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', null, null, true );
	wp_register_script( 'datatables-buttons', 'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js', null, null, true );
	wp_register_script( 'datatables-buttons-HTML5', 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js');
	wp_register_script( 'datatables-print', 'https://cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js', null, null, true );
}
add_action( 'wp_enqueue_scripts', 'register_datatables' );

function datatables_shortcode( $a, $c, $t ) {
	
	wp_enqueue_script( 'pdfmake' );
	wp_enqueue_script( 'pdfmake-fonts' );
	wp_enqueue_style( 'datatables' );
	wp_enqueue_style( 'datatables-buttons' );
	wp_enqueue_script( 'datatables' );
	wp_enqueue_script( 'datatables-buttons' );
	wp_enqueue_script( 'datatables-buttons-HTML5' );
	wp_enqueue_script( 'datatables-print' );
	$script = "jQuery(document).ready(function($) {

			$('.dtExportAndPDF').DataTable( {
				order: [],
				searching: false, 
				info: false, 
				paging: false,
				dom: 'Bt',
				buttons: [
					'copy', 'csv', 'excel', 'pdf'
				],
			});

		} );";
	wp_add_inline_script( 'datatables', $script );
	
//	return $html;
}
add_shortcode( 'datatables', 'datatables_shortcode' );
