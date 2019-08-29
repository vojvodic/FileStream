# FileStream (local files) chunk by chunk

PHP class that can read large files (1GB, 5GB,20GB, 50GB) chunk by chunk.<br/>
If you are reading large files with size over 1GB,10GB, ... we can read them chunk by chunk (line by line or by 10000 lines or by bytes length).

## Examples
Read file using bytes where chunk size is bytes length
```
$stream = new FileStream( $input_file_path ); // String - path to file
$stream->chunkSize = 1024; // How many bytes to allocate in one call

foreach($stream->start() as $buffer){

  echo $buffer;

}

```

Read file line by line where chunk size is one line.
```
$stream = new FileStream( $input_file_path );
$stream->usingBytes = false; // Stream in lines

foreach($stream->start() as $line){ $line is object representing line content and number

  echo $line->number . " : " . $line->content . "\n";
  echo $stream->getMemoryUsage() . "\n";
}

```

Read file using lines and allocate more lines in one call
```
$stream = new FileStream( $input_file_path );
$stream->chunkSize = 50000; // Integer - How many lines to allocate in one call
$stream->usingBytes = false; // Stream in lines

foreach($stream->start() as $lines){ // $lines is now array of line objects with array size the same as chunk size

  foreach ($lines as $line) {
    file_put_contents('output_to_file.txt', $line->content, FILE_APPEND);
  }

}

```

## Tests
During testing i have created dummy file (10000 lines) that you can use for testing and script in /tests <br/>
Here are results:<br/>
<br/>

```
Chunk size 10000 (all lines allocated in memory in one call):
Line number: 1     Memory usage: 11.183MB
Line number: 1000  Memory usage: 11.183MB
Line number: 2000  Memory usage: 11.183MB
Line number: 5000  Memory usage: 11.183MB
Line number: 7000  Memory usage: 11.183MB
Line number: 10000 Memory usage: 11.183MB

```

```
Chunk size 10:
Line number: 1     Memory usage: 415.781KB
Line number: 1000  Memory usage: 415.820KB
Line number: 2000  Memory usage: 415.820KB
Line number: 5000  Memory usage: 415.820KB
Line number: 7000  Memory usage: 415.820KB
Line number: 10000 Memory usage: 415.820KB

```
