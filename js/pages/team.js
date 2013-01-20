$(function() {
    "use strict";

    // Mitglieder
    sortTable('.datatable-sort-members', 0, 'asc', 8);

    // Gruppenstafette
    sortTable('.datatable-sort-gs', 2, 'desc', 11);

    // Feuerwehrstafette
    sortTable('.datatable-sort-fs', 2, 'desc', 9);

    // Löschangriff
    sortTable('.datatable-sort-la', 2, 'desc', 12);


    $('#report-error').click(function() {
        checkLogin(function() {
            FormWindow.create(
                [
                    ['Radio', 'what', 'Was ist passiert?', null, null, {options: [
                        { value: 'together', display: 'Team ist doppelt vorhanden'},
                        { value: 'wrong', display: 'Team ist falsch geschrieben'},
                        { value: 'other', display: 'Etwas anderes'}
                    ]}]
                ], 'Auswahl des Fehlers', 'Bitte wählen Sie das Problem aus:', {titleOk:'Weiter'})
            .open().submit(function(data) {
                var selected = data.what;

                this.close();

                if (selected == 'wrong') {
                    FormWindow.create([
                        ['Text', 'name', 'Name', global('team-name'), 'Vollständiger Name'],
                        ['Text', 'short', 'Abkürzung', global('team-short'), 'Kurzer Name (maximal 10 Zeichen)'],
                        ['Text', 'website', 'Webseite', global('team-website'), 'Webseite der Mannschaft falls vorhanden'],
                        ['Select', 'type', 'Typ der Mannschaft', global('team-type'), null, {options: [
                            { value: 'Team', display: 'Zusammenschluss (Team)'},
                            { value: 'Feuerwehr', display: 'Einzelne Feuerwehr'}
                        ]}]
                    ], 'Namen korrigieren', 'Bitte korrigieren Sie den Namen:'
                    ).open().submit(function(data) {
                        data.reason = selected;
                        data.type = 'team';

                        data.team_id = global('team-id');

                        this.close();
                        wPost('add-error', data, function(data) {
                            success(data, 'Der Fehlerbericht wurde gespeichert und ein Administrator informiert. In ein paar Tagen wird das Problem behoben sein.');
                        });
                    });

                } else if (selected == 'together') {
                    wPost('get-teams', {}, function( data ) {
                        var teams = data.teams;
                        var i,l, pid = global('team-id');
                        var options = []

                        for (i=0, l=teams.length; i<l; i++) {
                            if (teams[i].value != pid) continue;
                            options.push({
                                value: teams[i].id,
                                display: teams[i].name
                            });
                        }

                        FormWindow.create([
                                ['Select', 'new_team_id', 'Richtiges Team:', null, null, {options: options}]
                            ], 'Namen korrigieren', 'Bitte wählen Sie das korrekte Team aus:'
                        ).open().submit(function(data) {
                            data.reason = selected;
                            data.type = 'team';

                            data.team_id = global('team-id');

                            this.close();
                            wPost('add-error', data, function(data) {
                                success(data, 'Der Fehlerbericht wurde gespeichert und ein Administrator informiert. In ein paar Tagen wird das Problem behoben sein.');
                            });
                        });
                    });

                } else if (selected == 'other') {
                    FormWindow.create([
                        ['Textarea','description','','']
                    ], 'Fehler beschreiben', 'Bitte beschreiben Sie das Problem:')
                    .open().submit(function(data) {
                        data.reason = selected;
                        data.type = 'team';

                        data.team_id = global('team-id');

                        this.close();
                        wPost('add-error', data, function(data) {
                            success(data, 'Der Fehlerbericht wurde gespeichert und ein Administrator informiert. In ein paar Tagen wird das Problem behoben sein.');
                        });
                    });
                }

            });
            return;
        });
    });
});
