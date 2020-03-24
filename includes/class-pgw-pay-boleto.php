<?php
defined('ABSPATH') || exit;

/**
 * Gateway class
 */
class WC_Gateway_Pgw_Pay_Boleto extends WC_Payment_Gateway
{
    private $pedido;

    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {
        $this->id = 'pgw-pay-boleto';

        $this->has_fields = true;
        $this->order_button_text = __('Gerar o Boleto', 'woocommerce');
        $this->method_title = __('PGW Payments - Boleto', 'woocommerce');
        $this->method_description = __('Pagamento no Brasil com Boleto Bancário pela PGW Payments.', 'woocommerce');

        $this->supports = array(
            'products',
        );

        $this->init_form_fields();
        $this->init_settings();

        // Get settings
        $this->enabled = $this->settings['enabled'];
        $this->updateStatus = $this->settings['updateStatus'];
        $this->shopId = $this->settings['shopId'];
        $this->enabledDisclaimer = $this->settings['enabledDisclaimer'];
        $this->msgDisclaimer = $this->settings['msgDisclaimer'];

        $this->title = 'Boleto Bancário';
        $this->description = 'Pagamento no Brasil com Boleto Bancário.';
        $this->instructions = 'Instruções de pagamento';

        // Pedido
        $this->pedido->origem = 'WC';
        $this->pedido->qtdParcela = 1;
        $this->pedido->formaPagamento = 'BOLETO';        
        $this->pedido->licencaId = trim($this->shopId);
        $this->pedido->origemVersao = PGW_BOLETO_VERSION;
        $this->pedido->environment = 'production';
        $this->pedido->updateStatus = $this->updateStatus === 'yes' ? 'true' : 'false';
        $this->pedido->updateEndpoint = get_rest_url() . 'pgwpay/boleto/v1/order/status';

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields()
    {
        $this->form_fields = include(PGW_BOLETO_DIR . '/includes/settings-pgw-pay-boleto.php');
    }

    public function payment_fields()
    {
        echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
        do_action('woocommerce_credit_card_form_start', $this->id);

        $licencaId = trim($this->shopId);
        $totalWC = doubleval(WC()->cart->get_total('total'));
        $ch = curl_init(PGW_BOLETO_ENDPOINT . "/woo/boletos/config?licencaId={$licencaId}&environment=production&totalWC={$totalWC}");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=UTF-8', 'Accept: application/json;charset=UTF-8'));
        $configBoletoJSON = curl_exec($ch);
        $configBoleto = json_decode($configBoletoJSON);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erro = curl_error($ch);
        if (!empty($erro)) {
            error_log('PGW Payments: Falha de autenticação. ' . $erro);
        }
        curl_close($ch);
        if ($httpCode == 200 && $configBoleto->autorizado) {
            $disclaimers = explode("\n", $this->msgDisclaimer);
            $disclaimerHtml = '';
            foreach ($disclaimers as $line) {
                $disclaimerHtml .= '<p>'  . $line . '</p>';
            }
            include_once(PGW_BOLETO_DIR . '/assets/views/html-payment-fields-boleto.php');
        } else {
            echo '<p style="color: red;">Identificador do plugin não encontrado!</p>';
            error_log('PGW Payments: licença de uso inválida!');
        }

        do_action('woocommerce_credit_card_form_end', $this->id);
        echo '<div class="clear"></div></fieldset>';
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        $disclaimerEnabled = false;
        $disclaimerAccepted = false;
        if ($this->enabledDisclaimer === 'yes') {
            $disclaimerEnabled = true;
            if (!isset($_POST['pgw--disclaimerAcceptedBoleto'])) {
                wc_add_notice('Leia e confirme os termos e condições do meio de pagamento.', 'error');
                return;
            }
            if ($_POST['pgw--disclaimerAcceptedBoleto'] !== 'on') {
                wc_add_notice('Leia e confirme os termos e condições do meio de pagamento.', 'error');
                return;
            }
            $disclaimerAccepted = true;
        }
        $this->pedido->disclaimerEnabled = $disclaimerEnabled;
        $this->pedido->disclaimerAccepted = $disclaimerAccepted;

        if (!isset($_POST['pgw--tipoPessoaBoleto'])) {
            wc_add_notice('Selecione o Tipo de Pessoa.', 'error');
            return;
        }
        if ($_POST['pgw--tipoPessoaBoleto'] === 'PF') {
            if (!isset($_POST['pgw--cpfBoleto'])) {
                wc_add_notice('Digite o CPF.', 'error');
                return;
            }
            $this->pedido->compradorTipoDoc = 'CPF';
            $this->pedido->compradorCpfCnpj = preg_replace('/[^0-9]/', '', $_POST['pgw--cpfBoleto']);  
            if (strlen($this->pedido->compradorCpfCnpj) < 11) {
                wc_add_notice('Digite um CPF válido.', 'error');
                return;
            }
        } else {
            if (!isset($_POST['pgw--cnpjBoleto'])) {
                wc_add_notice('Digite o CNPJ.', 'error');
                return;
            }
            $this->pedido->compradorTipoDoc = 'CNPJ';
            $this->pedido->compradorCpfCnpj = preg_replace('/[^0-9]/', '', $_POST['pgw--cnpjBoleto']);
            if (strlen($this->pedido->compradorCpfCnpj) < 14) {
                wc_add_notice('Digite um CNPJ válido.', 'error');
                return;
            }
        }
        
        if (!isset($_POST['pgw--boletoConfig'])) {
            wc_add_notice('Erro ao totalizar o pedido.', 'error');
            return;
        }
        $configBoleto = json_decode(stripslashes($_POST['pgw--boletoConfig']));
        
        $this->pedido->moedaLoja = $configBoleto->moeda;
        $this->pedido->cambio = $configBoleto->cambio;
        $this->pedido->totalPedidoBase = $configBoleto->totalPedidoBase;
        $this->pedido->totalPedidoBRL = $configBoleto->totalPedidoBRL;
        $this->pedido->totalGeralCompradorBase = $configBoleto->totalGeralCompradorBase;
        $this->pedido->totalGeralCompradorBRL = $configBoleto->totalGeralCompradorBRL;
        $this->pedido->totalLiquidoVendedorBase = $configBoleto->totalLiquidoVendedorBase;
        $this->pedido->totalLiquidoVendedorBRL = $configBoleto->totalLiquidoVendedorBRL;
        $this->pedido->totalEncargosCompradorBase = $configBoleto->totalEncargosCompradorBase;
        $this->pedido->totalEncargosCompradorBRL = $configBoleto->totalEncargosCompradorBRL;
        $this->pedido->totalEncargosVendedorBase = $configBoleto->totalEncargosVendedorBase;
        $this->pedido->totalEncargosVendedorBRL = $configBoleto->totalEncargosVendedorBRL;
        $this->pedido->encargosComprador = $configBoleto->encargosComprador;
        $this->pedido->encargosVendedor = $configBoleto->encargosVendedor;

        $this->pedido->totalWC = $configBoleto->totalPedidoBase;
        $this->pedido->totalGeral = $configBoleto->totalGeralCompradorBRL;
        $this->pedido->vlrParcela = $configBoleto->totalGeralCompradorBRL;        

        $this->pedido->pedidoNumero = $order->get_order_number();

        $this->pedido->compradorNomeCompleto = $order->get_formatted_billing_full_name();
        $this->pedido->compradorNome = $order->get_billing_first_name();
        $this->pedido->compradorSobrenome = $order->get_billing_last_name();
        $this->pedido->compradorEmail = $order->get_billing_email();

        $this->pedido->compradorFoneCompleto = preg_replace('/[^0-9]/', '', $order->get_billing_phone());
        $this->pedido->compradorDdd = substr($this->pedido->compradorFoneCompleto, 0, 2);
        $this->pedido->compradorFone = substr($this->pedido->compradorFoneCompleto, 2);

        $billing_array = $order->get_address();

        $cellphone = $this->pedido->compradorFoneCompleto;
        if (array_key_exists('cellphone', $billing_array)) {
            $cellphone = $billing_array['cellphone'];
        }
        $this->pedido->compradorFoneCelular = $cellphone;

        $billingNumber = null;
        if (array_key_exists('number', $billing_array)) {
            $billingNumber = $billing_array['number'];
        }
        if (!is_numeric($billingNumber)) {
            $billingNumber = $order->get_billing_address_1();
            $billingNumber = str_replace(',', ' ', $billingNumber);
            $billingNumber = str_replace('-', ' ', $billingNumber);
            $billingNumberArray = EXPLODE(' ', $billingNumber);
            $billingNumber = end($billingNumberArray);
        }

        if (!is_numeric($billingNumber)) {
            wc_add_notice('Informe o número. Ex: Av. Paulista, 501', 'error');
            return;
        }

        $neighborhood = $order->get_billing_city();
        if (array_key_exists('neighborhood', $billing_array)) {
            $neighborhood = $billing_array['neighborhood'];
        }

        $this->pedido->compradorEnderecoLogradouro = $order->get_billing_address_1();
        $this->pedido->compradorEnderecoNumero = $billingNumber;
        $this->pedido->compradorEnderecoComplemento = $order->get_billing_address_2();
        $this->pedido->compradorEnderecoBairro = $neighborhood;
        $this->pedido->compradorEnderecoCidade = $order->get_billing_city();
        $this->pedido->compradorEnderecoUf = $order->get_billing_state();
        $this->pedido->compradorEnderecoCep = preg_replace('/[^0-9]/', '', $order->get_billing_postcode());

        $this->pedido->itensPedido = array();
        foreach ($order->get_items() as $lineItem) {

            $qtd = intval($lineItem->get_quantity());
            $precoBase = $order->get_item_total($lineItem, false);

            array_push($this->pedido->itensPedido, array(
                'produtoId' => $lineItem->get_product_id(),
                'produtoDescricao' => strip_tags($lineItem->get_name()),
                'produtoQuantidade' => $qtd,
                'produtoPreco' => $precoBase,
                'produtoTotal' => round($precoBase * $qtd, 2)
            ));
        }

        // Registra venda na PGW Payments
        $json = json_encode($this->pedido);
        $ch = curl_init(PGW_BOLETO_ENDPOINT . '/woo/boletos');
        // $ch = curl_init($this->pgwPayEndpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json)
        ));
        $json_response = json_decode(curl_exec($ch));
        $erro = curl_error($ch);
        if (!empty($erro)) {
            error_log('PGW Payments: ' . $erro);
        }
        curl_close($ch);
        $order->set_customer_note($json_response->paymentLink);
        $order->save();

        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }
}
