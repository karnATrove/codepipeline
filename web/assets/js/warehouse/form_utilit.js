/**
 * Physically post the form to web service
 *
 * @param $form
 * @param callback
 */
function postForm($form, callback) {
	/*
	 * Get all form values
	 */
	var values = getFormValuesToArray($form);

	/* Show the progress bar */
	var ajaxOptions = {};
	if ($form.find('.progress-bar').length) {
		$('.progress').show();
		$('.progress .progress-bar').css('width', 0);

		// If there is a file field, we want to track it and add more options
		ajaxOptions = {
			async: true,
			processData: false,
			contentType: false,
			data: new FormData($form[0]),
			xhr: function () {
				// get the native XmlHttpRequest object
				var xhr = $.ajaxSettings.xhr();
				// set the onprogress event handler
				xhr.upload.onprogress = function (evt) {
					$('.progress .progress-bar').css('width', (evt.loaded / evt.total * 100) + '%');
				};
				// set the onload event handler
				xhr.upload.onload = function () {
					$('.progress').hide();
				};
				// return the customized object
				return xhr;
			}
		};
	}

	/*
	 * Throw the form values to the server!
	 */
	$.ajax(jQuery.extend({
		type: $form.attr('method'),
		url: $form.attr('action'),
		data: values,
		cache: false,
		success: function (data) {
			callback(data, $form);

		},
		error: function (response, desc, err) {
			if (response.responseJSON && response.responseJSON.message) {
				alert(response.responseJSON.message);
			}
			else {
				alert(desc);
			}
		}
	}, ajaxOptions));
}

/**
 * Post a link
 *
 * @param link     The link
 * @param ajaxOptions
 * @param      {Function}  callback  The callback
 */
function ajaxLink(link, ajaxOptions, callback) {
	/*
	 * Throw the link to the server!
	 */
	$.ajax(jQuery.extend({
		type: 'GET',
		url: link.attr('href'),
		data: {},
		cache: false,
		success: function (data) {
			callback(data, link);
		},
		error: function (response, desc, err) {
			if (response.responseJSON && response.responseJSON.message) {
				alert(response.responseJSON.message);
			}
			else {
				alert(desc);
			}
		}
	}, ajaxOptions));
}

/**
 * Variety of handing functions for when ajax returns a response of actions
 *
 * @param response
 */
function handleAjaxResponse(response) {
	$(response.ajaxCommand).each(function (i, e) {
		switch (response.ajaxCommand[i].op) {
			case 'replaceWith':
				$(response.ajaxCommand[i].selector).replace(response.ajaxCommand[i].value);
				break;
			case 'html':
				$(response.ajaxCommand[i].selector).html(response.ajaxCommand[i].value);
				break;
			case 'append':
				$(response.ajaxCommand[i].selector).append(response.ajaxCommand[i].value);
				break;
			case 'prepend':
				$(response.ajaxCommand[i].selector).prepend(response.ajaxCommand[i].value);
				break;
			case 'before':
				$(response.ajaxCommand[i].selector).before(response.ajaxCommand[i].value);
				break;
			case 'after':
				$(response.ajaxCommand[i].selector).after(response.ajaxCommand[i].value);
				break;
			case 'remove':
				$(response.ajaxCommand[i].selector).remove();
				break;
			case 'hide':
				$(response.ajaxCommand[i].selector).hide();
				break;
			case 'show':
				$(response.ajaxCommand[i].selector).show();
				break;
			case 'alert':
				alert(response.ajaxCommand[i].value);
				break;
			case 'value':
				$(response.ajaxCommand[i].selector).val(response.ajaxCommand[i].value);
				break;
			case 'attr':
				for (var index in response.ajaxCommand[i].value) {
					$(response.ajaxCommand[i].selector).attr(index, response.ajaxCommand[i].value[index]);
				}
				break;
			case 'modal':
				$(response.ajaxCommand[i].selector).modal(response.ajaxCommand[i].value);
				break;
			case 'invoke':
				var fn = window[response.ajaxCommand[i].value];
				if (typeof(fn) === 'function') {
					if (typeof(response.ajaxCommand[i].params) !== 'undefined') {
						fn(response.ajaxCommand[i].params);
					} else {
						fn();
					}
				}
				break;
			case 'log':
				console.log(response.ajaxCommand[i].value);
				break;
			case 'redirect':
				window.location = response.ajaxCommand[i].value;
				break;
			case 'noty':
				var params = response.ajaxCommand[i].params;
				var type = 'success';
				if (params.type !== null) {
					type = params.type;
				}
				new Noty({
					theme: 'relax',
					type: type,
					layout: 'topLeft',
					text: response.ajaxCommand[i].value,
					timeout: 2000
				}).show();
				break;
		}
	});
}

/**
 * Loop through a form element and obtain all its current values.
 * @param  {[type]} $element [description]
 * @return {[type]}          [description]
 */
function getFormValuesToArray($element) {
	var values = {};
	$.each($element.serializeArray(), function (i, field) {
		values[field.name] = field.value;
	});

	// append buttons
	if ($(document.activeElement).is('button')) {
		values[$(document.activeElement).attr('name')] = $(document.activeElement).text();
	}

	return values;
}