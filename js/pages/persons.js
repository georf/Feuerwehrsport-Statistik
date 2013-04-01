$(function() {
    sortTable('.datatable', 0, "asc", 6, 15);
    
    $('#add-person').click(function() {
        checkLogin(function() {
            var options = [
                { value: 'male', display: 'männlich'},
                { value: 'female', display: 'weiblich'}
            ];


            FormWindow.create([
                ['Text', 'firstname', 'Vorname', '', 'Vorname'],
                ['Text', 'name', 'Name', '', 'Nachname'],
                ['Select', 'sex', 'Geschlecht', options[0].value, null, {options: options}]
            ], 'Person hinzufügen')
            .open().submit(function(data) {
                this.close();

                wPost('add-person', data, function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            });;
        });
    });
});
