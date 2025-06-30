$(function () {

    var definedLang = document.documentElement.lang;

    if (typeof moment !== 'undefined') {
        moment.locale(definedLang);
    }

    $("body").tooltip({
        selector: '[data-toggle="tooltip"]',
        delay: 400
    });

    $(".select-box").each(function () {
        var target = $(this).data('target');
        var visibleOptions = $(this).data('visible');

        var select = $(this).find("select");
        var selectedValue = parseInt(select.val());
        var isVisible = visibleOptions.includes(selectedValue);

        $('[data-handler=' + target + ']').each(function () {
            var element = $(this);

            if (isVisible) {
                element.show();
            } else {
                element.hide();
            }
        });

        select.on('change', function () {
            var selectedValue = parseInt($(this).val());
            var isVisible = visibleOptions.includes(selectedValue);

            $('[data-handler=' + target + ']').each(function () {
                var element = $(this);

                if (isVisible) {
                    element.stop().slideDown(200);
                } else {
                    element.stop().slideUp(200);
                }
            });

        });
    });

    $('.switch-box').find('input[type=checkbox]').each(function () {
        var target = $(this).parents('.switch-box').data('target');

        var checked = $(this).prop("checked");
        if ($(this).parents('.switch-box').hasClass('switch-inverted')) {
            checked = !checked;
        }

        $('[data-handler=' + target + ']').each(function () {
            var element = $(this);
            var toHide = !checked;

            if (element.hasClass('inverted')) {
                toHide = !toHide;
            }

            if (toHide) {
                element.hide();
            } else {
                element.show();
            }
        });

        $(this).on('change', function () {

            var checked = $(this).prop("checked");
            var bootstrapSwitch = $(this).parents('.switch-box');

            if (bootstrapSwitch.hasClass('switch-inverted')) {
                checked = !checked;
            }

            if (bootstrapSwitch.attr('data-switch')) {
                var switchTarget = bootstrapSwitch.data('switch-target');
                var mode = bootstrapSwitch.data('switch').toString() === 'true';

                var container = $('[data-container="' + switchTarget + '"]');
                container.val(mode === bootstrapSwitch.hasClass('inverted') ? 'true' : 'false');
                container.trigger('change');
            }

            $('[data-handler=' + target + ']').each(function () {
                var element = $(this);
                var toHide = !checked;

                if (element.hasClass('inverted')) {
                    toHide = !toHide;
                }

                if (toHide) {
                    element.stop().slideUp(200);
                    element.find('input[type=checkbox]').prop("checked", false);
                } else {
                    element.stop().slideDown(200);
                }
            });
        });
    });

    if (jQuery().datetimepicker) {
        jQuery.fn.extend({
            dateTimePickerBind: function (handler) {
                $(handler).find('.datetime-picker').datetimepicker({
                    locale: definedLang
                });

                $(handler).find('.date-picker').datetimepicker({
                    format: 'L',
                    locale: definedLang
                });

                $(handler).find('.date-month-picker').datetimepicker({
                    viewMode: 'months',
                    format: 'MM.YYYY',
                    locale: definedLang
                });

                $(handler).find('.date-time-picker').datetimepicker({
                    format: 'HH:mm',
                    locale: definedLang
                });
            }
        });

        $(document).dateTimePickerBind(document);
    }

    var menu = {
        recalculate: function () {
            var sideMenu = $('.sidebar-nav .side-menu');

            var appVersion = $('.app-version');
            var appVersionHeight = 0;

            if ($(appVersion).length) {
                appVersionHeight = $(appVersion).outerHeight();
            }

            var top = $(window).scrollTop();
            if (top < 0) {
                top = 0;
            }

            sideMenu.css('left', -$(window).scrollLeft())

            if ($(window).width() >= 768) {
                var scroll = top;

                sideMenu.css('position', 'fixed');
                var newPositionSideMenu = 0;
                if (sideMenu.outerHeight() < $(window).height()) {
                    if (scroll - appVersionHeight < 0) {
                        newPositionSideMenu = appVersionHeight - scroll;
                    } else if (sideMenu.outerHeight() > $(window).height() - appVersionHeight) {
                        newPositionSideMenu = $(window).height() - sideMenu.outerHeight();
                    }
                } else {
                    if (sideMenu.outerHeight() - scroll < $(window).height() - appVersionHeight) {
                        newPositionSideMenu = $(window).height() - sideMenu.outerHeight();
                    } else {
                        newPositionSideMenu = -scroll + appVersionHeight;
                    }
                }
                sideMenu.css('top', newPositionSideMenu);
            } else {
                sideMenu.css('position', 'relative');
                sideMenu.css('top', 0);
            }
        },
        init: function () {
            var win = this;
            $(window).on('load resize scroll', function () {
                win.recalculate();
            });
        }
    };

    $('#side-menu').metisMenu({
        onTransitionStart: function () {
        },
        onTransitionEnd: function () {
        }
    }).on('shown.metisMenu', function (event) {
        $(window).trigger('resize');
    }).on('hidden.metisMenu', function (event) {
        $(window).trigger('resize');
    });

    $('[data-toggle="tooltip"]').tooltip({
        delay: { "show": 500, "hide": 100 },
        html: true
    });

    $(document).on('click', 'button.row-delete', function (e) {
        e.preventDefault();
        var action = $(this).parents('tr,li,.panel-box,.data-delete').data('delete-action');
        var name = $(this).parents('tr,li,.panel-box,.data-delete').data('name');
        var subject = $(this).parents('tr,li,.panel-box,.data-delete').data('subject');
        var del = $('#delete');

        if (subject) {
            del.find('.subject').text(subject);
        }

        del.data('delete-action', action).modal('show');
        del.find('.name').text(name);
    });

    $(document).on('click', 'button.row-copy', function (e) {
        e.preventDefault();
        var action = $(this).parents('tr,li,.panel-box,.data-delete').data('copy-action');
        var name = $(this).parents('tr,li,.panel-box,.data-delete').data('name');
        var del = $('#copy');

        del.data('copy-action', action).modal('show');
        del.find('.name').text(name);
    });

    $('#switch-user-link').on('click', function (e) {
        e.preventDefault();
        $('#switch-user-modal').modal('show');
    });

    $('#_switch_user').on('changed.bs.select', function () {
        $(this).parents('form').submit();
    });

    $('#row-delete-perform').click(function () {
        location.href = $('#delete').data('delete-action');
        return false;
    });

    $('#row-copy-perform').click(function () {
        location.href = $('#copy').data('copy-action');
        return false;
    });

    $('.alert-dismissible').delay(5000).animate({
        'opacity': 0,
        'height': 0,
        'padding-top': 0,
        'padding-bottom': 0,
        'margin-top': 0,
        'margin-bottom': 0
    }, 600, function () {
        $(this).hide();
    });

    $('.pagination input[data-action]').on('keyup', function (event) {
        if (event.keyCode === 13) {
            var url = $(this).data('link');
            var page = parseInt($(this).val());
            if (!isNaN(page)) {
                location.href = url.replace('/0', '/' + page);
            }

            $(this).effect("highlight", { color: '#ebcccc' })
        }
    });

    $(window).bind("load resize", function () {
        var navbarCollapse = $('div.navbar-collapse');
        var heightOffset = $('nav.navbar').height();
        var width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 769) {
            $(navbarCollapse).addClass('collapse');
        } else {
            $(navbarCollapse).removeClass('collapse');
        }

        var height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1 - heightOffset;
        var currentHeight = parseInt($("#page-wrapper").css("min-height"));
        var sidebarHeight = $('.sidebar').height() - heightOffset;
        var menuHeight = $('#side-menu').height();

        var conntentHeight = Math.max(currentHeight, sidebarHeight, menuHeight);

        if (height > conntentHeight) {
            $("#page-wrapper").css("min-height", (height) + "px");
            $(".page-content").css("min-height", (height) + "px");
        } else {
            $("#page-wrapper").css("min-height", (conntentHeight) + "px");
            $(".page-content").css("min-height", (conntentHeight) + "px");
        }

        menu.init();
    });

    if (typeof $.fn.nestedSortable !== 'undefined') {
        if ($('.alab-sortable-tree').length) {
            const handler = $('.alab-sortable-tree').data('handler');

            $('.alab-sortable-tree > ol').nestedSortable({
                handle: '.move',
                items: 'li[data-type='+handler+']',
                toleranceElement: '> .move',
                maxLevels: 10,
                opacity: .6,
                revert: 250,
                tabSize: 25,
                tolerance: 'pointer',
                forcePlaceholderSize: true,
                placeholder: 'placeholder',
                relocate: function () {
                    $.ajax({
                        url: "/api/" + handler + "/order",
                        method: 'PATCH',
                        dataType: 'json',
                        data: {
                            'tree': JSON.stringify($('.alab-sortable-tree > ol').nestedSortable('toArray', {
                                startDepthCount: 0,
                                excludeRoot: 1
                            }))
                        }
                    });
                }
            });
        }

        if ($('.alab-sortable-box').length) {
            $('.alab-sortable-box tbody').sortable({
                handle: '.move',
                items: "tr",
                axis: "y",
                containment: "parent",
                update: function (event, ui) {
                    let sort = [];
                    $(this).find('tr').each(function () {
                        sort.push($(this).data('id'));
                    })
                    $.ajax({
                        url: "/api/box/order",
                        method: 'PATCH',
                        dataType: 'json',
                        data: {
                            'data': sort
                        }
                    });
                }
            })
        }

        if ($('.alab-sortable-carousel').length) {
            $('.alab-sortable-carousel tbody').sortable({
                handle: '.move',
                items: "tr",
                axis: "y",
                containment: "parent",
                update: function (event, ui) {
                    let sort = [];
                    $(this).find('tr').each(function () {
                        sort.push($(this).data('id'));
                    })
                    $.ajax({
                        url: "/api/carousel/order",
                        method: 'PATCH',
                        dataType: 'json',
                        data: {
                            'data': sort
                        }
                    });
                }
            })
        }

        if ($('.alab-sortable-iso-files').length) {
            $('.alab-sortable-iso-files > ol').sortable({
                handle: '.move',
                items: "li[data-type=iso-file]",
                axis: "y",
                containment: "parent",
                update: function (event, ui) {
                    let sort = [];
                    $(this).find('li').each(function () {
                        sort.push($(this).data('id'));
                    })
                    $.ajax({
                        url: "/api/iso-file/order",
                        method: 'PATCH',
                        dataType: 'json',
                        data: {
                            'data': sort
                        }
                    });
                }
            })

            $(document).on('click', '[data-toggle="iso-files"]', function(e) {
                e.preventDefault();

                let files = $(this).closest('li').find('> [data-type="iso-files"]')

                if ($(files).is(':visible')) {
                    $(files).stop().slideUp()
                    $(this).html('pokaż pliki')
                } else {
                    $(files).stop().slideDown()
                    $(this).html('ukryj pliki')
                }
            })
        }

        if ($('.alab-sortable-page-files').length) {
            $('.alab-sortable-page-files > ol').sortable({
                handle: '.move',
                items: "li[data-type=page-file]",
                axis: "y",
                containment: "parent",
                update: function (event, ui) {
                    let sort = [];
                    $(this).find('li').each(function () {
                        sort.push($(this).data('id'));
                    })
                    $.ajax({
                        url: "/api/page-file/order",
                        method: 'PATCH',
                        dataType: 'json',
                        data: {
                            'data': sort
                        }
                    });
                }
            })
        }

        if ($('.alab-sortable-list').length) {

            var sortableList = {
                list: null,

                save: function () {
                    let userData = [];
                    let self = this;

                    $(self.list).find('li').each(function () {
                        if ($(this).data('id')) {
                            userData.push({
                                'id': $(this).data('id'),
                                'isInclude': $(this).find('input[type=checkbox]').is(':checked')
                            });
                        }
                    });

                    $.ajax({
                        url: "/api/file-export",
                        method: 'PATCH',
                        dataType: 'json',
                        data: {
                            'data': userData
                        },
                        error: function (response) {
                            $.notification('error', 'Błąd zapisu.');
                        }
                    });

                },

                init: function (list) {
                    this.list = list;
                    let self = this;

                    $(self.list).find('> ol').nestedSortable({
                        handle: '.move',
                        items: 'li',
                        toleranceElement: '> .move',
                        maxLevels: 1,
                        opacity: .6,
                        revert: 250,
                        tabSize: 100000,
                        tolerance: 'pointer',
                        forcePlaceholderSize: true,
                        placeholder: 'placeholder',
                        relocate: function () {
                            self.save();
                        }
                    });

                    $(list).find('input').on('change', function () {
                        self.save();
                    })

                    return this;
                }
            }.init('.alab-sortable-list');
        }
    }

    $(document).on('change', '.btn-file :file', function () {
        var input = $(this),
            numFiles = input.get(0).files ? input.get(0).files.length : 1,
            label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
        input.trigger('fileselect', [numFiles, label]);
    });

    $(document).ready(function () {
        $(document).on('fileselect', '.btn-file :file', function (event, numFiles, label) {

            var input = $(this).parents('.input-group').find(':text'),
                log = numFiles > 1 ? numFiles + ' files selected' : label;

            if (input.length) {
                input.val(log);
            } else {
                if (log) {
                    alert(log);
                }
            }

        });
    });

    $('.dropdown-column-select label').on('click', function (event) {
        event.stopPropagation();
    });

    $('.dropdown-column-select input').on('change', function () {
        var columnName = $(this).data('column');
        var tableName = $(this).parents('table').data('table');
        var status = $(this).prop("checked") ? 1 : 0;

        if (status) {
            $(this).parents('table').find('th[data-column=' + columnName + '],td[data-column=' + columnName + ']').fadeIn(200);
        } else {
            $(this).parents('table').find('th[data-column=' + columnName + '],td[data-column=' + columnName + ']').fadeOut(200);
        }

        $.ajax({
            url: "/api/user-table-column-visible",
            method: 'POST',
            dataType: 'json',
            data: {
                'table': tableName,
                'column': columnName,
                'status': status
            },
            success: function (response) {
                if (!response.status) {
                    alert('column visibility error');
                }
            }
        });

        return false;
    });

    $('.mr-filter-button').on('click', function () {
        if ($(this).hasClass('active')) {
            $('.mr-filter-row').hide();
        } else {
            $('.mr-filter-row').show(10, function () {
                $('.mr-filter-row').find('input:visible:eq(0)').focus();
            });
        }
    });

    $('.mr-filter-reset').on('click', function () {
        var inputs = $(this).parents('.mr-filter-row').find('input[type=text]');
        $(inputs).val('');
    });

    $('form:not(.no-wait-button)').on('submit', function (e) {

        var form = $(this);

        if (($(form).find('button[type=submit]').length) && (!$(form).data('submited'))) {
            e.preventDefault();

            var button = $(form).find('button[type="submit"]');
            // Prevent button size change (if content change)
            button.css('width', button.outerWidth());
            button.css('height', button.outerHeight());
            // button.css('margin-left', (button.outerWidth() / 2) - 16);

            $(form).data('submited', true);
            // $(button).html('<i class="fas fa-refresh fa-spin fa-fw"></i> Zapisuję...');
            $(button).html('<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>');
            $(button).attr('disabled', 'disabled');
            $(button).addClass('submited');
            $(button).animate({
                opacity: 1
            }, 100, function () {
                $(form).submit();
            })
        }
    });

    $('[data-container]').on('change', function () {
        var target = $(this).data('container');
        var mode = $(this).val().toString() === 'true';

        $('[data-handler="' + target + '"]').each(function () {
            if (mode === $(this).hasClass('inverted')) {
                $(this).stop().slideDown(200);
            } else {
                $(this).stop().slideUp(200);
            }
        });

        if ($(this).attr('data-switch')) {
            var container = $('[data-container="' + $(this).attr('data-switch-target') + '"]');
            container.val($(this).attr('data-switch') === 'true' ? 'false' : 'true');
            container.trigger('change');
        }

        return false;
    });

    $('[data-switch]').on('click', function () {
        var target = $(this).data('switch-target');
        var mode = $(this).data('switch').toString() === 'true';

        $('[data-handler="' + target + '"]').each(function () {
            var container = $('[data-container="' + target + '"]');

            container.val(mode === $(this).hasClass('inverted') ? 'true' : 'false');
            container.trigger('change');

        });

        return false;
    });

    if (jQuery().selectpicker) {
        jQuery.fn.extend({
            selectBind: function (root) {

                root.find('.form-group select').each(function () {

                    var select = $(this);

                    var selectpicker = select.selectpicker({
                        size: 10,
                        width: '100%',
                        noneResultsText: 'Nic nie znaleziono',
                        noneSelectedText: 'wybierz',
                    });

                    if (select.attr('data-ajax')) {
                        selectpicker.ajaxSelectPicker({
                            ajax: {
                                url: select.data('ajax'),
                                method: 'GET',
                                dataType: 'json',
                                data: function () {
                                    return {
                                        query: '{{{q}}}'
                                    };
                                }
                            },
                            preprocessData: function (data) {
                                var rows = [];
                                for (var i = 0; i < data.length; i++) {
                                    rows.push({
                                        'value': data[i].id,
                                        'text': data[i].name,
                                    });
                                }
                                return rows;
                            },
                            locale: {
                                currentlySelected: 'Wybrany',
                                emptyTitle: '-- wybierz --',
                                errorText: 'Nie można wyszukiwać danych',
                                searchPlaceholder: '',
                                statusInitialized: null,
                                statusNoResults: 'Brak wyników',
                                statusSearching: 'Szukam...'
                            },
                            preserveSelectedPosition: 'before'
                        });
                    }
                });
            }
        });
        $(document).selectBind($('body'));
    }

    $(document).on('keydown', function (e) {
        var modal = $('.modal-content:visible');
        if (modal.length) {
            if (e.which === 13) {
                var submit = modal.find('.modal-footer button[type=submit]');
                if (submit.length) {
                    submit.click();
                } else {
                    var a = modal.find('.modal-footer a');
                    if (a.length) {
                        location.href = a.attr('href');
                    } else {
                        var button = modal.find('.modal-footer button[type=button]:visible');
                        if (button.length) {
                            button.click();
                        }
                    }
                }
            }
        }
    });

    var passwordHint = {
        eye: $('<span>').addClass('fas fa-eye-slash'),
        init: function () {
            var self = this;
            $('input[type=password]').each(function () {
                var input = $(this).clone();
                var eye = self.eye.clone();
                var wrapper = $('<div>').addClass('eye');
                wrapper.append(input).append(eye);
                $(this).replaceWith(wrapper);
                eye.on('click', function () {
                    if (eye.hasClass('fa-eye')) {
                        eye.addClass('fa-eye-slash');
                        eye.removeClass('fa-eye');
                        input.prop('type', 'password');
                    } else {
                        eye.addClass('fa-eye');
                        eye.removeClass('fa-eye-slash');
                        input.prop('type', 'text');
                    }
                })
            });

            return this;
        }
    }.init();

    $('.pagination .inactive').on('click', function (event) {
        event.preventDefault();
    });

    $('[data-toggle=dropdown-filter]').on('click', function () {
        var dropdown = $(this).parents('.dropdown-filter');
        var isOpen = dropdown.hasClass('open');
        var id = $(this).attr('id');
        var dropdownBody = dropdown.find('[aria-labelledby=' + id + ']');

        if (isOpen) {
            dropdown.removeClass('open');
            $(this).attr('aria-expanded', 'false');
            dropdownBody.hide();
        } else {
            dropdown.addClass('open');
            $(this).attr('aria-expanded', 'true');
            dropdownBody.show();
        }
    });

    $(document).mouseup(function (e) {
        var dropdownFilter = $('.dropdown-filter');
        var dropdownDateRangePicker = $('.daterangepicker');
        if (!dropdownFilter.hasClass('open')) {
            return;
        }


        if (!dropdownFilter.is(e.target) && dropdownFilter.has(e.target).length === 0 && !dropdownDateRangePicker.is(e.target) && dropdownDateRangePicker.has(e.target).length === 0) {
            dropdownFilter.find('.dropdown-toggle').click();
        }
    });

    $(document).on('click', '.text-expandable button', function () {
        var hidden = $(this).parent('.text-expandable').find('.hidden');
        hidden.hide();
        hidden.removeClass('hidden');
        hidden.slideDown({
            duration: 500,
            easing: "easeOutCirc"
        });
        $(this).hide();

        return false;
    });

    $('#task-view-status').on('changed.bs.select', function () {
        var href = window.location.href;
        var value = $(this).val();

        if (href.indexOf('task-view-status=') !== -1) {
            href = href.replace(/task\-view\-status=[\d]+/g, 'task-view-status=' + value);
        } else {
            if (href.indexOf('?') !== -1) {
                href = href + '&task-view-status=' + value;
            } else {
                href = href + '?task-view-status=' + value;
            }
        }

        location.href = href;
    });

    $(document).on('click', 'table.table-sortable *[data-toggle=sort]', function () {
        var table = $(this).parents('table.table-sortable');

        var requestedOrder = $(this).data('column-order-id');
        var currentOrder = $(table).data('column-order-id');

        if (requestedOrder === currentOrder) {
            requestedOrder = -requestedOrder;
        }

        $(table).find('a[data-toggle=sort]').find('span').remove();

        if (requestedOrder > 0) {
            $(this).append('<span class="fas fa-fw fa-arrow-up"></span>');
        } else {
            $(this).append('<span class="fas fa-fw fa-arrow-down"></span>');
        }

        var rows = Array.from(table.find('tbody tr'));
        var qs = '*:nth-child(' + Math.abs(requestedOrder) + ')';

        rows.sort(function (r1, r2) {
            var t1 = r1.querySelector(qs);
            var t2 = r2.querySelector(qs);

            var v1 = t1.textContent;
            var v2 = t2.textContent;

            if (v1.length && !isNaN(v1)) {
                v1 = parseFloat(v1);
            }

            if (v2.length && !isNaN(v2)) {
                v2 = parseFloat(v2);
            }

            var sign = requestedOrder > 0 ? 1 : -1;
            return (v1 < v2) ? -sign : (v1 > v2) ? sign : 0;
        });

        rows.forEach(function (row) {
            table.append(row)
        });

        $(table).data('column-order-id', requestedOrder);
    });

    $(window).trigger('resize');

    var marcelValidator = {
        inputGroup: null,
        init: function () {
            var self = this;
            $(document).on('click', '.validate-marcel button', function () {
                self.request($(this).parents('.validate-marcel'));
                return false;
            });
        },
        request: function (input) {
            var form = $(input).parents('form');
            var name = $(form).find("input[name*='[name]']");
            var symbol = $(form).find("input[name*='[marcel]']");
            var laboratory = $(form).find("select[name*='[laboratory]']");

            $.ajax({
                url: "/api/marcel/collection-point/" + symbol.val(),
                method: 'GET',
                dataType: 'json',
                success: function (response) {

                    name.val(response.name);
                    name.effect("highlight", { color: '#d9edf7' }, 1000);

                    symbol.effect("highlight", { color: '#d9edf7' }, 1000);

                    laboratory.val(response.laboratory.id).change();
                    laboratory.siblings('button').css({ transition: false }).effect("highlight", { color: '#d9edf7' }, 1000);

                    $.notification('info', 'Kod MARCEL został znaleziony. Automatycznie wybrano przypisane laboratorium oraz uzupełniono nazwę.');
                },
                error: function () {
                    symbol.effect("highlight", { color: "#f2dede" }, 1000);

                    $.notification('error', 'Nie znaleziono podanego kodu MARCEL');
                },
            });
        }
    }.init();

    $('.select-is-more-info').find('select').each(function () {
        $(this).on('changed.bs.select', function (e) {

            var target = $(this).parents('.select-is-more-info').data('target');
            var attrs = $(this).find("option:selected").data();

            $('[data-handler=' + target + ']').each(function () {

                if (attrs[$.camelCase($(this).data('variable'))]) {
                    $(this).slideDown(200);
                } else {
                    $(this).slideUp(200);
                }
            });
        })
    });

    $('.table-stick').floatThead({
        position: 'absolute'
    });

    $('.auto-submit').change(function () {
        var form = $(this).parents('form');
        setTimeout(function () {
            form.submit();
        }, 200);
    });

    const employeeSearch = {
        departmentList: null,
        employeeList: null,
        infoInfo: null,
        infoNotFound: null,

        department: null,
        query: '',

        rowTemplate: null,

        employees: null,

        init: function () {
            this.departmentList = $('.employee--departments');
            this.employeeList = $('.employee--list');
            this.infoInfo = $('.employee--list-info');
            this.infoNotFound = $('.employee--list-not_found');
            this.rowTemplate = $(this.employeeList).data('template');
            const self = this;

            $(this.departmentList).find('a').on('click', function () {
                const alreadySelected = $(this).hasClass('selected');

                $(this).parents('ol').find('a').removeClass('selected');

                if (alreadySelected) {
                    $(this).removeClass('selected');
                    self.department = null
                } else {
                    $(this).addClass('selected');
                    self.department = {
                        lft: $(this).data('lft'),
                        rgt: $(this).data('rgt'),
                    }
                }

                self.search();

                return false;
            });

            $('.page-header--employee input[name=query]').on('keyup', function () {
                self.query = $(this).val().toLowerCase();

                self.search();
            });

            $('.page-header--employee input[name=query]').parents('form').on('submit', function () {
                return false;
            });

            return self;
        },

        search: function () {
            this.employees = [];

            for (let i = 0; i < employees.length; i++) {
                for (let key in employees[i]) {
                    if (JSON.stringify(employees[i][key]).toLowerCase().indexOf(this.query) !== -1) {
                        this.employees.push(employees[i]);
                        break;
                    }
                }
            }

            this.draw();
        },

        draw: function () {
            $(this.employeeList).find('li').remove();
            $(this.employeeList).addClass('hidden');
            $(this.infoNotFound).addClass('hidden');
            $(this.infoInfo).addClass('hidden');

            // Nothing selected
            if (this.department === null && this.query === '') {
                $(this.infoInfo).removeClass('hidden');
                // Nothing was found
            } else if ((this.department !== null || this.query !== '') && !this.employees.length) {
                $(this.infoNotFound).removeClass('hidden');
            } else {
                for (const employee of this.employees) {


                    if (this.department !== null) {
                        let show = false;

                        for (let department of employee.departments) {
                            if (department.lft >= this.department.lft && department.rgt <= this.department.rgt) {
                                show = true;
                            }
                        }

                        if (!show) {
                            continue;
                        }
                    }

                    let row = this.rowTemplate;

                    // if (employee.name.slice(-1) === 'a') {
                    //     row = row.replace('#avatar#', 'female');
                    // } else {
                    //     row = row.replace('#avatar#', 'male');
                    // }

                    row = row.replace('#fullname#', employee.name + ' ' + employee.surname);

                    if (employee.email) {
                        row = row.replace('#email#', 'email: <a href="mailto:' + employee.email + '">' + employee.email + '</a>');
                    } else {
                        row = row.replace('#email#', '');
                    }

                    if (employee.phones.length) {
                        let phones = 'tel.: ';
                        for (let phone of employee.phones) {
                            phones += '<a href="callto:' + phone + '">' + phone + '</a>, ';
                        }
                        row = row.replace('#phones#', phones.substring(0, phones.length - 2));
                    } else {
                        row = row.replace('#phones#', '');
                    }

                    row = row.replace('#position#', employee.position);

                    let departments = [];
                    if (employee.departments) {
                        for (let department of employee.departments) {
                            departments.push(department.name);
                        }
                    }
                    row = row.replace('#department#', departments.join(', '));

                    $(this.employeeList).append(row);

                    $(this.employeeList).removeClass('hidden');
                }

                if (!$(this.employeeList).find('li').length) {
                    $(this.infoNotFound).removeClass('hidden');
                }
            }

            this.updateDepartmentList();
        },

        updateDepartmentList: function () {
            const self = this;

            this.departmentList.find('a').each(function () {
                let isVisible = false;

                outer_loop:
                for (const employee of self.employees) {
                    if (employee.departments) {
                        for (let department of employee.departments) {
                            if (department.lft >= $(this).data('lft') && department.rgt <= $(this).data('rgt')) {
                                isVisible = true;
                                break outer_loop;
                            }
                        }
                    }
                }

                if (isVisible) {
                    $(this).removeClass('hidden');
                } else {
                    $(this).addClass('hidden');
                }
            })

        }
    }.init();
});