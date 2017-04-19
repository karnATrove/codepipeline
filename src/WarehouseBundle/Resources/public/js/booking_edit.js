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
});