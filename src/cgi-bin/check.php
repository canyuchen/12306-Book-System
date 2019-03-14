<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>用户注册</title>
</head>
<body>

<?php 

function check_username_len(){
    $url = "../sign/sign-up.php";
    $username = $_POST["my-username"];
    $username_len = strlen("$username");
    if($username_len > 20){
        echo "用户名过长，请<a href = $url>返回</a>重新输入";
        echo "</br>";
        return 0;
    }
    echo "</br>";
    return 1;
}

function check_id_len(){
    $url = "../sign/sign-up.php";
    $user_id = $_POST["my-number"];
    $uid_len = strlen("$user_id");
    if ($uid_len != 18){
        echo "身份证号必须为 18 位，请<a href = $url>返回</a>重新输入";
        echo "</br>";
        return 0;
    }
    echo "</br>";
    return 1;
}

function check_phone_len(){
    $url = "../sign/sign-up.php";
    $phone = $_POST["my-phone"]; 
    $phone_len = strlen("$phone");
    if($phone_len != 11){
        echo "手机号必须为 11 位，请<a href = $url>返回</a>重新输入";
        echo "</br>";        
        return 0;
    }
    echo "</br>";
    return 1;
}

function check_card_len(){
    $url = "../sign/sign-up.php";
    $card = $_POST["my-card"];
    $card_len = strlen("$card");
    if($card_len != 16){
        echo "信用卡号必须为 16 位，请<a href = $url>返回</a>重新输入";
        echo "</br>";
        return 0;
    }
    echo "</br>";
    return 1;
}

function check_password_len(){
    $url = "../sign/sign-up.php";
    $password = $_POST["my-password"];
    $password_len = strlen($password);
    if($password_len > 20){
        echo "用户名过长，请<a href = $url>返回</a>重新输入"; 
        echo "</br>";       
        return 0;
    }
    echo "</br>";
    return 1;
}

function check_username_exist($v){
    $url = "../sign/sign-up.php";
    $username = $_POST["my-username"];
    if($v || $username == "Admi"){
        echo "用户名已被注册，请<a href = $url>返回</a>重新注册";
        echo "</br>";
        return 0;
    }
    echo "</br>";
    return 1;
}

function check_username_correct($v){
    $url = "../sign/sign-up.php";
    $username = $_POST["name"];
    if($v){
        echo "用户名未注册，请<a href = $url>返回</a>注册";
        echo "</br>";
        return 0;
    }
    echo "</br>";
    return 1;
}

function check_id_exist($v){
    $url = "../sign/sign-up.php";
    if($v){
        echo "身份证号已被注册，请<a href = $url>返回</a>重新注册";
        echo "</br>";
        return 0;
    }
    echo "</br>";
    return 1;
}

function check_card_exist($v){
    $url = "../sign/sign-up.php";
    if($v){
        echo "信用卡已被注册，请<a href = $url>返回</a>重新注册";
        echo "</br>";
        return 0;
    }
    echo "</br>";
    return 1;
}

function check_phone_exist($v){
    $url = "../sign/sign-up.php";
    if($v){
        echo "手机号已被注册，请<a href = $url>返回</a>重新注册";
        echo "</br>";
        return 0;
    }
    echo "</br>";
    return 1;
}

function check_user_registered($v){
    if (!$v){
        echo "<center>";
        // include_once "check.php";
        check_username_correct($sum);
        echo "</center>";
        // session_destroy();
        return 0;
    }
    return 1;
}


function check_seaS_Type($v){
    switch ($v){
        case "YZ":
            $seat = "硬座";
            break;
        case "RZ":
            $seat = "软座";
            break;
        case "YW1":
            $seat = "硬卧上";
            break;
        case "YW2":
            $seat = "硬卧中";
            break;
        case "YW3":
            $seat = "硬卧下";
            break;
        case "RW1":
            $seat = "软卧上";
            break;
        case "RW2":
            $seat = "软卧下";
            break;
        }
    return $seat;
}






?>


</body>
</html>