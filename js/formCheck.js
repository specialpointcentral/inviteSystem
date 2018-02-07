function inSchoolFromCheck() {
    var name = document.getElementsByName("name")[0];
    if(name.value ==  null || name.value == ''){
        swal("姓名不能为空", "Name is required", "warning");
        //alert("姓名不能为空");
        return false;
    }
    var id=document.getElementsByName("id")[0];
    if(id.value ==  null || id.value == ''){
        swal("学号不能为空", "ID number is required", "warning");
        //alert("学号不能为空");
        return false;
    }else if(parseInt(id.value) <=80100000 || parseInt(id.value) >=299999999){
        swal("学号有误", "ID number is incorrect", "warning");
        //alert("学号有误");
        return false;
    }
    var phone=document.getElementsByName("phone")[0];
    if(phone.value ==  null || phone.value == ''){
        swal("手机号码不能为空", "Phone number is required", "warning");
        //alert("手机号码不能为空");
        return false;
    }else if(phone.value <=10000000000 || phone.value >=20000000000){
        swal("手机号码有误", "Phone number is incorrect", "warning");
        //alert("手机号码有误");
        return false;
    }
    var time=document.getElementsByName("time")[0];
    if(time.value ==  null || time.value == ''){
        swal("平均时间不能为空", "Time is required", "warning");
        //alert("时间不能为空");
        return false;
    }
    var disk=document.getElementsByName("disk")[0];
    if(disk.value ==  null || disk.value == ''){
        swal("硬盘大小不能为空", "Storage capacity is required", "warning");
        //alert("硬盘大小不能为空");
        return false;
    }
    var mail=document.getElementsByName("email")[0];
    if(mail.value ==  null || mail.value == ''){
        swal("邮件地址不能为空", "Email address is required", "warning");
        //alert("邮件地址不能为空");
        return false;
    }
    return true;
}

function isStudent(select){
    var selectedOption=select.options[select.selectedIndex];
    var school=document.getElementById("school");
    if (selectedOption.value=="学生"){
        //is select
        school.disabled="";
    }else{
        school.disabled="disabled";
        school.value="";
    }
}

function outSchoolFromCheck() {
    var name = document.getElementsByName("name")[1];
    if(name.value ==  null || name.value == ''){
        swal("姓名不能为空", "Name is required", "warning");
        //alert("姓名不能为空");
        return false;
    }
    var phone=document.getElementsByName("phone")[1];
    if(phone.value ==  null || phone.value == ''){
        swal("手机号码不能为空", "Phone number is required", "warning");
        //alert("手机号码不能为空");
        return false;
    }else if(phone.value <=10000000000 || phone.value >=20000000000){
        swal("手机号码有误", "Phone number is incorrect", "warning");
        //alert("手机号码有误");
        return false;
    }
    var time=document.getElementsByName("time")[1];
    if(time.value ==  null || time.value == ''){
        swal("平均时间不能为空", "Time is required", "warning");
        //alert("时间不能为空");
        return false;
    }
    var disk=document.getElementsByName("disk")[1];
    if(disk.value ==  null || disk.value == ''){
        swal("硬盘大小不能为空", "Storage capacity is required", "warning");
        //alert("硬盘大小不能为空");
        return false;
    }
    var select =document.getElementsByName("work")[0];
    var work =select.options[select.selectedIndex].value;
    if(work=="学生"){
        var id=document.getElementsByName("school")[1];
        if(id.value ==  null || id.value == ''){
            swal("学校不能为空", "School name is required", "warning");
            //alert("学校不能为空");
            return false;
        }
    }
    var mail=document.getElementsByName("email")[1];
    if(mail.value ==  null || mail.value == ''){
        swal("邮件地址不能为空", "Email address is required", "warning");
        //alert("邮件地址不能为空");
    }
    return true;
}