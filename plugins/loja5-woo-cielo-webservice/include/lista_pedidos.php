<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pgwmodal@2.0.2/pgwmodal.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pgwmodal@2.0.2/pgwmodal.min.css">

<form method="post" action="admin.php?page=loja5-woo-cielo-webservice-pedidos">
<div id="tela-pedidos-cielo" class="wrap">
<h1 class="wp-heading-inline">Gestor de Pedidos Cielo</h1>
<div class="updated notice">
    <p>Gerencie os pedidos Cielo em sua loja abaixo qual podera consultar detalhes, cancelar (total) e capturar (total) pedidos autorizados, lembre-se ao cancelar um pedido o mesmo a a&ccedil;&atilde;o jamais poder&aacute; ser desfeita, para realizar captura (parcial) ou cancelamento (parcial) <a href="https://developercielo.github.io/tutorial/tutoriais-3-0" target="_blank">acesse aqui</a> e siga as instru&ccedil;&otilde;es.</p>
</div>
<hr class="wp-header-end">
<div class="tablenav">

<div class="tablenav-pages" style="margin: 1em 0;float: left;">
<select onchange="filtar_pedido_tipo_cielo(this.value)" name="tipo">
<option value=""<?php echo ($tipo=='')?' selected':'';?>>Todos</option>
<option value="loja5_woo_cielo_webservice"<?php echo ($tipo=='loja5_woo_cielo_webservice')?' selected':'';?>>Pago por Cr&eacute;dito</option>
<option value="loja5_woo_cielo_webservice_debito"<?php echo ($tipo=='loja5_woo_cielo_webservice_debito')?' selected':'';?>>Pago por D&eacute;bito</option>
<option value="loja5_woo_cielo_webservice_boleto"<?php echo ($tipo=='loja5_woo_cielo_webservice_boleto')?' selected':'';?>>Pago por Boleto</option>
<option value="loja5_woo_cielo_webservice_tef"<?php echo ($tipo=='loja5_woo_cielo_webservice_tef')?' selected':'';?>>Pago por TEF</option>
<option value="loja5_woo_cielo_webservice_qrcode"<?php echo ($tipo=='loja5_woo_cielo_webservice_qrcode')?' selected':'';?>>Pago por QrCode</option>
</select>
</div>
	
<div class="tablenav-pages" style="margin: 1em 0">
<button onclick="salvar_acaoes_cielo()" class="button button-primary" type="button">Aplicar A&ccedil;&otilde;es</button>
</div>

</div>

