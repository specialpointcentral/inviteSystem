<?php
/**
 * Created by PhpStorm.
 * User: huqi1
 * Date: 2018/1/24
 * Time: 11:06
 */

class receive
{
    private $flag,$name,$sex,$id,$work,$phone,$time,$disk,
    $school,$email,$favorite,$reason,$other,$ip;

    function __set($propName,$propValue){
        $this->$propName=base64_encode($propValue);
    }
    function __get($propName){
        return $this->$propName;
    }
    function __construct($flag,$name,$sex,$phone,$time,$disk,$email,$favorite,$reason,$other,$ip)
    {
        $array=array("flag","name","sex","phone","time","disk","email","favorite","reason","other","ip");
        foreach ($array as $vars)
            $this->$vars=base64_encode($$vars);
    }

}

?>