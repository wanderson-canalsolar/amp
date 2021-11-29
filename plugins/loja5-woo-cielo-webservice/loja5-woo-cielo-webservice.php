<?php
/*
Plugin Name: Cielo API 3.0 - Loja5
Description: Integra&ccedil;&atilde;o de Pagamento ao Cielo API 3.0.
Version: 4.0
Author: Loja5.com.br
Author URI: https://loja5.com.br/
Copyright: © 2009-2021 Loja5.
License: Commercial
*/

//define a pasta do modulo
define('CIELO_WEBSERVICE_WOO_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ));
define('LOJA5_CIELO_WEBSERVICE_WOO_MODULO_COMERCIAL', __FILE__ );
define('CIELO_WEBSERVICE_WOO_CURL_SSL', 6);
define('CIELO_WEBSERVICE_WOO_PRAZO_GESTOR', 15);
define('CIELO_WEBSERVICE_WOO_BAIXA_STOCK', false);
define('CIELO_WEBSERVICE_WOO_REESTOCK', false);
define('CIELO_WEBSERVICE_WOO_CREDITO_3DS', true);
define('CIELO_WEBSERVICE_WOO_ROLE', 'manage_woocommerce');//role de permissao de ver pedidos

//atalhos
function plugin_action_links_loja5_woo_cielo_webservice( $links ) {
    $plugin_links = array();
	$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=loja5_woo_cielo_webservice' ) ) . '">' . __( 'Crédito', 'loja5-woo-cielo-webservice' ) . '</a>';
	$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=loja5_woo_cielo_webservice_debito' ) ) . '">' . __( 'Débito', 'loja5-woo-cielo-webservice' ) . '</a>';
	$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=loja5_woo_cielo_webservice_boleto' ) ) . '">' . __( 'Boleto', 'loja5-woo-cielo-webservice' ) . '</a>';
	$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=loja5_woo_cielo_webservice_tef' ) ) . '">' . __( 'TEF', 'loja5-woo-cielo-webservice' ) . '</a>';
	$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=loja5_woo_cielo_webservice_qrcode' ) ) . '">' . __( 'QrCode', 'loja5-woo-cielo-webservice' ) . '</a>';
    return array_merge( $plugin_links, $links );
}
if(is_admin()) {
    add_filter('plugin_action_links_'.plugin_basename( __FILE__ ),'plugin_action_links_loja5_woo_cielo_webservice');
}

//funcao de inicializacao
function loja5_woo_cielo_webservice_init() {
	//se possui ioncube loads
	if(extension_loaded("IonCube Loader")) {
		//chama as classes do modulo
		if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

		if ( !class_exists( 'WC_Gateway_Loja5_Woo_Cielo_Webservice' ) ){
			//especifico por versao
			if(version_compare(PHP_VERSION, '5.6.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/class.loja5.php' );
			}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/class.loja5.php' );
			}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/class.loja5.php' );
			}else{
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/class.loja5.php' );
			}

			//em comum
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/classes/class.cielo_credito.php' );
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/classes/class.cielo_debito.php' );
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/classes/class.cielo_tef.php' );
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/classes/class.cielo_boleto.php' );
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/classes/class.cielo_qrcode.php' );
		}
		
		//class validar cpf/cnpj
		if ( !class_exists( 'ValidaCPFCNPJ' ) ){
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/classes/class.fiscal.php' );
		}
		
		//cria um metabox em detalhes do pedido
		if ( !class_exists( 'WC_Cielo_Webservice_Loja5_Metabox' ) ){
			require_once(CIELO_WEBSERVICE_WOO_PATH.'/classes/class.metabox.cielo.php');
			new WC_Cielo_Webservice_Loja5_Metabox;
		}
		
		//permissao de escrita 
		if(!is_writable(CIELO_WEBSERVICE_WOO_PATH)){
			add_action( 'admin_notices', 'loja5_woo_cielo_webservice_alerta_escrita' );
		}
		
		//admin
		if ( !class_exists( 'WC_Cielo_Webservice_Loja5_Admin' ) ){
			//especifico por versao
			if(version_compare(PHP_VERSION, '5.6.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/admin.php' );
			}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/admin.php' );
			}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/admin.php' );
			}else{
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/admin.php' );
			}
			new WC_Cielo_Webservice_Loja5_Admin;
		}
		
		//adiciona o plugin ao woocommerce
		function woocommerce_add_loja5_woo_cielo_webservice($methods) {
			$methods[] = 'WC_Gateway_Loja5_Woo_Cielo_Webservice';
			$methods[] = 'WC_Gateway_Loja5_Woo_Cielo_Webservice_Debito';
			$methods[] = 'WC_Gateway_Loja5_Woo_Cielo_Webservice_TEF';
			$methods[] = 'WC_Gateway_Loja5_Woo_Cielo_Webservice_Boleto';
			$methods[] = 'WC_Gateway_Loja5_Woo_Cielo_Webservice_QrCode';
			return $methods;
		}
		add_filter('woocommerce_payment_gateways', 'woocommerce_add_loja5_woo_cielo_webservice');
	
	}else{
		//alerta ioncube
		add_action( 'admin_notices', 'loja5_woo_cielo_webservice_alerta_ioncube' );
	}
}

