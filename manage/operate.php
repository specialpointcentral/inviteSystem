<?php
/**
 * Created by PhpStorm.
 * User: huqi1
 * Date: 2018/1/28
 * Time: 0:34
 */
require_once("../config.php");
require_once("checkOperate.php");
header('Access-Control-Allow-Origin:*');
date_default_timezone_set("Asia/Shanghai");

if(!isset($_GET['require'])&&!isset($_GET['passkey'])&&!isset($_GET['timeStamp'])&&!isset($_GET['publickey'])){
    $echo = array(
        'code' => '400',
        'info'=>'post information is incorrect',
        'msg' => '参数不完整',
        'data' => 'NULL',
    );
    echo json_encode($echo);
    exit();
}
//first identify the key
if($_GET['require']!='getData' && $_GET['require']!='submit'){
    $secretKey="y4wZttOy7Sbyrunh";
    $passkey=$_GET['passkey'];
    $publicKey=sha1($_GET['publickey']);
    $timeStamp=$_GET['timeStamp'];
    if(abs($timeStamp-time())>30){
        $echo = array(
            'code' => '403',
            'info'=>'timeStamp is wrong',
            'msg' => '时间戳过长，访问被拒绝',
            'data' => 'NULL',
        );
        echo json_encode($echo);
        exit();
    }else if(md5($publicKey.$timeStamp.$secretKey)!=$passkey){
        $echo = array(
            'code' => '403',
            'info'=>'passkey is not correct',
            'msg' => '密钥不正确，访问被拒绝',
            'data' => 'NULL',
        );
        echo json_encode($echo);
        exit();
    }
}else{
    //require is 'getData',we will get passkey from database
    $publicKey=base64_encode($_GET['publickey']);
    $timeStamp=(int)$_GET['timeStamp'];

    $db=new mysqli($config["SQL_URL"], $config["SQL_User"], $config["SQL_Password"], $config["SQL_Database"], $config["SQL_Port"]);
    if($db->connect_error){
        $echo = array(
            'code' => '500',
            'info'=>'cannot connect database | '.$db->connect_error,
            'msg' => '数据库错误',
            'data' => 'NULL',
        );
        echo json_encode($echo);
        exit();
    }
    $sql="SELECT privateKey FROM tbl_identify WHERE timeStamp = '".$timeStamp."' AND publicKey = '".$publicKey."'";
    $result=$db->query($sql);
    if($result->num_rows!=1){
        $echo = array(
            'code' => '403',
            'info'=>'for security,system is forbidden this session',
            'msg' => '有伪造风险，系统已禁止此次访问',
            'data' => 'NULL',
        );
        echo json_encode($echo);
        $db->close();
        exit();
    }
    $row=$result->fetch_assoc();
    $db->close();

    if(abs($timeStamp-time())>300){
        $echo = array(
            'code' => '403',
            'info'=>'timeStamp is wrong',
            'msg' => '时间戳过长，访问被拒绝',
            'data' => 'NULL',
        );
        echo json_encode($echo);
        exit();
    }else if(md5($_GET['passkey'])!=$row['privateKey']){
        $echo = array(
            'code' => '403',
            'info'=>'passkey is not correct',
            'msg' => '密钥不正确，访问被拒绝',
            'data' => 'NULL',
            'test' => md5($_GET['passkey']).' '.$row['privateKey'],
        );
        echo json_encode($echo);
        exit();
    }
}

$db=new mysqli($config["SQL_URL"], $config["SQL_User"], $config["SQL_Password"], $config["SQL_Database"], $config["SQL_Port"]);
if($db->connect_error){
    $echo = array(
        'code' => '500',
        'info'=>'cannot connect database | '.$db->connect_error,
        'msg' => '数据库错误',
        'data' => 'NULL',
    );
    echo json_encode($echo);
    exit();
}

switch ($_GET['require']){
    case 'dashBoard'://dashBoard
        dashBoard($db);
        break;
    case 'totalList'://totalList
        totalList($db);
        break;
    case 'unCheckList'://unCheckList
        unCheckList($db);
        break;
    case 'problemList'://problemList
        problemList($db);
        break;
    case 'getData':
        if(!isset($_GET['id'])){
            $echo = array(
                'code' => '400',
                'info'=>'post information is incorrect(id)',
                'msg' => '提交参数id错误',
                'data' => 'NULL',
            );
            echo json_encode($echo);
            $db->close();
            exit();
        }else

            getDetail($db,(int)$_GET['id']);
        break;
    case 'submit':
        if(!isset($_GET['id'])){
            $echo = array(
                'code' => '400',
                'info'=>'post information is incorrect(id)',
                'msg' => '提交参数id错误',
                'data' => 'NULL',
            );
            echo json_encode($echo);
            $db->close();
            exit();
        }else
            if($_GET['type']=="pass")
                passTheCheck($db,(int)$_GET['id']);
            else if($_GET['type']=="unpass")
                unPassTheCheck($db,(int)$_GET['id']);
        break;
    default:
        $echo = array(
            'code' => '400',
            'info'=>'post information is incorrect(require)',
            'msg' => '提交参数require错误',
            'data' => 'NULL',
        );
        echo json_encode($echo);
        $db->close();
        exit();
}

