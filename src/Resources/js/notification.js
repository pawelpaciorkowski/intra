var notification = {
    type: 'bootstrap',

    init: function () {

        if (!("Notification" in window)) {
        } else if (Notification.permission === "granted") {
            this.type = 'native';
        } else if (Notification.permission !== "denied") {
            var self = this;
            Notification.requestPermission(function (permission) {
                if (permission === "granted") {
                    self.type = 'native';
                }
            });
        }

        return this;
    },

    _: function (type, message) {
        if (this.type === 'native') {
            this.native(type, message);
        } else {
            this.bootstrap(type, message);
        }
    },

    native: function (type, message) {
        new Notification(message, {
            // body: message,
            icon: '/favicon-196.png'
        });
    },

    bootstrap: function (type, message) {
        var icon = '';
        var className = '';

        switch (type) {
            case 'info':
                icon = 'fa-info';
                className = 'info';
                break;
            case 'notice':
                icon = 'fa-check';
                className = 'success';
                break;
            case 'error':
                icon = 'fa-exclamation-circle';
                className = 'danger';
                break;
        }

        $.notify({
            // options
            icon: 'fas ' + icon,
            message: message
        }, {
            // settings
            type: className,
            allow_dismiss: true,
            timer: 1000,
            animate: {
                enter: 'notification fadeInDown',
                exit: 'notification fadeOutRight'
            },
            template: '<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert">' +
                '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
                '<span data-notify="icon"></span> ' +
                '<span data-notify="title">{1}</span> ' +
                '<span data-notify="message" class="message">{2}</span>' +
                '<div class="progress" data-notify="progressbar">' +
                '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;"></div>' +
                '</div>' +
                '<a href="{3}" target="{4}" data-notify="url"></a>' +
                '</div>'
        });
    }
}.init();

(function (jQuery) {
    jQuery.notification = function (type, string) {
        return notification._(type, string);
    }
}(jQuery));

if (notificationList !== null) {
    // loop on every notifications
    for (var i in notificationList) {
        $.notification(notificationList[i].type, notificationList[i].message);
    }
}
