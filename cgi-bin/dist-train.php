<!doctype html>
<?php session_start();?>
<html>
<head>
<meta charset = "utf-8">
<title>两地间查询</title>
</head>
	<body>

<center>
<img src="/figs/train.jpeg"  />
</center>
<center>
<?php
$username = $_SESSION["name"];
$from = $_POST["from"];
$to = $_POST["to"];
$date = $_POST["date"];
$inputtime = $_POST["time"];
//echo $to;
//echo $from;
$tomorrow = date("Y-m-d", strtotime("+1 day"));
if ($date){
	$thedate = $date;
}
else {
	$thedate = $tomorrow;
}

if ($inputime){
	$time = $inputtime;
}
else{
	$time = "00:00:00";
}
// $conn = pg_connect("host=localhost port=5432 dbname=train user=fenglv password=nopasswd");
$conn = pg_connect("host=localhost port=5432 dbname=train user=postgres password=postgres") or die('connection failed') ;
if (!$conn){
	echo "连接失败";
}

$query_train = <<<SQL
with S1(S1_TrainId, S1_StationNum) as 
(select A_TrainId.A_TrainId, A_TrainId.A_StationNum
    from A_TrainId, Station
    where A_TrainId.A_StationName = Station.S_Name
        and Station.S_City = '$from'),

S2(S2_TrainId,S2_StationNum) as
(select A_TrainId.A_TrainId, A_TrainId.A_StationNum
    from A_TrainId, Station
    where A_TrainId.A_StationName = Station.S_Name
        and Station.S_City = '$to'),

T1(T1_TrainId) as
(   select S1.S1_TrainId
    from S1, S2
    where S1.S1_TrainId = S2.S2_TrainId
        and S1.S1_StationNum < S2.S2_StationNum
    ),

T3(TA_TrainId, TA_StationName, TA_StationNum, TA_ArriveTime, TA_GoTime, TA_CostYZ, TA_CostRZ, TA_CostYW1, TA_CostYW2, TA_CostYW3, TA_CostRW1, TA_CostRW2, Tt1_trainid, Ts_name, s_city)  as
    (select *
    from A_TrainId, T1, Station
    where A_TrainId.A_TrainId = T1.T1_TrainId
        and A_TrainId.A_StationName = Station.S_Name
        and (Station.S_City = '$from'
            or Station.S_City = '$to')),

T4(T4_id,  yz, rz, yw1, yw2, yw3, rw1, rw2) as
(
select TA_TrainId, Max(TA_CostYZ)-Min(TA_CostYZ), Max(TA_CostRZ)-Min(TA_CostRZ), Max(TA_CostYW1)-Min(TA_CostYW1), Max(TA_CostYW2)-Min(TA_CostYW2), Max(TA_CostYW3)-Min(TA_CostYW3), Max(TA_CostRW1)-Min(TA_CostRW1), Max(TA_CostRW2)-Min(TA_CostRW2)
from T3
group by TA_TrainId
),

T5(T5_id,T5_StationName, T5_GoTime, T5_StationNum) as
(
select T1.T1_TrainId, A_TrainId.A_StationName, A_TrainId.A_GoTime, A_TrainId.A_StationNum
from T1, A_TrainId, Station
where A_TrainId = T1_TrainId
    and A_StationName = Station.S_Name
    and Station.S_City = '$from'
),

T6(T6_id, T6_StationName, T6_ArriveTime, T6_StationNum) as
(
select T1.T1_TrainId, A_TrainId.A_StationName, A_TrainId.A_ArriveTime, A_TrainId.A_StationNum
from T1, A_TrainId, Station
where A_TrainId = T1_TrainId
    and A_StationName = Station.S_Name
    and Station.S_City = '$to'
)

-- order by yz;
select DISTINCT 
T4.T4_id,
T5.T5_StationName, 
T5.T5_GoTime, 
T6.T6_StationName,
T6.T6_ArriveTime,  
T4.yz, 
T4.rz, 
T4.yw1, 
T4.yw2, 
T4.yw3, 
T4.rw1, 
T4.rw2, 
T5.T5_StationNum, 
T6.T6_StationNum
from T4, T5, T6
where T4.T4_id = T5.T5_id
    and T6.T6_id = T5.T5_id
    and T5_GoTime > '$time'
order by T4.yz
;
SQL;

$ret_train = pg_query($conn, $query_train);
if (!$ret_train){
	echo "执行失败";
}
$row_num = pg_num_rows($ret_train);
//echo "row_num = $row_num  ";

echo "直达方案和余票信息如下：<br>";
echo "<table border=\"4\">";
echo "<tr>";
echo "<td>方案</td>" ;
echo "<td>车次</td>" ;
echo "<td>出发站</td>" ;
echo "<td>出发时间</td>" ;
echo "<td>到达站</td>" ;
echo "<td>到达时间</td>";
echo "<td>硬座</td>" ;
// echo "<td>剩余票量</td>";
echo "<td>软座  </td>" ; 
echo "<td>硬卧上铺</td>";
echo "<td>硬卧中铺</td>" ;
echo "<td>硬卧下铺</td>" ;
echo "<td>软卧上铺</td>" ;
echo "<td>软卧下铺</td>";
echo "</tr>";


