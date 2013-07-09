$(function() {
    "use strict";


    var sortCol = 3;
    if (global('competition-score_type')) sortCol++;

    sortTable('.sc_hb, .sc_hl', sortCol, "asc", [sortCol + 1]);
    sortTable('.sc_hb-final, .sc_hl-final', 2, "asc", 3);
    sortTable('.sc_zk', 4, "asc", 5);
    sortTable('.sc_gs', 1, "asc", [ 2,3,4,5,6,7 ]);
    sortTable('.sc_fs', 1, "asc", [ 2,3,4,5 ]);
    sortTable('.sc_la', 1, "asc", [ 2,3,4,5,6,7,8 ]);


    /*
     * Datei hinzufügen Dialog anzeigen
     */
    $('#add-file').click(function() {
        checkLogin(function() {
            $('#add-file-form').show();
            $('#add-file').hide();
        });
        return false;
    });

    /*
     * Mehrere Dateien anzeigen
     */
    var fileCounter = 0;
    $('#more-files').click(function() {

        fileCounter++;

        var $tr = $('.input-file-row').closest('tr').clone().removeClass('input-file-row');

        var $file = $tr.find('input[type=file]');
        $file.val('');
        $file.attr('name', $file.attr('name').replace(/[0-9]+/,'') + fileCounter);

        $tr.find(':checkbox').each(function() {
            var $cb = $(this);
            $cb.removeAttr('checked');
            $cb.attr('name', $cb.attr('name').replace(/[0-9]+/,'') + fileCounter);
        });
        $('.input-file-row').closest('table').append($tr);
        return false;
    });

    $('#form-excel').click(function() {
        var form = this;
        var fw = FormWindow.confirm('Excel-Tabelle erstellen', 'Das Erstellen der Excel-Tabelle kann einige Sekunden dauern. Wollen Sie die Tabelle herunterladen?', function() {
            form.submit();
        });
    });

});

