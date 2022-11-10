<?php
@set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
$Message = '';
$Version = '5.9.0';
define('DATABASE_PREFIX', 'sb_');

if (is_file('update.lock')) {
	die("Please Remove update/update.lock before update!");
}


if (is_writable(dirname(dirname(__FILE__))) === false) {
	die("The root directory can not be written. This causes the configuration file to not be generated. ");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$Language       = $_POST['Language'];
	$DBHost         = $_POST['DBHost'];
	$DBName         = $_POST['DBName'];
	$DBUser         = $_POST['DBUser'];
	$DBPassword     = $_POST['DBPassword'];
	$SearchServer   = $_POST['SearchServer'];
	$SearchPort     = $_POST['SearchPort'];
	$EnableMemcache = $_POST['EnableMemcache'];
	$MemCachePrefix = $_POST['MemCachePrefix'];
	$WebsitePath    = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	if (preg_match('/(.*)\/update/i', $WebsitePath, $WebsitePathMatch)) {
		$WebsitePath = $WebsitePathMatch[1];
	} else {
		$WebsitePath = '';
	}
	require('../library/PDO.class.php');
	$DB         = new Db($DBHost, 3306, $DBName, $DBUser, $DBPassword);
	$OldVersion = $DB->single("SELECT ConfigValue FROM `" . DATABASE_PREFIX . "config` WHERE `ConfigName`='Version'");

	$DB->query("UPDATE `" . DATABASE_PREFIX . "config` SET `ConfigValue`='" . $WebsitePath . "' WHERE `ConfigName`='WebsitePath'");
	$DB->query("UPDATE `" . DATABASE_PREFIX . "config` SET `ConfigValue`='" . $WebsitePath . "/static/js/jquery.js' WHERE `ConfigName`='LoadJqueryUrl'");


	$ConfigPointer = fopen('../install/config.tpl', 'r');
	$ConfigBuffer  = fread($ConfigPointer, filesize('../install/config.tpl'));
	$ConfigBuffer  = str_replace("{{Language}}", $Language, $ConfigBuffer);
	$ConfigBuffer  = str_replace("{{DBHost}}", $DBHost, $ConfigBuffer);
	$ConfigBuffer  = str_replace("{{DBName}}", $DBName, $ConfigBuffer);
	$ConfigBuffer  = str_replace("{{DBUser}}", $DBUser, $ConfigBuffer);
	$ConfigBuffer  = str_replace("{{DBPassword}}", $DBPassword, $ConfigBuffer);
	$ConfigBuffer  = str_replace("{{SearchServer}}", $SearchServer, $ConfigBuffer);
	$ConfigBuffer  = str_replace("{{SearchPort}}", $SearchPort, $ConfigBuffer);
	$ConfigBuffer  = str_replace("{{EnableMemcache}}", $EnableMemcache, $ConfigBuffer);
	$ConfigBuffer  = str_replace("{{MemCachePrefix}}", $MemCachePrefix, $ConfigBuffer);

	fclose($ConfigPointer);
	$ConfigPHP = fopen('../config.php', "w+");
	fwrite($ConfigPHP, $ConfigBuffer);
	fclose($ConfigPHP);
	$HtaccessPointer = fopen('../install/htaccess.tpl', 'r');
	$HtaccessBuffer  = fread($HtaccessPointer, filesize('../install/htaccess.tpl'));
	$HtaccessBuffer  = str_replace("{{WebSitePath}}", $WebsitePath, $HtaccessBuffer);

	if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
		$HtaccessBuffer = str_replace("{{RedirectionType}}", "[QSA,NU,PT,L]", $HtaccessBuffer);
	} else {
		$HtaccessBuffer = str_replace("{{RedirectionType}}", "[L]", $HtaccessBuffer);
	}
	fclose($HtaccessPointer);
	$Htaccess = fopen("../.htaccess", "w+");
	fwrite($Htaccess, $HtaccessBuffer);
	fclose($Htaccess);


	if (VersionCompare('3.3.0', $OldVersion)) {
		require("../library/MaterialDesign.Avatars.class.php");
		$UserIDArray = $DB->query('SELECT UserName, ID FROM ' . DATABASE_PREFIX . 'users');
		foreach ($UserIDArray as $UserInfo) {
			if (!is_file('../upload/avatar/small/' . $UserInfo['ID'] . '.png')) {

				if (extension_loaded('gd')) {
					$Avatar = new MDAvtars(mb_substr($UserInfo['UserName'], 0, 1, "UTF-8"), 256);
					$Avatar->Save('../upload/avatar/large/' . $UserInfo['ID'] . '.png', 256);
					$Avatar->Save('../upload/avatar/middle/' . $UserInfo['ID'] . '.png', 48);
					$Avatar->Save('../upload/avatar/small/' . $UserInfo['ID'] . '.png', 24);
				}
			}
		}
	}


	if (VersionCompare('3.5.0', $OldVersion)) {
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('PushConnectionTimeoutPeriod', '22')");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('SMTPHost', 'smtp1.example.com')");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('SMTPPort', '587')");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('SMTPAuth', 'true')");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('SMTPUsername', 'user@example.com')");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('SMTPPassword', 'secret')");

		$DB->query("DROP TABLE IF EXISTS `" . DATABASE_PREFIX . "app_user`");
		$DB->query("CREATE TABLE `" . DATABASE_PREFIX . "app_users` (
					  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `AppID` int(10) unsigned NOT NULL,
					  `OpenID` varchar(64) NOT NULL,
					  `AppUserName` varchar(50) CHARACTER SET utf8,
					  `UserID` int(10) unsigned NOT NULL,
					  `Time` int(10) unsigned NOT NULL,
					  PRIMARY KEY (`ID`),
					  KEY `Index` (`AppID`,`OpenID`),
					  KEY `UserID` (`UserID`)
					) DEFAULT CHARSET=utf8;");
	}


	if (VersionCompare('3.6.0', $OldVersion)) {
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "tags` CHANGE `IsEnabled` `IsEnabled` TINYINT(1) UNSIGNED NULL DEFAULT '1'");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "tags` ADD INDEX `TotalPosts` (`IsEnabled`, `TotalPosts`)");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "tags` CHANGE `Description` `Description` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('CacheHotTags', '')");
	}


	if (VersionCompare('5.9.0', $OldVersion)) {
		if (!empty($DB->query("SHOW COLUMNS FROM `" . DATABASE_PREFIX . "users` LIKE 'NewNotification'"))) {
			$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "users` DROP COLUMN `NewNotification`;");
		}
		if (!empty($DB->query("SHOW COLUMNS FROM `" . DATABASE_PREFIX . "users` LIKE 'NewMention'"))) {
			$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "users` DROP COLUMN `NewMention`;");
		}
		if (!empty($DB->query("SHOW COLUMNS FROM `" . DATABASE_PREFIX . "users` LIKE 'NewReply'"))) {
			$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "users` DROP COLUMN `NewReply`;");
		}
		$DB->query("ALTER TABLE " . DATABASE_PREFIX . "users ADD COLUMN `NewMention` INT (10) UNSIGNED NOT NULL DEFAULT 0 AFTER `NumFavTopics`;");
		$DB->query("ALTER TABLE " . DATABASE_PREFIX . "users ADD COLUMN `NewReply` INT (10) UNSIGNED NOT NULL DEFAULT 0 AFTER `NumFavTopics`;");
		$DB->query("UPDATE " . DATABASE_PREFIX . "users SET NewReply = NewMessage;");
		$DB->query("UPDATE " . DATABASE_PREFIX . "users SET NewMessage = 0;");
		$DB->query("DROP TABLE IF EXISTS `" . DATABASE_PREFIX . "messages`;");
		$DB->query("CREATE TABLE `" . DATABASE_PREFIX . "messages` (
			  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `InboxID` int(10) NOT NULL DEFAULT '0',
			  `UserID` int(10) NOT NULL DEFAULT '0',
			  `Content` longtext NOT NULL,
			  `Time` int(10) unsigned NOT NULL,
			  `IsDel` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`ID`),
			  KEY `Index` (`IsDel`,`InboxID`,`Time`) USING BTREE
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

		$DB->query("DROP TABLE IF EXISTS `" . DATABASE_PREFIX . "inbox`;");
		$DB->query("CREATE TABLE `" . DATABASE_PREFIX . "inbox` (
			`ID` int(10) NOT NULL AUTO_INCREMENT,
			`SenderID` int(10) NOT NULL,
			`SenderName` varchar(50) NOT NULL,
			`ReceiverID` int(10) NOT NULL,
			`ReceiverName` varchar(50) NOT NULL,
			`LastContent` varchar(255) NOT NULL DEFAULT '',
			`LastTime` int(10) NOT NULL DEFAULT '0',
			`IsDel` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`ID`),
			KEY `DialogueID` (`LastTime`) USING BTREE,
			KEY `SenderID` (`SenderID`,`ReceiverID`),
			KEY `ReceiverID` (`ReceiverID`,`SenderID`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "app` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "app_users` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "blogs` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "blogsettings` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "config` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "dict` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "favorites` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "link` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "log` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "notifications` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "pictures` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "postrating` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "posts` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "posttags` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "roles` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "statistics` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "tags` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "topics` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "upload` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "users` ENGINE=InnoDB;");
		$DB->query("ALTER TABLE `" . DATABASE_PREFIX . "vote` ENGINE=InnoDB;");

		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('AllowEditing', 'true');");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('AllowEmptyTags', 'false');");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('AllowNewTopic', 'true');");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('CloseRegistration', 'false');");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('FreezingTime', '0');");
		$DB->query("INSERT INTO `" . DATABASE_PREFIX . "config` VALUES ('PostingInterval', '8');");
	}
	$Message = '升级成功。<br />Update successfully! ';

	$DB->query("UPDATE `" . DATABASE_PREFIX . "config` SET `ConfigValue`='" . $Version . "' WHERE `ConfigName`='Version'");

	$DB->CloseConnection();
	if (!file_exists('update.lock')) {
		touch('update.lock');
	}
	if (file_exists('../install/install.lock')) {
		touch("../install/install.lock");
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
		$Message = 'Your PHP don’t support dom extension, this program does not work! ';
	}
	if (is_file('../config.php')) {
		require("../config.php");
	} else {
		define('ForumLanguage', 'zh-cn');
		define('DBHost', 'localhost');
		define('DBName', 'stack-brain_db');
		define('DBUser', 'root');
	}
}

function VersionCompare($Version, $OldVersion)
{
	$VersionArray = array_map("intval", explode('.', $Version));;
	$OldVersionArray = array_map("intval", explode('.', $OldVersion));
	$NeedToUpdate    = false;
	foreach ($VersionArray as $Key => $Value) {
		if ($VersionArray[$Key] != $OldVersionArray[$Key]) {
			if ($VersionArray[$Key] > $OldVersionArray[$Key]) {
				$NeedToUpdate = true;
			}
			break;
		}
	}
	return $NeedToUpdate;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-cmn-Hans" lang="zh-cmn-Hans">

<head>
	<meta charset="UTF-8" />
	<meta content="True" name="HandheldFriendly" />
	<title>Update - Stack Brain</title>
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
					Stack Brain &raquo; 升级&nbsp;&nbsp;/&nbsp;&nbsp;Update
				</div>
				<div class="main-box">
					<form action="?" method="post">
						<table cellpadding="5" cellspacing="8" border="0" width="100%" class="fs14">
							<tbody>
								<tr>
									<td width="auto" align="center" colspan="2"><span class="red"><?php echo $Message; ?></span></td>
								</tr>
								<?php
								if (!$Message) {
								?>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Language</td>
										<td width="auto" align="left">
											<select name="Language">
												<option value="<?php echo ForumLanguage; ?>">Current Language: <?php echo ForumLanguage; ?></option>
												<option value="zh-cn">简体中文</option>
												<option value="zh-tw">繁體中文</option>
												<option value="en">English</option>
												<option value="ru">Русский</option>
												<option value="pl">polski</option>
											</select>
										</td>
									</tr>
									<tr>
										<td width="280" align="right">/&nbsp;&nbsp;Database Host</td>
										<td width="auto" align="left"><input type="text" name="DBHost" class="sl w200" value="<?php echo DBHost; ?>" /></td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Database Name</td>
										<td width="auto" align="left"><input type="text" name="DBName" class="sl w200" value="<?php echo DBName; ?>" /></td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Database Account</td>
										<td width="auto" align="left"><input type="text" name="DBUser" class="sl w200" value="<?php echo DBUser; ?>" /></td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Database Password</td>
										<td width="auto" align="left"><input type="password" name="DBPassword" class="sl w200" value="" /></td>
									</tr>
									<tr>
										<td colspan="2" class="title">Advanced Settings (Optional)</td>

									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Sphinx Search Server</td>
										<td width="auto" align="left"><input type="text" name="SearchServer" class="sl w200" value="" /></td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Sphinx Search Port</td>
										<td width="auto" align="left"><input type="text" name="SearchPort" class="sl w200" value="" /></td>
									</tr>
									<tr>
										<td width="280" align="right">&nbsp;&nbsp;Enable Cache<br />(Memcached / Redis / XCache)</td>
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
										<td width="auto" align="left"><input type="submit" value="Update" name="submit" class="textbtn" /></td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>
					</form>
				</div>
			</div>
			<!-- main-content end -->
			<div class="main-sider">
				<div class="sider-box">
					<div class="sider-box-title">Upgrade Instructions</div>
					<div class="sider-box-content">
						<p class="red">
							Be sure to back up your database and upload folder first! ! !<br />
							Do not proceed with the upgrade operation without a backup.
						</p>
						<p class="red">
							Please be sure to back up your database and upload folders! ! !<br />
							If there is no backup, please do not proceed with the upgrade operation! ! !
						</p>
						<p>
							If an "Access denied" error appears, it means that the filling is incorrect, please go back and fill in again.
						</p>
						<p>
							If you are using a virtual host with extremely poor performance, you can complete the upgrade on the local computer and upload it to the server (note that the running environment of the local computer must be the same as the server).
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