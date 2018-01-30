<?php
/**
 * Created by PhpStorm.
 * User: huqi1
 * Date: 2018/1/30
 * Time: 22:07
 */

$sendURL="http://pttest.spcsky.com/invite/mail.php?key=JGYWOjoBIdsIU89HBkkJG";
echo $sendURL;
$email="spcreply@126.com";
$msg="<p>尊敬的{$name}，您好</p>".
    "<p>您的申请我们已经收到并且通过了审核，欢迎您加入百川PT的大家庭！</p>".
    "<p>下面的链接是您的邀请链接，请尽快注册：</p>".
    "<a herf=\"$url\">$url</a>".
    "<p style=\"font-weight:blod\">下面的内容请您认真阅读：</p>".
    "<blockquote style=\"border: 1px solid #999;padding: 1.25rem;border-left-width: .25rem;\">".
            "百川PT是一个拥有丰富资源的非开放社区,烦请在加入后仔细阅读规则并确认邀请。最后,确保维持一个良好的分享率，并且分享允许的资源。".
        "</blockquote>".
    "<p style=\"float:right\">百川PT管理组</p>";
$post=array(
    'email'=>base64_encode($email),
    'subject'=>base64_encode('百川PT申请通过函'),
    'title'=>base64_encode('百川PT-申请通过及确认函'),
    'msg'=>base64_encode($msg),
);
echo request_post($sendURL,$post);

function request_post($url = '', $param = array()) {

    $postUrl = $url;
    $curlPost = $param;
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
?>