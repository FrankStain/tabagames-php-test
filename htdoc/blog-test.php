<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>Тестирование</title>
<style type="text/css">
.TraceCloud {
	outline: 1px solid #047700;
	padding: 4px;
	margin: 5px;
	background-color: #E9F4E6;
	overflow: auto;
	font-family: "Courier New";
	font-size: 12pt;
}

.InfoCloud {
	outline: 1px solid #E0BDC6;
	padding: 4px; margin: 5px;
	background-color: #F4E6E9;
	overflow: auto;
	font-family: courier;
	font-size: 10pt;
}
</style>
</head><body><?php

	define( 'PROJECT_DIR', preg_replace( '/([\/\\\]htdoc[\/\\\].+)/', '', __FILE__ ) );
	require_once( PROJECT_DIR.'/php.inc/include.php' );

	?><div class="InfoCloud"><?php

		$aPaths = array( PROJECT_DIR.'/tests/prototypes' );
		while( count( $aPaths ) ){

			$sPath = array_pop( $aPaths );
			$aDir = scandir( $sPath );

			foreach( $aDir as $sDirPath ){

				if( '.' == $sDirPath['0'] ) continue;
				$sFileName = $sPath.'/'.$sDirPath;

				if( is_dir( $sFileName ) ){

					$aPaths[] = $sFileName;
					continue;

				};

				if( strstr( $sDirPath, '.example.' ) ){

					?><a href="<?=$_SERVER['SCRIPT_NAME'].'?f='.base64_encode( $sFileName );?>"><?=$sDirPath;?></a><br><?php

				};

			};

		};

	?></div><hr>
	<? if( isset( $_GET['f'] ) ){ ?>
		<?php $sFileName = base64_decode( $_GET['f'] ); ?>
		<div class="InfoCloud">Вывод файла <?=basename( $sFileName );?> : </div>
		<pre><div class="TraceCloud"><?php

			if( file_exists( $sFileName ) ){

				require_once( $sFileName );

			};

		?></div></pre>
	<? }; ?>
</body></html>