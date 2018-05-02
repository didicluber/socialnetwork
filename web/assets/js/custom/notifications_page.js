$(document).ready(function () {

    var ias = jQuery.ias({
        container: '#notifications .box-content',
        item: '.notification-item',
        pagination: '#notifications .pagination',
        next: '#notifications .pagination .next_link',
        triggerPageThreshold: 5
    });

    ias.extension(new IASTriggerExtension({
        text: 'voir plus de notifications',
        offset: 3
    }));

    ias.extension(new IASSpinnerExtension({
        src: URL+'/../assets/images/ajax-loader.gif' // modificar fuera de app_dev.php
    }));

    ias.extension(new IASNoneLeftExtension({
        text: 'Plus de notifications'
    }));

    ias.on('ready', function(event) {

    });

    ias.on('rendered', function(event) {

    });

});
