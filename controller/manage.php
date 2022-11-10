<?php
require(LanguagePath . 'manage.php');
SetStyle('api', 'API');

$ID     = intval(Request('Post', 'ID', 0));
$Type   = intval(Request('Post', 'Type', 0));
$Action = Request('Post', 'Action', false);

$TypeList         = array(
	1 => 'topic',
	2 => 'post',
	3 => 'user',
	4 => 'favorite',
	5 => 'tag'
);
//FavoriteType: 1:Topic 2:Tag 3:User 4:Post 5:Blog
$FavoriteTypeList = array(
	1 => 'Topic',
	2 => 'Tag',
	3 => 'User',
	4 => 'Post',
	5 => 'Blog'
);

$Manage = new Manage($ID, $Action);
if (empty($TypeList[$Type]) || ($Type == 4 && empty($FavoriteTypeList[$Action]))) {
	AlertMsg('Action or Type error. Bad Request', 'Action or Type error. Bad Request');
}
$ManageMethod = $TypeList[$Type] . ($Type == 4 ? $FavoriteTypeList[$Action] : $Action);
if (method_exists($Manage, $ManageMethod)) {
	$ManageInfo = $Manage->getManageInfo($TypeList[$Type]);
	if ($Type != 4 && empty($ManageInfo)) {
		AlertMsg('Resource Not Found', 'Resource Not Found');
	}
	$Manage->$ManageMethod($ManageInfo);
	$Message = $Manage->message;
}
/**
 * @property MemcacheMod mCache
 * @property DB db
 */
class Manage
{
	private $id;
	private $action;
	private $db;
	private $lang;
	private $config;
	private $mCache;
	public $message = '';

	public function __construct($id, $action)
	{
		global $DB, $Lang, $Config, $MCache;
		$this->id     = $id;
		$this->action = $action;
		$this->db     = $DB;
		$this->lang   = $Lang;
		$this->config = $Config;
		$this->mCache = $MCache;
	}

	// Get information about a project to manage
	public function getManageInfo($manageType)
	{
		global $CurUserID;
		switch ($manageType) {
			case 'topic':
				return $this->db->row("SELECT * FROM " . PREFIX . "topics force index(PRI) WHERE ID=:ID", array(
					"ID" => $this->id
				));
				break;
			case 'post':
				return $this->db->row("SELECT * FROM " . PREFIX . "posts force index(PRI) WHERE ID=:ID", array(
					"ID" => $this->id
				));
				break;
			case 'user':
				return $this->db->row("SELECT * FROM " . PREFIX . "users force index(PRI) WHERE ID=:ID", array(
					"ID" => $this->id
				));
				break;
			case 'favorite':
				return $this->db->single("SELECT ID FROM " . PREFIX . "favorites WHERE UserID=:UserID and Type=:Type and FavoriteID=:FavoriteID", array(
					'UserID' => $CurUserID,
					'Type' => $this->action,
					'FavoriteID' => $this->id
				));
				break;
			case 'tag':
				return $this->db->row("SELECT * FROM " . PREFIX . "tags WHERE ID=:ID", array(
					"ID" => $this->id
				));
				break;
			default:
				AlertMsg('Bad Request', 'Bad Request');
				return [];
				break;
		}
	}

	//Clear theme cache
	private function refreshTopicCache()
	{
		if ($this->mCache) {
			//Clear the homepage memory cache
			$this->mCache->delete(MemCachePrefix . 'Homepage');
			//Clear theme cache
			$this->mCache->delete(MemCachePrefix . 'Topic_' . $this->id);
		}
	}

	// Clean up attachments when deleting posts
	private function deleteUpload($uploadRecordList)
	{
		foreach ($uploadRecordList as $uploadRecord) {
			$numberDuplicateFiles = $this->db->single('SELECT count(*) FROM ' . PREFIX . 'upload 
					WHERE 
						FileSize = ? AND
						MD5 = ? AND 
						SHA1 = ?
						', array(
				$uploadRecord['FileSize'],
				$uploadRecord['MD5'],
				$uploadRecord['SHA1']
			));
			$rootPath = $_SERVER['DOCUMENT_ROOT'];
			if (substr($uploadRecord['FilePath'], 0, 1) != '/') {
				$uploadRecord['FilePath'] = '/' . $uploadRecord['FilePath'];
			}
			if ($numberDuplicateFiles <= 1) {
				unlink($rootPath . $uploadRecord['FilePath']);
			}
		}
		$UploadIDs = ArrayColumn($uploadRecordList, 'ID');
		if ($UploadIDs) {
			$this->db->query('DELETE FROM ' . PREFIX . 'upload 
				WHERE ID IN (?)', $UploadIDs);
		}
	}

