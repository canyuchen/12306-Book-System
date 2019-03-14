<!doctype html>
<?php session_start();?>
<html>
<head>
<meta charset="utf-8"/>
<title>车次查询</title>
<body>

<center>
<img src="/figs/train.jpeg"  />
</center>
<center>
<?php
// 具体车次查询
//echo $_SESSION["name"];
$tomorrow = date("Y-m-d", strtotime("+1 day"));
//echo $tomorrow;
$trainid = $_POST["trainid"];
$inputdate = $_POST["date"];
//echo $date;
//获取查询日期，若用户不输入，则默认为明天
if ($inputdate){
	$thedate = $inputdate;
}
else{
	$thedate = $tomorrow;
}
//echo $thedate;

// $conn = pg_connect("host=localhost port=5432 dbname=train user=fenglv password=nopasswd");
$conn = pg_connect("host=localhost port=5432 dbname=train user=postgres password=postgres") or die('connection failed') ;
if (!$conn){
	echo "连接失败";
}
$sql = <<<EOF
	SELECT * FROM A_TrainId WHERE A_TrainId = '$trainid';
EOF;
$ret1 = pg_query($conn, $sql);

echo "车次情况如下：<br>
      <table border=\"4\">
      <tr>
	  <td>车次</td>
      <td>类型</td>
      <td>站名</td>
      <td>站次</td>
      <td>到达时间</td>
      <td>出发时间</td>
      <td>硬座价格</td>
      <td>软座价格</td> 
      <td>硬卧上铺价格</td>
      <td>硬卧中铺价格</td>
      <td>硬卧下铺价格</td>
      <td>软卧上铺价格</td>
      <td>软卧下铺价格</td>
	  </tr>";

//获取总站数
$get_station_num = <<<EOF
	SELECT COUNT(A_TrainId)
	FROM A_TrainId
	WHERE A_TrainId = '$trainid';
EOF;
$ret2 = pg_query($conn, $get_station_num);
$station_num = pg_fetch_row($ret2);
$sta_num = $station_num[0];

// pg_close($conn);

// $conn = pg_connect("host=localhost port=5432 dbname=train user=postgres password=postgres") or die('connection failed') ;
// if (!$conn){
// 	echo "连接失败";
// }
$row_num = 0;
// echo "<center>";
while ($row = pg_fetch_row($ret1)){
	////echo count($row);
	$num = count($row);
	echo "<tr>";
	

	// for ( $i = 0; $i < $num; $i = $i + 1 ){
	// 	echo "<td>" . "$row[$i]" . "</td>";
	// }
	echo "<td>" . "$row[0]" . "</td>";
	if($row_num == 0){
		echo "<td>" . "始发站" . "</td>";
	}
	elseif($row_num == $sta_num - 1){
		echo "<td>" . "终点站" . "</td>";
	}
	else{
		echo "<td>" . "中间站" . "</td>";
	}

	$row[5]  = $row[5] ? $row[5] :   '-';	
	$row[6]  = $row[6] ? $row[6] :   '-';	
	$row[7]  = $row[7] ? $row[7] :   '-';	
	$row[8]  = $row[8] ? $row[8] :   '-';	
	$row[9]  = $row[9] ? $row[9] :   '-';	
	$row[10] = $row[10] ? $row[10] : '-';	
	$row[11] = $row[11] ? $row[11] : '-';	

	echo "<td>" . "$row[1]" . "</td>";
	echo "<td>" . "$row[2]" . "</td>";
	echo "<td>" . "$row[3]" . "</td>";
	echo "<td>" . "$row[4]" . "</td>";
	echo "<td>" . "$row[5] " . "</td>";
	echo "<td>" . "$row[6] " . "</td>";
	echo "<td>" . "$row[7] " . "</td>";
	echo "<td>" . "$row[8] " . "</td>";
	echo "<td>" . "$row[9] " . "</td>";
	echo "<td>" . "$row[10]" . "</td>";
	echo "<td>" . "$row[11]" . "</td>";

	$row_num++;
	echo "</tr>";
}

echo "</table>";


$get_price = <<<EOF
	SELECT *
	FROM A_TrainId
	WHERE A_TrainId = '$trainid' 
	AND   A_StationNum = $sta_num;
EOF;

$ret = pg_query($conn, $get_price);
$hastype = array();
$price = pg_fetch_row($ret);
//$hastype = array(0, 0, 0, 0, 0, 0, 0);
$hastype = array($price[5], $price[6], $price[7],
 $price[8], $price[9], $price[10], $price[11]);

//获取余票信息
$get_booked_ticket = <<<EOF
	with T1(T1_Type, T1_SeatNum) as
	(SELECT S_Type, S_SeatNum
		FROM Seats 
		WHERE S_TrainId = '$trainid'
		AND S_PStationNum >= 1
		AND S_PStationNum < $sta_num
		AND S_Date = '$thedate')
	
	SELECT T1_Type, MAX(T1_SeatNum)
	FROM T1
	GROUP BY T1_Type;
EOF;
$ret = pg_query($conn, $get_booked_ticket);
if (!$ret){
	echo "执行失败";
}

$all_type = array("YZ", "RZ", "YW1", "YW2", "YW3", "RW1", "RW2");

$left_num = array(0, 0, 0, 0, 0, 0, 0);

for ($i = 0; $i < 7; $i = $i + 1){
	if (!$hastype[$i])
		$left_num[$i] = -1;//不存在
	else
		$left_num[$i] = 5;
}
//计算余票
while ($row = pg_fetch_row($ret)){

	if ($row[0] == $all_type[0]){
		$left_num[0] = 5 - $row[1];		
	}
	if ($row[0] == $all_type[1]){
		$left_num[1] = 5 - $row[1];		
	}
	if ($row[0] == $all_type[2]){
		$left_num[2] = 5 - $row[1];		
	}
	if ($row[0] == $all_type[3]){
		$left_num[3] = 5 - $row[1];		
	}
	if ($row[0] == $all_type[4]){
		$left_num[4] = 5 - $row[1];		
	}
	if ($row[0] == $all_type[5]){
		$left_num[5] = 5 - $row[1];		
	}
	if ($row[0] == $all_type[6]){
		$left_num[6] = 5 - $row[1];		
	}
	
}
echo "<center>";
echo "<p>$thedate ，列车 $trainid 的余票信息如下</p>
      <table border = \"4\">
      <tr>
      <td>硬座</td>
      <td>软座</td>
      <td>硬卧上</td>
      <td>硬卧中</td>
      <td>硬卧下</td>
      <td>软卧上</td>
      <td>软卧下</td>
      </tr>
	  <tr>";
echo "</center>";
for ($i = 0; $i <7; $i = $i + 1){
	if ($left_num[$i] == -1)
		echo "<td> - </td>";
	elseif( $left_num[$i] == 0 )
		echo "<td>0</td>";
	else{
		$k = $i + 5;
		echo "<td><a href=\"booking.php?trainid=$trainid&date=$thedate&type=$all_type[$i]&price=$price[$k]&fromstation=1&tostation=$sta_num\">$left_num[$i]</a></td>";

	}
}

echo "</tr>";
echo "</table>";
pg_close($conn);
?>
</center>
</body>
</html>
