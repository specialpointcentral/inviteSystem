<?php
/**
 * Created by PhpStorm.
 * User: huqi1
 * Date: 2018/1/24
 * Time: 11:02
 */
error_reporting(E_ALL^E_NOTICE);

require_once ("receive.php");
require_once ("config.php");
date_default_timezone_set("Asia/Shanghai");
if(getDataIsRight()) {
    $ip=real_ip();
    $get = new receive($_POST["flag"], $_POST["name"], $_POST["sex"], $_POST["phone"],
        $_POST["time"], $_POST["disk"], $_POST["email"], "",
        $_POST["reason"], $_POST["other"], $ip);
    if(!empty($_POST["favorite"])){
        $favorite="";
        foreach ($_POST["favorite"] as $value) $favorite = $value."," .$favorite  ;
        $get->favorite = $favorite;
    }
    if ($_POST["flag"] == 1) {
        //inSchool
        $get->id = $_POST["id"];
        $get->school = $_POST["school"];

    } else {
        //outSchool
        $get->work = $_POST["work"];
        $get->school = $_POST["school"];
    }

    $db=new mysqli($config["SQL_URL"], $config["SQL_User"], $config["SQL_Password"], $config["SQL_Database"], $config["SQL_Port"]);
    if($db->connect_error){
        $echo = array(
            'code' => '500',
            'info'=>'cannot connect database | '.$db->connect_error,
            'msg' => '数据库错误'
        );
        echo json_encode($echo);
        exit();
    }

    $db->set_charset("utf8");
    $sql = "SELECT * FROM tbl_apply WHERE email = '".base64_encode($_POST['email'])."'";
    $result = $db->query($sql);
    if($result->num_rows > 0){
        //已经申请过了
        $echo = array(
            'code' => '401',
            'info'=>'email is existed',
            'msg' => '此邮箱已经申请过了'
        );
        echo json_encode($echo);
        exit();
    }else{
        $postTime=date("Y-m-d H:i:s",time());
        $sql="INSERT INTO tbl_apply (flag,name,sex,phone,time,
            disk,email,favorite,reason,other,school,ids,work,postTime,ip,status) VALUES
            ('".$get->flag."','".$get->name."','".$get->sex."','".$get->phone."','".$get->time."','"
            .$get->disk."','".$get->email."','".$get->favorite."','".$get->reason."','".$get->other."','"
            .$get->school."','".$get->id."','".$get->work."','".$postTime."','".$get->ip."',0)";

        $db->query($sql);

        $key="JGYWOjoBIdsIU89HBkkJG";
        //$out = "GET /invite/mail.php?key=".$key."&flag=1&email=".base64_encode($_POST['email']). " HTTP/1.1 \r\n";

        //send the mail
        $sendURL=dirname(curPageURL())."/mail.php";
        $sendURL.="?key=".$key."&flag=1&email=".base64_encode($_POST['email']);
        request_by_fsockopen($sendURL);
        //---------------
        }
    $db->close();

    $echo = array(
        'code' => '200',
        'info'=>'success',
        'msg' => '提交成功'
    );
    echo json_encode($echo);
    exit();
}

/**
 * using to identify get data is right
 * @return bool
 */
function getDataIsRight(){
    $set= isset($_POST["flag"])&&isset($_POST["name"])&&isset($_POST["sex"])&&isset($_POST["phone"])&&
    isset($_POST["time"])&&isset($_POST["disk"])&&isset($_POST["email"])&&
    isset($_POST["reason"])&&isset($_POST["other"]);
    $set=$set&&!empty($_POST["flag"])&&!empty($_POST["name"])&&!empty($_POST["sex"])&&!empty($_POST["phone"])&&
        !empty($_POST["time"])&&!empty($_POST["disk"])&&!empty($_POST["email"]);
    if(!$set) {
        $echo = array(
            'code' => '400',
            'info'=>'post information is not enough',
            'msg' => '参数不完整'
        );
        echo json_encode($echo);
        return false;
    }else if($_POST["flag"]==1){
        //inSchool
        if(isset($_POST["id"])&&isset($_POST["school"])==false){
            $echo = array(
                'code' => '400',
                'info'=>'post information is not enough',
                'msg' => '参数不完整'
            );
            echo json_encode($echo);
            return false;
        }

    }else{
        //outSchool
        if(isset($_POST["work"])==false){
            $echo = array(
                'code' => '400',
                'info'=>'post information is not enough',
                'msg' => '参数不完整'
            );
            echo json_encode($echo);
            return false;
        }
        if((($_POST["work"]!="学生")||isset($_POST["school"]))==false){
            $echo = array(
                'code' => '400',
                'info'=>'post information is not enough',
                'msg' => '参数不完整1'
            );
            echo json_encode($echo);
            return false;
        }
    }
    return true;
}

/**
 * 获得用户的真实IP地址
 * 来源：ecshop
 * $_SERVER和getenv的区别，getenv不支持IIS的isapi方式运行的php
 * @access  public
 * @return  string
 */
function real_ip() {
    static $realip = NULL;
    if ($realip !== NULL) {
        return $realip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr AS $ip) {
                $ip = trim($ip);

                if ($ip != 'unknown') {
                    $realip = $ip;

                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    // 使用正则验证IP地址的有效性，防止伪造IP地址进行SQL注入攻击
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    return $realip;
}


/**
 * 获取完整URL
 * @return string
 */
function curPageURL()
{
    $pageURL = 'http';

    if (isset($_SERVER["HTTPS"])&&$_SERVER["HTTPS"] == "on")
    {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    $this_page = $_SERVER["REQUEST_URI"];

    // 只取 ? 前面的内容
    if (strpos($this_page, "?") !== false)
    {
        $this_pages = explode("?", $this_page);
        $this_page = reset($this_pages);
    }
        $pageURL .= $_SERVER["HTTP_HOST"] . $this_page;
    return $pageURL;
}

/**
 * @param $url 地址
 * @param array $post_data post数据
 * @return bool
 */
function request_by_fsockopen($url,$post_data=array()){
    $url_array = parse_url($url);
    $hostname = $url_array['host'];
    $port = isset($url_array['port'])? $url_array['port'] : 80;
    $requestPath = $url_array['path'] ."?". $url_array['query'];
    $fp = fsockopen($hostname, $port, $errno, $errstr, 10);
    if (!$fp) {
        $echo = array(
            'code' => '501',
            'info'=>'email send error | '.$errstr."(".$errno.")",
            'msg' => '邮件发送失败'
        );
        echo json_encode($echo);
        exit();
    }
    $method = "GET";
    if(!empty($post_data)){
        $method = "POST";
    }
    $header = "$method $requestPath HTTP/1.1\r\n";
    $header.="Host: $hostname\r\n";
    if(!empty($post_data)){
        $_post = strval(NULL);
        foreach($post_data as $k => $v){
            $_post[]= $k."=".urlencode($v);//必须做url转码以防模拟post提交的数据中有&符而导致post参数键值对紊乱
        }
        $_post = implode('&', $_post);
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";//POST数据
        $header .= "Content-Length: ". strlen($_post) ."\r\n";//POST数据的长度
        $header.="Connection: Close\r\n\r\n";//长连接关闭
        $header .= $_post; //传递POST数据
    }else{
        $header.="Connection: Close\r\n\r\n";//长连接关闭
    }
    fwrite($fp, $header);
    //-----------------调试代码区间-----------------
    /*$html = '';
    while (!feof($fp)) {
        $html.=fgets($fp);
    }
    echo $html;*/
    //-----------------调试代码区间-----------------
    fclose($fp);
    return true;
}
?>