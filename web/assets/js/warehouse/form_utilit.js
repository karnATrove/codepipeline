/**
 * Auto refresh
 */
var autoRefresh;
var refreshPause = false;
var refresher;

// Enable/Disable inline autorefresh
$('[data-autorefresh="on"]').each(function() {
	autoRefresh($(this),true);
});

/**
 * Auto Refresh an element/table/etc.
 * data-autourl|data-autoselector|data-autotime
 * @param  {[type]} $element  [description]
 * @param  {[type]} autoStart [description]
 * @return {[type]}           [description]
 */
function autoRefresh($element,autoStart) {
	if ($element.data('autourl') && $element.data('autoselector')) {
		var $refresher = $('<a href="javascript:" class="refresh-toggle"></a>');
		if (autoStart) {
			$refresher.append('<i class="fa fa-refresh btn btn-success"> on</i>');
			autoRefreshOp($element,'init');
		} else {
			$refresher.append('<i class="fa fa-refresh btn btn-danger"> off</i>');
			refreshPause = true;
		}
		
		$refresher.on('click',function(e) {
			e.preventDefault();
			if (refreshPause) {
				refreshPause = false;
				autoRefreshOp($element,'start');
				$refresher.find('i').removeClass('btn-danger').addClass('btn-success').text(' on');
			} else {
				refreshPause = true;
				autoRefreshOp($element,'pause');
				$refresher.find('i').removeClass('btn-success').addClass('btn-danger').text(' off');
			}
		});
		$('#refresher').append($refresher);
	}
}

function autoRefreshOp($element,op) {
	switch(op) {
		case 'init':
		case 'start':
			refresher = setInterval(function(){
				if (!$element.find('input:focus').length && !refreshPause) {
					ajaxUpdateRefresh($element.data('autoselector'),$element.data('autourl'));
				}
			}, ($element.data('autotime') ? $element.data('autotime') : 30000));
			break;
		case 'pause':
			clearInterval(refresher);
			refresher = null;
			break;
	}
}

/**
 * Refresh a page automatically
 * @param  {[type]} wrapper [description]
 * @return {[type]}         [description]
 */
function ajaxUpdateRefresh(selector,url) {
	var data = {'selector': selector};
	$.ajax({
		url: url,
		data: data,
		type: 'GET',
		dataType: 'json',
		success: function (response) {
			if (typeof(response.ajaxCommand) !== 'undefined') {
				handleAjaxResponse(response);
  			} else {
				new Noty({
					theme: 'relax',
					type: 'error',
					layout: 'topLeft',
					text: 'Error trying to auto refresh.',
					timeout: 2000
				}).show();
			}
		},
		error: function (response, desc, err) {
			if (response.responseJSON && response.responseJSON.message) {
				new Noty({
					theme: 'relax',
					type: 'error',
					layout: 'topLeft',
					text: response.responseJSON.message,
					timeout: 2000
				}).show();
			}
			else {
				new Noty({
					theme: 'relax',
					type: 'error',
					layout: 'topLeft',
					text: desc,
					timeout: 2000
				}).show();
			}
		}
	});
}

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
				$(response.ajaxCommand[i].selector).replaceWith(response.ajaxCommand[i].value);
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
			case 'attribute':
			case 'attr':
				for (var index in response.ajaxCommand[i].value) {
					$(response.ajaxCommand[i].selector).attr(index, response.ajaxCommand[i].value[index]);
				}
				break;
			case 'addClass':
                $(response.ajaxCommand[i].selector).addClass(response.ajaxCommand[i].value);
                break;
            case 'removeClass':
                $(response.ajaxCommand[i].selector).removeClass(response.ajaxCommand[i].value);
                break;
            case 'blink':
                for(i=0;i<2;i++) {
				    $(response.ajaxCommand[i].selector).fadeTo('slow', 0.5).fadeTo('slow', 1.0);
				}
            	break;
			case 'modal':
				$(response.ajaxCommand[i].selector).modal(response.ajaxCommand[i].value);
				break;
			case 'notice':
			case 'noty':
 				var params = typeof(response.ajaxCommand[i].params) !== "undefined" ? response.ajaxCommand[i].params : {};
 				var type = response.ajaxCommand[i].type !== null ? response.ajaxCommand[i].type : 'success';
 				if (params.type !== null && typeof(params.type) !== "undefined") {
 					console.log(params.type);
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