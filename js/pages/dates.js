$(function() {
    var addDate = function(places, events, data) {
        var p_options = [],
            e_options = [],
            i = 0;

        p_options.push({
                value: 'NULL',
                display: '----'
            });
        for (i = 0; i < places.length; i++) {
            p_options.push({
                value: places[i].id,
                display: places[i].name
            });
        }

        e_options.push({
                value: 'NULL',
                display: '----'
            });
        for (i = 0; i < events.length; i++) {
            e_options.push({
                value: events[i].id,
                display: events[i].name
            });
        }

        if (!data.date) {
            var d = new Date();
            var yyyy = d.getFullYear().toString();
            var mm = (d.getMonth()+1).toString();
            var dd  = d.getDate().toString();
            data.date = yyyy + '-' + (mm[1]?mm:"0"+mm[0]) + '-' + (dd[1]?dd:"0"+dd[0]);
        }
        if (!data.place_id) data.place_id = p_options[0].value;
        if (!data.event_id) data.event_id = e_options[0].value;
        if (!data.description) data.description = '';
        if (!data.name) data.name = '';

        FormWindow.create([
            ['Date', 'date', 'Datum', data.date ],
            ['Text', 'name', 'Name', data.name, 'Name der Veranstaltung'],
            ['Select', 'place_id', 'Ort', data.place_id, null, {options: p_options}],
            ['Select', 'event_id', 'Typ', data.event_id, null, {options: e_options}],
            ['Textarea', 'description', 'Beschreibung', data.description, 'Eine kurze Beschreibung der Veranstaltung'],
            ['Label', '', 'Disziplinen', 'Bitte wähle die angebotenen Disziplinen aus:'],
            ['Checkbox', 'fs', null, data.fs, null, {label:'Feuerwehrstafette'}],
            ['Checkbox', 'hb', null, data.hb, null, {label:'Hindernisbahn'}],
            ['Checkbox', 'hl', null, data.hl, null, {label:'Hakenleitersteigen'}],
            ['Checkbox', 'gs', null, data.gs, null, {label:'Gruppenstafette'}],
            ['Checkbox', 'la', null, data.la, null, {label:'Löschangriff'}]
        ], 'Termin hinzufügen')
        .open().submit(function(data) {
            this.close();

            if (data.name == '' || data.description == '') {
                addDate(places, events, data);
                return;
            }

            wPost('add-date', data, function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        });
    };

    $('#add-date').click(function() {
        checkLogin(function() {
            wPost('get-places', {}, function(data) {
                var places = data.places;
                wPost('get-events', {}, function(data) {
                    var events = data.events;
                    addDate(places, events, {});
                });
            });
        });
    });

    sortTable('.datatable', 0, "asc", 4, 15);
});


