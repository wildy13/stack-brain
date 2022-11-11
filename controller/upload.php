<?php
//header('Access-Control-Allow-Origin: http://www.baidu.com'); 
//header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
//error_reporting(E_ERROR);
if (is_file(LibraryPath . 'Uploader.config.json')) {
	$UploadConfig = JsonDecode(file_get_contents(LibraryPath . 'Uploader.config.json'));
} else {
	$UploadConfig = JsonDecode(file_get_contents(LibraryPath . 'Uploader.config.template.json'));
}
$action = $_GET['action'];

switch ($action) {
	case 'config':
		$result = json_encode($UploadConfig);
		break;
	case 'uploadimage':
	case 'uploadscrawl':
	case 'uploadvideo':
	case 'uploadfile':
		$result = include("upload_file.php");
		break;
	case 'listimage':
		$result = include("upload_list.php");
		break;
	case 'listfile':
		$result = include("upload_list.php");
		break;
	case 'catchimage':
		$result = include("upload_crawler.php");
		break;

	default:
		$result = json_encode(array(
			'state' => '请求地址出错'
		));
		break;
}

if (isset($_GET["callback"])) {
	if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
		echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
	} else {
		echo json_encode(array(
			'state' => 'callback参数不合法'
		));
	}
} else {
	echo $result;
}