<?php
include __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\CrossValidation\Reports\AggregateReport;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;


$sendCom = true; // send coms to server

$loop = 0;

$server = 'PUBLIC_SERVER/';

while(true) // never ending loop
{

    $loop++;
    echo "loop:".$loop.":\n";
    $command = 'stop';

    // download image
    if ($file = @file_get_contents($server.'img.jpg'))
    {
        file_put_contents('test/test.jpg',$file);

        $im = imagecreatefromjpeg('test/test.jpg');
        $im = imagecrop($im, ['x' => 100, 'y' => 125, 'width' => 230, 'height' => 230]);
        $im = imagerotate($im, -90, 0);
        imagefilter($im, IMG_FILTER_GRAYSCALE);

        $temp = imagecreatetruecolor(32, 32);
        imagecopyresampled($temp, $im, 0, 0, 0, 0, 32, 32, 230, 230);

        imagejpeg($temp, 'test/test.jpg');

        // code exe time
        $start = microtime(true);

        // get test images (usually only one) a make predictions
        $samples = $labels = [];
        foreach (glob('test/*.jpg') as $file) {
            $samples[] = [imagecreatefromjpeg($file)];
            $labels[] = preg_replace('/(.*).jpg/', '$1', basename($file));
        }
        $dataset = new Labeled($samples, $labels);
        $estimator = PersistentModel::load(new Filesystem('cifar10.rbx')); // trained model
        $predictions = $estimator->predict($dataset);
        $report = new AggregateReport([
            new MulticlassBreakdown(),
            new ConfusionMatrix(),
        ]);

        // code exe time
        $end = microtime(true);
        $time = $end - $start;
        echo "time:".$time."\n";
        echo "command:".$predictions[0]."\n";

        imagejpeg($im, 'real_life/'.date('dmY_His').'_'.$predictions[0].'.jpg'); // saving prediction

        imagedestroy($temp);
        imagedestroy($im);

        // send command
        sendCommand($predictions[0]);

        if ($predictions[0] == 'backward')
        {
            sleep(3); // sleep is for robot to give him some time to do command
            // stop
            sendCommand('stop');
        }

        if ($predictions[0] == 'forward')
        {
            sleep(2);
        }

        if ($predictions[0] == 'left' OR $predictions[0] == 'right')
        {
            sleep(1);
            // stop
            sendCommand('stop');
            sleep(1);
        }

        sendCommand('stop');

        sleep(2);
    }
    else
    {
        echo "no image\n";
        sleep(10);
    }

}


function sendCommand($com)
{
    global $sendCom, $server;

    if ($sendCom)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server."action.php?go=".$com);
        curl_exec($ch);
        curl_close($ch);
    }

    echo "go:".$com."\n";
}





