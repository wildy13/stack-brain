SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sb_app
-- ----------------------------
DROP TABLE IF EXISTS `sb_app`;
CREATE TABLE `sb_app` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AppKey` varchar(20) NOT NULL,
  `AppName` varchar(32) NOT NULL,
  `AppSecret` varchar(40) NOT NULL,
  `Time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `AppKey` (`AppKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_app_users
-- ----------------------------
DROP TABLE IF EXISTS `sb_app_users`;
CREATE TABLE `sb_app_users` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AppID` int(10) unsigned NOT NULL,
  `OpenID` varchar(64) NOT NULL,
  `AppUserName` varchar(50) CHARACTER SET utf8,
  `UserID` int(10) unsigned NOT NULL,
  `Time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Index` (`AppID`,`OpenID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_blogs
-- ----------------------------
DROP TABLE IF EXISTS `sb_blogs`;
CREATE TABLE `sb_blogs` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ParentID` int(10) unsigned NOT NULL DEFAULT '0',
  `Content` longtext CHARACTER SET utf8 NOT NULL,
  `UserName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `Category` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Subject` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `BlogDate` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `TotalReplies` int(10) unsigned NOT NULL DEFAULT '0',
  `DateCreated` int(10) unsigned NOT NULL,
  `DateNew` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Blogs` (`UserName`,`ParentID`,`DateCreated`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_blogsettings
-- ----------------------------
DROP TABLE IF EXISTS `sb_blogsettings`;
CREATE TABLE `sb_blogsettings` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BlogTitle` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `BlogTagline` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `UserName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `BlogBgcolor` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `BlogPermissions` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `BlogBackground` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `BlogAudio` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `BlogComments` int(11) NOT NULL DEFAULT '1',
  `NumBlogs` int(11) NOT NULL DEFAULT '5',
  `UserSkinID` int(11) DEFAULT '1',
  `BlogCount` int(11) DEFAULT '0',
  `BlogReplies` int(11) DEFAULT '0',
  `BlogViews` int(11) DEFAULT '0',
  `BlogScore` int(11) DEFAULT '0',
  `MusicUrl` longtext CHARACTER SET utf8,
  `MusicName` longtext CHARACTER SET utf8,
  PRIMARY KEY (`ID`),
  KEY `BlogSettings` (`UserName`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_config
-- ----------------------------
DROP TABLE IF EXISTS `sb_config`;
CREATE TABLE `sb_config` (
  `ConfigName` varchar(50) NOT NULL DEFAULT '',
  `ConfigValue` text NOT NULL,
  PRIMARY KEY (`ConfigName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_dict
-- ----------------------------
DROP TABLE IF EXISTS `sb_dict`;
CREATE TABLE `sb_dict` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(512) NOT NULL,
  `Abstract` mediumtext NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `title` (`Title`(200)) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_favorites
-- ----------------------------
DROP TABLE IF EXISTS `sb_favorites`;
CREATE TABLE `sb_favorites` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Category` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Type` tinyint(1) unsigned DEFAULT NULL,
  `FavoriteID` int(10) unsigned NOT NULL,
  `DateCreated` int(10) unsigned NOT NULL,
  `Description` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IsFavorite` (`UserID`,`Type`,`FavoriteID`),
  KEY `UsersFavorites` (`UserID`,`Type`,`DateCreated`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_inbox
-- ----------------------------
DROP TABLE IF EXISTS `sb_inbox`;
CREATE TABLE `sb_inbox` (
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_link
-- ----------------------------
DROP TABLE IF EXISTS `sb_link`;
CREATE TABLE `sb_link` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `URL` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Logo` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Intro` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Review` int(11) DEFAULT '0',
  `TopLink` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_log
-- ----------------------------
DROP TABLE IF EXISTS `sb_log`;
CREATE TABLE `sb_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserName` longtext CHARACTER SET utf8,
  `IPAddress` longtext CHARACTER SET utf8,
  `UserAgent` longtext CHARACTER SET utf8,
  `DateCreated` int(10) unsigned NOT NULL,
  `HttpVerb` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `PathAndQuery` longtext CHARACTER SET utf8,
  `Referrer` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `ErrDescription` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `POSTData` longtext CHARACTER SET utf8,
  `Notes` longtext CHARACTER SET utf8,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_messages
-- ----------------------------
DROP TABLE IF EXISTS `sb_messages`;
CREATE TABLE `sb_messages` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `InboxID` int(10) NOT NULL DEFAULT '0',
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Content` longtext NOT NULL,
  `Time` int(10) unsigned NOT NULL,
  `IsDel` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Index` (`IsDel`,`InboxID`,`Time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_notifications
-- ----------------------------
DROP TABLE IF EXISTS `sb_notifications`;
CREATE TABLE `sb_notifications` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `Type` tinyint(1) unsigned NOT NULL,
  `TopicID` int(10) unsigned NOT NULL,
  `PostID` int(10) unsigned NOT NULL,
  `Time` int(10) unsigned NOT NULL,
  `IsRead` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `TopicID` (`TopicID`),
  KEY `PostID` (`PostID`),
  KEY `UserID` (`UserID`,`Time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_pictures
-- ----------------------------
DROP TABLE IF EXISTS `sb_pictures`;
CREATE TABLE `sb_pictures` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PicUrl` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `UserName` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `PicReadme` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `TopicID` int(10) unsigned DEFAULT '0',
  `AddTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_postrating
-- ----------------------------
DROP TABLE IF EXISTS `sb_postrating`;
CREATE TABLE `sb_postrating` (
  `UserName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `TopicID` int(10) unsigned NOT NULL DEFAULT '0',
  `Rating` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `DateCreated` int(10) unsigned NOT NULL,
  KEY `TopicID` (`TopicID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_posts
-- ----------------------------
DROP TABLE IF EXISTS `sb_posts`;
CREATE TABLE `sb_posts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TopicID` int(10) unsigned DEFAULT '0',
  `IsTopic` tinyint(1) unsigned DEFAULT '0',
  `UserID` int(10) unsigned NOT NULL,
  `UserName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `Subject` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Content` longtext CHARACTER SET utf8,
  `PostIP` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `PostTime` int(10) unsigned NOT NULL,
  `IsDel` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `TopicID` (`TopicID`,`PostTime`,`IsDel`) USING BTREE,
  KEY `UserPosts` (`UserName`,`IsDel`,`PostTime`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_posttags
-- ----------------------------
DROP TABLE IF EXISTS `sb_posttags`;
CREATE TABLE `sb_posttags` (
  `TagID` int(11) DEFAULT '0',
  `TopicID` int(11) DEFAULT '0',
  `PostID` int(11) DEFAULT '0',
  KEY `TagsIndex` (`TagID`,`TopicID`),
  KEY `TopicID` (`TopicID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_roles
-- ----------------------------
DROP TABLE IF EXISTS `sb_roles`;
CREATE TABLE `sb_roles` (
  `ID` int(8) unsigned NOT NULL,
  `Name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `Description` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_statistics
-- ----------------------------
DROP TABLE IF EXISTS `sb_statistics`;
CREATE TABLE `sb_statistics` (
  `DaysUsers` int(10) unsigned NOT NULL DEFAULT '0',
  `DaysPosts` int(10) unsigned NOT NULL DEFAULT '0',
  `DaysTopics` int(10) unsigned NOT NULL DEFAULT '0',
  `TotalUsers` int(10) unsigned NOT NULL DEFAULT '0',
  `TotalPosts` int(10) unsigned NOT NULL DEFAULT '0',
  `TotalTopics` int(10) unsigned NOT NULL DEFAULT '0',
  `DaysDate` date NOT NULL DEFAULT '2014-11-01',
  `DateCreated` int(10) unsigned NOT NULL,
  PRIMARY KEY (`DaysDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_tags
-- ----------------------------
DROP TABLE IF EXISTS `sb_tags`;
CREATE TABLE `sb_tags` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `Followers` int(10) unsigned DEFAULT '0',
  `Icon` tinyint(1) unsigned DEFAULT '0',
  `Description` mediumtext CHARACTER SET utf8,
  `IsEnabled` tinyint(1) unsigned DEFAULT '1',
  `TotalPosts` int(10) unsigned DEFAULT '0',
  `MostRecentPostTime` int(10) unsigned NOT NULL,
  `DateCreated` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `TagName` (`Name`) USING HASH,
  KEY `TotalPosts` (`IsEnabled`, `TotalPosts`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_topics
-- ----------------------------
DROP TABLE IF EXISTS `sb_topics`;
CREATE TABLE `sb_topics` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Topic` varchar(255) CHARACTER SET utf8 NOT NULL,
  `Tags` text CHARACTER SET utf8,
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  `UserName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `LastName` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `PostTime` int(10) unsigned NOT NULL,
  `LastTime` int(10) unsigned NOT NULL,
  `IsGood` tinyint(1) unsigned DEFAULT '0',
  `IsTop` tinyint(1) unsigned DEFAULT '0',
  `IsLocked` tinyint(1) unsigned DEFAULT '0',
  `IsDel` tinyint(1) unsigned DEFAULT '0',
  `IsVote` tinyint(1) unsigned DEFAULT '0',
  `Views` int(10) unsigned DEFAULT '0',
  `Replies` int(10) unsigned DEFAULT '0',
  `Favorites` int(10) unsigned DEFAULT '0',
  `RatingSum` int(10) unsigned NOT NULL DEFAULT '0',
  `TotalRatings` int(10) unsigned NOT NULL DEFAULT '0',
  `LastViewedTime` int(10) unsigned NOT NULL,
  `PostsTableName` int(10) unsigned DEFAULT NULL,
  `ThreadStyle` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Lists` longtext CHARACTER SET utf8,
  `ListsTime` int(10) unsigned NOT NULL,
  `Log` longtext CHARACTER SET utf8,
  PRIMARY KEY (`ID`),
  KEY `LastTime` (`LastTime`,`IsDel`),
  KEY `UserTopics` (`UserName`,`IsDel`,`LastTime`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_upload
-- ----------------------------
DROP TABLE IF EXISTS `sb_upload`;
CREATE TABLE `sb_upload` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `FileName` varchar(255) CHARACTER SET utf8 NOT NULL,
  `FileSize` int(10) unsigned NOT NULL DEFAULT '0',
  `FileType` varchar(255) CHARACTER SET utf8 NOT NULL,
  `SHA1` char(40) DEFAULT NULL,
  `MD5` char(32) DEFAULT NULL,
  `FilePath` varchar(255) CHARACTER SET utf8 NOT NULL,
  `Description` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Category` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Class` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `PostID` int(10) unsigned DEFAULT NULL,
  `Created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Hash` (`FileSize`,`SHA1`,`MD5`) USING BTREE,
  KEY `UsersName` (`UserName`,`Created`) USING BTREE,
  KEY `PostID` (`PostID`,`UserName`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_users
-- ----------------------------
DROP TABLE IF EXISTS `sb_users`;
CREATE TABLE `sb_users` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `UserName` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `Salt` int(6) unsigned DEFAULT NULL,
  `Password` char(32) CHARACTER SET utf8 DEFAULT NULL,
  `UserMail` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `UserHomepage` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `PasswordQuestion` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `PasswordAnswer` char(32) CHARACTER SET utf8 DEFAULT NULL,
  `UserSex` tinyint(1) unsigned DEFAULT NULL,
  `NumFavUsers` int(10) unsigned DEFAULT '0',
  `NumFavTags` int(10) unsigned DEFAULT '0',
  `NumFavTopics` int(10) unsigned DEFAULT '0',
  `NewReply` int(10) unsigned DEFAULT '0',
  `NewMention` int(10) unsigned DEFAULT '0',
  `NewMessage` int(10) unsigned DEFAULT '0',
  `Topics` int(10) unsigned DEFAULT '0',
  `Replies` int(10) unsigned DEFAULT '0',
  `Followers` int(10) unsigned DEFAULT '0',
  `DelTopic` int(10) unsigned DEFAULT '0',
  `GoodTopic` int(10) unsigned DEFAULT '0',
  `UserPhoto` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `UserMobile` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `UserLastIP` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `UserRegTime` int(10) unsigned NOT NULL,
  `LastLoginTime` int(10) unsigned NOT NULL,
  `LastPostTime` int(10) unsigned DEFAULT NULL,
  `BlackLists` longtext CHARACTER SET utf8,
  `UserFriend` longtext CHARACTER SET utf8,
  `UserInfo` longtext CHARACTER SET utf8,
  `UserIntro` longtext CHARACTER SET utf8,
  `UserIM` longtext CHARACTER SET utf8,
  `UserRoleID` tinyint(1) unsigned NOT NULL DEFAULT '3',
  `UserAccountStatus` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Birthday` date DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UserName` (`UserName`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sb_vote
-- ----------------------------
DROP TABLE IF EXISTS `sb_vote`;
CREATE TABLE `sb_vote` (
  `TopicID` int(10) unsigned NOT NULL DEFAULT '0',
  `Type` tinyint(1) unsigned DEFAULT '0',
  `Expiry` int(10) unsigned NOT NULL,
  `Items` longtext CHARACTER SET utf8,
  `Result` longtext CHARACTER SET utf8,
  `BallotUserList` longtext CHARACTER SET utf8,
  `BallotIPList` longtext CHARACTER SET utf8,
  `BallotItemsList` longtext CHARACTER SET utf8,
  PRIMARY KEY (`TopicID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sb_config
-- ----------------------------
INSERT INTO `sb_config` VALUES ('AllowEditing', 'true');
INSERT INTO `sb_config` VALUES ('AllowEmptyTags', 'false');
INSERT INTO `sb_config` VALUES ('AllowNewTopic', 'true');
INSERT INTO `sb_config` VALUES ('AppDomainName', 'app.94cb.com');
INSERT INTO `sb_config` VALUES ('CacheAnnouncements', '');
INSERT INTO `sb_config` VALUES ('CacheHotTags', '');
INSERT INTO `sb_config` VALUES ('CacheHotTopics', '');
INSERT INTO `sb_config` VALUES ('CacheLinks', '');
INSERT INTO `sb_config` VALUES ('CacheOauth', '');
INSERT INTO `sb_config` VALUES ('CacheRolesDict', '');
INSERT INTO `sb_config` VALUES ('CacheTime', '');
INSERT INTO `sb_config` VALUES ('CloseRegistration', 'false');
INSERT INTO `sb_config` VALUES ('CookiePrefix', 'sbBBS_');
INSERT INTO `sb_config` VALUES ('DaysDate', '2014-11-01');
INSERT INTO `sb_config` VALUES ('DaysPosts', '0');
INSERT INTO `sb_config` VALUES ('DaysTopics', '0');
INSERT INTO `sb_config` VALUES ('DaysUsers', '0');
INSERT INTO `sb_config` VALUES ('FreezingTime', '0');
INSERT INTO `sb_config` VALUES ('MainDomainName', '');
INSERT INTO `sb_config` VALUES ('MaxPostChars', '60000');
INSERT INTO `sb_config` VALUES ('MaxTagChars', '128');
INSERT INTO `sb_config` VALUES ('MaxTagsNum', '5');
INSERT INTO `sb_config` VALUES ('MaxTitleChars', '255');
INSERT INTO `sb_config` VALUES ('MobileDomainName', '');
INSERT INTO `sb_config` VALUES ('NumFiles', '0');
INSERT INTO `sb_config` VALUES ('NumPosts', '0');
INSERT INTO `sb_config` VALUES ('NumTags', '0');
INSERT INTO `sb_config` VALUES ('NumTopics', '0');
INSERT INTO `sb_config` VALUES ('NumUsers', '0');
INSERT INTO `sb_config` VALUES ('PageBottomContent', '');
INSERT INTO `sb_config` VALUES ('PageHeadContent', '');
INSERT INTO `sb_config` VALUES ('PageSiderContent', 'Hello World');
INSERT INTO `sb_config` VALUES ('PostsPerPage', '25');
INSERT INTO `sb_config` VALUES ('PostingInterval', '8');
INSERT INTO `sb_config` VALUES ('SiteDesc', 'Forum baru berbasis topik yang ramping dan berkecepatan tinggi');
INSERT INTO `sb_config` VALUES ('SiteName', 'Stack Brain Forum');
INSERT INTO `sb_config` VALUES ('TopicsPerPage', '20');
INSERT INTO `sb_config` VALUES ('Version', '5.9.0');

INSERT INTO `sb_config` VALUES ('PushConnectionTimeoutPeriod', '22');
INSERT INTO `sb_config` VALUES ('SMTPHost', 'smtp1.example.com');
INSERT INTO `sb_config` VALUES ('SMTPPort', '587');
INSERT INTO `sb_config` VALUES ('SMTPAuth', 'true');
INSERT INTO `sb_config` VALUES ('SMTPUsername', 'user@example.com');
INSERT INTO `sb_config` VALUES ('SMTPPassword', 'secret');
-- ----------------------------
-- Records of sb_roles
-- ----------------------------
INSERT INTO `sb_roles` (`ID`, `Name`, `Description`) VALUES
(0, 'turis', 'Unintelligible tourist'),
(1, 'anggota terdaftar', 'Semua pengguna terdaftar secara otomatis menjadi bagian dari peran ini.'),
(2, 'anggota VIP', 'Tidak ada izin khusus, hanya simbol status'),
(3, 'moderator', 'Dapat mengelola posting di bawah beberapa topik'),
(4, 'Moderator super', 'Dapat mengelola posting di bawah semua topik dan semua anggota'),
(5, 'administrator', 'Nikmati otoritas tertinggi forum, dapat mengelola seluruh forum, mengatur parameter seluruh forum.');