for ($x = 0; $x < min($row_num, 10); $x++){
    $a_row = pg_fetch_row($ret_train);
	echo "<tr>";
	$z = $x+1;
	echo "<td> $z</td>";
    for ($y = 0; $y < 12; $y++){
        echo "<td>$a_row[$y]</td>";
    }
    echo "</tr>";
	//获取站序

//$get_station_num = <<<EOF
//                SELECT COUNT(A_TrainId)
//                FROM A_TrainId
//                WHERE A_TrainId = '$a_row[0]';
//EOF;
//$ret = pg_query($conn, $get_station_num);
//$station_num = pg_fetch_row($ret);
//$sta_num = $station_num[0];

//$get_price = <<<EOF
//            SELECT *
//            FROM A_TrainId
//            WHERE A_TrainId = '$a_row[0]'
//            AND   A_StationNum = $sta_num;
//EOF;

//$ret = pg_query($conn, $get_price);
//$price = pg_fetch_row($ret);
////$hastype = array(0, 0, 0, 0, 0, 0, 0);
$hastype = array($a_row[5], $a_row[6], $a_row[7],
 $a_row[8], $a_row[9], $a_row[10], $a_row[11]);
$get_booked_ticket = <<<EOF
    with T1(T1_Type, T1_SeatNum) as
    (SELECT S_Type, S_SeatNum
        FROM Seats 
        WHERE S_TrainId = '$a_row[0]'
        AND S_PStationNum >= $a_row[12]
        AND S_PStationNum < $a_row[13]
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
	for ($i = 0; $i <7; $i = $i + 1){
		if ($row[0] == $all_type[$i])
			$left_num[$i] = 5 - $row[1];
	}
}

echo "<tr>";
echo "<td> </td>";
echo "<td> </td>";
echo "<td> </td>";
echo "<td> </td>";
echo "<td> </td>";
echo "<td> </td>";
for ($i = 0; $i <7; $i = $i + 1){
	if ($left_num[$i] == -1)
		echo "<td> - </td>";
	elseif( $left_num[$i] == 0 )
		echo "<td>0</td>";
	else{
		//$k = $i + 5;
		//echo $price[$k] . "   ";
		echo "<td><a href=\"booking.php?trainid=$a_row[0]&date=$thedate&type=$all_type[$i]&price=$hastype[$i]&fromstation=$a_row[12]&tostation=$a_row[13]\">$left_num[$i]</a></td>";

	}
}
echo "</tr>";
    // left tickets
    // $a_row[0] is $train_id
    // $from
    // $to
    // $date
}
echo "</table>";

$username = $_SESSION["name"];
$from = $_POST["from"];
$to = $_POST["to"];
$date = $_POST["date"];
$inputtime = $_POST["time"];
//echo $to;
//echo $from;
$tomorrow = date("Y-m-d", strtotime("+1 day"));
if ($date){
	$thedate = $date;
}
else {
	$thedate = $tomorrow;
}

