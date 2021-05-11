<?php
use PRWS\PRWS;
use PRWS\PRWSAuth;
require_once '../PRWSPHPLib.php';
PRWSAuth::inThisDomain('api.prws.kr');
$headerCSP = "Content-Security-Policy:".    // Content Security Policy
             "default-src * 'unsafe-inline';". // 기본은 자기 도메인만 허용
             "report-uri https://prws.kr/csp_report.php;"; // 보안 정책 오류 레포트 URL 지정(meta 태그에선 사용불가)
header($headerCSP);
include 'checkApiKey.php';
$check = CheckAPIKey($_GET['key'], 'CSATTestSiteInfo');
if($check == 0){
    exit;
}
if(!$_GET['SchoolName']){
    $a = array('Status' => 400, 'Message' => '검색어를 입력해주세요.');
}else{
    $school = iconv('UTF-8', 'EUC-KR', $_GET['SchoolName']);
    $html = file_get_html('http://www.weather.go.kr/weather/special/special_exam_03.jsp?schoolName='.urlencode($school));
    $tr = $html->find('tr[class=td_03]');
    $pg = $html->find('div[class=paging_wrap]', 0)->plaintext;
    if(PRWS::count($tr) < 1){
        $a = array('Status' => 400, 'Message' => '검색 결과가 없습니다.');
    }else{
        if (preg_match('/[0-9]/', $pg)) {
            $sizeover = true;
        } else {
            $sizeover = false;
        }
        $results = array();
        foreach ($tr as $e) {
            $sname = $e->find('td', 0)->plaintext;
            $address = $e->find('td', 1)->plaintext;
            $tel = $e->find('td', 2)->plaintext;
            $info = array('SchoolName' => $sname, 'Address' => str_replace('&nbsp;', ' ', $address), 'Telephone' => $tel);
            array_push($results, $info);
        }
        $a = array('Status' => 200, 'Version' => 2020, 'Results' => $results, 'sizeover' => $sizeover);
    }
}
if($_GET['type'] =='xml'){
function array_xml($arr, $num_prefix = "num_") {
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
header('Content-Type: application/json; charset=utf-8');
echo json_encode($a, JSON_UNESCAPED_UNICODE);
}
?>