<?php

$totalm = exec('/usr/bin/free -tmo | /usr/bin/awk \'{print $1","$2","$3-$6-$7","$4+$6+$7}\' |grep -i mem: |cut -f2 -d,');

$usedm = exec('/usr/bin/free -tmo | /usr/bin/awk \'{print $1","$2","$3-$6-$7","$4+$6+$7}\' |grep -i mem: |cut -f3 -d,');


if($totalm >= 1024) {

	echo round($usedm / '1024',2) ." / ". round($totalm / '1024',2) . " GB"; 
	}

else {
		echo $usedm." / ".$totalm. " MB";
		}
	
$perc = round(($usedm*100)/$totalm ,1);
$umem = $perc;	
	
if($umem > 90) { $corm = "progress-bar-danger"; }

if($umem >= 60 and $umem <= 90) { $corm = "progress-bar-warning"; } 

if($umem > 51 and $umem < 60) { $corm = "progress-bar"; }

if($umem > 0 and $umem <= 50) { $corm = "progress-bar-success"; }	