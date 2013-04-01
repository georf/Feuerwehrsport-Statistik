(function(window, $, document) {
    "use strict";

    var checkUrl = 'json.php',
        loginUrl = 'json.php?type=login',

        question = '',
        answer = 0,

        dataStorage = -1,

        login = function( callback, lastData) {
            var name = '',
                email = '',
                test = '',
                message = '',
                save = false;

            if (lastData) {
                if (lastData.name) name = lastData.name;
                if (lastData.email) email = lastData.email;
                if (lastData.test) test = lastData.test;
                if (lastData.save) save = lastData.save;
                if (lastData.message) message = '<br/><strong>' + lastData.message + '</strong>';
            }

            var w = new FormWindow([
                ['Text', 'test', question, test, 'Bitte lösen Sie die Gleichung.'],
                ['Text', 'name', 'Dein Name', name, 'Dieser Name ist nur für Rückfragen bei Problemen gedacht.'],
                ['Text', 'email', 'Deine E-Mailadresse', email, 'Diese Angabe ist freiwillig.'],
                ['Checkbox', 'save', null, save, null, {label:'Angaben in Cookie speichern.'}]
            ], 'Anmelden', 'Um das ungewollte Bearbeiten vorzubeugen, musst du einmalig einen Code eingeben. ' + message);

            w.submit(function(data) {
                w.close();

                if (parseInt(data.test, 10) === answer) {
                    var wait = new FormWindow();
                    wait.open();
                    $.post(loginUrl, data, function (retData) {
                        wait.close();
                        if (retData.login) {
                            var d = new Date();
                            d.setYear(d.getFullYear() + 1)

                            var options = {
                                    expiresAt: d,
                                    expiration: d
                                };
                            // save in cookie
                            if (data.save) {
                                $.cookies.set('name', data.name, options);
                                $.cookies.set('email', data.email, options);
                            } else {
                                $.cookies.del('name');
                                $.cookies.del('email');
                            }
                            callback.apply(null, []);
                        } else {
                            data.message = retData.message;
                            login( callback, data );
                        }
                    }, 'json');
                } else {
                    data.message = 'Die Gleichung ist nicht korrekt';
                    login( callback, data );
                }
            }).open();
        };

    window.checkLogin = function( callback ) {

        var w = new FormWindow();
        w.open();

        $.post(checkUrl, function( data ) {
            var input = {};

            w.close();

            if ($.cookies.get('name')) {
                input.name = $.cookies.get('name');
                input.save = true;

                if ($.cookies.get('email')) {
                    input.email = $.cookies.get('email');
                }
            }

            if (data.login) {
                callback.apply(null, []);
            } else {
                login( callback, input );
            }
        }, 'json');
    };


    window.wPost = function( type, data, callback ) {
        var url = 'json.php?type=' + type;
        var w = new FormWindow();
        w.open();

        $.post(url, data, function(data) {
            w.close();
            callback(data);
        }, 'json');
    };


    (function() {
        var i, a, b,
            questions = [
                ['plus', function(a, b) { return a + b; }],
                ['minus', function(a, b) { return a - b; }],
                ['mal', function(a, b) { return a * b; }]
            ];

        i = Math.round(Math.random() * 2);
        a = Math.round(Math.random() * 10);
        b = Math.round(Math.random() * 9);

        question = a + ' ' + questions[i][0] + ' ' + b;
        answer = questions[i][1](a, b);
    })();


    var getData = function() {
        var output = {};
        var div = window.document.getElementById('global-data-object');
        var attrs, i, l;

        if (!div) return output;
        attrs = div.attributes;
        for (i = 0, l = attrs.length; i < l; i++) {
            if (attrs[i].nodeName.match(/^data-/)) {
                output[attrs[i].nodeName.replace(/^data-/, '')] =
                    attrs[i].nodeValue;
            }
        }
        return output;
    };

    window.global = function(key) {
        if (dataStorage == -1) {
            dataStorage = getData();
        }
        return dataStorage[key];
    };


    window.success = function (data, message) {
        var text = 'Die Aktion war erfolgreich.';
        if (message) text += '<br/>' + message;

        if (data.success) {
            FormWindow.alert('Erfolgreich', text);
        } else {
            text = 'Die Aktion war nicht erfolgreich.';
            if (data.message) text += '<br/>' + data.message;
            FormWindow.warning('Fehler', text);
        }
    };




    window.FormScoreMembers = function( value, id, key, config ) {
        var $table = $('<table class="score-members"></table>');

        var persons = config.persons;
        var scores = config.scores;
        var wks = config.wks;

        var rows = scores.length;
        var cols = wks.length;

        var r, c, p, $select, $option, $help, $tr;


        $tr = $('<tr></tr>');
        $('<td></td>').appendTo($tr);
        for (c = 0; c < cols; c++) {
            $('<td>' + wks[c] + '</td>').appendTo($tr);
        }
        $tr.appendTo($table);


        for (r = 0; r < rows; r++) {
            scores[r].selects = [];

            $tr = $('<tr></tr>');

            // add time
            $('<td>' + scores[r]['timeHuman'] + '</td>').appendTo($tr);

            for (c = 0; c < cols; c++) {
                $select = $('<select></select>');

                $option = $('<option value="NULL"> ---- </option>');
                if (null == scores[r]['person_' + (c + 1)]) {
                    $option.attr('selected', 'selected');
                }
                $option.appendTo($select);

                for (p = 0; p < persons.length; p++) {
                    $option = $('<option value="' + persons[p].id + '">' + persons[p].name + ', ' + persons[p].firstname + '</option>');
                    if (persons[p].id == scores[r]['person_' + (c + 1)]) {
                        $option.attr('selected', 'selected');
                    }
                    $option.appendTo($select);
                }

                $('<td></td>').append($select).appendTo($tr);
                scores[r].selects[c] = $select;
            }

            $tr.appendTo($table);


            if (r > 0) {
                $tr = $('<tr><td></td></tr>');
                for (c = 0; c < cols; c++) {
                    (function( $selectTop, $selectBottom) {
                        $('<td></td>').append(
                            $('<button>↧</button>').click(function() {
                                $selectBottom.val($selectTop.val());
                            })
                        ).appendTo($tr);
                    })(scores[r-1].selects[c], scores[r].selects[c]);
                }

                $tr.insertAfter(scores[r-1].selects[0].closest('tr'));
            }
        }

        this.getElement = function() {
            return $table;
        };

        this.focus = function() {
            scores[0].selects[0].focus();
        };
        this.getValue = function() {
            var retScores = [];

            for (r = 0; r < rows; r++) {
                retScores[r] = {
                    scoreId: scores[r].id
                };
                for (c = 0; c < cols; c++) {
                    retScores[r]['person_'+(c+1)] = scores[r].selects[c].val();
                }
            }

            return retScores;
        };
    };


    window.laMembers = function( scoreId ) {
        checkLogin(function() {
            wPost('get-score-information', {scoreId: scoreId, key: 'la'}, function(data) {
                var scores = data.scores;
                wPost('get-persons', {sex: scores[0].sex}, function(data) {
                    var persons = data.persons;

                    var loeschangriff = [
                        "Maschinist",
                        "A-Länge",
                        "Saugkorb",
                        "B-Schlauch",
                        "Strahlrohr links",
                        "Verteiler",
                        "Strahlrohr rechts"
                    ];

                    var w = new FormWindow([
                        ['ScoreMembers', 'la_score', null, null, null , {persons: persons, scores: scores, wks: loeschangriff}]
                    ], 'WK zuordnen', 'Sie ordnen Personen diesen Löschangriff-Läufen zu.');
                    w.submit(function(data) {
                        w.close();

                        var ready = 0;
                        var checkReady = function() {
                            ready++;
                            if (ready >= data.la_score.length) {
                                location.reload();
                            }
                        };
                        var i;
                        for (i = 0; i < data.la_score.length; i++) {
                            data.la_score[i].key = 'la';
                            wPost('set-score-wk', data.la_score[i], function(data) {
                                checkReady();
                            });
                        }

                    }).open();

                });
            });
        });
    };


    window.fsMembers = function( scoreId ) {
        checkLogin(function() {
            wPost('get-score-information', {scoreId: scoreId, key: 'fs'}, function(data) {
                var scores = data.scores;
                wPost('get-persons', {sex: scores[0].sex}, function(data) {
                    var persons = data.persons;


                    var stafette = [
                        "Haus",
                        "Wand",
                        "Balken",
                        "Feuer"
                    ];

                    if (scores[0].sex == 'female') {
                        stafette[0] = 'Leiterwand';
                        stafette[1] = 'Hürde';
                    }

                    var w = new FormWindow([
                        ['ScoreMembers', 'fs_score', null, null, null , {persons: persons, scores: scores, wks: stafette}]
                    ], 'WK zuordnen', 'Sie ordnen Personen diesen 4x100m-Läufen zu.');
                    w.submit(function(data) {
                        w.close();

                        var ready = 0;
                        var checkReady = function() {
                            ready++;
                            if (ready >= data.fs_score.length) {
                                location.reload();
                            }
                        };
                        var i;
                        for (i = 0; i < data.fs_score.length; i++) {
                            data.fs_score[i].key = 'fs';
                            wPost('set-score-wk', data.fs_score[i], function(data) {
                                checkReady();
                            });
                        }

                    }).open();

                });
            });
        });
    };


    window.gsMembers = function( scoreId ) {
        checkLogin(function() {
            wPost('get-score-information', {scoreId: scoreId, key: 'gs'}, function(data) {
                var scores = data.scores;
                wPost('get-persons', {sex: 'female'}, function(data) {
                    var persons = data.persons;

                    var gruppen = [
                        "B-Schlauch",
                        "Verteiler",
                        "C-Schlauch",
                        "Knoten",
                        "D-Schlauch",
                        "Läufer"
                    ];


                    var w = new FormWindow([
                        ['ScoreMembers', 'gs_score', null, null, null , {persons: persons, scores: scores, wks: gruppen}]
                    ], 'WK zuordnen', 'Sie ordnen Personen diesen Gruppenstafetten-Läufen zu.');
                    w.submit(function(data) {
                        w.close();

                        var ready = 0;
                        var checkReady = function() {
                            ready++;
                            if (ready >= data.gs_score.length) {
                                location.reload();
                            }
                        };
                        var i;
                        for (i = 0; i < data.gs_score.length; i++) {
                            data.gs_score[i].key = 'gs';
                            wPost('set-score-wk', data.gs_score[i], function(data) {
                                checkReady();
                            });
                        }

                    }).open();

                });
            });
        });
    };


    $(function() {
        var $bt;

        if ($('.infochart,.helpinfo').length) {
            var tooltip = $('<div class="tooltip"></div>').hide().appendTo('body');

            $('.infochart, .helpinfo').each(function() {
                var isIn = false;
                var text = null;

                var $self = $(this);

                var show = function() {
                    var os = $self.offset();
                    var left = os.left + $self.width()/2;
                    var top = os.top + $self.height()+10;

                    if (isIn) {
                        if (!text) {
                            $.get('info/' + $self.data('file') + '.info', function(t){
                                text = t;
                                show();
                            }, 'text');
                            return;
                        }
                        tooltip.css({
                            left: left,
                            top: top
                        });
                        tooltip.html(text).show();
                    } else {
                        tooltip.hide();
                    }
                };

                $self.mouseenter(function() {
                    isIn = true;
                    show();
                });
                $self.mouseleave(function() {
                    isIn = false;
                    show();
                });
            });
        }

        $('.toToc').each(function() {
            var elem = $(this);
            var text = $(this).text();
            var id = elem.attr('id');
            var i = 0;

            if (!id) {
                id = text.replace(/[^a-zA-Z0-9]/, '');

                while ($('#' + id).length) {
                    id += i;
                    i++;
                }

                elem.attr('id', id);
            }

            $('.toc ol').append('<li><a href="#' + id + '">' + text + '</a></li>');
        });

        $('.toc-placeholder').remove();


        // generate edit buttons for group scores
        $('.sc_la td.person , .sc_fs td.person , .sc_gs td.person').mouseenter(function() {
            var $tr = $(this).closest('tr'),
                score = $tr.data('id'),
                $table = $tr.closest('table');

            $bt = $('<span class="bt user-group-properties" title="Position bearbeiten">&nbsp;</span>')
            .click(function() {
                if ($table.hasClass('sc_la')) {
                    laMembers(score);
                } else if ($table.hasClass('sc_fs')) {
                    fsMembers(score);
                } else {
                    gsMembers(score);
                }
            });

            $(this).append($bt);
        }).mouseleave(function() {
            if ($bt) {
                $bt.remove();
                $bt = null;
            }
        });


        // generate edit buttons for single team
        $('.sc_hb td.number , .sc_hl td.number').mouseenter(function() {
            var $tr = $(this).closest('tr'),
                scoreId = $tr.data('id'),
                $table = $tr.closest('table');

            $bt = $('<span class="bt user-group-properties" title="Mannschaftwertung ändern">&nbsp;</span>');

            $bt.click(function() {
                checkLogin(function() {
                    wPost('get-score-information', {scoreId: scoreId, key: 'zk'}, function( scoreData ) {
                        if (!scoreData.success) return false;

                        var personId = scoreData.score.person_id;

                        wPost('get-person', {personId: personId}, function ( person ) {
                            if (!person.success) return false;


                            var numbers = [
                                { display: 'Finale', value: -2 },
                                { display: 'Einzelstarter', value: -1 },
                                { display: 'Mannschaft 1', value: 0 },
                                { display: 'Mannschaft 2', value: 1 },
                                { display: 'Mannschaft 3', value: 2 },
                                { display: 'Mannschaft 4', value: 3 },
                                { display: 'Mannschaft 5', value: 4 }
                            ];


                            var selectScores = [];


                            var i, s;
                            var firstScores = scoreData.scores;
                            for (i=0; i < firstScores.length; i++) {
                                s = firstScores[i];
                                selectScores.push(['Select', 'score' + s.id, s.discipline + ': ' +s.timeHuman, s.team_number, null, {options: numbers}]);
                            }

                            var w = new FormWindow(
                                selectScores,
                                'Wertungszeit zuordnen', 'Sie ordnen der Person <strong>' + person.firstname + ' ' + person.name + '</strong> bei diesem Wettkampf einer Wertung zu.');
                            w.submit(function(data) {

                                var i;
                                var ready = 0;
                                var score_ready = function() {
                                    ready++;
                                    if (ready > firstScores.length) {
                                        location.reload();
                                    }
                                };

                                w.close();
                                for (i=0; i < firstScores.length; i++) {
                                    wPost('set-score-number', {
                                        scoreId: firstScores[i].id,
                                        teamNumber: data['score' + firstScores[i].id]
                                    }, score_ready);
                                }
                                score_ready();
                            }).open();
                        });
                    });
                });
            });

            $(this).append($bt);
        }).mouseleave(function() {
            if ($bt) {
                $bt.remove();
                $bt = null;
            }
        });





        // generate edit buttons for single team
        $('.sc_hb td.team , .sc_hl td.team').mouseenter(function() {
            var $tr = $(this).closest('tr'),
                scoreId = $tr.data('id'),
                $table = $tr.closest('table');

            $bt = $('<span class="bt">&nbsp;</span>');
            if ($(this).text() == '') {
                $bt.addClass('user-group-new');
                $bt.attr('title', 'Mannschaft zur Zeit zuordnen');
            } else {
                $bt.addClass('user-group-properties');
                $bt.attr('title', 'Mannschaft ändern');
            }

            $bt.click(function() {
                checkLogin(function() {
                    wPost('get-score-information', {scoreId: scoreId, key: 'zk'}, function( scoreData ) {
                        if (!scoreData.success) return false;

                        var personId = scoreData.score.person_id;

                        wPost('get-person', {personId: personId}, function ( person ) {
                            if (!person.success) return false;

                            wPost('get-teams', {personId: personId}, function( data ) {
                                var i, l, text,
                                teams = data.teams;

                                teams.splice(0,0, {value: 'NULL', display: ''});

                                var forms = [];
                                forms.push(['Select', 'teamId', 'Mannschaft für die gestartet wurde: ', scoreData.score.team_id, 'Folgende Zeiten sind davon betroffen:', {options: teams}]);

                                for(i = 0; i < scoreData.scores.length; i++) {

                                    text = scoreData.scores[i].discipline;

                                    if (scoreData.scores[i].team_number == -2) {
                                        text += ' Finale';
                                    } else if (scoreData.scores[i].team_number == -1) {
                                        text += ' Einzelstarter';
                                    }
                                    text += ': ' + scoreData.scores[i].timeHuman;


                                    forms.push(['Label', scoreData.scores[i].id, null, text]);
                                }

                                var w = new FormWindow(forms, 'Wertungszeit zuordnen', 'Sie ordnen der Person <strong>' + person.firstname + ' ' + person.name + '</strong> bei diesem Wettkampf einer Mannschaft zu.');
                                w.submit(function(data) {
                                    w.close();

                                    var ready = 0;
                                    var checkReady = function() {
                                        ready++;
                                        if (ready >= scoreData.scores.length) {
                                            location.reload();
                                        }
                                    };
                                    var i;
                                    for (i = 0; i < scoreData.scores.length; i++) {

                                        data.scoreId = scoreData.scores[i].id;
                                        wPost('set-score-team', data, function(data) {
                                            checkReady();
                                        });
                                    }
                                }).open();
                            });
                        });
                    });
                });
            });

            $(this).append($bt);
        }).mouseleave(function() {
            if ($bt) {
                $bt.remove();
                $bt = null;
            }
        });




        $('#add-link').click(function() {
            var for_id = $(this).data('for-id');
            var for_table = $(this).data('for-table');

            checkLogin(function() {
                var w = new FormWindow([
                    ['Text', 'name', 'Name', '', 'Beschreibung des Links'],
                    ['Text', 'url', 'Link', 'http://']
                ], 'Link hinzufügen');
                w.submit(function(data) {
                    this.close();

                    data['id'] = for_id;
                    data['for'] = for_table;

                    wPost('add-link', data, function() {
                        location.reload();
                    });
                }).open();
            });
            return false;
        });

        $('img.big').each(function() {
            $(this).attr('title', 'Klicken zum Vergrößern').css('cursor','pointer');
        });

        $('img.big').click(function() {
            var $img = $(this);
            var newSrc = $img.attr('src') + '&amp;big=1';
            var $big = $('<img src="' + newSrc + '" alt=""/>');

            $img.css('cursor','wait');

            var os = $img.offset();
            var left = os.left + $img.width()/2;
            var top = os.top + $img.height()/2;

            $big.load(function() {
                var w = $big.get(0).width;
                var h = $big.get(0).height;

                $img.css('cursor','pointer');

                $big.attr('title', 'Klicken zum Schließen');

                $big.css({
                    position: 'absolute',
                    top: os.top,
                    left: os.left,
                    width: $img.width(),
                    height: $img.height()
                }).fadeIn(100, function() {
                    $(this).animate({
                        top: top - h/2,
                        left: left - w/2,
                        width: w,
                        height: h
                    }).click(function() {
                        $(this).animate({
                            top: os.top,
                            left: os.left,
                            width: $img.width(),
                            height: $img.height()
                        }, function() {
                            $(this).remove();
                        });
                    });
                });
            });
            $('body').append($big.css('display', 'none'));
        });

    });

    window.sortTable = function(selector, sort, direction, not, count) {
        var opt = {
            "aaSorting": [[ sort, direction ]],
            "bAutoWidth": false,
            "bPaginate": true
        };

        if (typeof not != 'undefined' && not !== null) {
            if (!Array.isArray(not) ){
                not = [not];
            }
            opt["aoColumnDefs"] = [
                { "bSortable": false, "aTargets": not }
            ];
        }
        if (typeof count != 'undefined') {
            opt["iDisplayLength"] = count;
        }
        $(selector).dataTable(opt);
    };

})(window, $, document);