if ($inputime){
	$time = $inputtime;
}
else{
	$time = "00:00:00";
}
// $conn = pg_connect("host=localhost port=5432 dbname=train user=fenglv password=nopasswd");
$conn = pg_connect("host=localhost port=5432 dbname=train user=postgres password=postgres") or die('connection failed') ;
if (!$conn){
	echo "连接失败";
}
/*
$query_train = <<<EOF
with S1(S1_TrainId, S1_StationNum) as 
(select A_TrainId.A_TrainId, A_TrainId.A_StationNum
    from A_TrainId, Station
    where A_TrainId.A_StationName = Station.S_Name
        and Station.S_City = '$from'),

S2(S2_TrainId,S2_StationNum) as
(select A_TrainId.A_TrainId, A_TrainId.A_StationNum
    from A_TrainId, Station
    where A_TrainId.A_StationName = Station.S_Name
        and Station.S_City = '$to'),

T1(T1_TrainId) as
(   select S1.S1_TrainId
    from S1, S2
    where S1.S1_TrainId = S2.S2_TrainId
        and S1.S1_StationNum < S2.S2_StationNum
    ),

T3(TA_TrainId, TA_StationName, TA_StationNum, TA_ArriveTime, TA_GoTime, TA_CostYZ, 
   TA_CostRZ, TA_CostYW1, TA_CostYW2, TA_CostYW3, TA_CostRW1, TA_CostRW2, 
   Tt1_trainid, 
   Ts_name, s_city)  as
    (select *
    from A_TrainId, T1, Station
    where A_TrainId.A_TrainId = T1.T1_TrainId
        and A_TrainId.A_StationName = Station.S_Name
        and (Station.S_City = '$from'
			or Station.S_City = '$to')),

T4(T4_id,  yz, rz, yw1, yw2, yw3, rw1, rw2) as
(
select TA_TrainId, Max(TA_CostYZ)-Min(TA_CostYZ), Max(TA_CostRZ)-Min(TA_CostRZ), 
				   Max(TA_CostYW1)-Min(TA_CostYW1), Max(TA_CostYW2)-Min(TA_CostYW2), 
				   Max(TA_CostYW3)-Min(TA_CostYW3), Max(TA_CostRW1)-Min(TA_CostRW1), Max(TA_CostRW2)-Min(TA_CostRW2)
from T3
group by TA_TrainId
),

T5(T5_id,T5_StationName, T5_GoTime, T5_StationNum) as
(
select T1.T1_TrainId, A_TrainId.A_StationName, A_TrainId.A_GoTime, A_TrainId.A_StationNum
from T1, A_TrainId, Station
where A_TrainId = T1_TrainId
    and A_StationName = Station.S_Name
    and Station.S_City = '$from'
),

T6(T6_id, T6_StationName, T6_ArriveTime, T6_StationNum) as
(
select T1.T1_TrainId, A_TrainId.A_StationName, A_TrainId.A_ArriveTime, A_TrainId.A_StationNum
from T1, A_TrainId, Station
where A_TrainId = T1_TrainId
    and A_StationName = Station.S_Name
    and Station.S_City = '$to'
)

-- order by yz;
select DISTINCT 
T4.T4_id,
T5.T5_StationName, 
T5.T5_GoTime, 
T6.T6_StationName,
T6.T6_ArriveTime,  
T4.yz, 
T4.rz, 
T4.yw1, 
T4.yw2, 
T4.yw3, 
T4.rw1, 
T4.rw2, 
T5.T5_StationNum, 
T6.T6_StationNum
from T4, T5, T6
where T4.T4_id = T5.T5_id
    and T6.T6_id = T5.T5_id
    and T5_GoTime > '$time'
order by T4.yz
;
EOF;
*/
/*
$query_train = <<<EOF
with S1(S1_TrainId, S1_StationNum) as 
(select A_TrainId.A_TrainId, A_TrainId.A_StationNum
    from A_TrainId, Station
    where A_TrainId.A_StationName = Station.S_Name
        and Station.S_City = '$from'),

S2(S2_TrainId,S2_StationNum) as
(select A_TrainId.A_TrainId, A_TrainId.A_StationNum
    from A_TrainId, Station
    where A_TrainId.A_StationName = Station.S_Name
        and Station.S_City = '$to'),

S3(S3_TrainID_1, S3_TrainID_2, S3_Stationnum_1, S3_Stationnum_2, S3_StationName_1, S3_StationName_2) as
(select pa1.A_TrainId, pa2.A_TrainId, pa1.A_StationNum, pa2.A_StationNum, pa1.A_StationName, pa2.A_StationName
    from Staion as st1, A_TrainId as pa1, A_TrainId as pa2, Station as st2
    where pa1.A_StationName = st1.S_name
        and pa2.A_StationName = st2.S_name
        and st1.S_city = st2.S_city
        and pa1.A_TrainId <> pa2.A_TrainId
        and ((st1.S_name=st2.S_name and (pa2.A_GoTime-pa1.A_ArriveTime)>='01:00:00' and (pa2.A_GoTime-pa1.A_ArriveTime)<='04:00:00')
            or (st1.S_name<>st2.S_name and (pa2.A_GoTime-pa1.A_ArriveTime)>='02:00:00' and (pa2.A_GoTime-pa1.A_ArriveTime)<='04:00:00'))
),


T1(T1_TrainID_1, T1_TrainID_2, T1_Stationnum_1, T1_Stationnum_2, T1_StationName_1, T1_StationName_2) as 
(select S3.S3_TrainID_1, S3.S3_TrainID_2, S3.S3_Stationnum_1, S3.S3_Stationnum_2, S3.S3_StationName_1, S3.S3_StationName_2
    from S1, S2, S3
    where S1.S1_TrainID = S3.S3_TrainID_1
        and S2.S2_TrainID = S3.S3_TrainID_2
        and S1.S1_StationNum < S3.S3_Stationnum_1
        and S2.S2_StationNum > S3.S3_Stationnum_2
),

T2(T2_TrainID_1, T2_TrainID_2, train1_yz, train1_rz, train1_zw1, train1_zw2, train1_zw3, train1_rw1, train1_rw2,
                                train2_yz, train2_rz, train2_zw1, train2_zw2, train2_zw3, train2_rw1, train2_rw2,
                                total_yz,  total_ra,  total_zw1,  total_zw2,  total_zw3,  total_rw1,  total_rw2) as 
(select T1.T1_TrainID_1, T1.T1_TrainID_2, 
        pa12.A_CostYZ-pa11.A_CostYZ as train1_yz, pa12.A_CostRZ-pa11.A_CostRZ as train1_rz, pa12.A_CostYW1-pa11.A_CostYW1 as train1_yw1,
        pa12.A_CostYW2-pa11.A_CostYW2 as train1_yw2, pa12.A_CostYW3-pa11.A_CostYW3 as train1_yw3,
        pa12.A_CostRW1-pa11.A_CostRW1 as train1_rw1, pa12.A_CostRW2-pa11.A_CostRW2 as train1_rw2,

        pa22.A_CostYZ-pa21.A_CostYZ as train2_yz, pa22.A_CostRZ-pa21.A_CostRZ as train2_rz, pa22.A_CostYW1-pa21.A_CostYW1 as train2_yw1,
        pa22.A_CostYW2-pa21.A_CostYW2 as train2_yw2, pa22.A_CostYW3-pa21.A_CostYW3 as train2_yw3,
        pa22.A_CostRW1-pa21.A_CostRW1 as train2_rw1, pa22.A_CostRW2-pa21.A_CostRW2 as train2_rw2,

        pa12.A_CostYZ+pa22.A_CostYZ-pa11.A_CostYZ-pa21.A_CostYZ as total_yz,
        pa12.A_CostRZ+pa22.A_CostRZ-pa11.A_CostRZ-pa21.A_CostRZ as total_rz, pa12.A_CostYW1+pa22.A_CostYW1-pa11.A_CostYW1-pa21.A_CostYW1 as total_yw1,
        pa12.A_CostYW2+pa22.A_CostYW2-pa11.A_CostYW2-pa21.A_CostYW2 as total_yw2, pa12.A_CostYW3+pa22.A_CostYW3-pa11.A_CostYW3-pa21.A_CostYW3 as total_yw3,
        pa12.A_CostRW1+pa22.A_CostRW1-pa11.A_CostRW1-pa21.A_CostRW1 as total_rw1, pa12.A_CostRW2+pa22.A_CostRW2-pa11.A_CostRW2-pa21.A_CostRW2 as total_rw2

    from A_TrainId as pa11, station as st11, A_TrainId as pa12, station as st12, A_TrainId as pa21, station as st21, A_TrainId as pa22, station as st22, T1 
    where T1.T1_TrainId_1 = pa11.A_TrainId
        and T1.T1_TrainId_1 = pa12.A_TrainId
        and T1.T1_TrainId_2 = pa21.A_TrainId
        and T1.T1_TrainId_2 = pa22.A_TrainId
        and st11.S_name = '$from'
        and st12.S_name = T1.T1StationName_1
        and st21.S_name = T1.T1StationName_2
        and st22.S_name = '$to'
        and pa11.A_StationName = st11.S_name
        and pa12.A_StationName = st12.S_name
        and pa21.A_StationName = st21.S_name
        and pa22.A_StationName = st22.S_name
),

T3(T3_id,T3_go_StationName, T3_GoTime, T3_go_StationNum, T3_arrive_StationName, T3_ArriveTime, T3_arrive_StationNum) as (
    select T1.T1_TrainId_1, pa1.A_StationName, pa1.A_GoTime, pa1.A_StationNum, pa2.A_StationName, pa2.A_ArriveTime, pa2.A_StationNum
    from T1, A_TrainId as pa1, Station as st1, A_TrainId as pa2, Station as st2
    where pa1.A_TrainId = T1.T1_TrainId_1
        and pa2.A_TrainId = T1.T1_TrainId_1
        and st1.S_name = '$from'
        and st2.S_name = T1.T1StationName_1
        and pa1.A_StationName = st1.S_name
        and pa2.A_StationName = st2.S_name
),

T4(T4_id,T4_go_StationName, T4_GoTime, T4_go_StationNum, T4_arrive_StationName, T4_ArriveTime, T4_arrive_StationNum) as (
    select T1.T1_TrainId_2, pa1.A_StationName, pa1.A_GoTime, pa1.A_StationNum, pa2.A_StationName, pa2.A_ArriveTime, pa2.A_StationNum
    from T1, A_TrainId as pa1, Station as st1, A_TrainId as pa2, Station as st2
    where pa1.A_TrainId = T1.T1_TrainId_2
        and pa2.A_TrainId = T1.T1_TrainId_2
        and st1.S_name = T1.T1StationName_2
        and st2.S_name = '$to'
        and pa1.A_StationName = st1.S_name
        and pa2.A_StationName = st2.S_name
)

-- order by yz;
select DISTINCT 
T2.T2_TrainId_1, T3.T3_go_StationName, T3.T3_GoTime, T3.T3_arrive_StationName, T3.T4_ArriveTime, 
	T2.train1_yz, T2.train1_rz, T2.train1_zw1, T2.train1_zw2, T2.train1_zw3, T2.train1_rw1, T2.train1_rw2,
	
T2.T2_TrainId_2, T4.T4_go_StationName, T4.T4_GoTime, T4.T4_arrive_StationName, T4.T4_ArriveTime, 
	T2.train2_yz, T2.train2_rz, T2.train2_zw1, T2.train2_zw2, T2.train2_zw3, T2.train2_rw1, T2.train2_rw2,

T3.T3_go_StationNum, T3.T3_arrive_StationNum, T4.T4_go_StationNum, T4.T4_arrive_StationNum	

T2.total_yz, T2.total_rz, T2.total_zw1, T2.total_zw2, T2.total_zw3, T2.total_rw1, T2.total_rw2,

from T2, T3, T4
where T2.T2_TrainId_1 = T3.T3_id
    and T2.T2_TrainId_2 = T4.T4_id
    and T3.T3_GoTime > '$time'
order by T2.total_yz
;
EOF;
*/