//alerta permissao de escrita
function loja5_woo_cielo_webservice_alerta_escrita(){
	echo '<div class="error">';
	echo '<p><strong>Cielo API [Loja5]:</strong> Aplique permiss&atilde;o de escrita ao diretorio <u>'.CIELO_WEBSERVICE_WOO_PATH.'</u> para que o m&oacute;dulo possa ser ativado corretamente!</p>';
	echo '</div>';
}

//alerta ioncube 
function loja5_woo_cielo_webservice_alerta_ioncube(){
	echo '<div class="error">';
	echo '<p><strong>Cielo API [Loja5]:</strong> Sua hospedagem n&atilde;o possui o Ioncube ativado, solicite a mesma ativar ou veja com o gestor de seu host!</p>';
	echo '</div>';
}

//inicializa o modulo no wordpress
add_action('plugins_loaded', 'loja5_woo_cielo_webservice_init', 0);

//retorno de dados qrcode
add_action( 'wp_ajax_retorno_qrcode_cielo_webservice', 'retorno_qrcode_cielo_webservice');
add_action( 'wp_ajax_nopriv_retorno_qrcode_cielo_webservice','retorno_qrcode_cielo_webservice');
function retorno_qrcode_cielo_webservice(){
	global $wpdb;
	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
		//valida 
		if(sha1($_REQUEST['id']) != $_REQUEST['hash']){
			echo json_encode(array('atualizar'=>false,'erro'=>'acesso negado'));
			exit;
		}

		//faz o include das config cielo
		if(version_compare(PHP_VERSION, '5.6.0', '<')){
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/restclient.php' );
		}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/restclient.php' );
		}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/restclient.php' );
		}else{
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/restclient.php' );
		}
		
		//config
		$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice_QrCode();
		$id_meio_woo = 'loja5_woo_cielo_webservice_qrcode';
		
		//cielo api
		if($config->testmode=='yes'){
			$provider = 'Simulado';
			$urlweb = "https://apiquerysandbox.cieloecommerce.cielo.com.br/1/";
		}else{
			$provider = 'Cielo';
			$urlweb = "https://apiquery.cieloecommerce.cielo.com.br/1/";
		}
		$objResposta = array();
		$headers = array(
			"Content-Type" => "application/json",
			"Accept" => "application/json",
			"MerchantId" =>trim($config->afiliacao),
			"MerchantKey" => trim($config->chave),
			"RequestId" => "",
		);
		$api = new RestClientCielo(array(
			'base_url' => $urlweb, 
			'headers' => $headers, 
		));
		$response = $api->get("sales/".trim($_REQUEST['id'])."");
		$dados_pedido = @json_decode($response->response,true);

		//debug
		if ( 'yes' === $config->debug ) {
			$logs = new WC_Logger();
			$logs->add( $id_meio_woo, 'Log Retorno QrCode Cielo: '.$response->response );
		}
		
		//se ocorreu erro salva os logs 
		if($response->status < 200 || $response->status > 201){
			$logs = new WC_Logger();
			$logs->add( $id_meio_woo, 'Erro Cielo Retorno QrCode em '.date('d/m/Y H:i:s').'');
			$logs->add( $id_meio_woo, 'Log: '.$response->response );
		}
		
		//resultado
		if(($response->status==200 || $response->status==201) && isset($dados_pedido['Payment']['PaymentId'])){
			//infors
			$pedido_id = $dados_pedido['MerchantOrderId'];
			$status_id = $dados_pedido['Payment']['Status'];
			$lr = isset($dados_pedido['Payment']['ReturnCode'])?$dados_pedido['Payment']['ReturnCode']:'';
			$lr_log = isset($dados_pedido['Payment']['ReturnMessage'])?$dados_pedido['Payment']['ReturnMessage']:'';

			//se status = 12
			if($dados_pedido['Payment']['Status']==12){
				echo json_encode(array('atualizar'=>false,'erro'=>'pagamento ainda pendente'));
				exit;
			}
			
			//pega o pedido
			$order = wc_get_order((int)($pedido_id));
			
			//status titulo
			switch($status_id){
				case '2':
					$status = 'Aprovada';
				break;
				case '1':
					$status = 'Autorizada';
				break;
				case '3':
					$status = 'Negada';
				break;
				case '10':
				case '13':
					$status = 'Cancelada';
				break;
				default:
					$status = 'Aguardando Pagamento';
			}
			
			//cria uma nota no pedido
			$order->add_order_note("Transa&ccedil;&atilde;o QrCode Cielo - QrCode ID ".$dados_pedido['Payment']['QrCodeId']." - ".$status."");
			
			//restore 
			if($status_id==3 || $status_id==10 || $status_id==13){
				wc_increase_stock_levels($order->get_id());
			}
				
			//status
			switch($status_id){
				case '2':
					$order->update_status($config->pago);
				break;
				case '1':
					$order->update_status($config->autorizado);
				break;
				case '3':
					$order->update_status($config->negado);
				break;
				case '10':
				case '13':
					$order->update_status($config->cancelado);
				break;
			}

			//bin 
			$bin = isset($dados_pedido['Payment']['CreditCard']['CardNumber'])?$dados_pedido['Payment']['CreditCard']['CardNumber']:'';
			
			//atualiza o pedido no banco de dados
			$wpdb->query("UPDATE `wp_cielo_api_loja5` SET `lr` =  '".$lr."', `bin` =  '".$bin."',  `lr_log` =  '".$lr_log."', `status` =  '".$status_id."' WHERE `pedido` = '".(int)($pedido_id)."';");

			//atualiza o meta
			update_post_meta($pedido_id,'_dados_cielo_api',$dados_pedido);
			
			//ok
			echo json_encode(array('atualizar'=>true,'erro'=>'pagamento ok'));
			exit;
			
		}else{
			//senao fim
			echo json_encode(array('atualizar'=>false,'erro'=>'sem resposta ou erro de api'));
			exit;
		}
	}else{
		//senao fim
		echo json_encode(array('atualizar'=>false,'erro'=>'sem dados'));
		exit;
	}
}

