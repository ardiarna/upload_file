<?php
$target = "../download/".$_POST["name"];
$dst = fopen($target, 'wb');

for($i = 0; $i < $_POST['index']; $i++) {
	$slice = $target . '-' . $i;
	$src = fopen($slice, 'rb');
	stream_copy_to_stream($src, $dst);
	fclose($src);
	unlink($slice);
}
fclose($dst);
$link = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"]."/download/".$_POST["name"];
echo '<h3>'.$_POST["name"].' sukses diupload<br><br>Link untuk email: <br><a href="'.$link.'">'.$link.'</a></h3>';
?>