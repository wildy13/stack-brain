<meta charset="utf-8">

<?php
@set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');

$Message = '';
$Version = '1.0.0';
define('PREFIX', 'sb_');
if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())) {
	die("Please enable Apache mod_rewrite! ");
}
if (is_file('install.lock')) {
	die("Please Remove install/install.lock before install!");
	exit();
}

if (is_writable(dirname(dirname(__FILE__))) === false) {
	die("The root directory can not be written. This causes the configuration file to not be generated. ");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$fp = fopen(__DIR__ . '/database.sql', "r") or die("The SQL File could not be opened.");
	if (isset($_POST["Language"]) && isset($_POST["DBHost"]) && isset($_POST["DBName"]) && isset($_POST["DBUser"]) && isset($_POST["DBPassword"])) {
		$Language = $_POST['Language'];
		$DBHost = $_POST['DBHost'];
		$DBName = $_POST['DBName'];
		$DBUser = $_POST['DBUser'];
		$DBPassword = $_POST['DBPassword'];
		$SearchServer = $_POST['SearchServer'];
		$SearchPort = $_POST['SearchPort'];
		$EnableMemcache = $_POST['EnableMemcache'];
		$MemCachePrefix = $_POST['MemCachePrefix'];
	} else {
		die("An Unexpected Error Occured!");
	}
	$WebsitePath = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	if (preg_match('/(.*)\/install/i', $WebsitePath, $WebsitePathMatch)) {
		$WebsitePath = $WebsitePathMatch[1];
	} else {
		$WebsitePath = '';
	}
	require('../library/PDO.class.php');
	$DB = new Db($DBHost, 3306, '', $DBUser, $DBPassword);
	$DatabaseExist = $DB->single("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :DBName", array('DBName' => $DBName));
	if (empty($DatabaseExist)) {
		$DB->query("CREATE DATABASE IF NOT EXISTS " . $DBName . ";");
	}

	$DB = new Db($DBHost, 3306, $DBName, $DBUser, $DBPassword);
	while ($SQL = GetNextSQL()) {
		$DB->query($SQL);
	}
	$DB->query("INSERT INTO `" . PREFIX . "config` VALUES ('WebsitePath', '" . $WebsitePath . "')");
	$DB->query("INSERT INTO `" . PREFIX . "config` VALUES ('LoadJqueryUrl', '" . $WebsitePath . "/static/js/jquery.js')");
	$DB->query("UPDATE `" . PREFIX . "config` SET `ConfigValue`='" . date('Y-m-d') . "' WHERE `ConfigName`='DaysDate'");
	$DB->query("UPDATE `" . PREFIX . "config` SET `ConfigValue`='" . $Version . "' WHERE `ConfigName`='Version'");
	$DB->CloseConnection();
	fclose($fp) or die("Can’t close file");


	$ConfigPointer = fopen(__DIR__ . '/config.tpl', 'r');
	$ConfigBuffer = fread($ConfigPointer, filesize(__DIR__ . '/config.tpl'));
	$ConfigBuffer = str_replace("{{Language}}", $Language, $ConfigBuffer);
	$ConfigBuffer = str_replace("{{DBHost}}", $DBHost, $ConfigBuffer);
	$ConfigBuffer = str_replace("{{DBName}}", $DBName, $ConfigBuffer);
	$ConfigBuffer = str_replace("{{DBUser}}", $DBUser, $ConfigBuffer);
	$ConfigBuffer = str_replace("{{DBPassword}}", $DBPassword, $ConfigBuffer);
	$ConfigBuffer = str_replace("{{SearchServer}}", $SearchServer, $ConfigBuffer);
	$ConfigBuffer = str_replace("{{SearchPort}}", $SearchPort, $ConfigBuffer);
	$ConfigBuffer = str_replace("{{EnableMemcache}}", $EnableMemcache, $ConfigBuffer);
	$ConfigBuffer = str_replace("{{MemCachePrefix}}", $MemCachePrefix, $ConfigBuffer);

	fclose($ConfigPointer);
	$ConfigPHP = fopen("../config.php", "w+");
	fwrite($ConfigPHP, $ConfigBuffer);
	fclose($ConfigPHP);


	$HtaccessPointer = fopen(__DIR__ . '/htaccess.tpl', 'r');
	$HtaccessBuffer = fread($HtaccessPointer, filesize(__DIR__ . '/htaccess.tpl'));
	$HtaccessBuffer = str_replace("{{WebSitePath}}", $WebsitePath, $HtaccessBuffer);

	if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
		$HtaccessBuffer = str_replace("{{RedirectionType}}", "[QSA,NU,PT,L]", $HtaccessBuffer);
	} else {
		$HtaccessBuffer = str_replace("{{RedirectionType}}", "[L]", $HtaccessBuffer);
	}
	fclose($HtaccessPointer);
	$Htaccess = fopen("../.htaccess", "w+");
	fwrite($Htaccess, $HtaccessBuffer);
	fclose($Htaccess);


	$Message = 'Installed successfully! <br /><a href="../register">点我马上注册管理员账号<br />The first registered users will become administrators.</a>';

	if (!file_exists('install.lock')) {
		touch('install.lock');
	}
	if (!file_exists('../update/update.lock')) {
		touch("../update/update.lock");
	}
} else {
	if (version_compare(PHP_VERSION, '5.4.0') < 0) {
		$Message = 'Your PHP version is too low, it may not work properly!';
	}
	if (!extension_loaded('pdo_mysql')) {
		$Message = 'Your PHP don’t support pdo_mysql extension, this program does not work! ';
	}
	if (!extension_loaded('mbstring')) {
		$Message = 'Your PHP don’t support mbstring extension, this program does not work! ';
	}
	if (!extension_loaded('curl')) {
		$Message = 'Your PHP don’t support curl extension, this program does not work! ';
	}
	if (!extension_loaded('gd')) {
		$Message = 'Your PHP don’t support gd extension, this program does not work! ';
	}
	if (!extension_loaded('dom')) {
		$Message = 'dom，Your PHP don’t support dom extension, this program does not work! ';
	}
}


