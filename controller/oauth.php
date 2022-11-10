<?php
require(LanguagePath . 'oauth.php');

function CheckOpenID()
{
	global $DB, $AppID, $OauthObject, $TimeStamp, $Config, $CurUserID, $Lang;
	$OauthUserID = $DB->single("SELECT UserID FROM " . PREFIX . "app_users 
		WHERE AppID=:AppID AND OpenID = :OpenID", array(
		'AppID' => $AppID,
		'OpenID' => $OauthObject->OpenID
	));
	// The current openid already exists, log in directly
	if ($OauthUserID) {
		$OauthUserInfo               = $DB->row("SELECT * FROM " . PREFIX . "users WHERE ID = :UserID", array(
			"UserID" => $OauthUserID
		));
		$TemporaryUserExpirationTime = 30 * 86400 + $TimeStamp; //Keep login status for 30 days by default
		SetCookies(array(
			'UserID' => $OauthUserID,
			'UserExpirationTime' => $TemporaryUserExpirationTime,
			'UserCode' => md5($OauthUserInfo['Password'] . $OauthUserInfo['Salt'] . $TemporaryUserExpirationTime . SALT)
		), 30);
		Redirect();
	} elseif ($CurUserID) {
		// If logged in, directly bind the current account
		//Insert App user
		if ($DB->query('INSERT INTO `' . PREFIX . 'app_users`
			 (`ID`, `AppID`, `OpenID`, `AppUserName`, `UserID`, `Time`) 
			VALUES (:ID, :AppID, :OpenID, :AppUserName, :UserID, :Time)', array(
			'ID' => null,
			'AppID' => $AppID,
			'OpenID' => $OauthObject->OpenID,
			'AppUserName' => htmlspecialchars($OauthObject->NickName),
			'UserID' => $CurUserID,
			'Time' => $TimeStamp
		))) {
			AlertMsg($Lang['Binding_Success'], $Lang['Binding_Success']);
		} else {
			AlertMsg($Lang['Binding_Failure'], $Lang['Binding_Failure']);
		}
	}
}
$AppID   = intval(Request('Request', 'app_id'));
$AppInfo = $DB->row('SELECT * FROM ' . PREFIX . 'app WHERE ID=:ID', array(
	'ID' => $AppID
));
if (!file_exists(LibraryPath . 'Oauth.' . $AppInfo['AppName'] . '.class.php') || !$AppInfo) {
	AlertMsg('404 Not Found', '404 Not Found', 404);
} else {
	require(LibraryPath . 'Oauth.' . $AppInfo['AppName'] . '.class.php');
	$OauthObject = new Oauth($AppInfo['AppKey']);
}

