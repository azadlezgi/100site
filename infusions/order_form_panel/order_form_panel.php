<?php
if (!defined("IN_FUSION")) { die("Access Denied"); }
?>
<div id="order_form">
	<div class="order_form_overlow"></div>
	<div class="order_form_block">
		<?php
			if ($_POST['user_hash']) {
				$user_h1 = stripinput($_POST['user_h1']);
				$user_url = stripinput($_POST['user_url']);
				$user_name = stripinput($_POST['user_name']);
				$user_phone = stripinput($_POST['user_phone']);
			} else {
				$user_h1 = "";
				$user_url = "";
				$user_name = "";
				$user_phone = "";
			}

			if ($_POST['user_hash']) {

				if (empty($user_name)) { $error .= "<div class='error'>Заполните поле — Имя</div>\n"; }
				if (empty($user_phone)) { $error .= "<div class='error'>Заполните поле — Телефон</div>\n"; }

				if ($error) {
					echo "<div class='order_form_message'>\n";
					echo "<div class='order_form_error'>Ваша заявка не отправлено, заполните все поля формы.</div>\n";
					// echo $error;
					echo "</div>\n";
				} else {
						
						$headers=null;
						$headers.="Content-Type: text/html; charset=". $locale['charset'] ."\r\n";
						$headers.="From: ". $settings['siteusername'] ." <info@". $settings['site_host'] .">\r\n";
						$headers.="X-Mailer: PHP/".phpversion()."\r\n";

						// Собираем всю информацию в теле письма
						$messages .= "<table>\n";
						$messages .= "<tr><td colspan='2'><b>". $settings['mail_subject'] ."</b></td></tr>\n";
						$messages .= "<tr><td colspan='2'>&nbsp;</td></tr>\n";
						$messages .= "<tr><td>H1: </td><td><b>". $user_h1 ."</b></td></tr>\n";
						$messages .= "<tr><td>URL: </td><td><b>". $user_url ."</b></td></tr>\n";
						$messages .= "<tr><td>Имя: </td><td><b>". $user_name ."</b></td></tr>\n";
						$messages .= "<tr><td>Телефон: </td><td><b>". $user_phone ."</b></td></tr>\n";
						$messages .= "</table>\n";

						// Отправляем письмо майлеру
						mail($settings['siteemail'], $settings['mail_subject'], $messages, $headers);

						echo "<div class='order_form_message'>\n";
						echo "<div class='order_form_success'>Ваша заявка успешно отправлено. Наши менеджеры свяжутся с Вами в течении рабочего дня.</div>\n";
						echo "</div>\n";

						unset($user_name);
						unset($user_phone);

				} // Yesli Error

			} // Yesli POST
		?>

		<form id="order_form_form" name="order_form_form" action="<?php echo FUSION_URI; ?>" method="POST">
			<div class="order_form_title">
				<i class="fa fa-phone"></i>
				Форма заказа
				<label>Заполните поля формы, мы Вам перезвоним!</label>
			</div>
			<div class="order_form_close">
				<i class="fa fa-times-circle"></i>
				<i class="fa fa-times-circle-o"></i>
			</div>
			<div class="order_form_result"></div>
			<input type="hidden" name="user_hash" id="user_hash" value="<?php echo md5(mktime()); ?>" />
			<input type="hidden" name="user_h1" id="user_h1" value="" />
			<input type="hidden" name="user_url" id="user_url" value="" />
			<div class="order_form_body">
				<div class="fileds user_name">
					<label for="user_name">Ваше имя <span class="required">*</span></label>
					<input type="text" name="user_name" id="user_name" value="<?php echo $user_name; ?>" class="form-control" placeholder="Ивонов Иван" />
					<i class="fa fa-user"></i>
				</div>
				<div class="fileds user_phone">
					<label for="user_phone">Телефон</label>
					<input type="text" name="user_phone" id="user_phone" value="<?php echo $user_phone; ?>" class="form-control" placeholder="+7 (___) ___-__-__" />
					<i class="fa fa-phone"></i>
				</div>
				<div class="fileds user_submit">
					<input type="submit" name="user_submit" id="user_submit" value="Отправить" class="btn">
				</div>
			</div>
		</form>
	</div>
</div>

<?php
add_to_footer ("
<script type='text/javascript'>
	<!--
	$(document).ready(function(){
		$( '#order_form_form #user_phone' ).inputmask( '+7 (999) 999-99-99' );
	});

	$(document).ready(function() {
		$( '#order_form_form' ).submit(function(event) {

			$( '#order_form_form .order_form_result' ).html( '<img src=\'". IMAGES ."ajax-loader.gif\' alt=\'\' class=\'order_form_loader\' />');

			event.preventDefault();

			user_hash = $( '#order_form_form #user_hash' ).val(),
			user_h1 = $( '#order_form_form #user_h1' ).val(),
			user_url = $( '#order_form_form #user_url' ).val(),
			user_name = $( '#order_form_form #user_name' ).val(),
			user_phone = $( '#order_form_form #user_phone' ).val();

			if (user_phone=='') {
				var user_phone_error = 1;
				$( '#order_form_form #user_phone' ).addClass( 'form-error' );
				$( '#order_form_form #user_phone' ).focus();
			} else {
				var user_phone_error = 0;
				$( '#order_form_form #user_phone' ).removeClass( 'form-error' );
			}
			if (user_name=='') {
				var user_name_error = 1;
				$( '#order_form_form #user_name' ).addClass( 'form-error' );
				$( '#order_form_form #user_name' ).focus();
			} else {
				var user_name_error = 0;
				$( '#order_form_form #user_name' ).removeClass( 'form-error' );
			}

			var url = $( this ).attr( 'action' );

			var posting = $.post(url, {
												user_hash: user_hash,
												user_h1: user_h1,
												user_url: user_url,
												user_name: user_name,
												user_phone: user_phone
			});
				 
			posting.done(function( data ) {
				var content = $( data ).find( '#order_form .order_form_message' );
				$( '#order_form .order_form_result' ).empty().html( content );
			});

			if (user_phone_error!=1 && user_name_error!=1) {
				$( '#order_form_form #user_name' ).val( '' );
				$( '#order_form_form #user_phone' ).val( '' );
				$( '#order_form .order_form_body' ).css( 'display', 'none' );
			}
		});

		$( '#order_form_button' ).click(function() {
			var user_h1 = $( 'h1' ).text();
			$( '#order_form_form #user_h1' ).val( user_h1 );
			var user_url = window.location;
			$( '#order_form_form #user_url' ).val( user_url );

			$( '#order_form_form #user_name' ).removeClass( 'form-error' );
			$( '#order_form_form #user_phone' ).removeClass( 'form-error' );
			$( '#order_form .order_form_result' ).empty();
			$( '#order_form' ).css( 'display', 'block' );
			$( '#order_form .order_form_body' ).css( 'display', 'block' );
			return false;
		});
		$( '#order_form .order_form_overlow, #order_form .order_form_close' ).click(function() {
			$( '#order_form' ).css( 'display', 'none' );
			return false;
		});
	});
	//-->
</script>
");