function GetNextSQL()
{
	global $fp;
	$sql = "";
	while ($line = fgets($fp, 40960)) {
		$line = trim($line);
		if (strlen($line) > 1) {
			if ($line[0] == "-" && $line[1] == "-") {
				continue;
			}
		}
		$sql .= $line . chr(13) . chr(10);
		if (strlen($line) > 0) {
			if ($line[strlen($line) - 1] == ";") {
				break;
			}
		}
	}
	return $sql;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-cmn-Hans" lang="zh-cmn-Hans">

<head>
	<meta charset="UTF-8" />
	<meta content="True" name="HandheldFriendly" />
	<title>Install - Stack Brain</title>
	<link href="../static/css/default/style.css" rel="stylesheet" type="text/css" />
</head>

<body>
	<!-- content wrapper start -->
	<div class="wrapper">
		<!-- main start -->
		<div class="main">
			<!-- main-content start -->
			<div class="main-content">
				<div class="title">
					Stack Brain &raquo; 安装&nbsp;&nbsp;/&nbsp;&nbsp;Install
				</div>
				<div class="main-box">
					<form action="?" method="post">
						<table cellpadding="5" cellspacing="8" border="0" width="100%" class="fs14">
							<tbody>
								<tr>
									<td width="auto" align="center" colspan="2"><span class="red"><?php echo $Message; ?></span>
									</td>
								</tr>
								<?php if (!$Message) { ?>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Language</td>
										<td width="auto" align="left">
											<select name="Language">
												<?php
												$SupportedLanguages = array(
													'zh-cn' => '简体中文',
													'zh-tw' => '繁體中文',
													'en' => 'English',
													'ru' => 'Русский',
													'pl' => 'polski'
												);
												$UserLanguages = array();
												if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
													// break up string into pieces (languages and q factors)
													preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $LangParse);
													if (count($LangParse[1])) {
														// create a list like "en" => 0.8
														// $UserLanguages = array_combine($LangParse[1], $LangParse[4]);
														foreach ($LangParse[1] as $Key => $Value) {
															$UserLanguages[strtolower($LangParse[1][$Key])] = $LangParse[4][$Key];
														}
														// set default to 1 for any without q factor
														foreach ($UserLanguages as $Lang => $Val) {
															if ($Val === '')
																$UserLanguages[strtotime($Lang)] = 1;
														}
														// sort list based on value
														arsort($UserLanguages, SORT_NUMERIC);
													}
												}
												$IsLanguageSet = false;
												foreach ($SupportedLanguages as $Key => $Value) {
												?>
													<option<?php
															if (array_key_exists($Key, $UserLanguages) && !$IsLanguageSet) {
																echo " selected";
																$IsLanguageSet = true;
															}
															?> value="<?php echo $Key; ?>"><?php echo $Value; ?></option>
													<?php
												}
													?>
											</select>
										</td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Database Host</td>
										<td width="auto" align="left"><input type="text" name="DBHost" class="sl w200" value="localhost" /></td>
									</tr>
									<tr>
										<td width="280" align="right">/&nbsp;&nbsp;Database Name</td>
										<td width="auto" align="left"><input type="text" name="DBName" class="sl w200" value="" /></td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Database Account</td>
										<td width="auto" align="left"><input type="text" name="DBUser" class="sl w200" value="root" /></td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Database Password</td>
										<td width="auto" align="left"><input type="password" name="DBPassword" class="sl w200" value="" /></td>
									</tr>
									<tr>
										<td colspan="2" class="title">Advanced Settings (Optional)</td>

									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Sphinx Search Server
										</td>
										<td width="auto" align="left"><input type="text" name="SearchServer" class="sl w200" value="" /></td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Sphinx Search Port</td>
										<td width="auto" align="left"><input type="text" name="SearchPort" class="sl w200" value="" /></td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Enable Cache<br />(Memcached /
											Redis / XCache)
										</td>
										<td width="auto" align="left">
											<select name="EnableMemcache">
												<option value="false">False</option>
												<option value="true">True</option>
											</select>
										</td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Cache Prefix</td>
										<td width="auto" align="left"><input type="text" name="MemCachePrefix" class="sl w200" value="sb_" /></td>
									</tr>
									<tr>
										<td width="280" align="right"></td>
										<td width="auto" align="left"><input type="submit" value="Install " name="submit" class="textbtn" /></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</form>
				</div>
			</div>
			<!-- main-content end -->
			<div class="main-sider">
				<div class="sider-box">
					<div class="sider-box-title">Installation Notes</div>
					<div class="sider-box-content">
						<p>
							If there is an "Access denied" error, it means that the filling is incorrect, please go back and fill in again.
						</p>
						<p>
							After installation, the first registered user will automatically become an administrator.
						</p>
					</div>
				</div>
			</div>
			<div class="c"></div>
		</div>
		<!-- main end -->
		<div class="c"></div>

		<!-- footer start -->
		<div class="Copyright">
			<p>
				Powered By Stack Brain <?php echo $Version; ?> © 2022
			</p>
		</div>
		<!-- footer end -->

	</div>
	<!-- content wrapper end -->
</body>

</html>