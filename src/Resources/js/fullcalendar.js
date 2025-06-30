(function (jQuery) {
    const calendarEl = document.getElementById('calendar');
    const collectionPointId = calendarEl.dataset.collectionPointId;

    jQuery.calendar = function (editPrivileges) {

        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'pl',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek' + (editPrivileges ? ' edit' : '')
            },
            initialView: 'timeGridWeek',
            firstDay: 1,
            timeZone: 'local',
            droppable: true,
            events: "/api/calendar/" + collectionPointId,
            editable: false,
            hasAccess: false,
            init: function () {
                this.render()
            },
            dateClick: function (args) {
                calendar.changeView('timeGridWeek');
                calendar.gotoDate(args.dateStr);
            },
            customButtons: {
                edit: {
                    text: 'Edytuj',
                    click: function (event, button) {
                        mode.toggle();

                        if (mode.mode === 'edit') {
                            button.classList.add('fc-button-active')
                        } else {
                            button.classList.remove('fc-button-active')
                        }
                    }
                }
            }
        });
        calendar.render();

        return calendar;
    };

}(jQuery));