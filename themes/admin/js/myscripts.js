$(document).ready(function() {

	var window_height = $( window ).height();
	window_height = window_height-120;
	$( 'section > .main aside' ).css( 'height', window_height );
	$( 'section > .main article' ).css( 'height', window_height );

	$('.form_table #seo_tr_button').click(function() {
		if($('.form_table #seo_tr_button').hasClass('open')) {
			$('.form_table #seo_tr_button').removeClass('open');
			$('.form_table .seo_tr').hide('slow');
		} else {
			$('.form_table #seo_tr_button').addClass('open');
			$('.form_table .seo_tr').show('slow');
		}
	});

	$( '.user_info_panel .user_name' ).click(function() {
		if($( '.user_info_panel' ).hasClass( 'open' )) {
			$( '.user_info_panel' ).removeClass( 'open' );
		} else {
			$( '.user_info_panel' ).addClass( 'open' );
		}
	});

});