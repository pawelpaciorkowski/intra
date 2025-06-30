$(function () {

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function reorderList(container) {
        let i = 0;

        $(container).find('input[type=hidden][name*=order]').each(function () {
            $(this).val(i++);
        })
    }

    $('#file-section-item-list').sortable({
        axis: "y",
        handle: ".move",
        forcePlaceholderSize: true,
        items: ".file-section-item",
        update: function (event, ui) {
            reorderList(event.target);
        }
    });

    $(document).on('click', '.add-another-file-section-item', function (e) {
        e.preventDefault();

        const fileSectionItemList = $('#' + $(this).data('list'));
        const length = fileSectionItemList.find('.file-section-item').length;
        let id = length;
        if (length > 0) {
            fileSectionItemList.find('.file-section-item').each(function () {
                id = Math.max(id, parseInt($(this).attr('id').match(/\d+/)[0]) + 1);
            })
        }

        let filePrototype = fileSectionItemList.attr('data-file-prototype');
        filePrototype = filePrototype.replace(/__name___files/g, id + '_files');
        filePrototype = filePrototype.replace(/\[__name__]\[files]/g, '[' + id + '][files]');

        let newWidget = fileSectionItemList.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, id);

        const inputs = $(newWidget).find('input,select,textarea');
        const widgets = $(newWidget).find('.form-group');

        const newDiv = $('<div class="file-section-item" id="fileSection_fileSectionItems_' + id + '">' +
            '    <div class="panel panel-default panel">' +
            '        <div class="panel-heading">' +
            '            <div class="pull-right">' +
            '                <button class="btn btn-xs btn-danger remove-file-section-item delete" type="button" title="Usuń sekcję" data-toggle="tooltip">' +
            '                    <span class="fa fa-times"></span>' +
            '                </button>' +
            '            </div>' +
            '            <a class="move btn btn-xs btn-info"><span class="fas fa-fw fa-arrows-alt"></span></a>' +
            inputs[2].outerHTML +
            '        </div>' +
            '        <div class="panel-body">' +
            '            <div class="col-xs-12 file-section-item-title">' +
            widgets[0].outerHTML +
            '            </div>' +
            '            <div class="col-xs-12 file-section-item-short-text">' +
            widgets[1].outerHTML +
            '            </div>' +
            '            <div class="col-xs-12 file-section-item-files">' +
            '                <div class="row">' +
            '                    <div class="col-sm-3"></div>' +
            '                    <div id="file-item-list-' + id + '-0" class="col-sm-9 file-item-list" data-type="file" data-prototype="' + escapeHtml(filePrototype) + '">' +
            '                    </div>' +
            '                </div>' +
            '                <div class="form-group">' +
            '                    <div class="col-sm-3"></div>' +
            '                    <div class="col-sm-9">' +
            '                        <button class="btn btn-warning add-another-file-item" data-list="file-item-list-' + id + '-0" type="button" title="Dodaj załącznik" data-toggle="tooltip"><span class="fa fa-plus"></span> Dodaj załącznik</button>' +
            '                    </div>' +
            '                </div>' +
            '            </div>' +
            '        </div>' +
            '    </div>' +
            '</div>');

        newDiv.hide();
        newDiv.appendTo(fileSectionItemList);
        reorderList(fileSectionItemList);
        newDiv.slideDown(300);

        $(newDiv).find('.file-item-list').sortable({
            axis: "y",
            handle: ".move",
            forcePlaceholderSize: true,
            items: ".file-item",
            update: function (event, ui) {
                reorderList(event.target);
            }
        });

        tinymce.EditorManager.execCommand('mceAddEditor', false, $(newDiv).find('textarea').attr('id'));
    });

    $(document).on('click', '.remove-file-section-item', function (e) {
        e.preventDefault();

        const container = $(this).closest('.file-section-item-list');

        $(this).closest('.file-section-item').slideUp(300, function () {
            $(this).remove();
            reorderList(container);
        });
    });
});