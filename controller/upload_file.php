<?php
include(LibraryPath . 'Uploader.class.php');

SetStyle('api', 'API');
header("Content-Type: text/html; charset=utf-8");
Auth(1, 0, true);

$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
	case 'uploadimage':
		$config    = array(
			"pathFormat" => $Config['WebsitePath'] . $UploadConfig['imagePathFormat'],
			"maxSize" => $UploadConfig['imageMaxSize'],
			"allowFiles" => $UploadConfig['imageAllowFiles']
		);
		$fieldName = $UploadConfig['imageFieldName'];
		break;
	case 'uploadscrawl':
		$config    = array(
			"pathFormat" => $Config['WebsitePath'] . $UploadConfig['scrawlPathFormat'],
			"maxSize" => $UploadConfig['scrawlMaxSize'],
			"allowFiles" => $UploadConfig['scrawlAllowFiles'],
			"oriName" => "scrawl.png"
		);
		$fieldName = $UploadConfig['scrawlFieldName'];
		$base64    = "base64";
		break;
	case 'uploadvideo':
		$config    = array(
			"pathFormat" => $Config['WebsitePath'] . $UploadConfig['videoPathFormat'],
			"maxSize" => $UploadConfig['videoMaxSize'],
			"allowFiles" => $UploadConfig['videoAllowFiles']
		);
		$fieldName = $UploadConfig['videoFieldName'];
		break;
	case 'uploadfile':
	default:
		$config    = array(
			"pathFormat" => $Config['WebsitePath'] . $UploadConfig['filePathFormat'],
			"maxSize" => $UploadConfig['fileMaxSize'],
			"allowFiles" => $UploadConfig['fileAllowFiles']
		);
		$fieldName = $UploadConfig['fileFieldName'];
		break;
}

$up = new Uploader($fieldName, $config, $base64, $CurUserName, $DB);

/**
 * 
 * array(
 *     "state" => "",          
 *     "url" => "",           
 *     "title" => "",          
 *     "original" => "",      
 *     "type" => ""            
 *     "size" => "",           
 * )
 */

return json_encode($up->getFileInfo());