<?php
class Uploader
{
	private $DB;
	private $CurUserName;
	private $fileField;
	private $file;
	private $base64;
	private $config;
	private $oriName;
	private $fileName;
	private $fullName;
	private $filePath;
	private $fileSize;
	private $fileType;
	private $fileLongType;
	private $fileMD5;
	private $fileSHA1;
	private $stateInfo;
	private $stateMap = array(
		"SUCCESS",
		"File size exceeds upload_max_filesize limit",
		"File size exceeds MAX_FILE_SIZE limit",
		"The file is not uploaded completely",
		"No file is uploaded",
		"Upload files are empty",
		"ERROR_TMP_FILE" => "Temporary file error",
		"ERROR_TMP_FILE_NOT_FOUND" => "Can't find temporary files",
		"ERROR_SIZE_EXCEED" => "The file size exceeds the website restriction",
		"ERROR_TYPE_NOT_ALLOWED" => "File types are not allowed",
		"ERROR_CREATE_DIR" => "Directory creation failed",
		"ERROR_DIR_NOT_WRITEABLE" => "The directory is not written permissions",
		"ERROR_FILE_MOVE" => "Error when the file is saved",
		"ERROR_FILE_NOT_FOUND" => "Can't find upload files",
		"ERROR_WRITE_CONTENT" => "Write the content of the file error",
		"ERROR_UNKNOWN" => "unknown mistake",
		"ERROR_DEAD_LINK" => "Links are unavailable",
		"ERROR_HTTP_LINK" => "The link is not http link",
		"ERROR_HTTP_CONTENTTYPE" => "Link ContentType is incorrect",
		"INVALID_URL" => "illegal URL",
		"INVALID_IP" => "illegal IP"
	);
	/**
	 
	 * @param string $fileField 
	 * @param array $config 
	 * @param bool $base64 
	 */
	public function __construct($fileField, $config, $type, $CurUserName, $DB = '')
	{
		$this->fileField   = $fileField;
		$this->config      = $config;
		$this->type        = $type;
		$this->CurUserName = $CurUserName;
		$this->DB          = $DB;
		if ($type == "remote") {
			$this->saveRemote();
		} else if ($type == "base64") {
			$this->upBase64();
		} else {
			$this->upFile();
		}

		//$this->stateMap['ERROR_TYPE_NOT_ALLOWED'] = iconv('unicode', 'utf-8', $this->stateMap['ERROR_TYPE_NOT_ALLOWED']);
	}

	/**
	 * @return mixed
	 */
	private function upFile()
	{
		$file = $this->file = $_FILES[$this->fileField];
		if (!$file) {
			$this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
			return;
		}
		if ($this->file['error']) {
			$this->stateInfo = $this->getStateInfo($file['error']);
			return;
		} else if (!file_exists($file['tmp_name'])) {
			$this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE_NOT_FOUND");
			return;
		} else if (!is_uploaded_file($file['tmp_name'])) {
			$this->stateInfo = $this->getStateInfo("ERROR_TMPFILE");
			return;
		}

		$this->oriName      = $file['name'];
		$this->fileSize     = $file['size'];
		$this->fileLongType = $file['type'];
		$this->fileType     = $this->getFileExt();
		$this->fullName     = $this->getFullName();
		$this->filePath     = $this->getFilePath();
		$this->fileName     = $this->getFileName();
		$dirname            = dirname($this->filePath);

		if (!$this->checkIdenticalFiles($file['tmp_name'], 'file')) {
			return;
		}

		if (!$this->checkSize()) {
			$this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
			return;
		}

		if (!$this->checkType()) {
			$this->stateInfo = $this->getStateInfo("ERROR_TYPE_NOT_ALLOWED");
			return;
		}

		if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
			$this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
			return;
		} else if (!is_writeable($dirname)) {
			$this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
			return;
		}

