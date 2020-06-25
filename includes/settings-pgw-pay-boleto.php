<?php
defined('ABSPATH') || exit;

return array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'woocommerce'),
        'type' => 'checkbox',
        'label' => __('Ativar PGW Payments - Boleto', 'woocommerce'),
        'default' => 'no',
    ),
    'updateStatus' => array(
        'title' => __('Enable/Disable', 'woocommerce'),
        'type' => 'checkbox',
        'label' => __('Ativar Atualização de Status Automática', 'woocommerce'),
        'default' => 'yes',
    ),
    'shopId' => array(
        'title' => __('Identificador da Loja', 'woocommerce'),
        'type' => 'text',
        'description' => __('Identifica o lojista na PGW Payments.', 'woocommerce'),
        'default' => __('', 'woocommerce'),
        'desc_tip' => true,
    ),
    'enabledDisclaimer' => array(
        'title' => __('Enable/Disable', 'woocommerce'),
        'type' => 'checkbox',
        'label' => __('Ativar Termos e Condições', 'woocommerce'),
        'default' => 'no',
    ),
    'msgDisclaimer' => array(
        'title' => __('Termos e Condições', 'woocommerce'),
        'type' => 'textarea',
        'label' => __('Termos e Condições', 'woocommerce'),
        'msgDisclaimer' => array(
            'title' => __('Termos e Condições', 'woocommerce'),
            'type' => 'textarea',
            'label' => __('Termos e Condições', 'woocommerce'),
            'default' => '',
        )
    )
);
