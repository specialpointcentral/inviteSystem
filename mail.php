<?php
/**
 * Created by PhpStorm.
 * User: huqi1
 * Date: 2018/1/26
 * Time: 0:12
 */
require_once ("Smtp.class.php");
require_once ("config.php");
date_default_timezone_set("Asia/Shanghai");

$key="JGYWOjoBIdsIU89HBkkJG";
if(base64_encode($_GET['key'])!=base64_encode($key)) echo "非法进入";
else{
    if(isset($_GET['flag'])&&$_GET['flag']==1){
        //提交发送邮件
        $db=new mysqli($config["SQL_URL"], $config["SQL_User"], $config["SQL_Password"], $config["SQL_Database"], $config["SQL_Port"]);
        if($db->connect_error){
            $echo = array(
                'code' => '500',
                'info'=>'cannot connect database'.$db->connect_error,
                'msg' => '数据库错误'
            );
            echo json_encode($echo);
            exit();
        }
        $sql="SELECT * FROM tbl_apply WHERE email = '".$_GET['email']."'";
        $result=$db->query($sql);
        if($result->num_rows>0){
            $row=$result->fetch_assoc();
            $msg="<p>您好，首先欢迎您申请我们的PT站账号</p>";
            $msg=$msg."<p>以下是您提交的部分信息：</p>";
            $msg=$msg."<ul><li>姓名：".base64_decode($row['name'])."</li><li>性别：".base64_decode($row['sex']).
                "</li><li>手机号码：".base64_decode(
                    ['phone'])."</li><li>Email：".base64_decode($row['email']).
                "</li><li>提交时间：".$row['postTime']."</li><li>提交IP：".base64_decode($row['ip'])."</li></ul>";
            $msg=$msg."<p>在这里我们要提前声明，这里是私有种子站，请您在得到邀请后查看具体规则。</p>";
            $msg=$msg."<p>管理员审核完成后将会通过邮件的方式告知审核结果。</p>";
            mailSend(base64_decode($row['email']),"自助申请结果确认",
                mailModule("百川PT-自助申请结果确认",$msg,
                    date("Y-m-d H:i",time()),"管理员","http://www.spcsky.com"));

        }else{
            $echo = array(
                'code' => '402',
                'info'=>'cannot find the email',
                'msg' => '数据库中找不到邮箱地址'
            );
            echo json_encode($echo);
            exit();
        }

    }else{
        //flag不存在，接受post
        if(!isset($_POST['email'])||!isset($_POST['subject'])||!isset($_POST['title'])||!isset($_POST['msg'])){
            $echo = array(
                'code' => '400',
                'info'=>'post information is incorrect',
                'msg' => '提交参数错误',
            );
            echo json_encode($echo);
            exit();
        }
        mailSend(base64_decode($_POST['email']),base64_decode($_POST['subject']),
            mailModule(base64_decode($_POST['title']),base64_decode($_POST['msg']),
                date("Y-m-d H:i",time()),"管理员","http://www.spcsky.com"));
    }

}


function mailPost($to,$subject,$message){
// 当发送 HTML 电子邮件时，请始终设置 content-type
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=utf8" . "\r\n";
    mail($to,$subject,$message,$headers);
}

function mailSend($to,$subject,$message){
    //******************** 配置信息 ********************************
    $smtpserver = "smtpdm.aliyun.com";//SMTP服务器
    $smtpserverport =25;//SMTP服务器端口
    $smtpusermail = "bcpt-tts@email.spcsky.com";//SMTP服务器的用户邮箱
    $smtpuser = "bcpt-tts@email.spcsky.com";//SMTP服务器的用户帐号，注：部分邮箱只需@前面的用户名
    $smtppass = "HU990114qi";//SMTP服务器的用户密码
    $mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
    //************************ 配置信息 ****************************
    $smtp = new Smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
    $smtp->debug = false;//是否显示发送的调试信息
    $state = $smtp->sendmail($to, $smtpusermail, $subject, $message, $mailtype);
    if($state="") return false;
    else return true;
}


/**
 * User: huqi1
 * Date: 2018/1/26
 * function:百川PT统一邮件格式
 * @param $head 邮件标题
 * @param $content 邮件正文 HTML格式
 * @param $time 发送时间
 * @param $connect 联系方式（显示）
 * @param $connectLink 联系链接
 * @return string 返回邮件HTML代码
 */
function mailModule($head,$content,$time,$connect,$connectLink){
    return <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:https="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{$head}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin: 0px; background-color: #F4F3F4; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#F4F3F4" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">
<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F4F3F4" >
    <tbody>
    <tr>
        <td style="padding: 15px;">
            <table width="550" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                <tbody>
                <tr>
                    <td align="left">
                        <div style="border: solid 1px #d9d9d9;">
                            <table id="header" style="line-height: 1.6; font-size: 12px; font-family: Helvetica, Arial, sans-serif; border: solid 1px #FFFFFF; color: #444;" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                <tbody>
                                <tr>
                                    <td style="color: #ffffff;" colspan="2" valign="bottom" height="30"></td>
                                </tr>
                                <tr>
                                    <td style="line-height: 32px; padding-left: 30px;" valign="baseline">
                                        <div style="font-size: 32px;color: #000">百川PT</div>
                                        <div style="font-size: 12px;color: #000">海纳百川，高速分享</div>
                                    </td>
                                    <td style="padding-right: 30px;" align="right" valign="baseline"><span style="font-size: 14px; color: #000;">{$head}</span></td>
                                </tr>
                                </tbody>
                            </table>
                            <table id="content" style="margin-top: 8px; margin-right: 30px; margin-left: 30px; color: #444; line-height: 1.6; font-size: 12px; font-family: Arial, sans-serif;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                <tbody>
                                <tr>
                                    <td style="border-top: solid 1px #d9d9d9;" colspan="2">
                                        <div style="padding: 15px 0;">{$content}</div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <table id="footer" style="line-height: 1.5; font-size: 12px; font-family: Arial, sans-serif; margin-right: 30px; margin-left: 30px;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                <tbody>
                                <tr style="font-size: 11px; color: #999999;">
                                    <td style="border-top: solid 1px #d9d9d9;" colspan="2">
                                        <div style="padding-top: 15px; padding-bottom: 1px;"><img class="" style="vertical-align: middle;" src="https://www.spcsky.com/wp-admin/images/date-button.gif" alt="日期" width="13" height="13" /> 邮件发送于 {$time}</div>
                                        <div><img style="vertical-align: middle;" src="https://www.spcsky.com/wp-admin/images/comment-grey-bubble.png" alt="联系人" width="12" height="12" /> 如果有任何问题，请联系 <a href="{$connectLink}">{$connect}</a></div>
                                    </td>
                                    <td style="border-top: solid 1px #d9d9d9;" colspan="2">
                                        <div style="padding-top: 15px; padding-bottom: 1px;" align="right">这是系统发出的邮件，请勿回复</div>
                                        <div align="right">&copy;百川PT-哈尔滨工业大学</div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </center></td>
    </tr>
    </tbody>
</table>
</body>
</html>
EOF;

}


?>