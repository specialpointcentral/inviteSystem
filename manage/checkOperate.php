<?php
/**
 * Created by PhpStorm.
 * User: huqi1
 * Date: 2018/1/30
 * Time: 16:31
 */
/**
 * @param $email
 */
function invite($email){
    $config=$GLOBALS['config'];
    $email = safe_email($email);
    //check email is exist?
    $db=new mysqli($config["SQL_URL"], $config["SQL_User"], $config["SQL_Password"], $config["SQL_Database"], $config["SQL_Port"]);
    if($db->connect_error){
        $echo = array(
            'code' => '500',
            'info'=>'cannot connect database'.$db->connect_error,
            'msg' => '数据库错误',
        );
        echo json_encode($echo);
        exit();
    }
    $sql="SELECT * FROM tbl_apply WHERE email = '".base64_encode($email)."'";
    $result=$db->query($sql);
    if($result->num_rows==0){
        $echo = array(
            'code' => '402',
            'info'=>'email address is not exist',
            'msg' => 'Email地址不存在',
        );
        echo json_encode($echo);
        exit();
    }else{
        $row=$result->fetch_assoc();
        $name=$row['name'];
    }
    if (!$email) {
        $echo = array(
            'code' => '400',
            'info'=>'email address is incorrect',
            'msg' => 'Email地址不正确',
        );
        echo json_encode($echo);
        $postTime=date("Y-m-d H:i:s",time());
        $sql="UPDATE tbl_apply SET status = '-1' , errorList = '".$echo['msg']."' , checkTime = '".$postTime."' WHERE email = '".base64_encode($email)."'";
        $db->query($sql);
        exit();
    }
    if (!check_email($email)) {
        $echo = array(
            'code' => '400',
            'info'=>'email address is incorrect',
            'msg' => 'Email地址不正确',
        );
        echo json_encode($echo);
        $postTime=date("Y-m-d H:i:s",time());
        $sql="UPDATE tbl_apply SET status = '-1' , errorList = '".$echo['msg']."' , checkTime = '".$postTime."' WHERE email = '".base64_encode($email)."'";
        $db->query($sql);
        exit();
    }
    if(EmailBanned($email)){
        $echo = array(
            'code' => '400',
            'info'=>'email address is blocked',
            'msg' => 'Email地址被禁封',
        );
        echo json_encode($echo);
        $postTime=date("Y-m-d H:i:s",time());
        $sql="UPDATE tbl_apply SET status = '-1' , errorList = '".$echo['msg']."' , checkTime = '".$postTime."' WHERE email = '".base64_encode($email)."'";
        $db->query($sql);
        exit();
    }
    // check if email addy is already in use
    $a = (mysqli_fetch_row(sql_query("select count(*) from users where email='".$email."'"))) or die(mysqli_error());
    if ($a[0] != 0){
        $echo = array(
            'code' => '405',
            'info'=>'email address is exits',
            'msg' => 'Email地址已被注册',
        );
        echo json_encode($echo);
        $postTime=date("Y-m-d H:i:s",time());
        $sql="UPDATE tbl_apply SET status = '-1' , errorList = '".$echo['msg']."' , checkTime = '".$postTime."' WHERE email = '".base64_encode($email)."'";
        $db->query($sql);
        exit();
    }
    $b = (mysqli_fetch_row(sql_query("select count(*) from invites where invitee='".$email."'"))) or die(mysqli_error());
    if ($b[0] != 0){
        $echo = array(
            'code' => '405',
            'info'=>'invitation is sent',
            'msg' => '已向Email地址发送过邀请',
        );
        echo json_encode($echo);
        $postTime=date("Y-m-d H:i:s",time());
        $sql="UPDATE tbl_apply SET status = '-1' , errorList = '".$echo['msg']."' , checkTime = '".$postTime."' WHERE email = '".base64_encode($email)."'";
        $db->query($sql);
        exit();
    }

    $hash="";
    for ($i = 0; $i < 6; $i++)
    {
        $hash .= chr(mt_rand(65, 90));
    }
    $hash  = md5(mt_rand(1,10000).base64_encode($_SERVER['HTTP_USER_AGENT']).time().$hash);
    sql_query("INSERT INTO invites (inviter, invitee, hash, time_invited) VALUES ('0', '"
        .($email)."', '".($hash)."', '"
        . date("Y-m-d H:i:s") . "')");
    $name=base64_decode($name);
    $url=$GLOBALS["ptConfig"]["url"]."/signup.php?type=invite&invitenumber=$hash";
    $sendURL=$GLOBALS["config"]["url"]."/mail.php?key=JGYWOjoBIdsIU89HBkkJG";
    $msg="<p>尊敬的{$name}，您好</p>".
        "<p>您的申请我们已经收到并且通过了审核，欢迎您加入百川PT的大家庭！</p>".
        "<p>下面的链接是您的邀请链接，请尽快注册：</p>".
        "<a href=\"$url\"; target=\"_blank\">$url</a>".
        "<p style=\"font-weight:blod\">下面的内容请您认真阅读：</p>".
        "<blockquote style=\"border: 1px solid #999;padding: 1.25rem;border-left-width: .25rem; margin-left: 0px\">
            百川PT是一个拥有丰富资源的非开放社区,烦请在加入后确认邀请并<span style=\"color: red; font-weight: bold\">仔细阅读规则<span>。最后,确保维持一个良好的分享率，并且分享允许的资源。
        </blockquote>".
        "<p style=\"float:right\">百川PT管理组</p>";
    $post=array(
        'email'=>base64_encode($email),
        'subject'=>base64_encode('百川PT申请通过函'),
        'title'=>base64_encode('百川PT-申请通过及确认函'),
        'msg'=>base64_encode($msg),
    );
    $postTime=date("Y-m-d H:i:s",time());
    $sql="UPDATE tbl_apply SET status = '1' , checkTime = '".$postTime."' WHERE email = '".base64_encode($email)."'";
    $db->query($sql);
    $db->close();
    $echo = array(
        'code' => '200',
        'info'=>'success',
        'msg' => '成功',
    );
    echo json_encode($echo);
    request_post($sendURL,$post);//请求发送邮件
    exit();
}


