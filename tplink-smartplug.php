// 
// TP-Link Wi-Fi Smart Plug Protocol Client
// For use with TP-Link HS-100 or HS-110
//
// PHP port by John Horton of the great work by Lubomir Stroetmann
// 
// Copyright 2016 softScheck GmbH 
// 
//
// Use either:
// Terminal 'php -f tplink-smartplug.php <IP ADDRESS OF PLUG> <COMMAND>' or 
// Webpage 'tplink-smartplug.php?ipaddress=<IP ADDRESS OF PLUG>&command=<COMMAND>'
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
// 
//      http://www.apache.org/licenses/LICENSE-2.0
// 
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
// 
//
<?php

// Set version number
$version = 0.1;

// Set port number
$port = 9999;

// Array containing predefined Smart Plug Commands
// For a full list of commands, consult tplink_commands.txt

$commands = array('info'     => '{"system":{"get_sysinfo":{}}}',
			'on'       => '{"system":{"set_relay_state":{"state":1}}}',
			'off'      => '{"system":{"set_relay_state":{"state":0}}}',
			'cloudinfo'=> '{"cnCloud":{"get_info":{}}}',
			'wlanscan' => '{"netif":{"get_scaninfo":{"refresh":0}}}',
			'time'     => '{"time":{"get_time":{}}}',
			'schedule' => '{"schedule":{"get_rules":{}}}',
			'countdown'=> '{"count_down":{"get_rules":{}}}',
			'antitheft'=> '{"anti_theft":{"get_rules":{}}}',
			'reboot'   => '{"system":{"reboot":{"delay":1}}}',
			'reset'    => '{"system":{"reset":{"delay":1}}}'
			);

// Check if IP if valid
function validIP($ip){
	if (!filter_var($ip, FILTER_VALIDATE_IP) === false) {
    		return($ip);
	} else {
    		echo("$ip is not a valid IP address");
	}
}

// Encryption function
function encryptMe($string){
	$strlen = strlen($string);
	$key = 171;
	$result = "\0\0\0\0";
	for( $i = 0; $i <= $strlen; $i++ ) {
    		$char = substr( $string, $i, 1 );
        	// Start of encryption
        	$a = $key ^ ord($char);
        	$key = $a;
        	$result .= chr($a);
	}
	return $result;
}

// Decryption function
function decryptMe($string){
        $strlen = strlen($string);
        $key = 171;
        $result = "\0\0\0\0";
        for( $i = 0; $i <= $strlen; $i++ ) {
                $char = substr( $string, $i, 1 );
                // Start of decryption
                $a = $key ^ ord($char);
                $key = ord($char);
                $result .= chr($a);
	}
	return $result;
}

// Function to send to plug
function transmitMe($command){
	global $ipaddress, $port;
	$fp = fsockopen($ipaddress, $port, $errno, $errstr, 30);
	if (!$fp) {
	    echo "$errstr ($errno)<br />\n";
	} else {
	    fwrite($fp, $command);
	    while (!feof($fp)) {
	        echo fgets($fp, 128);
	    }
	    fclose($fp);
	}
}

// Check to see whether php is run in browser or command line and parse command arguments
if (PHP_SAPI === 'cli') {
	$ipaddress = $argv[1];
	$receivedCommand = $argv[2];
}
else {
	$ipaddress = $_GET['ipaddress'];
	$receivedCommand = $_GET['command'];
}

// Send the command to TP Link
transmitMe(encryptMe($commands[$receivedCommand]));

?>
