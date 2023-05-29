//==========Modal / Popup==========//
(function ($) {
	"use strict";

	                /*  start date picker for global and local search*/

	$(document).ready(function($) {

		var single_bus = $( "#all_date_picker_info" ).data( "single_bus" ) || '';
		var return_single_bus = $( "#return_all_date_picker_info" ).data( "return_single_bus" ) || '';
		var date_format = $( "#all_date_picker_info" ).data( "date_format" );

		if(single_bus){

			var enableDates = $( "#all_date_picker_info" ).data( "enabledates" );
			var off_particular_date = $( "#all_date_picker_info" ).data( "off_particular_date" );
			var weekly_offday = $( "#all_date_picker_info" ).data( "weekly_offday" );
			var enable_onday = $( "#all_date_picker_info" ).data( "enable_onday" );
			var enable_offday = $( "#all_date_picker_info" ).data( "enable_offday" );


			if(enable_onday || enable_offday){
				if(enable_onday == 'yes') {
					if(enableDates){
						console.log(enableDates);
						jQuery('#j_date').datepicker({
							dateFormat: date_format,
							minDate: 0,
							beforeShowDay: function (date){
								return enableAllTheseDays(date, enableDates );
							}

						});
					}else{
						jQuery("#j_date").datepicker({
							dateFormat: date_format,
							minDate: 0,
						});
					}

				} else if(enable_offday=='yes'){
					jQuery("#j_date").datepicker({
						dateFormat: date_format,
						minDate: 0,
						beforeShowDay: function (date){
							return off_particular(date, off_particular_date,weekly_offday );
						}
					});
				}else{
					jQuery("#j_date").datepicker({
						dateFormat: date_format,
						minDate: 0,
					});
				}
			}else{
				if(enableDates){
					jQuery('#j_date').datepicker({
						dateFormat: date_format,
						minDate: 0,
						beforeShowDay: function (date){
							return enableAllTheseDays(date, enableDates );
						}
					});
				}else{
					jQuery("#j_date").datepicker({
						dateFormat: date_format,
						minDate: 0,
						beforeShowDay: function (date){
							return off_particular(date, off_particular_date,weekly_offday );
						}
					});
				}
			}

		}else{
			var global_off_particular_date = $( "#all_date_picker_info" ).data( "disabledates" );
			var global_weekly_offday = $( "#all_date_picker_info" ).data( "disabledays" );

			jQuery("#j_date, #r_date").datepicker({
				dateFormat: date_format,
				minDate: 0,
				beforeShowDay: function (date){
					return off_particular(date, global_off_particular_date,global_weekly_offday );
				}
			});

		}


		function enableAllTheseDays(date,enableDates) {
			console.log(enableDates);
			var sdate = jQuery.datepicker.formatDate('dd-mm-yy', date)
			if (enableDates.length > 0) {
				console.log(sdate);
				
				if (jQuery.inArray(sdate, enableDates) != -1) {
					return [true];
				}
			}
			return [false];
		}


		function off_particular(date,off_particular_date,weekly_offday) {
			var sdate = jQuery.datepicker.formatDate('dd-mm-yy', date)
			if (off_particular_date.length > 0) {
				if (jQuery.inArray(sdate, off_particular_date) != -1) {
					return [false];
				}
			}
			if (weekly_offday.length > 0) {

				// Fix sunday value issue
				const sundayIndex = weekly_offday.indexOf(7);
				if(sundayIndex !== -1) {
					weekly_offday[sundayIndex] = 0;
				}
				// Fix sunday value issue
				
				if (weekly_offday.includes(date.getDay())) {
					return [false];
				}
			}
			return [true];
		}

	});



	/*  end date picker for global and local search*/










	$(document).on('click','.mage_qty_inc, .mage_qty_dec',function (e){
		var name_value = $(this).siblings('input').attr('name');
		var  available_quantity =	$('[name="available_quantity"]').val();
		// set_max_quantity( name_value , available_quantity)
	});
	$(document).on('click','.ra_seat_qty',function (e){
		var name_value = ($(this).attr('name'));
		var  available_quantity =	$('[name="available_quantity"]').val();
		// set_max_quantity( name_value, available_quantity )
	});

	$(document).on('keyup','.ra_seat_qty',function (e){
		var name_value = ($(this).attr('name'));
		var  available_quantity =	$('[name="available_quantity"]').val();
		// set_max_quantity( name_value, available_quantity )
	});

	function set_max_quantity (name_value,available_quantity){
		var adult_quantity =	$('.ra_seat_qty[name="adult_quantity"]').val() || 0;
		var child_quantity =	$('.ra_seat_qty[name="child_quantity"]').val() || 0;
		var infant_quantity =	$('.ra_seat_qty[name="infant_quantity"]').val() || 0;

		if(name_value == 'adult_quantity'){
			var  rest_quantity = available_quantity  -child_quantity-infant_quantity;
		}else if(name_value == 'child_quantity'){
			var rest_quantity = available_quantity - adult_quantity-infant_quantity;
		}else{
			var rest_quantity = available_quantity - adult_quantity-child_quantity;
		}

		console.log('rest_quantity',available_quantity);

		$('[name="'+name_value+'"]').attr('max',rest_quantity);

		if(rest_quantity==$('[name="'+name_value+'"]').val()){

			$('.mage_qty_inc').addClass('mage_disabled');
		}else{
			$('.mage_qty_inc').removeClass('mage_disabled');
		}

	}















}(jQuery));