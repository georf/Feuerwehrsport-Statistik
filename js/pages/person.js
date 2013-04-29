$(function() {
    "use strict";

    var $edit;
    var person_id = global('person-id');

    $('#report-error').click(function() {
        checkLogin(function() {
            FormWindow.create(
                [
                    ['Radio', 'what', 'Was ist passiert?', null, null, {options: [
                        { value: 'wrong', display: 'Person ist falsch geschrieben'},
                        { value: 'other', display: 'Etwas anderes'}
                    ]}]
                ], 'Auswahl des Fehlers', 'Bitte wählen Sie das Problem aus:', {titleOk:'Weiter'})
            .open().submit(function(data) {
                var selected = data.what;

                this.close();

                if (selected == 'wrong') {
                    FormWindow.create(
                        [
                            ['Radio', 'what', 'Korrektur wählen', null, null, {options: [
                                { value: 'together', display: 'Richtige Schreibweise auswählen (für Administrator <b>VIEL</b> einfacher)'},
                                { value: 'correction', display: 'Selbst korrekte Schreibweise hinzufügen'}
                            ]}]
                        ], 'Korrektur des Fehlers', 'Bitte wählen Sie die Korrekturmethode aus:', {titleOk:'Weiter'})
                    .open().submit(function(data) {
                        var selected = data.what;

                        this.close();


                        if (selected == 'correction') {
                            FormWindow.create([
                                ['Text','firstname','Vorname',global('person-firstname')],
                                ['Text','name','Nachname',global('person-name')]
                            ], 'Namen korrigieren', 'Bitte korrigieren Sie den Namen:'
                            ).open().submit(function(data) {
                                data.reason = selected;
                                data.type = 'person';

                                data.person_id = global('person-id');

                                this.close();
                                wPost('add-error', data, function(data) {
                                    success(data, 'Der Fehlerbericht wurde gespeichert und ein Administrator informiert. In ein paar Tagen wird das Problem behoben sein.');
                                });
                            });




                        } else if (selected == 'together') {
                            wPost('get-persons', {}, function( data ) {
                                var persons = data.persons;
                                var i,l, pid = global('person-id');

                                var options = [];
                                var sex;

                                for (i=0, l=persons.length; i<l; i++) {
                                    if (persons[i].value == pid) continue;

                                    sex = (persons[i].sex == 'male') ? 'männlich' : 'weiblich';
                                    options.push({
                                        value: persons[i].id,
                                        display: persons[i].name + ', ' + persons[i].firstname + ' (' + sex + ')'
                                    });
                                }

                                FormWindow.create([
                                        ['Select', 'new_person_id', 'Richtige Person:', null, null, {options: options}]
                                    ], 'Namen korrigieren', 'Bitte wählen Sie die korrekte Person aus:'
                                ).open().submit(function(data) {
                                    data.reason = selected;
                                    data.type = 'person';

                                    data.person_id = global('person-id');

                                    this.close();
                                    wPost('add-error', data, function(data) {
                                        success(data, 'Der Fehlerbericht wurde gespeichert und ein Administrator informiert. In ein paar Tagen wird das Problem behoben sein.');
                                    });
                                });
                            });
                        }
                    });
                } else if (selected == 'other') {
                    FormWindow.create([
                        ['Textarea','description','','']
                    ], 'Fehler beschreiben', 'Bitte beschreiben Sie das Problem:')
                    .open().submit(function(data) {
                        data.reason = selected;
                        data.type = 'person';

                        data.person_id = global('person-id');

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


    sortTable('.sc_hl , .sc_hb', 3, "desc", 5);
    sortTable('.sc_zk', 2, "desc", 6);
    sortTable('.sc_gs , .sc_la , .sc_fs', 3, "desc", 6);
    sortTable('.teammates', 1, "desc", 2);
});
