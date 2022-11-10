<?php
require(__DIR__ . '/URL.class.php');
// http://open.weibo.com/wiki/%E6%8E%88%E6%9D%83%E6%9C%BA%E5%88%B6
class Oauth
{
	const VERSION = "2.0";
	const GET_AUTH_CODE_URL = "https://api.weibo.com/oauth2/authorize";
	const GET_ACCESS_TOKEN_URL = "https://api.weibo.com/oauth2/access_token";
	const GET_OPENID_URL = "https://api.weibo.com/oauth2/get_token_info";
	const GET_USER_INFO_URL = "https://api.weibo.com/2/users/show.json";

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
		// http://open.weibo.com/wiki/Oauth2/authorize
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
		// http://open.weibo.com/wiki/Oauth2/access_token
		$RequestParameter = array(
			"grant_type" => "authorization_code",
			"client_id" => $this->AppKey,
			"redirect_uri" => $WebsitePath . '/oauth-' . $AppID,
			"client_secret" => $AppSecret,
			"code" => $Code
		);
		$Response         = URL::Post(self::GET_ACCESS_TOKEN_URL, $RequestParameter);
		$Params           = json_decode($Response, true);
		if ($Params === false || empty($Params['access_token'])) {
			$this->AccessToken = null;
			return false;
		} else {
			$this->AccessToken = $Params["access_token"];
			return true;
		}
	}


	public function GetOpenID()
	{
		$RequestParameter = array(
			"access_token" => $this->AccessToken
		);
		$Response         = URL::Post(self::GET_OPENID_URL, $RequestParameter);
		$UserInfo         = json_decode($Response, true);
		if ($UserInfo === false || empty($UserInfo['uid'])) {
			$this->OpenID = null;
			return null;
		} else {
			$this->OpenID = $UserInfo['uid'];
			return $UserInfo['uid'];
		}
	}


	public function GetUserInfo()
	{
		$RequestParameter = array(
			//"source" => $this->AppKey,
			"access_token" => $this->AccessToken,
			"uid" => $this->OpenID
		);

		$GraphURL = self::GET_USER_INFO_URL . '?' . http_build_query($RequestParameter);
		$Response = URL::Get($GraphURL);

		// 
		$UserInfo = json_decode($Response, true);
		if ($UserInfo === false || empty($UserInfo['id'])) {
			return false;
		} else {
			$this->NickName  = $UserInfo['screen_name'];
			$this->AvatarURL = $UserInfo['avatar_hd'];
			return true;
		}
	}
}
