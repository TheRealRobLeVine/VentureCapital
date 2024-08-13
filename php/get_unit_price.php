<?php
	$path = preg_replace('/wp-content.*$/','',__DIR__);
	require($path . '/wp-load.php');

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You are not allowed to call this page directly.' );
	}

	$categoryID = $_GET["category"];
	$channelID = $_GET["channel"];

	$priceCategoryFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_product_category");
	$priceChannelFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_sales_channel");
	$unitPriceFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_unit_price");    
	$priceByCategoryAndChannelChildFormID = FrmForm::get_id_by_key("nfqp3");	
	$allPriceEntries = FrmEntry::getAll(array('it.form_id' => $priceByCategoryAndChannelChildFormID, 'it.user_id' => get_current_user_id()), '', '', true);
    $val = "0.00";
	foreach($allPriceEntries as $row) {
		if ($row->metas[$priceChannelFieldID] == $channelID && $row->metas[$priceCategoryFieldID] == $categoryID) {
        	$val = $row->metas[$unitPriceFieldID];
            break;
        }
    }

	echo $val;
    die;
?>