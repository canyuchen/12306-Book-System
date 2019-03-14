
create table Station
(
    S_Name          varchar(20) primary key,
    S_City          varchar(20) not null
);

-- Change owner to be import into database
-- $ sudo chown -R postgres train-2016-10

copy Station
-- from './all-stations-out.txt'
from '/tmp/all-stations-out.txt'
with (FORMAT csv);

-- Test if import success
select S_Name, S_City
from Station
where S_City = '上海';


create table A_TrainId
(
    A_TrainId       varchar(6) not null,
    A_StationName   varchar(20) not null,
    A_StationNum    integer not null,
    A_ArriveTime    time,
    A_GoTime        time,
    A_CostYZ       float,
    A_CostRZ       float,
    A_CostYW1      float,
    A_CostYW2      float,
    A_CostYW3      float,
    A_CostRW1      float,
    A_CostRW2      float,
    primary key (A_TrainId, A_StationNum),
    foreign key (A_StationName) references Station(S_Name)
);

copy A_TrainId
-- from './all-all.csv'
from '/tmp/all-all.csv'
with (FORMAT csv);

-- dbmslab2=# copy A_TrainId
-- dbmslab2-# from '/home/dingshizhe/Documents/db_/dbms-lab2/train-2016-10/tmp/pass_by_all_data/all-all-tmp.csv'
-- dbmslab2-# with (FORMAT csv);
-- COPY 54742



-- Test if import success

select A_TrainId, A_StationName, A_StationNum
from A_TrainId
where A_StationName = '北京';

select Station.S_City, A_TrainId.A_TrainId
from A_TrainId, Station
where A_TrainId.A_StationName = Station.S_Name
    and Station.S_City = '北京';

select A_TrainId.A_TrainId
from A_TrainId, Station
where A_TrainId.A_StationName = Station.S_Name
    and Station.S_City = '北京'
intersect
select A_TrainId.A_TrainId
from A_TrainId, Station
where A_TrainId.A_StationName = Station.S_Name
    and Station.S_City = '苏州';

select *
from A_TrainId
where A_TrainId = 'G107';


create table UserInfo
(
    User_Id         char(18) primary key,
    U_Name          varchar(20) not null,
    U_Phone         char(11) not null,
    U_UName         varchar(20) not null,
    U_CreditCardId  char(16) not null,
    U_Password      char(16) not null
);

-- create type status_type as enum (
--     'normal', 'cancelled', 'past'
-- );

-- create type seaS_Type as enum (
--     'YZ', 'RZ', 'YW1', 'YW2', 'YW3', 'RW1', 'RW2'
-- );

create table Book
(
    B_Id            SERIAL primary key,
    B_UserId        char(18) not null,
    B_TrainId       varchar(6) not null,
    B_Date          date not null,
    B_StationNum1   integer not null,
    B_StationNum2   integer not null,
    B_SType         seaS_Type not null,
    B_Money         integer not null,
    B_Status        status_type not null,
    foreign key (B_TrainId, B_StationNum1) references A_TrainId(A_TrainId, A_StationNum),
    foreign key (B_UserId) references UserInfo(User_Id)
);

create table Seats
(
    S_TrainId      varchar(6) not null,
    S_PStationNum  integer not null,
    S_Type         seaS_Type not null,
    S_Date         date not null,
    S_SeatNum      integer not null,
    primary key (S_TrainId, S_PStationNum, S_Type, S_Date),
    foreign key (S_TrainId, S_PStationNum) references A_TrainId(A_TrainId, A_StationNum)
);


