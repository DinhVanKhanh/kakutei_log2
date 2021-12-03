<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/variable.php';

require_once __DIR__ . '/../model/M_Kakutei_Download.php';
require_once __DIR__ . '/../view/V_Main.php';

$model = new M_Kakutei_Download();
$view = new V_Main();

session_start();

$action = $_POST['action'];
switch ($action) {
	case 'loadList':
		http_response_code(200);
		if (!isset($_SESSION['data_view']))
			$_SESSION['data_view'] = [];
		echo json_encode($GLOBALS['view']->showResult($_SESSION['data_view']));
		break;
	case 'uploadFileZip':
		$file_name = $_FILES['zipFileUpload']['name'];
		break;
	case 'uploadFolder':
		$folder_upload_name = $_POST['folder_name'];
		$dir_folder_upload = $_POST['dir_folder'];
		$fullPath = explode($dir_folder_upload, ",");

		$json_decode = json_decode($dir_folder_upload);
		$json_encode = json_encode($dir_folder_upload);

		$arrFullPath = [];
		foreach ($json_decode as $key => $value) {
			$file = explode("/", $value);
			$file_name = end($file);
			$arrFullPath[$value] = $file_name;
		}

		//create folder upload
		if (!file_exists($GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . $folder_upload_name) && !is_dir($GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . $folder_upload_name)) {
			mkdir($GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . $folder_upload_name, 0777);
		}
		$data_view = [];
		$target_file = "";
		$target_dir = $GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . $folder_upload_name;

		foreach ($_FILES['folderUpload']['name'] as $i => $name) {
			if (strlen($_FILES['folderUpload']['name'][$i]) > 1) {
				$target_file = $GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . $folder_upload_name . DIRECTORY_SEPARATOR . basename($_FILES['folderUpload']['name'][$i]);
				// $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

				// if ($fileType != "txt") {
				// 	$uploadOK = 0;
				// 	$note = "File extension must be txt";
				// }
				move_uploaded_file($_FILES['folderUpload']['tmp_name'][$i], $target_file);
				// $data_view[] = ReadAndSaveToDB($target_file, substr($_FILES["folderUpload"]["name"][$i], 0, -4), "folder", $folder_upload_name);
			}
		}
		$data_view = readDirAndInsert($target_dir, $arrFullPath);
		// echo '<pre>';
		// print_r("countFileInDir:" . @$_SESSION['countFileInDir']);
		// echo '<pre>';
		// die();
		$_SESSION['data_view'] = $data_view;
		http_response_code(200);
		echo json_encode($GLOBALS['view']->showResult($data_view));
		break;
	case 'uploadLogFileTxt':
		$message = [];
		$uploadOK = 1;
		$err = "";

		$target_file = $GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . basename($_FILES["fileTxtUpload"]["name"]);
		$checkFile = checkListErrorFile('fileTxtUpload');
		if (!empty($checkFile)) {
			$data_view = [];
			$data_view[] = saveArr($_FILES["fileTxtUpload"]["name"], $checkFile['error']);
			$_SESSION['data_view'] = $data_view;
			http_response_code(200);
			echo json_encode($GLOBALS['view']->showResult($data_view));
		} else {
			move_uploaded_file($_FILES["fileTxtUpload"]["tmp_name"], $target_file);

			$result = ReadAndSaveToDB($target_file, substr($_FILES["fileTxtUpload"]["name"], 0, -4));
			$data_view = [];
			$data_view[] = $result;
			$_SESSION['data_view'] = $data_view;
			http_response_code(200);
			echo json_encode($GLOBALS['view']->showResult($data_view));
		}
		break;
}
function ReadAndSaveToDB($inputFile, $nameOfFile, $config = "file", $nameOfFolder = "")
{
	//read line by line from txt file 
	$fHandler = fopen($inputFile, 'r') or die('Can\'t open this file');
	$result = [];

	$data = HandlingFileNameInfo($nameOfFile);
	//check name file
	if (!$data['success']) {
		$file_name = $nameOfFolder;
		$err['error'][] = 'File name is not correct';
		$result = saveArr($file_name, $err['error']);
		return $result;
	}
	//write log
	$error_count = 0;
	$success_count = 0;
	$log_file_name = date('Ymd') . '_' . date('Hi') . $nameOfFile . '.txt';
	// $log_file_path = $GLOBALS['LOG_FILES_PATH'] . date('Ymd') . '_' . date('Hi') . $nameOfFile . '.txt';
	$log_file_path = $GLOBALS['LOG_FILES_PATH'] . $log_file_name;
	// $log_file = fopen($GLOBALS['LOG_FILES_PATH'] . $log_file_name . '.txt', "a+");
	$log_file = fopen($log_file_path, "a+");

	$para_ins = "";
	$i = 0;
	while (!feof($fHandler)) {

		$line = fgets($fHandler);
		if ($line == "")
			continue;

		$line = explode(',', $line);

		if ($data['nameCategory'] == 'SMB') {
			//partner
			if ($data['brandCategory'] == 'partner') {
				//if userSerialNumber is NULL, not process
				if (array_key_exists(2, $line)) {
					$UserIDPartner = trim($line[2]);
					$UserClassification = 3;
				} elseif (array_key_exists(3, $line)) {
					$UserIDPartner = trim($line[3]);
					$UserClassification = 4;
				} elseif (array_key_exists(4, $line)) {
					$UserIDPartner = trim($line[4]);
					$UserClassification = 5;
				} else {
					$UserIDPartner = "";
				}

				//UserIDPartner
				$UserSerialNumberIdx = 6;
				$UserSerialNumber = (array_key_exists($UserSerialNumberIdx, $line)) ? $line[$UserSerialNumberIdx] : "";
				if (strlen($UserSerialNumber) == 0 && strlen($UserIDPartner) == 0) {
					continue;
				}
				$data['DownloadDate']       = $line[0] . ' ' . $line[1];
				$data['UserSerialNumber']   = $UserSerialNumber;
				$data['UserIDPartner']      = $UserIDPartner;
				$data['UserClassification'] = $UserClassification;
				$data['AdminComment']       = "From CSV";
				$data['AdminCommentDate']   = "2021-10-24 00:00:00";

				$UserIpAddressIdx = 7;
				$data['UserIPAddress']      = (array_key_exists($UserIpAddressIdx, $line)) ? $line[$UserIpAddressIdx] : "NULL";
				// parameter insert
				$para_ins .= ",('{$data['DownloadDate']}','{$data['DownloadProgramName']}', '{$data['DownloadProgramYear']}','{$data['DownloadProgramVersion']}','{$data['UserClassification']}','{$data['UserSerialNumber']}','{$data['UserIDPartner']}','{$data['AdminComment']}','{$data['AdminCommentDate']}', NULL, NULL,'{$data['UserIPAddress']}')";
			} else {
				//user
				$UserSerialNumberIdx = 2;
				$UserSerialNumber = (array_key_exists($UserSerialNumberIdx, $line)) ? trim($line[$UserSerialNumberIdx]) : "";
				if (strlen($UserSerialNumber) == 0) {
					continue;
				}
				$data['DownloadDate']       = $line[0] . ' ' . $line[1];
				$data['UserSerialNumber']   = $UserSerialNumber;
				$data['UserClassification'] = 2;
				$data['AdminComment']       = "From CSV";
				$data['AdminCommentDate']   = "2021-10-24 00:00:00";

				$UserIpAddressIdx = 5;
				$data['UserIPAddress']      = (array_key_exists($UserIpAddressIdx, $line)) ? $line[$UserIpAddressIdx] : "NULL";
				// parameter insert
				$para_ins .= ",('{$data['DownloadDate']}','{$data['DownloadProgramName']}', '{$data['DownloadProgramYear']}','{$data['DownloadProgramVersion']}','{$data['UserClassification']}','{$data['UserSerialNumber']}',NULL,'{$data['AdminComment']}','{$data['AdminCommentDate']}', NULL, NULL,'{$data['UserIPAddress']}')";
			}
		} else {
			//if userSerialNumber is NULL, not process
			$UserSerialNumberIdx = 2;
			$UserSerialNumber = (array_key_exists($UserSerialNumberIdx, $line)) ? trim($line[$UserSerialNumberIdx]) : "";
			if (strlen($UserSerialNumber) == 0) {
				continue;
			}
			$data['DownloadDate']       = $line[0] . ' ' . $line[1];
			$data['UserSerialNumber']   = $UserSerialNumber;
			$data['UserClassification'] = "1";
			$data['AdminComment']       = "From CSV";
			$data['AdminCommentDate']   = "2021-10-21 00:00:00";

			$UserIpAddressIdx = 6;
			$data['UserIPAddress']      = (array_key_exists($UserIpAddressIdx, $line)) ? $line[$UserIpAddressIdx] : "";
			// parameter insert
			$para_ins .= ",('{$data['DownloadDate']}','{$data['DownloadProgramName']}', '{$data['DownloadProgramYear']}','{$data['DownloadProgramVersion']}','{$data['UserClassification']}','{$data['UserSerialNumber']}',NULL,'{$data['AdminComment']}','{$data['AdminCommentDate']}', NULL, NULL,'{$data['UserIPAddress']}')";
		}

		//insert when reach 100 row, reset variables
		if ($i == 100) {
			//remove first comma
			$para_ins = substr($para_ins, 1);

			$insert_rs = $GLOBALS["model"]->addOptimize($para_ins);

			if (array_key_exists('error', $insert_rs)) {
				fwrite($log_file, 'error query:' . $para_ins . "\n");
				fwrite($log_file, $insert_rs['error'] . "\n");
				$error_count += $i;
			} else {
				$success_count += $i;
			}
			$para_ins = "";
			$i = 0;
		}
		++$i;
	}
	//for the last lines that count < 100
	if ($i > 0 && $i < 100) {
		//remove first comma
		$para_ins = substr($para_ins, 1);
		$insert_rs = $GLOBALS["model"]->addOptimize($para_ins);
		if (array_key_exists('error', $insert_rs)) {
			fwrite($log_file, 'error query:' . $para_ins . "\n");
			fwrite($log_file, $insert_rs['error'] . "\n");
			$error_count += $i;
		} else {
			$success_count += $i;
		}
	}

	fwrite($log_file, "error_count:" . $error_count . "\n");
	fwrite($log_file, "success_count:" . $success_count . "\n");

	fclose($log_file);
	fclose($fHandler);

	$file_name = $nameOfFolder;
	$logfile = "<a href='" . $GLOBALS['LOG_FILES_URL'] . $log_file_name . "' target='_blank'>" . $log_file_name . "</a>";
	$error = "";
	$result = saveArr($file_name, $error, $logfile, $success_count, $error_count);
	return $result;
}

