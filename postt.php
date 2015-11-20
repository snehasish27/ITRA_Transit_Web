<?php
ini_set('max_execution_time', 0);
$connstr="host=localhost dbname=postgres user=postgres password=1234";
//$connection=pg_connect($connstr);
$fl=0;
if ($connection=pg_connect($connstr))
{
  // echo "hello!!";
}

$srclat=$_POST['sourcelat'];
$srclong=$_POST['sourcelong'];
$destlat=$_POST['destlat'];
$destlong=$_POST['destlong'];
$route=3;
//echo $sourcelat." ".$sourcelong." ".$destlat." ".$destlong;
while ($route > 0) {
        //echo $route . " ";
        //select destination busstop
        $qdst = "SELECT lat,lng,route_id,direction,bus_stop_id from bus_stop_heads WHERE route_id=$route and 
       ST_Distance(ST_GeographyFromText('POINT($destlong $destlat)'),location)<300
       ORDER BY ST_Distance(ST_GeographyFromText('POINT($destlong $destlat)'),location)
       LIMIT 1";
        $qt   = pg_query($connection, $qdst);
        //echo pg_num_rows($qt);
        //var_dump($qt);
        if(pg_num_rows($qt)==0)
        {
            $fl++;
            //echo $fl;
            $route-=1;
            continue;
        }
        $qtt  = pg_fetch_row($qt);
        //echo "kkllllllllllllllllllll".$qtt;
        /*
           */
        
        //echo $qtt[0]."    ";
        //to check if a point lies in threshhold range
        if ($qtt) {
            
            echo "dest," . $qtt[0] . "," . $qtt[1] . "," . $qtt[2] . "," . $qtt[3] . "," . $qtt[4] . "\n";
            
            //select source busstop
            $qsrc = "SELECT lat,lng,route_id,direction,bus_stop_id from bus_stop_heads WHERE route_id=$route
       AND direction='$qtt[3]' and 
       ST_Distance(ST_GeographyFromText('POINT($srclong $srclat)'),location)<1000
       ORDER BY ST_Distance(ST_GeographyFromText('POINT($srclong $srclat)'),location)
       LIMIT 1";
            $qt2  = pg_query($connection, $qsrc);
            $qtt2 = pg_fetch_row($qt2);
            echo "source," . $qtt2[0] . "," . $qtt2[1] . "," . $qtt2[2] . "," . $qtt2[3] . "," . $qtt2[4] . "\n";
            
            
            //direction check
            if ($qtt[4] < $qtt2[4]) {
                echo "opposite direction\n";
                $qdst = "SELECT lat,lng,route_id,direction,bus_stop_id from bus_stop_heads WHERE route_id=$route and direction!='$qtt[3]' and 
       ST_Distance(ST_GeographyFromText('POINT($destlong $destlat)'),location)<300
       ORDER BY ST_Distance(ST_GeographyFromText('POINT($destlong $destlat)'),location)
       LIMIT 1";
                $qt   = pg_query($connection, $qdst);
                $qtt  = pg_fetch_row($qt);
                echo "dest," . $qtt[0] . "," . $qtt[1] . "," . $qtt[2] . "," . $qtt[3] . "," . $qtt[4] . "\n";
                
                $qsrc = "SELECT lat,lng,route_id,direction,bus_stop_id from bus_stop_heads WHERE route_id=$route AND direction='$qtt[3]' and 
       ST_Distance(ST_GeographyFromText('POINT($srclong $srclat)'),location)<1000
     ORDER BY ST_Distance(ST_GeographyFromText('POINT($srclong $srclat)'),location)
       LIMIT 1";
                $qt2  = pg_query($connection, $qsrc);
                $qtt2 = pg_fetch_row($qt2);
                echo " source," . $qtt2[0] . "," . $qtt2[1] . "," . $qtt2[2] . "," . $qtt2[3] . "," . $qtt2[4] . "\n";
            }
            
            
    echo "ll\n";        
            
            
            
            //selecting intermediate busstops
            //echo "busstop" . "\n";
          /*  $queryt = "SELECT ST_AsText(bus_stop_heads.location),wait_time.whole_day,wait_time.early_morning, wait_time.morning,
wait_time.noon,wait_time.evening,wait_time.night from bus_stop_heads JOIN wait_time ON wait_time.route_id=bus_stop_heads.route_id AND 
wait_time.bus_stop_id=bus_stop_heads.bus_stop_id AND wait_time.direction=bus_stop_heads.direction
 WHERE bus_stop_heads.route_id=$route AND bus_stop_heads.direction='$qtt[3]' AND (bus_stop_heads.bus_stop_id>$qtt2[4]-1 AND bus_stop_heads.bus_stop_id<$qtt[4]+1)";*/
            $queryt="SELECT bus_stop_heads.lat,bus_stop_heads.lng,wait_time.whole_day,wait_time.early_morning, wait_time.morning,
wait_time.noon,wait_time.evening,wait_time.night,distance_spatial_spread.spatial_spread,distance_spatial_spread.distance,travel_time.whole_day,bus_stop_heads.route_id,bus_stop_heads.bus_stop_id,trail_participation.whole_day,trail_participation.early_morning,trail_participation.morning,trail_participation.noon,trail_participation.evening,trail_participation.night 
 from bus_stop_heads

JOIN wait_time ON
 wait_time.route_id=bus_stop_heads.route_id AND 
wait_time.bus_stop_id=bus_stop_heads.bus_stop_id AND 
wait_time.direction=bus_stop_heads.direction

JOIN distance_spatial_spread ON
distance_spatial_spread.route_id=bus_stop_heads.route_id AND
distance_spatial_spread.bus_stop_id=bus_stop_heads.bus_stop_id AND 
distance_spatial_spread.direction=bus_stop_heads.direction

JOIN travel_time ON
travel_time.route_id=bus_stop_heads.route_id AND
travel_time.bus_stop_id=bus_stop_heads.bus_stop_id AND 
travel_time.direction=bus_stop_heads.direction

JOIN trail_participation ON
trail_participation.route_id=bus_stop_heads.route_id AND
trail_participation.bus_stop_id=bus_stop_heads.bus_stop_id AND 
trail_participation.direction=bus_stop_heads.direction

 WHERE bus_stop_heads.route_id=$route AND 
bus_stop_heads.direction='$qtt[3]' AND 
(bus_stop_heads.bus_stop_id>$qtt2[4]-1 AND bus_stop_heads.bus_stop_id<$qtt[4]+1)";
            
    $qtb = pg_query($connection, $queryt);
            while ($qttb = pg_fetch_row($qtb)) {
                echo $qttb[0] . "," . $qttb[1] . "," . $qttb[2] . "," . $qttb[3] . "," . $qttb[4] . "," . $qttb[5] . "," . $qttb[6]. "," . $qttb[7]. "," . $qttb[8]. "," . $qttb[9]. "," . $qttb[10]. "," . $qttb[11]. "," . $qttb[12]. "," . $qttb[13]. "," . $qttb[14]. "," . $qttb[15]. "," . $qttb[16]. "," . $qttb[17]. "," . $qttb[18];
                echo "\n";
            }
            echo "lat\n";
            

            
            
            //trail plot
            
     $qns    = "SELECT slno FROM gps_trace WHERE (route_id=$route AND direction='$qtt2[3]' AND trail_id=10) ORDER BY ST_Distance(ST_GeographyFromText('POINT($qtt2[1] $qtt2[0])'),location_data) LIMIT 1";
            $qnspq  = pg_query($connection, $qns);
            $qnspfr = pg_fetch_row($qnspq);
            //echo $qnspfr[0].",";
    $qnd    = "SELECT slno FROM gps_trace WHERE (route_id=$route AND direction='$qtt2[3]' AND trail_id=10) ORDER BY ST_Distance(ST_GeographyFromText('POINT($qtt[1] $qtt[0])'),location_data) LIMIT 1";
            $qndpq  = pg_query($connection, $qnd);
            $qndpfr = pg_fetch_row($qndpq);
            //echo $qndpfr[0];
            //echo "trails" . "\n";
            
      $queryt = "SELECT lat,lng,route_id,slno from gps_trace WHERE (route_id=$route AND trail_id=10 AND direction='$qtt2[3]' AND (slno>$qnspfr[0] AND slno<$qndpfr[0]))
          ORDER BY slno";
            $qt     = pg_query($connection, $queryt);
            $count=0;
            while ($qtt = pg_fetch_row($qt)) {
                $color=$route;
                if($count<=20){
                    echo $qtt[0] . "," . $qtt[1] . "," . $qtt[2]. "," . $qtt[3]. "," .$color."\n";
                    $count++;
                }
                else
                {
                    if($count<=40)
                        $count++;
                    else
                        $count=0;
                }
            }
                
            echo "next_route\n";
        }
        $route = $route - 1;
        $qtt=0;
    }
//echo $fl;
    if($fl==3)
    {
        echo "nope";
    }
//echo "pp";

        
   