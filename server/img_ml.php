<?php
/**
 * Grayscale , crop and rotate raw image from robot for use in ML preview
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
$im = imagecrop($im, ['x' => 100, 'y' => 125, 'width' => 230, 'height' => 230]);
$im = imagerotate($im, -90, 0);
imagefilter($im, IMG_FILTER_GRAYSCALE);
$temp = imagecreatetruecolor(32, 32);
imagecopyresampled($temp, $im, 0, 0, 0, 0, 32, 32, 230, 230);
imagejpeg($temp);