// $query_train = <<<EOF
// with S1(S1_TrainId, S1_StationNum) as 
// (select A_TrainId.A_TrainId, A_TrainId.A_StationNum
//     from A_TrainId, Station
//     where A_TrainId.A_StationName = Station.S_Name
//         and Station.S_City = '$from'),

// S2(S2_TrainId,S2_StationNum) as
// (select A_TrainId.A_TrainId, A_TrainId.A_StationNum
//     from A_TrainId, Station
//     where A_TrainId.A_StationName = Station.S_Name
//         and Station.S_City = '$to'),

// S3(S3_TrainID_1, S3_TrainID_2, S3_Stationnum_1, S3_Stationnum_2, S3_StationName_1, S3_StationName_2) as
// (select pa1.A_TrainId, pa2.A_TrainId, pa1.A_StationNum, pa2.A_StationNum, pa1.A_StationName, pa2.A_StationName
//     from Staion as st1, A_TrainId as pa1, A_TrainId as pa2, Station as st2
//     where pa1.A_StationName = st1.S_name
//         and pa2.A_StationName = st2.S_name
//         and st1.S_city = st2.S_city
//         and pa1.A_TrainId <> pa2.A_TrainId
//         and pa2.A_GoTime > pa1.A_ArriveTime/*((st1.S_name=st2.S_name and (pa2.A_GoTime-pa1.A_ArriveTime)>='01:00:00' and (pa2.A_GoTime-pa1.A_ArriveTime)<='04:00:00')
//             or (st1.S_name<>st2.S_name and (pa2.A_GoTime-pa1.A_ArriveTime)>='02:00:00' and (pa2.A_GoTime-pa1.A_ArriveTime)<='04:00:00'))*/
// ),


