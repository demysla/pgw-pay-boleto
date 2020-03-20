<?php
defined('ABSPATH') || exit;

function update_order_status_boleto($server)
{
    $server->register_route('pgwpay', '/pgwpay/boleto/v1/order/status', array(
        'methods'  => 'PUT',
        'callback' => function ($request) {
            try {
                error_log('Iniciando função de atualização de status ...');
                if (!isset($request['id'])) {
                    error_log('ID do pedido indefinido');
                    return 'ID do pedido indefinido';
                }
                if (!isset($request['status'])) {
                    error_log('Status do pedido indefinido');
                    return 'Status do pedido indefinido';
                }
                error_log('Consultando Order #' . $request['id']);
                $info = ' [WooCommerce]';
                $order = wc_get_order($request['id']);
                if (!$order) {
                    $order_id = wc_seq_order_number_pro()->find_order_by_order_number($request['id']);
                    $order = wc_get_order($order_id);
                    if (!$order) {
                        error_log('Pedido não encontrado para o ID: ' . $request['id']);
                        return 'Pedido não encontrado para o ID: ' . $request['id'];
                    }
                    $info = ' [WooCommerce Sequence Order Number]';
                }
                $order->set_status($request['status']);
                $order->save();
                error_log('Pedido #' . $request['id'] . ' atualizado para ' . $order->get_status() . $info);
                return 'Pedido #' . $request['id'] . ' atualizado para ' . $order->get_status() . $info;
            } catch (Exception $e) {
                error_log('PGW Payments: ' . $e->getTraceAsString());
                return '$e->getTraceAsString(): ' . $e->getTraceAsString();
            }
        },
    ));
}
add_action('rest_api_init', 'update_order_status_boleto', 0);


function get_debug_status_boleto($server)
{
    $server->register_route('pgwpay', '/pgwpay/boleto/v1/debug/status', array(
        'methods'  => 'GET',
        'callback' => function () {
            try {
                return wc_get_order_statuses();
            } catch (Exception $e) {
                error_log('PGW Payments: ' . $e->getTraceAsString());
                return '$e->getTraceAsString(): ' . $e->getTraceAsString();
            }
        },
    ));
}
add_action('rest_api_init', 'get_debug_status_boleto', 0);

function get_debug_types_boleto($server)
{
    $server->register_route('pgwpay', '/pgwpay/boleto/v1/debug/types', array(
        'methods'  => 'GET',
        'callback' => function () {
            try {

                return wc_get_order_types();
            } catch (Exception $e) {
                error_log('PGW Payments: ' . $e->getTraceAsString());
                return '$e->getTraceAsString(): ' . $e->getTraceAsString();
            }
        },
    ));
}
add_action('rest_api_init', 'get_debug_types_boleto', 0);


function get_debug_update_boleto($server)
{
    $server->register_route('pgwpay', '/pgwpay/boleto/v1/debug/update', array(
        'methods'  => 'PUT',
        'callback' => function ($request) {
            try {
                error_log('Iniciando função de atualização de status ...');
                if (!isset($request['id'])) {
                    error_log('ID do pedido indefinido');
                    return 'ID do pedido indefinido';
                }
                if (!isset($request['status'])) {
                    error_log('Status do pedido indefinido');
                    return 'Status do pedido indefinido';
                }
                error_log('Consultando Order #' . $request['id']);
                $post = get_post($request['id']);
                $postStatus = $post->post_status;
                $postType = $post->post_type;
                error_log('Salvei o status e o type: ' . $postStatus . '/' . $postType);

                $my_post = array(
                    'ID'           => $request['id'],
                    'post_type'     => 'shop_order'
                );
                wp_update_post($my_post);
                error_log('Atualizei o post_type para shop_order');

                $order = wc_get_order($request['id']);
                if ($order) {
                    error_log('Peguei a ordem do Woo');
                    $order->set_status($request['status']);
                    $order->save();
                    error_log('Atualizei o status da ordem do Woo');

                    $my_post = array(
                        'ID'           => $request['id'],
                        'post_type'     => $postType,
                        'post_status'     => $postStatus
                    );
                    error_log('Devolvi o type e o status original.');
                    return wp_update_post($my_post) . ' - atualizado para ' . $request['status'] . ' via wp_update_post()';
                } else {
                    error_log('Pedido não encontrado para o ID: ' . $request['id']);
                    return 'Pedido não encontrado para o ID: ' . $request['id'];
                }
            } catch (Exception $e) {
                error_log('PGW Payments: ' . $e->getTraceAsString());
                return '$e->getTraceAsString(): ' . $e->getTraceAsString();
            }
        },
    ));
}
add_action('rest_api_init', 'get_debug_update_boleto', 0);


function get_debug_number_boleto($server)
{
    $server->register_route('pgwpay', '/pgwpay/boleto/v1/debug/number', array(
        'methods'  => 'PUT',
        'callback' => function ($request) {
            try {
                $order = wc_get_order($request['id']);
                return var_dump($order);
            } catch (Exception $e) {
                error_log('PGW Payments: ' . $e->getTraceAsString());
                return '$e->getTraceAsString(): ' . $e->getTraceAsString();
            }
        },
    ));
}
add_action('rest_api_init', 'get_debug_number_boleto', 0);
