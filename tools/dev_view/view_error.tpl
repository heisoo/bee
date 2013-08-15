<?php

$message = nl2br($data['message']);
$time = date('Y-m-d H:i:s');

$html = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>
{$data['type']}
</title>

<style type="text/css">
/*<![CDATA[*/
body {font-family:"Verdana";font-weight:normal;color:black;background-color:white;}
h1 { font-family:"Verdana";font-weight:normal;font-size:18pt;color:red }
h2 { font-family:"Verdana";font-weight:normal;font-size:14pt;color:maroon }
h3 {font-family:"Verdana";font-weight:bold;font-size:11pt}
p {font-family:"Verdana";font-size:9pt;}
pre {font-family:"Lucida Console";font-size:10pt;}
.version {color: gray;font-size:8pt;border-top:1px solid #aaaaaa;}
.message {color: maroon;}
.source {font-family:"Lucida Console";font-weight:normal;background-color:#ffffee;}
.error {background-color: #ffeeee;}
/*]]>*/
</style>
</head>

<body>
<h1>{$data['type']}</h1>

<h3>Description</h3>
<p class="message">
{$message}
</p>

<h3>Source File</h3>
<p>
{$data['file']}"({$data['line']})"
</p>

HTML;


$html .= <<<HTML
<div class="source">
<pre>
HTML;

if(empty($data['source']))
    $html .= 'No source code available.';
else
{
    foreach($data['source'] as $line=>$code)
    {
        $trans = array('<' => '&lt;', '>' => '&gt;');
        $code = strtr($code, $trans);

        if($line!==$data['line'])
        {
            $html .= sprintf("%05d: %s",$line,str_replace("\t",'    ',$code));
        }
        else
        {
            $html .=  "<div class=\"error\">";
            $html .=  sprintf("%05d: %s",$line,str_replace("\t",'    ',$code));
            $html .=  "</div>";
        }
    }
}

$html .= <<<HTML
</pre>
</div><!-- end of source -->

<h3>Stack Trace</h3>
<div class="callstack">
<pre>
{$data['trace']}
</pre>
</div><!-- end of callstack -->

<div class="version">
{$time} {$data['version']}
</div>
</body>
</html>
HTML;

return $html;

?>
