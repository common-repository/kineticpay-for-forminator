<?php
/**
 * Wrapper Kineticpay
 * Class Forminator_Gateway_Kineticpay
 *
 * @since 1.0.0
 */
class Forminator_Gateway_Kineticpay {

	/**
	 * Kineticpay Live Pub key
	 *
	 * @var string
	 */
	protected $merchent_key = '';

	/**
	 * Kineticpay Live Sec key
	 *
	 * @var string
	 */
	protected $bill_description = '';

	/**
	 * Default Currency for Kineticpay
	 *
	 * @var string
	 */
	protected $default_currency = 'RM';

	const EMPTY_bill_description_EXCEPTION = 110;
    const EMPTY_merchent_key_EXCEPTION = 786;

	public $purpose;
	public $amount;
	public $phone;
	public $bank_id;
	public $buyer_name;
	public $email;
	public $button_lang;
	public $kineticpay_bill_url;
	public $redirect_url;
	public $fail_url;
	public $billcode;
	public $getBillTransactions;
	public $kineticpay_success_url;

	/**
	 * Forminator_Gateway_Kineticpay constructor.
	 *
	 * @throws Forminator_Gateway_Exception
	 */
	public function __construct() {
		
		$config = get_option( 'forminator_kineticpay_configuration', array() );
		$this->default_currency = isset( $config['default_currency'] ) ? $config['default_currency'] : 'MYR';

		$this->merchent_key    = isset( $config['merchent_key'] ) ? $config['merchent_key'] : '';
		$this->bill_description = isset( $config['bill_description'] ) ? $config['bill_description'] : '';

	}
	
	public function is_live_ready() {
		return ! empty( $this->merchent_key );
	}

	/**
	 * @return string
	 */
	public function get_bill_description() {
		return $this->bill_description;
	}

	/**
	 * @return string
	 */
	public function get_default_currency() {
		return $this->default_currency;
	}

	/**
	 * @return string
	 */
	public function get_merchent_key() {
		return $this->merchent_key;
	}

	/**
	 * Store Kineticpay settings
	 *
	 * @param $settings
	 */
	public static function store_settings( $settings ) {
		update_option( 'forminator_kineticpay_configuration', $settings );
	}

	/**
	 * Get the exception error and return WP_Error
	 *
	 * @param $e
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error
	 */
	public function get_error( $e ) {
		$code = $e->getCode();

		if ( is_int( $code ) ) {
			$code = ( 0 === $code ) ? 'zero' : $code;

			return new WP_Error( $error_code, $e->getMessage() );
		} else {
			return new WP_Error( $e->getError()->code, $e->getError()->message );
		}
	}

    public function create_billcode()
	{
		// Get ID from user
		$bankid = $this->bank_id;
		// This is merchant_key get from Collection page
		$secretkey = $this->merchent_key;
		// This variable should be generated or populated from your system
		$name = $this->buyer_name;
		$phone = $this->phone;
		$email = $this->email;
		$order_id = $this->billcode;
		$amount = $this->amount;
		$description = $this->purpose;
		if ( is_null($this->fail_url) ) {
			$this->fail_url = $this->kineticpay_success_url;
		}
		$body = [
			'merchant_key' => $secretkey,
			'invoice' => $order_id,
			'amount' => $amount,
			'description' => $description,
			'bank' => $bankid,
			'callback_success' => $this->kineticpay_success_url,
			'callback_error' => $this->fail_url,
			'callback_status' => $this->kineticpay_success_url
		];		
		// API Endpoint URL
		$url = "https://manage.kineticpay.my/payment/create";
		
		$args = array(
			'body'        => $body,
			'headers'     => array('Content-Type:application/json'),
		);
		
		$result = wp_remote_post( $url, $args );
		if (is_wp_error($result)) {
			return array("error" =>  $result->get_error_message());
		}
		$response = json_decode($result["body"], true);
		return $response;
		
	}	

    public function success_action($order_id)
	{
		$secretkey = $this->merchent_key;
		// This variable should be generated or populated from your system
		$url = "https://manage.kineticpay.my/payment/status?merchant_key=". $secretkey . "&invoice=" . (string)$order_id;
			
		$result = wp_remote_get( $url );
		if (is_wp_error($result)) {
			return array("error" =>  $result->get_error_message());
		}
		$response = json_decode($result["body"], true);		
		return $response;
		
	}
	
	public function return_url($page_url, $form_id, $lead_id, $pay_id, $render_id) {
		
		$ids_query = "ids={$form_id}|{$lead_id}|{$pay_id}|{$render_id}";
        $ids_query .= '&hash=' . wp_hash($ids_query);

        $url = add_query_arg('forminator_kineticpay_return', base64_encode($ids_query), $page_url);

        $query = 'forminator_kineticpay_return=' . base64_encode($ids_query);

        return apply_filters('forminator_kineticpay_return_url', $url, $form_id, $lead_id, $query);
	}

}