	// Move topic to trash
	public function topicDelete($TopicInfo)
	{
		Auth(4);
		if ($TopicInfo['IsDel'] == 0) {
			$this->db->query("UPDATE " . PREFIX . "topics SET IsDel = 1 WHERE ID=:ID", array(
				"ID" => $this->id
			));
			$NewConfig = array(
				"NumTopics" => $this->config["NumTopics"] - 1
			);
			UpdateConfig($NewConfig);
			$this->db->query("UPDATE `" . PREFIX . "users` SET Topics=Topics-1 WHERE `ID`=?", array(
				$TopicInfo['UserID']
			));
			if ($TopicInfo['Tags']) {
				$this->db->query("UPDATE `" . PREFIX . "tags` SET TotalPosts=TotalPosts-1 WHERE `Name` in (?)", explode('|', $TopicInfo['Tags']));
			}
			$this->message = $this->lang['Deleted'];
		} else {
			AlertMsg('Bad Request', $this->lang['Deleted']);
		}
		$this->refreshTopicCache();
	}

	// Restoring themes from the recycle bin
	public function topicRecover($TopicInfo)
	{
		Auth(4);
		if ($TopicInfo['IsDel'] == 1) {
			$this->db->query("UPDATE " . PREFIX . "topics SET IsDel = 0 WHERE ID=:ID", array(
				"ID" => $this->id
			));
			$NewConfig = array(
				"NumTopics" => $this->config["NumTopics"] + 1
			);
			UpdateConfig($NewConfig);
			$this->db->query("UPDATE `" . PREFIX . "users` SET Topics=Topics+1 WHERE `ID`=?", array(
				$TopicInfo['UserID']
			));
			if ($TopicInfo['Tags']) {
				$this->db->query("UPDATE `" . PREFIX . "tags` SET TotalPosts=TotalPosts+1 WHERE `Name` in (?)", explode('|', $TopicInfo['Tags']));
			}
			$this->message = $this->lang['Recovered'];
		} else {
			AlertMsg('Bad Request', $this->lang['Failure_Recovery']);
		}
		$this->refreshTopicCache();
	}

	// Restoring themes from the recycle bin
	public function topicPermanentlyDelete($TopicInfo)
	{
		Auth(5);
		if ($TopicInfo['IsDel'] == 1) {
			$this->deleteUpload($this->db->query(
				"SELECT * FROM `" . PREFIX . "upload` 
				WHERE `PostID` IN (SELECT ID FROM `" . PREFIX . "posts` WHERE TopicID = ?)",
				array(
					$this->id
				)
			));
			$this->db->query('DELETE FROM `' . PREFIX . 'posttags` WHERE TopicID = ?', array(
				$this->id
			));
			$this->db->query('DELETE FROM `' . PREFIX . 'posts` WHERE TopicID = ?', array(
				$this->id
			));
			$this->db->query('DELETE FROM `' . PREFIX . 'topics` WHERE ID = ?', array(
				$this->id
			));
			$this->db->query('DELETE FROM `' . PREFIX . 'notifications` WHERE TopicID = ?', array(
				$this->id
			));
			$this->message = $this->lang['Permanently_Deleted'];
		} else {
			AlertMsg('Bad Request', $this->lang['Failure_Permanent_Deletion']);
		}
		$this->refreshTopicCache();
	}

	// theme sinking
	public function topicSink($TopicInfo)
	{
		Auth(4);
		$this->db->query("UPDATE " . PREFIX . "topics SET LastTime = LastTime-604800 WHERE ID=:ID", array(
			"ID" => $this->id
		));
		$this->message = $this->lang['Sunk'];
		$this->refreshTopicCache();
	}

