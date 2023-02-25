<?php
    $benchmarks = array();

    function log_slack( $message ) {
        logger( "slack", "slack.log", $message );
    }

    function log_sql( $message ) {
        logger( "sql", "sql.log", $message );
    }

    function log_debug( $message ) {
        logger( "debug", "debug.log", $message );
    }

    function log_payment( $message ) {
        logger( "payment", "payment.log", $message );
    }

    function log_benchmark( $message ) {
        logger( "benchmark", "benchmark.log", $message );
    }

    function benchmark_start( $name ) {
        global $benchMarks;
        $mt = explode(' ', microtime());
        $startTime = ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
        $benchMarks[$name] = $startTime;
    }

    function benchmark_stop( $name ) {
        global $benchMarks;
        $mt = explode(' ', microtime());
        $stopTime = ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
        $startTime = $benchMarks[$name];

        log_benchmark( "BENCHMARK [$name] - [" . ($stopTime - $startTime ) . " ms]" );
        unset( $benchMarks[$name] );
    }

    /**
     * Log function with log file rotation
     * and loglevel restrictions
     * SEE: https://blog.niklasottosson.com/php/custom-log-to-file-function/
     *
     * @param <int> $level
     * @param <string> $event
     * @param <string> $text
     */
    function logger($directory, $logFile, $text ){
        $logDirectory = getLogDirectory( $directory );
        $completeLogFile = $logDirectory . $logFile;

        $maxsize = 5242880; //Max filesize in bytes (e.q. 5MB)

        if( file_exists ( $completeLogFile ) && filesize( $completeLogFile ) > $maxsize ) {
//            $nb = 1;
//            $logfiles = scandir( $logDirectory );
//
//            // Rolling the logs, find the highest number file
//            foreach ( $logfiles as $file ) {
//                $tmpnb = substr( $file, strlen( $logFile ) - 4, 1 );
//                if( $nb < $tmpnb ){
//                    $nb = $tmpnb;
//                }
//            }

            $logFileWithoutSuffix = substr( $completeLogFile, 0, strlen( $completeLogFile ) - 4 );

            rename( $completeLogFile, $logFileWithoutSuffix . date('_Y_m_d_H_i') . ".log");
        }

        $data = "[" . date('Y-m-d H:i:s') . "] " . $text . PHP_EOL;
        file_put_contents( $completeLogFile, $data, FILE_APPEND );
    }

    function getLogDirectory( $directory ) {
        $completeDirectory = $_ENV['LOG_DIRECTORY'] . $directory . $_ENV['SEPARATOR'];

        if( !is_dir( $completeDirectory ) ) {
            mkdir( $completeDirectory );
        }

        return $completeDirectory ;
    }
?>