function HandlingFileNameInfo($inputFileName)
{
	//file name has format: download_log_11-WinKakutei_AGRI_2006.txt
	//file name has format: download_log_31-eTaxOp_SMB-partner_2007.txt
	//file name has format: download_log_31-eTaxOp_SMB-user_2007.txt
	$data = [];
	$inputFileName = explode('-', $inputFileName);
	$lenFile = count($inputFileName);

	//AGRI
	switch ($lenFile) {
		case 2:
			$downloadProgramIdx = 0;
			$downloadYearIdx = 2;
			$category = 1;

			$namePartsValid = $inputFileName[1];

			$namePartsValid = explode('_', $namePartsValid);
			$DownloadProgramName = $namePartsValid[$downloadProgramIdx];

			$DownloadProgramVersion = DownloadProgramVersion($DownloadProgramName);
			if (!$DownloadProgramVersion) {
				$data['success'] = false;
				break;
			}

			$DownloadProgramYear = $namePartsValid[$downloadYearIdx];
			$nameCategory = $namePartsValid[$category];

			$data['DownloadProgramName'] = $DownloadProgramVersion;
			$data['DownloadProgramVersion'] = "";
			$data['DownloadProgramYear']    = $DownloadProgramYear;
			$data['nameCategory']    = $nameCategory;
			$data['success'] = true;
			break;

			//SMB
		case 3:
			$downloadProgramIdx = 0;
			$downloadYearIdx = 1;
			$category = 1;

			$namePartsValid = $inputFileName[1];
			$namePartsValid = explode('_', $namePartsValid);

			$namePartsYear = $inputFileName[2];
			$namePartsYear = explode('_', $namePartsYear);
			$DownloadProgramName = $namePartsValid[$downloadProgramIdx];

			$DownloadProgramVersion = DownloadProgramVersion($DownloadProgramName);
			if (!$DownloadProgramVersion) {
				$data['success'] = false;
				break;
			}

			$DownloadProgramYear = $namePartsYear[$downloadYearIdx];
			$nameCategory = $namePartsValid[$category];
			$bandCategory = $namePartsYear[$downloadProgramIdx];

			$data['DownloadProgramName'] = $DownloadProgramVersion;
			$data['DownloadProgramVersion'] = "";
			$data['DownloadProgramYear']    = $DownloadProgramYear;
			$data['nameCategory']    = $nameCategory;
			$data['brandCategory']    = $bandCategory;
			$data['success'] = true;
			break;
		default:
			$data['success'] = false;
			break;
	}
	return $data;
}

