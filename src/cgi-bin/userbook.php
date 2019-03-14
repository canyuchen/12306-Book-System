<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>用户订单</title>
</head>
<body>
<center>
<img src="/figs/train.jpeg"  />
</center>

<center>
<?php
//　显示管理员查询的用户订单结果
$username = $_GET["username"];
$id = $_GET["id"];
//echo "id = $id";
echo "<H3>用户 $username 的订单</H3>";
// $conn = pg_connect("host=localhost port=5432 dbname=train user=fenglv password=nopasswd");
$conn = pg_connect("host=localhost port=5432 dbname=train user=postgres password=postgres") or die('connection failed') ;
if (!$conn){
	echo "连接失败";
}
$bookselect = <<<EOF
	SELECT *
	FROM book 
	WHERE b_userid = '$id';
EOF;
$ret = pg_query( $conn, $bookselect );
$row_num = pg_num_rows($ret);
//echo $row_num;
if ( $row_num == 0 ){
	echo "<p>该用户无任何订单记录。</p>";
}
else {
	//$all_record = pg_fetch_all($ret);
	//echo $all_record[0][0];
	//echo count($all_record);
	$status = array ("normal"=>"未乘坐", "cancelled"=>"已取消", "past"=>"已乘坐");
	//$notuse = "未乘坐";
	//$used   = "已乘坐";
	//$cancel = "已取消";
	//echo $row_num;
	$seat   = array("YZ"=>"硬座", "RZ"=>"软座", "YW1"=>"硬卧上", "YW2"=>"硬卧中", "YW3"=>"硬卧下", "RW1"=>"软卧上", "RW2"=>"软卧下");
	echo "<table border = \"4\">
	      <tr>
	      <th>订单ID</th>
	      <th>用户ID</th>
	      <th>列车号</th>
	      <th>日期</th>
	      <th>始发站</th>
	      <th>到达站</th>
	      <th>类型</th>
	      <th>票价</th>
	      <th>订单状态</th>
	      </tr>";
	//$i = 0;
	while ($all_record = pg_fetch_row($ret)){
		$i = $i + 1;
		$sd1 = $all_record[4];
		$sd2 = $all_record[5];
		$trainid = $all_record[2];
		$sdname = <<<EOF
			 SELECT A_StationName
			 FROM A_TrainId 
			 WHERE A_StationNum = $sd1
			 AND A_TrainId = '$trainid';
EOF;
		$ret1 = pg_query( $conn, $sdname );
		$station1 = pg_fetch_row($ret1);
		$station1name = $station1[0];
		$sdname = <<<EOF
			 SELECT A_StationName 
			 FROM A_TrainId 
			 WHERE A_StationNum = $sd2
			 AND A_TrainId = '$trainid';
EOF;
		$ret1 = pg_query( $conn, $sdname );
		$station2 = pg_fetch_row($ret1);
		$station2name = $station2[0];

		echo "<tr>";
		for ( $j = 0; $j < 4; $j = $j + 1 )
			echo "<td>$all_record[$j]</td>";
		$se_index = $all_record[6];
		$st_index = $all_record[8];
		echo "<td>$station1name</td>
		      <td>$station2name</td>
		      <td>$seat[$se_index]</td>
		      <td>$all_record[7]</td>
		      <td>$status[$st_index]</td>
		      </tr>";
		//$ret = pg_query( $conn, $bookselect );
		//for ($j = 0; $j <= $i; $j++)
		//    $all_record = pg_fetch_row($ret);
	}
	echo "</table>";
}
pg_close($conn);
?>
</center>
</body>
</html>
