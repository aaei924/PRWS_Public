<?php
use PRWS\PRWS;
use PRWS\PRWSAuth;
require_once 'PRWSPHPLib.php';
include 'checkApiKey.php';
//PRWS::fixing();
PRWSAuth::inThisDomain('api.prws.kr');
$headerCSP = "Content-Security-Policy:".    // Content Security Policy
             "default-src * 'unsafe-inline';". // 기본은 자기 도메인만 허용
             "report-uri https://prws.kr/csp_report.php;"; // 보안 정책 오류 레포트 URL 지정(meta 태그에선 사용불가)
header($headerCSP);
/*$check = CheckAPIKey($_GET['key'], 'SchoolInfo');
if($check == 0){
    exit;
}*/
if (!$_GET['Name']) {
    $a = array('Status' => 400, 'Message' => '학교명을 입력하세요.');
}elseif(!$_GET['Region']){
    $a = array('Status' => 400, 'Message' => '지역명을 입력하세요.');
}else{

    $region = array(
        '해외' => 00,
        '서울' => 01,
        '부산' => 02,
        '대구' => 03,
        '인천' => 04,
        '광주' => 05,
        '대전' => 06,
        '울산' => 07,
        '세종' => '08',
        '경기' => 10,
        '강원' => 11,
        '충북' => 12,
        '충남' => 13,
        '전북' => 14,
        '전남' => 15,
        '경북' => 16,
        '경남' => 17,
        '제주' => 18
    );
    $fullregion = array(
        '해외' => '해외',
        '서울' => '서울',
        '부산' => '부산',
        '대구' => '대구',
        '인천' => '인천',
        '광주' => '광주',
        '대전' => '대전',
        '울산' => '울산',
        '세종' => '세종',
        '경기' => '경기',
        '강원' => '강원',
        '충북' => '충청북도',
        '충남' => '충청남도',
        '전북' => '전라북도',
        '전남' => '전라남도',
        '경북' => '경상북도',
        '경남' => '경상남도',
        '제주' => '제주'
    );
    $reg = array(
        '서울' => 1,
        '부산' => 2,
        '대구' => 3,
        '인천' => 4,
        '광주' => 5,
        '대전' => 6,
        '울산' => 7,
        '세종' => 8,
        '경기' => 9,
        '강원' => 10,
        '충북' => 11,
        '충남' => 12,
        '전북' => 13,
        '전남' => 14,
        '경북' => 15,
        '경남' => 16,
        '제주' => 17
    );

    /* 코드 테스트 학교
    - 컴시간 지역 잘리는 학교:      분당중앙고, 신탄중앙중
    - 리로스쿨 쓰는 동명학교:       오산고등학교(서울), 오산고등학교(경기)
    - 컴시간 쓰는 동명학교:         한솔고등학교(경기), 한솔고등학교(세종)
    - 리로스쿨만 쓰는 학교:         분당대진고등학교
    - 컴시간만 쓰는 학교:           정자중학교
    - 공립 + 컴시간 + 리로스쿨:     서현고등학교
    - 사립 동명학교:                오산고등학교 (서울/경기)
    - 사립 + 컴시간 + 리로스쿨:     휘문고등학교


    추가 지원 계획(POST 정보)
    - ISIC(국제학생증) 학교번호
    - 학군안내
    
    */

        // 자가진단
    $html = file_get_html('https://hcs.eduro.go.kr/v2/searchSchool?orgName='.urlencode($_GET['Name']));
    $hcs = json_decode($html, true);
    $hcs = $hcs["schulList"];
    $search = $_GET['Name'];
                
        // 컴시간
    $comcigan = file_get_html('https://api.prws.kr/ComciganSchoolSearch?key=X6bRU1QbEJTVjFOVWIydGxiancrYUQxd2NuZHpMbXR5TzJFOWQybHNaR05oY21RN2RUMTNaV0k3YVQxaFpHMXBianc&SchoolName='.urlencode($_GET['Name'])); 
    $comci = json_decode($comcigan, true);
    $comci = $comci['Results'];

        // 리로스쿨
    $riroschool = file_get_html('https://crm.rirosoft.com/api/v2/school/'.urlencode($_GET['Name']));
    $riro = json_decode($riroschool, true);
    $riro = $riro['data'];

        // NEIS
    $g = file_get_html('https://open.neis.go.kr/hub/schoolInfo?KEY=68182fd68d404fdd89272e42abad373c&Type=json&SCHUL_NM='.urlencode($_GET['Name']));
    $neis = json_decode($g, true);
    $neis = $neis['schoolInfo'][1]['row'];


    // 자가진단 배열검색
    foreach ($hcs as $hk => $hs) {
        $hi = array_search($search, $hs);
        if ($hcs[$hk]['lctnScCode'] == $region[$_GET['Region']]) {
            $hcs = $hcs[$hk];
            global $hcs;
            
        }          
    }

    // NEIS 배열검색
    foreach ($neis as $nk => $ns) {
        $ni = array_search($search, $ns);
        if (strpos($neis[$nk]['LCTN_SC_NM'],$fullregion[$_GET['Region']]) !== false) {
            $neis = $neis[$nk];
        }
        global $neis;     
    }

    if ($neis['FOND_SC_NM'] == '사립') {
        // 사학연금
        $tp = file_get_html('http://www.tp.or.kr:8088/tp/cc/PIASItSrUC2.jsp?_js_rs=rs&PGACCESS=R:999&ACTION=SELECT&txtNmInst='.iconv('UTF-8', 'EUC-KR', $_GET['Name']));
        $tpa = iconv('EUC-KR', 'UTF-8', $tp);
        $tpa = str_replace(
            'var rs=new jsResultSet(); rs.setError("세션이 없습니다."); var rs0=new jsResultSet(); rs0.md.setTableName("oTbl"); rs0.md.setNames(new Array("CD_INST","NM_INST","NM_DGR_SCH","CD_OLDINST","NM_CDT_SCH","NO_PH","ADR2","CD_DGR_SCH","CD_ZIP","ADR","NO_HS","NM_STRD_COINST","CD_REGN","CD_CRPINST","CD_HIGNINST","DD_SLR","CD_SEQ_NTC","CD_CDT_SCH","YN_SPCFY_OPTN","CD_CL_UNIV","YN_CSRV","NO_DOC","NM_CRPINST","NO_FAX","DT_APP_LAW")); rs0.md.setTypes(new Array("String","String","String","String","String","String","String","String","String","String","String","String","String","String","String","String","String","String","String","String","String","String","String","String","String")); rs0.add(new Array("',
            '',
            $tpa
        );
        $tpa = str_replace('rs0.add(new Array("', '|', $tpa);
        $tpa = str_replace('")); ', '', $tpa);
        $tpa = str_replace('//end', '', $tpa);
        //print_r($tpa);
        if (strpos($tpa, '|')) {
            $p = explode('|', $tpa);
            //print_r($p);
            $sahak_data = array();
            foreach ($p as $pe) {
                $d = explode('","', $pe);
                if (strpos($d[6], $fullregion[$_GET['Region']]) !== false) {
                    $s_output = $d;
                    global $s_output;
                    
                }
            }
            $tpa = $s_output;
        }else{
            // 2개 이상의 검색결과 대응
            //$p = explode('|', $tpa);
    
            /*
            $sahak_data = array();
            foreach($p as $pe){
                $d = explode('","', $pe);
                array_push($sahak_data, $d);
            }
            $sahak = array('Status' => 200, 'Results' => $sahak_data);
            */
    
            $tpa = explode('","', $tpa);

            // print_r($tpa);       코드 테스트용
        }
    /*
    사학연금 사이트 데이터 조회 내용

    <코드명>           <설명>            <인덱스>

    CD_INST:        학교기관코드            0
    CD_OLDINST:     구 학교코드             3
    NM_INST:        기관명                  1
    CD_DGR_SCH:     학교급코드              7
    NM_DGR_SCH:     학교급명                2
    CD_ZIP:         우편번호                8
    ADR:            주소                    9
    ADR2:           주소2                   6
    NO_HS:          번지                    10
    NM_STRD_COINST: 기관장직위명            11
    CD_REGN:        지역코드                12
    CD_CRPINST:     법인기관코드            13
    NM_CRPINST:     법인기관명              22
    NO_PH:          전화번호                5
    NO_FAX:         팩스                    23
    CD_HIGNINST:    상위기관코드            14
    DD_SLR:         봉급일자                15
    CD_SEQ_NTC:     고지차수코드            16
    CD_CDT_SCH:     학교상태코드            17
    NM_CDT_SCH:     학교상태                4
    YN_SPCFY_OPTN:  임의지정여부            18
    CD_CL_UNIV:     대학교구분코드          19
    YN_CSRV:        근속해당여부            20
    NO_DOC:         문서번호                21
    DT_APP_LAW:     법 적용일               24

    */
    }else{
        $tpa = array(null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null);
        // 공립학교
    }

    // 리로스쿨 배열검색
    foreach ($riro as $rk => $rs) {
        $ri = array_search($search, $rs);
        if (strpos($riro[$rk]['city'], $fullregion[$_GET['Region']]) !== false) {
            $rirourl = $riro[$rk]['siteId'];
        }
        global $rirourl;
    }

    // 컴시간 배열검색
    foreach ($comci as $ck => $cs) {
        $ci = array_search($search, $cs);
        if ($comci[$ck]['Region'] == $_GET['Region']) {
            $comcicode = $comci[$ck]['ComciCode'];
        }
        global $comcicode;
    }

    // 학교알리미
    $sci = file_get_html('https://api.prws.kr/parseSchoolInfo?key=X6bRU1QbEJTVjFOVWIydGxiancrYUQxd2NuZHpMbXR5TzJFOWQybHNaR05oY21RN2RUMTNaV0k3YVQxaFpHMXBianc&Code='.$hcs['orgCode']);
    $si = json_decode($sci, true);

    // 컴시간/리로 사용 안하는 학교(중학교 이상)
    if(!$comcicode){
        $comcicode = '컴시간알리미를 사용하지 않음';
    }
    if(!$rirourl){
        $rirourl = '리로스쿨을 사용하지 않음';
    }

    

    $a = array(
        'Status' => 200,
        'Message' => '정상 처리되었습니다.',
        'TPSchoolStatus'              => $tpa[4].'('.$tpa[17].')',            // 사립학교상태
        'SchoolName'                => $hcs['orgAbrvNm01'],                 // 학교이름
        'NEISName'                  => $hcs['kraOrgNm'],                    // 전산상 명칭
        'EnglishName'               => $neis['ENG_SCHUL_NM'],               // 영문이름
        'SchoolType'                => $neis['SCHUL_KND_SC_NM'],            // 학교종류
        'SchoolCode'                => $hcs['orgCode'],                     // 학교코드
        'SchoolLevel'               => $hcs['schulKndScCode'],              // 학교레벨코드
        'OrgLevel'                  => $hcs['insttClsfCode'],               // 기관분류코드
        'TPCode'                    => $tpa[0],                             // 사학연금코드
        'OldTPCode'                 => $tpa[3],                             // 구 사학연금코드
        'TPRegCode'                 => $tpa[12],                            // 사학지역코드
        'FoundYmd'                  => $neis['FOND_YMD'],                   // 설립일
        'FoundAnnvYmd'              => $neis['FOAS_MEMRD'],                 // 개교기념일
        'LawApplied'                => $tpa[24],                            // 법률적용일
        'HighSchoolType'            => $neis['HS_SC_NM'],                   // 고교구분
        'SpcHighSchoolType'         => $neis['SPCLY_PURPS_HS_ORD_NM'],      // 특목고종류
        'HighSchoolType2'           => $neis['HS_GNRL_BUSNS_SC_NM'],        // 일반/실업 여부
        'IndustrialSpecialClass'    => $neis['INDST_SPECL_CCCCL_EXST_YN'],  // 산업체특별학급유무
        'FoundType'                 => $si['FoundType'],                    // 단설여부
        'OperationType'             => $neis['FOND_SC_NM'],                 // 운영형태
        'SchoolGender'              => $neis['COEDU_SC_NM'],                // 남녀구분상태
        'DayOrNight'                => $neis['DGHT_SC_NM'],                 // 주야간구분
        'FormerOrLatter'            => $neis['ENE_BFE_SEHF_SC_NM'],         // 전후기구분
        'Students'                  => $si['Students'],                     // 학생수
        'StudentsM'                 => $si['StudentsM'],                    // 남학생수
        'StudentsF'                 => $si['StudentsF'],                    // 여학생수
        'Officers'                  => $si['Officers'],                     // 교직원수
        'OfficersM'                 => $si['OfficersM'],                    // 남직원수
        'OfficersF'                 => $si['OfficersF'],                    // 여직원수
        'StudentsPerTeacher'        => $si['StudentsPerTeacher'],           // 교사당학생수
        'StudentsPerClass'          => $si['StudentsPerClass'],             // 학급당학생수
        'Region'                    => $hcs['lctnScNm'],                    // 소재지
        'HeadOfficerName'           => $tpa[11],                            // 기관장직위명
        'CRPName'                   => $tpa[22],                            // 법인기관명
        'CRPCode'                   => $tpa[13],                            // 법인기관코드
        'UpperPrvOE'                => $neis['ATPT_OFCDC_SC_NM'],           // 상위 교육청
        'UpperOfficeOfEducation'    => $neis['JU_ORG_NM'],                  // 관할기관명
        'UpperOrgCode'              => $hcs['juOrgCode'],                   // 관할기관코드
        'UpperInstTPCode'           => $tpa[14],                            // 상위기관사학코드
        'Address'                   => $neis['ORG_RDNMA'],                  // 주소(도로명)
        'AddressDetail'             => $neis['ORG_RDNDA'],                  // 상세주소
        'ZipCode'                   => $neis['ORG_RDNZC'],                  // 우편번호
        'Telephone'                 => $neis['ORG_TELNO'],                  // 전화번호
        'TeachersOffice'            => $si['TeachersOffice'],               // 교무실
        'AdminOffice'               => $si['AdminOffice'],                  // 행정실
        'Fax'                       => $neis['ORG_FAXNO'],                  // 팩스
        'SalaryDate'                => $tpa[15],                            // 사립학교봉급일자
        'NotifySeq'                 => $tpa[16],                            // 사학고지차수코드
        'StdAdmCode'                => $neis['SD_SCHUL_CODE'],              // 표준행정코드
        'PEFacilities'              => $si['PEFacilities'],                 // 체육집회공간수
        'WebSite'                   => $neis['HMPG_ADRES'],                 // 학교홈페이지
        'Comcigan'                  => $comcicode,                          // 컴시간학교코드
        'RiroSchool'                => $rirourl,                            // 리로스쿨아이디
    );
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
echo stripslashes(json_encode($a, JSON_UNESCAPED_UNICODE));
Header('Content-Type: application/json; Charset=utf-8');

}
?>
