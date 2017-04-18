$(document).ready(function() {

	/**
	 * Lock main scanner and create alternative
	 */
	 //if (!$('.form-control-feedback').length) {
	 //	$('#scan').after('<a class="fa fa-lock form-control-feedback right" aria-hidden="true"></a>');
	 //}
	 //$('#scan').attr('readonly','readonly');
	 //destroyScanner(); // Unbind previous listener
	 //active_scanner = $(window).scannerListener({'element_val':'#form_scanPicking','trigger':'keypress.incomingscanner','delay':300});
	 

	 /*
	 var key_stroke_start = key_current_time = 0;
	 var key_stroke_string = '';
	 $('#quick-scan').on('keypress',function(e) {
	 	if ($(document.activeElement).attr('id') !== 'scan') {
	 		key_current_time = new Date().getTime();
	 		if (key_stroke_start == 0 || (key_current_time - key_stroke_start) > 1000) {
	 			key_stroke_string = e.key;
	 		} else {
	 			key_stroke_string += e.key;
	 		}
	 		key_stroke_start = key_current_time;
	 		console.log('ks: ' + key_stroke_string);
	 	}
	 });
	 */

	/**
	 * Picking Order
	 */	
	 $('.location_pick_qty').on('change',function() {
	 	var remaining = $(this).parents('[data-pick-remaining]');
		var remaining_qty = parseInt($(remaining).data('pick-remaining'));
		var picked_qty = parseInt($(remaining).data('picked-qty'));
		var total_qty = parseInt($(remaining).data('total-qty'));
		var total_picks = 0;

	 	// Force a proper range
	 	if (parseInt($(this).val()) > parseInt($(this).attr('max'))) {
	 		$(this).val($(this).attr('max')).trigger('change');
	 		return;
	 	} else if (parseInt($(this).val()) < parseInt($(this).attr('min'))) {
	 		$(this).val($(this).attr('min')).trigger('change');
	 		return;
	 	}

	 	// Sum of all entered vals
	 	$(this).parents('.booking_product_row_pick').find('.location_pick_qty').each(function() {
	 		total_picks += parseInt($(this).val());
	 	});
	 	//console.log('remaining_qty: '+remaining_qty);
	 	//console.log('total_picks: '+total_picks);
	 	//console.log('picked_qty: '+picked_qty);
	 	//console.log('total_qty: '+total_qty);

	 	// Validate qty of picks
	 	if (total_picks > remaining_qty) {
	 		// Disable buttons
	 		//$('#pickForProducts .actionBar .btn').attr('disabled','disabled');
	 		alert('Pick quantity exceeds required quantity by '+ (total_picks - total_qty));
	 		// Set to max value possible
	 		$(this).val(parseInt($(this).val()) - (total_picks - total_qty));
	 		total_picks -= (total_picks - total_qty);
	 	} else {
	 		//$('#pickForProducts .actionBar .btn').attr('disabled',false);
	 	}

	 	// Handles product status as picked quantity changes
	 	if ($(this).parents('.booking_product_row_pick').prev().find('.productStatus select :selected').val() == 0) {
			alert('Why are you changing the picked quantity when the status is in deleted/cancelled?');
		} else {
			remaining_qty = (total_qty-picked_qty-total_picks);
			if ((picked_qty + total_picks) == 0) {
				// Change to Pending
				$(this).parents('.booking_product_row_pick').prev().find('.productStatus select').val(1);
			} else if (remaining_qty <= 0) {
				// Change product to 'picked'
				$(this).parents('.booking_product_row_pick').prev().find('.productStatus select').val(3);
			} else if (remaining_qty > 0) {
				// Change product to 'in progress'
				$(this).parents('.booking_product_row_pick').prev().find('.productStatus select').val(2);
			}
			// Adjust remaining
			$(remaining).attr('data-pick-remaining',remaining_qty);
		}

		if (scanPickedIsComplete()) {

		}

		// Determine if the form has changed values.
		if (scanFormIsChanged($(this).parents('form'))) {

		}
	 });

	 /**
	  * Status Change
	  * @param  {[type]} e) {	           	e.preventDefault();	 	$(this).addClass('changed');	 	$(this).parent().nextAll().each(function() {	 		$(this).find('.status_step').removeClass('changed');	 	});	 	$(this).parent().prevAll().each(function() {	 		$(this).find('.status_step').addClass('changed');	 	});	 } [description]
	  * @return {[type]}    [description]
	  */
	 $('.status_steps .status_step').on('click',function(e) {
	 	e.preventDefault();

	 	// Determine the lowest status to see what order status we can change to
	 	var lowest_product_status = highest_order_status = null;
	 	$('.pickProducts .productStatus').each(function() {
	 		lowest_product_status = $(this).find(':selected').val() < lowest_product_status || lowest_product_status == null ? $(this).find(':selected').val() : lowest_product_status;
	 	});
	 	switch(parseInt(lowest_product_status)) {
	 		case 0: // Deleted
	 			// Deleted shouldnt even be visible
	 			highest_order_status = 1; // Awaiting Forward...
	 			break;
	 		case 1: // Pending
	 			highest_order_status = 2; // Accepted
	 			break;
	 		case 2: // In Progress
	 			highest_order_status = 2; // Accepted
	 			break;
	 		case 3: // Picked
	 			highest_order_status = 5; // Shipped (or Picked or Packed)
	 			break;
	 	}

	 	var qty_remaining = 0;
	 	$('.booking_product_row_pick').each(function() {
	 		qty_remaining += parseInt($(this).attr('data-pick-remaining'));
	 	});

	 	// Validate the qty total picked before allowing (Picked, Packed or Shipped) order statuses
	 	// Change back to 2 (Accepted) as the highest allowed value if there are remaining picks
	 	if (highest_order_status > 2 && qty_remaining !== 0) {
	 		highest_order_status = 2;
	 	} 

	 	if ($(this).data('status') <= highest_order_status) {
		 	$(this).addClass('changed');
		 	$(this).parent().nextAll().each(function() {
		 		$(this).find('.status_step').removeClass('changed');
		 	});
		 	$(this).parent().prevAll().each(function() {
		 		$(this).find('.status_step').addClass('changed');
		 	});
		 	// Set the order status form field
		 	$('#form_status').val($(this).data('status')).trigger('change');
		 } else {
		 	alert('All products are required to be in the appropriate status for the order to change to this status.');
		 }
	 });



	 /**
	  * Determines if there was a change of the form.
	  * @param  {[type]} $form [description]
	  * @return {[type]}       [description]
	  */
	 function scanFormIsChanged($form) {
	 	var changed = false;
	 	$form.find('[data-default]').each(function() {
	 		if ($(this).data('default') != $(this).val()) {
	 			changed = true;
	 		}
	 	});
		return changed;
	 }

	 // Determine if all products are marked as picked or closed (shipped)
	 function scanPickedIsComplete() {
	 	return true;
	 }
	

});