$(function() {
    sortTable('.datatable', 0, "asc", null, 15);

    $('#add-team').click(function() {
        checkLogin(function() {
            var options = [
                { value: 'Team', display: 'Zusammenschluss (Team)'},
                { value: 'Feuerwehr', display: 'Einzelne Feuerwehr'}
            ];


            FormWindow.create([
                ['Text', 'name', 'Name', '', 'Vollständiger Name'],
                ['Text', 'short', 'Abkürzung', '', 'Kurzer Name (maximal 10 Zeichen)'],
                ['Select', 'type', 'Typ der Mannschaft', options[0].value, null, {options: options}]
            ], 'Mannschaft anlegen', 'Falls du ein Foto oder Icon zu dieser Mannschaft zuordnen willst, kann du es per E-Mail an den Administrator senden.')
            .open().submit(function(data) {
                this.close();

                wPost('add-team', data, function() {
                    location.reload();
                });
            });;
        });
    });
});