$db->close();

/**
 * using to return the dashboard info
 * @param mysqli $db
 *
 */
function dashBoard(mysqli $db){
    /**
     * $all 总人数
     * $pass 通过 1
     * $error 异常 -1
     * $unCheck 未审核 0
     * $notPass 未通过 2
     *
     * $boy 男生
     * $girl 女生
     *
     * $array return to
     */
    $sql="SELECT * FROM tbl_apply WHERE status ='1'";
    $result=$db->query($sql);
    $pass=$result->num_rows;

    $sql="SELECT * FROM tbl_apply WHERE status ='-1'";
    $result=$db->query($sql);
    $error=$result->num_rows;

    $sql="SELECT * FROM tbl_apply WHERE status ='0'";
    $result=$db->query($sql);
    $standby=$result->num_rows;

    $sql="SELECT * FROM tbl_apply WHERE status ='2'";
    $result=$db->query($sql);
    $notPass=$result->num_rows;

    $sql="SELECT * FROM tbl_apply WHERE sex ='".base64_encode('男')."'";
    $result=$db->query($sql);
    $boy=$result->num_rows;

    $sql="SELECT * FROM tbl_apply WHERE sex ='".base64_encode('女')."'";
    $result=$db->query($sql);
    $girl=$result->num_rows;

    $array=array(
        'all'=>$pass+$error+$standby+$notPass,
        'pass'=>$pass,
        'error'=>$error,
        'unCheck'=>$standby,
        'notPass'=>$notPass,
        'boy'=>$boy,
        'girl'=>$girl,
    );

    /**
     * $day[8] 8 day data
     */
    $time=time();
    for($i=0;$i<8;$i++){
        $timeBefore=strtotime(($i-7).' days');
        $timeAfter=strtotime(($i-8).' days');
        $sql="SELECT * FROM tbl_apply WHERE unix_timestamp(postTime) BETWEEN ".$timeAfter." AND ".$timeBefore;
        $result=$db->query($sql);
        $num=$result->num_rows;

        $sql="SELECT * FROM tbl_apply WHERE unix_timestamp(checkTime) BETWEEN ".$timeAfter." AND ".$timeBefore;
        $result=$db->query($sql);
        $haveSee=$result->num_rows;

        $day[$i]=array(
            'all'=>$num,
            'haveSee'=>$haveSee,
            'time'=>date("Y-m-d",$timeBefore),
        );
    }
    $array['day']=$day;
    $return=array(
        'code' => '200',
        'info'=>'Get the data',
        'msg' => '获取到数据',
    );
    $return['data']=$array;
    echo json_encode($return);
}

/**
 * using to return the totalList info
 * @param mysqli $db
 */
function totalList(mysqli $db){
    /**
     * name
     * type
     * time
     * disk
     * id Use identify the submit
     */
    $sql="SELECT * FROM tbl_apply";
    $result=$db->query($sql);
    $list=array();
    while($row=$result->fetch_assoc()){
        $get=array(
            'name'=>base64_decode($row['name']),
            'type'=>(base64_decode($row['flag'])==1)?'校内':'校外',
            'time'=>base64_decode($row['time']),
            'disk'=>base64_decode($row['disk']),
            'status'=>$row['status'],
            'id'=>$row['id'],
        );
        array_push($list,$get);
    }
    $totle=count($list);
    $keyArray=securityCheck($db);
    $return=array(
        'code' => '200',
        'info'=>'Get the data',
        'msg' => '获取到数据',
    );
    $return['data']=array(
        'num'=>$totle,
        'dataList'=>$list,
        'passkey'=>$keyArray,
    );
    echo json_encode($return);
}

/**
 * using to return the totalList info
 * @param mysqli $db
 */
function unCheckList(mysqli $db){
    /**
     * name
     * type
     * time
     * disk
     * id Use identify the submit
     */
    $sql="SELECT * FROM tbl_apply WHERE status = 0";
    $result=$db->query($sql);
    $list=array();
    while($row=$result->fetch_assoc()){
        $get=array(
            'name'=>base64_decode($row['name']),
            'type'=>(base64_decode($row['flag'])==1)?'校内':'校外',
            'sex'=>base64_decode($row['sex']),
            'time'=>base64_decode($row['time']),
            'disk'=>base64_decode($row['disk']),
            'id'=>$row['id'],
        );
        array_push($list,$get);
    }
    $totle=count($list);
    $keyArray=securityCheck($db);
    $return=array(
        'code' => '200',
        'info'=>'Get the data',
        'msg' => '获取到数据',
    );
    $return['data']=array(
        'num'=>$totle,
        'dataList'=>$list,
        'passkey'=>$keyArray,
    );
    echo json_encode($return);
}

