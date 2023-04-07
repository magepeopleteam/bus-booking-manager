//==========Modal / Popup==========//
(function ($) {
	"use strict";
               /*add bus stop*/
	$(".submit-bus-stop").click(function(e) {
		e.preventDefault();
		let $this=$(this);
		let target=$this.closest('.mpPopup').find('.bus-stop-form');
		let name = $("#bus_stop_name").val().trim();
		$(".success_text").slideUp('fast');
		if(!name){
			$(".name_required").show();
		}else {
			let description = $("#bus_stop_description").val().trim();

			$.ajax({
				type: 'POST',
				// url:wbtm_ajax.wbtm_ajaxurl,
				url: wbtm_ajaxurl,
				dataType: 'JSON',
				data: {
					"action": "wbtm_add_bus_stope",
					"name": name,
					"description": description,
				},

				beforeSend: function () {
					dLoader(target);
				},

				success: function (data) {
					$('.bus_stop_add_option').append($('<option>', {
						value: data.text,
						text: data.text,
						'data-term_id': data.term_id
					}));

					$(".name_required").hide();
					$("#bus_stop_name").val("");
					$("#bus_stop_description").val("");
					$(".success_text").slideDown('fast');
					setTimeout(function() {
						$('.success_text').fadeOut('fast');
					}, 1000); // <-- time in milliseconds
					dLoaderRemove(target);
					if ($this.hasClass('close_popup')) {
						$this.delay(2000).closest('.popupMainArea').find('.popupClose').trigger('click');
					}
				}
			});
		}
	});

	$(".submit-feature").click(function(e) {
		e.preventDefault();
		let $this=$(this);
		let target=$this.closest('.mpPopup').find('.bus-feature');
		let name = $("#bus_feature").val().trim();
		$(".success_text").slideUp('fast');
		if(!name){
			$(".name_required").show();
		}else {
			let description = $("#feature_description").val().trim();
			let wbbm_feature_icon = $("#feature_icon").val().trim();

			$.ajax({
				type: 'POST',
				// url:wbtm_ajax.wbtm_ajaxurl,
				url: wbtm_ajaxurl,
				dataType: 'HTML',
				data: {
					"action": "wbtm_add_bus_feature",
					"name": name,
					"description": description,
					"wbbm_feature_icon": wbbm_feature_icon,
				},

				beforeSend: function () {
					dLoader(target);
				},

				success: function (data) {

					$('.features').append(data);

					$(".name_required").hide();
					$("#bus_feature").val("");
					$("#feature_description").val("");
					$("#feature_icon").val("fas fa-forward");
					$('.mp_input_add_icon').find('span[data-empty-text]').removeAttr('class').addClass("fas fa-forward").html('');
					$(".success_text").slideDown('fast');
					setTimeout(function() {
						$('.success_text').fadeOut('fast');
					}, 1000); // <-- time in milliseconds
					dLoaderRemove(target);
					if ($this.hasClass('close_popup')) {
						$this.delay(2000).closest('.popupMainArea').find('.popupClose').trigger('click');
					}
				}
			});
		}
	});

	$(document).on('click', 'button.mp_input_add_icon_button', function () { 
		$(this).attr('data-icon-target', 'icon');
		$('body').addClass('noScroll').find('.add_icon_list_popup').addClass('in');
	});

	$(document).on('click', 'button.mp_input_add_icon_button', function () {
		$(this).attr('data-icon-target', 'icon');
		$('body').addClass('noScroll').find('.add_icon_list_popup').addClass('in');
	});
	$(document).on('click', '.add_icon_list_popup .popupCloseIcon', function () {
		let parent = $(this).closest('.add_icon_list_popup');
		parent.removeClass('in');
		$('body').removeClass('noScroll');
		$('[data-icon-target]').removeAttr('data-icon-target');
		parent.find('[data-icon-menu="all_item"]').trigger('click');
		parent.find('.iconItem').removeClass('active');
	});
	$(document).on('click', '.add_icon_list_popup .iconItem', function () {
		let target = $('[data-icon-target]');
		let icon_class = $(this).data('icon-class');
		target.find('span.remove_input_icon').slideDown('fast');
		target.find('span[data-empty-text]').removeAttr('class').addClass(icon_class).html('');
		target.find('input').val(icon_class);
		let targetParent = $(this).closest('.add_icon_list_popup');
		targetParent.find('.iconItem').removeClass('active');
		$(this).addClass('active');
		targetParent.find('.popupCloseIcon').trigger('click');
	});
	$(document).on('click', 'button.mp_input_add_icon_button span.remove_input_icon', function (e) {
		e.stopImmediatePropagation();
		let parent = $(this).closest('button.mp_input_add_icon_button');
		let text = parent.find('span[data-empty-text]').data('empty-text');
		parent.find('span[data-empty-text]').removeAttr('class').html(text);
		parent.find('input').val('');
		$(this).slideUp('fast');
	});
	$(document).on('click', '.add_icon_list_popup [data-icon-menu]', function () {
		if (!$(this).hasClass('active')) {
			let target = $(this);
			let tabsTarget = target.data('icon-menu');
			let targetParent = target.closest('.add_icon_list_popup');
			targetParent.find('[data-icon-menu]').removeClass('active');
			target.addClass('active');
			targetParent.find('[data-icon-list]').each(function () {
				let targetItem = $(this).data('icon-list');
				if (tabsTarget === 'all_item' || targetItem === tabsTarget) {
					$(this).slideDown(250);
				} else {
					$(this).slideUp(250);
				}
			});
		}
		return false;
	});
                 /*add pickup point*/
	$(".submit-pickup").click(function(e) {
		e.preventDefault();
		let $this=$(this);
		let target=$this.closest('.mpPopup').find('.bus-pickup');
		let name = $("#pickup_name").val().trim();
		$(".success_text").slideUp('fast');
		if(!name){
			$(".name_required").show();
		}else {
			let description = $("#pickup_description").val().trim();

			$.ajax({
				type: 'POST',
				// url:wbtm_ajax.wbtm_ajaxurl,
				url: wbtm_ajaxurl,
				dataType: 'JSON',
				data: {
					"action": "wbtm_add_pickup",
					"name": name,
					"description": description,
				},

				beforeSend: function () {
					dLoader(target);
				},

				success: function (data) {
					$('.pickup_add_option').append($('<option>', {
						value: data.text,
						text: data.text,
						'data-term_id': data.term_id
					}));

					$(".name_required").hide();
					$("#pickup_name").val("");
					$("#pickup_description").val("");
					$(".success_text").slideDown('fast');
					setTimeout(function() {
						$('.success_text').fadeOut('fast');
					}, 1000); // <-- time in milliseconds
					dLoaderRemove(target);
					if ($this.hasClass('close_popup')) {
						$this.delay(2000).closest('.popupMainArea').find('.popupClose').trigger('click');
					}
				}
			});
		}
	});


	$("#upper-desk-control").click(function(){
		$("#upper-desk").slideToggle("slow");
	});

	$("#pickup-point-control").click(function(){
		$("#pickup-point").slideToggle("slow");
	});

	$("#operational-on-day-control").click(function(){
		$(".operational-on-day").slideToggle("slow");
	});

	$("#off-day-control").click(function(){
		$(".off-day").slideToggle("slow");
	});
	$("#extra-service-control").click(function(){
		$(".extra-service").slideToggle("slow");
	});


	$(".add-more-bd-point").click(function(e){
		e.preventDefault();
		$(this).siblings().children('.bd-point').append('<tr>'+$(this).siblings().children().children(".more-bd-point").html()+'</tr>');
		$(this).parent().find('input.text').timepicker({
			timeFormat: 'H:mm',
			interval: 15,
			minTime: '00:00',
			maxTime: '23:59',
			dynamic: true,
			dropdown: true,
			scrollbar: true
		});
	});

	$(document).on('click','.remove-bp-row',function (e){
		e.preventDefault();
		$(this).parents('tr').remove();
		return false;
	});

	$(document).on('click','.open-routing-tab',function (e){
		e.preventDefault();
		//$(this).removeClass();
		$( ".wbtm_routing_tab" ).click();
		return false;
	});

	/*seat pricing start*/

	$(document).on('change','.wbbm_bus_stops_route',function (e){
		e.preventDefault();

		var new_bus = $('#price_bus_record').val();
		var discount_price_switch = $('#discount_price_switch').val();

		if(new_bus==''){
			var route_row = '';
			var i = 0;
			$( ".boarding-point tr" ).each(function( index ) {
				var j = 0;
				let term_id = $(this).find(':selected').data('term_id');
				if(term_id){
					var boarding_point = $(this).find(":selected").val();
					$( ".dropping-point tr" ).each(function( index ) {
						if (i <= j) {
							let term_id = $(this).find(':selected').data('term_id');
							if(term_id){
								var dropping_point = $(this).find(":selected").val();

								if(discount_price_switch=='on'){
									route_row += '<tr class="temprary-record-price"><td>'+boarding_point+'</td><td>'+dropping_point+'</td><td class="wbbm-price-col">\n' +
										'                    <input step="0.01" type="number" name="wbbm_bus_price[]" value="" class="text">\n' +
										'                    <input type="hidden" name="wbbm_bus_bp_price_stop[]" value="'+boarding_point+'" class="text">\n' +
										'                    <input type="hidden" name="wbbm_bus_dp_price_stop[]" value="'+dropping_point+'" class="text">\n' +
										'                    <input step="0.01" type="number" name="wbbm_bus_price_roundtrip[]" placeholder="Adult return discount price" value="" class="text roundtrip-input">\n' +
										'                </td><td class="wbbm-price-col">\n' +
										'                    <input step="0.01" type="number" name="wbbm_bus_price_child[]" value="" class="text">\n' +
										'                    <input step="0.01" type="number" name="wbbm_bus_price_child_roundtrip[]" placeholder="Child return discount price" value="" class="text roundtrip-input">\n' +
										'                </td><td class="wbbm-price-col">\n' +
										'                    <input step="0.01" type="number" name="wbbm_bus_price_infant[]" value="" class="text">\n' +
										'                    <input step="0.01" type="number" name="wbbm_bus_price_infant_roundtrip[]" placeholder="Infant return discount price" value="" class="text roundtrip-input">\n' +
										'                </td><td class="wbbm-price-col"><a class="button remove-price-row" href="#"><i class="fas fa-minus-circle"></i>\n' +
										'                        Remove</a>\n' +
										'                </td></tr>';
								}else{
									route_row += '<tr class="temprary-record-price"><td>'+boarding_point+'</td><td>'+dropping_point+'</td><td class="wbbm-price-col">\n' +
										'                    <input step="0.01" type="number" name="wbbm_bus_price[]" value="" class="text">\n' +
										'                    <input type="hidden" name="wbbm_bus_bp_price_stop[]" value="'+boarding_point+'" class="text">\n' +
										'                    <input type="hidden" name="wbbm_bus_dp_price_stop[]" value="'+dropping_point+'" class="text">\n' +
										'                </td><td class="wbbm-price-col">\n' +
										'                    <input step="0.01" type="number" name="wbbm_bus_price_child[]" value="" class="text">\n' +
										'                </td><td class="wbbm-price-col">\n' +
										'                    <input step="0.01" type="number" name="wbbm_bus_price_infant[]" value="" class="text">\n' +
										'                </td><td class="wbbm-price-col"><a class="button remove-price-row" href="#"><i class="fas fa-minus-circle"></i>\n' +
										'                        Remove</a>\n' +
										'                </td></tr>';
								}




							}
						}
						j++;
					});
				}
				i++
			});
			$('.temprary-record-price').remove();
			$('.auto-generated').append(route_row);
		}

			$('.ra_bus_bp_price_stop').html("<option value=''>Select Boarging Point</option>");
			$( ".boarding-point tr" ).each(function( index ) {
				let term_id = $(this).find(':selected').data('term_id');
				if(term_id){
					$('.ra_bus_bp_price_stop').append("<option value='"+$(this).find(":selected").val()+"'>"+$(this).find(":selected").val()+"</option>")
				}
			});

			$('.ra_bus_dp_price_stop').html("<option value=''>Select Dropping Point</option>");
			$( ".dropping-point tr" ).each(function( index ) {
				let term_id = $(this).find(':selected').data('term_id');
				if(term_id){
					$('.ra_bus_dp_price_stop').append("<option value='"+$(this).find(":selected").val()+"'>"+$(this).find(":selected").val()+"</option>")
				}
			});


		return false;



	});

	$(document).on('change','.ra_bus_bp_price_stop',function (e){
		e.preventDefault();
		$( this ).even().removeClass( "ra_bus_bp_price_stop" );
	});

	$(document).on('change','.ra_bus_dp_price_stop',function (e){
		e.preventDefault();
		$( this ).even().removeClass( "ra_bus_dp_price_stop" );
	});

	$(document).on('click','#add-price-row',function (e){
		var row = $('.empty-row-price.screen-reader-text').clone(true);
		row.removeClass('empty-row-price screen-reader-text');
		row.insertAfter('#repeatable-fieldset-price-one tbody>tr:last');
		return false;
	});

	$(document).on('click','.remove-price-row',function (e){
		e.preventDefault();
		$(this).parents('tr').remove();
		return false;
	});


	/*seat pricing end*/

	$(document).on('click','.ra_pickuppoint_tab',function (e){
		e.preventDefault();
		$('.ra_pick_boarding').html("<option value=''>Select Boarding Point</option>");
		let options = '';
		$( ".boarding-point tr" ).each(function( index ) {

			let pick_name = $(this).find(":selected").val()

			options = options+$(this).find(":selected").val();

			if(options){
				$('.boarding_points').show();
				$('.open-routing-tab').hide();
			}else{
				$('.open-routing-tab').show();
				$('.boarding_points').hide();
			}
			let term_id = $(this).find(':selected').data('term_id');
			if(term_id){
				$('.ra_pick_boarding').append("<option value="+pick_name+">"+pick_name+"</option>")
			}
		});

		return false;
	});


	$(".global_particular_onday").multiDatesPicker({
		numberOfMonths: [1,3],
		dateFormat: "yy-mm-dd",
		minDate: 0,
	});


	// click event
	$( '.misha-upload-button' ).click( function( event ){ // button click
		// prevent default link click event
		event.preventDefault();

		const button = $(this)
		// we are going to use <input type="hidden"> to store image IDs, comma separated
		const hiddenField = button.prev()
		const hiddenFieldValue = hiddenField.val().split( ',' )

		const customUploader = wp.media({
			title: 'Insert images',
			library: {
				type: 'image'
			},
			button: {
				text: 'Use these images'
			},
			multiple: true
		}).on( 'select', function() {

			// get selected images and rearrange the array
			let selectedImages = customUploader.state().get( 'selection' ).map( item => {
				item.toJSON();
				return item;
			} )

			selectedImages.map( image => {
				// add every selected image to the <ul> list
				$( '.misha-gallery' ).append( '<li data-id="' + image.id + '"><img src="' + image.attributes.url +  '"></img><a href="#" class="misha-gallery-remove">×</a></li>' );
				// and to hidden field
				hiddenFieldValue.push( image.id )
			} );

			// refresh sortable
			$( '.misha-gallery' ).sortable( 'refresh' );
			// add the IDs to the hidden field value
			hiddenField.val( hiddenFieldValue.join() );

		}).open();
	});

// remove image event

	$(document).on('click','.misha-gallery-remove',function (event){
		event.preventDefault();

		const button = $(this)
		const imageId = button.parent().data( 'id' )
		const container = button.parent().parent()
		const hiddenField = container.next()

		var hiddenFieldValue = hiddenField.val().split(",")


		hiddenFieldValue = $.grep(hiddenFieldValue, function(value) {
			return value != imageId;
		});


		button.parent().remove();

		// add the IDs to the hidden field value
		hiddenField.val( hiddenFieldValue.join() );

		// refresh sortable
		container.sortable( 'refresh' );

	});

// reordering the images with drag and drop
	$( '.misha-gallery' ).sortable({
		items: 'li',
		cursor: '-webkit-grabbing', // mouse cursor
		scrollSensitivity: 40,
		/*
        You can set your custom CSS styles while this element is dragging
        start:function(event,ui){
            ui.item.css({'background-color':'grey'});
        },
        */
		stop: function( event, ui ){
			ui.item.removeAttr( 'style' );

			let sort = new Array() // array of image IDs
			const container = $(this) // .misha-gallery

			// each time after dragging we resort our array
			container.find( 'li' ).each( function( index ){
				sort.push( $(this).attr( 'data-id' ) );
			});
			// add the array value to the hidden input field
			container.parent().next().val( sort.join() );
			// console.log(sort);
		}
	});







}(jQuery));