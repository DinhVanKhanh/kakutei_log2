const _url_init = "redirect.php";
const _controller = "Main";
function loadList() {
    $.ajax({
        type: "POST",
        dataType: "json",
        url: _url_init,
        data: {
            controller: _controller,
            action: "loadList",
        },
        beforeSend: function () {
            $("#scLoading").show();
        },
        success: function (data) {
            $("#table_id tbody").html(data);
            resetInput();
            $("#scLoading").hide();
        },
        error: function (xhr, textStatus, errorThrown) {
            console.warn(xhr.responseText);
        },
        complete: function () {
            $("#scLoading").hide();
        },
    });
}

function changeTypeOfUpload() {
    let type = $("#typeOfUpload").val();
    let action = "";
    if (type === "file") {
        $("#folderTypeSelect").hide();
        $("#fileTypeSelect").show();
        action = "uploadLogFileTxt";
    } else if (type === "folder") {
        $("#fileTypeSelect").hide();
        $("#folderTypeSelect").show();

        action = "uploadFolder";
    }
    $("#action").val(action);
}

function selectFolder(e) {
    let theFiles = e.target.files;
    let relativePath = theFiles[0].webkitRelativePath;
    let folder = relativePath.split("/");
    let allfileName = [];
    $.each(theFiles, function (index, value) {
        allfileName[index] = value.webkitRelativePath;
    });

    $("#folder_name").val(folder[0]);
    $("#dir_folder").val(JSON.stringify(allfileName)); //get dir folder

    $("#noFolder").text(folder[0]);
    $(".file-upload").addClass("active");
}

function resetInput() {
    $("#chooseFile").val("");
    $("#chooseFolder").val("");
    $("#noFile").text("ファイルが選択されていません...");
    $("#noFolder").text("フォルダーが選択されていません...");
    $(".file-upload").removeClass("active");
}
let formUploadFile = $("#formUploadFile");
formUploadFile.submit(function (e) {
    console.log(e.target.files);
    e.preventDefault();

    if ($("#chooseFile").val() == "" && $("#chooseFolder").val() == "") {
        alert("更新のファイル・フォルダーを選択してください。");
        return;
    }

    let formData = new FormData(this);
    $.ajax({
        url: _url_init,
        type: "POST",
        controller: _controller,
        action: $("#action").val(),
        data: formData,
        dataType: "json",
        beforeSend: function () {
            $("#scLoading").show();
        },
        success: function (data) {
            // $("#table_id tbody").append(data);
            $("#table_id tbody").html(data);
            resetInput();
            $("#scLoading").hide();
        },
        error: function (xhr, textStatus, errorThrown) {
            console.warn(xhr.responseText);
        },
        complete: function () {
            $("#scLoading").hide();
        },
        cache: false,
        contentType: false,
        processData: false,
    });
});

$("#chooseFile").bind("change", function () {
    let filename = $("#chooseFile").val();
    if (/^\s*$/.test(filename)) {
        $(".file-upload").removeClass("active");
        $("#noFile").text("ファイルが選択されていません...");
    } else {
        $(".file-upload").addClass("active");
        $("#noFile").text(filename.replace("C:\\fakepath\\", ""));
    }
});