<table class="wp-list-table widefat fixed striped posts">
<thead>
<tr>
<th scope="col" style="width:60px;">Pedido</th>
<th scope="col">Detalhes</th>
<th scope="col">ID Payment</th>
<th scope="col" style="width:100px;">TID / Nosso Numero / QrCode ID</th>
<th scope="col" style="width:100px;">BIN / Link</th>
<th scope="col">LR</th>
<th scope="col">Status</th>
<th scope="col" style="width:120px;">Data</th>
<th scope="col">A&ccedil;&atilde;o</th>
<th scope="col" style="width:60px;">Pedido</th>
</tr>
</thead>
<tbody>
<?php 
foreach($pedidos as $k => $v) {
	$data = $v->get_date_created();
	$cielo = $this->registro_cielo($v->get_id());
	$status = (isset($cielo['status'])?$cielo['status']:'');
	$metodo = (isset($cielo['metodo'])?$cielo['metodo']:'');
	$prazo_captura = strtotime($data)+(CIELO_WEBSERVICE_WOO_PRAZO_GESTOR*24*60*60);
	?>
	<tr>
	<td><a href="post.php?post=<?php echo $v->get_id(); ?>&action=edit"><?php echo $v->get_id(); ?></a></td>
	<td><?php echo isset($cielo['metodo'])?ucfirst($cielo['metodo']):''; ?> <?php echo isset($cielo['bandeira'])?ucfirst($cielo['bandeira']):''; ?> / <?php echo isset($cielo['total'])?'R$'.$cielo['total']:''; ?> <?php echo isset($cielo['parcela'])?' em '.$cielo['parcela'].'x':''; ?><br>Por: <?php echo $v->get_billing_first_name(); ?> <?php echo $v->get_billing_last_name(); ?></td>
	<td><a alt="Ver logs da transacao <?php echo $v->get_id(); ?>" title="Ver logs da transacao <?php echo $v->get_id(); ?>" href="<?php echo admin_url( 'admin-ajax.php' );?>?action=logs_cielo_webservice_api_loja5&id=<?php echo isset($cielo['id_pagamento'])?$cielo['id_pagamento']:''; ?>" target="_blank"><?php echo isset($cielo['id_pagamento'])?$cielo['id_pagamento']:''; ?></a></td>
	<td><?php echo isset($cielo['tid'])?$cielo['tid']:''; ?></td>
	<td>
	<?php if($metodo=='credito' || $metodo=='debito'){ ?>
		<?php echo isset($cielo['bin'])?$cielo['bin']:''; ?>
	<?php }elseif(!empty($cielo['link'])){ ?>
		<a target="_blank" href="<?php echo isset($cielo['link'])?$cielo['link']:''; ?>">link de pagamento</a>
	<?php } ?>
	</td>
	<td><?php echo isset($cielo['lr'])?$cielo['lr']:''; ?> <?php echo isset($cielo['lr_log'])?$cielo['lr_log']:''; ?></td>
	<td><?php echo $this->status_cielo($status); ?></td>
	<td><?php echo $data->date('d/m/Y H:i'); ?></td>
	<td>
	<?php if(($metodo=='credito' || $metodo=='debito') && $prazo_captura >=  time()){ ?>
	<?php if($status==1 || $status==2){ ?>
	<select style="width: 100%;" class="lista_pedidos_acoes" data-pedido="<?php echo $v->get_id(); ?>" data-pedido_hash="<?php echo sha1(md5($v->get_id())); ?>" name="pedido_acao[<?php echo $v->get_id(); ?>]">
	<option value=''>A&ccedil;&atilde;o</option>
	<?php if($status==1){ ?>
	<option value='capturar'>CAPTURAR (<?php echo isset($cielo['total'])?'R$'.$cielo['total']:''; ?>)</option>
	<?php } ?>
	<?php if($status==1 || $status==2){ ?>
	<option value='cancelar'>CANCELAR (<?php echo isset($cielo['total'])?'R$'.$cielo['total']:''; ?>)</option>
	<?php } ?>
	</select>
	<?php } ?>
	<?php } ?>
	</td>
	<td><a href="post.php?post=<?php echo $v->get_id(); ?>&action=edit"><?php echo $v->get_id(); ?></a></td>
	</tr>
	<?php 
}
?>
</tbody>
</table>

<div class="tablenav">
<div class="tablenav-pages" style="margin: 1em 0">
<?php echo $total; ?> Registros - <?php echo ($page_links)?$page_links:'';?>
</div>
</div>

</div>
</form>

<script>
function filtar_pedido_tipo_cielo(tipo){
	location.href=window.location.href+'&tipo='+tipo;
}
function todos_selecionados(){
	var pedidos = [];
	jQuery(".lista_pedidos_acoes").each(function(){
		var acao = jQuery(this).val();
		var pedido = jQuery(this).data('pedido');
		var pedido_hash = jQuery(this).data('pedido_hash');
		if(acao!='' && pedido!=''){
			var objeto = {acao: acao,pedido: pedido,hash: pedido_hash};
			pedidos.push(objeto);
		}
	});
	console.log(pedidos);
	return pedidos;
}
function salvar_acaoes_cielo(){
	var selecionados = todos_selecionados();
	if(selecionados.length==0){
		alert('Ops, selecione os pedidos qual deseja aplicar alguma acao, lembre-se que apos aplicado a mesma jamais podera ser desfeita!');
		return false;
	}
	if(confirm('Confirma aplicar as acoes selecionadas? Lembre-se que ao aplicar a mesma jamais podera ser desfeita!')){
		//bloqueia a tela
		jQuery('#tela-pedidos-cielo').block({ 
			message: '<br><center><b>Aplicando a&ccedil;&otilde;es aos pedidos selecionados...</b></center><br>', 
			css: { border: '2px solid #CCC', 'border-radius': '5px' } 
		});
		//salva os rastreios
		jQuery.ajax({
			url : '<?php echo admin_url( 'admin-ajax.php' );?>?action=processar_pedidos_cielo_webservice',
			type : 'post',
			data : {acoes: selecionados},
			success : function( response ) {        
				console.log(response);
				jQuery('#tela-pedidos-cielo').unblock();
				location.reload();
			}
		});
	}
}
//fix modal
jQuery(document).bind('PgwModal::Close', function() {
    jQuery('#pgwModalBackdrop').remove();
	jQuery('#pgwModal').remove();
});
</script>