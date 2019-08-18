# FileStream (local files) chunk by chunk

Small class that can read large files in PHP (1GB, 5GB,20GB, 50GB) chunk by chunk


## Examples
Read file line by line where chunk size is one line.
```
$stream = new FileStream( $input_file_path ); // String

foreach($stream->start() as $line){ $line is object representing line content and number

  echo $line->number . " : " . $line->content . "\n";

}

```

Chunk size can be set by caller.
This allows you to allocate more lines into memory in one call - useful for processing milions of lines where you can't allocate 50 milion lines into memory but you can based on your machine 1 milion or more and script will execute faster.
```
$stream = new FileStream( $input_file_path ); // String
$stream->chunkSize = 50000; // Integer

foreach($stream->start() as $lines){ // $lines is now array of line objects with array size the same as chunk size

  foreach ($lines as $line) {
    file_put_contents('output_to_file.txt', $line->content, FILE_APPEND);
  }

}

```
