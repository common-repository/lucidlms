<?php

/**
 * Emails Controller
 *
 * @class          LU_Emails
 * @version        1.0.0
 * @package        LucidLMS/Classes/Emails
 * @category       Class
 * @author         New Normal
 */
class LU_Emails {

	/**
	 * @var string Email action (example: 'course_completed')
	 * @access private
	 */
	private $email_action;

	/**
	 * @var object User
	 * @access private
	 */
	private $user;

	/**
	 * @var string Email address
	 * @access private
	 */
	private $recipient;

	/**
	 * @var string Email subject
	 * @access private
	 */
	private $subject;

	/**
	 * @var string Email heading
	 * @access private
	 */
	private $heading;

	/**
	 * @var string Stores the emailer's address.
	 * @access private
	 */
	private $_from_address;

	/**
	 * @var string Stores the emailer's name.
	 * @access private
	 */
	private $_from_name;

	/**
	 * @var mixed Content type for sent emails
	 * @access private
	 */
	private $_content_type;

	/**
	 * @var string Template path
	 */
	private $template;

	/**
	 * @var string Course name
	 */
	private $course_name;

	/**
	 * @var string Find patterns
	 */
	private $find;

	/**
	 * @var string Replace patterns
	 */
	private $replace;

	/**
	 * @var LucidLMS The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Class Instance
	 *
	 * @return LU_Emails|LucidLMS
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'lucidlms' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'lucidlms' ), '2.1' );
	}

	/**
	 * Constructor for the email class hooks in all emails that can be sent.
	 * 
	 * * @access public
	 * 
	 * LU_Emails constructor.
	 */
	function __construct() {

		// Email Header, Footer hooks
		add_action( 'lucidlms_email_header', array( $this, 'email_header' ) );
		add_action( 'lucidlms_email_footer', array( $this, 'email_footer' ) );

		// Hooks for sending emails
		add_action( 'lucidlms_course_completed_notification', array( $this, 'lucidlms_course_completed' ), 10, 3 );

		// Let 3rd parties unhook the above via this hook
		do_action( 'lucidlms_email', $this );
	}

	/**
	 * format_string function.
	 *
	 * @access public
	 *
	 * @param mixed $string
	 *
	 * @return string
	 */
	function format_string( $string ) {
		return str_replace( $this->find, $this->replace, $string );
	}

	/**
	 * get_blogname function.
	 *
	 * @access public
	 * @return string
	 */
	function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * Get from name for email.
	 *
	 * @access public
	 * @return string
	 */
	function get_from_name() {
		if ( ! $this->_from_name ) {
			$this->_from_name = get_option( 'lucidlms_organization_name' );
		}

		return wp_specialchars_decode( $this->_from_name );
	}

	/**
	 * Get from email address.
	 *
	 * @access public
	 * @return string
	 */
	function get_from_address() {
		if ( ! $this->_from_address ) {
			$this->_from_address = get_option( 'lucidlms_emails_from_address', get_option( 'admin_email' ) );
		}

		return $this->_from_address;
	}

	/**
	 * Get the content type for the email.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_type() {
		return apply_filters( 'lucidlms_email_content_type', $this->_content_type );
	}

	/**
	 * get_attachments function.
	 *
	 * @access public
	 * @return array
	 */
	function get_attachments() {
		return apply_filters( 'lucidlms_email_attachments', array(), $this->user );
	}

	/**
	 * Get recipient
	 *
	 * @return mixed|void
	 */
	function get_recipient() {
		return apply_filters( 'lucidlms_email_recipient', $this->recipient, $this->user );
	}

	/**
	 * Get subject
	 *
	 * @return mixed|void
	 */
	function get_subject() {
		return apply_filters( 'lucidlms_email_subject', $this->format_string( $this->subject ), $this->email_action );
	}

	/**
	 * Get heading
	 *
	 * @return mixed|void
	 */
	function get_heading() {
		return apply_filters( 'lucidlms_email_heading', $this->format_string( $this->heading ), $this->email_action );
	}

	/**
	 * get_headers function.
	 *
	 * @access public
	 * @return string
	 */
	function get_headers() {
		return apply_filters( 'lucidlms_email_headers', "Content-Type: " . $this->get_content_type() . "\r\n", $this->user );
	}

	/**
	 * get_content function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content() {
		ob_start();
		lucid_get_template( $this->template, array(
			'email_heading' => $this->get_heading(),
			'course_name' => $this->course_name,
			'blogname'      => $this->get_blogname(),
			'user'          => $this->user,
			'sent_to_admin' => false
		) );

		return ob_get_clean();
	}

	/**
	 * Find all needed data for sending an email
	 *
	 * @access public
	 */
	function init($score_card) {

        if( !isset($GLOBALS['scorecard']) ){
            $GLOBALS['scorecard'] = $score_card;
        }

		$this->user          = get_user_by( 'id', get_current_user_id() );
		$this->recipient     = stripslashes( $this->user->user_email );
		$this->subject       = get_option( 'lucidlms_emails_subject_' . $this->email_action );
		$this->heading       = get_option( 'lucidlms_emails_heading_' . $this->email_action, get_option( 'lucidlms_emails_subject_' . $this->email_action ) );
		$this->_content_type = apply_filters( 'lucidlms_emails_set_content_type', 'text/html', $this->email_action );
		$this->template      = 'emails/' . str_replace( '_', '-', $this->email_action ) . '.php';
		$this->course_name = $score_card->get_course_title();

		// Find/replace
		$this->find    = apply_filters( 'lucidlms_emails_find_filter', array( '{site_name}', '{course_name}' ) );
		$this->replace = apply_filters( 'lucidlms_emails_replace_filter', array(
				$this->get_blogname(),
				$this->course_name
			) );
	}

	/**
	 * Get the email header.
	 *
	 * @access public
	 *
	 * @param mixed $email_heading heading for the email
	 *
	 * @return void
	 */
	function email_header( $email_heading ) {
		lucid_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
	}

	/**
	 * Get the email footer.
	 *
	 * @access public
	 * @return void
	 */
	function email_footer() {
		lucid_get_template( 'emails/email-footer.php' );
	}

	/**
	 * Wraps a message in the lucidlms mail template.
	 *
	 * @access public
	 *
	 * @param mixed $email_heading
	 * @param mixed $message
	 *
	 * @return string
	 */
	function wrap_message( $email_heading, $message, $plain_text = false ) {
		// Buffer
		ob_start();

		do_action( 'lucidlms_email_header', $email_heading );

		echo wpautop( wptexturize( $message ) );

		do_action( 'lucidlms_email_footer' );

		// Get contents
		$message = ob_get_clean();

		return $message;
	}

	/**
	 * Send the email.
	 *
	 * @access public
	 *
	 * @param mixed  $to
	 * @param mixed  $subject
	 * @param mixed  $message
	 * @param string $headers     (default: "Content-Type: text/html\r\n")
	 * @param string $attachments (default: "")
	 *
	 * @return void
	 */
	function send( $to, $subject, $message, $headers = "Content-Type: text/html\r\n", $attachments = "" ) {

		// Filters for the email
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		// Send
		wp_mail( $to, $subject, $message, $headers, $attachments );

		// Unhook filters
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Send an email once user completed the course
	 */
	function lucidlms_course_completed($score_card) {

		$this->email_action = 'course_completed';

		$this->init($score_card);

		if ( ! $this->user ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

}
