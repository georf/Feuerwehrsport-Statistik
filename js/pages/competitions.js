$(function() {
    sortTable('.datatable', 0, "desc", [12,13], 15);

    $('td').each(function() {
        if ($(this).text() == '0') {
            $(this).css('color',$(this).css('background-color'));
        }
    });
});
