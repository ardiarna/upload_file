<?php
$filepath="../download/";
$randname=$_POST["name"]. '-' . $_POST['index'];
if(move_uploaded_file($_FILES["file"]["tmp_name"], $filepath.$randname)){
	echo "1";
}else{
	echo "0";
}
?>