function DownloadProgramVersion($DownloadProgramName = null)
{
	switch ($DownloadProgramName) {
		case 'WinKakutei':
			$DownloadProgramVersion = '11';
			break;
		case 'Gensen':
			$DownloadProgramVersion = '21';
			break;
		case 'eTaxOp':
			$DownloadProgramVersion = '31';
			break;
		default:
			$DownloadProgramVersion = false;
			break;
	}
	return $DownloadProgramVersion;
}

function filterStringValid($string)
{
}

function saveArr($file_name = "", $message = [], $log_file = "", $success_count = "", $error_count = "")
{
	$result = [];
	$result['filename'] = $file_name;
	$result['datetime'] = date('d/m/Y') . '_' . date('H:i:s');
	$result['success_count'] = (strlen($success_count) != 0) ? $success_count : "No data";
	$result['error_count'] = (strlen($error_count) != 0) ? $error_count : "No data";
	$result['logfile'] = (strlen($log_file) != 0) ? $log_file : "";
	$result['notes'] = !empty($message) ? $message : "";
	// $result['success'] = empty($result['notes']) ? 1 : 0;
	return $result;
}

function checkListErrorFile($file_name = "", $path = "", $i = 0)
{
	$err = [];
	switch ($file_name) {
		case 'fileTxtUpload':
			if (!file_exists($_FILES['fileTxtUpload']['tmp_name']) || !is_uploaded_file($_FILES['fileTxtUpload']['tmp_name'])) {
				$err['uploadOK'] = 0;
				$err['error'][] = "File does not exist, pls try again";
			}
			//filesize valid when < 5MB
			if (filesize($_FILES['fileTxtUpload']['tmp_name']) / 1024 > 5120) {
				$err['uploadOK'] = 0;
				$err['error'][] = "File size only smaller than 5MB";
			}

			$target_file = $GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . basename($_FILES['fileTxtUpload']["name"]);
			//file extension must be .txt
			$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

			if ($fileType != "txt") {
				$err['uploadOK'] = 0;
				$err['error'][] = "File extension must be .txt";
			}
			break;

		case 'folderUpload':
			if (!file_exists($path)) {
				$err['uploadOK'] = 0;
				$err['error'][] = "File does not exist, pls try again";
			}
			//filesize valid when < 5MB
			if (filesize($path) / 1024 > 5120) {
				$err['uploadOK'] = 0;
				$err['error'][] = "File size only smaller than 5MB";
			}

			$target_file = $GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . basename($path);
			//file extension must be .txt
			$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

			if ($fileType != "txt") {
				$err['uploadOK'] = 0;
				$err['error'][] = "File extension must be .txt";
			}
			break;

		default:
			break;
	}
	return $err;
}

