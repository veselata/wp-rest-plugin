<?php

if ( ! class_exists( 'Base_Settings' ) ) {

/**
 * Handles plugin settings
 */
class Base_Settings extends Base_Module {
    
protected $settings;
protected static $default_settings;
protected static $readable_properties  = array( 'settings' );
protected static $writeable_properties = array( 'settings' );

const REQUIRED_CAPABILITY = 'administrator';

/**
 * Constructor
 *
 */
protected function __construct() {
    $this->register_hook_callbacks();
}

/**
 * Setter for protected variables
 *
 * Updates settings and merged with default
 *
 * @param string $variable
 * @param array  $value 
 **/
public function __set( $variable, $value ) {
    if ( $variable != 'settings' ) {
        return;
    }
    $this->settings = self::validate_settings( $value );
    update_option( 'base_settings', $this->settings );
}

/**
 * Register callbacks for actions and filters
 *
 */
public function register_hook_callbacks() {
   add_action( 'admin_menu', __CLASS__ . '::register_settings_pages' );
   add_action( 'init',                     array( $this, 'init' ) );
   add_action( 'admin_init',               array( $this, 'register_settings' ) );
   
   //add_options_page('$page_title', '$menu_title', '$capability', '$menu_slug');
    
    add_filter(
        'plugin_action_links_' . plugin_basename( dirname( __DIR__ ) ) . '/bootstrap.php',
	__CLASS__ . '::add_plugin_action_links'
    );
    
   // add_action('admin_menu', 'awesome_page_create');
}
/*
function awesome_page_create() {
    $page_title = 'My Awesome Admin Page';
    $menu_title = 'Awesome Admin Page';
    $capability = 'edit_posts';
    $menu_slug = 'awesome_page';
    $function = 'my_awesome_page_display';
    $icon_url = '';
    $position = 24;

    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}
*/

/**
 * Prepares site to use the plugin during activation
 *
 * @param bool $network_wide
 */
public function activate( $network_wide ) {}

/**
 * Deactivation process
 *
 */
public function deactivate() {}

/**
 * Initializes variables
 *
 */
public function init() {
    self::$default_settings = self::get_default_settings();
    $this->settings         = self::get_settings();
}

/**
 * Establishes initial values for all settings
 *
 * @return array
 */
protected static function get_default_settings() {
    $placeholder = array(
        'endpoint' => ''
    );

    return array(
        'version' => '1.0',
        'placeholder' => $placeholder
    );
}

/**
 * Retrieves all of the settings from the database
 *
 * @return array
 */
protected static function get_settings() {
    $settings = shortcode_atts(
        self::$default_settings,
	get_option( 'base_settings', array() )
    );
    return $settings;
}

/**
 * Adds links to the plugin's action link section on the Plugins page
 *
 * @param array $links The links currently mapped to the plugin
 * @return array
 */
public static function add_plugin_action_links( $links ) {
    array_unshift( $links, '<a href="options-general.php?page=' . 'base_sample">Settings</a>' );
    return $links;
}

/**
 * Adds pages to the Admin Panel menu
 *
 */
public static function register_settings_pages() {
    add_submenu_page(
        'options-general.php',
	BASE_NAME . ' Settings',
	BASE_NAME,
	'base_sample',
	__CLASS__ . '::markup_settings_page'
    );
}

/**
 * Creates the markup for the Settings page
 *
 */
public static function markup_settings_page() { 
    die(1);
    if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
        echo self::render_template( 'base-settings/page-settings.php' );
    } else {
        wp_die( 'Access denied.' );
    }
}

/**
 * Registers settings sections, fields and settings
 *
 */
public function register_settings() {
    /*
     * Basic Section
     */
    add_settings_section(
        'base_section-basic',
        'Basic Settings',
        __CLASS__ . '::markup_section_headers',
        'base_settings'
    );

    add_settings_field(
        'base_placeholder',
        'Placeholder Field',
        array( $this, 'markup_fields' ),
        'base_settings',
        'base_section-basic',
        array( 'label_for' => 'base_placeholder' )
    );

    // The settings container
    register_setting(
        'base_settings',
        'base_settings',
        array( $this, 'validate_settings' )
    ); 
}

/**
 * Display introduction text to the Settings page
 *
 *
 * @param array $section
 */
public static function markup_section_headers( $section ) {
    echo self::render_template( 'base-settings/page-settings-headers.php', array( 'section' => $section ), 'always' );
}

/**
 * Validates setting values
 *
 *
 * @param array $new_settings
 * @return array
 */
public function validate_settings( $new_settings ) {
    $new_settings = shortcode_atts( $this->settings, $new_settings );
    if ( ! is_string( $new_settings['version'] ) ) {
        $new_settings['version'] = Base_Plugin::VERSION;
    }
    return $new_settings;
}

    }
}
