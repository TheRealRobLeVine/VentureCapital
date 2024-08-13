<script type="text/javascript">

	jQuery(document).ready(function($) {
		if ($("[name='frm_action']").val() == "update" && !$("a.frm_save_draft").length) {
			$(".frm_repeat_buttons").css("display", "none");
			$("input").attr('readonly', true).css("pointer-events", "none");
			$(".frm_opt_container").css("pointer-events", "none");
		}
	});

</script>