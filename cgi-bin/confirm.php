<!doctype html>
<?php session_start();?>
<html>
<head>
<meta charset = "utf-8">
<title>订单确定</title>
</head>
	<body>
<center>
<img src="/figs/train.jpeg"  />
</center>
<center>    
<?php 
$username = $_SESSION["name"];
//echo $username;
$trainid = $_GET["trainid"];
$date = $_GET["date"];
$stnum1 = $_GET["from"];
$stnum2 = $_GET["to"];
$seattype = $_GET["type"];
$money = $_GET["money"];
$gotime =$_GET["gotime"];
$fromname = $_GET["fromname"];
$toname = $_GET["toname"];
$seat = $_GET["seat"];

// $conn = pg_connect("host=localhost port=5432 dbname=train user=fenglv password=nopasswd");
$conn = pg_connect("host=localhost port=5432 dbname=train user=postgres password=postgres") or die('connection failed') ;
if (!$conn){
	echo "连接失败";
}

$get_uid = <<<EOF
			SELECT user_id 
			FROM userinfo
			WHERE u_uname = '$username';
EOF;

$ret = pg_query($conn, $get_uid);
$result = pg_fetch_row($ret);
$uid = $result[0];

$book = <<<EOF
		INSERT INTO 
    	book(B_UserId,B_TrainId,B_Date,B_StationNum1,B_StationNum2,B_SType,B_Money,B_Status)
	    VALUES('$uid', '$trainid', '$date', $stnum1, $stnum2, '$seattype', $money, 'normal');
EOF;
$ins = pg_query($conn, $book);
if($ins){
	echo "您已成功预定 $date $gotime ，从 $fromname 到 $toname 的 $trainid 次列车的 $seat 票一张，票价为 $money (包含5元手续费)。";
}
else{
	echo "预定失败!!!";
}
//echo $uid;
//echo $date;
//echo $seattype;
//echo $money;
for ($x=$stnum1; $x<$stnum2; $x++) {
	//echo $x . "  ";
    $query_seat_num = <<<EOF
        select S_SeatNum
        from Seats
        where S_TrainId = '$trainid'
            and S_PStationNum = $x
            and S_Type = '$seattype'
            and S_Date = '$date';
EOF;
    $ret = pg_query($conn, $query_seat_num);
	if (!$ret){
		echo "执行失败";
	}
    $row_num = pg_num_rows($ret);
    if ($row_num == 0){
        $fuction = <<<EOF
            insert into
                Seats(S_TrainId,S_PStationNum,S_Type,S_Date,S_SeatNum)
            values ('$trainid', $x, '$seattype', '$date', 1);
EOF;
    $ret = pg_query($conn, $fuction);
		if (!$ret){
			echo "执行失败";
		}
    }
    else{
        $row = pg_fetch_row($ret);
		$seat_num = $row[0];
        $new_seat_num = $seat_num + 1;
        $fuction = <<<EOF
            update Seats
            set S_SeatNum = $new_seat_num
            where S_TrainId = '$trainid'
                and S_PStationNum = $x
                and S_Type = '$seattype'
                and S_Date = '$date';
EOF;
        $ret = pg_query($conn, $fuction);
		if (!$ret){
			echo "执行失败";
		}
    }
}
// echo "<p><a href = \"back.php?fromname=$toname&toname=$fromname&date=$date&username=$username\">点击</a>查询更多信息。</p>";

echo "<p><a href = \"../serve/train-search.php\">点击</a>查询更多具体车次。</p>";
echo "<p><a href = \"../serve/dist-search.php\">点击</a>查询更多两地间车次。</p>";
echo "<p><a href = \"../serve/book.php\">点击</a>查询订单详情。</p>";

?>
</center>
</body>
</html>
