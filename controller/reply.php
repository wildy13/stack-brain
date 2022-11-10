<?php
require(LanguagePath . 'reply.php');
SetStyle('api', 'API');
Auth(1, 0, true);

$ErrorCodeList = require(LibraryPath . 'code/new.error.code.php');
$Error = '';
$ErrorCode = $ErrorCodeList['Default'];
$TopicID = intval(Request('Post', 'TopicID'));
$Content = '';

$Topic = $DB->row("SELECT * FROM " . PREFIX . "topics WHERE ID=?", array(
	$TopicID
));
if (!$Topic || ($Topic['IsDel'] && $CurUserRole < 3)) {
	AlertMsg('404 NOT FOUND', '404 NOT FOUND', 404);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!ReferCheck(Request('Post', 'FormHash'))) {
		AlertMsg($Lang['Error_Unknown_Referer'], $Lang['Error_Unknown_Referer'], 403);
	}
	do {
		if ($Topic['IsLocked'] && $CurUserRole < 3) {
			$Error = $Lang['Topic_Has_Been_Locked'];
			$ErrorCode = $ErrorCodeList['Topic_Has_Been_Locked'];
			break;
		}

		if (DEBUG_MODE === false && ($CurUserRole < 3 && ($TimeStamp - intval($CurUserInfo['LastPostTime'])) <= intval($Config['PostingInterval']))) {
			$Error = $Lang['Posting_Too_Often'];
			$ErrorCode = $ErrorCodeList['Posting_Too_Often'];
			break;
		}


		$Content = Request('Post', 'Content');
		if (!$Content) {
			$Error = $Lang['Content_Empty'];
			$ErrorCode = $ErrorCodeList['Too_Long'];
			break;
		}


		if (strlen($Content) > $Config['MaxPostChars']) {
			$Error = str_replace('{{MaxPostChars}}', $Config['MaxPostChars'], $Lang['Too_Long']);
			$ErrorCode = $ErrorCodeList['Too_Long'];
			break;
		}


		$ContentFilterResult = Filter($Content);
		$GagTime = $CurUserRole < 3 ? $ContentFilterResult['GagTime'] : 0;
		$Prohibited = $ContentFilterResult['Prohibited'];
		if ($Prohibited) {
			$Error = $Lang['Prohibited_Content'];
			$ErrorCode = $ErrorCodeList['Prohibited_Content'];
			if ($GagTime) {
				UpdateUserInfo(array(
					"LastPostTime" => $TimeStamp + $GagTime
				));
			}
			break;
		}
		$Content = $ContentFilterResult['Content'];

		try {
			$DB->beginTransaction();
			$PostData = array(
				"ID" => null,
				"TopicID" => $TopicID,
				"IsTopic" => 0,
				"UserID" => $CurUserID,
				"UserName" => $CurUserName,
				"Subject" => $Topic['Topic'],
				"Content" => XssEscape($Content),
				"PostIP" => $CurIP,
				"PostTime" => $TimeStamp,
				"IsDel" => 0
			);
			$NewPostResult = $DB->query("INSERT INTO `" . PREFIX . "posts`
                (`ID`, `TopicID`, `IsTopic`, `UserID`, `UserName`, `Subject`, `Content`, `PostIP`, `PostTime`, `IsDel`) 
                VALUES (:ID,:TopicID,:IsTopic,:UserID,:UserName,:Subject,:Content,:PostIP,:PostTime,:IsDel)", $PostData);

			$PostID = $DB->lastInsertId();

			if ($NewPostResult) {
				$NewConfig = array(
					"NumPosts" => $Config["NumPosts"] + 1,
					"DaysPosts" => $Config["DaysPosts"] + 1
				);
				UpdateConfig($NewConfig);
				$DB->query("UPDATE `" . PREFIX . "topics` SET Replies=Replies+1,LastTime=?,LastName=? WHERE `ID`=?", array(
					($TimeStamp > $Topic['LastTime']) ? $TimeStamp : $Topic['LastTime'],
					$CurUserName,
					$TopicID
				));
				UpdateUserInfo(array(
					"Replies" => $CurUserInfo['Replies'] + 1,
					"LastPostTime" => $TimeStamp + $GagTime
				));
				$DB->query("UPDATE `" . PREFIX . "upload` SET PostID=? WHERE `PostID`=0 and `UserName`=?", array(
					$PostID,
					$CurUserName
				));
				AddingNotifications($Content, $TopicID, $PostID, $Topic['UserName']);
				if ($CurUserID != $Topic['UserID']) {
					$DB->query('INSERT INTO `' . PREFIX . 'notifications`
                    (`ID`, `UserID`, `UserName`, `Type`, `TopicID`, `PostID`, `Time`, `IsRead`) 
                    VALUES (NULL,?,?,?,?,?,?,?)', array(
						$Topic['UserID'],
						$CurUserName,
						1,
						$TopicID,
						$PostID,
						$TimeStamp,
						0
					));
					$DB->query('UPDATE `' . PREFIX . 'users` SET `NewReply` = `NewReply`+1 WHERE ID = :UserID', array(
						'UserID' => $Topic['UserID']
					));
					if ($MCache) {
						$MCache->delete(MemCachePrefix . 'UserInfo_' . $Topic['UserID']);
					}
				}
				if ($MCache) {
					$MCache->delete(MemCachePrefix . 'Homepage');
					$MCache->delete(MemCachePrefix . 'Topic_' . $TopicID);
				}
				$TotalPage = ceil(($Topic['Replies'] + 2) / $Config['PostsPerPage']);
			}
			$DB->commit();
		} catch (Exception $ex) {
			$DB->rollBack();
			$Error = $Lang['Posting_Too_Often'];
			$ErrorCode = $ErrorCodeList['Posting_Too_Often'];
		}
	} while (false);
}
$DB->CloseConnection();

$PageTitle = 'Reply';
$ContentFile = $TemplatePath . 'reply.php';
include($TemplatePath . 'layout.php');
