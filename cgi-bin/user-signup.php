<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>用户注册</title>
</head>
<body>
<center>
<img src="/figs/train.jpeg"  />
</center>

<center>
<?php 
include 'check.php';
$ret1 = check_id_len();
$ret2 = check_phone_len();
$ret3 = check_card_len();
$ret4 = check_username_len();
$ret5 = check_password_len();

if($ret1 == 1 && $ret2 == 1 && $ret3 == 1 && $ret4 == 1 && $ret5 == 1){
$conn = pg_connect("host=localhost port=5432 dbname=train user=postgres password=postgres") or die('connection failed') ;
if (!$conn){
	echo "连接失败";
}
else{

$name = $_POST["my-name"];
$username = $_POST["my-username"];
$user_id = $_POST["my-number"];
$phone = $_POST["my-phone"];   
$card = $_POST["my-card"];
$password = $_POST["my-password"];

//echo "连接成功";
$sql =<<<EOF
		SELECT u_uname 
		FROM userinfo 
		WHERE u_uname='$username';
EOF;
$ret = pg_query($conn, $sql);
//$result = pg_fetch_all($ret);
$sum = pg_num_rows($ret);

$sql_uid = <<<EOF
			SELECT user_id 
			FROM userinfo
			WHERE user_id = '$user_id';
EOF;
$ret = pg_query($conn, $sql_uid);
$sum_uid = pg_num_rows($ret);

$sql_phone = <<<EOF
			SELECT u_phone
			FROM userinfo
			WHERE u_phone = '$phone';
EOF;
$ret = pg_query($conn, $sql_phone);
$sum_phone = pg_num_rows($ret);

//include 'check.php';
$ret6 = check_username_exist($sum);
$ret7 = check_id_exist($sum_uid);
$ret8 = check_phone_exist($sum_phone);

//echo "连接成功";

if($ret6 == 1 && $ret6 == 1 && $ret6 == 1){

echo "连接成功";

$ins = <<<EOF
	INSERT INTO 
	userinfo(user_id,u_name,u_phone, u_uname, u_creditcardid) 
	VALUES ('$user_id', '$name', '$phone', '$username', '$card');
EOF;

$insert = pg_query($conn, $ins);
if (!$insert){
	echo "注册用户失败。";
}
else {
	$login = "../sign/user-sign-in.php";
	echo "用户注册成功。" . "请<a href = $login>登录</a>。";
}

}

}
}


?>
</center>
</body>
</html>
