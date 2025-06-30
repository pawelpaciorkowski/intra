/*
 * @file period.js
 *
 * @author Mariusz Rysz <mariusz.rysz@alab.com.pl>
 * @date 03.06.2020 14:24:02
 */

$(function () {
    $('.add-another-phone-item').click(function (e) {
        e.preventDefault();

        var phone = $('#' + $(this).data('list'));
        var length = phone.parents('form').find('.phone-item').length;
        let id = length;
        if (length > 0) {
            id = parseInt(phone.parents('form').find('.phone-item:last-child').attr('id').match(/\d+/)[0]) + 1;
        }

        var newWidget = phone.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, id);

        var inputs = $(newWidget).find('.col-sm-9');

        var newDiv = $('<div class="row phone-item" id="phone_phoneItems_' + id + '">' +
            '<div class="col-xs-8 phone-item-start-at"><div class="form-group"><div class="col-sm-12">' +
            inputs[0].innerHTML +
            '</div></div></div>' +
            '<div class="col-xs-3 phone-item-end-at"><div class="form-group"><div class="col-sm-12">' +
            inputs[1].innerHTML +
            '</div></div></div>' +
            '<div class="col-xs-1">' +
            '<div class="form-check">' +
            '<button class="btn btn-xs btn-danger remove-phone-item" type="button"><span class="fa fa-times"></span></button>' +
            '</div>' +
            '</div>' +
            '</div>');

        newDiv.hide();
        newDiv.appendTo(phone);
        newDiv.slideDown(100);

        var rows = $(phone).find('.row');
        if (rows.length === 2) {
            $(rows[0]).hide().removeClass('hidden').slideDown(100);
        }

        $(document).selectBind(phone);
        $(document).dateTimePickerBind(newDiv);
    });

    $(document).on('click', '.remove-phone-item', function (e) {
        e.preventDefault();

        var rows = $(this).parents('.dynamic-item-list').find('.row');
        if (rows.length === 2) {
            $(rows[0]).slideUp(100);
        }

        $(this).parents('.phone-item').slideUp(100, function () {
            $(this).remove();
        });
    });
});