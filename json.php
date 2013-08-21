<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once 'config.inc.php';

$q = (isset($_REQUEST['q'])) ? $_REQUEST['q'] : false; //query
$a = (isset($_REQUEST['a'])) ? $_REQUEST['a'] : false; //ajax
$prefix = (isset($_REQUEST['prefix'])) ? $_REQUEST['prefix'] : false; //ajax result prefix

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
    
    $parsed = array();
    $count = 0;
    $results = array();
    while($count < 10) {
        usleep(200000); 
        foreach (glob('tmp/result.'.$tmpPrefix.'.*') as $file) {
            if (!in_array($file, $parsed)) {
                $json = json_decode(file_get_contents($file));
                if (isset($json->domains)) {
                    foreach ($json->domains as $domain) {
                        if ($domain->domain != "") {
                            $results[] = $domain;
                        }
                    }
                }
                $parsed[] = $file;
            }
        }
        $count++;
    }
    header('Content-Type:application/json');
    echo json_encode($results);
    
} 
?>