<?php

/**
 * Stream large files chunk by chunk using lines or bytes
 * Specify chunk size in lines or bytes
 */
class FileStream
{
    /**
     * Input file path a file which will be used for reading
     * @var [string] $inputFilePath
     */
    private $inputFilePath;

    /**
     * Mime type of input file
     * @var [string] $mimeType
     */
    public $mimeType;

    /**
     * Size of input file in bytes
     * @var [integer] $fileSize
     */
    public $fileSize;

    /**
     * File handle resource / buffer of input file
     * @var [resource / buffer] $handle
     */
    private $handle;

    /**
     * If true stream in bytes otherwise in file lines
     * @var [bool] $usingBytes
     */
    public $usingBytes = true;

    /**
     * How many chunks to stream in one call
     * If $usingBytes is true then chunkSize is bytes length otherwise it represents number of lines
     * @var [integer] $chunkSize
     */
    public $chunkSize = 1024;

    /**
     * Memory units
     * @var [array] $memoryUnits
     */
    private $memoryUnits = ['B', 'KB', 'MB', 'GB'];

    /**
     * @method __construct
     * @param  [string]      $input_file_path
     */
    public function __construct( $input_file_path )
    {
        $this->setInputFile($input_file_path);
        $this->setMimeType();
        $this->setFileSize();
    }

    /**
     * Set input file
     * @method setInputFile
     * @var    [string]    $file_path
     * @return [bool]
     */
    private function setInputFile($file_path)
    {
        if ( !file_exists($file_path) ) {
          throw new \Exception("Input file doesn't exist!");
          return false;
        }
        $this->inputFilePath = $file_path;
        return true;
    }

    /**
     * Set mime type of the file
     * @method setMimeType
     * @return [bool]
     */
    private function setMimeType()
    {
        $mime_type = mime_content_type( $this->inputFilePath );
        if ( $mime_type === false ) {
          throw new \Exception("Unable to get mime type of input file!");
          return false;
        }
        $this->mimeType = $mime_type;
        return true;
    }

    /**
     * Set file size
     * @method setFileSize
     * @return [void]
     */
    private function setFileSize()
    {
        $this->fileSize = filesize( $this->inputFilePath );
    }

    /**
     * Return human readable memory usage
     * @method getMemoryUsage
     * @var    $bytes [integer]
     * @return [string]
     */
    public function getMemoryUsage($bytes = 0)
    {
        $bytes = $bytes !== 0 ?  $bytes : memory_get_usage();
        $power = $bytes > 0 ? floor( log($bytes, 1024) ) : 0;
        return number_format($bytes / pow(1024, $power), 3, '.', ',') . $this->memoryUnits[$power];
    }

    /**
     * Generator function that will start stream to input file
     * @method start
     * @return [object / array / string]
     */
    public function start()
    {
        $this->openHandle();

        if ($this->usingBytes) {
          yield from $this->streamUsingBytes();
        } else {
          yield from $this->streamUsingLines();
        }

        $this->closeHandle();
    }

    /**
     * Generator function that will start reading $input_file_path and return requested line or lines
     * @method streamUsingLines
     * @return [object / array]
     */
    private function streamUsingLines()
    {
        $line_pointer = 0;
        $internal_chunk_pointer = 0; // Reset this each time start method is called
        $lines = []; // If chunk size is greater then 1, we will push lines here to allocate more into memory in one call
        while ( !feof($this->handle) ) {
            $buffer = fgets( $this->handle );
            $line_pointer++;
            $internal_chunk_pointer++;
            if ( $this->chunkSize == 1 ) { // Release one chunk
                yield (object) [
                  'number'  => $line_pointer,
                  'content' => $buffer
                ];
            } else{ // Allocate lines here
              $lines[$internal_chunk_pointer] = (object) [
                'number'  => $line_pointer,
                'content' => $buffer
              ];
              // We just hit the maximum limit of chunk size, release here and reset lines and internal pointer
              if ($internal_chunk_pointer == $this->chunkSize) {
                yield $lines;
                $lines = [];
                $internal_chunk_pointer = 0;
                $buffer = null;
              }
            }
        }

        // No more lines to read - check for last chunk and close file handle
        // Last chunk of lines can be smaller then requested chunk and we need to release it here
        // Example: if file has 10 lines and request chunk is 4 lines, last chunk is 2 lines
        // This will only happen while reading last part of file
        if ( $internal_chunk_pointer < $this->chunkSize && count($lines) > 0) {
          yield $lines;
        }
    }

    /**
     * Generator function that will start reading $input_file_path and return requested bytes lenght
     * @method streamUsingBytes
     * @return [string]
     */
    private function streamUsingBytes()
    {
        // Use fread instead of fgets function since we can specify how many bytes should be returned becouse fgets will stop on specified bytes (lenght), new line, EOF - whichever comes first
        while( !feof($this->handle) ) {
            $buffer = fread($this->handle,$this->chunkSize);
            yield $buffer;
            $buffer = null;
        }
    }

    /**
     * Open file handle on $input_file_path
     * @method openHandle
     * @return [void]
     */
    private function openHandle()
    {
        if ( !$this->handle ) {
          $this->handle = fopen($this->inputFilePath,($this->usingBytes ? 'rb' : 'r')); // If stream in bytes then use rb safe reading in binary mode
          if ( !$this->handle ) {
            throw new Exception('File open failed.');
          }
        }
    }

    /**
     * Close file handle on $input_file_path
     * @method closeHandle
     * @return [void]
     */
    private function closeHandle()
    {
        if ( $this->handle ) {
          fclose($this->handle);
        }
    }
}
