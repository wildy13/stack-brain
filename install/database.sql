-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2022 at 07:17 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.0.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stack-brain_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `sb_app`
--

CREATE TABLE `sb_app` (
  `ID` int(10) UNSIGNED NOT NULL,
  `AppKey` varchar(20) NOT NULL,
  `AppName` varchar(32) NOT NULL,
  `AppSecret` varchar(40) NOT NULL,
  `Time` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_app`
--

INSERT INTO `sb_app` (`ID`, `AppKey`, `AppName`, `AppSecret`, `Time`) VALUES
(1, 'bf0859bf281330ba7171', 'GitHub', '92d4bf7520917d93bc3fdd0a4767b4ef0f36aa06', 1667987242);

-- --------------------------------------------------------

--
-- Table structure for table `sb_app_users`
--

CREATE TABLE `sb_app_users` (
  `ID` int(10) UNSIGNED NOT NULL,
  `AppID` int(10) UNSIGNED NOT NULL,
  `OpenID` varchar(64) NOT NULL,
  `AppUserName` varchar(50) DEFAULT NULL,
  `UserID` int(10) UNSIGNED NOT NULL,
  `Time` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sb_blogs`
--

CREATE TABLE `sb_blogs` (
  `ID` int(10) UNSIGNED NOT NULL,
  `ParentID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `Content` longtext NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `Category` varchar(255) DEFAULT NULL,
  `Subject` varchar(255) DEFAULT NULL,
  `BlogDate` varchar(50) DEFAULT NULL,
  `TotalReplies` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `DateCreated` int(10) UNSIGNED NOT NULL,
  `DateNew` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sb_blogsettings`
--

CREATE TABLE `sb_blogsettings` (
  `ID` int(11) NOT NULL,
  `BlogTitle` varchar(255) DEFAULT NULL,
  `BlogTagline` varchar(255) DEFAULT NULL,
  `UserName` varchar(50) NOT NULL,
  `BlogBgcolor` varchar(255) DEFAULT NULL,
  `BlogPermissions` varchar(50) DEFAULT NULL,
  `BlogBackground` varchar(255) DEFAULT NULL,
  `BlogAudio` varchar(255) DEFAULT NULL,
  `BlogComments` int(11) NOT NULL DEFAULT 1,
  `NumBlogs` int(11) NOT NULL DEFAULT 5,
  `UserSkinID` int(11) DEFAULT 1,
  `BlogCount` int(11) DEFAULT 0,
  `BlogReplies` int(11) DEFAULT 0,
  `BlogViews` int(11) DEFAULT 0,
  `BlogScore` int(11) DEFAULT 0,
  `MusicUrl` longtext DEFAULT NULL,
  `MusicName` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sb_config`
--

CREATE TABLE `sb_config` (
  `ConfigName` varchar(50) NOT NULL DEFAULT '',
  `ConfigValue` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_config`
--

INSERT INTO `sb_config` (`ConfigName`, `ConfigValue`) VALUES
('AllowEditing', 'true'),
('AllowEmptyTags', 'false'),
('AllowNewTopic', 'true'),
('AppDomainName', 'app.example.com'),
('CacheAnnouncements', ''),
('CacheHotTags', '[{\"ID\":1,\"Name\":\"First\",\"Icon\":0,\"TotalPosts\":1,\"Followers\":1}]'),
('CacheHotTopics', ''),
('CacheLinks', ''),
('CacheOauth', '{\"GitHub\":{\"ID\":1,\"AppKey\":\"bf0859bf281330ba7171\",\"AppName\":\"GitHub\",\"AppSecret\":\"92d4bf7520917d93bc3fdd0a4767b4ef0f36aa06\",\"Time\":1667987242,\"Alias\":\"GitHub\",\"LogoUrl\":\"\\/static\\/img\\/oauth\\/github_icon.png\",\"ButtonImageUrl\":\"\\/static\\/img\\/oauth\\/github_icon_label.png\"}}'),
('CacheRolesDict', ''),
('CacheTime', ''),
('CloseRegistration', 'false'),
('CookiePrefix', 'sbBBS_'),
('DaysDate', '2022-11-10'),
('DaysPosts', '0'),
('DaysTopics', '1'),
('DaysUsers', '0'),
('FreezingTime', '0'),
('LoadJqueryUrl', '/stack-brain/static/js/jquery.js'),
('MainDomainName', ''),
('MaxPostChars', '60000'),
('MaxTagChars', '128'),
('MaxTagsNum', '5'),
('MaxTitleChars', '255'),
('MobileDomainName', ''),
('NumFiles', '0'),
('NumPosts', '1'),
('NumTags', '1'),
('NumTopics', '2'),
('NumUsers', '4'),
('PageBottomContent', ''),
('PageHeadContent', ''),
('PageSiderContent', '<a href=\"/stack-brain/statistics\"><div>Stattistic</div></a>'),
('PostingInterval', '8'),
('PostsPerPage', '25'),
('PushConnectionTimeoutPeriod', '22'),
('SiteDesc', 'solve the bug problems'),
('SiteName', 'Stack Brain'),
('SMTPAuth', 'true'),
('SMTPHost', 'smtp1.example.com'),
('SMTPPassword', 'secret'),
('SMTPPort', '587'),
('SMTPUsername', 'user@example.com'),
('TopicsPerPage', '20'),
('Version', '1.0.0'),
('WebsitePath', '/stack-brain');

-- --------------------------------------------------------

--
-- Table structure for table `sb_dict`
--

CREATE TABLE `sb_dict` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Title` varchar(512) NOT NULL,
  `Abstract` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_dict`
--

INSERT INTO `sb_dict` (`ID`, `Title`, `Abstract`) VALUES
(1, 'First', '');

-- --------------------------------------------------------

--
-- Table structure for table `sb_favorites`
--

CREATE TABLE `sb_favorites` (
  `ID` int(10) UNSIGNED NOT NULL,
  `UserID` int(10) UNSIGNED NOT NULL,
  `Category` varchar(255) DEFAULT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Type` tinyint(1) UNSIGNED DEFAULT NULL,
  `FavoriteID` int(10) UNSIGNED NOT NULL,
  `DateCreated` int(10) UNSIGNED NOT NULL,
  `Description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_favorites`
--

INSERT INTO `sb_favorites` (`ID`, `UserID`, `Category`, `Title`, `Type`, `FavoriteID`, `DateCreated`, `Description`) VALUES
(1, 2, '', 'wildy', 3, 1, 1667977773, ''),
(2, 1, '', 'First', 2, 1, 1667978095, '');

-- --------------------------------------------------------

--
-- Table structure for table `sb_inbox`
--

CREATE TABLE `sb_inbox` (
  `ID` int(10) NOT NULL,
  `SenderID` int(10) NOT NULL,
  `SenderName` varchar(50) NOT NULL,
  `ReceiverID` int(10) NOT NULL,
  `ReceiverName` varchar(50) NOT NULL,
  `LastContent` varchar(255) NOT NULL DEFAULT '',
  `LastTime` int(10) NOT NULL DEFAULT 0,
  `IsDel` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_inbox`
--

INSERT INTO `sb_inbox` (`ID`, `SenderID`, `SenderName`, `ReceiverID`, `ReceiverName`, `LastContent`, `LastTime`, `IsDel`) VALUES
(1, 2, 'frenata', 1, 'wildy', 'nothing happend, just would u like join our party?', 1668047728, 0),
(2, 1, 'wildy', 1, 'wildy', '', 1668047440, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sb_link`
--

CREATE TABLE `sb_link` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `URL` varchar(255) DEFAULT NULL,
  `Logo` varchar(255) DEFAULT NULL,
  `Intro` varchar(255) DEFAULT NULL,
  `Review` int(11) DEFAULT 0,
  `TopLink` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sb_log`
--

CREATE TABLE `sb_log` (
  `ID` int(11) NOT NULL,
  `UserName` longtext DEFAULT NULL,
  `IPAddress` longtext DEFAULT NULL,
  `UserAgent` longtext DEFAULT NULL,
  `DateCreated` int(10) UNSIGNED NOT NULL,
  `HttpVerb` varchar(50) DEFAULT NULL,
  `PathAndQuery` longtext DEFAULT NULL,
  `Referrer` varchar(255) DEFAULT NULL,
  `ErrDescription` varchar(255) DEFAULT NULL,
  `POSTData` longtext DEFAULT NULL,
  `Notes` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sb_messages`
--

CREATE TABLE `sb_messages` (
  `ID` int(10) UNSIGNED NOT NULL,
  `InboxID` int(10) NOT NULL DEFAULT 0,
  `UserID` int(10) NOT NULL DEFAULT 0,
  `Content` longtext NOT NULL,
  `Time` int(10) UNSIGNED NOT NULL,
  `IsDel` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_messages`
--

INSERT INTO `sb_messages` (`ID`, `InboxID`, `UserID`, `Content`, `Time`, `IsDel`) VALUES
(1, 1, 2, 'hi bro', 1668047423, 0),
(2, 1, 1, 'hi', 1668047658, 0),
(3, 1, 1, 'what\'s going on?', 1668047679, 0),
(4, 1, 2, 'nothing happend, just would u like join our party?', 1668047728, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sb_notifications`
--

CREATE TABLE `sb_notifications` (
  `ID` int(10) UNSIGNED NOT NULL,
  `UserID` int(10) UNSIGNED NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `Type` tinyint(1) UNSIGNED NOT NULL,
  `TopicID` int(10) UNSIGNED NOT NULL,
  `PostID` int(10) UNSIGNED NOT NULL,
  `Time` int(10) UNSIGNED NOT NULL,
  `IsRead` tinyint(1) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_notifications`
--

INSERT INTO `sb_notifications` (`ID`, `UserID`, `UserName`, `Type`, `TopicID`, `PostID`, `Time`, `IsRead`) VALUES
(1, 1, 'frenata', 1, 1, 2, 1667977805, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sb_pictures`
--

CREATE TABLE `sb_pictures` (
  `ID` int(10) UNSIGNED NOT NULL,
  `PicUrl` varchar(255) DEFAULT NULL,
  `UserName` varchar(255) DEFAULT NULL,
  `PicReadme` varchar(255) DEFAULT NULL,
  `TopicID` int(10) UNSIGNED DEFAULT 0,
  `AddTime` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sb_postrating`
--

CREATE TABLE `sb_postrating` (
  `UserName` varchar(50) NOT NULL,
  `TopicID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `Rating` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `DateCreated` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sb_posts`
--

CREATE TABLE `sb_posts` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TopicID` int(10) UNSIGNED DEFAULT 0,
  `IsTopic` tinyint(1) UNSIGNED DEFAULT 0,
  `UserID` int(10) UNSIGNED NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `Subject` varchar(255) DEFAULT NULL,
  `Content` longtext DEFAULT NULL,
  `PostIP` varchar(50) DEFAULT NULL,
  `PostTime` int(10) UNSIGNED NOT NULL,
  `IsDel` tinyint(1) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_posts`
--

INSERT INTO `sb_posts` (`ID`, `TopicID`, `IsTopic`, `UserID`, `UserName`, `Subject`, `Content`, `PostIP`, `PostTime`, `IsDel`) VALUES
(1, 1, 1, 1, 'wildy', 'First', '<p>this is my first time for upload question on this forum<br/></p>', '::1', 1667977652, 0),
(2, 1, 0, 2, 'frenata', 'First', '<p>yeah me too <img src=\"/stack-brain/static/editor/dialogs/emotion/images/pp/i_f02.png\"/></p>', '::1', 1667977805, 0),
(3, 2, 1, 2, 'frenata', 'my first time', '<p>my first time</p>', '::1', 1668058196, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sb_posttags`
--

CREATE TABLE `sb_posttags` (
  `TagID` int(11) DEFAULT 0,
  `TopicID` int(11) DEFAULT 0,
  `PostID` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_posttags`
--

INSERT INTO `sb_posttags` (`TagID`, `TopicID`, `PostID`) VALUES
(1, 1, 1),
(1, 2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `sb_roles`
--

CREATE TABLE `sb_roles` (
  `ID` int(8) UNSIGNED NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_roles`
--

INSERT INTO `sb_roles` (`ID`, `Name`, `Description`) VALUES
(0, 'turis', 'Unintelligible tourist'),
(1, 'anggota terdaftar', 'Semua pengguna terdaftar secara otomatis menjadi bagian dari peran ini.'),
(2, 'anggota VIP', 'Tidak ada izin khusus, hanya simbol status'),
(3, 'moderator', 'Dapat mengelola posting di bawah beberapa topik'),
(4, 'Moderator super', 'Dapat mengelola posting di bawah semua topik dan semua anggota'),
(5, 'administrator', 'Nikmati otoritas tertinggi forum, dapat mengelola seluruh forum, mengatur parameter seluruh forum.');

-- --------------------------------------------------------

--
-- Table structure for table `sb_statistics`
--

CREATE TABLE `sb_statistics` (
  `DaysUsers` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `DaysPosts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `DaysTopics` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `TotalUsers` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `TotalPosts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `TotalTopics` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `DaysDate` date NOT NULL DEFAULT '2014-11-01',
  `DateCreated` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_statistics`
--

INSERT INTO `sb_statistics` (`DaysUsers`, `DaysPosts`, `DaysTopics`, `TotalUsers`, `TotalPosts`, `TotalTopics`, `DaysDate`, `DateCreated`) VALUES
(4, 1, 1, 4, 1, 1, '2022-11-09', 1668042966);

-- --------------------------------------------------------

--
-- Table structure for table `sb_tags`
--

CREATE TABLE `sb_tags` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Followers` int(10) UNSIGNED DEFAULT 0,
  `Icon` tinyint(1) UNSIGNED DEFAULT 0,
  `Description` mediumtext DEFAULT NULL,
  `IsEnabled` tinyint(1) UNSIGNED DEFAULT 1,
  `TotalPosts` int(10) UNSIGNED DEFAULT 0,
  `MostRecentPostTime` int(10) UNSIGNED NOT NULL,
  `DateCreated` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_tags`
--

INSERT INTO `sb_tags` (`ID`, `Name`, `Followers`, `Icon`, `Description`, `IsEnabled`, `TotalPosts`, `MostRecentPostTime`, `DateCreated`) VALUES
(1, 'First', 1, 0, NULL, 1, 2, 1668058196, 1667977652);

-- --------------------------------------------------------

--
-- Table structure for table `sb_topics`
--

CREATE TABLE `sb_topics` (
  `ID` int(11) NOT NULL,
  `Topic` varchar(255) NOT NULL,
  `Tags` text DEFAULT NULL,
  `UserID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `UserName` varchar(50) NOT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `PostTime` int(10) UNSIGNED NOT NULL,
  `LastTime` int(10) UNSIGNED NOT NULL,
  `IsGood` tinyint(1) UNSIGNED DEFAULT 0,
  `IsTop` tinyint(1) UNSIGNED DEFAULT 0,
  `IsLocked` tinyint(1) UNSIGNED DEFAULT 0,
  `IsDel` tinyint(1) UNSIGNED DEFAULT 0,
  `IsVote` tinyint(1) UNSIGNED DEFAULT 0,
  `Views` int(10) UNSIGNED DEFAULT 0,
  `Replies` int(10) UNSIGNED DEFAULT 0,
  `Favorites` int(10) UNSIGNED DEFAULT 0,
  `RatingSum` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `TotalRatings` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `LastViewedTime` int(10) UNSIGNED NOT NULL,
  `PostsTableName` int(10) UNSIGNED DEFAULT NULL,
  `ThreadStyle` varchar(255) DEFAULT NULL,
  `Lists` longtext DEFAULT NULL,
  `ListsTime` int(10) UNSIGNED NOT NULL,
  `Log` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_topics`
--

INSERT INTO `sb_topics` (`ID`, `Topic`, `Tags`, `UserID`, `UserName`, `LastName`, `PostTime`, `LastTime`, `IsGood`, `IsTop`, `IsLocked`, `IsDel`, `IsVote`, `Views`, `Replies`, `Favorites`, `RatingSum`, `TotalRatings`, `LastViewedTime`, `PostsTableName`, `ThreadStyle`, `Lists`, `ListsTime`, `Log`) VALUES
(1, 'First', 'First', 1, 'wildy', 'frenata', 1667977652, 1667977805, 0, 0, 0, 0, 0, 12, 1, 0, 0, 0, 1668055035, NULL, '', '', 1667977652, ''),
(2, 'my first time', 'First', 2, 'frenata', '', 1668058196, 1668058196, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 1668060743, NULL, '', '', 1668058196, '');

-- --------------------------------------------------------

--
-- Table structure for table `sb_upload`
--

CREATE TABLE `sb_upload` (
  `ID` int(11) NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `FileName` varchar(255) NOT NULL,
  `FileSize` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `FileType` varchar(255) NOT NULL,
  `SHA1` char(40) DEFAULT NULL,
  `MD5` char(32) DEFAULT NULL,
  `FilePath` varchar(255) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Category` varchar(255) DEFAULT NULL,
  `Class` varchar(255) DEFAULT NULL,
  `PostID` int(10) UNSIGNED DEFAULT NULL,
  `Created` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sb_users`
--

CREATE TABLE `sb_users` (
  `ID` int(11) UNSIGNED NOT NULL,
  `UserName` varchar(50) DEFAULT NULL,
  `Salt` int(6) UNSIGNED DEFAULT NULL,
  `Password` char(32) DEFAULT NULL,
  `UserMail` varchar(255) DEFAULT NULL,
  `UserHomepage` varchar(255) DEFAULT NULL,
  `PasswordQuestion` varchar(255) DEFAULT NULL,
  `PasswordAnswer` char(32) DEFAULT NULL,
  `UserSex` tinyint(1) UNSIGNED DEFAULT NULL,
  `NumFavUsers` int(10) UNSIGNED DEFAULT 0,
  `NumFavTags` int(10) UNSIGNED DEFAULT 0,
  `NumFavTopics` int(10) UNSIGNED DEFAULT 0,
  `NewReply` int(10) UNSIGNED DEFAULT 0,
  `NewMention` int(10) UNSIGNED DEFAULT 0,
  `NewMessage` int(10) UNSIGNED DEFAULT 0,
  `Topics` int(10) UNSIGNED DEFAULT 0,
  `Replies` int(10) UNSIGNED DEFAULT 0,
  `Followers` int(10) UNSIGNED DEFAULT 0,
  `DelTopic` int(10) UNSIGNED DEFAULT 0,
  `GoodTopic` int(10) UNSIGNED DEFAULT 0,
  `UserPhoto` varchar(255) DEFAULT NULL,
  `UserMobile` varchar(255) DEFAULT NULL,
  `UserLastIP` varchar(50) DEFAULT NULL,
  `UserRegTime` int(10) UNSIGNED NOT NULL,
  `LastLoginTime` int(10) UNSIGNED NOT NULL,
  `LastPostTime` int(10) UNSIGNED DEFAULT NULL,
  `BlackLists` longtext DEFAULT NULL,
  `UserFriend` longtext DEFAULT NULL,
  `UserInfo` longtext DEFAULT NULL,
  `UserIntro` longtext DEFAULT NULL,
  `UserIM` longtext DEFAULT NULL,
  `UserRoleID` tinyint(1) UNSIGNED NOT NULL DEFAULT 3,
  `UserAccountStatus` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `Birthday` date DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sb_users`
--

INSERT INTO `sb_users` (`ID`, `UserName`, `Salt`, `Password`, `UserMail`, `UserHomepage`, `PasswordQuestion`, `PasswordAnswer`, `UserSex`, `NumFavUsers`, `NumFavTags`, `NumFavTopics`, `NewReply`, `NewMention`, `NewMessage`, `Topics`, `Replies`, `Followers`, `DelTopic`, `GoodTopic`, `UserPhoto`, `UserMobile`, `UserLastIP`, `UserRegTime`, `LastLoginTime`, `LastPostTime`, `BlackLists`, `UserFriend`, `UserInfo`, `UserIntro`, `UserIM`, `UserRoleID`, `UserAccountStatus`, `Birthday`, `Address`) VALUES
(1, 'wildy', 417391, '80330b4c3237f280b4cee6fdeea35f8e', 'wildy.simanjuntak13@gmail.com', 'https://wildy13.github.io/wildy-johannes-simanjuntak/', '', '', 1, 0, 1, 0, 0, 0, 0, 1, 0, 1, 0, 0, '', '', '::1', 1667977579, 1668058256, 1667977652, '', '', '', 'Love Nature', '', 5, 1, '2000-09-13', 'Batam, Indonesia'),
(2, 'frenata', 130088, 'abae8e81cd89dc2ec9b6ca037ba2ea59', 'frenata@gmail.com', '', '', '', 0, 1, 0, 0, 0, 0, 2, 1, 1, 0, 0, 0, '', '', '::1', 1667977765, 1668058165, 1668058196, '', '', '', '', '', 1, 1, '2000-04-29', ''),
(3, 'denita', 330630, '716bfdddb5f3f6dc4cc0fa717785872d', 'denita@gmail.com', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '::1', 1667979152, 1667979152, 1667979152, '', '', '', '', '', 1, 1, '2000-01-19', ''),
(4, 'dwisuci', 198802, '3a5d10b34ef475287d8242c4a020a93f', 'dwisuci@gmail.com', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '::1', 1667979840, 1667981460, 1667979840, '', '', '', '', '', 1, 1, '2000-06-05', '');

-- --------------------------------------------------------

--
-- Table structure for table `sb_vote`
--

CREATE TABLE `sb_vote` (
  `TopicID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `Type` tinyint(1) UNSIGNED DEFAULT 0,
  `Expiry` int(10) UNSIGNED NOT NULL,
  `Items` longtext DEFAULT NULL,
  `Result` longtext DEFAULT NULL,
  `BallotUserList` longtext DEFAULT NULL,
  `BallotIPList` longtext DEFAULT NULL,
  `BallotItemsList` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sb_app`
--
ALTER TABLE `sb_app`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `AppKey` (`AppKey`);

--
-- Indexes for table `sb_app_users`
--
ALTER TABLE `sb_app_users`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Index` (`AppID`,`OpenID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `sb_blogs`
--
ALTER TABLE `sb_blogs`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Blogs` (`UserName`,`ParentID`,`DateCreated`);

--
-- Indexes for table `sb_blogsettings`
--
ALTER TABLE `sb_blogsettings`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `BlogSettings` (`UserName`);

--
-- Indexes for table `sb_config`
--
ALTER TABLE `sb_config`
  ADD PRIMARY KEY (`ConfigName`);

--
-- Indexes for table `sb_dict`
--
ALTER TABLE `sb_dict`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `title` (`Title`(200)) USING HASH;

--
-- Indexes for table `sb_favorites`
--
ALTER TABLE `sb_favorites`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `IsFavorite` (`UserID`,`Type`,`FavoriteID`),
  ADD KEY `UsersFavorites` (`UserID`,`Type`,`DateCreated`) USING BTREE;

--
-- Indexes for table `sb_inbox`
--
ALTER TABLE `sb_inbox`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `DialogueID` (`LastTime`) USING BTREE,
  ADD KEY `SenderID` (`SenderID`,`ReceiverID`),
  ADD KEY `ReceiverID` (`ReceiverID`,`SenderID`);

--
-- Indexes for table `sb_link`
--
ALTER TABLE `sb_link`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sb_log`
--
ALTER TABLE `sb_log`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sb_messages`
--
ALTER TABLE `sb_messages`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Index` (`IsDel`,`InboxID`,`Time`) USING BTREE;

--
-- Indexes for table `sb_notifications`
--
ALTER TABLE `sb_notifications`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TopicID` (`TopicID`),
  ADD KEY `PostID` (`PostID`),
  ADD KEY `UserID` (`UserID`,`Time`) USING BTREE;

--
-- Indexes for table `sb_pictures`
--
ALTER TABLE `sb_pictures`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sb_postrating`
--
ALTER TABLE `sb_postrating`
  ADD KEY `TopicID` (`TopicID`) USING BTREE;

--
-- Indexes for table `sb_posts`
--
ALTER TABLE `sb_posts`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TopicID` (`TopicID`,`PostTime`,`IsDel`) USING BTREE,
  ADD KEY `UserPosts` (`UserName`,`IsDel`,`PostTime`) USING BTREE;

--
-- Indexes for table `sb_posttags`
--
ALTER TABLE `sb_posttags`
  ADD KEY `TagsIndex` (`TagID`,`TopicID`),
  ADD KEY `TopicID` (`TopicID`);

--
-- Indexes for table `sb_roles`
--
ALTER TABLE `sb_roles`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sb_statistics`
--
ALTER TABLE `sb_statistics`
  ADD PRIMARY KEY (`DaysDate`);

--
-- Indexes for table `sb_tags`
--
ALTER TABLE `sb_tags`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TagName` (`Name`) USING HASH,
  ADD KEY `TotalPosts` (`IsEnabled`,`TotalPosts`);

--
-- Indexes for table `sb_topics`
--
ALTER TABLE `sb_topics`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `LastTime` (`LastTime`,`IsDel`),
  ADD KEY `UserTopics` (`UserName`,`IsDel`,`LastTime`);

--
-- Indexes for table `sb_upload`
--
ALTER TABLE `sb_upload`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Hash` (`FileSize`,`SHA1`,`MD5`) USING BTREE,
  ADD KEY `UsersName` (`UserName`,`Created`) USING BTREE,
  ADD KEY `PostID` (`PostID`,`UserName`) USING BTREE;

--
-- Indexes for table `sb_users`
--
ALTER TABLE `sb_users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `UserName` (`UserName`);

--
-- Indexes for table `sb_vote`
--
ALTER TABLE `sb_vote`
  ADD PRIMARY KEY (`TopicID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sb_app`
--
ALTER TABLE `sb_app`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sb_app_users`
--
ALTER TABLE `sb_app_users`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sb_blogs`
--
ALTER TABLE `sb_blogs`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sb_blogsettings`
--
ALTER TABLE `sb_blogsettings`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sb_dict`
--
ALTER TABLE `sb_dict`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sb_favorites`
--
ALTER TABLE `sb_favorites`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sb_inbox`
--
ALTER TABLE `sb_inbox`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sb_link`
--
ALTER TABLE `sb_link`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sb_log`
--
ALTER TABLE `sb_log`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sb_messages`
--
ALTER TABLE `sb_messages`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sb_notifications`
--
ALTER TABLE `sb_notifications`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sb_pictures`
--
ALTER TABLE `sb_pictures`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sb_posts`
--
ALTER TABLE `sb_posts`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sb_tags`
--
ALTER TABLE `sb_tags`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sb_topics`
--
ALTER TABLE `sb_topics`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sb_upload`
--
ALTER TABLE `sb_upload`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sb_users`
--
ALTER TABLE `sb_users`
  MODIFY `ID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
