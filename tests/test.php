<?php

require '../src/FileStream.php';

$stream = new FileStream( 'dummy_file.txt' );
$stream->chunkSize = 10; // Change this to 10000 to allocate all lines into memory in one call

$time_start = microtime(true);

foreach($stream->start() as $lines){

  foreach ($lines as $line) {
    if ( in_array($line->number,[1,1000,2000,5000,7000,10000]) ) {
      echo "Line number: " . $line->number . ' Memory usage: ' . $stream->getMemoryUsage() . "\n";
    }
  }

}

echo 'Total execution time in seconds: ' . (microtime(true) - $time_start) . "\n";