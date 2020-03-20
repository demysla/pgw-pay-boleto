<?php
defined('ABSPATH') || exit;

function action_payment_link($order_id)
{
    $order = wc_get_order($order_id);
    echo '
            <h3>
                <a 
                    href="' . $order->get_customer_note() . '" 
                    target="_blank"
                    style="margin-bottom: 25px; font-weight: bold; color: #0000FF; text-decoration: underline;">
                        Imprima aqui o seu Boleto
                </a>
            </h3>';
    $order->set_customer_note('');
    $order->save();
}
add_action('woocommerce_thankyou_pgw-pay-boleto', 'action_payment_link', 4);
