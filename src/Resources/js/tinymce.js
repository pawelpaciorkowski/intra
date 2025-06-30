$(function () {

    var lang = document.documentElement.lang;

    function myFileBrowser(callback, value, meta) {

        var type = meta.filetype;
        var cmsURL = "/file-manager?module=tiny&conf=tiny";
        if (cmsURL.indexOf("?") < 0) {
            cmsURL = cmsURL + "?type=" + type;
        }
        else {
            cmsURL = cmsURL + "&type=" + type;
        }

        var windowManagerCSS = '<style type="text/css">' +
            '.tox-dialog {max-width: 100%!important; width:97.5%!important; overflow: hidden; height:95%!important; border-radius:0.25em;}' +
            '.tox-dialog__body { padding: 0!important; }' +
            '.tox .tox-form__group{height:100%;}' +
            '.tox-dialog__body-content > div { height: 100%; overflow:hidden}' +
            '</style > ';

        window.tinymceCallBackURL = '';
        window.tinymceWindowManager = tinymce.activeEditor.windowManager;
        tinymceWindowManager.open({
            title: 'Zarządzanie plikami',
            body: {
                type: 'panel',
                items: [{
                    type: 'htmlpanel',
                    html: windowManagerCSS + '<iframe src="' + cmsURL + '"  frameborder="0" style="width:100%; height:100%"></iframe>'
                }]
            },
            buttons: [],
            onClose: function () {
                if (tinymceCallBackURL != '')
                    callback(tinymceCallBackURL, {}); //to set selected file path
            }
        });
    }

    tinymce.init({
        selector: ".tinymce",
        plugins: [
            "advlist autolink link image lists charmap print preview hr anchor pagebreak",
            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
            "save table directionality template paste"
        ],
        language: lang,
        convert_urls: false,
        content_css: "/css/alab.tinymce.min.css",
        paste_auto_cleanup_on_paste: true,
        paste_word_valid_elements: "p,strong,em,h1,h2,h3,h4,h5,h6,ol,ul,li,br,table,tbody,tr,td,a,div,img",
        paste_retain_style_properties: "color font-size font-weight",
        height: 300,
        images_upload_url: '/upload/image',
        automatic_uploads: true,
        paste_data_images: true,
        toolbar: "styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | fullpage | forecolor backcolor",
        extended_valid_elements : "iframe[src|frameborder|style|scrolling|class|width|height|name|align|title|allow|referrerpolicy|allowfullscreen]|video",
        file_picker_callback: myFileBrowser,
        paste_postprocess: function (pl, o) {
            html = o.node.innerHTML

            var tags = html.match(/(<\/?[\S][^>]*>)/gi);
            tags.forEach(function (tag) {
                html = html.replace(tag, tag.replace(/(data-.+?=".*?")|(data-.+?='.*?')|(data-[a-zA-Z0-9-]+)|(aria-[a-zA-Z0-9-]+)/g, ''));
            });

            o.node.innerHTML = html
        },
        images_upload_handler: function (blobInfo, success, failure) {
            var xhr, formData;

            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '/upload/image');

            xhr.onload = function () {
                var json;

                if (xhr.status !== 201) {
                    failure('HTTP Error: ' + xhr.status);
                    return;
                }

                json = JSON.parse(xhr.responseText);

                if (!json || typeof json.location != 'string') {
                    failure('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                success(json.location);
            };

            formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());

            xhr.send(formData);
        }
    });
});