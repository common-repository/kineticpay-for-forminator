<?php
// defaults.
$vars = array(
	'error_message'     => '',
	'merchent_key'          => '',
	'merchent_key_error'    => '',
	'bill_description'       => '',
	'bill_description_error' => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>

<p class="sui-description" style="margin-top: 0; text-align: center;"><?php esc_html_e( 'Enter your Merchent Key, Obtain your merchant key from your kineticPay dashboard.', 'kineticpay-forminator'  ); ?></p>

<?php if ( ! empty( $vars['error_message'] ) ) : ?>

	<div
		role="alert"
		class="sui-notice sui-notice-red sui-active"
		style="display: block; text-align: left;"
		aria-live="assertive"
	>

		<div class="sui-notice-content">

			<div class="sui-notice-message">

				<span class="sui-notice-icon sui-icon-info" aria-hidden="true"></span>

				<p><?php echo esc_html( $vars['error_message'] ); ?></p>

			</div>

		</div>

	</div>

<?php endif; ?>

<form class="sui-form-field">

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['merchent_key_error'] ) ? 'sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'Merchent Key', 'kineticpay-forminator' ); ?></label>

		<input
			class="sui-form-control"
			name="merchent_key" id="merchent_key" placeholder="<?php echo esc_attr( __( 'Enter your Merchent key', 'kineticpay-forminator' ) ); ?>"
			value="<?php echo esc_attr( $vars['merchent_key'] ); ?>"
		/>

		<?php if ( ! empty( $vars['merchent_key_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['merchent_key_error'] ); ?></span>
		<?php endif; ?>

	</div>

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['bill_description_error'] ) ? 'sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'Bill Description', 'kineticpay-forminator' ); ?></label>

		<input
			class="sui-form-control"
			name="bill_description" id="bill_description" placeholder="<?php echo esc_attr( __( 'Enter description to be included in the bill', 'kineticpay-forminator' ) ); ?>"
			value="<?php echo esc_attr( $vars['bill_description'] ); ?>"
		/>

		<?php if ( ! empty( $vars['bill_description_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['bill_description_error'] ); ?></span>
		<?php endif; ?>

	</div>

</form>
