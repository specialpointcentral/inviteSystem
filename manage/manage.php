<?php
/**
 * Created by PhpStorm.
 * User: huqi1
 * Date: 2018/1/29
 * Time: 21:20
 */
$page=isset($_GET['page'])?$_GET['page']:"dashBoard";
switch ($page){
    case 'dashBoard':
        creatHTML(dashBoard("manage.php"),"manage.php",1);
        break;
    case 'totalList':
        creatHTML(totalList("manage.php"),"manage.php",2);
        break;
    case 'unCheckList':
        creatHTML(unCheckList("manage.php"),"manage.php",3);
        break;
    case 'problemList':
        creatHTML(problemList("manage.php"),"manage.php",4);
        break;
    case 'logOut':
        logOut();
        break;
    default:
        creatHTML(dashBoard("manage.php"),"manage.php",1);
}
/**
 * 数据格式：GET
 * 数据验证部分：
 * $secretKey,$publicKey,$timeStamp,$passkey
 * $passkey=md5(sha1($publicKey).$timeStamp.$secretKey)
 * 数据获取部分：
 * require：dashBoard totalList unCheckList problemList getData
 * 其中getData 需要参数id
 *
 */
/**
 * dashboard HTML
 * @param $page
 * @return array
 */
function dashBoard($page){
    $secretKey="y4wZttOy7Sbyrunh";
    $timeStamp=time();
    $publicKey="";
    for ($i = 0; $i < 6; $i++)
    {
        $publicKey .= chr(mt_rand(101, 132));
    }
    $publicKey=base64_encode($publicKey);
    $passkey=md5(sha1($publicKey).$timeStamp.$secretKey);
    $url = "http://pttest.spcsky.com/invite/manage/operate.php?timeStamp=$timeStamp&publickey=$publicKey&passkey=$passkey&require=dashBoard";
    $file_contents = file_get_contents($url);
    $result=json_decode($file_contents,true);
    if($result['code']!=200)
        return array(
            'html'=>'服务器发生错误，请重试！'.$file_contents,
            'script'=>'',
        );

    $return=array(
        'html'=><<<EOF
<div class="card-group p-2">

        <div class="card">
            <div class="card-header">
                当前数据
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <tbody>
                    <tr class="table-primary">
                        <th>总申请人数</th>
                        <td>{$result['data']['all']}</td>
                    </tr>
                    <tr class="table-success">
                        <th>已通过人数</th>
                        <td>{$result['data']['pass']}</td>
                    </tr>
                    <tr class="table-warning">
                        <th>未通过人数</th>
                        <td>{$result['data']['notPass']}</td>
                    </tr>
                    <tr class="table-danger">
                        <th>有异常人数</th>
                        <td>{$result['data']['error']}</td>
                    </tr>
                    <tr class="table-info">
                        <th>未审核人数</th>
                        <td>{$result['data']['unCheck']}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                数据监控-男女比例
            </div>
            <div class="card-body">
                <canvas id="sex"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                数据监控-申请通过比例
            </div>
            <div class="card-body">
                <canvas id="pass"></canvas>
            </div>
        </div>

    </div>
    <div class="card-group p-2">

        <div class="card">
            <div class="card-header">
                数据监控-申请态势
            </div>
            <div class="card-body">
                <canvas id="post"></canvas>
            </div>
        </div>

        <div class="card col-sm-4" style="padding: 0;">
            <div class="card-header">
                操作选择
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item"><a href="{$page}?page=totalList"><i class="fa fa-list-ol feather" aria-hidden="true"></i>查看总申请列表</a></li>
                    <li class="list-group-item"><a href="{$page}?page=unCheckList"><i class="fa fa-check-square-o feather" aria-hidden="true"></i>查看待处理列表</a></li>
                    <li class="list-group-item"><a href="{$page}?page=problemList"><i class="fa fa-exclamation-triangle feather" aria-hidden="true"></i>查看有问题列表</a></li>
                </ul>

            </div>
        </div>

    </div>
</div>
EOF
        ,
        'script'=><<<EOF
<script>
var datasex = {
    datasets: [{
        data: [{$result['data']['girl']}, {$result['data']['boy']}],
        backgroundColor: [
            window.chartColors.red,
            window.chartColors.green,
        ],
    }],

    // These labels appear in the legend and in the tooltips when hovering different arcs
    labels: [
        '女生',
        '男生',
    ],
};
var dataPass = {
    datasets: [{
        data: [{$result['data']['pass']},{$result['data']['notPass']},{$result['data']['error']},{$result['data']['unCheck']}],
        backgroundColor: [
            window.chartColors.red,
            window.chartColors.green,
            window.chartColors.purple,
            window.chartColors.blue,
        ],
    }],
    labels: [
        '通过',
        '未通过',
        '异常',
        '待审核',
    ],
};

var post = {
    type: 'line',
    data: {
        labels: ["{$result['data']['day'][0]['time']}", "{$result['data']['day'][1]['time']}", 
        "{$result['data']['day'][2]['time']}", "{$result['data']['day'][3]['time']}", 
        "{$result['data']['day'][4]['time']}", "{$result['data']['day'][5]['time']}", 
        "{$result['data']['day'][6]['time']}", "{$result['data']['day'][7]['time']}"],
        datasets: [{
            label: "申请量",
            backgroundColor: window.chartColors.red,
            borderColor: window.chartColors.red,
            data: [
                {$result['data']['day'][0]['all']},{$result['data']['day'][1]['all']},
                {$result['data']['day'][2]['all']},{$result['data']['day'][3]['all']},
                {$result['data']['day'][4]['all']},{$result['data']['day'][5]['all']},
                {$result['data']['day'][6]['all']},{$result['data']['day'][7]['all']}
            ],
            fill: false,
        }, {
            label: "审核量",
            fill: false,
            backgroundColor: window.chartColors.blue,
            borderColor: window.chartColors.blue,
            data: [
                {$result['data']['day'][0]['haveSee']},{$result['data']['day'][1]['haveSee']},
                {$result['data']['day'][2]['haveSee']},{$result['data']['day'][3]['haveSee']},
                {$result['data']['day'][4]['haveSee']},{$result['data']['day'][5]['haveSee']},
                {$result['data']['day'][6]['haveSee']},{$result['data']['day'][7]['haveSee']}
            ],
        }]
    },
    options: {
        responsive: true,
        tooltips: {
            mode: 'index',
            intersect: false,
        },
        hover: {
            mode: 'nearest',
            intersect: true
        },
        scales: {
            xAxes: [{
                display: true,
            }],
            yAxes: [{
                display: true,
            }]
        }
    }
};

window.onload = function(){
    var ctxSex = document.getElementById("sex").getContext("2d");
    var chartSex = new Chart(ctxSex,{
        type:'doughnut',
        data:datasex,
    });
    var ctxPass = document.getElementById("pass").getContext("2d");
    var chartPass = new Chart(ctxPass,{
        type:'doughnut',
        data:dataPass,
    });
    var ctxPost = document.getElementById("post").getContext("2d");
    var chartPost = new Chart(ctxPost,post);
}
</script>
EOF
        ,
);
    return $return;

}

