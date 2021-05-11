<?php
use PRWS\PRWS;
use PRWS\PRWSAuth;
use PRWS\HTMLSkin;
require_once '../PRWSPHPLib.php';
$nonce_key = PRWS::SetHeaders();
//fixing();
if(!$_SESSION['userid']){
    echo 'Login Required';
    exit;
}
?><meta charset="UTF-8"><?php

if(!$_POST['usage'] || !$_POST['acptNm']){
    HTMLSkin::head('API KEY 발급 - PRWS');
    HTMLSkin::header($nonce_key);
    ?>
    <form action="issueToken.php" method="POST">
        <input type="text" name="host" placeholder="호스트 IP">
        <select name="usage">
            <option value="">용도</option>
            <option value="web">web</option>
            <option value="bot">bot</option>
            <option value="etc">기타</option>
        </select>
        <select name="acptNm">
            <option value="">사용할 API를 선택하세요</option>
            <option value="ComciganSchoolSearch">컴시간 학교검색</option>
            <option value="CSATTestSiteInfo">수능 시험장 정보</option>
            <option value="SchoolInfo">학교 정보</option>
            <option value="MCLatestForgeVersion">마인크래프트 포지 최신버전</option>
            <option value="ComciganTimeTable">컴시간알리미 시간표</option>
            <?php //<option value="RTMModelPackVersion">RTM 애드온(모델 팩) 최신버전</option> ?>
        </select>
        <input type="submit" value="발급">
    </form>
    <?php HTMLSkin::footer();
}else{
    if(strpos($_POST['acptNm'], 'ildcard') !== false){
        echo '잘못된 API명';
        exit;
    }

    $userid = $_SESSION['userid'];
    $usage = $_POST['usage'];

    $host = $_POST['host'];

    // 관리자는 와일드카드
    if ($userid == 'admin') {
        $acptNm = 'wildcard';
    }else{
        $acptNm = $_POST['acptNm'];
    }

    $k = '>PRWSToken<>h='.$host.';a='.$acptNm.';u='.$usage.';i='.$userid.'<';
    $cipher = PRWS::addkey(2);
    $cipher2 = PRWS::addkey(3);
    $key1 = $cipher.str_replace('=', '', base64_encode($k));
    $key2 = $cipher2.str_replace('=', '', base64_encode($key1));
// algo: 키퍼 + base64( 키퍼 + base64( 정보 ) )
$dat = date("Y-m-d H:i:s");
$stmt = $db->stmt_init();
$stmt->prepare("INSERT INTO `apikey` (`issuedat`, `issuedby`, `apikey`) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $dat, $_SESSION['userid'], $key2);
$stmt->execute();
$stmr = $stmt->get_result();
$stmt->close();

    HTMLSkin::head('API KEY 발급 - PRWS');
    HTMLSkin::header($nonce_key); ?>
    <h2> API KEY 발급 완료 </h2>
    <br>
    <p> API KEY는 <?=$key2?> 입니다.<br>
    마이페이지에서도 확인 가능합니다. </p>
    <?php HTMLSkin::footer();
}
?>