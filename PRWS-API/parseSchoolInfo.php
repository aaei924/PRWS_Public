<?php
use PRWS\PRWS;
use PRWS\PRWSAuth;
require_once 'PRWSPHPLib.php';
//fixing();
PRWSAuth::inThisDomain('api.prws.kr');
$headerCSP = "Content-Security-Policy:".    // Content Security Policy
             "default-src * 'unsafe-inline';". // 기본은 자기 도메인만 허용
             "report-uri https://prws.kr/csp_report.php;"; // 보안 정책 오류 레포트 URL 지정(meta 태그에선 사용불가)
header($headerCSP);
/*include 'checkApiKey.php';
$check = CheckAPIKey($_GET['key'], 'parseSchoolInfo');
if($check == 0){
    exit;
}*/
if(!$_GET['Code']){
    $a = array('Status' => 400, 'Message' => '학교코드를 입력하세요.');
}else{
    // 학교알리미 파싱
    $sif = file_get_html('https://www.schoolinfo.go.kr/ei/ss/Pneiss_b01_s0.do?VIEWMODE=1&HG_CD='.$_GET['Code']);

    if ($sif->find('title', 0)->plaintext == '404 - Not Found') {
        $a = array('Status' => 400, 'Message' => '잘못된 학교코드입니다.');
    }else{
        // 기본정보 추출
        $bsc_data = $sif->find('div[class=basic_data]', 0);
        $key_data = $sif->find('div[class=KeyInfo]', 0);
    
        // 학교이름
        $schoolNm = $bsc_data->find('p', 0)->plaintext;
        $schoolNm = iconv('EUC-KR', 'UTF-8', $schoolNm);
        $schoolNm = rtrim(substr($schoolNm, 7, -82), " \t.");

        // 설립구분
        $type = $bsc_data->find('span[class=md]', 0)->plaintext;
        $type = iconv('EUC-KR', 'UTF-8', $type);
        $type = str_replace('설립구분 : ', '', $type);

        // 설립유형
        $danseol = $bsc_data->find('span[class=md]', 1)->plaintext;
        $danseol = iconv('EUC-KR', 'UTF-8', $danseol);
        $danseol = str_replace('설립유형 : ', '', $danseol);

        // 학교특성
        $fea = $bsc_data->find('span[class=md]', 2)->plaintext;
        $fea = iconv('EUC-KR', 'UTF-8', $fea);
        $fea = str_replace('학교특성 : ', '', $fea);
        
        // 설립일자
        $dtm = $bsc_data->find('span[class=md]', 3)->plaintext;
        $dtm = iconv('EUC-KR', 'UTF-8', $dtm);
        $dtm = str_replace('설립일자 : ', '', $dtm);
        $dtm = str_replace('년 ', '-', $dtm);
        $dtm = str_replace('월 ', '-', $dtm);
        $dtm = str_replace('일', '', $dtm);
        $dtm = trim(str_replace("\t", '', $dtm));        

        // 학생수
        $student = $bsc_data->find('span[class=md]', 4)->plaintext;
        preg_match_all('!\d+!', $student, $students);
        $student = $students[0][0];
        $boys = $students[0][1];
        $girls = $students[0][2];

        // 교원수
        $officer = $bsc_data->find('span[class=md]', 5)->plaintext;
        preg_match_all('!\d+!', $officer, $officers);
        $officer = $officers[0][0];
        $men = $officers[0][1];
        $women = $officers[0][2];

        // 체육시설수
        $pespace = $bsc_data->find('span[class=md]', 6)->plaintext;
        $pespace = iconv('EUC-KR', 'UTF-8', $pespace);
        $pespace = preg_replace("/[^0-9]*/s", "", $pespace);
    
        // 대표번호
        $mainnumber = $bsc_data->find('span[class=md]', 7)->plaintext;
        $mainnumber = iconv('EUC-KR', 'UTF-8', $mainnumber);
        $mainnumber = str_replace('대표번호 : ', '', $mainnumber);  

        // 팩스
        $fax = $bsc_data->find('span[class=md]', 8)->plaintext;
        $fax = iconv('EUC-KR', 'UTF-8', $fax);
        $fax = str_replace('팩스 : ', '', $fax);

        // 행정실
        $admin = $bsc_data->find('span[class=md]', 9)->plaintext;
        $admin = iconv('EUC-KR', 'UTF-8', $admin);
        $admin = str_replace('행정실 : ', '', $admin);        
        
        // 교무실
        $tcroff = $bsc_data->find('span[class=md]', 10)->plaintext;
        $tcroff = iconv('EUC-KR', 'UTF-8', $tcroff);
        $tcroff = str_replace('교무실 : ', '', $tcroff);

        // 홈페이지
        $homepage = $bsc_data->find('span[class=md]', 11)->find('a',0)->href;
        $homepage = trim(str_replace("\t", '', $homepage));

        // 주소
        $addr = $bsc_data->find('span[class=md]', 12)->plaintext;
        $addr = iconv('EUC-KR', 'UTF-8', $addr);
        $addr = str_replace('주소 : ', '', $addr);

        // 관할교육청
        $offc = $bsc_data->find('span[class=md]', 13)->plaintext;
        $offc = iconv('EUC-KR', 'UTF-8', $offc);
        $offc = str_replace('관할교육청 : ', '', $offc);

        // 교사당/학급당 학생수
        $stdPerTeacher = $key_data->find('div[class=box]', 2)->find('text', 9)->plaintext;
        //var_dump($key_data->find('div[class=box]', 2)->find('text', 9));
        //$stdPerClass = $key_data->find('div[class=box]', 2)->find('svg', 0)->find('text', 10)->plaintext;
        // 최종배열
        $a = array(
            'Status'                => 200,
            'SchoolName'            => $schoolNm,   // 학교이름
            'SchoolType'            => $type,       // 설립구분(사립)
            'FoundType'             => $danseol,    // 설립형태(단설)
            'SchoolFeature'         => $fea,        // 학교특성
            'FoundYmd'              => $dtm,        // 설립일자
            'Students'              => $student,    // 전체학생
            'StudentsM'             => $boys,       // 남학생
            'StudentsF'             => $girls,      // 여학생
            'Officers'              => $officer,    // 교원
            'OfficersM'             => $men,        // 남자교원
            'OfficersF'             => $women,      // 여자교원
            'PEFacilities'          => $pespace,    // 체육집회시설
            'MainTelephone'         => $mainnumber, // 대표번호
            'Fax'                   => $fax,        // 팩스
            'TeachersOffice'        => $tcroff,     // 교무실
            'AdminOffice'           => $admin,      // 행정실
            'WebSite'               => $homepage,   // 홈페이지
            'Address'               => $addr,       // 주소
            'UpperOffice'           => $offc,       // 관할교육청(도)
            'StudentsPerTeacher'    => $stdPerTeacher, // 교원당학생수
            'StudentsPerClass'      => $stdPerClass // 학급당학생수
        );
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
$xml = array('prws_api' => $xml);
header('Content-type: application/xml'); 
echo "<?xml version='1.0' encoding='UTF-8'?>\n";
echo array_xml($xml);
}else{
Header('Content-Type: application/json; Charset=utf-8');
echo json_encode($a, JSON_UNESCAPED_UNICODE);
}
?>
