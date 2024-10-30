<?php

add_action( 'init', 'lwm_basic_auth_func', 1 );

function lwm_basic_auth_func() {
    $options = get_option( 'lwmba_auth_settings' );

    $enable = $options[ 'enable' ];
    $username = $options[ 'username' ];
    $password = $options[ 'password' ];
    $enable_localhost_bypass = $options[ 'enable_localhost_bypass' ] ?? '';
    $access = False;
    if ( !is_admin() ) {

        if ( isset( $options[ 'username' ] ) && isset( $options[ 'password' ] ) ) {

            if ( defined( 'WP_CLI' ) && WP_CLI && $enable_localhost_bypass == '1' ) {
                if ( !$_SERVER[ 'REQUEST_URI' ] || $_SERVER[ 'REMOTE_ADDR' ] === '127.0.0.1' ) {
                    $access = True;

                }

            }

            if ( $enable && $username ) {
                if ( $access === False ) {
                    $AUTH_USER = $username;
                    $AUTH_PASS = $password;
                    header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
                    $has_supplied_credentials = !( empty( $_SERVER[ 'PHP_AUTH_USER' ] ) && empty( $_SERVER[ 'PHP_AUTH_PW' ] ) );
                    $is_not_authenticated = (
                        ( !$has_supplied_credentials ||
                        $_SERVER[ 'PHP_AUTH_USER' ] != $AUTH_USER ||
                        $_SERVER[ 'PHP_AUTH_PW' ] != $AUTH_PASS )
                    );

                    if ( $is_not_authenticated ) {
                        header( 'HTTP/1.1 401 Authorization Required' );
                        header( 'WWW-Authenticate: Basic realm="Access denied"' );
                        exit;
                    }

                }
            }
        }
    }
}