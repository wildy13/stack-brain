<?php
require(LanguagePath . 'new.php');
Auth(1, 0, true);

$ErrorCodeList = require(LibraryPath . 'code/new.error.code.php');
$Error     = '';
$ErrorCode = $ErrorCodeList['Default'];
$Title     = '';
$Content   = '';
$TagsArray = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	SetStyle('api', 'API');
	if (!ReferCheck(Request('Post', 'FormHash'))) {
		AlertMsg($Lang['Error_Unknown_Referer'], $Lang['Error_Unknown_Referer'], 403);
	}
	$Title     = Request('Post', 'Title');
	$Content   = Request('Post', 'Content');
	$TagsArray = isset($_POST['Tag']) ? $_POST['Tag'] : array();
	do {
		if ($Config['AllowNewTopic'] === 'false' && $CurUserRole < 3) {
			$Error     = $Lang['Prohibited_New_Topic'];
			$ErrorCode = $ErrorCodeList['Prohibited_New_Topic'];
			break;
		}

		//Post at least 8 seconds apart
		if (DEBUG_MODE === false && ($CurUserRole < 3 && ($TimeStamp - intval($CurUserInfo['LastPostTime'])) <= intval($Config['PostingInterval']))) {
			$Error     = $Lang['Posting_Too_Often'];
			$ErrorCode = $ErrorCodeList['Posting_Too_Often'];
			break;
		}

		if (!$Title) {
			$Error     = $Lang['Title_Empty'];
			$ErrorCode = $ErrorCodeList['Title_Empty'];
			break;
		}


		if (strlen($Title) > $Config['MaxTitleChars'] || strlen($Content) > $Config['MaxPostChars']) {
			$Error     = str_replace('{{MaxPostChars}}', $Config['MaxPostChars'], str_replace('{{MaxTitleChars}}', $Config['MaxTitleChars'], $Lang['Too_Long']));
			$ErrorCode = $ErrorCodeList['Too_Long'];
			break;
		}


		$TagsArray = TagsDiff($TagsArray, array());
		if ($Config['AllowEmptyTags'] !== 'true' && (empty($TagsArray) || in_array('', $TagsArray) || count($TagsArray) > $Config["MaxTagsNum"])) {
			$Error     = $Lang['Tags_Empty'];
			$ErrorCode = $ErrorCodeList['Tags_Empty'];
			break;
		}


		// Content Filtering System
		$TitleFilterResult = Filter($Title);
		$ContentFilterResult = Filter($Content);
		$GagTime = ($TitleFilterResult['GagTime'] > $ContentFilterResult['GagTime']) ? $TitleFilterResult['GagTime'] : $ContentFilterResult['GagTime'];
		$GagTime = $CurUserRole < 3 ? $GagTime : 0;
		$Prohibited = $TitleFilterResult['Prohibited'] | $ContentFilterResult['Prohibited'];
		if ($Prohibited) {
			$Error     = $Lang['Prohibited_Content'];
			$ErrorCode = $ErrorCodeList['Prohibited_Content'];
			if ($GagTime) {
				// Mute the user $GagTime seconds
				UpdateUserInfo(array(
					"LastPostTime" => $TimeStamp + $GagTime
				));
			}
			break;
		}

		$Title = $TitleFilterResult['Content'];
		$Content = $ContentFilterResult['Content'];
		try {
			$DB->beginTransaction();
			//Get existing tags
			if (!empty($TagsArray)) {
				$TagsExistArray = $DB->query("SELECT ID, Name FROM `" . PREFIX . "tags` WHERE `Name` IN (?)", $TagsArray);
			} else {
				$TagsExistArray = array();
			}
			$TagsExist      = ArrayColumn($TagsExistArray, 'Name');
			$TagsID         = ArrayColumn($TagsExistArray, 'ID');
			$NewTags        = TagsDiff($TagsArray, $TagsExist);
			//Create a new label that does not exist
			if ($NewTags) {
				foreach ($NewTags as $Name) {
					$DB->query("INSERT INTO `" . PREFIX . "tags` 
						(`ID`, `Name`,`Followers`,`Icon`,`Description`, `IsEnabled`, `TotalPosts`, `MostRecentPostTime`, `DateCreated`) 
						VALUES (?,?,?,?,?,?,?,?,?)", array(
						null,
						$Name,
						0,
						0,
						null,
						1,
						1,
						$TimeStamp,
						$TimeStamp
					));
					$TagsID[] = $DB->lastInsertId();
				}
				//Update site-wide statistics
				$NewConfig = array(
					"NumTags" => $Config["NumTags"] + count($NewTags)
				);
			}
			$TagsArray      = array_merge($TagsExist, $NewTags);
			//Insert data into Topics table
			$TopicData      = array(
				"ID" => null,
				"Topic" => htmlspecialchars($Title),
				"Tags" => implode("|", $TagsArray),
				"UserID" => $CurUserID,
				"UserName" => $CurUserName,
				"LastName" => "",
				"PostTime" => $TimeStamp,
				"LastTime" => $TimeStamp,
				"IsGood" => 0,
				"IsTop" => 0,
				"IsLocked" => 0,
				"IsDel" => 0,
				"IsVote" => 0,
				"Views" => 0,
				"Replies" => 0,
				"Favorites" => 0,
				"RatingSum" => 0,
				"TotalRatings" => 0,
				"LastViewedTime" => 0,
				"PostsTableName" => null,
				"ThreadStyle" => "",
				"Lists" => "",
				"ListsTime" => $TimeStamp,
				"Log" => ""
			);
			$NewTopicResult = $DB->query("INSERT INTO `" . PREFIX . "topics` 
				(
					`ID`, 
					`Topic`, 
					`Tags`, 
					`UserID`, 
					`UserName`, 
					`LastName`, 
					`PostTime`, 
					`LastTime`, 
					`IsGood`, 
					`IsTop`, 
					`IsLocked`, 
					`IsDel`, 
					`IsVote`, 
					`Views`, 
					`Replies`, 
					`Favorites`, 
					`RatingSum`, 
					`TotalRatings`, 
					`LastViewedTime`, 
					`PostsTableName`, 
					`ThreadStyle`, 
					`Lists`, 
					`ListsTime`, 
					`Log`
				) 
				VALUES 
				(
					:ID,
					:Topic,
					:Tags,
					:UserID,
					:UserName,
					:LastName,
					:PostTime,
					:LastTime,
					:IsGood,
					:IsTop,
					:IsLocked,
					:IsDel,
					:IsVote,
					:Views,
					:Replies,
					:Favorites,
					:RatingSum,
					:TotalRatings,
					:LastViewedTime,
					:PostsTableName,
					:ThreadStyle,
					:Lists,
					:ListsTime,
					:Log
				)", $TopicData);

			$TopicID       = $DB->lastInsertId();
			//Insert data into Posts table
			$PostData      = array(
				"ID" => null,
				"TopicID" => $TopicID,
				"IsTopic" => 1,
				"UserID" => $CurUserID,
				"UserName" => $CurUserName,
				"Subject" => CharCV($Title),
				"Content" => XssEscape($Content),
				"PostIP" => $CurIP,
				"PostTime" => $TimeStamp
			);
			$NewPostResult = $DB->query("INSERT INTO `" . PREFIX . "posts` 
				(`ID`, `TopicID`, `IsTopic`, `UserID`, `UserName`, `Subject`, `Content`, `PostIP`, `PostTime`) 
				VALUES (:ID,:TopicID,:IsTopic,:UserID,:UserName,:Subject,:Content,:PostIP,:PostTime)", $PostData);

			$PostID = $DB->lastInsertId();

			if ($NewTopicResult && $NewPostResult) {
				//Update site-wide statistics
				$NewConfig = array(
					"NumTopics" => $Config["NumTopics"] + 1,
					"DaysTopics" => $Config["DaysTopics"] + 1
				);
				UpdateConfig($NewConfig);
				//Update user's own statistics
				UpdateUserInfo(array(
					"Topics" => $CurUserInfo['Topics'] + 1,
					"LastPostTime" => $TimeStamp + $GagTime
				));
				//Tag the post tag corresponding to the attachment
				$DB->query("UPDATE `" . PREFIX . "upload` SET PostID=? WHERE `PostID`=0 and `UserName`=?", array(
					$PostID,
					$CurUserName
				));
				//Correspondence between record labels and TopicIDs
				foreach ($TagsID as $TagID) {
					$DB->query("INSERT INTO `" . PREFIX . "posttags` 
						(`TagID`, `TopicID`, `PostID`) 
						VALUES (?,?,?)", array(
						$TagID,
						$TopicID,
						$PostID
					));
				}
				//Update Tag Statistics
				if ($TagsExist) {
					$DB->query("UPDATE `" . PREFIX . "tags` SET TotalPosts=TotalPosts+1, MostRecentPostTime=" . $TimeStamp . " WHERE `Name` in (?)", $TagsExist);
				}
				//Add reminder message
				AddingNotifications($Content, $TopicID, $PostID);
				//Clear the homepage memory cache
				if ($MCache) {
					$MCache->delete(MemCachePrefix . 'Homepage');
				}
				//Jump to topic page
			}
			$DB->commit();
		} catch (Exception $ex) {
			$DB->rollBack();
			$Error     = $Lang['Posting_Too_Often'];
			$ErrorCode = $ErrorCodeList['Posting_Too_Often'];
		}
	} while (false);
}
$DB->CloseConnection();
$PageTitle   = $Lang['Create_New_Topic'];
$ContentFile = $TemplatePath . 'new.php';
include($TemplatePath . 'layout.php');