//cron de pagamento boleto e tef 
add_action( 'wp_ajax_cron_boleto_tef_cielo_webservice', 'cron_boleto_tef_cielo_webservice');
add_action( 'wp_ajax_nopriv_cron_boleto_tef_cielo_webservice','cron_boleto_tef_cielo_webservice');
function cron_boleto_tef_cielo_webservice(){
	global $wpdb;
	//faz o include das config cielo
	if(version_compare(PHP_VERSION, '5.6.0', '<')){
		include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/restclient.php' );
	}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
		include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/restclient.php' );
	}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
		include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/restclient.php' );
	}else{
		include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/restclient.php' );
	}
	//consulta
	$pedidos = $wpdb->get_results("SELECT * FROM `wp_cielo_api_loja5` WHERE (metodo='boleto' OR metodo='tef') AND status='0' ORDER BY id DESC LIMIT 50;", 'ARRAY_A');
	foreach($pedidos as $registro){
		//pedido no woo 
		$order = wc_get_order((int)($registro['pedido']));
		if($order){
		$status_atual = str_replace('wc-','',$order->get_status());
		
		//config de acordo o tipo de pagamento 
		if($order->get_payment_method()=='loja5_woo_cielo_webservice'){
			$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice();
		}elseif($order->get_payment_method()=='loja5_woo_cielo_webservice_debito'){
			$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice_Debito();
		}elseif($order->get_payment_method()=='loja5_woo_cielo_webservice_tef'){
			$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice_TEF();
		}elseif($order->get_payment_method()=='loja5_woo_cielo_webservice_boleto'){
			$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice_Boleto();
		}
		
		//somente pedidos aguardando pagamento
		if($status_atual==str_replace('wc-','','wc-on-hold')){
			
			//cielo api
			if($config->testmode=='yes'){
				$provider = 'Simulado';
				$urlweb = "https://apiquerysandbox.cieloecommerce.cielo.com.br/1/";
			}else{
				$provider = 'Cielo';
				$urlweb = "https://apiquery.cieloecommerce.cielo.com.br/1/";
			}
			$objResposta = array();
			$headers = array(
				"Content-Type" => "application/json",
				"Accept" => "application/json",
				"MerchantId" =>trim($config->afiliacao),
				"MerchantKey" => trim($config->chave),
				"RequestId" => "",
			);
			$api = new RestClientCielo(array(
				'base_url' => $urlweb, 
				'headers' => $headers, 
			));
			$response = $api->get("sales/".$registro['id_pagamento']."");
			$dados_pedido = @json_decode($response->response,true);
			if(($response->status==200 || $response->status==201) && isset($dados_pedido['Payment']['Status'])){
				if($dados_pedido['Payment']['Status']==2){
					//atualiza
					$order->update_status($config->pago);
					echo $registro['pedido'].' pago!<br>';
					//atualiza o pedido no banco de dados
					$wpdb->query("UPDATE `wp_cielo_api_loja5` SET `status` =  '2' WHERE `pedido` = '".(int)($registro['pedido'])."';");
				}elseif($dados_pedido['Payment']['Status']==3 || $dados_pedido['Payment']['Status']==10 || $dados_pedido['Payment']['Status']==13){
					//atualiza
					$order->update_status($config->cancelado);
					wc_increase_stock_levels($order->get_id());
					echo $registro['pedido'].' cancelado!<br>';
					//atualiza o pedido no banco de dados
					$wpdb->query("UPDATE `wp_cielo_api_loja5` SET `status` =  '10' WHERE `pedido` = '".(int)($registro['pedido'])."';");
				}else{
					//exibe
					echo $registro['pedido'].' aguardando!<br>';
				}
			}

		}
		}		
	}
	echo '<br>CRON - OK';
	exit;
}

