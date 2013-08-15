<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);  
//set_error_handler("errorHandler"); 
function phpError() { 
    $isError = false;

    if ($err = error_get_last()){
        switch($err['type']){
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
            $isError = true;
            break;
        }
    }

    $data = array(
        'type'      => $err['type'],
        'message'   => $err['message'],
        'file'      => $err['file'],
        'line'      => $err['line'],
        'source'    => getSourceLines($err['file'], $err['line']),
        'time'      => BEE_BEGIN_TIME,
    );

    $viewFile = 'view_error.tpl';
    echo include($viewFile);

}


function getSourceLines($file, $line)
{

    // determine the max number of lines to display
    $maxLines = 25;

    if( $maxLines < 1 ){
        $maxLines=1;
    } else if($maxLines>100) {
        $maxLines=100;
    }

    $line--;    // adjust line number to 0-based from 1-based
    if($line<0 || ($lines = @file($file)) === false || ($lineCount=count($lines)) <= $line)
    {
        return array();
    }

    $halfLines  = (int)($maxLines/2);
    $beginLine  = $line-$halfLines>0?$line-$halfLines:0;
    $endLine    = $line+$halfLines<$lineCount?$line+$halfLines:$lineCount-1;

    $sourceLines=array();
    for($i=$beginLine;$i<=$endLine;++$i)
    {
        $sourceLines[$i+1]=$lines[$i];
    }

    return $sourceLines;
}

register_shutdown_function('phpError');

?>
