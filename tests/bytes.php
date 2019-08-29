<?php

require __DIR__ . '/../src/FileStream.php';

$stream = new FileStream( 'dummy_file.txt' );
$stream->chunkSize = 1024; // Change this to  $stream->fileSize to allocate all bytes into memory in one call

foreach($stream->start() as $buffer){
  // echo $buffer . "\n";
  echo $stream->getMemoryUsage() . "\n";

}
