<?php
//include "../db.php";
use PRWS\PRWS;
use PRWS\PRWSAuth;
require_once 'PRWSPHPLib.php';
//PRWS::fixing();
function json_print($array){
    Header('Content-type:application/json, Charset=UTF-8');
    echo json_encode($array, JSON_UNESCAPED_UNICODE);
}

function CheckAPIKey($key, $apiurl){
    // 유효한 Key에 대해 1 반환
    if(!$key || !$apiurl){
        json_print(array('Status' => 400, 'Message' => 'API KEY를 입력해주세요.'));
        return 0;
        exit;
    }
    $db = new mysqli("localhost","admin","Joey0924!","prwskr");
    $db->set_charset("utf8");
    $stmt = $db->stmt_init();
    $stmt->prepare("SELECT * FROM `apikey` WHERE `apikey`=?");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $stmr = $stmt->get_result();
    $stmt->close();
    if($stmr->num_rows < 1){
        json_print(array('Status' => 400, 'Message' => '유효하지 않은 API Key 입니다.'));
        return 0;
        exit;
    }else{
        $dkey = base64_decode(substr(base64_decode(substr($key, 3)), 2));
        $keyc = str_replace('<', '', str_replace('>PRWSToken<>', '', $dkey));
        $content = explode(';', $keyc);
        //print_r($content);
        $host = str_replace('h=', '', $content[0]);
        $api = str_replace('a=', '', $content[1]);
        $usage = str_replace('u=', '', $content[2]);
        $id = str_replace('i=', '', $content[3]);
        if($_SERVER['REMOTE_ADDR'] == gethostbyname($host)){
            $HostOk = 1;
        }elseif($_SERVER['REMOTE_ADDR'] == '172.30.1.254'){
            $HostOk = 1;
        }else{
            $HostOk = false;
        }
        if($HostOk == false){
            json_print(array('Status' => 300, 'Message' => '유효하지 않은 API Key 입니다.'));
            return 0;
            exit;
        }elseif($api !== 'wildcard' && $api !== $apiurl){
            json_print(array('Status' => 300, 'Message' => '유효하지 않은 API Key 입니다.'));
            return 0;
            exit;
        }else{
            $log_content = date("[Y-m-d H:i:s]").'Connection From '.$_SERVER['REMOTE_ADDR']." in $apiurl WITH KEY $key\n";
            $f = fopen('../../api_use.log', 'a');
            fwrite($f, $log_content);
            fclose($f);
            return 1;
        }

    }
}
?>