		if (!(move_uploaded_file($file["tmp_name"], $this->filePath) && file_exists($this->filePath))) { //移动失败
			$this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
		} else {
			$this->stateInfo = $this->stateMap[0];
			$this->insertData();
		}
	}

	/**
	 * @return mixed
	 */
	private function upBase64()
	{
		$base64Data = $_POST[$this->fileField];
		$img        = base64_decode($base64Data);

		$this->oriName      = $this->config['oriName'];
		$this->fileSize     = strlen($img);
		$this->fileType     = $this->getFileExt();
		$this->fileLongType = 'image/png';
		$this->fullName     = $this->getFullName();
		$this->filePath     = $this->getFilePath();
		$this->fileName     = $this->getFileName();
		$dirname            = dirname($this->filePath);

		if (!$this->checkSize()) {
			$this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
			return;
		}

		if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
			$this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
			return;
		} else if (!is_writeable($dirname)) {
			$this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
			return;
		}

		if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
			$this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
		} else {
			$this->stateInfo = $this->stateMap[0];
			$this->insertData();
		}
	}

	/**
	 * @return mixed
	 */
	private function saveRemote()
	{
		$imgUrl = htmlspecialchars($this->fileField);
		$imgUrl = str_replace("&amp;", "&", $imgUrl);

		if (strpos($imgUrl, "http") !== 0) {
			$this->stateInfo = $this->getStateInfo("ERROR_HTTP_LINK");
			return;
		}
		preg_match('/(^https*:\/\/[^:\/]+)/', $imgUrl, $matches);
		$host_with_protocol = count($matches) > 1 ? $matches[1] : '';

		if (!filter_var($host_with_protocol, FILTER_VALIDATE_URL)) {
			$this->stateInfo = $this->getStateInfo("INVALID_URL");
			return;
		}

		preg_match('/^https*:\/\/(.+)/', $host_with_protocol, $matches);
		$host_without_protocol = count($matches) > 1 ? $matches[1] : '';


		$ip = gethostbyname($host_without_protocol);

		if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
			$this->stateInfo = $this->getStateInfo("INVALID_IP");
			return;
		}


		$heads = get_headers($imgUrl, 1);
		if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
			$this->stateInfo = $this->getStateInfo("ERROR_DEAD_LINK");
			return;
		}

		$fileType = strtolower(strrchr($imgUrl, '.'));
		if (!in_array($fileType, $this->config['allowFiles']) || !isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image")) {
			$this->stateInfo = $this->getStateInfo("ERROR_HTTP_CONTENTTYPE");
			return;
		}


		ob_start();
		$context = stream_context_create(array(
			'http' => array(
				'follow_location' => false // don't follow redirects
			)
		));
		readfile($imgUrl, false, $context);
		$img = ob_get_contents();
		ob_end_clean();
		preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

		$this->oriName      = $m ? $m[1] : "";
		$this->fileSize     = strlen($img);
		$this->fileType     = $this->getFileExt();
		$this->fileLongType = 'image/png';
		$this->fullName     = $this->getFullName();
		$this->filePath     = $this->getFilePath();
		$this->fileName     = $this->getFileName();
		$dirname            = dirname($this->filePath);


		if (!$this->checkIdenticalFiles($img, 'string')) {
			return;
		}


		if (!$this->checkSize()) {
			$this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
			return;
		}


		if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
			$this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
			return;
		} else if (!is_writeable($dirname)) {
			$this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
			return;
		}


		if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
			$this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
		} else {
			$this->stateInfo = $this->stateMap[0];

			$this->insertData();
		}
	}
	private function checkIdenticalFiles($tempFileName, $inputType)
	{
		if ($this->DB) {
			if ($inputType == 'string') {
				$this->fileMD5  = md5($tempFileName);
				$this->fileSHA1 = sha1($tempFileName);
			} else {
				$this->fileMD5  = md5_file($tempFileName);
				$this->fileSHA1 = sha1_file($tempFileName);
			}
			$duplicateFiles = $this->DB->query('SELECT UserName, FilePath, PostID FROM ' . PREFIX . 'upload 
				WHERE 
					FileSize = ? AND 
					MD5 = ? AND 
					SHA1 = ?', array(
				$this->fileSize,
				$this->fileMD5,
				$this->fileSHA1
			));
			if ($duplicateFiles) {
				$this->fullName = $duplicateFiles[0]['FilePath'];
				$this->filePath = $this->getFilePath();
				$isInsertData = true;
				foreach ($duplicateFiles as $file) {
					if ($file['UserName'] == $this->CurUserName && $file['PostID'] == 0) {
						$isInsertData = false;
						break;
					}
				}
				if ($isInsertData) {
					$this->insertData();
				}
				$this->stateInfo = $this->stateMap[0];
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}

	private function insertData()
	{
		if ($this->DB) {
			$this->DB->query('INSERT INTO ' . PREFIX . 'upload(`ID`, `UserName`, `FileName`, `FileSize`, `FileType`, `SHA1`, `MD5`, `FilePath`, `Description`, `Category`, `Class`, `PostID`, `Created`) VALUES(:ID, :UserName, :FileName, :FileSize, :FileType, :SHA1, :MD5, :FilePath, :Description, :Category, :Class, :PostID, :Created)', array(
				'ID' => Null,
				'UserName' => $this->CurUserName,
				'FileName' => htmlspecialchars($this->oriName),
				'FileSize' => $this->fileSize,
				'FileType' => $this->fileLongType,
				'SHA1' => $this->fileSHA1,
				'MD5' => $this->fileMD5,
				'FilePath' => $this->fullName,
				'Description' => '',
				'Category' => '',
				'Class' => 'Forum',
				'PostID' => 0,
				'Created' => time()
			));
		}
	}

	/**
	 * @param $errCode
	 * @return string
	 */
	private function getStateInfo($errCode)
	{
		return !$this->stateMap[$errCode] ? $this->stateMap["ERROR_UNKNOWN"] : $this->stateMap[$errCode];
	}

	/**
	 * @return string
	 */
	private function getFileExt()
	{
		return strtolower(strrchr($this->oriName, '.'));
	}

	/**
	 * @return string
	 */
	private function getFullName()
	{
		$t      = time();
		$d      = explode('-', date("Y-y-m-d-H-i-s"));
		$format = $this->config["pathFormat"];
		$format = str_replace("{yyyy}", $d[0], $format);
		$format = str_replace("{yy}", $d[1], $format);
		$format = str_replace("{mm}", $d[2], $format);
		$format = str_replace("{dd}", $d[3], $format);
		$format = str_replace("{hh}", $d[4], $format);
		$format = str_replace("{ii}", $d[5], $format);
		$format = str_replace("{ss}", $d[6], $format);
		$format = str_replace("{time}", $t, $format);

		$oriName = substr($this->oriName, 0, strrpos($this->oriName, '.'));
		$oriName = preg_replace("/[\|\?\"\<\>\/\*\\\\]+/", '', $oriName);
		$format  = str_replace("{filename}", $oriName, $format);

		$randNum = mt_rand(1, 10000000000) . mt_rand(1, 10000000000);
		if (preg_match("/\{rand\:([\d]*)\}/i", $format, $matches)) {
			$format = preg_replace("/\{rand\:[\d]*\}/i", substr($randNum, 0, $matches[1]), $format);
		}

		if ($this->fileType) {
			$ext = $this->fileType;
		} else {
			$ext = $this->getFileExt();
		}
		return $format . $ext;
	}

	/**
	 * @return string
	 */
	private function getFileName()
	{
		return substr($this->filePath, strrpos($this->filePath, '/') + 1);
	}

	/**
	 * @return string
	 */
	private function getFilePath()
	{
		$fullname = $this->fullName;
		$rootPath = $_SERVER['DOCUMENT_ROOT'];

		if (substr($fullname, 0, 1) != '/') {
			$fullname = '/' . $fullname;
		}

		return $rootPath . $fullname;
	}

	/**
	 * @return bool
	 */
	private function checkType()
	{
		return in_array($this->getFileExt(), $this->config["allowFiles"]);
	}

	/**
	 * @return bool
	 */
	private function checkSize()
	{
		return $this->fileSize <= ($this->config["maxSize"]);
	}

	/**
	 * @return array
	 */
	public function getFileInfo()
	{
		return array(
			"state" => $this->stateInfo,
			"url" => $this->fullName,
			"title" => $this->fileName,
			"original" => $this->oriName,
			"type" => $this->fileType,
			"size" => $this->fileSize
		);
	}
}
