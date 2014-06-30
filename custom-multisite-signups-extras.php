<?php

class Custom_Mulitsite_Signups_Extras extends Custom_Mulitsite_Signups {

	public function __construct() {
		add_filter( 'cms_add_extra_signup_fields', array( $this, 'extra_fields' ) );
		add_filter( 'cms_extra_fields_css_selectors', array( $this, 'extra_fields_selectors' ) );
		add_filter( 'cms_extra_fields_css', array( $this, 'extra_fields_css' ) );
		add_filter( 'cms_wpmu_validate_user_signup', array( $this, 'validate_user_signup' ) );
		add_action( 'cms_extra_signup_meta', array( $this, 'cms_extra_signup_meta' ) );
		add_filter( 'cms_show_extra_profile_fields', array( $this, 'show_extra_profile_fields' ) );
		add_action( 'cms_save_extra_profile_fields', array( $this, 'save_extra_profile_fields' ) );
	}

	public function extra_fields( $html ) {
		$extra_field1 = isset( $_REQUEST['extra_field1'] ) ? (string) $_REQUEST['extra_field1'] : '';
		$extra_fields[] = '<label>Extra Field</label>';
		$extra_fields[] = '<input id="extra_field1" name="extra_field1" type="text" value="' . $extra_field1 . '" />';

		$html .=  implode( "\n", $extra_fields );
		echo $html;
}

	public function extra_fields_selectors( $selectors ) {
		$selectors .= ', .mu_register #extra_field1';
		return $selectors;
	}

	public function extra_fields_css( $css ) {
		$css .= ' /* CSS comment */ ';
		return $css;
	}

	public function validate_user_signup( $result ) {
		if ( empty( $_POST['extra_field1'] ) ) {
			$result['errors']->add( 'last_name', __( 'You must include an extra field.' ) );
			echo '<p class="error">', $result['errors']->get_error_message('extra_field1'), '</p>';
		}

		return $result;
	}

	public function cms_extra_signup_meta() {
		return array(
				'extra_field1' => sanitize_text_field( $_POST['extra_field1'] ),
				);
	}

	public function show_extra_profile_fields( $user ) {

		$html[] = '<th><label for="extra_field1" id="extra_field1">' . __( 'Extra Field 1' ) . '</label></th>';
		$html[] = '<td>';
		$html[] = '<input type="text" name="extra_field1" id="extra_field1" value="';
		$html[] = esc_attr( get_the_author_meta( 'extra_field1', $user->ID ) );
		$html[] = '" class="regular-text" /><br />';
		$html[] = '<span class="description">Please enter your Extra Field 1 data.</span>';
		$html[] = '';
		$html = implode( " ", $html );

		return $html;

	}

	public function save_extra_profile_fields() {
		return array( 'extra_field1' );
	}

}
