//==========Modal / Popup==========//
(function ($) {
	"use strict";

	$(document).on('click','.mage_qty_inc, .mage_qty_dec',function (e){
		var name_value = $(this).siblings('input').attr('name');
		var  available_quantity =	$('[name="available_quantity"]').val();
		set_max_quantity( name_value , available_quantity)
	});
	$(document).on('click','.ra_seat_qty',function (e){
		var name_value = ($(this).attr('name'));
		var  available_quantity =	$('[name="available_quantity"]').val();
		set_max_quantity( name_value, available_quantity )
	});

	$(document).on('keyup','.ra_seat_qty',function (e){
		var name_value = ($(this).attr('name'));
		var  available_quantity =	$('[name="available_quantity"]').val();
		set_max_quantity( name_value, available_quantity )
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