<?php

include __DIR__ . '/vendor/autoload.php';


use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

ini_set('memory_limit', '-1');

$samples = $labels = [];
foreach (glob('validate/*.jpg') as $file) {
    $samples[] = [imagecreatefromjpeg($file)];
    $labels[] = preg_replace('/(.*).jpg/', '$1', basename($file));
}
$dataset = new Labeled($samples, $labels);
$estimator = PersistentModel::load(new Filesystem('cifar10.rbx'));
$predictions = $estimator->predict($dataset);


echo "<pre>";
print_r($labels);
print_r($predictions);
echo "</pre>";

exit;
