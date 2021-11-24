function ReadAndSaveToDBBK($inputFile, $nameOfFile)
{
    //read line by line from txt file 
    $fHandler = fopen($inputFile, 'r') or die('Can\'t open this file');

    //write log
    $error_count = 0;
    $success_count = 0;
    $log_file_name = date('Ymd') . '_' . date('Hi') . $nameOfFile;
    $log_file = fopen($GLOBALS['LOG_FILES_PATH'] . $log_file_name . '.txt', "a+");
    while (!feof($fHandler)) {

        $line = fgets($fHandler);
        if ($line == "")
            continue;

        $line = explode(',', $line);

        //if userSerialNumber is NULL, not process
        $UserSerialNumberIdx = 2;
        $UserSerialNumber = (array_key_exists($UserSerialNumberIdx, $line)) ? $line[$UserSerialNumberIdx] : "";
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

        $rs_insert = $GLOBALS['model']->add($data);
        if (array_key_exists('error', $rs_insert)) {
            $error_count++;
            fwrite($log_file, $rs_insert['query'] . '\n');
        } else {
            $success_count++;
        }
    }
    fwrite($log_file, "error_count:" . $error_count . "\n");
    fwrite($log_file, "success_count:" . $success_count . "\n");

    fclose($log_file);

    fclose($fHandler);
}