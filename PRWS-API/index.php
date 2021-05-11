<?php 
session_name("prws_session");
session_set_cookie_params(0, '/', '.prws.kr');
ini_set("session.cookie_domain", ".prws.kr");
session_start();    
use PRWS\PRWS;
use PRWS\PRWSAuth;
use PRWS\HTMLSkin;
require_once '../PRWSPHPLib.php';
$nonce_key = PRWS::SetHeaders();
HTMLSkin::head('PRWS Rest API Service');
HTMLSkin::header($nonce_key); PRWSAuth::logininfo();?>
    <div>
        <h2> PRWS Rest API 이용안내 </h2><br>
        <br>
        <p> PRWS에서는 개발자 여러분의 편의를 위하여 Rest API를 제공하고 있습니다.</p><br>
        <p> 제공하는 데이터 항목은 <a href="https://github.com/aaei924/PRWS_Public/wiki/">해당 페이지</a>를 참고하십시오.</p><br>
        <p> 발급된 API Key는 마이페이지에서 조회할 수 있습니다. </p>
        <?php
        if (isset($_SESSION['userid'])) {
            ?><p><a href="issueToken.php">API Key 신규 발급</a></p><?php
        }else{
            ?><p><a href="https://prws.kr/auth/login">로그인</a> 후 API Key 발급이 가능합니다. </p><?php
        } ?>
    </div>
<?php HTMLSkin::footer();?>