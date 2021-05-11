<?php
use PRWS\PRWS;
use PRWS\PRWSAuth;
use PRWS\PRWSParser;
require_once 'PRWSPHPLib.php';
PRWSAuth::inThisDomain('api.prws.kr');
$headerCSP = "Content-Security-Policy:".    // Content Security Policy
             "default-src * 'unsafe-inline';". // 기본은 자기 도메인만 허용
             "report-uri https://prws.kr/csp_report.php;"; // 보안 정책 오류 레포트 URL 지정(meta 태그에선 사용불가)
header($headerCSP);
//PRWS::fixing();
/*include 'checkApiKey.php';
$check = CheckAPIKey($_GET['key'], 'ComciganSchoolSearch');
if($check == 0){
    exit;
}*/
    // URL 숫자값
    $u = 'http://api.prws.kr/ComciganString';
    $str1 = file_get_html($u);
    $srr1 = json_decode($str1, true);
    $name2 = str_replace('학교', '', $_GET['SchoolName']);
    $name3 = str_replace('등학교', '', $_GET['SchoolName']);

    if ($_GET['SchoolName']) {
    // 받음
        //function SearchSchool($keyword){
            $kw = iconv("UTF-8", "EUC-KR", $_GET['SchoolName']);
            if (substr($srr1['inGetSchool'], -1, 1) !== 'l') {
                $string = $srr1['inGetSchool'].'l';
            }else{
                $string = $srr1['inGetSchool'];
            }
            $url = 'http://112.186.146.81:4082/'.$string.$kw;
            $get = json_decode(file_get_html($url), true);
            $c = $get['학교검색'];
        //}
        //$e = SearchSchool($_GET['SchoolName']);

        if (PRWS::count($c) == 0) {
            // 결과 없음-> 중학교 철자빼고 검색
            $kw = iconv("UTF-8", "EUC-KR", $name2);
            $url = 'http://112.186.146.81/'.$srr1['inGetSchool'].'l'.$kw;
            $get = json_decode(file_get_html($url), true);
            $f = $get['학교검색'];
            if (PRWS::count($f) == 0) {
                // 결과없음 -> 고등학교 철자빼고 검색
                $kw = iconv("UTF-8", "EUC-KR", $name3);
                $url = 'http://112.186.146.81:4082/'.$srr1['inGetSchool'].'l'.$kw;
                $get = json_decode(file_get_html($url), true);
                $g = $get['학교검색'];
                if (PRWS::count($g) == 0) {
                    // 진짜 없음
                    $a = array('Status' => '400', 'Message' => '검색 결과가 없습니다.');
                    Header('Content-type:application/json, Charset=UTF-8');
                    echo json_encode($a, JSON_UNESCAPED_UNICODE);
                    
                    exit;
                } else {
                    $e = $g;
                }
            } else {
                $e = $f;
            }
        }else{
            $e = $c;     
        }
        // 결과 있음
            $a = array('Status' => 200);
            $re = array();

            // 결과값 배열 창출
            foreach ($e as $k => $v) {
                $sc = array('Region' => $v[1], 'SchoolName' => $v[2], 'ComciCode' => $v[3]);
                array_push($re,$sc);
            }
            $a['Results'] = $re;
            // 컴시간 배열검색
            if (isset($_GET['Region'])) {
                foreach ($re as $el) {
                    if ($el['Region'] == $_GET['Region']) {
                        $sc = array('Region' => $el['Region'], 'SchoolName' => $el['SchoolName'], 'ComciCode' => $el['ComciCode']);
                    }
                }
                $a = array('Status' => 200, 'Message' => '정상 처리되었습니다.', 'Result' => $sc);
            }
                
    }elseif(isset($_GET['SchoolCode'])){
        // 학교코드로 검색
        $u = 'http://api.prws.kr/ComciganString';
        $srr1 = PRWSParser::parse_json($u);
        $t_string = $srr1['inGetTT'].'_'.$_GET['SchoolCode'].'_0_1';
        $tturl = 'http://112.186.146.81:4082/'.$srr1['inGetData'].'_T?'.base64_encode($t_string);
        $r = PRWSParser::parse_json($tturl);
        $s = $r['학교명'];
        $reg = $r['지역명'];
        if(!$r['학교명']){
            $a = array('Status' => '400', 'Message' => '검색 결과가 없습니다.');
        }else{
            $a = array('Status' => 200, 'Message' => '정상 처리되었습니다.');
            $result = array('SchoolCode' => $_GET['SchoolCode'], 'SchoolName' => $s, 'Region' => $reg);
            $a['Result'] = $result;
        }
    }else{
        $a = array('Status' => '400', 'Message' => '검색할 단어를 입력하세요.');
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
    Header('Content-type:application/json, Charset=UTF-8');  
    echo json_encode($a, JSON_UNESCAPED_UNICODE);
 }

?>
