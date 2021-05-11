<?php
use PRWS\PRWS;
use PRWS\PRWSAuth;
require_once 'PRWSPHPLib.php';
PRWSAuth::inThisDomain('api.prws.kr');
$headerCSP = "Content-Security-Policy:".    // Content Security Policy
             "default-src * 'unsafe-inline';". // 기본은 자기 도메인만 허용
             "report-uri https://prws.kr/csp_report.php;"; // 보안 정책 오류 레포트 URL 지정(meta 태그에선 사용불가)
header($headerCSP);
/*include 'checkApiKey.php';
$check = CheckAPIKey($_GET['key'], 'MCLatestForgeVersion');
if($check == 0){
    exit;
}*/
function getNewVersion($ver){
    $url = 'https://files.minecraftforge.net/maven/net/minecraftforge/forge/index_'.$ver.'.html';
    $ttle = file_get_html($url)->find('div[class=title]', 0);
    $v = $ttle->find('small', 0)->plaintext;
    return str_replace("$ver - ", '', $v);
}


if (!$_GET['ver']){
    $json = (['Status' => '400']);
}else{
    $newver = getNewVersion($_GET['ver']);
    $json = array('Status' => '200', 'Version' => $_GET['ver'], 'Forge' => $newver);
    Header('Content-type:application/json, Charset=UTF-8');
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
$xml = array('prws_api' => $json);
header('Content-type: application/xml'); 
echo "<?xml version='1.0' encoding='UTF-8'?>\n";
echo array_xml($xml);
}else{
echo json_encode($json, JSON_UNESCAPED_UNICODE);
}
?>
