function ajaxPost(post) {
    $.ajax({
        async: true,   //是否为异步请求
        cache: false,  //是否缓存结果
        type: "POST", //请求方式为POST
        dataType: "json",   //服务器返回的数据是什么类型
        url: "ajax.php" ,//url
        data: $(post).serialize(),
        success: function (result) {
            console.log(result);//打印服务端返回的数据(调试用)
            if(result.code==200) {
                //成功
                swal("申请成功","您已经申请成功，待管理员审核后邮件通知结果","success");
                $('#btn_submit_in').attr('disabled',false);
                $('#btn_submit_in').attr('value','提交');
                $('#btn_submit_out').attr('disabled',false);
                $('#btn_submit_out').attr('value','提交');

            }else{
                //alert(result.code+":"+result.msg);
                swal(result.msg,"("+result.code+") "+result.info,"warning");
                $('#btn_submit_in').attr('disabled',false);
                $('#btn_submit_in').attr('value','提交');
                $('#btn_submit_out').attr('disabled',false);
                $('#btn_submit_out').attr('value','提交');
            }
        },
        error : function() {
            //alert("异常！");
            swal("发送异常","Ajax服务异常，请联系管理员","error");
            $('#btn_submit_in').attr('disabled',false);
            $('#btn_submit_in').attr('value','提交');
            $('#btn_submit_out').attr('disabled',false);
            $('#btn_submit_out').attr('value','提交');
        }
    });
}
$('#inSchool').submit(function (){
    if(inSchoolFromCheck()) {
        $('#btn_submit_in').attr('disabled',true);
        $('#btn_submit_in').attr('value','正在提交');
        ajaxPost('#inSchool');
    }
    return false;
});
$('#outSchool').submit(function (){
    if(outSchoolFromCheck()) {
        //document.getElementById("btn_submit_out").value = "正在提交";
        $('#btn_submit_out').attr('disabled',true);
        $('#btn_submit_out').attr('value','正在提交');
        ajaxPost('#outSchool');
    }
    return false;
});
