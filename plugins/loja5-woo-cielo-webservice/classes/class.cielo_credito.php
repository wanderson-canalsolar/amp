<?php   
	class WC_Gateway_Loja5_Woo_Cielo_Webservice extends WC_Payment_Gateway {
	
        public function __construct() {
            global $woocommerce;
            $this->id           = 'loja5_woo_cielo_webservice';
            $this->icon         = apply_filters( 'woocommerce_loja5_woo_cielo_webservice', plugins_url().'/loja5-woo-cielo-webservice/images/cielo.png' );
            $this->has_fields   = false;
            $this->supports   = array('products','refunds');
            $this->description = true;
			$this->method_description = __( 'Ativa o pagamento por Cartão de Crédito via Cielo.', 'loja5-woo-cielo-webservice-boleto' );
            $this->method_title = 'Cielo API 3.0 - Cr&eacute;dito';
            $this->init_settings();
            $this->init_form_fields();
            $this->instalar_mysql_cielo_webservice();
            
            foreach ( $this->settings as $key => $val ) $this->$key = $val;
            
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
			add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 2 );
            
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
		
		public function email_instructions( $order, $sent_to_admin ) {
			global $wpdb;
			if ( $sent_to_admin || $this->id !== $order->get_payment_method() ) {
				return;
			}
			//dados
			$data = (array)$wpdb->get_row("SELECT * FROM `wp_cielo_api_loja5` WHERE `pedido` = '".(int)($order->get_id())."' ORDER BY id DESC;");
			$total_pedido = $order->get_total();
			//email
			if(isset($data['tid']) && !empty($data['tid'])){	
				$html = '<h2>' . __( 'Detalhes do Pagamento', 'loja5-woo-cielo-webservice' ) . '</h2>';
				$html .= '<p class="order_details">';
				$html .= '' . sprintf( __( '<b>TID:</b> %s', 'loja5-woo-cielo-webservice' ), $data['tid'] ) . '<br />';
				$html .= '' . sprintf( __( '<b>BIN:</b> %s', 'loja5-woo-cielo-webservice' ), $data['bin'] ) . '<br />';
				if(($data['total']-$total_pedido) > 0.10){
					$html .= '<b>Juros:</b> R$ '.number_format(($data['total']-$total_pedido),'2','.','').'<br />';
				}
				$html .= '<b>Total a Pagar:</b> R$ '.number_format($data['total'],'2','.','').'<br />';
				$html .= '' . sprintf( __( '<b>Bandeira:</b> %s', 'loja5-woo-cielo-webservice' ), ucfirst($data['bandeira']) ) . ' em '.$data['parcela'].'x de R$ '.number_format(($data['total']/$data['parcela']),'2','.','').'<br />';
				$html .= '</p>';
				echo $html;
			}else{
				return;
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
            if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_loja5_woo_cielo_webservice_supported_currencies', array( 'BRL' ) ) ) ) return false;
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
                <div class="inline error"><p><strong><?php _e( 'Gateway Desativado', 'woocommerce' ); ?></strong>: <?php _e( 'Cielo API 3.0 n&atilde;o aceita o tipo e moeda de sua loja, apenas BRL (real).', 'woocommerce' ); ?></p></div>
            <?php
                endif;
        }
        
        public function gerar_parcelas(){
            $parcelas = array();
            for($i=1;$i<=12;$i++){
                $parcelas[$i] = $i."x";
            }
            return $parcelas;
        }
        
        public function init_form_fields() {
            //especifico por versao
			if(version_compare(PHP_VERSION, '5.6.0', '<')){
				include(CIELO_WEBSERVICE_WOO_PATH.'/include/php55/config_credito.php' );
			}elseif(version_compare(PHP_VERSION, '7.1.0', '<')){
				include(CIELO_WEBSERVICE_WOO_PATH.'/include/php56/config_credito.php' );
			}elseif(version_compare(PHP_VERSION, '7.2.0', '<')){
				include(CIELO_WEBSERVICE_WOO_PATH.'/include/php71/config_credito.php' );
			}else{
				include(CIELO_WEBSERVICE_WOO_PATH.'/include/php72/config_credito.php' );
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
			//anti fraude 
			$anti_fraude = ($this->settings['fraude']=='yes')?true:false;
			$finger      = ($this->settings['finger']=='yes')?true:false;
			$merchant_id = trim($this->settings['afiliacao']);
			$hash = md5($this->get_user_login());
			if($this->settings['testmode']=='yes'){
				$oggid = "1snn5n9w";
			}else{
				$oggid = "k8vif92e";
			}
			//form
			$config = $this->settings;
			if($config['3ds']=='yes' && CIELO_WEBSERVICE_WOO_CREDITO_3DS){
				add_filter( 'woocommerce_order_button_html', array($this,'loja5_woo_debito_3ds_button_html') );
				$auth = $this->auth2();
				if(isset($auth[0]['Message'])){
					echo $auth[0]['Message'];
				}elseif(isset($auth['access_token'])){
					include(dirname(__FILE__) . '/layout_credito2.php'); 
				}else{
					echo 'Erro desconhecido ao gerar token 3DS Cielo!';
				}
			}else{
				include(dirname(__FILE__) . '/layout_credito.php'); 
			}
		}
		
		public function loja5_woo_debito_3ds_button_html( $button_html ) {
			$config = $this->settings;
			$chosen_payment_method = WC()->session->get('chosen_payment_method');
			$payment_method = isset($_REQUEST['payment_method'])?$_REQUEST['payment_method']:$chosen_payment_method;
			if($payment_method==$this->id && $config['3ds']=='yes' && CIELO_WEBSERVICE_WOO_CREDITO_3DS){
				$order_button_text = __('Place order', 'woocommerce');
				$button_html = '<button type="button" onclick="autenticar_credito()" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>';
			}
			return $button_html;
		}
        
        public function validate_fields() {
            global $woocommerce;
			//moeda
			$currency_code = get_woocommerce_currency();
			$currency_symbol = get_woocommerce_currency_symbol( $currency_code );
			
			//valida os dados de cartão de crédito
            if(isset($_POST['payment_method']) && $_POST['payment_method']=='loja5_woo_cielo_webservice'){
				//valida a autenticacao
				if($this->get_post('bandeira')=='visa' || $this->get_post('bandeira')=='mastercard' || $this->get_post('bandeira')=='elo'){
					if($this->settings['3ds']=='yes' && CIELO_WEBSERVICE_WOO_CREDITO_3DS && $this->settings['forcar_autenticar']=='no' && $this->get_post('auth')!='onSuccess'){
						$this->tratar_erro("Ops, n&atilde;o foi poss&iacute;vel autenticar seu cart&atilde;o junto ao emissor (".trim($this->get_post('auth'))."), tente outro ou escolha outra forma de pagamento!");
					}
				}
				//valida
                $erros = 0;
                if($this->get_post('titular')==''){
					$this->tratar_erro("Informe o nome do titular!");
					$erros++;
                }
                if($this->settings['fraude']=='yes' && $currency_code=='BRL'){
					if($this->get_post('fiscal')==''){
						$this->tratar_erro("Informe um CPF/CNPJ v&aacute;lido!");
						$erros++;
					}
					$cpf_cnpj = new ValidaCPFCNPJ($this->get_post('fiscal'));
					if(!$cpf_cnpj->valida()){
						$this->tratar_erro("O CPF/CNPJ n&atilde;o &eacute; v&aacute;lido!");
						$erros++;
					}
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
					$this->tratar_erro("Selecione a parcela desejada!");
					$erros++;
                }
                if($erros>0){
                    return false;
                }
            }
            return true;
        }
        
        private function get_post( $name ) {
                if (isset($_POST['cielo_webservice'][$name])) {
                    return $_POST['cielo_webservice'][$name];
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
			if($bandeira=='Mastercard' || $bandeira=='mastercard'){
				$bandeira = 'Master';
			}
			
			//header cielo 
			$headers = array(
				"Content-Type" => "application/json",
				"Accept" => "application/json",
				"MerchantId" =>$afiliacao,
				"MerchantKey" =>$chave,
				"RequestId" => "",
			);
			
			//custom cobranca
			$celular = $order->get_meta( '_billing_cellphone' );
			$num_cob = $order->get_meta( '_billing_number' );
			$bairro_cob = $order->get_meta( '_billing_neighborhood' );
			$cpf_cob = preg_replace('/\D/', '', $order->get_meta( '_billing_cpf' ));
			$cnpj_cob = preg_replace('/\D/', '', $order->get_meta( '_billing_cnpj' ));
			
			//custom entrega
			$num_ent = $order->get_meta( '_shipping_number' );
			$bairro_ent = $order->get_meta( '_shipping_neighborhood' );
			
			//trata o telefone
			$telefone = '';
			if($order->get_billing_phone()!=""){
				$telefone = preg_replace('/\D/', '', $order->get_billing_phone());
			}elseif(!empty($celular)){
				$telefone = preg_replace('/\D/', '', $celular);
			}
			
			//dados json a enviar a cielo
			$dados = array();
			$dados['MerchantOrderId'] = $order->get_id();
			if($this->settings['fraude']=='no' || $currency_code!='BRL'){
				//sem anti fraude
				$dados['Customer'] = array(
					'Name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
					'Email' => $order->get_billing_email(),
				);
			}else{
				//com anti-fraude ativo
				$fiscal_valor = $fiscal;
				$dados['Customer'] = array(
					'Name'=>$order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
					'Email'=>$order->get_billing_email(),
					"Identity" => $fiscal_valor,
					"IdentityType" => (strlen($fiscal_valor)==11?'CPF':'CNPJ'),
					'Address'=>array(
						'Street'=>$order->get_billing_address_1(),
						'Number'=>(!empty($num_cob)?$num_cob:'*'),
						'District'=>(!empty($bairro_cob)?$bairro_cob:$order->get_billing_address_2()),
						'Complement' => (!empty($bairro_cob)?$order->get_billing_address_2():''),
						'ZipCode'=>preg_replace('/\D/', '', $order->get_billing_postcode()),
						'City'=>$order->get_billing_city(),
						'State'=>$order->get_billing_state(),
						'Country'=>substr($order->get_billing_country(),0,2),
					),
					'DeliveryAddress'=>array(
						'Street'=>$order->get_shipping_address_1(),
						'Number'=>(!empty($num_ent)?$num_ent:'*'),
						'District'=>(!empty($bairro_ent)?$bairro_ent:$order->get_shipping_address_2()),
						'Complement' => (!empty($bairro_ent)?$order->get_shipping_address_2():''),
						'ZipCode'=>preg_replace('/\D/', '', $order->get_shipping_postcode()),
						'City'=>$order->get_shipping_city(),
						'State'=>$order->get_shipping_state(),
						'Country'=>substr($order->get_shipping_country(),0,2),
					)
				);
				//remove endereco de entrega se dados vazios
				$pais_entrega = $order->get_shipping_country();
				$endereco_entrega = $order->get_shipping_address_1();
				if(empty($endereco_entrega) || empty($pais_entrega)){
					unset($dados['Customer']['DeliveryAddress']);
				}
			}

			//produtos 
			$produtos = array();
			if ( 0 < count( $order->get_items() ) ) {
				foreach ( $order->get_items() as $order_item ) {
					if ( $order_item['qty'] ) {
						$item_total = $order->get_item_total( $order_item, false );
						if ( 0 > $item_total ) {
							continue;
						}
						$item_name = $order_item['name'];
						$produtos[] = array(
							'GiftCategory' => 'No',
							'HostHedge' => 'Normal',
							'NonSensicalHedge' => 'Normal',
							'ObscenitiesHedge' => 'Normal',
							'PhoneHedge' => 'Normal',
							'Type' => 'Default',
							'Name' => $item_name,
							'Quantity' => $order_item['qty'],
							'Sku' =>  $order_item['product_id'],
							"TimeHedge" => "Normal",
							'UnitPrice' => number_format($item_total, 2, '', ''),
							'Risk' => 'Normal',
						);
					}
				}
			}

			//dados do cartão
			if($this->settings['fraude']=='yes' && $currency_code=='BRL' && substr($order->get_billing_country(),0,2)=='BR'){
				//anti fraude ativado
				//3ds 2.0
				if($this->settings['3ds']=='yes' && CIELO_WEBSERVICE_WOO_CREDITO_3DS && ($this->get_post('bandeira')=='visa' || $this->get_post('bandeira')=='mastercard' || $this->get_post('bandeira')=='elo')){
					$dados['Payment'] = array(
						'Type' => 'CreditCard',
						'Amount' => number_format($total, 2, '', ''),
						'Currency' => $currency_code,
						'Country' => 'BRA',
						'Provider' => $provider,
						'ServiceTaxAmount' => 0,
						'Installments' => $parcela,
						'Interest' => (($tipo_parcela==1 || $tipo_parcela==2)?'ByMerchant':'ByIssuer'),
						'Capture' =>  (($this->settings['captura']=='automatica')?'true':'false'),
						'Authenticate' => 'true',    
						'Recurrent' => 'false',
						'SoftDescriptor' => substr(preg_replace("/[^a-zA-Z0-9]+/", "", trim($this->settings['soft'])),0,13),
						'CreditCard' => array(  
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
						),
						'FraudAnalysis' => array(
							"Provider" => "cybersource",
							"Sequence" => trim($this->settings['fraude_analise']),
							"SequenceCriteria" => trim($this->settings['fraude_analise_criterio']),
							"CaptureOnLowRisk" => (($this->settings['fraude_captura']=='yes')?'true':'false'),
							"VoidOnHighRisk" => (($this->settings['fraude_cancelar']=='yes')?'true':'false'),
							"TotalOrderAmount" => number_format($total, 2, '', ''),
							"FingerPrintId" => $hash,
							"Browser" => array(
								"CookiesAccepted" => true,
								"Email" => $order->get_billing_email(),
								"IpAddress" => get_ip_cielo_webservice(),
								"Type" => substr($this->get_user_agent(),0,39)
							),
							'Cart' => array(
								'IsGift' => 'false',
								'ReturnsAccepted' => 'true',
								'Items' => $produtos,
							)
						)
					);
				}else{
					$dados['Payment'] = array(
						'Type' => 'CreditCard',
						'Amount' => number_format($total, 2, '', ''),
						'Currency' => $currency_code,
						'Country' => 'BRA',
						'Provider' => $provider,
						'ServiceTaxAmount' => 0,
						'Installments' => $parcela,
						'Interest' => (($tipo_parcela==1 || $tipo_parcela==2)?'ByMerchant':'ByIssuer'),
						'Capture' =>  (($this->settings['captura']=='automatica')?'true':'false'),
						'Authenticate' => 'false',    
						'Recurrent' => 'false',
						'SoftDescriptor' => substr(preg_replace("/[^a-zA-Z0-9]+/", "", trim($this->settings['soft'])),0,13),
						'CreditCard' => array(  
							"CardNumber" => $numero_cartao,
							"Holder" => $nome_completo,
							"ExpirationDate" => $mes_cartao.'/'.$ano_cartao,
							"SecurityCode" => $cod_cartao,
							"SaveCard" => "false",
							"Brand" => ucfirst($bandeira)
						),
						'FraudAnalysis' => array(
							"Provider" => "cybersource",
							"Sequence" => trim($this->settings['fraude_analise']),
							"SequenceCriteria" => trim($this->settings['fraude_analise_criterio']),
							"CaptureOnLowRisk" => (($this->settings['fraude_captura']=='yes')?'true':'false'),
							"VoidOnHighRisk" => (($this->settings['fraude_cancelar']=='yes')?'true':'false'),
							"TotalOrderAmount" => number_format($total, 2, '', ''),
							"FingerPrintId" => $hash,
							"Browser" => array(
								"CookiesAccepted" => true,
								"Email" => $order->get_billing_email(),
								"IpAddress" => get_ip_cielo_webservice(),
								"Type" => substr($this->get_user_agent(),0,39)
							),
							'Cart' => array(
								'IsGift' => 'false',
								'ReturnsAccepted' => 'true',
								'Items' => $produtos,
							)
						)
					);
				}
			}else{
				//sem anti-fraude 
				//3ds 2.0
				if($this->settings['3ds']=='yes' && CIELO_WEBSERVICE_WOO_CREDITO_3DS && ($this->get_post('bandeira')=='visa' || $this->get_post('bandeira')=='mastercard' || $this->get_post('bandeira')=='elo')){
					$dados['Payment'] = array(
						'Type' => 'CreditCard',
						'Amount' => number_format($total, 2, '', ''),
						'Currency' => $currency_code,
						'Country' => 'BRA',
						'Provider' => $provider,
						'ServiceTaxAmount' => 0,
						'Installments' => $parcela,
						'Interest' => (($tipo_parcela==1 || $tipo_parcela==2)?'ByMerchant':'ByIssuer'),
						'Capture' =>  (($this->settings['captura']=='automatica')?'true':'false'),
						'Authenticate' => 'true',    
						'Recurrent' => 'false',
						'SoftDescriptor' => substr(preg_replace("/[^a-zA-Z0-9]+/", "", trim($this->settings['soft'])),0,13),
						'CreditCard' => array(  
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
				}else{
					$dados['Payment'] = array(
						'Type' => 'CreditCard',
						'Amount' => number_format($total, 2, '', ''),
						'Currency' => $currency_code,
						'Country' => 'BRA',
						'Provider' => $provider,
						'ServiceTaxAmount' => 0,
						'Installments' => $parcela,
						'Interest' => (($tipo_parcela==1 || $tipo_parcela==2)?'ByMerchant':'ByIssuer'),
						'Capture' =>  (($this->settings['captura']=='automatica')?'true':'false'),
						'Authenticate' => 'false',    
						'Recurrent' => 'false',
						'SoftDescriptor' => substr(preg_replace("/[^a-zA-Z0-9]+/", "", trim($this->settings['soft'])),0,13),
						'CreditCard' => array(  
							 "CardNumber" => $numero_cartao,
							 "Holder" => $nome_completo,
							 "ExpirationDate" => $mes_cartao.'/'.$ano_cartao,
							 "SecurityCode" => $cod_cartao,
							 "SaveCard" => "false",
							 "Brand" => ucfirst($bandeira)
						)
					);
				}
				
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
					//bloqueia a finalização de pedidos negados se ativado 
					if($dados_pedido['Payment']['Status']==3 && $this->settings['acao']=='alerta'){
						$this->tratar_erro("Seu pedido foi negado por o emissor de seu cart&atilde;o, verifique se informou todos os dados corretamente e se preferir tente usando um novo cart&atilde;o.");
						return false;
					}
					
					//cria meta com a resposta
					update_post_meta($order_id,'_dados_cielo_api',$dados_pedido);
					update_post_meta($order_id,'_processado_cielo_loja5','true');
					
					//limpa a session 
					WC()->session->set( 'session_cielo_loja_5', null );
					
					//cria no banco de dados 
					$wpdb->query("INSERT INTO `wp_cielo_api_loja5` (`id`, `metodo`, `id_pagamento`, `tid`, `pedido`, `bandeira`, `parcela`, `lr`, `lr_log`, `total`, `status`, `bin`, `link`) VALUES (NULL, 'credito', '".$dados_pedido['Payment']['PaymentId']."', '".$dados_pedido['Payment']['Tid']."', '".$order->get_id()."', '".ucfirst($bandeira)."', '".$parcela."', '".(isset($dados_pedido['Payment']['ReturnCode'])?$dados_pedido['Payment']['ReturnCode']:'')."', '".(isset($dados_pedido['Payment']['ReturnMessage'])?$dados_pedido['Payment']['ReturnMessage']:'')."', '".$total."', '".$dados_pedido['Payment']['Status']."', '".$bin."', '');");
					
					//cria uma nota no pedido
					$log = "Transa&ccedil;&atilde;o Cr&eacute;dito Cielo - TID ".$dados_pedido['Payment']['Tid']." em ".$parcela."x no ".ucfirst($bandeira)." (".$bin.")";
					if(isset($dados_pedido['Payment']['ProofOfSale'])){
						$log .= ' / NSU: '.$dados_pedido['Payment']['ProofOfSale'].'';
					}
					if(isset($dados_pedido['Payment']['AuthorizationCode'])){
						$log .= ' / Auth: '.$dados_pedido['Payment']['AuthorizationCode'].'';
					}
					if(isset($dados_pedido['Payment']['FraudAnalysis']['Id'])){
						$html .= ' / ID Anti-fraude: ' . $dados_pedido['Payment']['FraudAnalysis']['Id'] . '';
						if(isset($dados_pedido['Payment']['FraudAnalysis']['Status'])){
							if($dados_pedido['Payment']['FraudAnalysis']['Status']==1){
								$html .= ' / Status Anti-fraude: ' . $dados_pedido['Payment']['FraudAnalysis']['Status'] . ' - Aceito';
							}elseif($dados_pedido['Payment']['FraudAnalysis']['Status']==2){
								$html .= ' / Status Anti-fraude: ' . $dados_pedido['Payment']['FraudAnalysis']['Status'] . ' - Rejeitado</p>';
							}elseif($dados_pedido['Payment']['FraudAnalysis']['Status']==3){
								$html .= ' / Status Anti-fraude: ' . $dados_pedido['Payment']['FraudAnalysis']['Status'] . ' - Revis&atilde;o</p>';
							}elseif($dados_pedido['Payment']['FraudAnalysis']['Status']==4){
								$html .= ' / Status Anti-fraude: ' . $dados_pedido['Payment']['FraudAnalysis']['Status'] . ' - Abortado</p>';
							}elseif($dados_pedido['Payment']['FraudAnalysis']['Status']==5){
								$html .= ' / Status Anti-fraude: ' . $dados_pedido['Payment']['FraudAnalysis']['Status'] . ' - N&atilde;o Finalizado</p>';
							}else{
								$html .= ' / Status Anti-fraude: ' . $dados_pedido['Payment']['FraudAnalysis']['Status'] . ' - Desconhecido';
							}
						}
						if(isset($dados_pedido['Payment']['FraudAnalysis']['FraudAnalysisReasonCode'])){
							$html .= ' / Anti-fraude Raz&atilde;o: ' . $dados_pedido['Payment']['FraudAnalysis']['FraudAnalysisReasonCode'] . '';
						}
						if(isset($dados_pedido['Payment']['FraudAnalysis']['ReplyData']['Score'])){
							$html .= ' / Anti-fraude Score: ' . $dados_pedido['Payment']['FraudAnalysis']['ReplyData']['Score'] . '';
						}
					}
					if(!empty($this->get_post('auth')) && $this->settings['3ds']=='yes' && ($this->get_post('bandeira')=='visa' || $this->get_post('bandeira')=='mastercard' || $this->get_post('bandeira')=='elo')){
						$log .= ' / Autenticação Crédito: '.$this->get_post('auth').'';
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
        
        public function get_user_agent() {
          return isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : '';
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
        
        private function get_user_login() {
            global $user_login;
			$ip = (isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'127.0.0.1');
            wp_get_current_user();
			if(is_user_logged_in() && !empty($user_login)){
				return $user_login;
			}else{
				return $ip;
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