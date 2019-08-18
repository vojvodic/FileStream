# FileStream (local files) chunk by chunk

Small class that can read large files (1GB, 5GB,20GB, 50GB) chunk by chunk

## Examples
Read input file line by line
```
$stream = new FileStream( $input_file_path );
foreach($stream->start() as $line){

  echo $line->number . " : " . $line->content . "\n";

}
```