/**
 * @param $page
 * @return array
 */
function totalList($page){
    $secretKey="y4wZttOy7Sbyrunh";
    $timeStamp=time();
    $publicKey="";
    for ($i = 0; $i < 6; $i++)
    {
        $publicKey .= chr(mt_rand(101, 132));
    }
    $publicKey=base64_encode($publicKey);
    $passkey=md5(sha1($publicKey).$timeStamp.$secretKey);
    $url = "http://pttest.spcsky.com/invite/manage/operate.php?timeStamp=$timeStamp&publickey=$publicKey&passkey=$passkey&require=totalList";
    $file_contents = file_get_contents($url);
    $result=json_decode($file_contents,true);
    if($result['code']!=200)
        return array(
            'html'=>'服务器发生错误，请重试！'.$file_contents,
            'script'=>'',
        );
    $html="";
for($i=0;$i<$result['data']['num'];) {
    $i++;
    switch ($result['data']['dataList'][$i - 1]['status']){
        case 0://未审核
            $class='';
            break;
        case 1://通过
            $class='class="table-success"';
            break;
        case 2://未通过
            $class='class="table-warning"';
            break;
        case -1://异常
            $class='class="table-danger"';
            break;
    }

    $htmls = <<<EOF
<tr {$class}>
    <th scope="row">{$i}</th>
    <td>{$result['data']['dataList'][$i - 1]['name']}</td>
    <td>{$result['data']['dataList'][$i - 1]['type']}</td>
    <td>{$result['data']['dataList'][$i - 1]['time']}</td>
    <td>{$result['data']['dataList'][$i - 1]['disk']}</td>
    <td><a href="javascript:void(0)" onclick="showDetail({$result['data']['dataList'][$i - 1]['id']})">详细信息</a></td>
</tr>
EOF;
    $html.=$htmls;
}
$model=<<<EOF
<div class="modal fade" id="infoDetail" tabindex="-1" role="dialog" aria-labelledby="infoDetailTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoDetailTitle">详细信息</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="infoDetailName">姓名：</p>
                <p id="infoDetailSex">性别：</p>
                <p id="infoDetailPhone">手机号码：</p>
                <p id="infoDetailTime">做种时间：</p>
                <p id="infoDetailDisk">硬盘大小：</p>
                <p id="infoDetailEmail">邮箱：</p>
                <p id="infoDetailId">学号：</p>
                <p id="infoDetailShcool">学校/学院：</p>
                <p id="infoDetailWork">工作：</p>
                <p id="infoDetailPostTime">提交时间：</p>
                <p id="infoDetailIp">提交IP：</p>
                <p id="infoDetailFavorite">喜好：</p>
                <p id="infoDetailReason">加入理由：</p>
                <p id="infoDetailOther">备注：</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
EOF;

    $html=<<<EOF
<table class="table table-hover table-striped table-bordered">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">姓名</th>
        <th scope="col">类型</th>
        <th scope="col">做种时间</th>
        <th scope="col">硬盘大小</th>
        <th scope="col">详细信息</th>
    </tr>
    </thead>
    <tbody>
    {$html}
    </tbody>
</table>
{$model}
EOF;

$script=<<<EOF
<script>
    function showDetail(id){
        $.ajax({
            async: true,   //是否为异步请求
            cache: false,  //是否缓存结果
            type: "GET", //请求方式
            dataType: "json",   //服务器返回的数据是什么类型
            url: "http://pttest.spcsky.com/invite/manage/operate.php" ,//url
            data: {
                publickey:"{$result['data']['passkey']['publicKey']}",
                passkey:"{$result['data']['passkey']['passKey']}",
                timeStamp:"{$result['data']['passkey']['timeStamp']}",
                require:"getData",
                id:id,
            },
            success: function (result) {
                if(result.code==200) {
                    //成功
                    $('#infoDetailName').text("姓名："+result.data.dataList.name);
                    $('#infoDetailSex').text("性别："+result.data.dataList.sex);
                    $('#infoDetailPhone').text("手机号码："+result.data.dataList.phone);
                    $('#infoDetailTime').text("做种时间："+result.data.dataList.time);
                    $('#infoDetailDisk').text("硬盘大小："+result.data.dataList.disk);
                    $('#infoDetailEmail').text("Email："+result.data.dataList.email);
                    $('#infoDetailId').text("学号："+result.data.dataList.id);
                    $('#infoDetailShcool').text("学校/学院："+result.data.dataList.school);
                    $('#infoDetailWork').text("工作："+result.data.dataList.work);
                    $('#infoDetailPostTime').text("提交时间："+result.data.dataList.postTime);
                    $('#infoDetailIp').text("IP："+result.data.dataList.ip);
                    $('#infoDetailFavorite').text("喜好："+result.data.dataList.favorite);
                    $('#infoDetailReason').text("加入原因："+result.data.dataList.reason);
                    $('#infoDetailOther').text("备注："+result.data.dataList.other);
                    
                    $('#infoDetail').modal('show');
                }else{
                    //alert(result.code+":"+result.msg);
                    swal(result.msg,"("+result.code+") "+result.info,"warning");
                }
            },
            error : function() {
                //alert("异常！");
                swal("发生异常","Ajax服务异常，请联系管理员","error");
            }
        });

    }
</script>
EOF;

    $return=array(
        'html'=>$html,
        'script'=>$script,
    );
    return $return;

}
function unCheckList($page){
    $secretKey="y4wZttOy7Sbyrunh";
    $timeStamp=time();
    $publicKey="";
    for ($i = 0; $i < 6; $i++)
    {
        $publicKey .= chr(mt_rand(101, 132));
    }
    $publicKey=base64_encode($publicKey);
    $passkey=md5(sha1($publicKey).$timeStamp.$secretKey);
    $url = "http://pttest.spcsky.com/invite/manage/operate.php?timeStamp=$timeStamp&publickey=$publicKey&passkey=$passkey&require=unCheckList";
    $file_contents = file_get_contents($url);
    $result=json_decode($file_contents,true);
    if($result['code']!=200)
        return array(
            'html'=>'服务器发生错误，请重试！'.$file_contents,
            'script'=>'',
        );
    $html="";
    for($i=0;$i<$result['data']['num'];) {
        $i++;
        $htmls = <<<EOF
<tr>
    <th scope="row">{$i}</th>
    <td>{$result['data']['dataList'][$i - 1]['name']}</td>
    <td>{$result['data']['dataList'][$i - 1]['type']}</td>
    <td>{$result['data']['dataList'][$i - 1]['time']}</td>
    <td>{$result['data']['dataList'][$i - 1]['disk']}</td>
    <td><a href="javascript:void(0)" onclick="showDetail({$result['data']['dataList'][$i - 1]['id']})">详细信息</a></td>
    <td><a href="javascript:void(0)" onclick="passCheck({$result['data']['dataList'][$i - 1]['id']})">通过审核</a></td>
</tr>
EOF;
        $html.=$htmls;
    }
    $model=<<<EOF
<div class="modal fade" id="infoDetail" tabindex="-1" role="dialog" aria-labelledby="infoDetailTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoDetailTitle">详细信息</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="infoDetailName">姓名：</p>
                <p id="infoDetailSex">性别：</p>
                <p id="infoDetailPhone">手机号码：</p>
                <p id="infoDetailTime">做种时间：</p>
                <p id="infoDetailDisk">硬盘大小：</p>
                <p id="infoDetailEmail">邮箱：</p>
                <p id="infoDetailId">学号：</p>
                <p id="infoDetailShcool">学校/学院：</p>
                <p id="infoDetailWork">工作：</p>
                <p id="infoDetailPostTime">提交时间：</p>
                <p id="infoDetailIp">提交IP：</p>
                <p id="infoDetailFavorite">喜好：</p>
                <p id="infoDetailReason">加入理由：</p>
                <p id="infoDetailOther">备注：</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
EOF;
    $html=<<<EOF
<table class="table table-hover table-striped table-bordered">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">姓名</th>
        <th scope="col">类型</th>
        <th scope="col">做种时间</th>
        <th scope="col">硬盘大小</th>
        <th scope="col">详细信息</th>
        <th scope="col">操作</th>
    </tr>
    </thead>
    <tbody>
    {$html}
    </tbody>
</table>
{$model}
EOF;
    $script=<<<EOF
<script>
    function showDetail(id){
        $.ajax({
            async: true,   //是否为异步请求
            cache: false,  //是否缓存结果
            type: "GET", //请求方式
            dataType: "json",   //服务器返回的数据是什么类型
            url: "http://pttest.spcsky.com/invite/manage/operate.php" ,//url
            data: {
                publickey:"{$result['data']['passkey']['publicKey']}",
                passkey:"{$result['data']['passkey']['passKey']}",
                timeStamp:"{$result['data']['passkey']['timeStamp']}",
                require:"getData",
                id:id,
            },
            success: function (result) {
                if(result.code==200) {
                    //成功                   
                    $('#infoDetailName').text("姓名："+result.data.dataList.name);
                    $('#infoDetailSex').text("性别："+result.data.dataList.sex);
                    $('#infoDetailPhone').text("手机号码："+result.data.dataList.phone);
                    $('#infoDetailTime').text("做种时间："+result.data.dataList.time);
                    $('#infoDetailDisk').text("硬盘大小："+result.data.dataList.disk);
                    $('#infoDetailEmail').text("Email："+result.data.dataList.email);
                    $('#infoDetailId').text("学号："+result.data.dataList.id);
                    $('#infoDetailShcool').text("学校/学院："+result.data.dataList.school);
                    $('#infoDetailWork').text("工作："+result.data.dataList.work);
                    $('#infoDetailPostTime').text("提交时间："+result.data.dataList.postTime);
                    $('#infoDetailIp').text("IP："+result.data.dataList.ip);
                    $('#infoDetailFavorite').text("喜好："+result.data.dataList.favorite);
                    $('#infoDetailReason').text("加入原因："+result.data.dataList.reason);
                    $('#infoDetailOther').text("备注："+result.data.dataList.other);
                    
                    $('#infoDetail').modal('show');
                }else{
                    //alert(result.code+":"+result.msg);
                    swal(result.msg,"("+result.code+") "+result.info,"warning");
                }
            },
            error : function() {
                //alert("异常！");
                swal("发生异常","Ajax服务异常，请联系管理员","error");
            }
        });

    }
    function passCheck(id){
        $.ajax({
            async: true,   //是否为异步请求
            cache: false,  //是否缓存结果
            type: "GET", //请求方式
            dataType: "json",   //服务器返回的数据是什么类型
            url: "http://pttest.spcsky.com/invite/manage/operate.php" ,//url
            data: {
                publickey:"{$result['data']['passkey']['publicKey']}",
                passkey:"{$result['data']['passkey']['passKey']}",
                timeStamp:"{$result['data']['passkey']['timeStamp']}",
                require:"submit",
                id:id,
            },
            success: function (result) {
                if(result.code==200) {
                    //成功
                    swal("成功啦~","","success");
                }else{
                    //alert(result.code+":"+result.msg);
                    swal(result.msg,"("+result.code+") "+result.info,"warning");
                }
            },
            error : function() {
                //alert("异常！");
                swal("发生异常","Ajax服务异常，请联系管理员","error");
            }
        });

    }
</script>
EOF;
    $return=array(
        'html'=>$html,
        'script'=>$script,
    );
    return $return;
}
function problemList($page){
    $secretKey="y4wZttOy7Sbyrunh";
    $timeStamp=time();
    $publicKey="";
    for ($i = 0; $i < 6; $i++)
    {
        $publicKey .= chr(mt_rand(101, 132));
    }
    $publicKey=base64_encode($publicKey);
    $passkey=md5(sha1($publicKey).$timeStamp.$secretKey);
    $url = "http://pttest.spcsky.com/invite/manage/operate.php?timeStamp=$timeStamp&publickey=$publicKey&passkey=$passkey&require=problemList";
    $file_contents = file_get_contents($url);
    $result=json_decode($file_contents,true);
    if($result['code']!=200)
        return array(
            'html'=>'服务器发生错误，请重试！'.$file_contents,
            'script'=>'',
        );
    $html="";
    for($i=0;$i<$result['data']['num'];) {
        $i++;
        $htmls = <<<EOF
<tr>
    <th scope="row">{$i}</th>
    <td>{$result['data']['dataList'][$i - 1]['name']}</td>
    <td>{$result['data']['dataList'][$i - 1]['type']}</td>
    <td>{$result['data']['dataList'][$i - 1]['time']}</td>
    <td>{$result['data']['dataList'][$i - 1]['disk']}</td>
    <td>原因</td>
    <td><a href="javascript:void(0)" onclick="showDetail({$result['data']['dataList'][$i - 1]['id']})">详细信息</a></td>
</tr>
EOF;
        $html.=$htmls;
    }
    $model=<<<EOF
<div class="modal fade" id="infoDetail" tabindex="-1" role="dialog" aria-labelledby="infoDetailTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoDetailTitle">详细信息</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="infoDetailName">姓名：</p>
                <p id="infoDetailSex">性别：</p>
                <p id="infoDetailPhone">手机号码：</p>
                <p id="infoDetailTime">做种时间：</p>
                <p id="infoDetailDisk">硬盘大小：</p>
                <p id="infoDetailEmail">邮箱：</p>
                <p id="infoDetailId">学号：</p>
                <p id="infoDetailShcool">学校/学院：</p>
                <p id="infoDetailWork">工作：</p>
                <p id="infoDetailPostTime">提交时间：</p>
                <p id="infoDetailIp">提交IP：</p>
                <p id="infoDetailFavorite">喜好：</p>
                <p id="infoDetailReason">加入理由：</p>
                <p id="infoDetailOther">备注：</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
EOF;
    $html=<<<EOF
<table class="table table-hover table-striped table-bordered">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">姓名</th>
        <th scope="col">类型</th>
        <th scope="col">做种时间</th>
        <th scope="col">硬盘大小</th>
        <th scope="col">原因</th>
        <th scope="col">详细信息</th>
    </tr>
    </thead>
    <tbody>
    {$html}
    </tbody>
</table>
{$model}
EOF;
    $script=<<<EOF
<script>
    function showDetail(id){
        $.ajax({
            async: true,   //是否为异步请求
            cache: false,  //是否缓存结果
            type: "GET", //请求方式
            dataType: "json",   //服务器返回的数据是什么类型
            url: "http://pttest.spcsky.com/invite/manage/operate.php" ,//url
            data: {
                publickey:"{$result['data']['passkey']['publicKey']}",
                passkey:"{$result['data']['passkey']['passKey']}",
                timeStamp:"{$result['data']['passkey']['timeStamp']}",
                require:"getData",
                id:id,
            },
            success: function (result) {
                if(result.code==200) {
                    //成功
                    $('#infoDetailName').text("姓名："+result.data.dataList.name);
                    $('#infoDetailSex').text("性别："+result.data.dataList.sex);
                    $('#infoDetailPhone').text("手机号码："+result.data.dataList.phone);
                    $('#infoDetailTime').text("做种时间："+result.data.dataList.time);
                    $('#infoDetailDisk').text("硬盘大小："+result.data.dataList.disk);
                    $('#infoDetailEmail').text("Email："+result.data.dataList.email);
                    $('#infoDetailId').text("学号："+result.data.dataList.id);
                    $('#infoDetailShcool').text("学校/学院："+result.data.dataList.school);
                    $('#infoDetailWork').text("工作："+result.data.dataList.work);
                    $('#infoDetailPostTime').text("提交时间："+result.data.dataList.postTime);
                    $('#infoDetailIp').text("IP："+result.data.dataList.ip);
                    $('#infoDetailFavorite').text("喜好："+result.data.dataList.favorite);
                    $('#infoDetailReason').text("加入原因："+result.data.dataList.reason);
                    $('#infoDetailOther').text("备注："+result.data.dataList.other);
                    
                    $('#infoDetail').modal('show');
                }else{
                    //alert(result.code+":"+result.msg);
                    swal(result.msg,"("+result.code+") "+result.info,"warning");
                }
            },
            error : function() {
                //alert("异常！");
                swal("发生异常","Ajax服务异常，请联系管理员","error");
            }
        });

    }
</script>
EOF;
    $return=array(
        'html'=>$html,
        'script'=>$script,
    );
    return $return;
}
function logOut(){

}
/**
 * 获取完整URL
 * @return string
 */
