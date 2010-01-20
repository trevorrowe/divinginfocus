<?php

## combine Pippa.php

$dir = realpath(dirname(__FILE__));

$combined = array();
$combined [] = "<?php\n";
$combined [] = "\n";
$combined [] = "namespace Pippa;\n";

foreach(glob("$dir/src/classes/*.php") as $file) {
  foreach(file($file) as $n => $line) {
    if($n < 3)
      continue;
    $combined[] = $line;
  }
}

file_put_contents("$dir/Pippa.php", implode('', $combined));

## combine PippaFunctions.php

$combined = array();
$combined [] = "<?php\n";

foreach(glob("$dir/src/functions/*.php") as $file) {
  foreach(file($file) as $n => $line) {
    if($n < 1)
      continue;
    $combined[] = $line;
  }
}

file_put_contents("$dir/PippaFunctions.php", implode('', $combined));
