$(function () {
	$('#incoming_eta').datetimepicker({
		'format': 'YYYY-MM-DD'
	});
	$('#incoming_scheduled').datetimepicker({
		'format': 'YYYY-MM-DD h:mm:ss a',
		'sideBySide': true
	});

	$('#is_complete_sel').change(function () {
		var form = $('#search_form');
		form.submit();
	});

	$('#number_per_page_sel').change(function () {
		var form = $('#search_form');
		form.submit();
	});


	var calendar = $('#calendar');
	var dataUrl = calendar.data('url');
	var eventClickUrl = calendar.data('event-url');
	calendar.fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: ''
			// right: 'month,agendaWeek,agendaDay,listMonth'
		},
		selectable: true,
		selectHelper: true,
		events: function (start, end, timezone, callback) {
			$.ajax({
				url: dataUrl,
				type: 'post',
				data: {
					start: start.unix(),
					end: end.unix()
				},
				dataType: 'json',
				success: function (doc) {
					var events = [];
					if (!!doc) {
						$.map(doc, function (r) {
							events.push({
								title: r.title,
								start: r.start,
								end: r.end,
								allDay: true,
								color: r.color,
								id: r.id
							});
						});
					}
					callback(events);
				},
				error: function (response, desc, err) {
					if (response.responseJSON && response.responseJSON.message) {
						alert(response.responseJSON.message);
					}
					else {
						alert(desc);
					}
				}
			});
		},
		eventClick: function (calEvent, jsEvent, view) {
			$.ajax({
				url: eventClickUrl,
				type: 'post',
				data: {
					id: calEvent.id
				},
				dataType: 'json',
				success: function (response) {
					window.location.href = response.url;
				},
				error: function (response, desc, err) {
					if (response.responseJSON && response.responseJSON.message) {
						alert(response.responseJSON.message);
					}
					else {
						alert(desc);
					}
				}
			});
		}
	});
});