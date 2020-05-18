<?php defined('ABSPATH') || exit; ?>

<style>
    #pgw--boleto-logo {
        max-width: 80px;
        max-height: 35px;
        margin-left: 5px;
    }

    #pgw--boleto-logo-container {
        font-size: 0,8em;
        text-align: right;
        color: #c2c4c7;
    }

    #pgw--boleto-logo-container::before {
        content: "Pagando Via";
    }
</style>

<div id="pgw--containerDisclaimerBoleto">
    <div class="form-row form-row-wide"> <?= $disclaimerHtml; ?> </div>
    <div class="form-row form-row-wide">
        <input type="checkbox" id="pgw--disclaimerAcceptedBoleto" name="pgw--disclaimerAcceptedBoleto" style="display: inline;">
        <label for="pgw--disclaimerAcceptedBoleto" style="display: inline;">Li e concordo com os termos acima.</label>
    </div>
</div>

<div id="pgw--containerPaymentFormBoleto">
    <div class="form-row form-row-wide">
        <label>Tipo de Pessoa <span class="required">*</span></label>
        <input id="pgw--tipoPessoaPfBoleto" type="radio" name="pgw--tipoPessoaBoleto" value="PF" />
        <label for="pgw--tipoPessoaPfBoleto" style="display: inline; margin-left: 3px;">Pessoa Física</label>
        <input id="pgw--tipoPessoaPjBoleto" type="radio" name="pgw--tipoPessoaBoleto" value="PJ" style="margin-left: 15px;" />
        <label for="pgw--tipoPessoaPjBoleto" style="display: inline; margin-left: 3px;">Pessoa Jurídica</label>
    </div>
    <div class="form-row form-row-wide" id="pgw--containerCpfBoleto">
        <label>CPF <span class="required">*</span></label>
        <input id="pgw--cpfBoleto" type="text" name="pgw--cpfBoleto" placeholder="000.000.000-00" />
    </div>
    <div class="form-row form-row-wide" id="pgw--containerCnpjBoleto">
        <label>CNPJ <span class="required">*</span></label>
        <input id="pgw--cnpjBoleto" type="text" name="pgw--cnpjBoleto" placeholder="00.000.000/0000-00" />
    </div>
    <div class="form-row form-row-wide" id="pgw--containerCambioBoleto">
        <label id="pgw--taxaCambioBoleto" style="display: block; width: 100%;">
            Taxa de câmbio:<strong style="margin-left: 10px;">R$ <?= number_format($configBoleto->cambio, 2, ',', '.'); ?></strong>
        </label>
        <label id="pgw--taxaBRLPedidoBoleto" style="display: block; width: 100%;">
            Total do pedido:<strong style="margin-left: 10px;">R$ <?= number_format($configBoleto->totalPedidoBRL, 2, ',', '.'); ?></strong>
        </label>
        <label id="pgw--taxaBRLDespesasBoleto" style="display: block; width: 100%;">
            Impostos incidentes:<strong style="margin-left: 10px;">R$ <?= number_format($configBoleto->totalEncargosCompradorBRL, 2, ',', '.'); ?></strong>
        </label>
        <label id="pgw--taxaBRLBoleto" style="display: block; width: 100%;">
            Total a pagar com impostos:<strong style="margin-left: 10px;">R$ <?= number_format($configBoleto->totalGeralCompradorBRL, 2, ',', '.'); ?></strong>
        </label>
    </div>  
    <div class="form-row form-row-wide">
        <p style="margin-top: 15px;"><strong>Importante:</strong> A PGW Payments utiliza a plataforma do PAGSEGURO no processamento das transações com boleto. Todos os nossos boletos são registrados. Pague preferencialmente pelo sistema DDA.</p>
    </div>  
    <div class="form-row form-row-wide" id="pgw--boleto-logo-container">
        <img id="pgw--boleto-logo" alt="PGW" src="<?= PGW_BOLETO_URL . '/assets/images/logo.png'; ?>" />
    </div>
    <div class="form-row form-row-wide">
        <input type="hidden" id="pgw--boletoConfig" name="pgw--boletoConfig" value='<?= $configBoletoJSON; ?>'/>
    </div>
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

<script type="text/javascript">
    (function($) {
        $(function() {

            var enabledDisclaimerBoleto = "<?= $this->enabledDisclaimer; ?>" === "yes";
            console.log("enabledDisclaimerBoleto:", enabledDisclaimerBoleto);
            if (enabledDisclaimerBoleto) {
                console.log('Show disclaimer - disclaimer');
                console.log('Hide form - disclaimer');
                $("#pgw--containerDisclaimerBoleto").show();
                $("#pgw--containerPaymentFormBoleto").hide();
            } else {
                console.log('Hide disclaimer - disclaimer');
                console.log('Show form - disclaimer');
                $("#pgw--containerDisclaimerBoleto").hide();
                $("#pgw--containerPaymentFormBoleto").show();
                $("#pgw--tipoPessoaPfBoleto").attr('checked', true);
                $("#pgw--containerCpfBoleto").show();
                $("#pgw--containerCnpjBoleto").hide();
                $("#pgw--cpfBoleto").val("");
                $("#pgw--cnpjBoleto").val("");
            }

            $("#pgw--disclaimerAcceptedBoleto").change(function() {
                if ($("#pgw--disclaimerAcceptedBoleto").prop("checked")) {
                    console.log('Show form - accept');
                    $("#pgw--containerPaymentFormBoleto").show();
                } else {
                    console.log('Hide form - accept');
                    if (enabledDisclaimerBoleto) {
                        $("#pgw--containerPaymentFormBoleto").hide();
                    }
                }
                $("#pgw--tipoPessoaPfBoleto").attr('checked', true);
                $("#pgw--containerCpfBoleto").show();
                $("#pgw--containerCnpjBoleto").hide();
                $("#pgw--cpfBoleto").val("");
                $("#pgw--cnpjBoleto").val("");
            });

            $("#pgw--cpfBoleto").mask("000.000.000-00");
            $("#pgw--cnpjBoleto").mask("00.000.000/0000-00");

            $("#pgw--tipoPessoaPfBoleto").change(function() {
                if ($("#pgw--tipoPessoaPfBoleto").attr('checked') == 'checked') {
                    $("#pgw--containerCpfBoleto").show();
                    $("#pgw--containerCnpjBoleto").hide();
                    $("#pgw--cnpjBoleto").val("");
                }
            });

            $("#pgw--tipoPessoaPjBoleto").change(function() {
                if ($("#pgw--tipoPessoaPjBoleto").attr('checked') == 'checked') {
                    $("#pgw--containerCpfBoleto").hide();
                    $("#pgw--containerCnpjBoleto").show();
                    $("#pgw--cpfBoleto").val("");
                }
            });

            var moedaLoja = "<?= $configBoleto->moeda; ?>";
            if (moedaLoja === "BRL") {
                $("#pgw--containerCambioBoleto").hide();
            } else {
                $("#pgw--containerCambioBoleto").show();                
            }

        });
    })(jQuery);
</script>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-146548974-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'UA-146548974-1');
</script>