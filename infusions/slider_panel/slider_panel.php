<?php
	if (!defined("IN_FUSION")) { die("Access Denied"); }



	add_to_footer ("<script type='text/javascript' src='". THEME ."js/flexslider.js'></script>");
	add_to_head ("<link rel='stylesheet' href='". THEME ."css/flexslider.css' type='text/css' media='screen' />");

	add_to_footer ("<script type='text/javascript'>
			<!--
			$(window).load(function() {
				$('.flexslider').flexslider({
					animation: 'fade',
					slideshowSpeed: 5000,
					animationSpeed: 500,
					pauseOnHover: true,
					controlNav: false,
					directionNav: true,
					prevText: '',
					nextText: '',
				});
			});
			//-->
		</script>");
?>

		<div class="flexslider clearfix">
			<ul class="slides">
				<li><a href="#"><img src="<?php echo IMAGES; ?>slider/slide1.jpg" alt="" /></a></li>
				<li><a href="#"><img src="<?php echo IMAGES; ?>slider/slide2.jpg" alt="" /></a></li>
				<li><a href="#"><img src="<?php echo IMAGES; ?>slider/slide3.jpg" alt="" /></a></li>
			</ul>
		</div>