// T1(T1_TrainID_1, T1_TrainID_2, T1_Stationnum_1, T1_Stationnum_2, T1_StationName_1, T1_StationName_2) as 
// (select S3.S3_TrainID_1, S3.S3_TrainID_2, S3.S3_Stationnum_1, S3.S3_Stationnum_2, S3.S3_StationName_1, S3.S3_StationName_2
//     from S1, S2, S3
//     where S1.S1_TrainID = S3.S3_TrainID_1
//         and S2.S2_TrainID = S3.S3_TrainID_2
//         and S1.S1_StationNum < S3.S3_Stationnum_1
//         and S2.S2_StationNum > S3.S3_Stationnum_2
// ),

// /*T2(T2_TrainID_1, T2_TrainID_2, train1_yz, train1_rz, train1_zw1, train1_zw2, train1_zw3, train1_rw1, train1_rw2,
//                                 train2_yz, train2_rz, train2_zw1, train2_zw2, train2_zw3, train2_rw1, train2_rw2,
//                                 total_yz,  total_ra,  total_zw1,  total_zw2,  total_zw3,  total_rw1,  total_rw2) as 
// (select T1.T1_TrainID_1, T1.T1_TrainID_2, 
//         pa12.A_CostYZ-pa11.A_CostYZ as train1_yz, pa12.A_CostRZ-pa11.A_CostRZ as train1_rz, pa12.A_CostYW1-pa11.A_CostYW1 as train1_yw1,
//         pa12.A_CostYW2-pa11.A_CostYW2 as train1_yw2, pa12.A_CostYW3-pa11.A_CostYW3 as train1_yw3,
//         pa12.A_CostRW1-pa11.A_CostRW1 as train1_rw1, pa12.A_CostRW2-pa11.A_CostRW2 as train1_rw2,

//         pa22.A_CostYZ-pa21.A_CostYZ as train2_yz, pa22.A_CostRZ-pa21.A_CostRZ as train2_rz, pa22.A_CostYW1-pa21.A_CostYW1 as train2_yw1,
//         pa22.A_CostYW2-pa21.A_CostYW2 as train2_yw2, pa22.A_CostYW3-pa21.A_CostYW3 as train2_yw3,
//         pa22.A_CostRW1-pa21.A_CostRW1 as train2_rw1, pa22.A_CostRW2-pa21.A_CostRW2 as train2_rw2,

//         pa12.A_CostYZ+pa22.A_CostYZ-pa11.A_CostYZ-pa21.A_CostYZ as total_yz,
//         pa12.A_CostRZ+pa22.A_CostRZ-pa11.A_CostRZ-pa21.A_CostRZ as total_rz, pa12.A_CostYW1+pa22.A_CostYW1-pa11.A_CostYW1-pa21.A_CostYW1 as total_yw1,
//         pa12.A_CostYW2+pa22.A_CostYW2-pa11.A_CostYW2-pa21.A_CostYW2 as total_yw2, pa12.A_CostYW3+pa22.A_CostYW3-pa11.A_CostYW3-pa21.A_CostYW3 as total_yw3,
//         pa12.A_CostRW1+pa22.A_CostRW1-pa11.A_CostRW1-pa21.A_CostRW1 as total_rw1, pa12.A_CostRW2+pa22.A_CostRW2-pa11.A_CostRW2-pa21.A_CostRW2 as total_rw2

//     from A_TrainId as pa11, station as st11, A_TrainId as pa12, station as st12, A_TrainId as pa21, station as st21, A_TrainId as pa22, station as st22, T1 
//     where T1.T1_TrainId_1 = pa11.A_TrainId
//         and T1.T1_TrainId_1 = pa12.A_TrainId
//         and T1.T1_TrainId_2 = pa21.A_TrainId
//         and T1.T1_TrainId_2 = pa22.A_TrainId
//         and st11.S_name = '$from'
//         and st12.S_name = T1.T1StationName_1
//         and st21.S_name = T1.T1StationName_2
//         and st22.S_name = '$to'
//         and pa11.A_StationName = st11.S_name
//         and pa12.A_StationName = st12.S_name
//         and pa21.A_StationName = st21.S_name
//         and pa22.A_StationName = st22.S_name
// ),*/

// T3(T3_id,T3_go_StationName, T3_GoTime, T3_go_StationNum, T3_arrive_StationName, T3_ArriveTime, T3_arrive_StationNum,
//            s1_yz, s1_rz, s1_yw1, s1_yw2, s1_yw3, s1_rw1, s1_rw2,
//            s2_yz, s2_rz, s2_yw1, s2_yw2, s2_yw3, s2_rw1, s2_rw2) as (
//     select T1.T1_TrainId_1, pa1.A_StationName, pa1.A_GoTime, pa1.A_StationNum, pa2.A_StationName, pa2.A_ArriveTime, pa2.A_StationNum,
//            pa1.A_CostYZ, pa1.A_CostRZ, pa1.P_moneyzw1, pa1.P_moneyzw2, pa1.P_moneyzw3, pa1.A_CostRW1, pa1.A_CostRW2,
//            pa2.A_CostYZ, pa2.A_CostRZ, pa2.P_moneyzw1, pa2.P_moneyzw2, pa2.P_moneyzw3, pa2.A_CostRW1, pa2.A_CostRW2
//     from T1, A_TrainId as pa1, A_TrainId as pa2
//     where pa1.A_TrainId = T1.T1_TrainId_1
//         and pa2.A_TrainId = T1.T1_TrainId_1
//         and pa1.A_StationName = '$from'
//         and pa2.A_StationName = T1.T1_StationName_1
// ),