function curPageURL()
{
    $pageURL = 'http';

    if ($_SERVER["HTTPS"] == "on")
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
function creatHTML($inner,$page,$select){
    $dashBoard="";$totalList="";$unCheckList="";$problemList="";
    switch ($select){
        case 1:
            $dashBoard="active";
            break;
        case 2:
            $totalList="active";
            break;
        case 3:
            $unCheckList="active";
            break;
        case 4:
            $problemList="active";
            break;
    }
    $script=$inner['script'];
    echo<<<EOF
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.css" rel="stylesheet">
    <title>百川PT邀请码管理平台</title>

</head>
<body>
<div class="container-fluid headers">
    <div class="container">
        <div class="media">
            <div class="media-left media-middle">
                <img class="media-object" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/PjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+PCEtLQpTb3VyY2UgVVJMOiBob2xkZXIuanMvNjR4NjQKQ3JlYXRlZCB3aXRoIEhvbGRlci5qcyAyLjYuMC4KTGVhcm4gbW9yZSBhdCBodHRwOi8vaG9sZGVyanMuY29tCihjKSAyMDEyLTIwMTUgSXZhbiBNYWxvcGluc2t5IC0gaHR0cDovL2ltc2t5LmNvCi0tPjxkZWZzPjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+PCFbQ0RBVEFbI2hvbGRlcl8xNjEyMWI1NDEzOCB0ZXh0IHsgZmlsbDojQUFBQUFBO2ZvbnQtd2VpZ2h0OmJvbGQ7Zm9udC1mYW1pbHk6QXJpYWwsIEhlbHZldGljYSwgT3BlbiBTYW5zLCBzYW5zLXNlcmlmLCBtb25vc3BhY2U7Zm9udC1zaXplOjEwcHQgfSBdXT48L3N0eWxlPjwvZGVmcz48ZyBpZD0iaG9sZGVyXzE2MTIxYjU0MTM4Ij48cmVjdCB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIGZpbGw9IiNFRUVFRUUiLz48Zz48dGV4dCB4PSIxMy4xNzk2ODc1IiB5PSIzNi41NTYyNSI+NjR4NjQ8L3RleHQ+PC9nPjwvZz48L3N2Zz4=" alt="BCPT">
            </div>
            <div class="media-body">
                <h1 class="media-heading">百川PT邀请码管理平台</h1>
                <p>海纳百川，高速分享</p>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {$dashBoard}" href="{$page}?page=dashBoard">
                            <i class="fa fa-home feather" aria-hidden="true"></i>
                            总览 <span class="sr-only">(current)</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {$totalList}" href="{$page}?page=totalList">
                            <i class="fa fa-list-ol feather" aria-hidden="true"></i>
                            总申请列表
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {$unCheckList}" href="{$page}?page=unCheckList">
                            <i class="fa fa-check-square-o feather" aria-hidden="true"></i>
                            待处理列表
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {$problemList}" href="{$page}?page=problemList">
                            <i class="fa fa-exclamation-triangle feather" aria-hidden="true"></i>
                            问题列表
                        </a>
                    </li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>其他选项</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link" href="{$page}?page=logOut">
                            <i class="fa fa-sign-out feather" aria-hidden="true"></i>
                            退出登录
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="col py-2">
            {$inner['html']}
        </div>
    </div>
</div>



<footer class="container-fluid footers">
    <div class="container">
        <!--<info></info>-->

        <p>&copy;百川PT-哈尔滨工业大学</p>
    </div>
</footer>


<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert-dev.min.js"></script>
<script src="https://cdn.bootcss.com/Chart.js/2.7.1/Chart.bundle.min.js"></script>
<script src="http://www.chartjs.org/samples/latest/utils.js"></script>
{$script}
</body>

</html>
EOF;

}
?>