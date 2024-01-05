<?php

$folder = 'data_labels';


//delete
if (isset($_GET['delete']))
{
    unlink($folder.'/'.$_GET['delete']);
}

// to data_labels
if (isset($_GET['to_data_labels']) AND isset($_GET['label']))
{

    $im = imagecreatefromjpeg($folder.'/'.$_GET['to_data_labels']);
    $temp = imagecreatetruecolor(32, 32);
    imagecopyresampled($temp, $im, 0, 0, 0, 0, 32, 32, 230, 230);

    imagejpeg($temp, 'data_labels/'.time().'_'.$_GET['label'].'.jpg');

}

// scandir
$files = scandir($folder);
$files = array_diff($files, array('.', '..'));


// display html + delete
foreach($files as $file)
{
    echo "<div style='display:inline-block;float:left;text-align: center;margin:5px'>";
    echo "<img src='".$folder."/".$file."' style='width: 230px; height: 230px;margin:auto'><br />";
    echo $file."<br>";

    echo "<a href='gallery.php?to_data_labels=".$file."&label=forward'>forward</a><br>";
    echo "<a href='gallery.php?to_data_labels=".$file."&label=left'>left</a> | ";
    echo "<a href='gallery.php?delete=".$file."'>delete</a> | ";
    echo "<a href='gallery.php?to_data_labels=".$file."&label=right'>right</a><br>";
    echo "<a href='gallery.php?to_data_labels=".$file."&label=backward'>backward</a><br>";



    echo "<br>";
    echo "</div>";
}



