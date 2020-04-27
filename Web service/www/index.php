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
	echo $linksGeneratorUrl;
	
} else {
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Utpobox download links</title>
		<meta charset="utf-8"/>
		<meta name="author" content="Maxime Vernusset"/>
		<meta name="copyright" content="Maxime Vernusset"/>
		<meta name="language" content="en"/>
		<meta name="robots" content="noindex, nofollow">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	</head>
	<body>
		<section id="form" class="container">
			<input type="text" id="<?=USER?>" class="form-control" placeholder="<?=USER?>"><br>
			<input type="password" id="<?=PASSWORD?>" class="form-control" placeholder="<?=PASSWORD?>"><br>
			<textarea cols="50" rows="7" id="<?=LINKS?>" class="form-control" placeholder="<?=LINKS?>"></textarea><br>
			<input type="button" class="form-control" value="Post" onclick="post()">
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