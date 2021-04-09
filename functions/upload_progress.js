function upload_addon() {
    var bar = $('#bar');
    var percent = $('#percent');
    var button = $('#submit_btn');

    $('#u').ajaxForm({
        beforeSubmit: function () {
            document.getElementById("progress_div").style.display = "block";
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
            button.prop('disabled', false);
        },

        uploadProgress: function (event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal)
            percent.html(percentVal);
            button.prop('disabled', true);
        },

        success: function () {
            var percentVal = '100%';
            bar.width(percentVal)
            percent.html(percentVal);
        },

        complete: function (xhr) {
            if (xhr.responseText) {
                document.getElementById("xhr_output").innerHTML = xhr.responseText;
            }
        }
    });
}