//retorno de dados postback cielo
add_action( 'wp_ajax_retorno_ipn_cielo_webservice', 'retorno_ipn_cielo_webservice');
add_action( 'wp_ajax_nopriv_retorno_ipn_cielo_webservice','retorno_ipn_cielo_webservice');
function retorno_ipn_cielo_webservice(){
	global $wpdb;
	if(isset($_REQUEST['PaymentId']) && isset($_REQUEST['ChangeType'])){
		$id_pagamento = trim($_REQUEST['PaymentId']);
		$tipo_req = (int)$_REQUEST['ChangeType'];
		$id_meio_woo = 'loja5_woo_cielo_webservice';
		if($tipo_req==1){
			//faz o include das config cielo
			if(version_compare(PHP_VERSION, '5.6.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/restclient.php' );
			}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/restclient.php' );
			}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/restclient.php' );
			}else{
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/restclient.php' );
			}
			//config 
			$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice();
			
			//cielo api
			if($config->testmode=='yes'){
				$provider = 'Simulado';
				$urlweb = "https://apiquerysandbox.cieloecommerce.cielo.com.br/1/";
			}else{
				$provider = 'Cielo';
				$urlweb = "https://apiquery.cieloecommerce.cielo.com.br/1/";
			}
			$objResposta = array();
			$headers = array(
				"Content-Type" => "application/json",
				"Accept" => "application/json",
				"MerchantId" =>trim($config->afiliacao),
				"MerchantKey" => trim($config->chave),
				"RequestId" => "",
			);
			$api = new RestClientCielo(array(
				'base_url' => $urlweb, 
				'headers' => $headers, 
			));
			$response = $api->get("sales/".$id_pagamento."");
			$dados_pedido = @json_decode($response->response,true);
			
			//debug
			if ( 'yes' === $config->debug ) {
				$logs = new WC_Logger();
				$logs->add( $id_meio_woo, 'Log Postback Cielo '.strtoupper($tipo).': '.$response->response );
			}
			
			//se ocorreu erro salva os logs 
			if($response->status < 200 || $response->status > 201){
				$logs = new WC_Logger();
				$logs->add( $id_meio_woo, 'Erro Cielo Postback em '.date('d/m/Y H:i:s').'');
				$logs->add( $id_meio_woo, 'Log: '.$response->response );
			}
			
			//resultado
			if(($response->status==200 || $response->status==201) && isset($dados_pedido['Payment']['PaymentId'])){
				//infors
				$pedido_id = $dados_pedido['MerchantOrderId'];
				$status_id = $dados_pedido['Payment']['Status'];
				$lr = isset($dados_pedido['Payment']['ReturnCode'])?$dados_pedido['Payment']['ReturnCode']:'';
				$lr_log = isset($dados_pedido['Payment']['ReturnMessage'])?$dados_pedido['Payment']['ReturnMessage']:'';
				
				//pega o pedido
				$order = wc_get_order((int)($pedido_id));
				if(!$order->get_id()){
					die('Pedido nao encontrado!');
				}
				$status_atual = str_replace('wc-','',$order->get_status());
				
				//status titulo
				switch($status_id){
					case '2':
						$status_mudar = $config->pago;
						$status = 'Aprovada';
					break;
					case '1':
					$status_mudar = $config->autorizado;
						$status = 'Autorizada';
					break;
					case '3':
						$status_mudar = $config->negado;
						$status = 'Negada';
					break;
					case '10':
					case '13':
						$status_mudar = $config->cancelado;
						$status = 'Cancelada';
					break;
				}
				
				//cria uma nota no pedido
				if(isset($status_mudar)){
					if($order->get_payment_method()=='loja5_woo_cielo_webservice_debito'){
						$order->add_order_note("Transa&ccedil;&atilde;o D&eacute;bito Cielo - TID ".$dados_pedido['Payment']['Tid']." - ".$status." (POST)");
					}elseif($order->get_payment_method()=='loja5_woo_cielo_webservice_tef'){
						$order->add_order_note("Transa&ccedil;&atilde;o TEF Cielo - ID ".$dados_pedido['Payment']['PaymentId']." - ".$status." (POST)");
					}elseif($order->get_payment_method()=='loja5_woo_cielo_webservice_boleto'){
						$order->add_order_note("Transa&ccedil;&atilde;o Boleto Cielo - ID ".$dados_pedido['Payment']['PaymentId']." - ".$status." (POST)");
					}else{
						$order->add_order_note("Transa&ccedil;&atilde;o Cr&eacute;dito Cielo - TID ".$dados_pedido['Payment']['Tid']." - ".$status." (POST)");
					}
				}
				
				//restore 
				if($status_id==3 || $status_id==10 || $status_id==13){
					wc_increase_stock_levels($order->get_id());
				}
					
				//status
				if(isset($status_mudar) && str_replace('wc-','',$status_mudar)!=$status_atual){
					$order->update_status($status_mudar);
				}
				
				//atualiza o pedido no banco de dados
				$wpdb->query("UPDATE `wp_cielo_api_loja5` SET  `lr` =  '".$lr."',  `lr_log` =  '".$lr_log."', `status` =  '".$status_id."' WHERE `pedido` = '".(int)($pedido_id)."';");
				
			}
			
		}
	}
	echo 'IPN - OK';
	exit;
}

