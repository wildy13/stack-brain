<?php
require(__DIR__ . '/URL.class.php');
// https://developer.github.com/v3/oauth/
class Oauth
{
	const VERSION = "2.0";
	const GET_AUTH_CODE_URL = "https://github.com/login/oauth/authorize";
	const GET_ACCESS_TOKEN_URL = "https://github.com/login/oauth/access_token";
	const GET_OPENID_URL = "https://api.github.com/user";
	const GET_USER_INFO_URL = "https://api.github.com/user";

	private $AppKey;
	public $AccessToken = null;
	public $OpenID = null;
	public $NickName = null;
	public $AvatarURL = null;

	function __construct($AppKey)
	{
		$this->AppKey = $AppKey;
		//$this->GetAccessToken();
		//$this->GetOpenID();
	}


	public static function AuthorizeURL($WebsitePath, $AppID, $AppKey, $SendState)
	{
		var_dump($WebsitePath);
		// https://developer.github.com/v3/oauth_authorizations/
		$RequestParameter = array(
			'client_id' => $AppKey,
			'redirect_uri' => $WebsitePath . '/oauth-' . $AppID,
			'state' => $SendState,
			'scope' => ''
		);
		return self::GET_AUTH_CODE_URL . '?' . http_build_query($RequestParameter);
	}


	public function GetAccessToken($WebsitePath, $AppID, $AppSecret, $Code)
	{
		// request parameter list
		$RequestParameter = array(
			"grant_type" => "authorization_code",
			"client_id" => $this->AppKey,
			"redirect_uri" => $WebsitePath . '/oauth-' . $AppID,
			"client_secret" => $AppSecret,
			"code" => $Code
			// ,"state" => 
		);
		// Construct the url requesting access_token
		$Response         = URL::Post(self::GET_ACCESS_TOKEN_URL, $RequestParameter);
		parse_str($Response, $Params);
		if (empty($Params['access_token'])) {
			$this->AccessToken = null;
			return false;
		} else {
			$this->AccessToken = $Params["access_token"];
			return true;
		}
	}


	public function GetOpenID()
	{
		// request parameter list
		$RequestParameter = array(
			"access_token" => $this->AccessToken
		);
		$Response         = URL::Get(self::GET_OPENID_URL . '?' . http_build_query($RequestParameter));
		$UserInfo         = json_decode($Response, true);
		if ($UserInfo === false || empty($UserInfo['id'])) {
			$this->OpenID = null;
			return null;
		} else {
			$this->OpenID = $UserInfo['id'];
			return $UserInfo['id'];
		}
	}


	public function GetUserInfo()
	{
		// request parameter list
		$RequestParameter = array(
			"access_token" => $this->AccessToken
		);
		$Response         = URL::Get(self::GET_USER_INFO_URL . '?' . http_build_query($RequestParameter));
		$UserInfo = json_decode($Response, true);
		if ($UserInfo === false || empty($UserInfo['id'])) {
			return false;
		} else {
			// save nickname
			$this->NickName  = $UserInfo['login'];
			// save avatar
			$this->AvatarURL = $UserInfo['avatar_url'];
			return true;
		}
	}
}
