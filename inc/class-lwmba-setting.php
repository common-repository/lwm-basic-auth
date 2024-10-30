<?php

/**
* Class WPBA_Setting
*/

class LWMBA_Setting {
    /**
    * Array of custom settings/options
    **/
    private $options;

    /**
    * Constructor
    */

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'page_init' ] );
    }

    /**
    * Add settings page
    * The page will appear in Admin menu
    */

    public function add_settings_page() {
        add_menu_page(
            __( 'Basic Authentication Settings', 'lwm-basic-auth' ), // Page title
            __( 'Authentication', 'lwm-basic-auth' ), // Title
            'edit_pages', // Capability
            'LWMBA-auth-settings-page', // Url slug
            [ $this, 'create_admin_page' ], // Callback
            'dashicons-privacy'
        );
    }

    /**
    * Options page callback
    */

    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'LWMBA_auth_settings' );

        ?>
        <div class = 'wrap'>
        <form method = 'post' action = 'options.php'>
        <?php
        // This prints out all hidden setting fields
        settings_fields( 'LWMBA_auth_settings_group' );
        do_settings_sections( 'LWMBA-auth-settings-page' );
        submit_button();
        ?>
        </form>
        </div>
        <?php
    }

    /**
    * Register and add settings
    */

    public function page_init() {
        register_setting(
            'LWMBA_auth_settings_group', // Option group
            'LWMBA_auth_settings', // Option name
            [ $this, 'sanitize' ] // Sanitize
        );

        add_settings_section(
            'LWMBA_auth_settings_section', // ID
            __( 'Basic HTTP Authentication', 'lwm-basic-auth' ), // Title
            [ $this, 'LWMBA_auth_settings_section' ], // Callback
            'LWMBA-auth-settings-page' // Page
        );

        add_settings_field(
            'enable', // ID
            __( 'Enable', 'lwm-basic-auth' ), // Title
            [ $this, 'enable_field' ], // Callback
            'LWMBA-auth-settings-page', // Page
            'LWMBA_auth_settings_section'
        );

        add_settings_field(
            'username', // ID
            __( 'Username', 'lwm-basic-auth' ), // Title
            [ $this, 'username_field' ], // Callback
            'LWMBA-auth-settings-page', // Page
            'LWMBA_auth_settings_section'
        );

        add_settings_field(
            'password',
            __( 'Password', 'lwm-basic-auth' ),
            [ $this, 'password_field' ],
            'LWMBA-auth-settings-page',
            'LWMBA_auth_settings_section'
        );

        add_settings_field(
            'enable_login', // ID
            __( 'Enable for Login page', 'lwm-basic-auth' ), // Title
            [ $this, 'enable_login_field' ], // Callback
            'LWMBA-auth-settings-page', // Page
            'LWMBA_auth_settings_section'
        );
        add_settings_field(
            'enable_localhost_bypass', // ID
            __( 'Enable Localhost ByPass', 'lwm-basic-auth' ), // Title
            [ $this, 'enable_localhost_bypass_field' ], // Callback
            'LWMBA-auth-settings-page', // Page
            'LWMBA_auth_settings_section'
        );
        add_settings_field(
            'create_mu_plugin_field', // ID
            __( 'Create MU-Plugin', 'lwm-basic-auth' ), // Title
            [ $this, 'create_mu_plugin_field' ], // Callback
            'LWMBA-auth-settings-page', // Page
            'LWMBA_auth_settings_section'
        );

    }

    /**
    * Sanitize POST data from custom settings form
    *
    * @param array $input Contains custom settings which are passed when saving the form
    * @return array
    */

    public function sanitize( array $input ) {
        $sanitized_input = [
            'enable'          => 0,
            'username'        => '',
            'password'        => '',
            'enable_login'    => 0,
            'enable_localhost_bypass'    => 0,
            'create_mu_plugin'    => 0,
        ];

        $sanitized_input = array_merge( $sanitized_input, $input );

        if ( $sanitized_input[ 'create_mu_plugin' ] === '1' && $sanitized_input[ 'enable' ] === '1' && isset( $sanitized_input[ 'username' ] ) && isset( $sanitized_input[ 'password' ] ) ) {
            $this->create_mu_plugin();
        } else {
            $this->remove_mu_plugin();
        }

        return $sanitized_input;
    }
    /**
    * add mu plugin if checked
    */

    public function create_mu_plugin() {

        if ( !file_exists( WPMU_PLUGIN_DIR ) && !is_dir( WPMU_PLUGIN_DIR ) ) {
            mkdir( WPMU_PLUGIN_DIR );

        }
        if ( !file_exists( WPMU_PLUGIN_DIR .'/lwm-basic-auth.php' ) ) {

            copy( LWMBA_PATH.'/mu-plugin/lwm-mu-basic-auth.php', WPMU_PLUGIN_DIR.'/lwm-mu-basic-auth.php' );

        }

    }
    /**
    * remove mu plugin file if no checked
    */

    public function remove_mu_plugin() {

        if ( file_exists( WPMU_PLUGIN_DIR .'/lwm-mu-basic-auth.php' ) ) {
            unlink( WPMU_PLUGIN_DIR .'/lwm-mu-basic-auth.php' );

        }

    }

    /**
    * Custom settings section text
    */

    public function LWMBA_auth_settings_section() {

    }

    public function enable_field() {
        echo '<input type="checkbox" id="enable" name="LWMBA_auth_settings[enable]" value="1" ' . checked( $this->options[ 'enable' ], 1, false ) . ' />';
        echo ' ' . __( 'Enable authentication for Front-End', 'lwm-basic-auth' );
    }

    public function username_field() {
        printf(
            '<input type="text" id="username" name="LWMBA_auth_settings[username]" value="%s" />',
            isset( $this->options[ 'username' ] ) ? esc_attr( $this->options[ 'username' ] ) : ''
        );
    }

    public function password_field() {
        printf(
            '<input type="password" id="password" name="LWMBA_auth_settings[password]" value="%s" />',
            isset( $this->options[ 'password' ] ) ? esc_attr( $this->options[ 'password' ] ) : ''
        );
    }

    public function enable_login_field() {
        echo '<input type="checkbox" id="enable_login" name="LWMBA_auth_settings[enable_login]" value="1" ' . checked( $this->options[ 'enable_login' ], 1, false ) . ' />';
        printf( '<p class="description" id="enable_login-description">' . __( '<strong>Warning</strong>: If enable basic authentication for login page and forgot password, please see <a href="%s" target="_blank">FAQs in plugin page</a>', 'lwm-basic-auth' ) . '</p>', 'https://wordpress.org/plugins/lwm-basic-auth/#faq' );
    }

    public function enable_localhost_bypass_field() {
        echo '<input type="checkbox" id="enable_localhost_bypass" name="LWMBA_auth_settings[enable_localhost_bypass]" value="1" ' . checked( $this->options[ 'enable_localhost_bypass' ], 1, false ) . ' />';
        printf( '<p class="description" id="enable_localhost_bypass-description">' . __( '<strong>Information</strong>: If Bypass is enabled you can use wp-cli', 'lwm-basic-auth').'</p>');
    }

    public function create_mu_plugin_field() {
        echo '<input type="checkbox" id="create_mu_plugin" name="LWMBA_auth_settings[create_mu_plugin]" value="1" ' . checked( $this->options[ 'create_mu_plugin' ], 1, false ) . ' />';
        printf( '<p class="description" id="create_mu_plugin-description">' . __( '<strong>Information</strong>: If mu_plugin is enable and lwm-basic auth is disabled, delete file under wp-content/mu-plugins', 'lwm-basic-auth').'</p>' );
    }
}