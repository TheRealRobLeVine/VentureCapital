<?php
	add_shortcode('sales-projections-report', 'salesProjectionsReport');
	
	/**
	* 	initializeSalesProjections
	*
	*  When the "Price By Product Category and Sales Channel" form is saved
	*    initialize the categories, channels and pricing in the Sales Projections form
	*
	*/
	function salesProjectionsReport($atts = [], $content = null, $tag = '') {
		global $wpdb;
		
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
		$html_data = null;
		
		// fill in default attributes
		$atts = shortcode_atts(
			array(
				'mode' => 'bychannel'
			), $atts, $tag
		);

		$include_meta = true;
		$byChannel =  ($atts['mode'] == 'bychannel') ;
		
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
				$categories[$category->id] = $category->metas[$planCategoryFieldID]; 
		}
		$allChannelEntries = FrmEntry::getAll(array('it.form_id' => $planChannelChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		foreach($allChannelEntries as $channel) {
				$channels[$channel->id] = $channel->metas[$planChannelFieldID]; ;
		}
		error_log(print_r($categories,true));
		error_log(print_r($channels,true));

		// Using the Price by Product Category & Sales Channel form
		//		get lists of the categories, channels
		// 	make an array of the existing category-channel combinations
		
		$priceCategoryFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_product_category");
		$priceChannelFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_sales_channel");
		$unitPriceFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_unit_price");
		$resellerMarginFieldID = FrmField::get_id_by_key("financialplan-pricebyproductcategorysaleschannel_reseller_margin");
		$allPriceAndMarginEntries = FrmEntry::getAll(array('it.form_id' => $priceByCategoryAndChannelChildFormID, 'it.user_id' => $userID), '', '', $include_meta);
		$userID = get_current_user_id();
		
		$projections = array();
		if ($byChannel) {
			error_log("bychannel");
			foreach($channels as $channelID => $channelName) {
				$projectionsData = new StdClass;
				$projectionsData->id = $channelID;
				$projectionsData->name = $channelName;
				$projectionsData->units_data = array();
				foreach($allPriceAndMarginEntries as $row) {
					if ($row->metas[$priceChannelFieldID] == $channelID) {
						$units_data = new StdClass;
						$units_data->y_axis_ID = $row->metas[$priceCategoryFieldID];
						$units_data->y_axis_Name = $categories[$units_data->y_axis_ID];
						$units_data->unitPrice = $row->metas[$unitPriceFieldID];
						$units_data->margin = $row->metas[$resellerMarginFieldID];
						$units_data->units = getUnitsByChannelAndCategory($channelID, $units_data->y_axis_ID, $userID);
						$projectionsData->units_data[] = $units_data;
					}
				}
				$projections[] = $projectionsData;
			}
			$percentGrowthChildFormID = FrmForm::get_id_by_key("n6kck");
			$percentGrowthFieldPrefix = "financialplan-salesforecasting_percent_growth_sales_channel_year_";
		}
		else {
			error_log("bycategory");
			foreach($categories as $categoryID => $categoryName) {
				$projectionsData = new StdClass;
				$projectionsData->id = $categoryID;
				$projectionsData->name = $categoryName;
				$projectionsData->units_data = array();
				foreach($allPriceAndMarginEntries as $row) {
					if ($row->metas[$priceCategoryFieldID] == $categoryID) {
						$units_data = new StdClass;
						$units_data->y_axis_ID = $row->metas[$priceChannelFieldID];
						$units_data->y_axis_Name = $channels[$units_data->y_axislID];
						$units_data->unitPrice = $row->metas[$unitPriceFieldID];
						$units_data->margin = $row->metas[$resellerMarginFieldID];
						$units_data->units = getUnitsByChannelAndCategory($units_data->y_axis_ID, $categoryID, $userID);
						$projectionsData->units_data[] = $units_data;
					}
				}
				$projections[] = $projectionsData;
			}
			$percentGrowthChildFormID = FrmForm::get_id_by_key("lqnwq");
			$percentGrowthFieldPrefix = "financialplan-salesforecasting_percent_growth_sales_category_year_";
		}
		error_log(print_r($projections, true));
		$tableHTML = "";
		
		foreach($projections as $projections) {
			if (empty($projections->units_data)) {
				continue;
			}
			$tableHTML .= "<table>";
			$tableHTML .= "<thead>";
			$tableHTML .= "<th>Category</th>";
			$tableHTML .= "<th>Baseline</th>";
			for($i=$planStartYear;$i<($planStartYear+$planLength);$i++) {
				$tableHTML .= "<th>" . $i . "</th>";
			}
			$tableHTML .= "<th>$planLength Yr Total</th>";
			$tableHTML .= "</thead>";
			$tableHTML .= "<tbody>";
			$tableHTML .= "<tr>";
			$tableHTML .= "<td>" . $projections->name . "</td>";
			$tableHTML .= "<td>&nbsp;</td>";
			for($i=$planStartYear;$i<($planStartYear+$planLength);$i++) {
				$tableHTML .= "<td>&nbsp;</td>";
			}
			$tableHTML .= "<td>&nbsp;</td>";
			$tableHTML .= "</tr>";
			
			foreach($projections->units_data as $units_data) {
				$tableHTML .= "<tr>";
				$tableHTML .= "<td>$units_data->y_axis_Name </td>";
				
				// Sales data
				$units = $units_data->units;
				$unitPrice = $units_data->unitPrice;
				$tableHTML .= "<td>$units</td>";
				$subtotalUnits[0] = $units;
				$units_data->growth = getGrowthByChannelOrCategory($planStartYear, $planLength, $percentGrowthChildFormID, $percentGrowthFieldPrefix, $userID);
				$totalUnits = 0;
				$subtotalUnits = array();
				for($i=$planStartYear;$i<($planStartYear+$planLength);$i++) {
					$newUnits = ($units * ("1." . $units_data->growth[$i]));
					$tableHTML .= "<td>". number_format($newUnits) . "</td>";
					$units = $newUnits;
					$totalUnits += $newUnits;
					$subtotalUnits[$i] = $newUnits;
				}
				$tableHTML .= "<td>" . number_format($totalUnits) . "</td>";
				$tableHTML .= "</tr>";

				$tableHTML .= blankRow(2+$planLength+1);
				
				// Growth Rate
				$tableHTML .= "<tr>";
				$tableHTML .= "<td>Growth Rate</td>";
				$tableHTML .= "<td>&nbsp;</td>";
				for($i=$planStartYear;$i<($planStartYear+$planLength);$i++) {
					$growthRate = $units_data->growth[$i] . "%";
					$tableHTML .= "<td>$growthRate</td>";
				}
				$tableHTML .= "<td>&nbsp;</td>";
				$tableHTML .= "</tr>";

				// Subtotal UNITS
				$tableHTML .= "<tr>";
				$tableHTML .= "<td>Subtotal Units</td>";
				$tableHTML .= "<td>" . isset($subtotalUnits[0]) ? $subtotalUnits[0] : "0" . "</td>";
				for($i=$planStartYear;$i<($planStartYear+$planLength);$i++) {
					$units = number_format($subtotalUnits[$i]);
					$tableHTML .= "<td>$units</td>";
				}
				$tableHTML .= "<td>&nbsp;</td>";
				$tableHTML .= "</tr>";
			}
			
			$tableHTML .= "</tbody>";
			$tableHTML .= "</table>";
		}
		
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
	
	/**
	* 	getGrowthByChannelOrCategory
	*
	*  Gets the growth for each year for a given channel or category
	*
	*   $planStartyear
	*   $planLength
	*   $channelOrCategoryID - channel or category ID
	*   $useChannel - is the ID a channel or a category
	*   $userID - 
	*/
	function getGrowthByChannelOrCategory($planStartYear, $planLength, $childFormID, $growthFieldPrefix, $userID) {
		$growthEntries = FrmEntry::getAll(array('it.form_id' => $childFormID, 'it.user_id' => $userID), '', '', true);
		$growthRates = array();
		foreach ($growthEntries as $growthEntry) {
			$iIndex = 1;
			for($i=$planStartYear;$i<($planStartYear+$planLength);$i++) {
				$fieldKey =  $growthFieldPrefix . $iIndex++;
				error_log("field key: $fieldKey");
				$fieldID = FrmField::get_id_by_key($fieldKey);
				$growthRates[$i] = $growthEntry->metas[$fieldID];
				error_log("growth rate: " . $growthRates[$i]);
			}
		}
		return $growthRates;
	}

	/**
	* 	blankRow
	*
	*  Creates a new, blank row for an HTML table
	*
	*   $numColumns - number of columns in the row
	*/
	function blankRow($numColumns) {
		$html = "";

		$html .= "<tr>";
		for($i=0;$i<$numColumns;$i++) {
			$html .= "<td>&nbsp;</td>";
		}
		$html .= "</tr>";
		
		return $html;
	}
