<?php

// history of 10 images
if (is_file('img.jpg') AND isset($_FILES["imageFile"]["tmp_name"]))
{
  unlink('history/img_10.jpg');
  for($i=9;$i>=1;$i--)
  {
      if (is_file("history/img_".$i.".jpg"))
      {
          rename("history/img_".$i.".jpg", "history/img_".($i+1).".jpg");
      }
  }
  rename("img.jpg", "history/img_1.jpg");
}

// save new image
move_uploaded_file($_FILES["imageFile"]["tmp_name"], "img.jpg");


// save get
$json = [
    'date' => date('d.m.Y H:i:s'),
    'batt' => $_GET['batt'],
    'charging' => $_GET['charging'],
];

// save data from robot to file 
$fp = fopen('status.dat', 'w');
fwrite($fp, json_encode($json));
fclose($fp);


echo "|";

$cmd = file_get_contents('action.dat'); // actual command is in this file

// just playing with servo speed and accel
$speed = 40; // 0 - 90 = 0 - 100%
$acceleration = 10;
$speed = $speed * 0.9;
$speedLeft = 0;
$speedRight = 0;


switch($cmd)
{
    case 'forward':
        $speedRight = 90-$speed+16; // right servo is weaker
        $speedLeft = 90+$speed;
    break;
    case 'left':
        $speedRight = 90-$speed;
        $speedLeft = 90-$speed;
    break;
    case 'right':
        $speedRight = 90+$speed;
        $speedLeft = 90+$speed;
    break;
    case 'backward':
        $speedRight = 90+$speed-10;
        $speedLeft = 90-$speed;
    break;
}

// response for robot
$bodyJson = [
    'command' => $cmd,
    'speedRight' => round($speedRight),
    'speedLeft' => round($speedLeft),
    'delay' => 10000,
    'acceleration' => $acceleration
];

echo json_encode($bodyJson);

echo "|";
exit;