	// topic floating
	public function topicRise($TopicInfo)
	{
		Auth(4);
		$this->db->query("UPDATE " . PREFIX . "topics SET LastTime = LastTime+604800 WHERE ID=:ID", array(
			"ID" => $this->id
		));
		$this->message = $this->lang['Risen'];
		$this->refreshTopicCache();
	}

	// locking / Unlock themes
	public function topicLock($TopicInfo)
	{
		Auth(4);
		$this->db->query("UPDATE " . PREFIX . "topics SET IsLocked = :IsLocked WHERE ID=:ID", array(
			"ID" => $this->id,
			"IsLocked" => $TopicInfo['IsLocked'] ? 0 : 1
		));
		$this->message = $TopicInfo['IsLocked'] ? $this->lang['Lock'] : $this->lang['Unlock'];
		$this->refreshTopicCache();
	}

	//delete thread from post
	public function topicDeleteTag($TopicInfo)
	{
		Auth(4, $TopicInfo['UserID'], true);
		$TagName = Request('Post', 'TagName');
		if ((count(explode('|', $TopicInfo['Tags'])) - 1) >= 1 && $this->db->query("DELETE FROM `" . PREFIX . "posttags` 
					WHERE TopicID = ? AND TagID = (SELECT ID FROM `" . PREFIX . "tags` WHERE Name = ?)", array(
			$this->id,
			$TagName
		))) {
			$this->db->query("UPDATE `" . PREFIX . "tags` SET TotalPosts=TotalPosts-1 WHERE `Name`=?", array(
				$TagName
			));
			$this->db->query("UPDATE `" . PREFIX . "topics` SET Tags=? WHERE `ID`=?", array(
				implode('|', TagsDiff(explode('|', $TopicInfo['Tags']), array(
					$TagName
				))),
				$this->id
			));
			$this->message = 'Success';
		} else {
			AlertMsg('Bad Request', 'Bad Request');
		}
		$this->refreshTopicCache();
	}

	// add topic to topic
	public function topicAddTag($TopicInfo)
	{
		global $TimeStamp;
		Auth(4, $TopicInfo['UserID'], true);
		$TagName = TagsDiff(array(
			Request('Post', 'TagName')
		), array());
		if ($TagName && !in_array($TagName[0], explode('|', $TopicInfo['Tags'])) && (count(explode('|', $TopicInfo['Tags'])) + 1) <= $this->config["MaxTagsNum"]) {
			$TagName   = $TagName[0];
			$TagsExist = $this->db->row("SELECT ID,Name FROM `" . PREFIX . "tags` WHERE `Name` = ?", array(
				$TagName
			));
			if (!$TagsExist) {
				$this->db->query("INSERT INTO `" . PREFIX . "tags` 
							(`ID`, `Name`,`Followers`,`Icon`,`Description`, `IsEnabled`, `TotalPosts`, `MostRecentPostTime`, `DateCreated`) 
							VALUES (?,?,?,?,?,?,?,?,?)", array(
					null,
					htmlspecialchars(trim($TagName)),
					0,
					0,
					null,
					1,
					1,
					$TimeStamp,
					$TimeStamp
				));
				$TagID = $this->db->lastInsertId();
				if ($TagID) {
					$this->db->query("INSERT INTO `" . PREFIX . "posttags` 
								(`TagID`, `TopicID`, `PostID`) 
								VALUES (" . $TagID . ", " . $this->id . ", (SELECT ID FROM `" . PREFIX . "posts` WHERE TopicID = " . $this->id . " AND IsTopic = 1 LIMIT 1))");
					$NewConfig = array(
						"NumTags" => $this->config["NumTags"] + 1
					);
					UpdateConfig($NewConfig);
				}
			} else {
				if ($this->db->query("INSERT INTO `" . PREFIX . "posttags` 
							(`TagID`, `TopicID`, `PostID`) 
							VALUES (" . $TagsExist['ID'] . ", " . $this->id . ", (SELECT ID FROM `" . PREFIX . "posts` WHERE TopicID = " . $this->id . " AND IsTopic = 1 LIMIT 1))")) {
					$this->db->query("UPDATE `" . PREFIX . "tags` SET TotalPosts=TotalPosts+1 WHERE `Name`=?", array(
						$TagName
					));
				}
			}
			$this->db->query("UPDATE `" . PREFIX . "topics` SET Tags=? WHERE `ID`=?", array(
				implode('|', $TopicInfo['Tags'] ? array_merge(explode('|', $TopicInfo['Tags']), array(
					$TagName
				)) : array(
					$TagName
				)),
				$this->id
			));
			$this->message = 'Success';
		} else {
			AlertMsg('Bad Request', 'Bad Request');
		}
		$this->refreshTopicCache();
	}

	// delete post
	public function postDelete($PostInfo)
	{
		Auth(4);
		$this->db->query('DELETE FROM `' . PREFIX . 'posts` WHERE ID=?', array(
			$this->id
		));
		$this->db->query('DELETE FROM `' . PREFIX . 'notifications` WHERE PostID=?', array(
			$this->id
		));
		$NewConfig = array(
			"NumPosts" => $this->config["NumPosts"] - 1
		);
		UpdateConfig($NewConfig);
		$this->db->query("UPDATE `" . PREFIX . "topics` SET Replies=Replies-1 WHERE `ID`=?", array(
			$PostInfo['TopicID']
		));
		$this->db->query("UPDATE `" . PREFIX . "users` SET Replies=Replies-1 WHERE `ID`=?", array(
			$PostInfo['UserID']
		));
		$this->deleteUpload($this->db->query("SELECT * FROM `" . PREFIX . "upload` WHERE `PostID` = ?", array(
			$PostInfo['ID']
		)));
		$this->message = $this->lang['Permanently_Deleted'];
	}

	// edit post
	public function postEdit($PostInfo)
	{
		global $CurUserRole, $CurUserName, $TimeStamp;
		if ($this->config['AllowEditing'] === 'true') {
			Auth(4, $PostInfo['UserID'], true);
		} else {
			Auth(4);
		}
		$Content             = XssEscape(Request('Post', 'Content', $PostInfo['Content']));
		$ContentFilterResult = Filter($Content);
		$GagTime             = $CurUserRole < 3 ? $ContentFilterResult['GagTime'] : 0;
		$Prohibited          = $ContentFilterResult['Prohibited'];
		if ($Prohibited) {
			if ($GagTime) {
				UpdateUserInfo(array(
					"LastPostTime" => $TimeStamp + $GagTime
				));
			}
			AlertMsg($this->lang['Prohibited_Content'], $this->lang['Prohibited_Content']);
			return;
		}
		$Content = $ContentFilterResult['Content'];

		if ($Content == $PostInfo['Content'])
			AlertMsg($this->lang['Do_Not_Modify'], $this->lang['Do_Not_Modify']);
		if ($this->db->query("UPDATE " . PREFIX . "posts SET Content = :Content WHERE ID=:ID", array(
			'ID' => $this->id,
			'Content' => $Content
		))) {
			$this->db->query("UPDATE `" . PREFIX . "upload` SET PostID=? WHERE `PostID`=0 and `UserName`=?", array(
				$this->id,
				$CurUserName
			));
			$this->message = $this->lang['Edited'];
		} else {
			AlertMsg($this->lang['Failure_Edit'], $this->lang['Failure_Edit']);
		}
	}

	//TODO: delete user function
	public function userDelete($UserInfo)
	{
		Auth(4);
		return;
	}

	// mute/Unban user
	public function userBlock($UserInfo)
	{
		Auth(4);
		$NewUserAccountStatus = $UserInfo['UserAccountStatus'] ? 0 : 1;
		if (UpdateUserInfo(array(
			'UserAccountStatus' => $NewUserAccountStatus
		), $this->id)) {
			$this->message = $NewUserAccountStatus ? $this->lang['Block_User'] : $this->lang['Unblock_User'];
		}
	}

	// Reset user avatar
	public function userResetAvatar($UserInfo)
	{
		Auth(4, $this->id);
		if (extension_loaded('gd')) {
			require(LibraryPath . "MaterialDesign.Avatars.class.php");
			$Avatar = new MDAvtars(mb_substr($UserInfo['UserName'], 0, 1, "UTF-8"), 256);
			$Avatar->Save(__DIR__ . '/../upload/avatar/large/' . $this->id . '.png', 256);
			$Avatar->Save(__DIR__ . '/../upload/avatar/middle/' . $this->id . '.png', 48);
			$Avatar->Save(__DIR__ . '/../upload/avatar/small/' . $this->id . '.png', 24);
			$Avatar->Free();
			$this->message = $this->lang['Reset_Avatar_Successfully'];
		} else {
			$this->message = $this->lang['Reset_Avatar_Successfully']; //Failure
		}
	}


	public function favoriteTopic($IsFavorite)
	{
		$this->favorite($IsFavorite, 'Topic');
	}

	public function favoriteTag($IsFavorite)
	{
		$this->favorite($IsFavorite, 'Tag');
	}

	public function favoriteUser($IsFavorite)
	{
		$this->favorite($IsFavorite, 'User');
	}

	public function favoritePost($IsFavorite)
	{
		$this->favorite($IsFavorite, 'Post');
	}

	public function favoriteBlog($IsFavorite)
	{
		$this->favorite($IsFavorite, 'Blog');
	}

	//focus on / Favorite function
	private function favorite($IsFavorite, $FavoriteType)
	{
		global $TimeStamp, $CurUserID;
		Auth(1);
		$MessageType = false;
		$SQLAction   = intval($IsFavorite) ? '-1' : '+1';
		switch ($FavoriteType) {
			case 'Topic':
				$Title = $this->db->single("SELECT Topic FROM " . PREFIX . "topics WHERE ID=:FavoriteID", array(
					'FavoriteID' => $this->id
				));
				break;
			case 'Tag':
				$Title       = $this->db->single("SELECT Name FROM " . PREFIX . "tags WHERE ID=:FavoriteID", array(
					'FavoriteID' => $this->id
				));
				$MessageType = true;
				break;
			case 'User':
				$Title       = $this->db->single("SELECT UserName FROM " . PREFIX . "users WHERE ID=:FavoriteID", array(
					'FavoriteID' => $this->id
				));
				$MessageType = true;
				break;
			case 'Post':
				$Title = $this->db->single("SELECT Subject FROM " . PREFIX . "posts WHERE ID=:FavoriteID", array(
					'FavoriteID' => $this->id
				));
				break;
			case 'Blog':
				$Title = $this->db->single("SELECT Subject FROM " . PREFIX . "blogs WHERE ID=:FavoriteID and ParentID=0", array(
					'FavoriteID' => $this->id
				));
				break;
			default:
				AlertMsg('Bad Request', 'Bad Request');
				$Title = '';
				break;
		}
		if ($Title) {
			if (!$IsFavorite) {
				if (!$this->db->query('INSERT INTO `' . PREFIX . 'favorites`(`ID`, `UserID`, `Category`, `Title`, `Type`, `FavoriteID`, `DateCreated`, `Description`) VALUES (?,?,?,?,?,?,?,?)', array(
					null,
					$CurUserID,
					'',
					$Title,
					$this->action,
					$this->id,
					$TimeStamp,
					''
				)))
					AlertMsg('Unknown Error', 'Unknown Error');
			} else {
				$this->db->query('DELETE FROM `' . PREFIX . 'favorites` WHERE `UserID`=? and `Type`=? and `FavoriteID`=?', array(
					$CurUserID,
					$this->action,
					$this->id
				));
			}
			switch ($FavoriteType) {
				case 'Topic':
					$this->db->query('UPDATE ' . PREFIX . 'topics SET Favorites = Favorites' . $SQLAction . ' WHERE ID=:FavoriteID', array(
						'FavoriteID' => $this->id
					));
					$this->db->query('UPDATE `' . PREFIX . 'users` SET NumFavTopics=NumFavTopics' . $SQLAction . ' WHERE `ID`=?', array(
						$CurUserID
					));
					break;
				case 'Tag':
					$this->db->query('UPDATE ' . PREFIX . 'tags SET Followers = Followers' . $SQLAction . ' WHERE ID=:FavoriteID', array(
						'FavoriteID' => $this->id
					));
					$this->db->query('UPDATE `' . PREFIX . 'users` SET NumFavTags=NumFavTags' . $SQLAction . ' WHERE `ID`=?', array(
						$CurUserID
					));
					break;
				case 'User':
					$this->db->query('UPDATE ' . PREFIX . 'users SET Followers = Followers' . $SQLAction . ' WHERE ID=:FavoriteID', array(
						'FavoriteID' => $this->id
					));
					$this->db->query('UPDATE `' . PREFIX . 'users` SET NumFavUsers=NumFavUsers' . $SQLAction . ' WHERE `ID`=?', array(
						$CurUserID
					));
					break;
				case 'Post':
					break;
				case 'Blog':
					break;
				default:
					AlertMsg('Bad Request', 'Bad Request');
					break;
			}

			if ($this->mCache) {
				$this->mCache->delete(MemCachePrefix . 'UserInfo_' . $CurUserID);
			}
			$this->message = $IsFavorite ? ($MessageType ? $this->lang['Follow'] : $this->lang['Collect']) : ($MessageType ? $this->lang['Unfollow'] : $this->lang['Unsubscribe']);
		} else {
			AlertMsg('404 Not Found', '404 Not Found', 404);
		}
	}

	// Modify topic description
	public function tagEditDescription($TagInfo)
	{
		Auth(3);
		$Content = CharCV(Request('Post', 'Content', $TagInfo['Description']));
		if ($Content == $TagInfo['Description'])
			AlertMsg($this->lang['Do_Not_Modify'], $this->lang['Do_Not_Modify']);
		if ($this->db->query('UPDATE ' . PREFIX . 'tags SET Description = :Content WHERE ID=:TagID', array(
			'TagID' => $this->id,
			'Content' => $Content
		))) {
			$this->message = $this->lang['Edited'];
		} else {
			AlertMsg($this->lang['Failure_Edit'], $this->lang['Failure_Edit']);
		}
	}

	// upload topic icon
	public function tagUploadIcon($TagInfo)
	{
		Auth(3);
		if ($_FILES['TagIcon']['size'] && $_FILES['TagIcon']['size'] < 1048576) {
			require(LibraryPath . "ImageResize.class.php");
			$UploadIcon    = new ImageResize('PostField', 'TagIcon');
			$LUploadResult = $UploadIcon->Resize(256, 'upload/tag/large/' . $this->id . '.png', 80);
			$MUploadResult = $UploadIcon->Resize(48, 'upload/tag/middle/' . $this->id . '.png', 90);
			$SUploadResult = $UploadIcon->Resize(24, 'upload/tag/small/' . $this->id . '.png', 90);

			if ($LUploadResult && $MUploadResult && $SUploadResult) {
				$SetTagIconStatus = $TagInfo['Icon'] == 0 ? $this->db->query('UPDATE ' . PREFIX . 'tags SET Icon = 1 WHERE ID=:TagID', array(
					'TagID' => $this->id
				)) : true;
				$this->message    = $SetTagIconStatus ? $this->lang['Icon_Upload_Success'] : $this->lang['Icon_Upload_Failure'];
			} else {
				$this->message = $this->lang['Icon_Upload_Failure'];
			}
		} else {
			$this->message = $this->lang['Icon_Is_Oversize'];
		}
	}

	// disabled/enable this label
	public function tagSwitchStatus($TagInfo)
	{
		Auth(4);
		if ($this->db->query('UPDATE ' . PREFIX . 'tags SET IsEnabled = :IsEnabled WHERE ID=:TagID', array(
			'TagID' => $this->id,
			'IsEnabled' => $TagInfo['IsEnabled'] ? 0 : 1
		))) {
			$NewConfig = array(
				"NumTags" => $this->config["NumTags"] + ($TagInfo['IsEnabled'] ? -1 : 1)
			);
			UpdateConfig($NewConfig);
			$this->message = $TagInfo['IsEnabled'] ? $this->lang['Enable_Tag'] : $this->lang['Disable_Tag'];
		} else {
			AlertMsg('Bad Request', 'Bad Request');
		}
	}
}

$PageTitle   = 'Manage';
$ContentFile = $TemplatePath . 'manage.php';
include($TemplatePath . 'layout.php');