$Code    = Request('Get', 'code');
$State   = Request('Get', 'state');
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	//If it is not the callback page that the authentication server jumps back to, jump back to the authorization service page
	if (!$Code || !$State || empty($_SESSION[PREFIX . 'OauthState']) || $State != $_SESSION[PREFIX . 'OauthState']) {
		//Generate State value to prevent CSRF
		$SendState                        = md5(uniqid(rand(), TRUE));
		$_SESSION[PREFIX . 'OauthState'] = $SendState;
		// Authorized address
		$AuthorizeURL = Oauth::AuthorizeURL($CurProtocol . $_SERVER['HTTP_HOST'] . $Config['WebsitePath'], $AppID, $AppInfo['AppKey'], $SendState);
		header("HTTP/1.1 301 Moved Permanently");
		header("Status: 301 Moved Permanently");
		header("Location: " . $AuthorizeURL);
		exit();
	}

	$Message = '';
	//The following is the processing of the callback page
	if (!$OauthObject->GetAccessToken($CurProtocol . $_SERVER['HTTP_HOST'] . $Config['WebsitePath'], $AppID, $AppInfo['AppSecret'], $Code)) {
		AlertMsg('400 Bad Request', '400 Bad Request', 400);
	}
	if (!$OauthObject->GetOpenID()) {
		AlertMsg('400 Bad Request', '400 Bad Request', 400);
	}
	// Non-Post page, store Access_Token and OpenID
	$_SESSION[PREFIX . 'OauthAccessToken'] = $OauthObject->AccessToken;
	$_SESSION[PREFIX . 'OauthOpenID'] = $OauthObject->OpenID;
	// Release the session to prevent blocking
	session_write_close();

	$OauthUserID = $DB->single("SELECT UserID FROM " . PREFIX . "app_users 
		WHERE AppID=:AppID AND OpenID = :OpenID", array(
		'AppID' => $AppID,
		'OpenID' => $OauthObject->OpenID
	));
	$OauthObject->GetUserInfo();
	CheckOpenID();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!ReferCheck(Request('Post', 'FormHash')) || empty($_SESSION[PREFIX . 'OauthAccessToken']) || !$State || empty($_SESSION[PREFIX . 'OauthState']) || $State != $_SESSION[PREFIX . 'OauthState']) {
		AlertMsg($Lang['Error_Unknown_Referer'], $Lang['Error_Unknown_Referer'], 403);
	}
	// Read in Access Token and OepnID
	$OauthObject->AccessToken = $_SESSION[PREFIX . 'OauthAccessToken'];
	$OauthObject->OpenID = $_SESSION[PREFIX . 'OauthOpenID'];
	// Release the session to prevent blocking
	session_write_close();
	if (!$OauthObject->OpenID) {
		AlertMsg('400 Bad Request', '400 Bad Request', 400);
	}
	$OauthUserInfo = $OauthObject->GetUserInfo();
	CheckOpenID();
	$UserName = strtolower(Request('Post', 'UserName'));
	if ($UserName && IsName($UserName)) {
		$UserExist = $DB->single("SELECT ID FROM " . PREFIX . "users WHERE UserName = :UserName", array(
			'UserName' => $UserName
		));
		if (!$UserExist) {
			$NewUserSalt     = mt_rand(100000, 999999);
			$NewUserPassword = 'zzz' . substr(md5(md5(mt_rand(1000000000, 2147483647)) . $NewUserSalt), 0, -3);
			$NewUserData     = array(
				'ID' => null,
				'UserName' => $UserName,
				'Salt' => $NewUserSalt,
				'Password' => $NewUserPassword,
				'UserMail' => '',
				'UserHomepage' => '',
				'PasswordQuestion' => '',
				'PasswordAnswer' => '',
				'UserSex' => 0,
				'NumFavUsers' => 0,
				'NumFavTags' => 0,
				'NumFavTopics' => 0,
				'NewReply' => 0,
				'NewMention' => 0,
				'NewMessage' => 0,
				'Topics' => 0,
				'Replies' => 0,
				'Followers' => 0,
				'DelTopic' => 0,
				'GoodTopic' => 0,
				'UserPhoto' => '',
				'UserMobile' => '',
				'UserLastIP' => $CurIP,
				'UserRegTime' => $TimeStamp,
				'LastLoginTime' => $TimeStamp,
				'LastPostTime' => $TimeStamp,
				'BlackLists' => '',
				'UserFriend' => '',
				'UserInfo' => '',
				'UserIntro' => '',
				'UserIM' => '',
				'UserRoleID' => 1,
				'UserAccountStatus' => 1,
				'Birthday' => date("Y-m-d", $TimeStamp)
			);

			$DB->query('INSERT INTO `' . PREFIX . 'users`
				(
					`ID`, `UserName`, `Salt`, `Password`, `UserMail`, 
					`UserHomepage`, `PasswordQuestion`, `PasswordAnswer`, 
					`UserSex`, `NumFavUsers`, `NumFavTags`, `NumFavTopics`, 
					`NewReply`, `NewMention`, `NewMessage`, `Topics`, `Replies`, `Followers`, 
					`DelTopic`, `GoodTopic`, `UserPhoto`, `UserMobile`, 
					`UserLastIP`, `UserRegTime`, `LastLoginTime`, `LastPostTime`, 
					`BlackLists`, `UserFriend`, `UserInfo`, `UserIntro`, `UserIM`, 
					`UserRoleID`, `UserAccountStatus`, `Birthday`
				) 
				VALUES (
					:ID, :UserName, :Salt, :Password, :UserMail, 
					:UserHomepage, :PasswordQuestion, :PasswordAnswer, 
					:UserSex, :NumFavUsers, :NumFavTags, :NumFavTopics, 
					:NewReply, :NewMention, :NewMessage, :Topics, :Replies, :Followers, 
					:DelTopic, :GoodTopic, :UserPhoto, :UserMobile, 
					:UserLastIP, :UserRegTime, :LastLoginTime, :LastPostTime, 
					:BlackLists, :UserFriend, :UserInfo, :UserIntro, :UserIM, 
					:UserRoleID, :UserAccountStatus, :Birthday
				)', $NewUserData);
			$CurUserID = $DB->lastInsertId();
			//Insert App user
			$DB->query('INSERT INTO `' . PREFIX . 'app_users`
				 (`ID`, `AppID`, `OpenID`, `AppUserName`, `UserID`, `Time`) 
				VALUES (:ID, :AppID, :OpenID, :AppUserName, :UserID, :Time)', array(
				'ID' => null,
				'AppID' => $AppID,
				'OpenID' => $OauthObject->OpenID,
				'AppUserName' => htmlspecialchars($OauthObject->NickName),
				'UserID' => $CurUserID,
				'Time' => $TimeStamp
			));
			//Update site-wide statistics
			$NewConfig = array(
				"NumUsers" => $Config["NumUsers"] + 1,
				"DaysUsers" => $Config["DaysUsers"] + 1
			);
			UpdateConfig($NewConfig);
			// set login status
			$TemporaryUserExpirationTime = 30 * 86400 + $TimeStamp;
			SetCookies(array(
				'UserID' => $CurUserID,
				'UserExpirationTime' => $TemporaryUserExpirationTime,
				'UserCode' => md5($NewUserPassword . $NewUserSalt . $TemporaryUserExpirationTime . SALT)
			), 30);
			if ($OauthUserInfo) {
				//Get and zoom avatar
				require(LibraryPath . "ImageResize.class.php");
				$UploadAvatar  = new ImageResize('String', URL::Get($OauthObject->AvatarURL));
				$LUploadResult = $UploadAvatar->Resize(256, 'upload/avatar/large/' . $CurUserID . '.png', 80);
				$MUploadResult = $UploadAvatar->Resize(48, 'upload/avatar/middle/' . $CurUserID . '.png', 90);
				$SUploadResult = $UploadAvatar->Resize(24, 'upload/avatar/small/' . $CurUserID . '.png', 90);
			} else {
				if (extension_loaded('gd')) {
					require(LibraryPath . "MaterialDesign.Avatars.class.php");
					$Avatar = new MDAvtars(mb_substr($UserName, 0, 1, "UTF-8"), 256);
					$Avatar->Save('upload/avatar/large/' . $CurUserID . '.png', 256);
					$Avatar->Save('upload/avatar/middle/' . $CurUserID . '.png', 48);
					$Avatar->Save('upload/avatar/small/' . $CurUserID . '.png', 24);
					$Avatar->Free();
				}
			}
			Redirect();
		} else {
			$Message = $Lang['This_User_Name_Already_Exists'];
		}
	} else {
		$Message = $Lang['UserName_Error'];
	}
}

$DB->CloseConnection();
$PageTitle   = $Lang['Set_Your_Username'];
$ContentFile = $TemplatePath . 'oauth.php';
include($TemplatePath . 'layout.php');