/**
 * using to return the totalList info
 * @param mysqli $db
 */
function problemList(mysqli $db){
    /**
     * name
     * type
     * time
     * disk
     * id Use identify the submit
     */
    $sql="SELECT * FROM tbl_apply WHERE status = -1";
    $result=$db->query($sql);
    $list=array();
    while($row=$result->fetch_assoc()){
        $get=array(
            'name'=>base64_decode($row['name']),
            'type'=>(base64_decode($row['flag'])==1)?'校内':'校外',
            'sex'=>base64_decode($row['sex']),
            'time'=>base64_decode($row['time']),
            'disk'=>base64_decode($row['disk']),
            'id'=>$row['id'],
            'errorList'=>$row['errorList'],
        );
        array_push($list,$get);
    }
    $totle=count($list);
    $keyArray=securityCheck($db);
    $return=array(
        'code' => '200',
        'info'=>'Get the data',
        'msg' => '获取到数据',
    );
    $return['data']=array(
        'num'=>$totle,
        'dataList'=>$list,
        'passkey'=>$keyArray,
    );
    echo json_encode($return);
}

/**
 * using to return the totalList info
 * @param mysqli $db
 */
function getDetail(mysqli $db,$id){
    /**
     * name
     * type
     * time
     * disk
     * id Use identify the submit
     */
    $sql="SELECT * FROM tbl_apply WHERE id = $id";
    $result=$db->query($sql);
    $row=$result->fetch_assoc();

    $get=array(
        'id'=>$row['id'],
        'name'=>base64_decode($row['name']),
        'type'=>(base64_decode($row['flag'])==1)?'校内':'校外',
        'sex'=>base64_decode($row['sex']),
        'phone'=>base64_decode($row['phone']),
        'time'=>base64_decode($row['time']),
        'disk'=>base64_decode($row['disk']),
        'favorite'=>base64_decode($row['favorite']),
        'reason'=>base64_decode($row['reason']),
        'other'=>base64_decode($row['other']),
        'school'=>base64_decode($row['school']),
        'ids'=>base64_decode($row['ids']),
        'work'=>base64_decode($row['work']),
        'postTime'=>$row['postTime'],
        'ip'=>base64_decode($row['ip']),
        'checkTime'=>$row['checkTime'],
        'email'=>base64_decode($row['email']),
    );
    $return=array(
        'code' => '200',
        'info'=>'Get the data',
        'msg' => '获取到数据',
    );

    $return['data']=array(
        'num'=>count($get),
        'dataList'=>$get,
    );

    echo json_encode($return);
}

function passTheCheck(mysqli $db,$id){
    $sql="SELECT email FROM tbl_apply WHERE id = $id";
    $result=$db->query($sql);
    if($result->num_rows==0){
        $return=array(
            'code' => '404',
            'info'=>'cannot find the information',
            'msg' => '找不到指定的信息',
        );
        echo json_encode($result);
        exit();
    }else{
        $row=$result->fetch_assoc();
        invite(base64_decode($row['email']));
    }
}

function unPassTheCheck(mysqli $db,$id){
    $sql="SELECT email FROM tbl_apply WHERE id = $id";
    $result=$db->query($sql);
    if($result->num_rows==0){
        $return=array(
            'code' => '404',
            'info'=>'cannot find the information',
            'msg' => '找不到指定的信息',
        );
        echo json_encode($result);
        exit();
    }else{
        $row=$result->fetch_assoc();
        invite(base64_decode($row['email']));
    }
}


/**
 * insert key to database
 * @param mysqli $db
 * @return array
 */
function securityCheck(mysqli $db){
    /**
     * timeStamp
     * publicKey base64
     * privateKey md5
     */
    $time=time();
    $publicKey="";
    for ($i = 0; $i < 6; $i++)
    {
        $publicKey .= chr(mt_rand(65, 90));
    }
    $encodePublicKey=base64_encode($publicKey);
    $passKey="";
    for ($i = 0; $i < 6; $i++)
    {
        $passKey .= chr(mt_rand(65, 90));
    }
    $encodePrivateKey=md5($passKey);
    $sql="INSERT INTO tbl_identify (timeStamp,publicKey,privateKey) VALUES ('".$time."','".$encodePublicKey."','".$encodePrivateKey."')";
    $db->query($sql);
    return array(
        'timeStamp'=>$time,
        'passKey'=>$passKey,
        'publicKey'=>$publicKey,
    );
}

?>