//javascript checkout 
add_action( 'wp_enqueue_scripts', 'loja5_woo_cielo_api_jscript_checkout');
function loja5_woo_cielo_api_jscript_checkout() {
	if(!is_checkout()){
		return false;
	}
	wp_register_script( 'javascript-select-cielo-loja5', '' );
	wp_enqueue_script( 'javascript-select-cielo-loja5' );
	wp_add_inline_script( 'javascript-select-cielo-loja5', "
		//acao via onchange
		jQuery( function( $ ) {
			jQuery( document ).on( 'change', 'input[name=\"payment_method\"]', function () {
				if(jQuery(this).val()=='loja5_woo_cielo_webservice'){
					console.log(jQuery(this).val());
					jQuery('body').trigger('update_checkout');
				}
				if(jQuery(this).val()=='loja5_woo_cielo_webservice_debito'){
					console.log(jQuery(this).val());
					jQuery('body').trigger('update_checkout');
				}
				if(jQuery(this).val()=='loja5_woo_cielo_webservice_boleto'){
					console.log(jQuery(this).val());
					jQuery('body').trigger('update_checkout');
				}
				if(jQuery(this).val()=='loja5_woo_cielo_webservice_tef'){
					console.log(jQuery(this).val());
					jQuery('body').trigger('update_checkout');
				}
				if(jQuery(this).val()=='loja5_woo_cielo_webservice_qrcode'){
					console.log(jQuery(this).val());
					jQuery('body').trigger('update_checkout');
				}
			});
		});
	");
}

//retorno de dados debito/tef
add_action( 'wp_ajax_retorno_debito_cielo_webservice', 'retorno_debito_cielo_webservice');
add_action( 'wp_ajax_nopriv_retorno_debito_cielo_webservice','retorno_debito_cielo_webservice');
function retorno_debito_cielo_webservice(){
	global $wpdb;
	if(isset($_REQUEST['PaymentId']) && !empty($_REQUEST['PaymentId'])){
		//faz o include das config cielo
		if(version_compare(PHP_VERSION, '5.6.0', '<')){
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/restclient.php' );
		}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/restclient.php' );
		}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/restclient.php' );
		}else{
			include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/restclient.php' );
		}
		$tipo = isset($_REQUEST['tipo'])?$_REQUEST['tipo']:'debito';
		
		//config
		if($tipo=='debito'){
			$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice_Debito();
			$id_meio_woo = 'loja5_woo_cielo_webservice_debito';
		}elseif($tipo=='tef'){
			$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice_TEF();
			$id_meio_woo = 'loja5_woo_cielo_webservice_tef';
		}elseif($tipo=='boleto'){
			$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice_Boleto();
			$id_meio_woo = 'loja5_woo_cielo_webservice_boleto';
		}else{
			$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice();
			$id_meio_woo = 'loja5_woo_cielo_webservice';
		}
		
		//cielo api
		if($config->testmode=='yes'){
			$provider = 'Simulado';
			$urlweb = "https://apiquerysandbox.cieloecommerce.cielo.com.br/1/";
		}else{
			$provider = 'Cielo';
			$urlweb = "https://apiquery.cieloecommerce.cielo.com.br/1/";
		}
		$objResposta = array();
		$headers = array(
			"Content-Type" => "application/json",
			"Accept" => "application/json",
			"MerchantId" =>trim($config->afiliacao),
			"MerchantKey" => trim($config->chave),
			"RequestId" => "",
		);
		$api = new RestClientCielo(array(
			'base_url' => $urlweb, 
			'headers' => $headers, 
		));
		$response = $api->get("sales/".trim($_REQUEST['PaymentId'])."");
		$dados_pedido = @json_decode($response->response,true);
		
		//debug
		if ( 'yes' === $config->debug ) {
			$logs = new WC_Logger();
			$logs->add( $id_meio_woo, 'Log Retorno Cielo '.strtoupper($tipo).': '.$response->response );
		}
		
		//se ocorreu erro salva os logs 
		if($response->status < 200 || $response->status > 201){
			$logs = new WC_Logger();
			$logs->add( $id_meio_woo, 'Erro Cielo Retorno '.strtoupper($tipo).' em '.date('d/m/Y H:i:s').'');
			$logs->add( $id_meio_woo, 'Log: '.$response->response );
		}
		
		//resultado
		if(($response->status==200 || $response->status==201) && isset($dados_pedido['Payment']['PaymentId'])){
			//infors
			$pedido_id = $dados_pedido['MerchantOrderId'];
			$status_id = $dados_pedido['Payment']['Status'];
			$lr = isset($dados_pedido['Payment']['ReturnCode'])?$dados_pedido['Payment']['ReturnCode']:'';
			$lr_log = isset($dados_pedido['Payment']['ReturnMessage'])?$dados_pedido['Payment']['ReturnMessage']:'';
			
			//pega o pedido
			$order = wc_get_order((int)($pedido_id));
			
			//status titulo
			switch($status_id){
				case '2':
					$status = 'Aprovada';
				break;
				case '1':
					$status = 'Autorizada';
				break;
				case '3':
					$status = 'Negada';
				break;
				case '10':
				case '13':
					$status = 'Cancelada';
				break;
				default:
					$status = 'Aguardando Pagamento';
			}
			
			//cria uma nota no pedido
			if($tipo=='debito'){
				$order->add_order_note("Transa&ccedil;&atilde;o D&eacute;bito Cielo - TID ".$dados_pedido['Payment']['Tid']." - ".$status."");
			}elseif($tipo=='tef'){
				$order->add_order_note("Transa&ccedil;&atilde;o TEF Cielo - ID ".$dados_pedido['Payment']['PaymentId']." - ".$status."");
			}elseif($tipo=='boleto'){
				$order->add_order_note("Transa&ccedil;&atilde;o Boleto Cielo - ID ".$dados_pedido['Payment']['PaymentId']." - ".$status."");
			}else{
				$order->add_order_note("Transa&ccedil;&atilde;o Cr&eacute;dito Cielo - TID ".$dados_pedido['Payment']['Tid']." - ".$status."");
			}
			
			//restore 
			if($status_id==3 || $status_id==10 || $status_id==13){
				wc_increase_stock_levels($order->get_id());
			}
				
			//status
			switch($status_id){
				case '2':
					$order->update_status($config->pago);
				break;
				case '1':
					$order->update_status($config->autorizado);
				break;
				case '3':
					$order->update_status($config->negado);
				break;
				case '10':
				case '13':
					$order->update_status($config->cancelado);
				break;
			}
			
			//atualiza o pedido no banco de dados
			$wpdb->query("UPDATE `wp_cielo_api_loja5` SET  `lr` =  '".$lr."',  `lr_log` =  '".$lr_log."', `status` =  '".$status_id."' WHERE `pedido` = '".(int)($pedido_id)."';");
			
			//redireciona ao cupom
			$link = $config->get_return_url( $order );
			wp_redirect($link);
			exit;
			
		}else{
			//senao fim
			$link = get_option('woocommerce_myaccount_page_id');
			$link = get_permalink($link);
			wp_redirect($link);
			exit;
		}
	}else{
		//senao fim
		$link = get_option('woocommerce_myaccount_page_id');
		$link = get_permalink($link);
		wp_redirect($link);
		exit;
	}
}

