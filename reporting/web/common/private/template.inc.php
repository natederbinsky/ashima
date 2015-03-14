<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>ashima {dash_title}</title>
		
		<link href="{homedir}common/public/speedy.css" rel="stylesheet" type="text/css" media="all" />
		<link rel="shortcut icon" href="{homedir}common/public/favicon.ico" >

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
				<table>
					<tbody>
						<tr>
							<td rowspan="2"><a href="{homedir}index.php"><img height="60" width="60" src="{homedir}common/public/logo.png" /></a></td>
							<td class="title"><a href="{homedir}index.php">ashima</a></td>
						</tr>
						<tr>
							<td class="nav">{nav}</td>
						</tr>

					</tbody>
				</table>
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
