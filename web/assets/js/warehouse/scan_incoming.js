$(document).ready(function() {

	 //$('#scan').attr('readonly','readonly');
	 destroyScanner(); // Unbind previous listener
	 
	 // Only run if on popup scan mode
	 if ($('#form_scanIncoming').length) {
		 active_scanner = $(window).scannerListener({'element_val':'#form_scanIncoming','trigger':'keypress.incomingscanner','delay':300});

		 $('#form_scanIncoming').unbind('change.scanincoming');
		 $('#form_scanIncoming').on('change.scanincoming',function() {
		 	if ($(this).val().length >= 2) {
				delay(function() {
					$('#quick-scan .loading').show();
					$('#scan-incoming-form').submit();
				}, 300);
			}
		 });

		 // Bind on change of location
		 $('body').unbind('change.scanStock'); // required 
		 $('body').on('change.scanStock', '.incomingScannedProducts select, .incomingScannedProducts input', function() {
		 	$('#quick-scan .loading').show();
			$('#scan-incoming-product-form').submit();
		 });
	 }

	 if ($('#incoming-product-page-form').length) {
		/**
		 * DIALPAD
		 */
	    var dials = $(".dials ol li");
	    var index;
	    var number = $(".dialabs .number");
	    var total;

	    $('#incoming-product-page-form select.form-control').each(function() {
	    	$(this).before('<div class="diallink"><a href="#">'+ ($(this).find(':selected').val()?$(this).find(':selected').text():'Select Location') + '</a></div>');
	    	$(this).hide();
	    });
	    $('.diallink>a').on('click.dialpad',function(e) {
	    	e.preventDefault();
	    	e.stopPropagation();
	    	number.html('');
	    	$(this).parent().append($('.dialabs').removeClass('hide').removeClass('only-levels').removeClass('only-rows').addClass('only-aisles'));
	    });
	    $(document).on('click.dialpad',function() {
	    	number.html('');
	    	$(this).parent().append($('.dialabs').removeClass('only-aisles').removeClass('only-levels').removeClass('only-rows').addClass('hide'));
	    });
	    $('.dialabs').on('click.dialpad',function(e) {
	    	e.stopPropagation();
	    });

	    dials.click(function(){
	    	if ($(this).data('op') == 'clear') {
	    		number.html('');
	    		dials.parents('td').find('select').val('').change();
	    		dials.parents('td').find('.diallink>a').text("Select Location");
	    		$('.dialabs').addClass('hide');
	    	} else if ($(this).data('op') == 'nil') {
	    		
	    	} else {
		    	if ($(this).data('val')) {
		    		index = $(this).data('val');
		    	} else {
		    		index = $(this).find('p>strong').html();
		    	}
		    	number.append(index);
		    }

	    	var dialstring = number.html();
	    	if (dialstring.match(/^[A-Z]$/)) {
	    		// first
	    		number.append(' - ');
	    		$('.dialabs').removeClass('only-levels').removeClass('only-aisles').addClass('only-rows');
	    	} else if (dialstring.match(/^[A-Z]\s-\s[0-9]{1,2}$/)) {
	    		// second
	    		number.append(' - ');
	    		$('.dialabs').removeClass('only-aisles').removeClass('only-rows').addClass('only-levels');
	    	} else if (dialstring.match(/^[A-Z]\s-\s([0-9]{1,2}|[A-Z]+)$/)) {
	    		// X - STAGING....
	    		number.append(' - ');
	    		$('.dialabs').removeClass('only-aisles').removeClass('only-rows').addClass('only-levels');
	    	} else if (dialstring.match(/^[A-Z]\s-\s([0-9]{1,2}|[A-Z]+)\s-\s[0-9]{1}/)) {
	    		// done
	    		var loc_id = dials.parents('td').find('select option').filter(function () { return $(this).html() == dialstring; }).val();
	    		if (loc_id) {
	    			dials.parents('td').find('select').val(loc_id).change();
	    			dials.parents('td').find('.diallink>a').text(dialstring);
	    		} else {
	    			alert('Location '+ dialstring + ' does not exist.');
	    		}
	    		number.html('');
	    		$('.dialabs').addClass('hide');
	    	}
	    });
	};

});