<html>
	<head>
	</head>
	<body style='background:#EEEFFF'>
	<br><br><br><br><br><br>
	<div style=" background:none repeat scroll 0 0 #FFF;
border:2px solid #DDD;font-size:1.25em;
margin:0 0 1.5em;color:#666666;
	width:750px;height:150px;padding:5px;align:center;margin:0 auto;" >
		Veuillez patienter nous allons vous rediriger vers le serveur de paiement... Merci.
		<br /><br />
		<p align="center"><img src="ajax-loader.gif"/></p>
		<p align="right">
		<a align="right" href="http://{$url}order.php">{$cancel_text}</a></p>
		<form action="{$smt_url}" method="post" id="smt_form" class="hidden">
				
			<input name="reference" type="hidden" value="{$reference}">
			<input name="montant" type="hidden" value="{$amount}">
			<input name="devise" type="hidden" value="{$currency_module->iso_code}">
			<input type="hidden" name="sid" value="{$sid}">
			<input type="hidden" name="affilie" value="{$affilie}">
		</form>
		<script type="text/javascript">
		{literal}
		$(document).ready(function() {
			$('#smt_form').submit();
		});
		{/literal}
		</script>
	</div>
	</body>
</html>
