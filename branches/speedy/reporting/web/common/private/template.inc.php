<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Speedy {dash_title}</title>
		
		<link href="{homedir}common/public/speedy.css" rel="stylesheet" type="text/css" media="all" />
		<link rel="shortcut icon" href="{homedir}common/public/favico.ico" >

		<script type="text/javascript" src="{homedir}common/public/jquery/jquery-1.8.3.min.js"></script>
		<script type="text/javascript" src="{homedir}common/public/jquery/jquery-ui-1.9.2.custom.min.js"></script>
		
		<link type="text/css" href="{homedir}common/public/jquery/jquery-ui-1.9.2.custom.min.css" rel="Stylesheet" />
		<link type="text/css" href="{homedir}common/public/shjs/sh_style.css" rel="Stylesheet" />
		
		<script type="text/javascript" src="{homedir}common/public/shjs/sh_main.min.js"></script>
		<script type="text/javascript" src="{homedir}common/public/shjs/sh_sql.min.js"></script>
		
		<meta name="format-detection" content="telephone=no">
		
		{head}
	</head>
	
	<body onload="sh_highlightDocument();">
		<div id="content">
			
			<div id="header">
				<div style="text-align: {align}"><a href="{homedir}index.php"><img src="{homedir}common/public/logo.png" /></a></div>
				<div style="text-align: {align}" class="nav">&nbsp;{nav}</div>
			</div>
			
			<div id="title">
				{title}
			</div>
			<br />
		
			{content}
			

			<div id="footer">
				Last updated on <?php echo htmlentities( date( 'j F Y', filemtime( $_SERVER['SCRIPT_FILENAME'] ) ) . '.' . "\n" ); ?>
				<br />
				Powered by <a href="http://ashima.googlecode.com" target="_blank">ashima</a>.
			</div>
		</div>
	</body>
	
</html>