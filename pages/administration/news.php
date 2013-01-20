<?php

if (Check::post('title', 'id', 'date', 'content')) {
    if (Check::isIn($_POST['id'], 'news')) {
        $db->updateRow('news', $_POST['id'], array(
            'title' => htmlspecialchars($_POST['title']),
            'content' => htmlspecialchars($_POST['content']),
            'date' => $_POST['date'],
        ));
        
        echo '---SUCCESS---';
        exit();
    } elseif ($_POST['id'] == 'new') {
        $db->insertRow('news', array(
            'title' => htmlspecialchars($_POST['title']),
            'content' => htmlspecialchars($_POST['content']),
            'date' => $_POST['date'],
        ));
        
        echo '---SUCCESS---';
        exit();
    }
    
    echo '---FAIL---';
    exit();
}
?>

<h1>News</h1>

<script type="text/javascript" src="js/jhtmlarea/scripts/jquery-ui-1.7.2.custom.min.js"></script>
<link rel="Stylesheet" type="text/css" href="js/jhtmlarea/style/jqueryui/ui-lightness/jquery-ui-1.7.2.custom.css" />

<script type="text/javascript" src="js/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript">
    var tinyoptions = {
        // Location of TinyMCE script
        script_url : 'js/tiny_mce/tiny_mce.js',

        // General options
        theme : "advanced",
        plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
        content_css : "styling/css/style.css"
    };

    $.ui.dialog.defaults.bgiframe = true;
    $(function () {
        $(".jarea-dialog").dialog({
            width: 1050, autoOpen: false,
            height: 650,
            open: function (evt, ui) {
                $(this).find("textarea").tinymce(tinyoptions);
            }
        });

        $(".dialog-open").click(function () {
            var id = $(this).data('id');
            $('#dialog-content-'+id).dialog('open');
            $('#dialog-content-'+id).find('button').click(function() {
                var content = $('#dialog-content-'+id).find('textarea').html();
                var title = $('#dialog-content-'+id).find('input[name="title"]').val();
                var date = $('#dialog-content-'+id).find('input[name="date"]').val();
               
                $.post('?page=administration&admin=news', {
                    content: content,
                    title: title,
                    date: date,
                    id: id
                }, function( ret ) {
                    if (ret.match(/---SUCCESS---/)) {
                        window.location.reload();
                    } else {
                        alert('FAIL');
                    }
                });
            
           });
       });
       

   });
</script>


<?php

$news = $db->getRows("
    SELECT *
    FROM `news`
    ORDER BY `date` DESC
");


echo '<button class="dialog-open" data-id="new">Bearbeiten</button>';

echo '<table class="table">';
foreach ($news as $new) {
    echo '<tr>';
    echo '<td>'.gDate($new['date']).'</td>';
    echo '<td>'.$new['title'].'</td>';
    echo '<td><button class="dialog-open" data-id="'.$new['id'].'">Bearbeiten</button></td>';
    echo '<td>'.substr(strip_tags(htmlspecialchars_decode($new['content'])),0,300).'</td>';
    echo '</tr>';
}
echo '</table>';

$news[] = array('id' => 'new', 'title' => 'Titel', 'date' => date('Y-m-d'), 'content' => '<br/>');

foreach ($news as $new) {
    echo '<div id="dialog-content-'.$new['id'].'" title="'.$new['title'].'" style="display: none" class="jarea-dialog">';
    echo '<input type="text" value="'.$new['date'].'" name="date" style="width:100px">';
    echo '<input type="text" value="'.$new['title'].'" name="title" style="width:300px">';
    echo '<textarea style="width:90%;height:450px;" id="news-content-'.$new['id'].'">'.$new['content'].'</textarea>';
    echo '<button>Speichern</button>';
    echo '</div>';
}
