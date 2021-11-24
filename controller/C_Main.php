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
        if(!isset($_SESSION['data_view'])) 
            $_SESSION['data_view'] = [];
        echo json_encode($GLOBALS['view']->showResult($_SESSION['data_view']));
        break;
    case 'uploadFileZip':
        $file_name = $_FILES['zipFileUpload']['name'];

    break;
    case 'uploadFolder':
        $folder_upload_name = $_POST['folder_name'];
        //create folder upload
        if ( !file_exists( $GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . $folder_upload_name ) && !is_dir( $GLOBALS['TARGET_FOLDER_UPLOAD_DIR'].$folder_upload_name ) ) {
            mkdir($GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . $folder_upload_name, 0777);
        }
        $data_view = [];
        foreach ($_FILES['folderUpload']['name'] as $i => $name) {
            if (strlen($_FILES['folderUpload']['name'][$i]) > 1) {
                $target_file = $GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . $folder_upload_name . basename($_FILES['folderUpload']['name'][$i]);
                $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                if ($fileType != "txt") {
                    $uploadOK = 0;
                    $note = "File extension must be txt";
                }
                move_uploaded_file($_FILES['folderUpload']['tmp_name'][$i], $target_file);
                $data_view[] = ReadAndSaveToDB($target_file, substr($_FILES["folderUpload"]["name"][$i], 0, -4), "folder", $folder_upload_name);
            }
        }
        $_SESSION['data_view'] = $data_view;
        http_response_code(200);
        echo json_encode($GLOBALS['view']->showResult($data_view));
        break;
    case 'uploadLogFileTxt':
        $message = [];
        $uploadOK = 1;

        if (!file_exists($_FILES['fileTxtUpload']['tmp_name']) || !is_uploaded_file($_FILES['fileTxtUpload']['tmp_name'])) {
            $uploadOK = 0;
            $message['error'][] = "File does not exist, pls try again";
        }
        //filesize valid when < 5MB
        if (filesize($_FILES['fileTxtUpload']['tmp_name'])/1024 > 5120){
            $uploadOK = 0;
            $message['error'][] = "File size only smaller than 5MB";
        }

        $target_file = $GLOBALS['TARGET_FOLDER_UPLOAD_DIR'] . basename($_FILES["fileTxtUpload"]["name"]);
        //file extension must be .txt
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if ($fileType != "txt") {
            $uploadOK = 0;
            $message['error'][] = "File extension must be .txt";
        }

        if ($uploadOK == 0) {
            $data_view = [];
            $result = [];
            $result['filename'] = $_FILES["fileTxtUpload"]["name"];
            $result['datetime'] = date('d/m/Y') . '_' . date('H:i:s');
            $result['success_count'] = "No data";
            $result['error_count'] = "No data";
            $result['logfile'] = "";
            $result['notes'] = $message['error'];
            $data_view[] = $result;
            $_SESSION['data_view'] = $data_view;
            http_response_code(200);
            echo json_encode($GLOBALS['view']->showResult($data_view));
        } else {
            move_uploaded_file($_FILES["fileTxtUpload"]["tmp_name"], $target_file);
            ReadAndSaveToDB($target_file, substr($_FILES["fileTxtUpload"]["name"], 0, -4));
        }
        break;
}

function ReadAndSaveToDB($inputFile, $nameOfFile, $config="file", $nameOfFolder="")
{
    //read line by line from txt file 
    $fHandler = fopen($inputFile, 'r') or die('Can\'t open this file');

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

        //if userSerialNumber is NULL, not process
        $UserSerialNumberIdx = 2;
        $UserSerialNumber = (array_key_exists($UserSerialNumberIdx, $line)) ? trim($line[$UserSerialNumberIdx]) : "";
        if (strlen($UserSerialNumber) == 0) {
            continue;
        }

        $data = HandlingFileNameInfo($nameOfFile);
        $data['DownloadDate']       = $line[0] . ' ' . $line[1];
        $data['UserSerialNumber']   = $UserSerialNumber;
        $data['UserClassification'] = "1";
        $data['AdminComment']       = "From CSV";
        $data['AdminCommentDate']   = "2021-10-21 00:00:00";

        $UserIpAddressIdx = 6;
        $data['UserIPAddress']      = (array_key_exists($UserIpAddressIdx, $line)) ? $line[$UserIpAddressIdx] : "";
        // parameter insert
        $para_ins .= ",('{$data['DownloadDate']}','{$data['DownloadProgramName']}', '{$data['DownloadProgramYear']}','{$data['DownloadProgramVersion']}','{$data['UserClassification']}','{$data['UserSerialNumber']}',NULL,'{$data['AdminComment']}','{$data['AdminCommentDate']}', NULL, NULL)";
        //insert when reach 1000 row, reset variables
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

    $result = [];
    $result['filename'] = (strlen($nameOfFolder)!= 0) ? $nameOfFolder . "/" . $nameOfFile . ".txt" : $nameOfFile . ".txt";
    $result['datetime'] = date('d/m/Y') . ' ' . date('H:i:s');
    $result['success_count'] = $success_count;
    $result['error_count'] = $error_count;
    $result['logfile'] = "<a href='" . $GLOBALS['LOG_FILES_URL'] . $log_file_name ."' target='_blank'>" . $log_file_name . "</a>";
    $result['notes'] = "";

    if ($config == "file"){
        $data_view = [];
        $data_view[] = $result;
        $_SESSION['data_view'] = $data_view;
        http_response_code(200);
        echo json_encode($GLOBALS['view']->showResult($data_view));
    }
    else {
        return $result;
    }
}

function HandlingFileNameInfo($inputFileName)
{
    //file name has format: download_log_11-WinKakutei_AGRI_2006.txt
    $inputFileName = explode('-', $inputFileName);

    $namePartsValid = $inputFileName[1];

    $namePartsValid = explode('_', $namePartsValid);
    $downloadProgramIdx = 0;
    $downloadYearIdx = 2;
    $DownloadProgramName = $namePartsValid[$downloadProgramIdx];
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
    }
    $DownloadProgramYear = $namePartsValid[$downloadYearIdx];

    $data = [];
    $data['DownloadProgramName'] = $DownloadProgramVersion;
    $data['DownloadProgramVersion'] = "";
    $data['DownloadProgramYear']    = $DownloadProgramYear;

    return $data;
}
function filterStringValid($string){

}