<?php
require_once "config.inc.php";

$response = array();


if ($argv[1] && $argv[2] && $argv[3] && $argv[4]) {
    define("BBRPC_URL", $argv[1]);
    define("USER", $argv[2]);
    define("PWD",  $argv[3]);
    define('BBRPC_COOKIE', 'tmp/cookie.'.sha1(serialize($argv)));
    require_once('bb.rpc.class.php');
    bbRpc::auth(USER,PWD);

    /* Search Domains */
    $rDomains = bbRpc::call(
        "bbDomain::searchEntry",
        array(
            "return_array" => 1,
            "name"         => '%'.$argv[4].'%'
        )
    );
    if (!empty($rDomains) && is_array($rDomains)) {
        $response['domains'] = array();
        foreach ($rDomains as $result) {
            $response['domains'][] = array(
                'domain' => $result['name'],
                'server' => $argv[1],
                'user'   => $argv[2],
                'pass'   => $argv[3]
            );
        }
    }      
    
    /* Search Customers */
    $rCustomers = array();
    foreach (array('first_name', 'last_name', 'company') as $field) {
        $rCustomerResponse = bbRpc::call(
            "bbCustomer::searchEntry",
            array(
                "return_array" => 1,
                $field    => $argv[4]
            )
        );
        if (is_array($rCustomers)) {
            $rCustomers = array_merge($rCustomers, $rCustomerResponse);
        }
    }
    
    
    if (!empty($rCustomers) && is_array($rCustomers)) {
        $response['customers'] = array();
        foreach ($rCustomers as $result) {
            $cResult = $result;

            $fieldStr = '';
            foreach ($result as $k=>$v) {
                $fieldStr.="<strong>".$k."</strong> : ".$v."<br>";
            }
            $cResult['server'] = $argv[1];
            $cResult['fields'] = $fieldStr;
            $response['customers'][] = $cResult;
        }
    }          
    

   
} else {
    $response['error'] = 'Missing parameters';
}

if (!empty($response)) {
    file_put_contents('tmp/result.'.$argv[5].".".sha1($argv[1]), json_encode($response));
}