function readDirAndInsert_BK($directory = null, $fullPath = "")
{
	$scdir = scandir($directory);

	//remove element 0 and 1
	unset($scdir[0]);
	unset($scdir[1]);

	//reset key in array
	$scdir = array_values($scdir);
	$basename = basename($directory);
	$data_view = [];

	//skips folder/file is intends
	$skips = [
		".",
		"..",
		"images",
		"index1.php",
		// "index.php",
		"data",
		"images_general",
		"lib",
		"case",
		"css",
		"js",
		"minzei",
	];

	foreach ($scdir as $key => $dfile) {
		if (!in_array($dfile, $skips)) {
			$path =  $directory . DIRECTORY_SEPARATOR . $dfile;
			if (is_dir($path)) {
				readDirAndInsert($path);
				continue;
			}
			// use $key to this->file 
			$checkFile = checkListErrorFile('folderUpload', $path);
			if (!empty($checkFile)) {
				$data_view[] = saveArr($dfile, $checkFile['error']);
				continue;
			}
			$data_view[] = ReadAndSaveToDB($path, substr($dfile, 0, -4), "folder", $basename);
		}
	}
	return $data_view;
}


function readDirAndInsert($directory = null, $scdir = [])
{
	$data_view = [];
	//skips folder/file is intends
	$skips = [
		".",
		"..",
		"images",
		"index1.php",
		// "index.php",
		"data",
		"images_general",
		"lib",
		"case",
		"css",
		"js",
		"minzei",
	];

	foreach ($scdir as $key => $aliasFile) {
		if (!in_array($aliasFile, $skips)) {
			$path =  $directory . DIRECTORY_SEPARATOR . $aliasFile;
			if (is_dir($path)) {
				readDirAndInsert($directory, $path);
				continue;
			}
			$checkFile = checkListErrorFile('folderUpload', $path);
			if (!empty($checkFile)) {
				$data_view[] = saveArr($key, $checkFile['error']);
				continue;
			}
			$data_view[] = ReadAndSaveToDB($path, substr($aliasFile, 0, -4), "folder", $key);
		}
	}
	return $data_view;
}
