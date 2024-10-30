<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Kineticpay_Forminator_Admin_AJAX
 *
 * @since 1.0.0
 */
class Kineticpay_Forminator_Public {
	
	public function __construct() {
		add_filter( 'forminator_render_form_submit_markup', array( $this, 'kineticpay_display' ), 10, 6 );
		add_action( 'forminator_custom_form_submit_before_set_fields', array( $this, 'kineticpay_inject_entry_id' ), 20, 2 );
		add_action( 'forminator_form_before_save_entry', array( $this, 'check_bank_code_phone' ), 10, 1 );
		add_action( 'forminator_form_after_save_entry', array( $this, 'start_kineicpay_payment' ), 10, 2 );
		add_action( 'forminator_form_after_handle_submit', array( $this, 'show_kineicpay_payment' ), 10, 2 );
		add_action('wp', array( $this, 'forminator_kineticpay_thankyou_page' ), 8);
	}
	
	public function kineticpay_display($html, $form_id, $post_id, $nonce) {
		$rlt_kinetic_form_enabled = get_option('rlt_kinetic_form_enabled_' . $form_id, 'false');
		if ($rlt_kinetic_form_enabled == 'true'){
			$imagesrc = FMNTR_KINETICPAY_URL . 'assets/images/kineticpay.png';
			$button_lang = __("Pay with kineticpay", "give-kineticpay");		
			$title = __('Pay With Kineticpay', 'give-kineticpay');
			$banks = '<style>.kineticpay-title{align-items: center;display: flex!important;}.kineticpay-logo{width: 100px;margin-left: 20px;}#bank_id{height: 50px;padding: 10px;border: 1px solid #253d80;}#bank_id option{font-size: 14px;font-weight: 500;color: #253d80;padding: 20px;}</style><div class="customs-select" style="margin-top: 10px; margin-bottom: 20px;">
			<h3 class="kineticpay-title">'.$title.'<img class="rounded kineticpay-logo" src="'.$imagesrc.'"></h3>
			<div class="forminator-field"><label style="font-weight: 600;">Select Bank:</label>
			<select id="bank_id" name="bank_id" required>
				<option value="">Select Your Bank</option>
				<option value="ABMB0212">Alliance Bank Malaysia Berhad</option>
				<option value="ABB0233">Affin Bank Berhad</option>
				<option value="AMBB0209">Ambank (M) Berhad</option>
				<option value="BCBB0235">CIMB Bank Berhad</option>
				<option value="BIMB0340">Bank Islam Malaysia Berhad</option>
				<option value="BKRM0602">Bank Kerjasama Rakyat Malaysia Berhad</option>
				<option value="BMMB0341">Bank Muamalat Malaysia Berhad</option>
				<option value="BSN0601">Bank Simpanan Nasional</option>
				<option value="CIT0219">Citibank Berhad</option>
				<option value="HLB0224">Hong Leong Bank Berhad</option>
				<option value="HSBC0223">HSBC Bank Malaysia Berhad</option>
				<option value="KFH0346">Kuwait Finance House</option>
				<option value="MB2U0227">Maybank2u / Malayan Banking Berhad</option>
				<option value="MBB0228">Maybank2E / Malayan Banking Berhad E</option>
				<option value="OCBC0229">OCBC Bank (Malaysia) Berhad</option>
				<option value="PBB0233">Public Bank Berhad</option>
				<option value="RHB0218">RHB Bank Berhad</option>
				<option value="SCB0216">Standard Chartered Bank Malaysia Berhad</option>
				<option value="UOB0226">United Overseas Bank (Malaysia) Berhad</option>
			</select></div>';
			$button = '<span class="forminator-error-message" aria-hidden="true">This field is required.</span></div>';
			$html_new = $banks . $button . $html;
			return $html_new;
		}
		return $html;
	}
	
