<?php
    class WC_Gateway_Loja5_Woo_Cielo_Webservice_Debito extends WC_Payment_Gateway {
        
        public function __construct() {
            global $woocommerce;
            $this->id           = 'loja5_woo_cielo_webservice_debito';
            $this->icon         = apply_filters( 'woocommerce_loja5_woo_cielo_webservice_debito', plugins_url().'/loja5-woo-cielo-webservice/images/cielo.png' );
            $this->has_fields   = false;
            $this->supports   = array('products','refunds');
            $this->description = true;
			$this->method_description = __( 'Ativa o pagamento por Cartão de Débito via Cielo.', 'loja5-woo-cielo-webservice-boleto' );
            $this->method_title = 'Cielo API 3.0 - Débito';
            $this->init_settings();
            $this->init_form_fields();
			$this->instalar_mysql_cielo_webservice();
            
            foreach ( $this->settings as $key => $val ) $this->$key = $val;
            
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
            
            if ( !$this->is_valid_for_use() ) $this->enabled = false;
		}
		
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			global $wpdb;
			//dados
			$data = (array)$wpdb->get_row("SELECT * FROM `wp_cielo_api_loja5` WHERE `pedido` = '".(int)($order_id)."' ORDER BY id DESC;");
			if(!isset($data['metodo'])){
				return new WP_Error( 'broke', __( "Não foi encontrado nenhum registro para este pedido.", $this->id ) );
			}elseif($data['status']!=2){
				return new WP_Error( 'broke', __( "Somente é possivel estornar parcialmente pedidos aprovados/capturados.", $this->id ) );
			}elseif(is_null($amount) || $amount==0){
				return new WP_Error( 'broke', __( "Informe um valor maior que zero e menor que o total do pedido para estornar.", $this->id ) );
			}elseif($amount > $data['total']){
				return new WP_Error( 'broke', __( "Somente é possivel estornar o valor menor ou igual ao total do pedido.", $this->id ) );
			}
			//tudo ok então estorna
			$total_bd = $data['total'];
			$total_estornar = number_format($amount, 2, '.', '');
			//pedido 
			$order = wc_get_order($order_id);
			//ambiente
			if($this->testmode=='yes'){
				$provider = 'Simulado';
				$urlweb = "https://apisandbox.cieloecommerce.cielo.com.br/1/";
			}else{
				$provider = 'Cielo';
				$urlweb = "https://api.cieloecommerce.cielo.com.br/1/";
			}
			//rest
			if(version_compare(PHP_VERSION, '5.6.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/restclient.php' );
			}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/restclient.php' );
			}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/restclient.php' );
			}else{
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/restclient.php' );
			}
			$headers = array(
				"Content-Type" => "application/json",
				"Accept" => "application/json",
				"MerchantId" =>trim($this->afiliacao),
				"MerchantKey" => trim($this->chave),
				"RequestId" => "",
			);
			$api = new RestClientCielo(array(
				'base_url' => $urlweb, 
				'headers' => $headers, 
			));
			//faz o estorno parcial
			$response = $api->put("sales/".$data['id_pagamento']."/void?amount=".number_format($total_estornar, 2, '', '')."");
			//debug
			if ( 'yes' === $this->debug ) {
				$logs = new WC_Logger();
				$logs->add( 'loja5_woo_cielo_cancelamentos', 'Log Cancelamento Parcial Cielo: '.$response->response );
			}
			if(isset($response->status)){
				$dados_pedido = json_decode($response->response,true);
				if(isset($dados_pedido['Status'])){
					//restore
					if($dados_pedido['Status']==10 || $dados_pedido['Status']==11){
						wc_increase_stock_levels($order->get_id());
					}
					//estorno ok
					if($dados_pedido['Status']==10 || $dados_pedido['Status']==11){
						$order->update_status($this->devolvido,'Pagamento estornado total por o ADMIN em '.date('d/m/Y H:i:s').' ('.$total_estornar.' de '.$total_bd.')');
						$wpdb->query("UPDATE `wp_cielo_api_loja5` SET `total` =  '".($total_bd-$total_estornar)."', `status` =  '".$dados_pedido['Status']."' WHERE `pedido` = '".(int)( $order_id)."';");

						return true;
					}elseif($dados_pedido['Status']==2){
						$order->add_order_note('Pagamento estornado parcialmente por o ADMIN em '.date('d/m/Y H:i:s').' ('.$total_estornar.' de '.$total_bd.')');
						$wpdb->query("UPDATE `wp_cielo_api_loja5` SET `total` =  '".($total_bd-$total_estornar)."', `status` =  '".$dados_pedido['Status']."' WHERE `pedido` = '".(int)( $order_id)."';");

						return true;
					}else{
						//log 
						$logs = new WC_Logger();
						$logs->add( 'loja5_woo_cielo_cancelamentos', 'Log Cancelamento Parcial Cielo: '.$response->response );
						//retorna erro
						return new WP_Error( 'broke', __( "Status desconhecido ao realizar estorno junto a Cielo (ver logs).", $this->id ) );
					}
				}else{
					//log 
					$logs = new WC_Logger();
					$logs->add( 'loja5_woo_cielo_cancelamentos', 'Log Cancelamento Parcial Cielo: '.$response->response );
					//retorna erro
					return new WP_Error( 'broke', __( "Problema desconhecido ao realizar estorno junto a Cielo (ver logs).", $this->id ) );
				}
			}
		}
        
        public function thankyou_page( $order_id ) {
            global $wpdb;
            //pega o pedido
            $order = wc_get_order((int)($order_id));
			$total_pedido = $order->get_total();

            //dados cielo mysql
            $cielo = (array)$wpdb->get_row("SELECT * FROM `wp_cielo_api_loja5` WHERE `pedido` = '".(int)($order_id)."' ORDER BY id DESC;");

			//define o status do pedido
			$status_cielo = isset($cielo['status'])?$cielo['status']:'0';
			switch($status_cielo){
				case '2':
					$status = '<span style="color: #20bb20;">Aprovada</span>';
				break;
				case '1':
					$status = '<span style="color: #2196f3;">Autorizada</span>';
				break;
				case '3':
					$status = '<span style="color: red;">Negada</span>';
				break;
				case '10':
				case '13':
					$status = '<span style="color: red;">Cancelada</span>';
				break;
				default:
					$status = 'Aguardando Pagamento';
			}
            
            //layout
            include_once(dirname(__FILE__) . '/cupom_cartao.php'); 
        }
	
        public function is_valid_for_use() {
            if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_loja5_woo_cielo_webservice_debito_supported_currencies', array( 'BRL' ) ) ) ) return false;
            return true;
        }
		
		public function instalar_mysql_cielo_webservice(){
            global $wpdb;
            $wpdb->query("CREATE TABLE IF NOT EXISTS `wp_cielo_api_loja5` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
			`metodo` varchar(40) NOT NULL,
			`id_pagamento` varchar(40) NOT NULL,
            `tid` varchar(40) NOT NULL,
            `pedido` varchar(40) NOT NULL,
            `bandeira` varchar(40) NOT NULL,
            `parcela` varchar(40) NOT NULL,
            `lr` varchar(20) NOT NULL,
			`lr_log` varchar(180) NOT NULL,
            `total` float(10,2) NOT NULL,
            `status` varchar(40) NOT NULL,
            `bin` varchar(40) NOT NULL,
			`link` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
        }
	
        public function get_status_pagamento(){
            if(function_exists('wc_get_order_statuses')){
                return wc_get_order_statuses();
            }else{
                $taxonomies = array( 
                    'shop_order_status',
                );
                $args = array(
                    'orderby'       => 'name', 
                    'order'         => 'ASC',
                    'hide_empty'    => false, 
                    'exclude'       => array(), 
                    'exclude_tree'  => array(), 
                    'include'       => array(),
                    'number'        => '', 
                    'fields'        => 'all', 
                    'slug'          => '', 
                    'parent'         => '',
                    'hierarchical'  => true, 
                    'child_of'      => 0, 
                    'get'           => '', 
                    'name__like'    => '',
                    'pad_counts'    => false, 
                    'offset'        => '', 
                    'search'        => '', 
                    'cache_domain'  => 'core'
                ); 
                foreach(get_terms( $taxonomies, $args ) AS $status){
                    $s[$status->slug] = __( $status->slug, 'woocommerce' );
                }
                return $s;
            }
        }
	
        public function admin_options() {
            ?>
            <?php if ( $this->is_valid_for_use() ) : ?>
                <table class="form-table">
                <?php
                    $this->generate_settings_html();
                ?>
                </table>
            <?php else : ?>
                <div class="inline error"><p><strong><?php _e( 'Gateway Desativado', 'woocommerce' ); ?></strong>: <?php _e( 'Cielo Webservice n&atilde;o aceita o tipo e moeda de sua loja, apenas BRL.', 'woocommerce' ); ?></p></div>
            <?php
                endif;
        }

        public function init_form_fields() {
            //especifico por versao
			$config = null;
			if(version_compare(PHP_VERSION, '5.6.0', '<')){
				include(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/config_debito.php' );
			}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
				include(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/config_debito.php' );
			}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
				include(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/config_debito.php' );
			}else{
				include(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/config_debito.php' );
			}			
			$this->form_fields = $config;
        }
		
		public function auth2(){
			//ambiente
			if($this->settings['testmode']=='yes'){
				$urlweb = "https://mpisandbox.braspag.com.br/v2/auth/token";
			}else{
				$urlweb = "https://mpi.braspag.com.br/v2/auth/token";
			}
			//header cielo 
			$basic = base64_encode(trim($this->settings['client_id']).":".trim($this->settings['client_secret']));
			$headers = array(
				"Content-Type" => "application/json",
				"Accept" => "application/json",
				"Authorization" => "Basic ".$basic."",
			);
			//rest
			if(version_compare(PHP_VERSION, '5.6.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/restclient.php' );
			}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/restclient.php' );
			}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/restclient.php' );
			}else{
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/restclient.php' );
			}
			$api = new RestClientCielo(array(
				'headers' => $headers, 
			));
			$dados = array(
				'EstablishmentCode' => trim($this->settings['cod_estabelecimento']),
				'MerchantName' => trim($this->settings['nome_estabelecimento']),
				'MCC' => trim($this->settings['mcc_estabelecimento']),
			);
			$response = $api->post($urlweb,json_encode($dados));
			return @json_decode($response->response,true);
		}
	
		public function getUserIpAddr(){
			if(!empty($_SERVER['HTTP_CLIENT_IP'])){
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}else{
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			if($ip=='::1'){
				$ip = '127.0.0.1';
			}
			return $ip;
		}
		
        public function payment_fields() {
			global $woocommerce;
			//valores
			$ip = $this->getUserIpAddr();
            if(!isset($_GET['pay_for_order'])){
				$total_cart = number_format($this->get_order_total(), 2, '.', '');
				$hash = 'C'.WC()->cart->get_cart_hash();
				$cliente = WC()->cart->get_customer();
				$currency_code = get_woocommerce_currency();
			}else{
				$order_id = wc_get_order_id_by_order_key($_GET['key']);
				$order = wc_get_order( $order_id );
				$total_cart = number_format($order->get_total(), 2, '.', '');
				$hash = 'O'.$order->get_order_key();
				$cliente = $order;
				$currency_code = $order->get_currency();
			}
			//moeda
			$currency_symbol = get_woocommerce_currency_symbol( $currency_code );
			//form
			$config = $this->settings;
			if($config['3ds']=='yes'){
				add_filter( 'woocommerce_order_button_html', array($this,'loja5_woo_debito_3ds_button_html') );
				$auth = $this->auth2();
				if(isset($auth[0]['Message'])){
					echo $auth[0]['Message'];
				}elseif(isset($auth['access_token'])){
					include(dirname(__FILE__) . '/layout_debito2.php'); 
				}else{
					echo 'Erro desconhecido ao gerar token 3DS Cielo!';
				}
			}else{
				include(dirname(__FILE__) . '/layout_debito.php'); 
			}
		}
		
		public function loja5_woo_debito_3ds_button_html( $button_html ) {
			$config = $this->settings;
			$chosen_payment_method = WC()->session->get('chosen_payment_method');
			$payment_method = isset($_REQUEST['payment_method'])?$_REQUEST['payment_method']:$chosen_payment_method;
			if($payment_method==$this->id && $config['3ds']=='yes'){
				$order_button_text = __('Place order', 'woocommerce');
				$button_html = '<button type="button" onclick="autenticar_debito()" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>';
			}
			return $button_html;
		}
	
        public function validate_fields() {
            global $woocommerce;
            if($_POST['payment_method']=='loja5_woo_cielo_webservice_debito'){
				//valida a autenticacao
				if($this->settings['3ds']=='yes' && $this->settings['forcar_autenticar']=='no' && $this->get_post('auth')!='onSuccess'){
					$this->tratar_erro("Ops, n&atilde;o foi poss&iacute;vel autenticar seu cart&atilde;o junto ao emissor (".trim($this->get_post('auth'))."), tente outro ou escolha outra forma de pagamento!");
				}
				//valida os dados
                $erros = 0;
                if($this->get_post('titular')==''){
					$this->tratar_erro("Informe o nome do titular!");
					$erros++;
                }
                if($this->get_post('numero')==''){
					$this->tratar_erro("Informe o n&uacute;mero do cart&atilde;o!");
					$erros++;
                }
                if($this->get_post('validade_mes')==''){
					$this->tratar_erro("Selecione o m&ecirc;s de validade do cart&atilde;o!");
					$erros++;
                }
				if($this->get_post('validade_ano')==''){
					$this->tratar_erro("Selecione o ano de validade do cart&atilde;o!");
					$erros++;
                }
                if($this->get_post('cvv')==''){
					$this->tratar_erro("Informe o CVV do cart&atilde;o!");
					$erros++;
                }
                if($this->get_post('parcela')==''){
					$this->tratar_erro("Selecione o valor a pagar!");
					$erros++;
                }
                if($erros>0){
                    return false;
                }
            }
            return false;
        }
        
        private function get_post( $name ) {
                if (isset($_POST['cielo_webservice_debito'][$name])) {
                    return $_POST['cielo_webservice_debito'][$name];
                }
                return null;
        }
        
        public function tratar_erro($erro){
            global $woocommerce;
            if(function_exists('wc_add_notice')){
				wc_add_notice($erro,$notice_type = 'error' );
            }else{
				$woocommerce->add_error($erro);
            }
        }
        
        public function process_payment($order_id) {
            global $woocommerce,$wpdb;
			$order = wc_get_order( $order_id );
			
			//moeda
			$currency_code = $order->get_currency();
			$currency_symbol = get_woocommerce_currency_symbol( $currency_code );

			//keys 
			$afiliacao = trim($this->settings['afiliacao']);
			$chave = trim($this->settings['chave']);

            //cartao
            $nome_completo = $this->get_post('titular');
			$hash = $this->get_post('hash');
            $fiscal = preg_replace('/\D/', '', $this->get_post('fiscal'));
            $numero_cartao = preg_replace('/\D/', '', $this->get_post('numero'));
			$mes_cartao = $this->get_post('validade_mes');
			$ano_cartao = $this->get_post('validade_ano');
            $cod_cartao = preg_replace('/\D/', '',$this->get_post('cvv'));
            
            //trata a parcela
            $dados = explode('|',base64_decode($this->get_post('parcela')));
			if(!isset($dados[5])){
				$this->tratar_erro("Ops, problema ao enviar dados de parcelas, tente novamente!");
                return false;
			}
            $parcela = $dados[0];
            $tipo_parcela = $dados[1];
            $total = number_format($dados[2],2,'.','');
            $bandeira = ucfirst(base64_decode($dados[3]));
			if(md5($total.$afiliacao)!=$dados[5]){
				$this->tratar_erro("Ops, valores de parcela divergente do real, tente novamente!");
                return false;
			}
			
			//ambiente
			if($this->settings['testmode']=='yes'){
				$provider = 'Simulado';
				$urlweb = "https://apisandbox.cieloecommerce.cielo.com.br/1/";
			}else{
				$provider = 'Cielo';
				$urlweb = "https://api.cieloecommerce.cielo.com.br/1/";
			}

			//corrige problema da bandeira master
			if($bandeira=='Mastercard' || $bandeira=='mastercard' || $bandeira=='Maestro'){
				$bandeira = 'Master';
			}elseif($bandeira=='Visaelectron'){
				$bandeira = 'Visa';
			}elseif($bandeira=='Elodebito'){
				$bandeira = 'Elo';
			}

			//header cielo 
			$headers = array(
				"Content-Type" => "application/json",
				"Accept" => "application/json",
				"MerchantId" =>$afiliacao,
				"MerchantKey" =>$chave,
				"RequestId" => "",
			);

			//dados
			$dados = array();
			$dados['MerchantOrderId'] = $order->get_id();
			$dados['Customer'] = array(
				'Name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			);
			if($this->settings['3ds']=='no'){
				$dados['Payment'] = array(
					'Type' => 'DebitCard',
					'Amount' => number_format($total, 2, '', ''),
					'Authenticate' => 'true',
					'ReturnUrl' => admin_url('admin-ajax.php').'?tipo=debito&action=retorno_debito_cielo_webservice',				
					'DebitCard' => array(  
						"CardNumber" => $numero_cartao,
						"Holder" => $nome_completo,
						"ExpirationDate" => $mes_cartao.'/'.$ano_cartao,
						"SecurityCode" => $cod_cartao,
						"SaveCard" => "false",
						"Brand" => ucfirst($bandeira)
					)
				);
			}else{
				$dados['Payment'] = array(
					'Type' => 'DebitCard',
					'Amount' => number_format($total, 2, '', ''),
					'Authenticate' => 'true',
					'ReturnUrl' => admin_url('admin-ajax.php').'?tipo=debito&action=retorno_debito_cielo_webservice',				
					'DebitCard' => array(  
						"CardNumber" => $numero_cartao,
						"Holder" => $nome_completo,
						"ExpirationDate" => $mes_cartao.'/'.$ano_cartao,
						"SecurityCode" => $cod_cartao,
						"SaveCard" => "false",
						"Brand" => ucfirst($bandeira)
					),
					'ExternalAuthentication' => array(
						'Cavv' => $this->get_post('cavv'),
						'Xid' => $this->get_post('xid'),
						'Eci' => $this->get_post('eci'),
						'Version' => $this->get_post('version'),
						'ReferenceID' => $this->get_post('referenceid'),
					)
				);
			}
			
			//rest
			if(version_compare(PHP_VERSION, '5.6.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/restclient.php' );
			}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/restclient.php' );
			}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/restclient.php' );
			}else{
				include_once(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/restclient.php' );
			}

			$api = new RestClientCielo(array(
				'base_url' => $urlweb, 
				'headers' => $headers, 
			));
			$response = $api->post("sales",json_encode($dados));
			$dados_pedido = @json_decode($response->response,true);
			
			//bin
			$bin = substr($numero_cartao,0,6);
			$bin .= '****';
			$bin .= substr($numero_cartao,-4);
			
			//debug se ativo ou erro de transação
			if ( 'yes' === $this->settings['debug'] || $response->status==401 || $response->status==500 || $response->status==400 || $response->status==404  || $response->status==0 ) {
				$logs = new WC_Logger();
				$dados = $this->findandReplace($dados,$bin);	
				$logs->add( $this->id, 'Debug Cielo HTTP: '.$response->status );
				$logs->add( $this->id, 'Debug Cielo Enviados: '.print_r($dados,true) );
				$logs->add( $this->id, 'Debug Cielo Recebido: '.print_r($response,true) );
			}

			//se credenciais invalidas 
			if($response->status==401){
				$this->tratar_erro("HTTP ".$response->status." - Cred&ecirc;nciais de integra&ccedil;&atilde;o Cielo inv&aacute;lidas ou n&atilde;o corresponde o ambiente configurado, revise-as!");
				return false;
			}elseif($response->status==400 || $response->status==404){
				$this->tratar_erro("HTTP ".$response->status." - Problema de processamento dos dados junto Cielo, verifique os logs de sua loja para mais detalhes!");
				return false;
			}elseif($response->status==500){
				$this->tratar_erro("HTTP ".$response->status." - Problema de processamento dos dados junto Cielo, erro interno, verifique os logs de sua loja para mais detalhes!");
				return false;
			}elseif($response->status==200 || $response->status==201){
				//se erros 
				if(isset($dados_pedido['Code'])){
					$this->tratar_erro("".$dados_pedido['Code']." - Problema de processamento dos dados junto Cielo: ".$dados_pedido['Message']."");
					return false;
				}elseif(isset($dados_pedido[0]['Code'])){
					$this->tratar_erro("".$dados_pedido[0]['Code']." - Problema de processamento dos dados junto Cielo: ".$dados_pedido[0]['Message']."");
					return false;
				}elseif(isset($dados_pedido['Payment']['Tid'])){
					//cria meta com a resposta
					update_post_meta($order_id,'_dados_cielo_api',$dados_pedido);
					update_post_meta($order_id,'_processado_cielo_loja5','true');

					//cria o pedido para enviar o e-mail 
					if($dados_pedido['Payment']['Status']==2 || $dados_pedido['Payment']['Status']==1){
						$order->update_status('wc-on-hold');
					}
					
					//cria no banco de dados 
					$wpdb->query("INSERT INTO `wp_cielo_api_loja5` (`id`, `metodo`, `id_pagamento`, `tid`, `pedido`, `bandeira`, `parcela`, `lr`, `lr_log`, `total`, `status`, `bin`, `link`) VALUES (NULL, 'debito', '".$dados_pedido['Payment']['PaymentId']."', '".$dados_pedido['Payment']['Tid']."', '".$order->get_id()."', '".ucfirst($bandeira)."', '".$parcela."', '".(isset($dados_pedido['Payment']['ReturnCode'])?$dados_pedido['Payment']['ReturnCode']:'')."', '".(isset($dados_pedido['Payment']['ReturnMessage'])?$dados_pedido['Payment']['ReturnMessage']:'')."', '".$total."', '".$dados_pedido['Payment']['Status']."', '".$bin."', '');");
					
					//cria uma nota no pedido
					$log = "Transa&ccedil;&atilde;o D&eacute;bito Cielo - TID ".$dados_pedido['Payment']['Tid']." no ".ucfirst($bandeira)." (".$bin.")";
					if(isset($dados_pedido['Payment']['ProofOfSale'])){
						$log .= ' / NSU: '.$dados_pedido['Payment']['ProofOfSale'].'';
					}
					if(isset($dados_pedido['Payment']['AuthorizationCode'])){
						$log .= ' / Auth: '.$dados_pedido['Payment']['AuthorizationCode'].'';
					}
					if(!empty($this->get_post('auth')) && $this->settings['3ds']=='yes' && ($this->get_post('bandeira')=='visa' || $this->get_post('bandeira')=='mastercard' || $this->get_post('bandeira')=='elo')){
						$log .= ' / Autenticação Débito: '.$this->get_post('auth').'';
					}
					$order->add_order_note($log);
					
					//status
					switch($dados_pedido['Payment']['Status']){
						case '2':
							$order->update_status($this->pago);
						break;
						case '1':
							$order->update_status($this->autorizado);
						break;
						case '3':
							$order->update_status($this->negado);
						break;
						case '10':
						case '13':
							$order->update_status($this->cancelado);
						break;
					}		 

					//limpa o carrinho
					WC()->cart->empty_cart();
						
					//se precisar autenticar
					if(isset($dados_pedido['Payment']['AuthenticationUrl']) && $dados_pedido['Payment']['Status']==0){
						$urlAutenticacaoLink = $dados_pedido['Payment']['AuthenticationUrl'];
					}else{
						$urlAutenticacaoLink = $this->get_return_url( $order );
					}
					
					//reduz um estoque se aprovado ou autorizado
					if(CIELO_WEBSERVICE_WOO_BAIXA_STOCK && ($dados_pedido['Payment']['Status']==1 || $dados_pedido['Payment']['Status']==2)){
						wc_reduce_stock_levels( $order->get_id() );
					}
					
					return array(
						'result' 	=> 'success',
						'redirect'	=>  $urlAutenticacaoLink
					);
				}else{
					$this->tratar_erro("".$response->status." - Problema de processamento dos dados junto Cielo desconhecido, tente novamente e se persistir contate o suporte da loja.");
					return false;
				}
			}else{
				$this->tratar_erro("HTTP ".$response->status." - Problema de processamento dos dados junto Cielo desconhecido, tente novamente e se persistir contate o suporte da loja.");
				return false;
			}
        }
            
        public function obj2array($obj){
            return json_decode(json_encode($obj),true);
        }
        
        public function json2array($obj){
            return json_decode($obj,true);
        }
        
        public function restore_order_stock($order_id) {
			$order = wc_get_order( $order_id );
			if ( ! get_option('woocommerce_manage_stock') == 'yes' && ! sizeof( $order->get_items() ) > 0 ) {
				wc_increase_stock_levels($order_id);
				return;
			}
		}
		
		public function findandReplace(&$array,$bin) {
			foreach($array as $key => &$value)
			{ 
				if(is_array($value))
				{ 
					$this->findandReplace($value,$bin); 
				} else{
					if ($key == 'CardNumber') {
						$array['CardNumber'] = $bin;
						break;
					}
				} 
			}
			return $array;
		}
    }    
?>