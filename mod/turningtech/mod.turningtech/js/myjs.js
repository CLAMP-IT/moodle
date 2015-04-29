$('#id_deviceid').bind('keypress', function (e) {
            if (e.keyCode == 13 || e.which == 13) {
                $('#id_submitbutton').click();
                return false;
            }
        });
$('#txtResponseCard_1').bind('keypress', function (e) {
    if (e.keyCode == 13 || e.which == 13) {
        $('#lnkUpdateRC_1').click();
        return false;
    }
});
