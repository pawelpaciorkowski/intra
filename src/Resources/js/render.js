var render = {
    templatesContent: {},
    loadTemplate: function (template) {

        $.ajax({
            url: '/js/template/' + template,
            cache: false,
            async: false,
            success: function (response) {
                render.templatesContent[template] = response;
            }
        });
    },
    parseData: function (template, data, suffix) {

        if (!(data instanceof Array)) {
            data = [data];
        }

        var selectedTemplate = this.templatesContent[template];

        var render = this;

        // remove new lines
        selectedTemplate = selectedTemplate.replace(/[\n\r]/gm, '');

        var returnData = '';

        $.each(data, function (key, value) {
            var rowTemplate = selectedTemplate;
            $.each(value, function (key, value) {

                if (suffix !== undefined) {
                    key = suffix + '.' + key;
                }

                var re = new RegExp('{{ (' + key + ')\\|?([a-z0-9]*) }}', "g");
                rowTemplate = rowTemplate.replace(re, function (match, skey, modifier) {
                    if (modifier) {
                        return $[modifier](value);
                    }
                    return value;
                });

                if (key && value !== 'false' && (!Array.isArray(value) || (Array.isArray(value) && value.length > 0))) {
                    if (!value) {
                        re = new RegExp('({%\ +if\ not\ +' + key + '\ +%})(((?!endif).)*)({%\ +endif\ +%})', "g");
                    } else {
                        re = new RegExp('({%\ +if\ +' + key + '\ +%})(((?!endif).)*)({%\ +endif\ +%})', "g");
                    }
                    rowTemplate = rowTemplate.replace(re, '$2');

                    re = new RegExp('{%\ +for\ +([^\ ]+)\ +in\ +' + key + '\ +%}(((?!endfor).)*)({%\ +endfor\ +%})', "g");
                    var iterateTemplate;
                    while (iterateTemplate = re.exec(rowTemplate)) {

                        if (suffix !== 'file' || !(key in render.templatesContent)) {
                            render.templatesContent[key] = iterateTemplate[2];
                        }
                        rowTemplate = rowTemplate.replace(iterateTemplate[0], render.parseData(key, value, iterateTemplate[1]));
                    }
                }
            });

            // Remove unnecessary IF statement
            var re = new RegExp('{%\ +if\ +[^%]+\ +%}(((?!endif).)*){%\ +endif\ +%}', "g");
            rowTemplate = rowTemplate.replace(re, '');

            // Remove unnecessary ITERATE statement
            re = new RegExp('{%\ +for\ +[^%]+\ +%}(((?!endfor).)*){%\ +endfor\ +%}', "g");
            rowTemplate = rowTemplate.replace(re, '');

            // translated texts
            rowTemplate = rowTemplate.replace(/\[\[\ ([^\]]+)\ \]\]/g, function (match, contents, offset, s) {
                    return $.i18n(contents);
                }
            );

            returnData += rowTemplate;
        });

        return returnData;
    },
    _: function (template, data) {
        if (!this.templatesContent[template]) {
            this.loadTemplate(template);
        }

        return this.parseData(template, data);
    }
};

(function (jQuery) {
    jQuery.render = function (template, string) {
        return render._(template, string);
    }
}(jQuery));