function safe_email($email) {
    $email = str_replace("<","",$email);
    $email = str_replace(">","",$email);
    $email = str_replace("\'","",$email);
    $email = str_replace('\"',"",$email);
    $email = str_replace("\\\\","",$email);

    return $email;
}
function check_email ($email) {
    if(preg_match('/^[A-Za-z0-9][A-Za-z0-9_.+\-]*@[A-Za-z0-9][A-Za-z0-9_+\-]*(\.[A-Za-z0-9][A-Za-z0-9_+\-]*)+$/', $email))
        return true;
    else
        return false;
}

function EmailBanned($newEmail)
{
    $newEmail = trim(strtolower($newEmail));
    $sql = sql_query("SELECT * FROM bannedemails");
    $list = mysqli_fetch_array($sql);
    $addresses = explode(' ', preg_replace("/[[:space:]]+/", " ", trim($list[value])) );

    if(count($addresses) > 0)
    {
        foreach ( $addresses as $email )
        {
            $email = trim(strtolower(preg_replace('/\./', '\\.', $email)));
            if(strstr($email, "@"))
            {
                if(preg_match('/^@/', $email))
                {// Any user @host?
                    // Expand the match expression to catch hosts and
                    // sub-domains
                    $email = preg_replace('/^@/', '[@\\.]', $email);
                    if(preg_match("/".$email."$/", $newEmail))
                        return true;
                }
            }
            elseif(preg_match('/@$/', $email))
            {    // User at any host?
                if(preg_match("/^".$email."/", $newEmail))
                    return true;
            }
            else
            {                // User@host
                if(strtolower($email) == $newEmail)
                    return true;
            }
        }
    }

    return false;
}
function sql_query($sql){
    $ptConfig=$GLOBALS["ptConfig"];
    $db=new mysqli($ptConfig["SQL_URL"], $ptConfig["SQL_User"], $ptConfig["SQL_Password"], $ptConfig["SQL_Database"], $ptConfig["SQL_Port"]);
    if($db->connect_error){
        $echo = array(
            'code' => '500',
            'info'=>'cannot connect database | '.$db->connect_error,
            'msg' => '数据库错误',
        );
        echo json_encode($echo);
        exit();
    }
    $return=$db->query($sql);
    $db->close();
    return $return;
}
/**
 * 模拟post进行url请求
 * @param string $url
 * @param array $post_data
 * @return string
 */
function request_post($url = '', $post_data = array()) {
    $postUrl = $url;
    $curlPost = $post_data;
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);

    return $data;
}