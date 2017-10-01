/**
 * Created by ITISME on 18/1/2560.
 */
$(document).ready(function(){
    $("#login").click(function(){
        var username = $("#username").val();
        var password = $("#password").val();
        // Checking for blank fields.
        //if( username =='' || password =='')
        if( username =='' ){
            $('input[type="text"],input[type="password"]').css("border","2px solid red");
            $('input[type="text"],input[type="password"]').css("box-shadow","0 0 3px red");
            alert("ไม่สามารถเป็นค่าว่างได้ !!!");
        }else {
            $.post("login.php",{ username: username, password:password},
                function(data) {
                    //alert(data);
                    if(data=='false'){
                        $('input[type="text"],input[type="password"]').css({"border":"2px solid red","box-shadow":"0 0 3px red"});
                      //  $("#msg").innerHTML = "username หรือ password ไม่ถูกต้อง !!!";
                        alert("ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง !!!");
                    } else if(data=='true'){
                        $("form")[0].reset();
                        $('input[type="text"],input[type="password"]').css({"border":"2px solid #00F5FF","box-shadow":"0 0 5px #00F5FF"});
                     //   window.location = "main.php";
                        //alert(data);
                    } else{
                   //     window.location = "login.html";
                        //alert(data);
                    }
                });
        }
    });
});