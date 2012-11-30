jQuery(function($) {
	if (typeof ($.fn.anythingSlider) != 'undefined') {
		$('.anythingSlider').anythingSlider({
			easing: "easeInOutExpo",        // Anything other than "linear" or "swing" requires the easing plugin
			autoPlay: true,                 // This turns off the entire FUNCTIONALY, not just if it starts running or not.
			delay: 5000,                    // How long between slide transitions in AutoPlay mode
			startStopped: false,            // If autoPlay is on, this can force it to start stopped
			animationTime: 1000,             // How long the slide transition takes
			hashTags: true,                 // Should links change the hashtag in the URL?
			buildNavigation: true,          // If true, builds and list of anchor links to link to each slide
			pauseOnHover: true,             // If true, and autoPlay is enabled, the show will pause on hover
			startText: "Go",             // Start text
			stopText: "Stop",               // Stop text
			navigationFormatter: null       // Details at the top of the file on this use (advanced use)
		});
	}
	if (typeof ($.fn.jCarouselLite) != 'undefined') {
		$(".carousel").jCarouselLite({
			btnNext: ".carousel .next",
			btnPrev: ".carousel .prev",
			visible: 5,
			circular: false
		});
	}
	if (typeof ($.fn.accordion) != 'undefined') {
		$( "#accordion" ).accordion({
			autoHeight: false,
			navigation: true
		});
	}
	
	if (typeof ($.fn.selectBox) != 'undefined') {
		$(".contact-element select").selectBox();
		$(".support_select select").selectBox();
	}
	
	if (typeof ($.fn.tabs) != 'undefined') {
		$( "#footer_tabs" ).tabs({selected: 1});
		$( "#att_tab" ).tabs();
	}
	if (typeof ($.fn.fancybox) != 'undefined') {
		$(".fancy-link").fancybox({
			'titlePosition' : 'inside',
			'transitionIn' : 'none',
			'transitionOut' : 'none',
			'showNavArrows' : false,
			'autoScale' : false
		});
		$(".fancy-video").fancybox({
			'titlePosition' : 'inside',
			'transitionIn' : 'none',
			'transitionOut' : 'none',
			'showNavArrows' : false,
			'autoScale' : false,
			'showCloseButton': false,
			'overlayColor':'#000',
			'overlayOpacity': 0.8
		});
		$(window).bind('load', $("#prod_popup").trigger('click.fb'));
	}
	$('input#search')
		.focus(function(){if ($(this).val() == 'Blog Search') {$(this).val('');} })
		.blur(function(){if ($(this).val() == '') {$(this).val('Blog Search');} })
	if (typeof ($.fn.jScrollHorizontalPane) != 'undefined') {
		$('#table-scroll').jScrollHorizontalPane({
			scrollbarHeight:15,
		});
	}
	if (typeof ($.fn.jScrollPane) != 'undefined') {
		 $('#pane').jScrollPane({scrollbarWidth:6, showArrows:false});
	}
	$('.answers_block h4').click(function(){
		$('.answers_block_wrap .answers_block').removeClass('active_answers');
		$(this).parent().addClass('active_answers');
		return false;
	})
	
	$('select').change(function(){
		var sVal = $(this).val();
		$('.block').addClass('hide');
		$('#'+ sVal).removeClass('hide');
	})
	
});
