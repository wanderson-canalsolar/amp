<?php 
if(isset($cielo['id_pagamento'])){
	$html = '<p>Sua transa&ccedil;&atilde;o refer&ecirc;nte ao pedido <b>#'.$order_id.'</b> foi processada junto a operadora.<br>
	A sua transa&ccedil;&atilde;o encontra-se <b>'.strtoupper($status).'</b>.<br><br>';
	if(isset($cielo['tid']) && !empty($cielo['tid']) && (int)$cielo['tid'] > 0){
		$html .= '<b>Nosso N&uacute;mero:</b>  '.$cielo['tid'].'<br>';
	}
	$html .= '<b>Banco:</b> '.ucfirst($cielo['bandeira']).' / &agrave; vista<br>';
	if(isset($cielo['lr']) && !empty($cielo['lr'])){
		$html .= '<b>LR:</b>  '.$cielo['lr'].' - '.$cielo['lr_log'].'<br>';
	}
	$html .= '<b>ID Pagamento:</b>  '.$cielo['id_pagamento'].'<br>';
	if(isset($_GET['linha']) && !empty($_GET['linha'])){
		$html .= '<b>Linha Digit&aacute;vel:</b>  '.urldecode($_GET['linha']).'<br>';
	}
	if($cielo['metodo']=='tef' && isset($cielo['link']) && !empty($cielo['link']) && $cielo['status']!=2){
		$html .= '<br><a class="button"  style="background: #32a2bb; color: #FFF;" href="'.$cielo['link'].'" target="_blank">Concluir Pagamento via '.ucfirst($cielo['bandeira']).'</a><br>';
	}elseif($cielo['metodo']=='boleto' && isset($cielo['link']) && !empty($cielo['link'])){
		if(isset($dados_pedido['Payment']['DigitableLine']) && !empty(trim($dados_pedido['Payment']['DigitableLine']))){
			$html .= '<b>Linha digit&aacute;vel:</b> <span style="user-select: all">'.$dados_pedido['Payment']['DigitableLine'].'</span><br>';
		}
		if(isset($dados_pedido['Payment']['BarCodeNumber']) && !empty(trim($dados_pedido['Payment']['BarCodeNumber']))){
			$html .= '<b>C&oacute;digo de Barras:</b><br><img src="'.plugins_url().'/loja5-woo-cielo-webservice/barra.php?codigo='.preg_replace('/\D/', '', $dados_pedido['Payment']['BarCodeNumber']).'">';
		}
		$html .= '<br><a class="button"  style="background: #32a2bb; color: #FFF;" href="'.$cielo['link'].'" target="_blank">Acessar e Pagar o Boleto</a><br>';
	}		
	$html .= '<br>Caso tenha alguma d&uacute;vida referente a transa&ccedil;&atilde;o entre em contato com o atendimento da loja e se j&aacute; pagou aguarde a confirma&ccedil;&atilde;o do seu pedido.</p>';
	echo wpautop(wptexturize($html));
}
?>