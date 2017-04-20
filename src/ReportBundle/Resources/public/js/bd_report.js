$(function () {
	$('input[name="daterange"]').daterangepicker({
		ranges: {
			'Today': [moment(), moment()],
			'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	}, function (start, end, label) {
		$('<input />').attr('type', 'hidden')
			.attr('name', 'start')
			.attr('value', start.format('YYYY-MM-DD'))
			.appendTo('#search_form');
		$('<input />').attr('type', 'hidden')
			.attr('name', 'end')
			.attr('value', end.format('YYYY-MM-DD'))
			.appendTo('#search_form');
		var form = $('#search_form');
		postForm(form, function (response, $form) {
			if (typeof(response.ajaxCommand) !== 'undefined') {
				handleAjaxResponse(response);
			}
		});
	});
});