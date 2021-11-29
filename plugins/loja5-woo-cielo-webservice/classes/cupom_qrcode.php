<?php 
if(isset($cielo['tid'])){
	$html = '<p>Sua transa&ccedil;&atilde;o refer&ecirc;nte ao pedido <b>#'.$order_id.'</b> foi processada junto a operadora.<br>
	A sua transa&ccedil;&atilde;o encontra-se <b>'.strtoupper($status).'</b>.<br><br>
	<b>QrCode ID:</b>  '.$dados_pedido['Payment']['QrCodeId'].'<br>
	<b>Bandeira:</b> '.ucfirst($cielo['bandeira']).' em '.$cielo['parcela'].'x<br>';
    $html .= '<b>ID Pagamento:</b>  '.$cielo['id_pagamento'].'<br>';
    if(isset($dados_pedido['Payment']['QrCodeBase64Image']) && !empty($dados_pedido['Payment']['QrCodeBase64Image'])){
		$html .= '<b>QrCode de Pagamento:</b><br><img style="border: 1px solid #CCC;border-radius: 10px;" src="data:image/png;base64,'.$dados_pedido['Payment']['QrCodeBase64Image'].'"><br>
		<i>Abra o seu APP Cielo Pay e aponte o leitor de QrCode para esta imagem e conclua o pagamento diretamente de seu APP, ao pagar aguarde alguns segundos que a tela ser&aacute; automaticamente atualizada.</i><br>';
	}
    $html .='<br>
	Caso tenha alguma d&uacute;vida referente a transa&ccedil;&atilde;o entre em contato com o atendimento da loja.</p>';
	echo wpautop(wptexturize($html));
}
?>
<script>
    function verificar_pagamento_qrcode_cielo(){
		//verifica a cada 10 segundos se o qrcode foi pago
		setInterval(function (){
			//consulta a preferencia de pagamento 
			jQuery.ajax({
				method: "POST",
				url: "<?php echo $url_ver_qrcode; ?>",
				dataType: "JSON",
				data: { id: "<?php echo $cielo['id_pagamento'];?>", hash: "<?php echo sha1($cielo['id_pagamento']);?>" }
			}).done(function( json ) {
				if(json.atualizar==true){
					location.reload(true);
				}
			});
		}, 10000);
	}
	verificar_pagamento_qrcode_cielo();
</script>