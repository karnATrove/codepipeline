$(function () {

	// Trigger scan picking mode to load.
	$('#booking_pick_trigger').on('click', function (e) {
		e.preventDefault();
	});

	/**
	 * Load product being bound for picking.
	 */
	$('#picking-modal').on('shown.bs.modal', function (event) {
		$('#picking-modal #picking-result').html('');
		$('#picking-modal .loading').show();
		var $link = $(event.relatedTarget);
		$link.attr('href', $link.data('href'));
		ajaxLink($link, {}, function (response, $link) {
			if (typeof(response.ajaxCommand) !== 'undefined') {
				handleAjaxResponse(response);
			}
			$('#picking-modal .loading').hide();
		});
	});

	$('#booking_futureship').datetimepicker({
		'format': 'YYYY-MM-DD'
	});

	$('#bulk_form').submit(function(e) {
		var msg = "";
		if($(this).find('select[name="form[action]"]').val() === 'printWithDocuments') {
			var bookings = $(this).find('input:checked');
			var alreadyPrinted = [];
			bookings.each(function(i, x) {
				if($(x).data('printed') !== "" && $(x).data('printed') !== "0") {
					alreadyPrinted.push($(x).data('order'));
				}
			});
			if(alreadyPrinted.length) {
				msg = "The following orders are flagged meaning the paperwork has been printed out. You are attempting to print them again. Are you sure you want to proceed?\n\n";
				msg += "Orders: " + alreadyPrinted.join(", ");
			}
			else {
				msg = "Are you sure?"
			}
		}
		else {
			msg = "Are you sure?"
		}
		if(!confirm(msg)) {
			e.preventDefault();
		}
	})
});