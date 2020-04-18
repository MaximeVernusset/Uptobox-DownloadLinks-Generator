<?php
// Configurable parameters in ws-config.php
// IMPORTANT: put this file outside the web root, for security reasons
include_once '../ws-config.php';

/* POST & GET mandatory parameters */
define('DOWNLOAD', 'download');
define('LINKS', 'links');
define('TOKEN', 'token');
/* User token parameters */
define('HASHED_PASSWORD', 'hashedPassword');
define('TIMESTAMP', 'timestamp');
define('USER', 'user');
/* Script constants */
define('CAN_DOWNLOAD', 'canDownload');
define('HASH_ALGO', 'sha256');
define('HTTP_403', 403);
define('MAX_TOKEN_VALIDITY', 5*60*1000); // 5 min in millis
define('PASSWORD', 'password');
define('UPTOBOX_API_URL', 'https://uptobox.com/api/link?file_code=%s&token='.UPTOBOX_TOKEN);
define('UPTOBOX_DATA', 'data');
define('UPTOBOX_DLLINK', 'dlLink');
define('UPTOBOX_MESSAGE', 'message');
define('UPTOBOX_STATUS_CODE', 'statusCode');
define('URL_PATH', 'path');

function getTimestampInMillis() {
	return time() * 1000;
}

function authorizeUser($token) {
	$user = isset($token[USER]) ? $token[USER] : '';
	$hasedPassword = isset($token[HASHED_PASSWORD]) ? $token[HASHED_PASSWORD] : '';
	$deltaTime = isset($token[TIMESTAMP]) ? getTimestampInMillis() - $token[TIMESTAMP] : -1;
	
	return isset(USERS[$user])
		&& hash(HASH_ALGO, $hasedPassword) == USERS[$user][PASSWORD]
		&& $deltaTime < MAX_TOKEN_VALIDITY;
}

if (isset($_GET[TOKEN]) && isset($_GET[LINKS]) && authorizeUser(json_decode(base64_decode($_GET[TOKEN]), true))) {
	$token = json_decode(base64_decode($_GET[TOKEN]), true);
	$user = $token[USER];
	$links = json_decode($_GET[LINKS]);
	$generatedDownloadLinks = array();
	$errors = array();
	
	foreach ($links as $link) {
		$parsedUrl = parse_url($link);
        if (isset($parsedUrl[URL_PATH])) {
			$fileCode = ltrim(filter_var($parsedUrl[URL_PATH], FILTER_SANITIZE_URL), '/');
			$uptoboxResponse = json_decode(file_get_contents(sprintf(UPTOBOX_API_URL, $fileCode)), true);
			if ($uptoboxResponse[UPTOBOX_STATUS_CODE] == 0) {
				$generatedDownloadLinks[] = $uptoboxResponse[UPTOBOX_DATA][UPTOBOX_DLLINK];
			} else {
				$errors[$fileCode] = $uptoboxResponse[UPTOBOX_MESSAGE];
			}
		}
	}
	
	$nbErrors = count($errors);
	$nbGeneratedLinks = count($generatedDownloadLinks);
	$token[TIMESTAMP] = getTimestampInMillis();
?>	

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Utpobox download links</title>
		<style>
			.hidden { visibility: hidden; }
		</style>
	</head>
	<body>
<?php if ($nbErrors > 0) { ?>
		<section>
			<h2>Errors:</h2>
			<textarea id="errors" rows="<?= $nbErrors + 2 ?>" style="width:100%;"><?= json_encode($errors, JSON_PRETTY_PRINT) ?></textarea>
		</section>
<?php } ?>
<?php if ($nbGeneratedLinks > 0) { ?>
		<section>
			<h2>Download links:</h2>			
			<ul>
	<?php
	foreach ($generatedDownloadLinks as $downloadLink) {
		$parts = explode('/', $downloadLink);
		echo '<li><a href="' . $downloadLink . '">' . $parts[count($parts) - 1] . '</a></li>';
	}
	?>
			</ul>
			<label for="downloadLinks">Raw:</label>
			<textarea id="downloadLinks" rows="<?= $nbGeneratedLinks * 3 ?>" style="width:100%;"><?= join($generatedDownloadLinks, PHP_EOL) ?></textarea>
		</section>
<?php if (USERS[$user][CAN_DOWNLOAD]) { ?>
		<section>
			<h2>Download servers</h2>
			<ul>
	<?php
	foreach (DOWNLOAD_SERVERS as $name => $url) {
		echo '<li><a href="' . $url . '" target="_blank">' . $name . '</a></li>';
	}
	?>		
			</ul>
		</section>
<?php } ?>
		<section>
			<h2 hidden>Toast</h2>
			<div id="toast"></div>
		</section>
<?php } ?>
		<script>
			window.onload = () => {
				const downloadLinks = document.getElementById('downloadLinks');
				if (downloadLinks) {
					downloadLinks.select();
					downloadLinks.setSelectionRange(0, 99999);
					if (document.execCommand('copy')) {
						document.getElementById('toast').innerHTML = '<p id="linksCopied"><em>Download links copied to clipboard</em></p>';
						setTimeout(() => document.getElementById('linksCopied').remove(), 3000);
					}
				}
			}
		</script>
	</body>
</html>

<?php
} else {
	header('Content-Type: text/html', true, HTTP_403);
}
?>