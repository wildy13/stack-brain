<?php
require(__DIR__ . '/URL.class.php');
class Oauth
{
	const VERSION = "2.0";
	const GET_AUTH_CODE_URL = "https://graph.qq.com/oauth2.0/authorize";
	const GET_ACCESS_TOKEN_URL = "https://graph.qq.com/oauth2.0/token";
	const GET_OPENID_URL = "https://graph.qq.com/oauth2.0/me";
	const GET_USER_INFO_URL = "https://graph.qq.com/user/get_user_info";

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
		//http://wiki.connect.qq.com/%E4%BD%BF%E7%94%A8authorization_code%E8%8E%B7%E5%8F%96access_token
		$RequestParameter = array(
			'response_type' => 'code',
			'client_id' => $AppKey,
			'redirect_uri' => $ . '/oauth-' . $AppID,
			'state' => $SendState,
			'scope' => 'get_user_info,get_info'
		);
		return self::GET_AUTH_CODE_URL . '?' . http_build_query($RequestParameter);
	}


	public function GetAccessToken($WebsitePath, $AppID, $AppSecret, $Code)
	{

		$RequestParameter = array(
			"grant_type" => "authorization_code",
			"client_id" => $this->AppKey,
			"redirect_uri" => $WebsitePath . '/oauth-' . $AppID,
			"client_secret" => $AppSecret,
			"code" => $Code
		);
		$TokenURL         = self::GET_ACCESS_TOKEN_URL . '?' . http_build_query($RequestParameter);
		$Response         = URL::Get($TokenURL);
		if (strpos($Response, "callback") !== false) {
			$LeftBracketPosition  = strpos($Response, "(");
			$RightBracketPosition = strrpos($Response, ")");
			$Response             = substr($Response, $LeftBracketPosition + 1, $RightBracketPosition - $LeftBracketPosition - 1);
			$Msg                  = json_decode($Response, true);
			$this->AccessToken    = null;
			return false;
		} else {
			$Params = array();
			parse_str($Response, $Params);
			$this->AccessToken = $Params["access_token"];
			return true;
		}
	}


	public function GetOpenID()
	{
		$RequestParameter = array(
			"access_token" => $this->AccessToken
		);

		$GraphURL = self::GET_OPENID_URL . '?' . http_build_query($RequestParameter);
		$Response = URL::Get($GraphURL);
		if (strpos($Response, "callback") !== false) {
			$LeftBracketPosition  = strpos($Response, "(");
			$RightBracketPosition = strrpos($Response, ")");
			$Response             = substr($Response, $LeftBracketPosition + 1, $RightBracketPosition - $LeftBracketPosition - 1);
		}
		$UserInfo = json_decode($Response, true);
		if (isset($UserInfo['error'])) {
			$this->OpenID = null;
			return null;
		} else {
			$this->OpenID = $UserInfo['openid'];
			return $UserInfo['openid'];
		}
	}


	public function GetUserInfo()
	{
		$RequestParameter = array(
			"access_token" => $this->AccessToken,
			"oauth_consumer_key" => $this->AppKey,
			"openid" => $this->OpenID,
			"format" => "json"
		);

		$GraphURL = self::GET_USER_INFO_URL . '?' . http_build_query($RequestParameter);
		$Response = URL::Get($GraphURL);

		// http://wiki.connect.qq.com/get_user_info
		$UserInfo = json_decode($Response, true);
		if ($UserInfo['ret'] != 0) {
			return false;
		} else {
			$this->NickName  = $UserInfo['nickname'];
			$this->AvatarURL = $UserInfo['figureurl_qq_2'];
			return true;
		}
	}
}
