<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="./assets/css/style.css" crossorigin="anonymous">
	<!-- Font awesome CSS -->
	<!-- <link href="./assets/fontawesome/css/all.css" rel="stylesheet"> -->
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css" rel="stylesheet" />
	<!--load all styles -->
	<title>Kakutei-Log2Database</title>
</head>

<body>
	<h2 class="heading">データベースへログを保存するツール</h2>
	<img id="scLoading" style="position:absolute; right:42%; top:500px; z-index:9999; display:none; background-color:#333; padding:2%;" src="assets/img/icon_loading.gif" />

	<div class="section-head">
		<!-- <div class="file-icon"><i class="fa-solid fa-folder"></i></div> -->
		<select class="custom-select custom-select-lg mb-3" aria-label=".form-select-lg example" id="typeOfUpload" onchange="changeTypeOfUpload()">
			<option value="file" selected>&#xf15b; ファイル</option>
			<option value="folder">&#xf07b; フォルダー</option>
		</select>
	</div>

	<div class="section-top">
		<!-- <form method="POST" action="redirect.php" enctype="multipart/form-data" id="formUploadFile"> -->
		<form method="POST" action="" enctype="multipart/form-data" id="formUploadFile">
			<div class="file-upload">
				<div class="file-select" id="fileTypeSelect">
					<div class="file-select-button" id="fileName">ファイルを選択</div>
					<div class="file-select-name" id="noFile">ファイルが選択されていません...</div>
					<input type="file" name="fileTxtUpload" id="chooseFile">
				</div>
				<div class="file-select" id="folderTypeSelect">
					<div class="file-select-button" id="folderName">フォルダーを選択</div>
					<div class="file-select-name" id="noFolder">フォルダーが選択されていません...</div>
					<input type="file" name="folderUpload[]" id="chooseFolder" multiple directory="" webkitdirectory="" moxdirectory="" onchange="selectFolder(event)" />
					<input type="hidden" name="folder_name" id="folder_name" value="" />
					<input type="hidden" name="dir_folder" id="dir_folder" value="" />
				</div>
				<div class="file-submit">
					<button type="submit" class="btn btn-primary">実行</button>
					<input type="hidden" name="controller" value="Main">
					<!-- default action is import file -->
					<input type="hidden" name="action" id="action" value="uploadLogFileTxt">
				</div>
			</div>
		</form>
	</div>

	<!-- <hr style=" border-top: 3px solid red;" /> -->

	<div class="section-bot">
		<table id="table_id" class="table table-striped">
			<thead>
				<tr>
					<th>ファイル</th>
					<th>処理開始</th>
					<th style="width: 13%;">登録件数</th>
					<th style="width: 10%;">エラー件数</th>
					<th>ログファイル</th>
					<th style="width: 30%;">備考</th>
				</tr>
			</thead>
			<tbody>

			</tbody>
		</table>
	</div>

</body>
<footer>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
	<script src="./assets/js/main.js"></script>

	<script>
		$(document).ready(function() {
			loadList();
		});
	</script>
</footer>

</html>