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
?>

<script style="display:none;">
function aplicar_bandeira_cielo_webservice_tef(bandeira){
    jQuery(".meio_cielo_webservice_img_tef").css({ opacity: 0.2 });
    jQuery("."+ bandeira ).css({ opacity: 1 });
    jQuery('#bandeira-cielo-webservice-tef').val(bandeira);
}
</script>

<div id="tela-cielo-webservice-tef" style="width:100%;">

<p style="margin-bottom: 5px;">Selecione abaixo a banco qual deseja realizar o pagamento, ao finalizar ser&aacute; redirecionado ao ambiente do mesmo para autorizar e concluir o pagamento.</p>

<fieldset class="wc-credit-card-form wc-payment-form">

<?php if(strlen($fiscal)==11 || strlen($fiscal)==14){ ?>

<input type="hidden" id="fiscal-cielo-webservice" name="cielo_webservice_tef[fiscal]" value="<?php echo $fiscal;?>">

<?php }else{ ?>

<p class="form-row form-row-wide woocommerce-validated campos_cielo_webservice">
<label style="padding: 5px 0 5px 5px;">CPF/CNPJ:</label>
<input style="box-shadow: inset 2px 0 0 #0f834d;height:40px;background-color: #f2f2f2;"  onkeyup="this.value=this.value.replace(/[^\d]/,'')" maxlength="14" type="text" class="input-text mascaras_campos_cielo_webservice" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="CPF ou CNPJ" id="fiscal-cielo-webservice-tef" name="cielo_webservice_tef[fiscal]" value="<?php echo $fiscal;?>">
</p>

<?php } ?>

<p id="tela-bandeiras-cielo-tef" class="form-row form-row-wide woocommerce-validated">
<span style="float:left;">
<?php 
$padrao = null;
foreach($this->meios AS $k=>$b){
$padrao = $b;
?>
<img style="cursor:pointer;float:left;min-height:40px;" class='meio_cielo_webservice_img_tef <?php echo $b;?>' onclick="aplicar_bandeira_cielo_webservice_tef('<?php echo $b;?>')" src='<?php echo plugins_url().'/loja5-woo-cielo-webservice/images/'.$b.'.png';?>' width="60">
<?php 
}
?>
</span>
</p>

<input type="hidden" name="cielo_webservice_tef[bandeira]" id="bandeira-cielo-webservice-tef" value="">

</fieldset>

</div>	

<?php if(count($this->meios)==1){ ?>
<script>aplicar_bandeira_cielo_webservice_tef('<?php echo $padrao; ?>');</script>
<?php } ?>