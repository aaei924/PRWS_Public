<?php
use PRWS\PRWS;
use PRWS\PRWSAuth;
use PRWS\Comcigan;
use PRWS\PRWSParser;
require_once 'PRWSPHPLib.php';
//PRWS::fixing();
PRWSAuth::inThisDomain('api.prws.kr');
$headerCSP = "Content-Security-Policy:".    // Content Security Policy
             "default-src * 'unsafe-inline';". // 기본은 자기 도메인만 허용
             "report-uri https://prws.kr/csp_report.php;"; // 보안 정책 오류 레포트 URL 지정(meta 태그에선 사용불가)
header($headerCSP);
/*include 'checkApiKey.php';
$check = CheckAPIKey($_GET['key'], 'ComciganTimeTable');
if($check == 0){
    exit;
}*/

$u = 'https://api.prws.kr/ComciganString';
$srr1 = PRWSParser::parse_json($u);
    
// 컴시간 이번주/다음주 구분 0/1
$wstring = $_GET['week'];

// 학교코드 -> 학교명 순서로 조회
if (!($_GET['Region'] && $_GET['SchoolName']) && !$_GET['SchoolCode']){
    $a = array('Status' => 400, 'Message' => '적절한 인자가 입력되지 않았습니다.');
}else{
    if (isset($_GET['SchoolCode'])) {
        $t_string = $srr1['inGetTT'].'_'.$_GET['SchoolCode'].'_0_'.$wstring;        //  {스트링}_{학교코드}_0_{1:이번주, 2:다음주}
    } elseif (isset($_GET['Region']) && isset($_GET['SchoolName'])) {
        $t_string = $srr1['inGetTT'].'_'.Comcigan::getcode($_GET['SchoolName'], $_GET['Region']).'_0_'.$wstring;
    }
    $tturl = 'http://112.186.146.81:4082/'.$srr1['inGetData'].'_T?'.base64_encode($t_string);
    $r = PRWSParser::parse_json($tturl);
    $rk = array_keys($r);

    if (PRWS::count($r[$rk[25]]) !== 4) { // Comcigan 버그 대응
        $time = 24;
    } else {
        $time = 25;
    }

    $tt_array = array(
    'Version'       => $r['버젼'],              // 버전
    'Region'        => $r['지역명'],            // 지역명
    'SchoolName'    => $r['학교명'],            // 학교명
    'AcadmcYear'    => $r['학년도'],            // 학년도
    'SemesterStart' => $r['학기시작일자'],      // 학기시작일
    'ViewFrom'      => $r['열람제한일'],        // 열람제한일
    'StartOfWeeks'  => $r['시작일'],            // 주 시작일
    'Weeks'         => $r['일자자료'],          // 일자자료
    'Teachers'      => $r['교사수'],            // 교사수
    'Teacher'       => $r[$rk[1]],              // 교사명단
    'GroupTeacher'  => $r['복수교사'],          // 복수교사
    'Homeroom'      => $r['담임'],             // 담임교사
    'Subjects'      => $r[$rk[4]],             // 과목리스트
    'SubjectsAbbr'  => $r[$rk[5]],             // 과목약칭리스트
    'Classes'       => $r['학급수'],            // 학급수[학년]
    'Classrooms'    => $r['강의실'],            // 강의실수
    'VirtualCls'    => $r['가상학급수'],        // 가상학급수
    'SpcRoom'       => $r['특별실수'],          // 특별실수
    'LessonTime'    => $r['일과시간'],          // 일과시간
    'Lessons'       => $r['요일별시수'],        // 요일별시수[학년][요일]
    'SameTime'      => $r['동시수업수'],        // 동시수업수
    'AllDay'        => $r['전일제'],            // 전일제
    'TimeTableDef'  => $r[$rk[6]],             // 기본시간표
    'TimeTableEdited' => $r[$rk[$time]],       // 수정된시간표
    'TeachersTimeEdited' => $r[$rk[$time + 1]], // 교사용시간표(수정)
    'Classroom'     => $r[$rk[$time + 2]],      // 강의실시간표
    'UpdatedAt'     => $r[$rk[14]],             // 업데이트시간
    );
    $a = array('Status' => 200, 'Message' => '정상 처리되었습니다.', 'Results' => $tt_array);
}
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
    echo stripslashes(json_encode($a, JSON_UNESCAPED_UNICODE)); // 역슬래시제거
}   
?>
