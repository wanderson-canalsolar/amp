<?php 
if(isset($cielo['tid'])){
	$html = '<p>Sua transa&ccedil;&atilde;o refer&ecirc;nte ao pedido <b>#'.$order_id.'</b> foi processada junto a operadora.<br>
	A sua transa&ccedil;&atilde;o encontra-se <b>'.strtoupper($status).'</b>.<br><br>
	<b>TID:</b>  '.$cielo['tid'].'<br>
	<b>Bandeira:</b> '.ucfirst($cielo['bandeira']).' em '.$cielo['parcela'].'x<br>
	<b>BIN:</b>  '.$cielo['bin'].'<br>';
	if(isset($cielo['lr']) && !empty($cielo['lr'])){
		$html .= '<b>LR:</b>  '.$cielo['lr'].' - '.$cielo['lr_log'].'<br>';
	}
	if(($cielo['total']-$total_pedido) > 0.10){
		$html .= '<b>Juros:</b> R$ '.number_format(($cielo['total']-$total_pedido),'2','.','').'<br />';
	}
	$html .= '<b>Total a Pagar:</b> R$ '.number_format($cielo['total'],'2','.','').'<br>';
	$html .= '<b>ID Pagamento:</b>  '.$cielo['id_pagamento'].'<br><br>
	Caso tenha alguma d&uacute;vida referente a transa&ccedil;&atilde;o entre em contato com o atendimento da loja.</p>';
	echo wpautop(wptexturize($html));
}
?>