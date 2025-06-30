$(function () {

    function reorderList(container) {
        let i = 0;

        $(container).find('input[type=hidden][name*=order]').each(function () {
            $(this).val(i++);
        })
    }

    $('.file-item-list').sortable({
        axis: "y",
        handle: ".move",
        forcePlaceholderSize: true,
        items: ".file-item",
        update: function (event, ui) {
            reorderList(event.target);
        }
    });

    $(document).on('click', '.add-another-file-item', function (e) {
        e.preventDefault();

        const fileItemList = $('#' + $(this).data('list'));
        const length = fileItemList.find('.file-item').length;
        let id = length;
        if (length > 0) {
            fileItemList.find('.file-item').each(function () {
                id = Math.max(id, parseInt($(this).attr('id').match(/\d+/)[0]) + 1);
            })
        }

        let newWidget = fileItemList.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, id);

        const inputs = $(newWidget).find('input,select,textarea');
        const widgets = $(newWidget).find('.form-group');

        const newDiv = $('<div class="file-item" id="file_fileItems_' + id + '">' +
            '    <div class="panel panel-default">' +
            '        <div class="panel-heading">' +
            '            <div class="pull-right">' +
            '                <button class="btn btn-xs btn-danger remove-file-item delete" type="button" title="Usuń załącznik" data-toggle="tooltip"><span class="fa fa-times"></span></button>' +
            '            </div>' +
            '            <a class="move btn btn-xs btn-info"><span class="fas fa-fw fa-arrows-alt"></span></a>' +
            inputs[3].outerHTML +
            '        </div>' +
            '        <div class="panel-body">' +
            widgets[0].outerHTML +
            widgets[1].outerHTML +
            '        </div>' +
            '    </div>' +
            '</div>');

        newDiv.hide();
        newDiv.appendTo(fileItemList);
        reorderList(fileItemList);
        newDiv.slideDown(100);
    });

    $(document).on('click', '.remove-file-item', function (e) {
        console.log('a');
        e.preventDefault();

        const container = $(this).closest('.file-item-list');

        $(this).closest('.file-item').slideUp(100, function () {
            $(this).remove();
            reorderList(container);
        });
    });
});