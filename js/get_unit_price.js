<!-- Get the unit price -->
<script>
jQuery(document).ready(function($) {
	"use strict";

	function setUpChangeEvents() {
		$("[id^='field_financialplan-salesforecasting_sales_category-']").on("change", (function() {
			var categoryID = $(this).children("option:selected").val();
			var id = $(this).attr("id");
			var lastIndex = id.lastIndexOf("-");
			var nth = id.substring(id.lastIndexOf("-")+1);
			var channelID = $("#field_financialplan-salesforecasting_sales_channel-" + nth).children("option:selected").val();
			if (channelID != null && channelID.length>0) {
				getUnitPrice(channelID, categoryID, nth);
			}
		}));
		
		$("[id^='field_financialplan-salesforecasting_sales_channel-']").on("change", (function() {
			var channelID = $(this).children("option:selected").val();
			var id = $(this).attr("id");
			var lastIndex = id.lastIndexOf("-");
			var nth = id.substring(id.lastIndexOf("-")+1);
			var categoryID = $("#field_financialplan-salesforecasting_sales_category-" + nth).children("option:selected").val();
			if (categoryID != null && categoryID.length > 0) {
				getUnitPrice(channelID, categoryID, nth);
			}
		}));
	}
	
	setUpChangeEvents();
	
	/** 
	  * Name: getUnitPrice
	  * Desc:  Makes an Ajax call to get the unit price for the given channel and category
	  * Params:
	  * 	channelID - channel
	  *	categoryID - category
	  *	nth - number of the repeater element
	  **/
	function getUnitPrice(channelID, categoryID, nth) {
		$.ajax({
				  type: "GET",
				  url: "/wp-content/themes/salient-child/scripts/unit_price.php?channel="+channelID+"&category="+categoryID,
				  dataType: "TEXT",
			}).done(function (price) {
				$("#field_financialplan-salesforecasting_sales_price_per_unit-"+nth).val(price);
			});
	}
	
	/* Each time a new row is added, you need to re-setup the change events */
	jQuery(document).on('frmAfterAddRow', setUpChangeEvents );
});
</script>