	public function check_bank_code_phone($form_id) {
		$rlt_kinetic_form_enabled = get_option('rlt_kinetic_form_enabled_' . $form_id, 'false');
		if ($rlt_kinetic_form_enabled == 'true'){
			$phone = isset($_POST["phone-1"]) ? sanitize_text_field($_POST["phone-1"]) : '';
			$bank_code = isset($_POST["bank_id"]) ? sanitize_text_field($_POST["bank_id"]) : '';
			$error = '';
			if (empty($phone)) {
				$error .= 'Phone number is required! ';
			}
			if (empty($bank_code)) {
				$error .= 'Please select a bank!';
			}
			if ($error) {
				wp_send_json_error( $error );
			}
		}
	}
	public function kineticpay_inject_entry_id($entry, $form_id) {
		$rlt_kinetic_form_enabled = get_option('rlt_kinetic_form_enabled_' . $form_id, 'false');
		if ($rlt_kinetic_form_enabled == 'true'){
			$_POST['_kineticpay_entry_id'] = $entry->entry_id;
		}
	}
	public function start_kineicpay_payment($form_id, $response) {
		$rlt_kinetic_form_enabled = get_option('rlt_kinetic_form_enabled_' . $form_id, 'false');
		if ($rlt_kinetic_form_enabled == 'true' && is_array($response) && isset($response['success'])) {
			$kineticpay_api = new Forminator_Gateway_Kineticpay();
			$name = '';
			$name = isset($_POST['name-1-first-name']) ? sanitize_text_field($_POST['name-1-first-name']) . ' ' : '';
			$name .= isset($_POST['name-1-last-name']) ? sanitize_text_field($_POST['name-1-last-name']) : $name;
			$email = isset($_POST['email-1']) ? sanitize_email($_POST['email-1']) : '';
			$mobile = isset($_POST['phone-1']) ? sanitize_text_field($_POST['phone-1']) : '';
			$user_bank = isset($_POST['bank_id']) ? sanitize_text_field($_POST['bank_id']) : '';
			$current_url = isset($_POST['current_url']) ? sanitize_text_field($_POST['current_url']) : '';
			$entry_id = isset($_POST['_kineticpay_entry_id']) ? sanitize_text_field($_POST['_kineticpay_entry_id']) : 0;
			$pid = (int)get_option('forminator_kineticpay_last_pid');
			$render_id = isset($_POST['render_id']) ? sanitize_text_field($_POST['render_id']) : 0;
			$amount = isset($_POST['currency-1']) ? sanitize_text_field($_POST['currency-1']) : '';
			if ($pid === 0) {
				$pay_id = $entry_id;
			} else {
				$pay_id = $pid + 1;
			}
			$return_url = $kineticpay_api->return_url($current_url, $form_id, $entry_id, $pay_id, $render_id);
			$kineticpay_api->purpose = $kineticpay_api->get_bill_description();
			$kineticpay_api->amount = $amount;
			$kineticpay_api->buyer_name = $name;
			$kineticpay_api->email = trim($email);
			$kineticpay_api->phone = trim($mobile);
			$kineticpay_api->billcode = FMNTR_PRODUCT_CODE . $pay_id;
			$kineticpay_api->bank_id = $user_bank;
			$kineticpay_api->kineticpay_success_url = $return_url;
			$kineticpay_api->fail_url = $return_url;
			$billcode_response = $kineticpay_api->create_billcode();
			update_option('forminator_kineticpay_last_pid', $pay_id);
			$error_message = 'Payment Failed: ';
			$html = '';
			$entry_meta = array(array('name' => 'kineticpay_response', 'value' => $response));
			Forminator_API::update_form_entry($form_id, $entry_id, $entry_meta);
			if (isset($billcode_response["error"])) {
				foreach ($billcode_response["error"] as $error) {
					if (is_array($error) && isset($error[0])) {
						$error_message .= esc_html($error[0]);
					}
					if (is_string($error)) {
						$error_message .= esc_html($error);
					}
				}	
			} else {
				if (isset($billcode_response["html"])) {
					$html = $billcode_response["html"];
				} else {
					$error = isset($billcode_response[0]) ? $billcode_response[0] : __("Payment was declined. Something error with payment gateway.", "kineticpay-forminator");
					$error_message .= esc_html($error);
				}
			}
			
			if ($html) {
				$response["message"] = $html;
				wp_send_json_success( $response );
			}
			
			if ($error_message) {				
				$response["message"] = $error_message;
				wp_send_json_success( $response );
			}
			
			wp_send_json_success( $html );
			
			exit();
		}
	}
	public function show_kineicpay_payment($form_id, $response) {
		$rlt_kinetic_form_enabled = get_option('rlt_kinetic_form_enabled_' . $form_id, 'false');
		if ($rlt_kinetic_form_enabled == 'true' && is_array($response) && isset($response['success'])) {
			$kineticpay_api = new Forminator_Gateway_Kineticpay();
			$name = '';
			$name = isset($_POST['name-1-first-name']) ? sanitize_text_field($_POST['name-1-first-name']) . ' ' : '';
			$name .= isset($_POST['name-1-last-name']) ? sanitize_text_field($_POST['name-1-last-name']) : $name;
			$email = isset($_POST['email-1']) ? sanitize_email($_POST['email-1']) : '';
			$mobile = isset($_POST['phone-1']) ? sanitize_text_field($_POST['phone-1']) : '';
			$user_bank = isset($_POST['bank_id']) ? sanitize_text_field($_POST['bank_id']) : '';
			$current_url = isset($_POST['current_url']) ? sanitize_text_field($_POST['current_url']) : '';
			$entry_id = isset($_POST['_kineticpay_entry_id']) ? sanitize_text_field($_POST['_kineticpay_entry_id']) : 0;
			$pid = (int)get_option('forminator_kineticpay_last_pid');
			$render_id = isset($_POST['render_id']) ? sanitize_text_field($_POST['render_id']) : 0;
			$amount = isset($_POST['currency-1']) ? sanitize_text_field($_POST['currency-1']) : '';
			if ($pid === 0) {
				$pay_id = $entry_id;
			} else {
				$pay_id = $pid + 1;
			}
			$return_url = $kineticpay_api->return_url($current_url, $form_id, $entry_id, $pay_id, $render_id);
			$kineticpay_api->purpose = $kineticpay_api->get_bill_description();
			$kineticpay_api->amount = $amount;
			$kineticpay_api->buyer_name = $name;
			$kineticpay_api->email = trim($email);
			$kineticpay_api->phone = trim($mobile);
			$kineticpay_api->billcode = FMNTR_PRODUCT_CODE . $pay_id;
			$kineticpay_api->bank_id = $user_bank;
			$kineticpay_api->kineticpay_success_url = $return_url;
			$kineticpay_api->fail_url = $return_url;
			$billcode_response = $kineticpay_api->create_billcode();
			update_option('forminator_kineticpay_last_pid', $pay_id);
			$error_message = 'Payment Failed: ';
			$html = '';
			$entry_meta = array(array('name' => 'kineticpay_response', 'value' => $response));
			Forminator_API::update_form_entry($form_id, $entry_id, $entry_meta);
			if (isset($billcode_response["error"])) {
				foreach ($billcode_response["error"] as $error) {
					if (is_array($error) && isset($error[0])) {
						$error_message .= esc_html($error[0]);
					}
					if (is_string($error)) {
						$error_message .= esc_html($error);
					}
				}	
			} else {
				if (isset($billcode_response["html"])) {
					$html = $billcode_response["html"];
				} else {
					$error = isset($billcode_response[0]) ? $billcode_response[0] : __("Payment was declined. Something error with payment gateway.", "kineticpay-forminator");
					$error_message .= esc_html($error);
				}
			}
			if ($html) {
				echo wp_kses( $html, array(
            		    	'form' => array(
            		    		'action' => array(),
            		    		'method' => array(),
            		    		'id' => array(),
            		    	),
            		    	'input' => array(
            		    		'type' => array(),
            		    		'value' => array(),
            		    		'name' => array(),
            		    	),
            		    	'script' => array(
            		    		'src' => array(),
            		    	),
            		    ) );
				exit();
			}
			
			if ($error_message) {
				$error_message .= __(" Please try again!", "kineticpay-forminator");
				echo esc_html($error_message);
				exit();
			}
			
		}
	}
	public function forminator_kineticpay_thankyou_page() {
		$return_url_str = isset($_GET['forminator_kineticpay_return']) ? sanitize_text_field($_GET['forminator_kineticpay_return']) : '';
		if (empty($return_url_str)) {
			return;
		}
		$str = base64_decode($return_url_str);
        parse_str($str, $query);
        if (wp_hash('ids=' . $query['ids']) == $query['hash']) {
				$hash_array = explode('|', $query['ids']);
				$form_id = $hash_array[0];
				$entry_id = $hash_array[1];
				$payment_id = $hash_array[2];
				
				$entry = new Forminator_Form_Entry_Model($entry_id);
				
				$response = $entry->get_meta('response', '');
				
				$kineticpay_api = new Forminator_Gateway_Kineticpay();
				
				$payment_data = array("inovice_id" => FMNTR_PRODUCT_CODE . $payment_id, "status" => "PENDING");
				
				$response_kineticpay = $kineticpay_api->success_action(FMNTR_PRODUCT_CODE . $payment_id);
				if( isset($response_kineticpay['code']) && $response_kineticpay['code'] == '00' )
				{
					$payment_data['status'] = 'COMPLETED';
					$payment_data['response_kineticpay'] = $response_kineticpay;
				} else {
					$payment_data['status'] = 'FAILED';
					$payment_data['response_kineticpay'] = $response_kineticpay;
				}
				
				$entry_meta = array(array('name' => 'kineticpay_payment_details', 'value' => $payment_data));
				
				Forminator_API::update_form_entry($form_id, $entry_id, $entry_meta);
				
            if ( $response && is_array( $response ) ) {
				if ( $response['success'] ) {
						if ( isset( $response['url'] ) && ( ! isset( $response['newtab'] ) || 'sametab' === $response['newtab'] ) ) {
							$url = $response['url'];
							wp_redirect( $url );
							exit;
						} else {
							add_action( 'forminator_form_post_message', array( $this, 'kineticpay_response_message' ), 10, 2 );
							// cleanup submitted data.
							$_POST = array();
						}
				} else {
					if ( $response['message'] ) {
						add_action( 'forminator_form_post_message', array( $this, 'kineticpay_response_message' ), 10, 2 );
						// cleanup submitted data.
						$_POST = array();
					}
					add_action( 'wp_footer', array( $this, 'footer_message' ) );
				}
			}
        }
	}
	public function kineticpay_response_message($form_id, $render_id) {
		$return_url_str = isset($_GET['forminator_kineticpay_return']) ? sanitize_text_field($_GET['forminator_kineticpay_return']) : '';
		if (empty($return_url_str)) {
			return;
		}
		$str = base64_decode($return_url_str);
        parse_str($str, $query);
        
		$hash_array = explode('|', $query['ids']);
		$post_form_id = $hash_array[0];
		$entry_id = $hash_array[1];
		$post_render_id = $hash_array[3];
				
		$entry = new Forminator_Form_Entry_Model($entry_id);
				
		$response = $entry->get_meta('response', '');
		
		//only show to related form
		if ( ! empty( $response ) && is_array( $response ) && (int) $form_id === (int) $post_form_id && (int) $render_id === (int) $post_render_id ) {
			$label_class = $response['success'] ? 'forminator-success' : 'forminator-error';
			?>
			<div class="forminator-response-message forminator-show <?php echo esc_attr( $label_class ); ?>"
				 tabindex="-1">
				<label class="forminator-label--<?php echo esc_attr( $label_class ); ?>"><?php echo wp_kses_post( $response['message'] ); ?></label>
				<?php
				if ( isset( $response['errors'] ) && ! empty( $response['errors'] ) ) {
					?>
					<ul class="forminator-screen-reader-only">
						<?php
						foreach ( $response['errors'] as $key => $error ) {
							foreach ( $error as $id => $value ) {
								?>
								<li><?php echo esc_html( $value ); ?></li>
								<?php
							}
						}
						?>
					</ul>
					<?php
				}
				?>
			</div>
			<?php

			if ( isset( $response['success'] ) && $response['success'] && isset( $response['behav'] ) && ( 'behaviour-hide' === $response['behav'] || ( isset( $response['newtab'] ) && 'newtab_hide' === $response['newtab'] ) ) ) {
				$selector = '#forminator-module-' . $form_id . '[data-forminator-render="' . $render_id . '"]';
				?>
				<script type="text/javascript">var ForminatorFormHider =
					<?php
					echo wp_json_encode(
						array(
							'selector' => $selector,
						)
					);
					?>
				</script>
				<?php
			}
			if ( isset( $response['success'] ) && $response['success'] && isset( $response['behav'] ) && 'behaviour-redirect' === $response['behav'] && isset( $response['newtab'] ) && ( 'newtab_hide' === $response['newtab'] || 'newtab_thankyou' === $response['newtab'] ) ) {
				$url = $response['url'];
				?>
				<script type="text/javascript">var ForminatorFormNewTabRedirect =
					<?php
					echo wp_json_encode(
						array(
							'url' => $url,
						)
					);
					?>
				</script>
				<?php
			}
			$selector  = '#forminator-module-' . $form_id . '[data-forminator-render="' . $render_id . '"]';
			if ( ! empty( $response['errors'] ) ) {
				?>
				<script type="text/javascript">var ForminatorValidationErrors =
					<?php
					echo wp_json_encode(
						array(
							'selector' => $selector,
							'errors'   => $response['errors'],
						)
					);
					?>
				</script>
				<?php
			}
		}
	}
}
new Kineticpay_Forminator_Public();