// T4(T4_id,T4_go_StationName, T4_GoTime, T4_go_StationNum, T4_arrive_StationName, T4_ArriveTime, T4_arrive_StationNum,
//            s3_yz, s3_rz, s3_yw1, s3_yw2, s3_yw3, s3_rw1, s3_rw2,
//            s4_yz, s4_rz, s4_yw1, s4_yw2, s4_yw3, s4_rw1, s4_rw2) as (
//     select T1.T1_TrainId_2, pa1.A_StationName, pa1.A_GoTime, pa1.A_StationNum, pa2.A_StationName, pa2.A_ArriveTime, pa2.A_StationNum,
//            pa1.A_CostYZ, pa1.A_CostRZ, pa1.P_moneyzw1, pa1.P_moneyzw2, pa1.P_moneyzw3, pa1.A_CostRW1, pa1.A_CostRW2,
//            pa2.A_CostYZ, pa2.A_CostRZ, pa2.P_moneyzw1, pa2.P_moneyzw2, pa2.P_moneyzw3, pa2.A_CostRW1, pa2.A_CostRW2
//     from T1, A_TrainId as pa1, A_TrainId as pa2
//     where pa1.A_TrainId = T1.T1_TrainId_2
//         and pa2.A_TrainId = T1.T1_TrainId_2
//         and pa1.A_StationName = T1.T1_StationName_2
//         and pa2.A_StationName = '$to'
// )

// -- order by yz;
// select DISTINCT 
// T1.T1_TrainId_1, T3.T3_go_StationName, T3.T3_GoTime, T3.T3_arrive_StationName, T3.T4_ArriveTime, 

// T1.T1_TrainId_2, T4.T4_go_StationName, T4.T4_GoTime, T4.T4_arrive_StationName, T4.T4_ArriveTime, 

// T3.s1_yz, T3.s1_rz, T3.s1_yw1, T3.s1_yw2, T3.s1_yw3, T3.s1_rw1, T3.s1_rw2,
// T3.s2_yz, T3.s2_rz, T3.s2_yw1, T3.s2_yw2, T3.s2_yw3, T3.s2_rw1, T3.s2_rw2,
// T4.s3_yz, T4.s3_rz, T4.s3_yw1, T4.s3_yw2, T4.s3_yw3, T4.s3_rw1, T4.s3_rw2,
// T4.s4_yz, T4.s4_rz, T4.s4_yw1, T4.s4_yw2, T4.s4_yw3, T4.s4_rw1, T4.s4_rw2

// /*T3.T3_go_StationNum, T3.T3_arrive_StationNum, T4.T4_go_StationNum, T4.T4_arrive_StationNum*/
// from T1, T3, T4
// where T1.T1_TrainId_1 = T3.T3_id
//     and T1.T1_TrainId_2 = T4.T4_id
//     and T3.T3_GoTime > '$time'
// ;
// EOF;

$query_train = <<<EOF
with S1(S1_TrainId, S1_StationNum) as 
(select A_TrainId.A_TrainId, A_TrainId.A_StationNum
    from A_TrainId, Station
    where A_TrainId.A_StationName = Station.S_Name
        and Station.S_City = '$from'),

S2(S2_TrainId,S2_StationNum) as
(select A_TrainId.A_TrainId, A_TrainId.A_StationNum
    from A_TrainId, Station
    where A_TrainId.A_StationName = Station.S_Name
        and Station.S_City = '$to'),

S3(S3_TrainID_1, S3_TrainID_2, S3_Stationnum_1, S3_Stationnum_2, S3_StationName_1, S3_StationName_2) as
(select pa1.A_TrainId, pa2.A_TrainId, pa1.A_StationNum, pa2.A_StationNum, pa1.A_StationName, pa2.A_StationName
    from Staion as st1, A_TrainId as pa1, A_TrainId as pa2, Station as st2
    where pa1.A_StationName = st1.S_name
        and pa2.A_StationName = st2.S_name
        and st1.S_city = st2.S_city
        and pa1.A_TrainId <> pa2.A_TrainId
        /*and pa2.A_GoTime > pa1.A_ArriveTime*//*((st1.S_name=st2.S_name and (pa2.A_GoTime-pa1.A_ArriveTime)>='01:00:00' and (pa2.A_GoTime-pa1.A_ArriveTime)<='04:00:00')
            or (st1.S_name<>st2.S_name and (pa2.A_GoTime-pa1.A_ArriveTime)>='02:00:00' and (pa2.A_GoTime-pa1.A_ArriveTime)<='04:00:00'))*/
),


T1(T1_TrainID_1, T1_TrainID_2, T1_Stationnum_1, T1_Stationnum_2, T1_StationName_1, T1_StationName_2) as 
(select S3.S3_TrainID_1, S3.S3_TrainID_2, S3.S3_Stationnum_1, S3.S3_Stationnum_2, S3.S3_StationName_1, S3.S3_StationName_2
    from S1, S2, S3
    where S1.S1_TrainID = S3.S3_TrainID_1
        and S2.S2_TrainID = S3.S3_TrainID_2
        and S1.S1_StationNum < S3.S3_Stationnum_1
        and S2.S2_StationNum > S3.S3_Stationnum_2
),