//regras de juros 
function calcular_juros_cielo_webservice_composto($valor, $taxa, $parcelas) {
	//juros composto
	$taxa = $taxa/100;
	$valParcela = $valor * pow((1 + $taxa), $parcelas);
	$valParcela = $valParcela/$parcelas;
	return round($valParcela, 2);
}

function calcular_juros_cielo_webservice_price($valorTotal, $taxa, $nParcelas){
	//juros price
	$taxa = $taxa/100;
	$cadaParcela = ($valorTotal*$taxa)/(1-(1/pow(1+$taxa, $nParcelas)));
	return round($cadaParcela, 2);
}

function calcular_juros_cielo_webservice_simples($valor, $taxa, $parcelas) {
	//juros simples
	$taxa = $taxa/100;
	$m = $valor * (1 + $taxa * $parcelas);
	$valParcela = $m/$parcelas;
	return round($valParcela, 2);
}

function calcular_juros_cielo_webservice_porcentagem($valor, $taxa, $parcelas) {
	//juros simples
	$taxa = $taxa/100;
	$m = $valor * $taxa;
	$valParcela = ($valor+$m)/$parcelas;
	return round($valParcela, 2);
}

//carregamento de parcelas
add_action( 'wp_ajax_parcelas_cielo_webservice', 'parcelas_cielo_webservice');
add_action( 'wp_ajax_nopriv_parcelas_cielo_webservice','parcelas_cielo_webservice');
function parcelas_cielo_webservice(){
    //carrega as config do modulo
	$tp = isset($_POST['tipo'])?$_POST['tipo']:'';
	if($tp=='debito'){
		$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice_Debito();
		$chave = trim($config->afiliacao);
	}else{
		$config = new WC_Gateway_Loja5_Woo_Cielo_Webservice();
		$chave = trim($config->afiliacao);
	}
    //envia o juros incluso
    $enviar_juros_embutido = true;
    //valida o valor esta correto
    if(isset($_POST['id']) && isset($_POST['total']) && sha1(md5($_POST['total']))==$_POST['hash']){
        //pega os dados e vars
        $minimo = (float)$config->minimo;
        $desconto = 0;
        $divmax = $config->div;
        $divsem = $config->sem;
        $juros  = $config->juros;
        $total  = (float)$_POST['total'];
		
		//moeda 
		$currency_code = isset($_POST['moeda'])?$_POST['moeda']:get_woocommerce_currency();
		$currency_symbol = get_woocommerce_currency_symbol( $currency_code );

        //corrije bug erro etapa2
        $total = $total_limpo = number_format($total, 2, '.', '');

        //calcula os minimos
        $split = (int)$total/$minimo;
        if($split>=$divmax){
			$div = (int)$divmax;
        }elseif($split<$divmax){
			$div = (int)$split;
        }elseif($total<=$minimo){
			$div = 1;
        }
		
		//se 1x 
		if($_POST['id']=='visaelectron' || $_POST['id']=='maestro' || $_POST['id']=='elodebito' || $_POST['id']=='discover' || $_POST['id']=='jcb'){
			$div = 1;
		}
		
		//partes juros 
		$partes_juros = explode('|',$juros);
		if(count($partes_juros)==1){
			//juros por parcela 
			$juros_p = array();
			$juros_p[2] = (float)$juros;
			$juros_p[3] = (float)$juros;
			$juros_p[4] = (float)$juros;
			$juros_p[5] = (float)$juros;
			$juros_p[6] = (float)$juros;
			$juros_p[7] = (float)$juros;
			$juros_p[8] = (float)$juros;
			$juros_p[9] = (float)$juros;
			$juros_p[10] = (float)$juros;
			$juros_p[11] = (float)$juros;
			$juros_p[12] = (float)$juros;
		}else{
			//juros por parcela 
			$juros_p = array();
			$juros_p[2] = (float)isset($partes_juros[1])?$partes_juros[1]:0;
			$juros_p[3] = (float)isset($partes_juros[2])?$partes_juros[2]:0;
			$juros_p[4] = (float)isset($partes_juros[3])?$partes_juros[3]:0;
			$juros_p[5] = (float)isset($partes_juros[4])?$partes_juros[4]:0;
			$juros_p[6] = (float)isset($partes_juros[5])?$partes_juros[5]:0;
			$juros_p[7] = (float)isset($partes_juros[6])?$partes_juros[6]:0;
			$juros_p[8] = (float)isset($partes_juros[7])?$partes_juros[7]:0;
			$juros_p[9] = (float)isset($partes_juros[8])?$partes_juros[8]:0;
			$juros_p[10] = (float)isset($partes_juros[9])?$partes_juros[9]:0;
			$juros_p[11] = (float)isset($partes_juros[10])?$partes_juros[10]:0;
			$juros_p[12] = (float)isset($partes_juros[11])?$partes_juros[11]:0;
		}

        //inicio
		if($div > 1){
			$linhas[''] = "-- Selecione a Parcela --";
		}

        //seleta o tipo de parcelamento
        if($config->parcelamento=='operadora'){
			$pcom = 3;
        }else{
			$pcom = 2;
        }

        //avista
        if($desconto > 0){
			$desconto_valor = ($total/100)*$desconto;
			$avista = number_format(($total-$desconto_valor), 2, '.', '');
			$linhas[base64_encode('1|1|'.$avista.'|'.base64_encode($_POST['id']).'|'.base64_encode($total).'|'.md5($avista.$chave))] = "&Agrave; vista por ".formatar_preco_cielo_webservice(number_format($avista, 2, '.', ''))." (j&aacute; com ".$desconto."% off)";
        }else{
			$linhas[base64_encode('1|1|'.number_format(($total), 2, '.', '').'|'.base64_encode($_POST['id']).'|'.base64_encode($total).'|'.md5($total.$chave))] = "&Agrave; vista por ".formatar_preco_cielo_webservice(number_format(($total), 2, '.', ''))."";
        }

        //se tiver parcelado
        if($_POST['id']!='visaelectron' && $_POST['id']!='maestro' && $_POST['id']!='elodebito' && $_POST['id']!='discover' && $_POST['id']!='jcb'){
            if($div>=2 && $currency_code=='BRL'){
                for($i=1;$i<=$div;$i++){
                    if($i>1){
                        if($i<=$divsem){
							//total
                            $totalf = number_format($total, 2, '.', '');
							//frase 
							$frase = " sem juros (".formatar_preco_cielo_webservice(number_format($totalf, 2, '.', '')).")";
							//linha
                            $linhas[base64_encode(''.$i.'|2|'.number_format(($totalf), 2, '.', '').'|'.base64_encode($_POST['id']).'|'.base64_encode($totalf).'|'.md5($totalf.$chave))] = $i."x de ".formatar_preco_cielo_webservice(number_format(($totalf/$i), 2, '.', ''))."".$frase."";
                        }else{
							//parcelas com juros
							$juros_par = isset($juros_p[$i])?$juros_p[$i]:$juros;
							if($config->tipo_juros=='composto'){
								$parcela_com_juros = calcular_juros_cielo_webservice_composto($total_limpo, $juros_par, $i);
							}elseif($config->tipo_juros=='simples'){
								$parcela_com_juros = calcular_juros_cielo_webservice_simples($total_limpo, $juros_par, $i);
							}elseif($config->tipo_juros=='porcentagem'){
								$parcela_com_juros = calcular_juros_cielo_webservice_porcentagem($total_limpo, $juros_par, $i);
							}else{
								$parcela_com_juros = calcular_juros_cielo_webservice_price($total_limpo, $juros_par, $i);
							}
                            //juros imbutido
                            if($enviar_juros_embutido){
                                $totalf = number_format(($parcela_com_juros*$i), 2, '.', '');
                            }
							//frase
							$frase = " com juros (".formatar_preco_cielo_webservice(number_format(($parcela_com_juros*$i), 2, '.', '')).")";
							//linha
                            $linhas[base64_encode(''.$i.'|'.$pcom.'|'.number_format(($totalf), 2, '.', '').'|'.base64_encode($_POST['id']).'|'.base64_encode($totalf).'|'.md5($totalf.$chave))] = $i."x de ".formatar_preco_cielo_webservice(number_format(($parcela_com_juros), 2, '.', ''))."".$frase."";
                        }
                    }
                }
            }
        }
        //converte json
        echo json_encode($linhas);
    }else{
        $linhas[''] = 'Ops, total invalido!';
        echo json_encode($linhas);
    }
    die();
}

function formatar_preco_cielo_webservice($valor){
	//moeda 
	$currency_code = isset($_POST['moeda'])?$_POST['moeda']:get_woocommerce_currency();
	$currency_symbol = get_woocommerce_currency_symbol( $currency_code );
	return $currency_symbol.''.number_format($valor, 2, '.', '');
}

function get_ip_cielo_webservice() {
    $variables = array('REMOTE_ADDR',
                       'HTTP_X_FORWARDED_FOR',
                       'HTTP_X_FORWARDED',
                       'HTTP_FORWARDED_FOR',
                       'HTTP_FORWARDED',
                       'HTTP_X_COMING_FROM',
                       'HTTP_COMING_FROM',
                       'HTTP_CLIENT_IP');

    $return = '127.0.0.1';
    foreach ($variables as $variable)
    {
        if (isset($_SERVER[$variable]))
        {
            $return = $_SERVER[$variable];
            break;
        }
    }
    return $return;
}
?>