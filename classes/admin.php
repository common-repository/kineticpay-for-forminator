<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Kineticpay_Forminator_Admin_AJAX
 *
 * @since 1.0.0
 */
class Kineticpay_Forminator_Admin_AJAX {
	
	private $_nonce = "forminator_kineticpay_settings_modal";
	
	/**
	 * Kineticpay_Forminator_Admin_AJAX constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		
		add_action( 'wp_ajax_kineticpay_settings_modal', array( $this, 'kineticpay_settings_modal' ) );
		add_action( 'wp_ajax_kineticpay_update_page', array( $this, 'kineticpay_update_page' ) );
		add_action( 'wp_ajax_kn_forminator_form_settings', array( $this, 'kn_forminator_form_settings' ) );
		add_action( 'admin_print_scripts', array( $this, 'set_kinetic_js'), 99 );
		add_filter('forminator_custom_form_entries_iterator', array($this, 'trt'), 10, 2);
	}
	public function trt( $iterator, $entry) {
		if (isset($entry->meta_data["kineticpay_payment_details"])) {
			$kineticpay_payment_details = $entry->meta_data["kineticpay_payment_details"];

			$kn_status = $kineticpay_payment_details["value"]["status"];
			$kn_invoiceid = $kineticpay_payment_details["value"]["inovice_id"];
			
			$l1 = __("Kineticpay Details", "kineticpay-forminator");
			$l2 = __("Status", "kineticpay-forminator");
			$l3 = __("Invoice ID", "kineticpay-forminator");
			
			$iterator["detail"]["items"][] = array("type" => "name", "label" => $l1, "value" => "", "sub_entries" => array(array("key" => "", "label" => $l2, "value" => $kn_status),array("key" => "", "label" => $l3, "value" => $kn_invoiceid)));
		}
		return $iterator;
	}
	/**
	 * Handle kineticpay settings
	 *
	 * @since 1.0.0
	 */
	public function kineticpay_settings_modal() {
		if ( ! class_exists( 'Forminator_Gateway_Kineticpay' ) ) {
			return false;
		}

		// Validate nonce
		if ( wp_verify_nonce( sanitize_text_field($_POST['security']), $this->_nonce ) === false ) {
			wp_send_json_error( __( 'Invalid request, you are not allowed to do that action.', 'kineticpay-forminator' ) );
		}

		$data = array();

		$post_data          = Forminator_Core::sanitize_array( $_POST );
		$is_connect_request = isset( $post_data['connect'] ) ? $post_data['connect'] : false;
		$template_vars      = array();
		$kineticpay = new Forminator_Gateway_Kineticpay();
		try {
		$merchent_key         = isset( $post_data['merchent_key'] ) ? $post_data['merchent_key'] : $kineticpay->get_merchent_key();
		$bill_description      = isset( $post_data['bill_description'] ) ? $post_data['bill_description'] : $kineticpay->get_bill_description();
		$default_currency = $kineticpay->get_default_currency();

		$template_vars['merchent_key']    = $merchent_key;
		$template_vars['bill_description'] = $bill_description;

		if ( ! empty( $is_connect_request ) ) {
			
			if ( empty( $merchent_key ) ) {
				throw new Forminator_Gateway_Exception(
					'',
					Forminator_Gateway_Kineticpay::EMPTY_merchent_key_EXCEPTION
				);
			}
			if ( empty( $bill_description ) ) {
				throw new Forminator_Gateway_Exception(
					'',
					Forminator_Gateway_Kineticpay::EMPTY_bill_description_EXCEPTION
				);
			}

			Forminator_Gateway_Kineticpay::store_settings(
				array(
					'merchent_key'         => $merchent_key,
					'bill_description'      => $bill_description,
					'default_currency' => $default_currency,
				)
			);

			$data['notification'] = array(
				'type'     => 'success',
				'text'     => __( 'Kineticpay account settings are updated successfully. You can now enable kineticpay from your forms settings tab and start collecting payments.', 'kineticpay-forminator' ),
				'duration' => '4000',
			);

			}
		} catch ( Forminator_Gateway_Exception $e ) {
			forminator_maybe_log( __METHOD__, $e->getMessage(), $e->getTrace() );
			$template_vars['error_message'] = $e->getMessage();

			if ( Forminator_Gateway_Kineticpay::EMPTY_merchent_key_EXCEPTION === $e->getCode() ) {
				$template_vars['merchent_key_error'] = __( 'Please input merchent key' );
			}
			if ( Forminator_Gateway_Kineticpay::EMPTY_bill_description_EXCEPTION === $e->getCode() ) {
				$template_vars['bill_description_error'] = __( 'Please input bill description' );
			}
		}

		ob_start();
		/** @noinspection PhpIncludeInspection */
		include FMNTR_KINETICPAY_PATH . '/views/admin/kineticpay.php';
		$html = ob_get_clean();

		$data['html'] = $html;

		$data['buttons'] = '<div class="sui-actions-right">' .
		                                        '<button class="sui-button forminator-kineticpay-connect" type="button" data-nonce="' . wp_create_nonce( 'forminator_kineticpay_settings_modal' ) . '">' .
		                                        '<span class="sui-loading-text">' . esc_html__( 'Update Settings', 'kineticpay-forminator' ) . '</span>' .
		                                        '<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>' .
		                                        '</button>' .
		                                        '</div>';

		wp_send_json_success( $data );
	}
	