/*T2(T2_TrainID_1, T2_TrainID_2, train1_yz, train1_rz, train1_zw1, train1_zw2, train1_zw3, train1_rw1, train1_rw2,
                                train2_yz, train2_rz, train2_zw1, train2_zw2, train2_zw3, train2_rw1, train2_rw2,
                                total_yz,  total_ra,  total_zw1,  total_zw2,  total_zw3,  total_rw1,  total_rw2) as 
(select T1.T1_TrainID_1, T1.T1_TrainID_2, 
        pa12.A_CostYZ-pa11.A_CostYZ as train1_yz, pa12.A_CostRZ-pa11.A_CostRZ as train1_rz, pa12.A_CostYW1-pa11.A_CostYW1 as train1_yw1,
        pa12.A_CostYW2-pa11.A_CostYW2 as train1_yw2, pa12.A_CostYW3-pa11.A_CostYW3 as train1_yw3,
        pa12.A_CostRW1-pa11.A_CostRW1 as train1_rw1, pa12.A_CostRW2-pa11.A_CostRW2 as train1_rw2,

        pa22.A_CostYZ-pa21.A_CostYZ as train2_yz, pa22.A_CostRZ-pa21.A_CostRZ as train2_rz, pa22.A_CostYW1-pa21.A_CostYW1 as train2_yw1,
        pa22.A_CostYW2-pa21.A_CostYW2 as train2_yw2, pa22.A_CostYW3-pa21.A_CostYW3 as train2_yw3,
        pa22.A_CostRW1-pa21.A_CostRW1 as train2_rw1, pa22.A_CostRW2-pa21.A_CostRW2 as train2_rw2,

        pa12.A_CostYZ+pa22.A_CostYZ-pa11.A_CostYZ-pa21.A_CostYZ as total_yz,
        pa12.A_CostRZ+pa22.A_CostRZ-pa11.A_CostRZ-pa21.A_CostRZ as total_rz, pa12.A_CostYW1+pa22.A_CostYW1-pa11.A_CostYW1-pa21.A_CostYW1 as total_yw1,
        pa12.A_CostYW2+pa22.A_CostYW2-pa11.A_CostYW2-pa21.A_CostYW2 as total_yw2, pa12.A_CostYW3+pa22.A_CostYW3-pa11.A_CostYW3-pa21.A_CostYW3 as total_yw3,
        pa12.A_CostRW1+pa22.A_CostRW1-pa11.A_CostRW1-pa21.A_CostRW1 as total_rw1, pa12.A_CostRW2+pa22.A_CostRW2-pa11.A_CostRW2-pa21.A_CostRW2 as total_rw2

    from A_TrainId as pa11, station as st11, A_TrainId as pa12, station as st12, A_TrainId as pa21, station as st21, A_TrainId as pa22, station as st22, T1 
    where T1.T1_TrainId_1 = pa11.A_TrainId
        and T1.T1_TrainId_1 = pa12.A_TrainId
        and T1.T1_TrainId_2 = pa21.A_TrainId
        and T1.T1_TrainId_2 = pa22.A_TrainId
        and st11.S_name = '$from'
        and st12.S_name = T1.T1StationName_1
        and st21.S_name = T1.T1StationName_2
        and st22.S_name = '$to'
        and pa11.A_StationName = st11.S_name
        and pa12.A_StationName = st12.S_name
        and pa21.A_StationName = st21.S_name
        and pa22.A_StationName = st22.S_name
),*/

T3(T3_id,T3_go_StationName, T3_GoTime, T3_go_StationNum, T3_arrive_StationName, T3_ArriveTime, T3_arrive_StationNum/*,
           s1_yz, s1_rz, s1_yw1, s1_yw2, s1_yw3, s1_rw1, s1_rw2,
           s2_yz, s2_rz, s2_yw1, s2_yw2, s2_yw3, s2_rw1, s2_rw2*/) as (
    select T1.T1_TrainId_1, pa1.A_StationName, pa1.A_GoTime, pa1.A_StationNum, pa2.A_StationName, pa2.A_ArriveTime, pa2.A_StationNum/*,
           pa1.A_CostYZ, pa1.A_CostRZ, pa1.P_moneyzw1, pa1.P_moneyzw2, pa1.P_moneyzw3, pa1.A_CostRW1, pa1.A_CostRW2,
           pa2.A_CostYZ, pa2.A_CostRZ, pa2.P_moneyzw1, pa2.P_moneyzw2, pa2.P_moneyzw3, pa2.A_CostRW1, pa2.A_CostRW2*/
    from T1, A_TrainId as pa1, A_TrainId as pa2
    where pa1.A_TrainId = T1.T1_TrainId_1
        and pa2.A_TrainId = T1.T1_TrainId_1
        and pa1.A_StationName = '$from'
        and pa2.A_StationName = T1.T1_StationName_1
),

