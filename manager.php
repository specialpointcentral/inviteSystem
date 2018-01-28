<?php
/**
 * Created by PhpStorm.
 * User: huqi1
 * Date: 2018/1/28
 * Time: 0:34
 */
require_once ("config.php");
date_default_timezone_set("Asia/Shanghai");
//first identify the key
$secretKey="y4wZttOy7Sbyrunh";
$passkey=$_GET['passkey'];
$publicKey=$_GET['publickey'];
//echo md5($publicKey.$secretKey);
//if(md5($publicKey.$secretKey)!=$passkey){
//    $echo = array(
//        'code' => '403',
//        'info'=>'forbidden',
//        'msg' => '访问被拒绝',
//        'data' => 'NULL',
//    );
//    echo json_encode($echo);
//    exit();
//}

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
dashBoard($db);

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
 * $standby 未审核 0
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

    $sql="SELECT * FROM tbl_apply WHERE sex ='男'";
    $result=$db->query($sql);
    $boy=$result->num_rows;

    $sql="SELECT * FROM tbl_apply WHERE sex ='女'";
    $result=$db->query($sql);
    $girl=$result->num_rows;

    $array=array(
        'all'=>$pass+$error+$standby+$notPass,
        'pass'=>$pass,
        'error'=>$error,
        'standby'=>$standby,
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

function totalList(mysqli $db){
    /**
     *
     */
    $sql="SELECT * FROM tbl_apply";
    $result=$db->query($sql);
    while($row=$result->fetch_assoc()){
        $get=array(
            'name'=>base64_decode($row['name']),
            'type'=>(base64_decode($row['a'])==1)?'校内':'校外',
        );
    }
}

?>