	/**
	 * Handle kineticpay settings
	 *
	 * @since 1.0.0
	 */
	public function kineticpay_update_page() {
		// Validate nonce
		if ( wp_verify_nonce( sanitize_text_field($_POST['security']), $this->_nonce ) === false ) {
			wp_send_json_error( __( 'Invalid request, you are not allowed to do that action.', 'kineticpay-forminator' ) );
		}
		$popnot = isset($_POST['is']) ? sanitize_text_field($_POST['is']) : '';
		$file = FMNTR_KINETICPAY_PATH . '/views/admin/section-kineticpay.php';

		ob_start();
		
		
		if ($popnot) { ?>
			<div id="sui-box-kineticpay" class="sui-box-settings-row">
		<?php }
		/** @noinspection PhpIncludeInspection */
		include $file;
		if ($popnot) { ?>
			</div>
		<?php }
		$html = ob_get_clean();
		if ($popnot) {
			$data['html'] = $html;
			$data['popup_html'] = '<div class="sui-modal sui-modal-sm"><div tabindex="0" class="sui-modal-overlay"></div><div role="dialog" id="forminator-kineticpay-popup" class="sui-modal-content" aria-modal="true" aria-labelledby="forminator-kineticpay-popup__title" aria-describedby=""><div class="sui-box"><div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60"><figure class="sui-box-logo" aria-hidden="true" style="width: 70%;height: auto;"><img src="'.FMNTR_KINETICPAY_URL.'/assets/images/kineticpay-icon.png" alt="'.esc_html__("Kineticpay Account Settings", "kineticpay-forminator").'" style="width: 100%;"></figure><button class="sui-button-icon sui-button-float--right forminator-popup-close"><span class="sui-icon-close sui-md" aria-hidden="true"></span><span class="sui-screen-reader-text">Close this dialog window</span></button><h3 id="forminator-kineticpay-popup__title" class="sui-box-title sui-lg" style="overflow: initial; white-space: normal; text-overflow: initial;">'.esc_html__("Kineticpay Account Settings", "kineticpay-forminator").'</h3></div><div class="sui-box-body sui-spacing-top--10"></div><div class="sui-box-footer sui-flatten sui-content-right" style="padding-top: 0px;"><div class="sui-actions-right"></div></div></div></div><div tabindex="0"></div></div>';
			wp_send_json_success( $data );
		} else {
			wp_send_json_success( $html );
		}
	}
	
	public function kn_forminator_form_settings() {
		// Validate nonce
		if ( wp_verify_nonce( sanitize_text_field($_POST['security']), $this->_nonce ) === false ) {
			wp_send_json_error( __( 'Invalid request, you are not allowed to do that action.', 'kineticpay-forminator' ) );
		}
		$form_id = isset($_POST['form_id']) ? sanitize_text_field($_POST['form_id']) : '';
		$enabled_is = isset($_POST['enabled']) ? sanitize_text_field($_POST['enabled']) : '';
		update_option( 'rlt_kinetic_form_enabled_'. $form_id, $enabled_is );
		if ($enabled_is === "true") {
			$data['notification'] = array(
					'type'     => 'success',
					'text'     => __( 'Kineticpay is enabled for this form.', 'kineticpay-forminator' ),
					'duration' => '4000',
			);
		} else {
			$data['notification'] = array(
					'type'     => 'success',
					'text'     => __( 'Kineticpay is disabled for this form.', 'kineticpay-forminator' ),
					'duration' => '4000',
			);
		}
		wp_send_json_success( $data );
	}
	
