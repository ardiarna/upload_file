<?php
$target = "../download/".$_POST["name"];
$dst = fopen($target, 'wb');

$hasil = [];

for($i = 0; $i < $_POST['index']; $i++) {
	$slice = $target . '-' . $i;
	$src = fopen($slice, 'rb');
	$hasil[$i] = stream_copy_to_stream($src, $dst);
	fclose($src);
	unlink($slice);
}
fclose($dst);

$link = "https://".$_SERVER["SERVER_NAME"]."/download/".$_POST["name"];
$htmlnya = '<h3>'.$_POST["name"].' sukses diupload<br><br>Link untuk email: <br><a href="'.$link.'">'.$link.'</a></h3>';
kirimJson($htmlnya, $hasil);

function kirimJson($data, $msg, $status = 'success') {
  $response = array(
    'status' => $status,
    'message' => $msg,
    'data' => $data
  );
  header('Content-Type: application/json');
  echo json_encode($response);
  exit();
}