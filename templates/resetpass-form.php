<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="tml tml-resetpass" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php $template->the_action_template_message( 'resetpass' ); ?>
	<?php $template->the_errors(); ?>
	<form name="resetpassform" id="resetpassform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'resetpass', 'login_post' ); ?>" method="post" autocomplete="off">

		<div class="user-pass1-wrap">
			<p>
				<label for="pass1"><?php _e( 'New password', 'theme-my-login' ); ?></label>
			</p>

			<div class="wp-pwd">
				<div class="password-input-wrapper">
					<input type="password" data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1" id="pass1" class="input password-input" value="" autocomplete="off" aria-describedby="pass-strength-result" />
					<span class="wp-hide-pw hide-if-no-js">
						<span class="dashicons dashicons-hidden"></span>
					</span>
				</div>
				<div id="pass-strength-result" class="hide-if-no-js" aria-live="polite"><?php _e( 'Strength indicator', 'theme-my-login' ); ?></div>
			</div>
			<div class="pw-weak">
				<label>
					<input type="checkbox" name="pw_weak" class="pw-checkbox" />
					<?php _e( 'Confirm use of weak password', 'theme-my-login' ); ?>
				</label>
			</div>
		</div>

		<p class="user-pass2-wrap">
			<label for="pass2"><?php _e( 'Confirm new password', 'theme-my-login' ); ?></label>
			<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" />
		</p>

		<p class="description indicator-hint"><?php echo wp_get_password_hint(); ?></p>

		<?php do_action( 'resetpassword_form' ); ?>

		<p class="tml-submit-wrap">
			<input type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="<?php esc_attr_e( 'Reset Password', 'theme-my-login' ); ?>" />
			<input type="hidden" id="user_login" value="<?php echo esc_attr( $GLOBALS['rp_login'] ); ?>" autocomplete="off" />
			<input type="hidden" name="rp_key" value="<?php echo esc_attr( $GLOBALS['rp_key'] ); ?>" />
			<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
			<input type="hidden" name="action" value="resetpass" />
		</p>
	</form>
	<?php $template->the_action_links( array(
		'login' => false,
		'register' => false,
		'lostpassword' => false
	) ); ?>
</div>
