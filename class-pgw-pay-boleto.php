<?php
/*
Plugin Name: PGW Payments - Boleto
Plugin URI: http://www.pgwpay.com.br
Description: Extends WooCommerce with an PGW Payments Boleto gateway.
Version: 1.1.04
WC requires at least: 3.5
WC tested up to: 4.1
Author: PGW Pay
Author URI: http://www.pgwpay.com.br
	Copyright: © 2019 PGW Payments.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined('ABSPATH') || exit;

error_reporting(E_ERROR);

define('PGW_BOLETO_DIR', WP_PLUGIN_DIR . '/pgw-pay-boleto');
define('PGW_BOLETO_URL', WP_PLUGIN_URL . '/pgw-pay-boleto');

// define('PGW_BOLETO_ENDPOINT', 'http://localhost:8443');
define('PGW_BOLETO_ENDPOINT', 'https://api.pgwpay.com.br');

$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
define('PGW_BOLETO_VERSION', $plugin_data['Version']);

add_action('plugins_loaded', 'woocommerce_gateway_pgw_pay_boleto_init', 0);

function woocommerce_gateway_pgw_pay_boleto_init()
{
    if (!class_exists('WC_Payment_Gateway')) return;
    /**
     * Localisation
     */
    load_plugin_textdomain('wc-gateway-pg', false, dirname(plugin_basename(__FILE__)) . '/languages');

    require_once(PGW_BOLETO_DIR . '/includes/class-pgw-pay-boleto.php');    

    /**
     * Add the Gateway to WooCommerce
     **/
    function woocommerce_add_gateway_pgw_pay_boleto_gateway($methods)
    {
        $methods[] = 'WC_Gateway_Pgw_Pay_Boleto';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_pgw_pay_boleto_gateway');

    require_once(PGW_BOLETO_DIR . '/includes/hooks-pgw-pay-boleto.php');    
}

require_once(PGW_BOLETO_DIR . '/includes/rest-pgw-pay-boleto.php');

