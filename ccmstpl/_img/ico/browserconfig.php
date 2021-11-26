<?
header("Content-type: application/xhtml+xml");
header('Expires: ' . gmdate('D, d M Y H:i:s T', time() + ($CFG["CACHE_EXPIRE"] * 60)));
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<browserconfig>
	<msapplication>
		<tile>
			<square150x150logo src="{CCMS_LIB:site.php;FUNC:load_resource("AWS")}/ccmstpl/_img/ico/mstile-150x150.png"/>
			<TileColor>#da532c</TileColor>
		</tile>
	</msapplication>
</browserconfig>
