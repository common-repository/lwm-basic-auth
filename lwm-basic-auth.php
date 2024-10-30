<?php
/**
 * Plugin Name: Lightweb Media Basic Authentication
 * Plugin URI:        
 * Description: Basic Authentication for protected your development WordPress site like .htpasswd
 * Version:           1.0.0
 * Requires at least: 4.7
 * Requires PHP:      7.4
 * Tested up to:      8.0.1
 * Author:            Sebastian Weiß
 * Author URI:        https://lightweb-media.de
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lwm-basic-auth 
 * Domain Path:       /languages
 */

// Define constants.
define('LWMBA_PATH', plugin_dir_path(__FILE__));
define('LWMBA_BASENAME', plugin_basename(__FILE__));
define('LWMBA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LWMBA_VERSION', '1.0.0');

/**
 * Class WPBA_Basic_Authentication
 */
class LWM_Basic_Auth
{
    /**
     * Array of custom settings/options
     **/
    private $options;


    /**
     * Constructor
     */
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'set_default_options']);

        $this->options = get_option('lwmba_auth_settings');

        if (is_admin()) {
            require LWMBA_PATH.'/inc/class-lwmba-setting.php';
            new lwmba_Setting();
        } else {

            $enable_login = $this->options['enable_login'] ?? 0;

            if ($enable_login && $this->is_login_page()) {
                add_action('init', [$this, 'basic_auth_handler'], 1);
            } elseif (!$this->is_login_page()) {
                add_action('init', [$this, 'basic_auth_handler'], 1);
            }

        }
    }

    /**
     * Basic auth handler
     */
    public function basic_auth_handler()
    {
 
      
        $enable = $this->options['enable'] ?? 0;
        $username = $this->options['username'] ?? '';
        $password = $this->options['password'] ?? '';
        $enable_mu_plugin = $this->options[ 'create_mu_plugin' ];
        $enable_localhost_bypass = $this->options['enable_localhost_bypass'] ?? '';
        $access = False;
     
        if($enable_mu_plugin == 0 && $enable == '1'){
            if ( defined( 'WP_CLI' ) && WP_CLI && $enable_localhost_bypass === "1" ) {
                if (!$_SERVER['REQUEST_URI'] || $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
                    $access = True;
             
                }     
            } 
          if ($enable && $username)
            {
                if( $access === False){
                    $AUTH_USER = $username;
                    $AUTH_PASS = $password;
                    header('Cache-Control: no-cache, must-revalidate, max-age=0');
                    $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
                    $is_not_authenticated = (
                        (!$has_supplied_credentials ||
                        $_SERVER['PHP_AUTH_USER'] != $AUTH_USER ||
                        $_SERVER['PHP_AUTH_PW'] != $AUTH_PASS) 
                    );
          
                    if ($is_not_authenticated) {
                        header('HTTP/1.1 401 Authorization Required');
                        header('WWW-Authenticate: Basic realm="Access denied"');
                        exit;
                    }
                }
            }
        }
  
    }
    /**
     * Check login page
     *
     * @return bool
     */
    private function is_login_page()
    {

        if (isset($GLOBALS['pagenow'])) {
            return in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php']);
        }

        return false;
    }


    /**
     * Set default options
     */
    public function set_default_options()
    {
        $this->options = [
            'enable'       => 0,
            'username'     => '',
            'password'     => '',
            'enable_login' => 0,
            'enable_localhost_bypass' => 0,
            'create_mu_plugin' => 0,
        ];

        update_option('lwmba_auth_settings', $this->options);
    }

    /**
     * Set default options
     */
 

}

new LWM_Basic_Auth();

