<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
//se pf ou pj
$fiscal = '';
if(isset($_REQUEST['post_data']) && !empty($_REQUEST['post_data'])){
	parse_str(trim($_REQUEST['post_data']), $output);
	if(isset($output['billing_persontype']) && $output['billing_persontype']==1 && isset($output['billing_cpf']) && !empty($output['billing_cpf'])){
		$fiscal = preg_replace('/\D/', '',$output['billing_cpf']);
	}elseif(isset($output['billing_persontype']) && $output['billing_persontype']==2 && isset($output['billing_cnpj']) && !empty($output['billing_cnpj'])){
		$fiscal = preg_replace('/\D/', '',$output['billing_cnpj']);
	}
}
//gera a session de validacao
$time = time();
$_SESSION['session_cielo_loja_5'] = $time;
//metodo selecionado
$chosen_payment_method = WC()->session->get('chosen_payment_method');
?>

<script style="display:none;">
//ajax acoes
var ajax_url_cielo_loja5 = "<?php echo admin_url('admin-ajax.php'); ?>";

//dados definidos
var url_cielo_webservice = '<?php echo plugins_url();?>';
var total_pedido_cielo = '<?php echo $total_cart;?>';
var hash_pedido_cielo = '<?php echo sha1(md5($total_cart));?>';

//funcoes
function aplicar_bandeira_cielo_webservice_qrcode(bandeira){
    jQuery(".meio_cielo_webservice_img_qrcode").css({ opacity: 0.2 });
    jQuery("."+ bandeira ).css({ opacity: 1 });
    jQuery('#parcela-cielo-webservice-qrcode').html('<option value="">Aguarde...</option>');
    jQuery.post(ajax_url_cielo_loja5, {action : 'parcelas_cielo_webservice', id : bandeira,total: total_pedido_cielo,hash: hash_pedido_cielo, moeda: '<?php echo $currency_code;?>' }, retorno_parcelamento_qrcode, 'JSON');
    jQuery('#bandeira-cielo-webservice-qrcode').val(bandeira);
}

function retorno_parcelamento_qrcode(data) {
    console.log(data);
    var items = '';
    var par  = 0;
    jQuery.each(data, function(key, val) {
        items += '<option data-parcela="'+par+'" value="' + key + '">' + val + '</option>';
        par += 1;
    });
    jQuery('#parcela-cielo-webservice-qrcode').html(items);
}
</script>

<div id="tela-cielo-webservice-qrcode" style="width:100%;">

<p style="margin-bottom: 5px;">Pagamento via <b>QrCode Cielo Pay</b>, pague diretamente via APP da Cielo de forma f&aacute;cil e segura, <u>clique e selecione a bandeira de seu cart&atilde;o qual deseja realizar o pagamento</u> para que o sistema calcule as parcelas, somente finalize por este m&eacute;todo caso j&aacute; tenha em seu Celular o APP Cielo Pay j&aacute; previamente instalado e configurado sua carteira de pagamento.</p>

<fieldset class="wc-credit-card-form wc-payment-form">

<p style="width: 100%; display: inherit;" id="tela-bandeiras-cielo-qrcode" class="form-row form-row-wide woocommerce-validated">
<span style="float:left;">
<?php
if($meios){ 
	foreach($meios AS $k=>$b){
	?>
	<img style="cursor:pointer;float:left;border: 1px solid #CCC;min-height:30px;" class='meio_cielo_webservice_img_qrcode <?php echo $b;?>' onclick="aplicar_bandeira_cielo_webservice_qrcode('<?php echo $b;?>')" src='<?php echo plugins_url().'/loja5-woo-cielo-webservice/images/'.$b.'.png';?>' width="45">
	<?php 
	}
}else{
	echo 'Selecione as bandeiras na config do plugin!';
}
?>
</span>
</p>

<input type="hidden" name="cielo_webservice_qrcode[bandeira]" id="bandeira-cielo-webservice-qrcode" value="">
<input type="hidden" name="cielo_webservice_qrcode[hash]" value="<?php echo $hash;?>">
<input type="hidden" name="cielo_webservice_qrcode[time]" value="<?php echo $time;?>">

<p class="form-row form-row-wide woocommerce-validated campos_cielo_webservice">
<label style="padding: 5px 0 5px 5px;">Parcela:</label>
<select style="box-shadow: inset 2px 0 0 #0f834d;height:40px;width:100%;background-color: #f2f2f2;border: none;" name="cielo_webservice_qrcode[parcela]" id="parcela-cielo-webservice-qrcode">
<option value="">Selecione uma bandeira...</option>
</select>
</p>

</fieldset>

</div>