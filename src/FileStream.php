<?php

/**
 * Stream large files chunk by chunk
 * Specify chunk size / how many lines to return from file in one call
 */
class FileStream
{
    /**
     * Input file path a file which will be used for reading
     * @var [string]
     */
    private $inputFilePath;

    /**
     * How many chunks / lines to stream in one call
     * @var [integer]
     */
    public $chunkSize = 1;

    /**
     * Memory units
     * @var [array]
     */
    private $memoryUnits = ['B', 'KB', 'MB', 'GB'];

    /**
     * @method __construct
     * @param  [string]      $input_file_path
     */
    public function __construct( $input_file_path )
    {
        $this->setInputFile($input_file_path);
    }

    /**
     * Set input file and make file if not exists
     * @method setInputFile
     * @var    [string]
     * @return [bool]
     */
    public function setInputFile($file_path)
    {
        if ( !file_exists($file_path) ) {
          throw new \Exception("Input file doesn't exist!");
          return false;
        }
        $this->inputFilePath = $file_path;
        return true;
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
     * @return [object / array] - It depends on chunk size 1 or more lines requested by the caller
     */
    public function start()
    {
        $fh = fopen( $this->inputFilePath,'r');
        if ($fh) {
            $line_pointer = 0;
            $internal_chunk_pointer = 0; // Reset this each time start method is called
            $lines = []; // If chunk size is greater then 1, we will push lines here to allocate more into memory in one call
            while ( ($buffer = fgets($fh)) !== false ) {
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
            fclose($fh);
        }
    }
}
