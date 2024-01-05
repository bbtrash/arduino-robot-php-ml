<?php
/**
 * Crop and rotate raw image from robot
 */
// check current or use history
if (is_file('img.jpg'))
{
    $im = imagecreatefromjpeg('img.jpg');
}
else
{
    $im = imagecreatefromjpeg('history/img_1.jpg');
}

$im = imagecrop($im, ['x' => 0, 'y' => 0, 'width' => 430, 'height' => 480]);
$im = imagerotate($im, -90, 0);
imagejpeg($im);