T4(T4_id,T4_go_StationName, T4_GoTime, T4_go_StationNum, T4_arrive_StationName, T4_ArriveTime, T4_arrive_StationNum/*,
           s3_yz, s3_rz, s3_yw1, s3_yw2, s3_yw3, s3_rw1, s3_rw2,
           s4_yz, s4_rz, s4_yw1, s4_yw2, s4_yw3, s4_rw1, s4_rw2*/) as (
    select T1.T1_TrainId_2, pa1.A_StationName, pa1.A_GoTime, pa1.A_StationNum, pa2.A_StationName, pa2.A_ArriveTime, pa2.A_StationNum/*,
           pa1.A_CostYZ, pa1.A_CostRZ, pa1.P_moneyzw1, pa1.P_moneyzw2, pa1.P_moneyzw3, pa1.A_CostRW1, pa1.A_CostRW2,
           pa2.A_CostYZ, pa2.A_CostRZ, pa2.P_moneyzw1, pa2.P_moneyzw2, pa2.P_moneyzw3, pa2.A_CostRW1, pa2.A_CostRW2*/
    from T1, A_TrainId as pa1, A_TrainId as pa2
    where pa1.A_TrainId = T1.T1_TrainId_2
        and pa2.A_TrainId = T1.T1_TrainId_2
        and pa1.A_StationName = T1.T1_StationName_2
        and pa2.A_StationName = '$to'
)

-- order by yz;
select DISTINCT 
T1.T1_TrainId_1, T3.T3_go_StationName, T3.T3_GoTime, T3.T3_arrive_StationName, T3.T4_ArriveTime, 

T1.T1_TrainId_2, T4.T4_go_StationName, T4.T4_GoTime, T4.T4_arrive_StationName, T4.T4_ArriveTime/*, 

T3.s1_yz, T3.s1_rz, T3.s1_yw1, T3.s1_yw2, T3.s1_yw3, T3.s1_rw1, T3.s1_rw2,
T3.s2_yz, T3.s2_rz, T3.s2_yw1, T3.s2_yw2, T3.s2_yw3, T3.s2_rw1, T3.s2_rw2,
T4.s3_yz, T4.s3_rz, T4.s3_yw1, T4.s3_yw2, T4.s3_yw3, T4.s3_rw1, T4.s3_rw2,
T4.s4_yz, T4.s4_rz, T4.s4_yw1, T4.s4_yw2, T4.s4_yw3, T4.s4_rw1, T4.s4_rw2*/

/*T3.T3_go_StationNum, T3.T3_arrive_StationNum, T4.T4_go_StationNum, T4.T4_arrive_StationNum*/
from T1, T3, T4
where T1.T1_TrainId_1 = T3.T3_id
    and T1.T1_TrainId_2 = T4.T4_id
    and T3.T3_GoTime > '$time'
;
EOF;


$ret_train = pg_query($conn, $query_train);
if (!$ret_train){
    echo "<br>";
    echo "非常抱歉，换乘方案查询失败，请选择直达方案";
    echo "<br>";
    echo "<br>";
}

$row_num = pg_num_rows($ret_train);
//echo "row_num = $row_num  ";

echo "同城换乘一次方案和余票信息如下：<br>
      <table border=\"4\">
      <tr>
      <td>方案</td> 
      <td>车次</td> 
      <td>出发站</td> 
      <td>出发时间</td> 
      <td>到达站</td> 
      <td>到达时间</td>
      <td>硬座</td> 
      <td>软座</td>  
      <td>硬卧上铺</td>
      <td>硬卧中铺</td> 
      <td>硬卧下铺</td> 
      <td>软卧上铺</td> 
      <td>软卧下铺</td>
      </tr>";


for ($x = 0, $z = 1; $x < min($row_num, 20); $x++){

	if($x%2 == 0){
		$a_row = pg_fetch_row($ret_train);
		echo "<tr>";
		echo "<td> $z </td>";
		$z++;
		for ($y = 0; $y < 12; $y++){	
            //echo "<td>$a_row[$y]</td>";
            echo "<td>'$a_row[$y]'</td>";
		}
	}
	else{
		$a_row = pg_fetch_row($ret_train);
		echo "<tr>";
		echo "<td>   </td>";
		for ($y = 0; $y < 12; $y++){	
			echo "<td>$a_row[$y]</td>";
		}		
	}
	
    echo "</tr>";
	//获取站序

$hastype = array($a_row[5], $a_row[6], $a_row[7],
 $a_row[8], $a_row[9], $a_row[10], $a_row[11]);
$get_booked_ticket = <<<EOF
				with T1(T1_Type, T1_SeatNum) as
				(SELECT S_Type, S_SeatNum
				 FROM Seats 
				 WHERE S_TrainId = '$a_row[0]'
					AND S_PStationNum >= $a_row[12]
					AND S_PStationNum < $a_row[13]
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
	for ($i = 0; $i <7; $i = $i + 1){
		if ($row[0] == $all_type[$i])
			$left_num[$i] = 5 - $row[1];
	}
}

echo "<tr>";
echo "<td> </td>";
echo "<td> </td>";
echo "<td> </td>";
echo "<td> </td>";
echo "<td> </td>";
echo "<td> </td>";
for ($i = 0; $i <7; $i = $i + 1){
	if ($left_num[$i] == -1)
		echo "<td> - </td>";
	elseif( $left_num[$i] == 0 )
		echo "<td>0</td>";
	else{
		//$k = $i + 5;
		//echo $price[$k] . "   ";
		echo "<td><a href=\"booking.php?trainid=$a_row[0]&date=$thedate&type=$all_type[$i]&price=$hastype[$i]&fromstation=$a_row[12]&tostation=$a_row[13]\">$left_num[$i]</a></td>";

	}
}
echo "</tr>";
    // left tickets
    // $a_row[0] is $train_id
    // $from
    // $to
    // $date
}
echo "</table>";


?>
</center>
</body>
</html>
