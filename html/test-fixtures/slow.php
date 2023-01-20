<?php
$timeToWait = $_GET['timeToWait'] ?? 0;
sleep($timeToWait);
echo "waited $timeToWait seconds";
