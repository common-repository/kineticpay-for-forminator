<?php
$plugin_url              = forminator_plugin_url();
$kineticpay_min_php_version  = apply_filters( 'forminator_payments_kineticpay_min_php_version', '5.6.0' );
$kineticpay_is_configured    = false;
$forminator_currencies   = forminator_currency_list();
$kineticpay_default_currency = 'RM';

	try {
		$kineticpay = new Forminator_Gateway_Kineticpay();

		$kineticpay_default_currency = $kineticpay->get_default_currency();
		if ( $kineticpay->is_live_ready() ) {
			$kineticpay_is_configured = true;
		}
	} catch ( Forminator_Gateway_Exception $e ) {
		$kineticpay_is_configured = false;
	}
?>

<div class="sui-box-settings-col-1">

	<span class="sui-settings-label"><?php esc_html_e( 'Kineticpay', 'kineticpay-forminator' ); ?></span>

	<span class="sui-description"><?php esc_html_e( 'Use Kineticpay Checkout to process payments in your forms.', 'kineticpay-forminator' ); ?></span>

</div>

<div class="sui-box-settings-col-2">

		<span class="sui-settings-label"><?php esc_html_e( 'Settings', 'kineticpay-forminator' ); ?></span>

		<span class="sui-description"><?php esc_html_e( 'Setup your Kineticpay account with Forminator to use kineticpay field for processing payments in your forms.', 'kineticpay-forminator' ); ?></span>

		<?php if ( ! $kineticpay_is_configured ) { ?>

			<div class="sui-form-field" style="margin-top: 10px;">

					<button
						class="sui-button kineticpay-connect-modal"
						type="button"
						data-modal-image="<?php echo esc_url( $plugin_url . 'assets/images/kineticpay-logo.png' ); ?>"
						data-modal-image-x2="<?php echo esc_url( $plugin_url . 'assets/images/kineticpay-logo@2x.png' ); ?>"
						data-modal-title="<?php esc_html_e( 'Kineticpay Account Settings', 'kineticpay-forminator' ); ?>"
						data-modal-nonce="<?php echo esc_html( wp_create_nonce( 'forminator_kineticpay_settings_modal' ) ); ?>"
					>
						<?php esc_html_e( 'Kineticpay Account Settings', 'kineticpay-forminator' ); ?>
					</button>

			</div>

		<?php } else { ?>

			<?php
			// SETTINGS: Authorization.
			?>
			<table class="sui-table" style="margin-top: 10px;">

				<thead>

					<tr>
						<th><?php esc_html_e( 'Key Type', 'kineticpay-forminator' ); ?></th>
						<th colspan="2"><?php esc_html_e( 'Merchent Key', 'kineticpay-forminator' ); ?></th>
					</tr>

				</thead>

				<tbody>

					<tr>
						<td class="sui-table-title"><?php esc_html_e( 'Merchent Key', 'kineticpay-forminator' ); ?></td>
						<td colspan="2"><span style="display: block; word-break: break-all;" id="mkey"><?php echo esc_html( $kineticpay->get_merchent_key() ); ?></span></td>
					</tr>
					<tr>
						<td class="sui-table-title"><?php esc_html_e( 'Bill Description', 'kineticpay-forminator' ); ?></td>
						<td colspan="2"><span style="display: block; word-break: break-all;" id="mbdesc"><?php echo esc_html( $kineticpay->get_bill_description() ); ?></span></td>
					</tr>

				</tbody>

				<tfoot>

					<tr>

						<td colspan="3">

							<div class="fui-buttons-alignment">

								<button
									class="sui-button kineticpay-connect-modal"
									type="button"
									data-modal-image="<?php echo esc_url( $plugin_url . 'assets/images/kineticpay-logo.png' ); ?>"
									data-modal-image-x2="<?php echo esc_url( $plugin_url . 'assets/images/kineticpay-logo@2x.png' ); ?>"
									data-modal-title="<?php esc_html_e( 'Kineticpay Account Settings', 'kineticpay-forminator' ); ?>"
									data-modal-nonce="<?php echo esc_html( wp_create_nonce( 'forminator_kineticpay_settings_modal' ) ); ?>"
								>
									<?php esc_html_e( 'Update Settings', 'kineticpay-forminator' ); ?>
								</button>

							</div>

						</td>

					</tr>

				</tfoot>

			</table>

			<?php // SETTINGS: Default Charge Currency. ?>
			<div class="sui-form-field" style="display: none;">

				<label for="forminator-kineticpay-currency" class="sui-settings-label"><?php esc_html_e( 'Default charge currency', 'kineticpay-forminator' ); ?></label>

				<span class="sui-description" aria-describedby="forminator-kineticpay-currency"><?php esc_html_e( 'Choose the default charge currency for your Kineticpay payments.', 'kineticpay-forminator' ); ?></span>

				<div style="max-width: 240px; display: block; margin-top: 10px;">

					<select class="sui-select" id="forminator-kineticpay-currency" name="kineticpay-default-currency">
						<?php foreach ( $forminator_currencies as $currency => $currency_nice ) : ?>
							<option value="<?php echo esc_attr( $currency ); ?>" <?php echo selected( $currency, $kineticpay_default_currency ); ?>><?php echo esc_html( $currency ); ?></option>
						<?php endforeach; ?>
					</select>

				</div>

			</div>

		<?php } ?>

</div>
