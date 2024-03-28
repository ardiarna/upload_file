<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Upload file untuk email</title>
<style type="text/css">
body{ 
  padding: 20px 
}
.dv-form{
  padding: 40px 20px;
  background: #d8e6e8; 
  border-radius: 10px; 
}
.dv-form p{
  font-size:18px;
}

.btn-copy {
    width: 70px;
    height: 20px;
    background-color: #cecece;
    border: 0px;
    font-size: 10px;
    border-radius: 30px;
    margin-left: 15px;
    color: white;
}

</style>
<script src="js/jquery.min.js"></script>
<link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<h2 style="margin: 70px 10px 30px 10px;" align="center">Upload file untuk link email</h2>
<div class="dv-form">
    <label>Pilih file: (max 500MB)</label><br/>
	<input type="file" id="file" class="form-control"><br/>
    <button type="button" class="btn btn-success" id="btn_upload" disabled>
        <span class="glyphicon glyphicon-arrow-up"></span>
        Upload File
    </button><br/><br/>
    <div class="progress" style="display:none;">
        <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div id="message_info"></div>
</div>
<script>

var file_obj;
const BYTES_PER_CHUNK = 1024 * 1024;
var slices;
var totalSlices;
var chunk;
var data_chunk = [];
var totalUploaded = 0;

$(document).ready(function(){
	$('#file').change(function(){
		 collectDataChunk();
	});
	$('#btn_upload').click(function(){
		var file_val = $('.file').val();
		if(file_val == ""){
			alert("Please select a file");
			return false;
		}else{
            $('#btn_upload').prop('disabled', true);
			$('.progress').show();
            ajax_file_upload(file_obj, 0);
		}
   
	});
});

function collectDataChunk() {
    file_obj = document.getElementById('file').files[0];
    if(!file_obj){
        alert("Select a file please..");
    }else{
        var start = 0;
        var end;
        var index = 0;
        slices = Math.ceil(file_obj.size / BYTES_PER_CHUNK);
        totalSlices= slices;
        data_chunk = [];
        totalUploaded = 0;
        
        while(start < file_obj.size) {
            end = start + BYTES_PER_CHUNK;
            if(end > file_obj.size) {
                end = file_obj.size;
            }
            /*collecting chunk's data and store it */
            data_chunk[index] = start + "|" + end;
            console.log("start : "+start+", end : "+end +", total slices : "+ totalSlices+", slices : "+ slices);
            start = end;
            index++;
            slices--;
        }
        $('#btn_upload').prop('disabled', false);
        $('.progress').hide();
        $('#message_info').html("");
    }
}

function ajax_file_upload(file_obj, f) {
	if(file_obj != undefined) {
		var text = data_chunk[f].split("|");
		var start_ = text[0];
		var end_ = text[1];
		
		chunk = file_obj.slice(start_, end_);
		var formdata = new FormData();                  
		formdata.append("file", chunk);
		formdata.append("name", file_obj.name);
		formdata.append("index", f);
		$.ajax({
			type: 'POST',
			url: 'upload.php',
			contentType: false,
			processData: false,
			data: formdata,
			beforeSend:function(response) {
				//
			},
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = f + (evt.loaded / evt.total);
                        totalUploaded += evt.loaded;
                        let ps = (percentComplete/data_chunk.length*100).toFixed();
                        $('.progress-bar').css("width", ps + "%");
                        $('.progress-bar').html(ps + "%");
                    }
                }, false);
                return xhr;
            },
			success:function(response) {
				console.log("response" + response);
				if(response == 1){
                    if(f<data_chunk.length-1){
                        sleep(300);/* give 0.3 sec to avoid error server getting busy..*/
                        ajax_file_upload(file_obj , f+1);
                        // $('.progress-bar').css("width", (f/data_chunk.length*100) + "%");
                    }else{
                        mergeFile(file_obj);
                    }
				} else if(response == 0){
					$('#message_info').html("Gagal upload file..").css("color","red");
				}
			},
			error:function(xhr, textStatus, error) {
				if(textStatus == "error"){
				    $('#message_info').html("Error, terjadi kesalahan..").css("color","red");
			    }
			}
		});

	}
}

function mergeFile(file_obj) {
    var formdata = new FormData();                  
    formdata.append("name", file_obj.name);
    formdata.append("index", totalSlices);
    $.ajax({
        type: 'POST',
        url: 'merge.php',
        contentType: false,
        processData: false,
        data: formdata,
        success:function(response) {
            $('#message_info').html(`
                <div>
                    ${file_obj.name} sukses diupload <br><br>
                    Link untuk email: <br>
                    <a href="${response.data}" style="font-size: 20px;">${response.data}</a>
                    <button onclick="copyKata('${response.data}')" class="btn-copy">
                        Copy Link
                    </button>
                    <div style="padding-top: 10px;">Uploaded ${totalUploaded} bytes</div>
                </div>
            `);
        },
        error:function(xhr, textStatus, error) {
            $('#message_info').html("error: "+textStatus+" "+error);
        }
    });
}

function sleep(milliseconds) {
    const date = Date.now();
    let currentDate = null;
    do {
        currentDate = Date.now();
    } while(currentDate - date < milliseconds)
}

function copyKata(isi) {
  var textarea = document.createElement('textarea');
  textarea.value = isi;
  textarea.style.position = 'fixed';
  textarea.style.left = '-9999px';
  document.body.appendChild(textarea);
  textarea.select();
  textarea.setSelectionRange(0, textarea.value.length);
  document.execCommand('copy');
  document.body.removeChild(textarea);
  alert(`"${isi}" telah disalin ke clipboard`);
}

</script>
</body>
</html>