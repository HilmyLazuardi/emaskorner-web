function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('.profile_img').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}
function show_popup(){
	$('.popup_wrapper').show();	
}
function show_popup_success(){
	$('.popup_success').show();	
}
function show_popup_failed(){
	$('.popup_failed').show();	
}
function hide_popup(){
	$('.popup_wrapper, .popup_success, .popup_failed, .popup_menu').hide();
}
$(document).ready(function(){
	$('.h_search').click(function(){
		$('.popup_search').show();
		$('.popup_search input[type="text"]').focus();
	})
	$('.popup_search .close_btn').click(function(){
		$('.popup_search').hide();
	})
	$('.popup_landing .close_btn').click(function(){
		$('.popup_landing').hide();
	})
	$('.popup_filter .close_btn').click(function(){
		$('.popup_filter').hide();
	})
	$('.filter_btn').click(function(){
		$('.popup_filter').show();
	});
	$('.sort_btn').click(function(){
		if($('.custom_two').length>0){
			$('.product_list_box').removeClass('custom_two').addClass('custom_three');
			$(this).addClass('grid_btn');
		}
		else if($('.custom_three').length>0){
			$('.product_list_box').removeClass('custom_three').addClass('custom_two');
			$(this).removeClass('grid_btn');
		}
	})
	if($('.select2').length>0){
		$('.select2').select2()
	}
	$('.select2').one('select2:open', function(e) {
	    $('input.select2-search__field').prop('placeholder', '');
	});
	if($('.select3').length>0){
		$('.select3').select2({
			minimumResultsForSearch: Infinity
		})
	}
	if($('.slider_banner_home').length>0){
		$('.slider_banner_home').slick({
			arrows: false,
			fade: true,
			autoplay:true,
			pauseOnHover:false
		});
	}
	if($('.slider_banner').length>0){
		var $status = $('.total_count');
		var $slickElement = $('.slider_banner');

		$slickElement.on('init reInit afterChange', function (event, slick, currentSlide, nextSlide) {
			//currentSlide is undefined on init -- set it to 0 in this case (currentSlide is 0 based)
			var i = (currentSlide ? currentSlide : 0) + 1;
			$status.text(i + '/' + slick.slideCount);
		});

		$slickElement.slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: false,
			fade: true,
			asNavFor: '.slider_thumb'
		});
		// $('.slider_banner').slick({
		// 	slidesToShow: 1,
		// 	slidesToScroll: 1,
		// 	arrows: false,
		// 	fade: true,
		// 	asNavFor: '.slider_thumb'
		// });
			$('.slider_thumb').slick({
			slidesToShow: 2,
			slidesToScroll: 1,
			asNavFor: '.slider_banner',
			dots: false,
			variableWidth: true,
			centerMode: true,
			focusOnSelect: true
		});
	}
	if($('.slider_news').length>0){
		$('.slider_news').slick({
			autoplay:true,
			infinite: true,
	  		slidesToShow: 3,
	  		slidesToScroll: 1,
	  		responsive: [
		    {
		      breakpoint: 767,
		      settings: {
		        slidesToShow: 2,
		        slidesToScroll: 2,
		        infinite: true,
		        arrows: false,
		        dots: true
		      }
		    }
		    // You can unslick at a given breakpoint now by adding:
		    // settings: "unslick"
		    // instead of a settings object
		  ]
		});
	}
	if($('.slider_blog').length>0){
		$('.slider_blog').slick({
			autoplay:true,
			infinite: true
		});
	}
	if($('.change_img').length>0){
		$('.change_img').change(function(){
		    readURL(this);
		    $('.profile_img').show();
		})
	}
	if($('.datepicker').length>0){
		$('.datepicker').datepicker({ dateFormat: 'dd-mm-yy' }).val();
	}
	$('.section_content_element').each(function(){
		if($(this).find('.ce_column .flex_box').length == 2 || $(this).find('.ce_column .flex_box').length == 4){
			$(this).find('.ce_column').addClass('twoandfour');
		}
		if($(this).find('.ce_column .flex_box').length == 3 || $(this).find('.ce_column .flex_box').length == 5 || $(this).find('.ce_column .flex_box').length == 6){
			$(this).find('.ce_column').addClass('threeandfive');
		}
	})
	$('.section_content_element .container').each(function(){
		if($(this).find('.ce_column .flex_box').length == 2 || $(this).find('.ce_column .flex_box').length == 4){
			$(this).find('.ce_column').removeClass('threeandfive').addClass('twoandfour');
		}
		if($(this).find('.ce_column .flex_box').length == 3 || $(this).find('.ce_column .flex_box').length == 5 || $(this).find('.ce_column .flex_box').length == 6){
			$(this).find('.ce_column').addClass('threeandfive');
		}
	})
	if($('.masthead_slider .mh_box').length > 1){
		$('.masthead_slider').slick({
			autoplay:true
		});
	}
	if($('.image_slider .mh_box').length > 1){
		$('.image_slider').slick({
			autoplay:true
		});
	}
	if($('.carousel_image_slider .mh_box').length > 3){
		$('.carousel_image_slider').slick({
			autoplay:true,
			infinite: true,
	  		slidesToShow: 3,
	  		slidesToScroll: 3
		});
	}
	if($('.slider_rp').length > 0){
		$('.slider_rp').slick({
			autoplay:true,
			infinite: true,
	  		slidesToShow: 4,
	  		slidesToScroll: 4,
	  		arrows: true,
	  		responsive: [
		    {
		      breakpoint: 959,
		      settings: {
		        slidesToShow: 3,
		        slidesToScroll: 3,
		        infinite: true,
		        dots: true,
		        arrows: true
		      }
		    },
		    {
		      breakpoint: 767,
		      settings: {
		        slidesToShow: 2,
		        slidesToScroll: 2,
		        infinite: true,
		        dots: false,
		        arrows: true
		      }
		    }
		    // You can unslick at a given breakpoint now by adding:
		    // settings: "unslick"
		    // instead of a settings object
		  ]
		});
	}
	if($('.ns_slider').length > 0){
		$('.ns_slider').slick({
			autoplay:true,
			infinite: true,
	  		slidesToShow: 3,
	  		slidesToScroll: 3,
	  		arrows: true,
	  		responsive: [
		    {
		      breakpoint: 959,
		      settings: {
		        slidesToShow: 3,
		        slidesToScroll: 3,
		        infinite: true,
		        dots: true,
		        arrows: true
		      }
		    },
		    {
		      breakpoint: 767,
		      settings: {
		        slidesToShow: 1,
		        slidesToScroll: 2,
		        infinite: true,
		        dots: false,
		        arrows: true
		      }
		    }
		  ]
		});
	}
	if($('.slider_rp_new').length > 0){
		$('.slider_rp_new').slick({
			autoplay:false,
			infinite: true,
	  		slidesToShow: 3,
	  		slidesToScroll: 3,
	  		arrows: true,
	  		responsive: [
		    {
		      breakpoint: 959,
		      settings: {
		        slidesToShow: 3,
		        slidesToScroll: 3,
		        infinite: true,
		        dots: true,
		        arrows: true
		      }
		    },
		    {
		      breakpoint: 767,
		      settings: {
		        slidesToShow: 2,
		        slidesToScroll: 2,
		        infinite: true,
		        dots: false,
		        arrows: true
		      }
		    },
		    {
		      breakpoint: 420,
		      settings: {
		        slidesToShow: 1,
		        slidesToScroll: 1,
		        infinite: true,
		        dots: false,
		        arrows: true
		      }
		    }
		    // You can unslick at a given breakpoint now by adding:
		    // settings: "unslick"
		    // instead of a settings object
		  ]
		});
	}
	if($('.product_slider').length > 0){
		$('.product_slider').slick({
			autoplay:false,
			infinite: true,
	  		slidesToShow: 2,
	  		slidesToScroll: 2,
	  		responsive: [
		    {
		      breakpoint: 767,
		      settings: {
		        slidesToShow: 2,
		        slidesToScroll: 2,
		        infinite: true,
		        dots: false,
		        arrows: false
		      }
		    }
		  ]
		});
	}
	$('.faq_box .faq_top').click(function(){
		if($(this).siblings('.faq_bottom').css('display')=="none"){
			$(this).addClass('active');
			$(this).siblings('.faq_bottom').slideDown();
		}
		else{
			$(this).removeClass('active')
			$(this).siblings('.faq_bottom').slideUp();
		}
	})
	$('.tab_menu li a').click(function(){
		var gethref = $(this).attr('href');
		$('.tab_menu li a').removeClass('active');
		$(this).addClass('active');
		$('.tab_wrapper').hide();
		$(gethref).show();
		return false;
	})
	$('.edit_btn').click(function(){
		$(this).siblings('input[type="text"]').focus();
	})
	$('.menu_mobile').click(function(){
		$('.popup_menu').show();
	})
	$('.h_profil').click(function(e){
		if($('.popup_profil').css('display')=="none"){
			$('.popup_profil').show();
			e.stopPropagation();
		}
		else{
			$('.popup_profil').hide();
			e.stopPropagation();
		}
	})
	$(document).click(function(){
		$('.popup_profil').hide();
	})
    $('.share_btn').click(function(){
    	if($('#sharethis_box').css('opacity')==0){
	        $('#sharethis_box').addClass('opened');
	    }
	    else{
	    	$('#sharethis_box').removeClass('opened');
	    }
    })
	$('.choose_all').click(function(){
		if($(this).is(':checked')){
			$('input[type="checkbox"]').prop('checked', true);
		}
		else{
			$('input[type="checkbox"]').prop('checked', false);
		}
	})
	$('.delete_btn a').click(function(){
		$('input[type="checkbox"]').prop('checked', false);
	})
})