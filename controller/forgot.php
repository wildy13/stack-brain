<?php
require(LanguagePath . 'forgot.php');
$Message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$UserName   = strtolower(Request('Post', 'UserName'));
	$Email      = strtolower(Request('Post', 'Email'));
	$VerifyCode = intval(Request('Post', 'VerifyCode'));
	$UserInfo   = array();
	if (!ReferCheck(Request('Post', 'FormHash'))) {
		AlertMsg($Lang['Error_Unknown_Referer'], $Lang['Error_Unknown_Referer'], 403);
	}
	if ($UserName && $Email && $VerifyCode) {
		session_start();
		$Session_VerifyCode = isset($_SESSION[PREFIX . 'VerificationCode']) ? intval($_SESSION[PREFIX . 'VerificationCode']) : '';
		unset($_SESSION[PREFIX . 'VerificationCode']);
		session_write_close();
		if ($VerifyCode === $Session_VerifyCode) {
			$UserInfo = $DB->row('SELECT * FROM ' . PREFIX . 'users 
				Where UserName=:UserName', array(
				'UserName' => $UserName
			));
			if ($UserInfo) {
				if ($Email === $UserInfo['UserMail']) {
					//Generate Access Token valid for 2 hours
					$TokenExpirationTime = 7200 + $TimeStamp;
					$AccessToken         = base64_encode($UserName . '|' . $TokenExpirationTime . '|' . md5($UserInfo['Password'] . $UserInfo['Salt'] . md5($TokenExpirationTime) . md5(SALT)));
					$ResetPasswordURL    = $CurProtocol . $Config['MainDomainName'] . $Config['WebsitePath'] . '/reset_password/' . $AccessToken;
					//Send an email to the secure mailbox in the database
					require(LibraryPath . 'PHPMailer.smtp.class.php');
					require(LibraryPath . 'PHPMailer.class.php');
					$MailObject = new PHPMailer;

					$MailObject->isSMTP(); // Set mailer to use SMTP
					$MailObject->CharSet    = "utf-8"; //Set character set encoding
					$MailObject->Host       = $Config['SMTPHost']; // Specify main and backup SMTP servers
					$MailObject->SMTPAuth   = ($Config['SMTPAuth'] === 'true' ? true : false);
					$MailObject->Username   = $Config['SMTPUsername']; // SMTP username
					$MailObject->Password   = $Config['SMTPPassword']; // SMTP password
					$MailObject->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
					$MailObject->Port       = intval($Config['SMTPPort']); // TCP port to connect to

					$MailObject->From     = $Config['SMTPUsername'];
					$MailObject->FromName = $Config['SiteName'];
					$MailObject->addAddress($UserInfo['UserMail'], $UserName); // Add a recipient

					$MailObject->isHTML(true); // Set email format to HTML

					$MailObject->Subject = str_replace('{{UserName}}', $UserName, str_replace('{{SiteName}}', $Config['SiteName'], $Lang['Mail_Template_Subject']));
					$MailObject->Body    = str_replace('{{UserName}}', $UserName, str_replace('{{ResetPasswordURL}}', $ResetPasswordURL, $Lang['Mail_Template_Body']));
					if (!$MailObject->send()) {
						$Message = $Lang['Email_Could_Not_Be_Sent'] . 'Mailer Error: ' . $MailObject->ErrorInfo;
					} else {
						$Message = $Lang['Email_Has_Been_Sent'];
					}
				} else {
					$UserMail = preg_replace('/([\w\-\.]{1})([\w\-\.]{0,})@([\w\-\.]+(\.\w+)+)$/', '\1*****@\3', $UserInfo['UserMail']);
					$Message  = str_replace('{{UserMail}}', $UserMail, $Lang['Email_Error']);
				}
			} else {
				$Message = $Lang['User_Does_Not_Exist'];
			}
		} else {
			$Message = $Lang['Verification_Code_Error'];
		}
	} else {
		$Message = $Lang['Forms_Can_Not_Be_Empty'];
	}
}


$DB->CloseConnection();
$PageTitle   = $Lang['Forgot_Password'];
$ContentFile = $TemplatePath . 'forgot.php';
include($TemplatePath . 'layout.php');
