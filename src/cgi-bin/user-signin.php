<!doctype html>
<?php session_start();?>
<html>
<head>
<meta charset="utf-8"/>
<title>用户登录</title>
</head>
<body>

<center>
<img src="/figs/train.jpeg"  />
</center>
<center>
<?php 
// 用户登录
$username = $_POST["name"];
$_SESSION["name"] = $_POST["name"];
$trainS_href = "../serve/train-search.php";
$distS_href  = "../serve/dist-search.php";
$bookS_href  = "../serve/book.php";

$conn = pg_connect("host=localhost port=5432 dbname=train user=postgres password=postgres") or die('connection failed') ;
if (!$conn){
	echo "连接失败";
}

$sql = <<<EOF
	SELECT u_uname FROM userinfo WHERE u_uname = '$username';
EOF;
$ret = pg_query($conn, $sql);
$sum = pg_num_rows($ret);

// if (!$sum){
// 	echo "<center>";
// 	// include_once "check.php";
// 	check_username_correct($sum);
// 	echo "</center>";
// 	session_destroy();
// }
// else{

include_once 'check.php';
$ret1 = check_user_registered($sum);

if($ret1 == 1){
	// $_SESSION["name"] = $_POST["name"];
	//echo $_SESSION["name"];
	// $trainS_href = "../serve/train-search.php";
	// $distS_href  = "../serve/dist-search.php";
	// $bookS_href  = "../serve/book.php";
	echo "<center>";
	echo "<br>
		  尊敬的乘客$username" . " ,欢迎访问12306在线购票系统，我们愿竭诚为您服务！
	      <br>
	      <br>
	      请在下面点击选择您需要的服务:
          <br>
		  <ul>";
	echo "</center>";
	// 	  <li><a href = $trainS_href>查询具体车次</a></li>
	// 	  <br>
	// 	  <li><a href = $distS_href>查询两地间车次</a></li>
	// 	  <br>
	// 	  <li><a href = $bookS_href>订单查询</a></li>
	// 	  <br>
	// 	  </ul>";
	echo "<center>";
	echo "<table border = \"4\"><tr></tr>";
	echo "<tr><td><a href = \"../serve/train-search.php\">查询具体车次</td></tr>";
	echo "<tr><td><a href = \"../serve/dist-search.php\">查询两地间车次</td></tr>";
	echo "<tr><td><a href = \"../serve/book.php\">订单查询</td></tr>";
	echo "</table>";
	echo "</center>";
	// echo "</center>";	
}
else{
	session_destroy();
}

// }
pg_close($conn);
?>
</center>
</body>
</html>
