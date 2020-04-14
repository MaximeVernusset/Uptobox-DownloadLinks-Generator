<?php
// Configurable parameters for uptobox-downloader.php
// IMPORTANT: put this file outside the web root, for security reasons

$users = array(
	'test' => array(
		'canDownload' => false,
		'password' => '7b3d979ca8330a94fa7e9e1b466d8b99e0bcdea1ec90596c0dcc8d7ef6b4300c' // Initial password hased 2 times with sha256 algorithm
	)
);

$downloadServers = array(
	'name' => 'url'
);

define('USERS', $users);
define('DOWNLOAD_SERVERS', $downloadServers);
define('UPTOBOX_TOKEN', 'PASTE_YOUR_TOKEN'); // Token can be found on uptobox.com account settings