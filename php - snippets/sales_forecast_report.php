<?php

	add_shortcode('sales-forecast-report', 'salesForecastReport');
	
	/**
	* 	initializeSalesForecast
	*
	*  When the "Price By Product Category and Sales Channel" form is saved
	*    initialize the categories, channels and pricing in the Sales Forecast form
	*
	*/
	function salesForecastReport($mode="BYCHANNEL") { //BYCATEGORY
		global $wpdb;
		
		$tableHTML = "";
		
		$include_meta = true;
		
		$planSetupFormID = FrmForm::get_id_by_key("financialplan-plansetup");
		$planLengthFieldID = FrmField::get_id_by_key("financialplan-plansetup_plan_length");
		$planStartYearFieldID = FrmField::get_id_by_key("financialplan-plansetup_start_year");
		
		$planCategoryChildFormID = FrmForm::get_id_by_key("398ms");
		$planCategoryRepeaterFieldID = FrmField::get_id_by_key("financialplan-plansetup_category_repeater");
		$planCategoryFieldID = FrmField::get_id_by_key("financialplan-plansetup_category");
		$planChannelChildFormID = FrmForm::get_id_by_key("yl07v");
		$planChannelRepeaterFieldID = FrmField::get_id_by_key("financialplan-plansetup_channel_repeater");
		$planChannelFieldID = FrmField::get_id_by_key("financialplan-plansetup_channel");
		
		$priceByCategoryAndChannelFormID = FrmForm::get_id_by_key("financialplan-pricebyproductcategorysaleschannel");
		$priceByCategoryAndChannelRepeaterFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_repeater");
		$priceByCategoryAndChannelChildFormID = FrmForm::get_id_by_key("nfqp3");
		
		$userID = get_current_user_id();
		$currentPlan = FrmEntry::getAll(array('it.form_id' => $planSetupFormID, 'it.user_id' => $userID), '', '', $include_meta);
        $firstIndex = array_key_first($currentPlan);
		$planLengthString = $currentPlan[$firstIndex]->metas[$planLengthFieldID];
		$planLength = $planLengthString[0];
		
		$planStartYear = $currentPlan[$firstIndex]->metas[$planStartYearFieldID];
		error_log("planstartyear: $planStartYear and planlength: $planLength");		

		$allCategoryEntries = FrmEntry::getAll(array('it.form_id' => $planCategoryChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		foreach($allCategoryEntries as $category) {
				$categories[] = $category->id; 
		}
		$allChannelEntries = FrmEntry::getAll(array('it.form_id' => $planChannelChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		foreach($allChannelEntries as $channel) {
				$channels[] = $channel->id;
		}
		
		// Using the Price by Product Category & Sales Channel form
		//		get lists of the categories, channels
		// 	make an array of the existing category-channel combinations
		
		$priceCategoryFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_product_category");
		$priceChannelFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_sales_channel");
		$unitPriceFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_unit_price");
		$resellerMarginFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_reseller_margin");
		$allPriceAndMarginEntries = FrmEntry::getAll(array('it.form_id' => $priceByCategoryAndChannelChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		$existing_category_channel_combos = array();
		$userID = get_current_user_id();
		
		$channelData = array();
		$categoryData = array();
		if ($mode=="BYCHANNEL") {
			foreach($channels as $channel) {
				$channelData = new StdClass;
				$channelData->id = $channel;
				$channelData->data = array();
				foreach($allPriceAndMarginEntries as $row) {
					if ($row->metas[$priceChannelFieldID] == $channel) {
						$data = new StdClass;
						$data->categoryID = $row->metas[$priceCategoryFieldID];
						$data->unitPrice = $row->metas[$unitPriceFieldID];
						$data->margin = $row->metas[$resellerMarginFieldID];
						$data->units = getUnitsByChannelAndCategory($data->categoryID, $channel, $userID);
						$channelData->data[] = $data;
					}
				}
			}
		}
		else {
			foreach($categories as $category) {
				$categoryData = new StdClass;
				$categoryData->id = $category;
				$categoryData->data = array();
				foreach($allPriceAndMarginEntries as $row) {
					if ($row->metas[$priceCategoryFieldID] == $category) {
						$data = new StdClass;
						$data->channelID = $row->metas[$priceChannelFieldID];
						$data->unitPrice = $row->metas[$unitPriceFieldID];
						$data->margin = $row->metas[$resellerMarginFieldID];
						$data->units = getUnitsByChannelAndCategory($data->channelID, $category, $userID);
						$categoryData->data[] = $data;
					}
				}
			}
		}
		error_log(print_r($channelData, true));
		error_log(print_r($categoryData, true));
		
		$tableHTML = "<table>";
		$tableHTML .= "<thead>";
		$tableHTML .= "<th>Category</th>";
		$tableHTML .= "<th>Baseline</th>";
		for($i=$planStartYear;$i<($planStartYear+$planLength);$i++) {
			$tableHTML .= "<th>" . $i . "</th>";
		}
		$tableHTML .= "<th>$planLength Yr Total</th>";
		$tableHTML .= "</thead>";
		$tableHTML .= "<tbody>";
		$tableHTML .= "</tbody>";
		$tableHTML .= "<table>";
		
		return $tableHTML;
	}
	
	/**
	* 	getUnitsByChannelAndCategory
	*
	*  Gets the number of units for the given $channel and $category combination
	*
	*   $channel
	*   $category
	*   $userID 
	*/
	function getUnitsByChannelAndCategory($channel, $category, $userID) {
		$averageBaselineSalesChildFormID = FrmForm::get_id_by_key("uvbxa");
		$averageBaselineSalesCategoryFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_sales_category");
		$averageBaselineSalesChannelFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_sales_channel");
		$averageBaselineSalesUnitsFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_sales_units");
		$averageBaselineSalesRepeaterFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_sales_repeater");
		
		$averageBaselineSalesEntries = FrmEntry::getAll(array('it.form_id' => $averageBaselineSalesChildFormID, 'it.user_id' => $userID), '', '', true);
		foreach ($averageBaselineSalesEntries as $averageBaselineSales) {
			$categoryID = $averageBaselineSales->metas[$averageBaselineSalesCategoryFieldID];
			$channelID = $averageBaselineSales->metas[$averageBaselineSalesChannelFieldID];
			if ($channel == $channelID && $category == $categoryID) {
				return $averageBaselineSales->metas[$averageBaselineSalesUnitsFieldID];
			}
		}
	}
