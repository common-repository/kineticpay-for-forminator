<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
/**
 * Class Forminator_Kineticpay
 *
 * @since 1.0.0
 */
class Forminator_Kineticpay extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'kineticpay';

	/**
	 * @var string
	 */
	public $type = 'kineticpay';

	/**
	 * @var int
	 */
	public $position = 25;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';


	/**
	 * @var string
	 */
	public $icon = 'sui-icon-kineticpay';

	/**
	 * @var bool
	 */
	public $is_connected = false;

	/**
	 * @var string
	 */
	public $mode = 'live';

	/**
	 * @var array
	 */
	public $payment_plan = array();

	/**
	 * Forminator_Stripe constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Kineticpay', 'forminator' );

	}

	/**
	 * Field defaults
	 *
	 * @return array
	 */
	public function defaults() {
		return array(
			'field_label' => __( 'Kineticpay', 'forminator' ),
		);
	}

	/**
	 * Field front-end markup
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
		$html    = '';
		$label   = esc_html( self::get_property( 'field_label', $field ) );
		$id      = self::get_property( 'element_id', $field );
		$form_id = false;

		$html .= '<div class="forminator-field forminator-merge-tags">';

		if ( $label ) {

			$html .= sprintf(
				'<label class="forminator-label">%s</label>',
				$label
			);
		}

			// Check if form_id exist.
		if ( isset( $settings['form_id'] ) ) {
			$form_id = $settings['form_id'];
		}

			$html .= forminator_replace_variables(
				wp_kses_post( self::get_property( 'variations', $field ) ),
				$form_id
			);

		$html .= '</div>';

		return $html;
	}
	
}