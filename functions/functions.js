function fConfirm() {
    return confirm('Die aktuelle Version des Addons wird gelöscht und die vorhergehende Version (sofern vorhanden) wieder hergestellt! Wirklich fortfahren?');
}

function copyClipboard(obj) {

    // for Internet Explorer

    if(document.body.createTextRange) {
        var range = document.body.createTextRange();
        range.moveToElementText(div);
        range.select();
        document.execCommand("Copy");
    }
    else if(window.getSelection) {

        // other browsers

        var selection = window.getSelection();
        var range = document.createRange();
        range.selectNodeContents(div);
        selection.removeAllRanges();
        selection.addRange(range);
        document.execCommand("Copy");
    }
}
