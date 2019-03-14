<!doctype html>
<?php session_start();?>
<html>
<head>
<meta charset = "utf-8">
<title>取消订单</title>
</head>
<body>
<center>
<img src="/figs/train.jpeg"  />
</center>
<center>
<?php
$bookid = $_GET["bookid"];
$the_trainid = $_GET["trainid"];
$station_num1 = $_GET["num1"];
$station_num2 = $_GET["num2"];
$the_type = $_GET["type"];
$the_date = $_GET["date"];

echo "<br>";
echo "<br>";
// $conn = pg_connect("host=localhost port=5432 dbname=train user=fenglv password=nopasswd");
$conn = pg_connect("host=localhost port=5432 dbname=train user=postgres password=postgres") or die('connection failed') ;
if (!$conn){
	echo "连接失败";
}

for ($x=$station_num1; $x<$station_num2; $x++) {
    $query_seat_num = <<<EOF
        select S_SeatNum
        from Seats
        where S_TrainId = '$the_trainid'
            and S_PStationNum = $x
            and S_Type = '$the_type'
            and S_Date = '$the_date';
EOF;
    $ret = pg_query($conn, $query_seat_num);
	if (!$ret){
		echo "执行失败";
	}
    $row = pg_fetch_row($ret);
	$seat_num = $row[0];
    if ($seat_num == 1){
		$fuction = <<<EOF
		DELETE
        from Seats
        where S_TrainId = '$the_trainid'
            and S_PStationNum = $x
            and S_Type = '$the_type'
            and S_Date = '$the_date';
EOF;
      $ret =  pg_query($conn, $fuction);
		if (!$ret){
			echo "执行失败";
		}
    }
    else{
        $new_seat_num = $seat_num - 1;
        $fuction = <<<EOF
            update Seats
            set S_SeatNum = $new_seat_num
            where S_TrainId = '$the_trainid'
                and S_PStationNum = $x
                and S_Type = '$the_type'
                and S_Date = '$the_date';
EOF;
        $ret = pg_query($conn, $fuction);
		if (!$ret){
			echo "执行失败";
		}
    }
}

$cancel = <<<EOF
			UPDATE book
			SET b_status = 'cancelled'
			WHERE b_id = '$bookid';
EOF;

$ret = pg_query($conn, $cancel);

if (!$ret){
	echo "订单取消失败！";
}
else{
	echo "订单已成功取消！";
}
echo "<br>";
echo "<br>";
echo "<a href = \"../12306.php\">返回首页</a>";
echo "<br>";
echo "<br>";
echo "<a href = \"../12306.php\">继续查询</a>";
pg_close($conn);
?>
</center>
</body>
</html>
