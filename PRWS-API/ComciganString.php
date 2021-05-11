<?php
use PRWS\PRWS;
use PRWS\PRWSAuth;
require_once 'PRWSPHPLib.php';
PRWSAuth::inThisDomain('api.prws.kr');
$headerCSP = "Content-Security-Policy:".    // Content Security Policy
             "default-src * 'unsafe-inline';". // 기본은 자기 도메인만 허용
             "report-uri https://prws.kr/csp_report.php;"; // 보안 정책 오류 레포트 URL 지정(meta 태그에선 사용불가)
header($headerCSP);
//include 'checkApiKey.php';
//$check = CheckAPIKey($_GET['key'], 'ComciganString');
//if($check == 0){
//    exit;
//}
//function parseit(){
    $url = 'http://112.186.146.81:4082/st';
    $ttle = file_get_html($url)->find('head', 0);
    $v = $ttle->find('script', 1)->innertext;
    $r = iconv("EUC-KR", "UTF-8", $v);

    //$ingetTT = str_replace('./', '',substr($r, 4057, 5)); // 시간표찾는값
    if(substr($r, strpos($r, "function 화면구성하기(")-24, 1) == '\''){
        $ingetTT = substr($r, strpos($r, "function 화면구성하기(")-23, 5);
    }else{
        $ingetTT = substr($r, strpos($r, "function 화면구성하기(")-24, 6);
    }
    $inGetSchool = str_replace('./', '',substr($r, 1670, 15)); // 학교찾는값
    //$inGetData = str_replace('./', '',substr($r, 3359, 8)); // 데이터찾는값
    $inGetData = substr($r, strpos($r, 'var sc3')+11, 6);
    $a = array('Status' => '200', 'inGetSchool' => $inGetSchool, 'inGetData' => $inGetData, 'inGetTT' => $ingetTT);
if($_GET['type'] =='xml'){
function array_xml($arr, $num_prefix = "_") {
	if(!is_array($arr)) return $arr;
	$result = '';
	foreach($arr as $key => $val) {
		$key = (is_numeric($key)? $num_prefix.$key : $key);
		$result .= '<'.$key.'>'.array_xml($val, $num_prefix).'</'.$key.'>';
	}
	return $result;
}
$xml = array('prws_api' => $a);
header('Content-type: text/xml'); 
echo "<?xml version='1.0' encoding='UTF-8'?>\n";
echo array_xml($xml);
}else{
    Header('Content-type:application/json, Charset=UTF-8');
    echo trim(stripslashes(json_encode($a, JSON_UNESCAPED_UNICODE))); // 역슬래시제거
}
//}
?>
