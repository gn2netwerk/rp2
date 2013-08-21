<?php
date_default_timezone_set("Europe/Berlin");
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once 'config.inc.php';

$q = (isset($_REQUEST['q'])) ? $_REQUEST['q'] : false; //query
$a = (isset($_REQUEST['a'])) ? $_REQUEST['a'] : false; //ajax
$prefix = (isset($_REQUEST['prefix'])) ? $_REQUEST['prefix'] : false; //ajax result prefix

/* AJAX Mode */
if ($a && $prefix) {
    $result = array();
    header('Content-Type:application/javascript');
    
    $pattern = 'tmp/result.'.$prefix.'.*';
    foreach (glob($pattern) as $file) {
        $serverResults = json_decode(file_get_contents($file));
        $result[] = $serverResults;
    }
    
    echo json_encode($result);
    die();
}

/* Search Mode */
if ($q) {
    
    /* Clear /tmp */
    $now = time();
    foreach (glob('tmp/*') as $file) {
        if ($now - filemtime($file) > 60) {
            unlink($file);
        }
    }


    $tmpPrefix = substr(sha1(date('YmdHis').rand()), 0, 6);
    
    foreach (DF_Config::$servers as $server) {
        $cmd = "nohup ".DF_Config::$php." -c ".DF_Config::$ini
                ." api.php $server[0] $server[1] $server[2] ".escapeshellarg($q)
                ." $tmpPrefix > /dev/null 2> /dev/null < /dev/null &";
        system($cmd);
    }
} 
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>DF API - <?php echo htmlentities($q, ENT_QUOTES, 'UTF-8'); ?></title>
        <script>var resultPrefix = "<?php echo $tmpPrefix; ?>";</script>
        <script src="jquery.min.js"></script>
        <script src="init.js"></script>
        <style type="text/css">
            body { font-family: Verdana; font-size: 12px; }
            #domains, #customers { border:1px solid #cccccc; margin: 20px 4%; float:left; width: 92%;}
            .result { float:left; width: 300px; height:100px; border:1px solid #333333; margin:10px; padding: 5px 10px; }
        </style>
    </head>
    <body>
        <div id="results">
        </div>
    </body>
</html>