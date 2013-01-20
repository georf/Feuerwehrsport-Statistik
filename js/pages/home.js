$(function() {
    $('a').each(function(i, elem) {
        var e = $(elem);
        var href = e.attr('href');
        var text = '';
        if (href && href.match(/^#/)) {
            text = $(href).text();
            if (text) {
                e.attr('title', 'Springe zu Abschnitt »' + text + '«');
            }
        }
    });
});
