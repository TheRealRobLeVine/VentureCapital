	/**
	* 	initializeSalesForecast
	*
	*  When the "Price By Product Category and Sales Channel" form is saved
	*    initialize the categories, channels and pricing in the Sales Forecast form
	*
	*/
	function initializeSalesForecast() {
		global $wpdb;
		$include_meta = true;
		
		$planSetupFormID = FrmForm::get_id_by_key("financialplan-plansetup");
		error_log("plansetupformid: $planSetupFormID");        
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
        if (empty($currentPlan)) {
        	// no plans for the current user
			error_log("no current plan - exiting");
        	die;
        }
        $firstIndex = array_key_first($currentPlan);
		$planLength = $currentPlan[$firstIndex]->metas[$planLengthFieldID];
		$planStartYear = $currentPlan[$firstIndex]->metas[$planStartYearFieldID];
		error_log("planstartyear: $planStartYear and planlength: $planLength");		
		$allCategoryEntries = FrmEntry::getAll(array('it.form_id' => $planCategoryChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		foreach($allCategoryEntries as $category) {
				$categories[] = $category->id; //$category->metas[$planCategoryFieldID];
		}
		error_log("Categories");
		error_log(print_r($categories, true));
		$allChannelEntries = FrmEntry::getAll(array('it.form_id' => $planChannelChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		foreach($allChannelEntries as $channel) {
				$channels[] = $channel->id; //$channel->metas[$planChannelFieldID];
		}
		error_log("Channels");
		error_log(print_r($channels, true));
		
		// Using the Price by Product Category & Sales Channel form
		//		get lists of the categories, channels
		// 	make an array of the existing category-channel combinations
		
		$priceCategoryFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_product_category");
		$priceChannelFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_sales_channel");
		$unitPriceFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_unit_price");
		$resellerMarginFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_reseller_margin");
		$allPriceAndMarginEntries = FrmEntry::getAll(array('it.form_id' => $priceByCategoryAndChannelChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		$existing_category_channel_combos = array();  // format = category # : channel # => unit price. e.g., 171:990 => 50.00
		foreach($allPriceAndMarginEntries as $row) {
			$categoryID = $row->metas[$priceCategoryFieldID];
			$channelID = $row->metas[$priceChannelFieldID];
			$unitPrice = $row->metas[$unitPriceFieldID];
			$combo = $categoryID . "~" .  $channelID;
			$existing_category_channel_combos[$combo] = $unitPrice;
		}
		error_log("Existing Category Channel Combos");		
		error_log(print_r($existing_category_channel_combos, true));
		
		// For the Sales Forecast form
		$salesForecastFormID = FrmForm::get_id_by_key("financialplan-salesforecasting");
		$salesForecasts = FrmEntry::getAll(array('it.form_id' => $salesForecastFormID, 'it.user_id' => $userID), '', '', $include_meta);
		error_log("Sales Forecast found?" . (count($salesForecasts) > 0 ? 'Yes' : 'No'));		
		if ((count($salesForecasts) <= 0)) {
			error_log("No sales forecast found - exiting");
			die;
		}
		
       	$salesForecast = $salesForecasts[array_key_first($salesForecasts)];
		
		// get a list of existing categories and channels used
		$averageBaselineSalesChildFormID = FrmForm::get_id_by_key("uvbxa");
		$averageBaselineSalesCategoryFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_sales_category");
		$averageBaselineSalesChannelFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_sales_channel");
		$averageBaselineSalesEntries = FrmEntry::getAll(array('it.form_id' => $averageBaselineSalesChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		$categories_and_channels_to_remove = array();
		$categories_and_channels_to_add = array();
		if (count($averageBaselineSalesEntries) > 0) {
			foreach ($averageBaselineSalesEntries as $averageBaselineSales) {
				$entryID = $averageBaselineSales->id;
				$categoryID = $averageBaselineSales->metas[$averageBaselineSalesCategoryFieldID];
				$channelID = $averageBaselineSales->metas[$averageBaselineSalesChannelFieldID];
				$combo = $categoryID . "~" . $channelID;
				$used_category_channel_combos[$combo] = $entryID;
				if (!array_key_exists($combo, $existing_category_channel_combos)) {
					$categories_and_channels_285
						[$combo] = '0.00';
				}
			}
			foreach($used_category_channel_combos as $combo => $entryID) {
				if (!array_key_exists($combo, $existing_category_channel_combos)) {
					$categories_and_channels_to_remove[$combo] = $entryID;
				}
			}
		}
		else {
			$categories_and_channels_to_add = $existing_category_channel_combos;
		}
		error_log("Category/Channels to Add");
		error_log(print_r($categories_and_channels_to_add, true));
		error_log("Category/Channels to Remove");
		error_log(print_r($categories_and_channels_to_remove, true));

		// For the Average/Baseline Sales section
        //  add repeater rows for the cat-chan combinations that don't already have one
        //  remove any repeater rows that have a cat-chan combination that no longer exists
		$averageBaselineSalesUnitsFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_sales_units");
		$averageBaselineSalesPricePerUnitFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_sales_price_per_unit");
		$averageBaselineSalesRepeaterFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_sales_repeater");
		foreach($categories_and_channels_to_add as $catChannel => $price_per_unit) {
			$data = explode("~", $catChannel);
			$categoryID = $data[0];
			$channelID = $data[1];
			$values = array(
					$averageBaselineSalesCategoryFieldID => $categoryID,
					$averageBaselineSalesChannelFieldID => $channelID,
					$averageBaselineSalesUnitsFieldID => 0,
					$averageBaselineSalesPricePerUnitFieldID => $price_per_unit
				  );
			error_log("adding row to Average/Baseline Sales section");
			addChildRow($salesForecast, $averageBaselineSalesRepeaterFieldID, $averageBaselineSalesChildFormID, $values);
			error_log("after adding row to Average/Baseline Sales section");
		}
/*		foreach($categories_and_channels_to_remove as $catChannel => $entry_id) {
			$data = explode("~", $catChannel);
			$categoryID = $data[0];
			$channelID = $data[1];
			removeChildRow($salesForecast, $averageBaselineSalesRepeaterFieldID, $entry_id);
		}
*/		

		// For the Growth Projection section
        //  add repeater rows for the "Percent Growth by Year" for the channels and categories
		$percentGrowthByCategoryChildFormID = FrmForm::get_id_by_key("lqnwq");
		$percentGrowthByCategoryRepeaterFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_growth_category_repeater");
		$percentGrowthByCategoryCategoryFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_growth_by_category_category");
		$percentGrowthByCategoryYearFieldPrefix = "financialplan-salesforecasting_growth_category_year_";
		$percentGrowthByCategoryEntries = FrmEntry::getAll(array('it.form_id' => $percentGrowthByCategoryChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		$categories_to_add = array();
		$categories_to_remove = array();
		if (count($percentGrowthByCategoryEntries) > 0) {
			foreach($percentGrowthByCategoryEntries as $entry) {
				$categoryID = $entry->metas[$percentGrowthByCategoryCategoryFieldID];
				$used_categories[] = array($categoryID => $entry->id);
				if (!in_array($categoryID, $categories)) {
					$categories_to_add[] = $categoryID;
				}
			}
			foreach($used_categories as $categoryID => $entryID) {
				if (!array_key_exists($categoryID, $categories)) {
					$categories_to_remove[] = $entryID;
				}
			}
		}
		else {
			$categories_to_add = $categories;
		}
		error_log("Categories to Add");
		error_log(print_r($categories_to_add, true));
		
		foreach($categories_to_add as $categoryID) {
			$values = array(
					$percentGrowthByCategoryCategoryFieldID => $categoryID
				  );
			addChildRow($salesForecast, $percentGrowthByCategoryRepeaterFieldID, $percentGrowthByCategoryChildFormID, $values);
		}
		
		// set the year values
		foreach($categories as $category) {
			setPercentGrowthYears($planStartYear, $percentGrowthByCategoryChildFormID, $percentGrowthByCategoryYearFieldPrefix, $percentGrowthByCategoryCategoryFieldID, $category);
		}

		foreach($categories_to_remove as $entry_id) {
			removeChildRow($salesForecast, $percentGrowthByCategoryRepeaterFieldID, $entry_id);
		}

		$percentGrowthByChannelChildFormID = FrmForm::get_id_by_key("n6kck");
		$percentGrowthByChannelRepeaterFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_growth_channel_repeater");
		$percentGrowthByChannelChannelFieldID = FrmField::get_id_by_key("financialplan-salesforecasting_growth_by_channel_channel");
		$percentGrowthByChannelYearFieldPrefix = "financialplan-salesforecasting_growth_channel_year_";
		$percentGrowthByChannelEntries = FrmEntry::getAll(array('it.form_id' => $percentGrowthByChannelChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		$channels_to_add = array();
		$channels_to_remove = array();
		if (count($percentGrowthByChannelEntries) > 0) {
			foreach($percentGrowthByChannelEntries as $entry) {
				$channelID = $entry->metas[$percentGrowthByChannelChannelFieldID];
				$used_channels[] = array($channelID => $entry->id);
				if (!in_array($channelID, $channels)) {
					$channels_to_add[] = $channelID;
				}
			}
			foreach($used_channels as $channelID => $entryID) {
				if (!array_key_exists($channelID, $channels)) {
					$channels_to_remove[] = $entryID;
				}
			}
		}
		else {
			$channels_to_add = $channels;
		}
		error_log("Channels to Add");
		error_log(print_r($channels_to_add, true));

		foreach($channels_to_add as $channelID) {
			$values = array(
				$percentGrowthByChannelChannelFieldID => $channelID
			);
			addChildRow($salesForecast, $percentGrowthByChannelRepeaterFieldID, $percentGrowthByChannelChildFormID, $values);
		}
		// set the year values
		// set the year values
		foreach($channels as $channel) {
			setPercentGrowthYears($planStartYear, $percentGrowthByChannelChildFormID, $percentGrowthByChannelYearFieldPrefix, $percentGrowthByChannelChannelFieldID, $channel);
		}
		
		foreach($channels_to_remove as $entry_id) {
			removeChildRow($salesForecast, $percentGrowthByChannelRepeaterFieldID, $entry_id);
		}

	}
	
	/**
	* 	addChildRow
	*
	*  Adds a child repeater row which also entails adding it from the list of rows stored in the parent items repeater field
	*
	*  $parent_entry - entry object of the parent entry
	*  $parentRepeaterFieldID - field ID of the repeater in the parent form
	*  $childFormID - child form to add a row to
	*  $values - values to add to the child row
	*
	*/
	function addChildRow($parent_entry, $parentRepeaterFieldID, $childFormID, $values) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$newChildEntryID = FrmEntry::create(array(
			  'form_id' => $childFormID, 
			  'item_key' => FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_items', 'item_key' ),
			  'frm_user_id' => get_current_user_id(), 
			  'item_meta' => $values
			));

		error_log("addChildRow newChildEntryID: $newChildEntryID");
		$updated = $wpdb->update( $prefix . "frm_items", array("parent_item_id" => $parent_entry->id, "post_id" => 0), array("id" =>  $newChildEntryID), null, null );
		error_log("addChildRow parent_item_id updated: $updated");
		// add the new repeater child row to the parent entry that stores a serialized list of children
		$serializedChildEntryList = isset($parent_entry->metas[$parentRepeaterFieldID]) ? $parent_entry->metas[$parentRepeaterFieldID] : array();
		$serializedChildEntryList[] = $newChildEntryID;
		error_log("serializedChildEntryList: " . print_r($serializedChildEntryList, true));
		$updated = FrmEntryMeta::update_entry_meta( $parent_entry->id, $parentRepeaterFieldID, null, $serializedChildEntryList );
		if (!$updated) {
			$updated = FrmEntryMeta::add_entry_meta( $parent_entry->id, $parentRepeaterFieldID, null, $serializedChildEntryList );
		}
		error_log("addChildRow child list updated: $updated");
	}

	/**
	* 	removeChildRow
	*
	*  Removes a child repeater row which entails removing it from the list of rows stored in the parent items repeater field
	*
	*  $parent_entry - entry object of the parent entry
	*  $parentRepeaterFieldID - field ID of the repeater in the parent form
	*  $childEntryID - ID of the child entry to delete
	*
	*/
	function removeChildRow($parent_entry, $parentRepeaterFieldID, $childEntryID ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$serializedChildEntryList = $parent_entry->metas[$parentRepeaterFieldID];
		foreach($serializedChildEntryList as $key => $value) {
			if ($value == $childEntryID) {
				unset($serializedChildEntryList[$key]);
				break;
			}
		}
		$updated = FrmEntryMeta::update_entry_meta( $parent_entry->id,  $parentRepeaterFieldID, null, $serializedChildEntryList );
		
		// delete the row
		$deleted = FrmEntry::destroy($childItemID);
	}
	
	/**
	* 	setPercentGrowthYears
	*
	*  Sets the the year values for each row in the product growth section
	*
	*  $startYear - Year 1
	*  $childFormID - ID of the form that stores the repeater rows for the section
	*  $yearFieldKeyPrefix - prefix of the key to delineate each year from the next
	*  $objectFieldID - id of the field to match to make sure you're only doing one channel/category at a time
	*  $objectValue - value to compare
	*
	*/
	function setPercentGrowthYears($startYear, $childFormID, $yearFieldKeyPrefix, $objectFieldID, $objectValue) {
		$userID = get_current_user_id();
		$year = $startYear;
		$childEntries = FrmEntry::getAll(array('it.form_id' => $childFormID, 'it.user_id' => $userID), '', '', true);
		foreach($childEntries as $entry) {
			if ($entry->metas[$objectFieldID] == $objectValue) {
				for($i=1;$i<=PLAN_MAX_YEARS;$i++) {
					$fieldKey =  $yearFieldKeyPrefix . $i;
					$fieldID = FrmField::get_id_by_key($fieldKey);
					$updated = FrmEntryMeta::update_entry_meta( $entry->id, $fieldID, null, $year );
					if (!$updated) {
						$updated = FrmEntryMeta::add_entry_meta( $entry->id, $fieldID, null, $year );
					}
					error_log("setPercentGrowthYears updated $fieldKey");
					$year++;
				}
			}
		}
	}