	public function set_kinetic_js()
	{
		$file = FMNTR_KINETICPAY_PATH . '/views/admin/section-kineticpay.php';

		ob_start();
		/** @noinspection PhpIncludeInspection */
		include $file;
		$html = ob_get_clean();
		$loading_html = '<p class="fui-loading-modal"><i class="sui-icon-loader sui-loading" aria-hidden="true"></i></p>';
		$form_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : 0;
		$rlt_kinetic_form_enabled = get_option('rlt_kinetic_form_enabled_' . $form_id, 'false');
		$is_checked = '';
		if ($rlt_kinetic_form_enabled == 'true') {
			$is_checked = 'checked';
		}
		$html_set = '<div class="sui-box-settings-row"><div class="sui-box-settings-col-1"><span class="sui-settings-label">'.esc_html__("Kineticpay", "kineticpay-forminator").'</span><span class="sui-description">'.esc_html__("Enable Kineticpay?", "kineticpay-forminator").'</span></div><div class="sui-box-settings-col-2"><label for="forminator-field-enable_kineticpay" class="sui-toggle"><input type="checkbox" id="forminator-field-enable_kineticpay" class="sui-form-control" value="'.$rlt_kinetic_form_enabled.'" '.$is_checked.'><span class="sui-toggle-slider"></span><span class="sui-screen-reader-text">'.esc_html__("Enable Kineticpay", "kineticpay-forminator").'</span><label for="forminator-field-enable_kineticpay" class="sui-toggle-label">'.esc_html__("Enable Kineticpay", "kineticpay-forminator").'</label></label><span class="sui-description sui-toggle-description">'.esc_html__("Disable this feature to prevent showing kineticpay form. If you enable Kineticpay then make sure to add a currency, mobile phone, name and email fields too!", "kineticpay-forminator").'</span></div></div>';
		$js = "<script>
			jQuery( document ).ready( function ($) { ";
				$kn = new Forminator_Gateway_Kineticpay();
				$page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
		if ($kn->is_live_ready() && $page === 'forminator-cform-wizard') {						
		$js .=	"$( document ).on('click', '.sui-vertical-tab a', function(e){
					if ($(this).attr('href')==='/settings') {
						var html_set = '".$html_set."';
						$('#forminator-form-appearance').find('.sui-box-body').append(html_set);
					}
				});
				$( document ).on('change', '#forminator-field-enable_kineticpay', function(e){
					if ($(this).is(':checked')) {
						$(this).val('true');
					} else {
						$(this).val('false');
					}
					var _form_id = '".$form_id."';
					var _enabled = $(this).val();
					$.ajax( {
						url: '".admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' )."',
						type: 'post',
						data: {
							action: 'kn_forminator_form_settings',
							security: '".wp_create_nonce($this->_nonce)."',
							form_id: _form_id,
							enabled: _enabled,
						},
						beforeSend: function(){
						},
						complete: function(){
						},
						success: function ( response ) {
							alert(response.data.notification.text);
						}
					} );
				});";	
		}	
		if ($page === 'forminator-settings') {
		$js .=	"$.ajax( {
					url: '".admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' )."',
					type: 'post',
					data: {
						action: 'kineticpay_update_page',
						security: '".wp_create_nonce($this->_nonce)."',
						'is': 'popnot'
					},
					beforeSend: function(){
					},
					complete: function(){
					},
					success: function ( response ) {
						$( response.data.html ).insertAfter('#sui-box-paypal');
						$( '.sui-wrap' ).append(response.data.popup_html);
					}
				} );";
		}		
		$js .=	"$( document ).on('click', '.kineticpay-connect-modal', function(e){
					var html_p = '".$loading_html."';
					$('.sui-modal').find('.sui-box-body').html(html_p);
					SUI.openModal('forminator-kineticpay-popup','wpbody',void 0,!0,!0);
					$.ajax( {
						url: '".admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' )."',
						type: 'post',
						data: {
							action: 'kineticpay_settings_modal',
							security: '".wp_create_nonce($this->_nonce)."',
						},
						beforeSend: function(){
						},
						complete: function(){
						},
						success: function ( response ) {
							$('.sui-modal').find('.sui-box-header h3.sui-box-title').show();
							$('.sui-modal').find('.sui-box-body').html(response.data.html);
							$('.sui-modal').find('.sui-box-footer').html(response.data.buttons);
						}
					} );
				});	
				$( document ).on('click', '.forminator-kineticpay-connect', function(e){
					var _mkey = $('#merchent_key').val();
					var _bdesc = $('#bill_description').val();
					var html_p = '".$loading_html."';
					$('.sui-modal').find('.sui-box-body').html(html_p);
					$.ajax( {
						url: '".admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' )."',
						type: 'post',
						data: {
							action: 'kineticpay_settings_modal',
							security: '".wp_create_nonce($this->_nonce)."',
							'connect': 'true',
							'merchent_key': _mkey,
							'bill_description': _bdesc
						},
						beforeSend: function(){
						},
						complete: function(){
						},
						success: function ( response ) {
							$('.sui-modal').find('.sui-box-header h3.sui-box-title').show();
							$('.sui-modal').find('.sui-box-body').html(response.data.html);
							$('.sui-modal').find('.sui-box-footer').html(response.data.buttons);
							if (response.data.notification.type) {
								Forminator.Notification.open(response.data.notification.type,response.data.notification.text,response.data.notification.duration);
								$('#mkey').html(_mkey);
								$('#mbdesc').html(_bdesc);
							}
						}
					} );
				});
				$( document ).on('click', '.forminator-popup-close', function(e){
					SUI.closeModal();
					$('.sui-modal').removeClass('sui-active');
					$('html').removeClass('sui-has-model');
				});
			});	
			</script>";
		echo wp_kses( $js, array(
			'script' => array(),
			'p' => array(
				"class" => array(),
			),
			'div' => array(
				"class" => array(),
				"id" => array(),
				"for" => array(),
			),
			'label' => array(
				"class" => array(),
				"id" => array(),
				"for" => array(),
			),
			'span' => array(
				"class" => array(),
				"id" => array(),
				"for" => array(),
			),
			'input' => array(
				"class" => array(),
				"id" => array(),
				"type" => array(),
				"value" => array(),
				"checked" => array(),
			),
			'i' => array(
				"class" => array(),
				"aria-hidden" => array(),
			),
		) );
	}
	
}
new Kineticpay_Forminator_Admin_AJAX();