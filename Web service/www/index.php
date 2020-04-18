<?php

define('HASH_ALGO', 'sha256');
define('HASHED_PASSWORD', 'hashedPassword');
define('LINKS', 'links');
define('LINKS_GENERATOR_URL', 'generateUptoboxLinks.php?token=%s&links=%s');
define('PASSWORD', 'password');
define('SPLIT_REGEX', '/(\r\n)|\r|\n/');
define('TIMESTAMP', 'timestamp');
define('USER', 'user');
define('REDIRECT_HEADER', 'location: ');

if (isset($_POST[USER]) && isset($_POST[HASHED_PASSWORD]) && isset($_POST[LINKS])) {
	$token = array(
		USER => $_POST[USER],
		HASHED_PASSWORD => $_POST[HASHED_PASSWORD],
		TIMESTAMP => time() * 1000
	);
	$links = preg_split(SPLIT_REGEX, $_POST[LINKS]);
	$linksGeneratorUrl = sprintf(LINKS_GENERATOR_URL, base64_encode(json_encode($token)), urlencode(json_encode($links)));
	//header(REDIRECT_HEADER . $linksGeneratorUrl);
	echo $linksGeneratorUrl;
	
} else {
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Utpobox download links</title>
	</head>
	<body>
		<section id="form">
			<input type="text" id="<?=USER?>" placeholder="<?=USER?>"><br>
			<input type="password" id="<?=PASSWORD?>" placeholder="<?=PASSWORD?>"><br>
			<textarea cols="50" rows="7" id="<?=LINKS?>" placeholder="<?=LINKS?>"></textarea><br>
			<input type="button" value="Post" onclick="post()">
		</section>
		<script>
			async function post() {
				const formData = new FormData();
				formData.append('<?=USER?>', document.getElementById('<?=USER?>').value);
				formData.append('<?=HASHED_PASSWORD?>', await sha256(document.getElementById('<?=PASSWORD?>').value));
				formData.append('<?=LINKS?>', document.getElementById('<?=LINKS?>').value);
				fetch('<?=$_SERVER['PHP_SELF']?>', {
					method: 'POST',
					body: formData
				}).then(response => response.text()).then(linksGeneratorUrl => document.location = linksGeneratorUrl);
			}
			async function sha256(message) {
				const msgBuffer = new TextEncoder('utf-8').encode(message);
				const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
				const hashArray = Array.from(new Uint8Array(hashBuffer));
				const hashHex = hashArray.map(b => ('00' + b.toString(16)).slice(-2)).join('');
				return hashHex;
			}
		</script>
	</body>
</html>

<?php } ?>