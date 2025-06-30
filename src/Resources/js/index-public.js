(function ($) {
    $(document).ready(function () {
        const popup = $("#popup");
        const closeButton = $("#popup-close-btn");
        const checkbox = $("#popup-checkbox");

        // Funkcja do odczytywania ciasteczka
        const getCookie = (name) => {
            const cookie = document.cookie
                .split("; ")
                .find((row) => row.startsWith(name + "="));
            return cookie ? cookie.split("=")[1] : null;
        };

        // Funkcja do ustawiania ciasteczka
        const setCookie = (name, value, days) => {
            const expires = new Date();
            expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
            // console.log(
            //     `Ciasteczko "${name}" ustawione na: ${expires.toUTCString()}`
            // );
            document.cookie = `${name}=${value}; path=/; expires=${expires.toUTCString()}`;
        };

        // Ukryj popup przed załadowaniem (zapobieganie migotaniu)
        popup.css("visibility", "hidden");

        // Sprawdź ciasteczko
        const shouldHidePopup = getCookie("hidePopup");

        if (shouldHidePopup === "true") {
            // console.log("Popup ukryty, ponieważ ciasteczko istnieje");
            popup.hide(); // Ukryj popup
        } else {
            // console.log("Wyświetlam popup");
            popup.css("visibility", "visible").fadeIn(300); // Pokazuje popup
        }

        // Zamykanie popupu
        closeButton.on("click", function () {
            // console.log("Zamykam popup");
            popup.fadeOut(300);

            if (checkbox.prop("checked")) {
                setCookie("hidePopup", "true", 365 * 10);
                // console.log("Ciasteczko ustawione na true");
            }
        });
        $(document)
            .find(".navbar-nav > li")
            .each(function () {
                let documentWidth = $("body").width();
                let dropdownOffset = $(this).offset().left;

                if (documentWidth / 2 <= dropdownOffset) {
                    $(this).find(".dropdown-menu").addClass("dropdown-menu-left");
                }
            });

        $(document).on("click", "[data-toggle]", function (e) {
            const id = $("#" + $(this).data("toggle"));

            if (!id.hasClass("always-show")) {
                id.slideToggle(100);
            }

            id.find("input").focus();
        });

        $(".dropdown-toggle").on("click", function (e) {
            let dropdown = $($(this).parent("li.dropdown"));

            if (dropdown.hasClass("open")) {
                dropdown.removeClass("open");
                dropdown.find(".dropdown").removeClass("open");
            } else {
                dropdown.addClass("open");
            }

            return false;
        });

        $("nav .dropdown")
            .on("mouseenter", function () {
                const width = $(document).width();
                if (width >= 991) {
                    $(this).addClass("open");
                }
            })
            .on("mouseleave", function () {
                const width = $(document).width();
                if (width >= 991) {
                    $(this).removeClass("open");
                }
            });

        $(".mr-filter-button").on("click", function () {
            if ($(this).hasClass("active")) {
                $(".mr-filter-row").hide();
            } else {
                $(".mr-filter-row").show(10, function () {
                    $(".mr-filter-row").find("input:visible:eq(0)").focus();
                });
            }
        });

        $(".mr-filter-reset").on("click", function () {
            var inputs = $(this).parents(".mr-filter-row").find("input[type=text]");
            $(inputs).val("");
        });

        if (jQuery().selectpicker) {
            jQuery.fn.extend({
                selectBind: function (root) {
                    root.find(".form-group select").each(function () {
                        var select = $(this);

                        var selectpicker = select.selectpicker({
                            size: 10,
                            width: "100%",
                            noneResultsText: "Nic nie znaleziono",
                            noneSelectedText: "wybierz",
                        });

                        if (select.attr("data-ajax")) {
                            selectpicker.ajaxSelectPicker({
                                ajax: {
                                    url: select.data("ajax"),
                                    method: "GET",
                                    dataType: "json",
                                    data: function () {
                                        return {
                                            query: "{{{q}}}",
                                        };
                                    },
                                },
                                preprocessData: function (data) {
                                    var rows = [];
                                    for (var i = 0; i < data.length; i++) {
                                        rows.push({
                                            value: data[i].id,
                                            text: data[i].name,
                                        });
                                    }
                                    return rows;
                                },
                                locale: {
                                    currentlySelected: "Wybrany",
                                    emptyTitle: "-- wybierz --",
                                    errorText: "Nie można wyszukiwać danych",
                                    searchPlaceholder: "",
                                    statusInitialized: null,
                                    statusNoResults: "Brak wyników",
                                    statusSearching: "Szukam...",
                                },
                                preserveSelectedPosition: "before",
                            });
                        }
                    });
                },
            });
            $(document).selectBind($("body"));
        }

        $(".dropdown-column-select label").on("click", function (event) {
            event.stopPropagation();
        });

        $(".dropdown-column-select input").on("change", function () {
            var columnName = $(this).data("column");
            var tableName = $(this).parents("table").data("table");
            var status = $(this).prop("checked") ? 1 : 0;

            if (status) {
                $(this)
                    .parents("table")
                    .find(
                        "th[data-column=" +
                        columnName +
                        "],td[data-column=" +
                        columnName +
                        "]"
                    )
                    .fadeIn(200);
            } else {
                $(this)
                    .parents("table")
                    .find(
                        "th[data-column=" +
                        columnName +
                        "],td[data-column=" +
                        columnName +
                        "]"
                    )
                    .fadeOut(200);
            }

            $.ajax({
                url: "/api/user-table-column-visible",
                method: "POST",
                dataType: "json",
                data: {
                    table: tableName,
                    column: columnName,
                    status: status,
                },
                success: function (response) {
                    if (!response.status) {
                        alert("column visibility error");
                    }
                },
            });

            return false;
        });

        const employeeSearch = {
            departmentList: null,
            employeeList: null,
            infoInfo: null,
            infoNotFound: null,

            department: null,
            query: "",

            rowTemplate: null,

            employees: null,

            init: function () {
                this.departmentList = $(".employee--departments");
                this.employeeList = $(".employee--list");
                this.infoInfo = $(".employee--list-info");
                this.infoNotFound = $(".employee--list-not_found");
                this.rowTemplate = $(this.employeeList).data("template");
                const self = this;

                $(this.departmentList)
                    .find("a")
                    .on("click", function () {
                        const alreadySelected = $(this).hasClass("selected");

                        $(this).parents("ol").find("a").removeClass("selected");

                        if (alreadySelected) {
                            $(this).removeClass("selected");
                            self.department = null;
                        } else {
                            $(this).addClass("selected");
                            self.department = {
                                lft: $(this).data("lft"),
                                rgt: $(this).data("rgt"),
                            };
                        }

                        self.search();

                        return false;
                    });

                $(".page-header--employee input[name=query]").on("keyup", function () {
                    self.query = $(this).val().toLowerCase();

                    self.departmentList.find("a").removeClass("selected");

                    self.search();
                });

                $(".page-header--employee input[name=query]")
                    .parents("form")
                    .on("submit", function () {
                        return false;
                    });

                return self;
            },

            search: function () {
                this.employees = [];

                for (let i = 0; i < employees.length; i++) {
                    for (let key in employees[i]) {
                        if (
                            JSON.stringify(employees[i][key])
                                .toLowerCase()
                                .indexOf(this.query) !== -1
                        ) {
                            this.employees.push(employees[i]);
                            break;
                        }
                    }
                }

                this.draw();
            },

            draw: function () {
                $(this.employeeList).find("li").remove();
                $(this.employeeList).addClass("hidden");
                $(this.infoNotFound).addClass("hidden");
                $(this.infoInfo).addClass("hidden");

                // Nothing selected
                if (this.department === null && this.query === "") {
                    $(this.infoInfo).removeClass("hidden");
                    // Nothing was found
                } else if (
                    (this.department !== null || this.query !== "") &&
                    !this.employees.length
                ) {
                    $(this.infoNotFound).removeClass("hidden");
                } else {
                    for (const employee of this.employees) {
                        if (this.department !== null) {
                            let show = false;

                            for (let department of employee.departments) {
                                if (
                                    department.lft >= this.department.lft &&
                                    department.rgt <= this.department.rgt
                                ) {
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

                        row = row.replace(
                            "#fullname#",
                            employee.name + " " + employee.surname
                        );

                        if (employee.email) {
                            row = row.replace(
                                "#email#",
                                'email: <a href="mailto:' +
                                employee.email +
                                '">' +
                                employee.email +
                                "</a>"
                            );
                        } else {
                            row = row.replace("#email#", "");
                        }

                        if (employee.phones.length) {
                            let phones = "tel.: ";
                            for (let phone of employee.phones) {
                                phones += '<a href="callto:' + phone + '">' + phone + "</a>, ";
                            }
                            row = row.replace(
                                "#phones#",
                                phones.substring(0, phones.length - 2)
                            );
                        } else {
                            row = row.replace("#phones#", "");
                        }

                        row = row.replace("#position#", employee.position);

                        let departments = [];
                        if (employee.departments) {
                            for (let department of employee.departments) {
                                departments.push(department.name);
                            }
                        }
                        row = row.replace("#department#", departments.join(", "));

                        $(this.employeeList).append(row);

                        $(this.employeeList).removeClass("hidden");
                    }

                    if (!$(this.employeeList).find("li").length) {
                        $(this.infoNotFound).removeClass("hidden");
                    }
                }

                this.updateDepartmentList();
            },

            updateDepartmentList: function () {
                const self = this;

                this.departmentList.find("a").each(function () {
                    let isVisible = false;

                    outer_loop: for (const employee of self.employees) {
                        if (employee.departments) {
                            for (let department of employee.departments) {
                                if (
                                    department.lft >= $(this).data("lft") &&
                                    department.rgt <= $(this).data("rgt")
                                ) {
                                    isVisible = true;
                                    break outer_loop;
                                }
                            }
                        }
                    }

                    if (isVisible || self.query === "") {
                        $(this).removeClass("hidden");
                    } else {
                        $(this).addClass("hidden");
                    }
                });
            },
        }.init();

        $(document).on("mousewheel scroll", function () {
            const pos = $(document).scrollTop();

            if (pos > 50) {
                $("nav").css("padding-top", "0");
                $("nav").css("padding-bottom", "0");
                $("nav").css("box-shadow", "0 0 10px rgba(0, 0, 0, 0.2)");
                $(".navbar-brand img").css("height", "25px");
            } else {
                $("nav").css("padding-top", "1rem");
                $("nav").css("padding-bottom", "1rem");
                $("nav").css("box-shadow", "none");
                $(".navbar-brand img").css("height", "40px");
            }
        });

        window.addEventListener("keydown", (e) => {
            if (e.code === "F3" || ((e.ctrlKey || e.metaKey) && e.code === "KeyF")) {
                e.preventDefault();

                const search = $("#search");

                search.slideDown(100);

                search.find("input").focus();
            }
        });
    });

    $(document).on("click", ".iso-show-archive", function () {
        $(this).fadeOut();

        $(this).parents("li").find(".iso-file-archives").fadeIn();
    });

    // Kliknięcie na folder lub jego tekst
    $(".iso").on(
        "click",
        ".fa-folder, .fa-folder-open, .folder-text",
        function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $li = $(this).closest("li");
            const $icon = $li.find(".fa-folder, .fa-folder-open").first();

            // Sprawdź, czy element już jest aktywny
            const isActive = $li.hasClass("active");

            if (isActive) {
                // Zwijamy, jeśli jest aktywny
                $li.removeClass("active");
                $li.children("ul.folder-content").slideUp(200);
                $icon.removeClass("fa-folder-open").addClass("fa-folder");
            } else {
                // Rozwijamy folder i zamykamy rodzeństwo
                $li.siblings(".active").each(function () {
                    $(this).removeClass("active");
                    $(this).children("ul.folder-content").slideUp(200);
                    $(this)
                        .find(".fa-folder-open")
                        .removeClass("fa-folder-open")
                        .addClass("fa-folder");
                });

                $li.addClass("active");
                $li.children("ul.folder-content").slideDown(200);
                $icon.removeClass("fa-folder").addClass("fa-folder-open");
            }
        }
    );

    // Zapobiega propagacji kliknięcia na link w plikach
    $(".iso").on("click", ".file a", function (e) {
        e.stopPropagation();
    });

    $(".employee--departments > li > a").on("click", function (e) {
        e.preventDefault();
        var $li = $(this).parent("li");
        var $ol = $li.find("> ol");

        if ($li.hasClass("open")) {
            $ol.css("max-height", 0);
            $li.removeClass("open");
        } else {
            var scrollHeight = $ol.prop("scrollHeight") + "px";
            $ol.css("max-height", scrollHeight);
            $li.addClass("open");
        }
    });

    $('.tag').on("click", function (e) {
        e.preventDefault();
        $(this).toggleClass("active");

        let selectedIds = []
        const tags = $(this).parents(".tags").find('.tag');
        for (let i = 0; i < tags.length; i++) {
            if ($(tags[i]).hasClass('active')) {
                selectedIds.push($(tags[i]).data('id'));
            }
        }

        let files = $('.files .file-description');
        if (selectedIds.length === 0) {
            $(files).removeClass("hidden-by-tag");
        } else {
            for (let i = 0; i < files.length; i++) {
                $(files[i]).addClass("hidden-by-tag");

                let fileTags = String($(files[i]).data('tags'));
                if (fileTags) {
                    let fileTagsIds = fileTags.split(',').map(str => parseInt(str, 10))

                    if (fileTagsIds.some(el => selectedIds.includes(el))) {
                        $(files[i]).removeClass("hidden-by-tag");
                    }
                }
            }
        }

        function updateCategoryVisibility($rootUl) {
            // Wykonuj iteracyjnie od najgłębiej zagnieżdżonych <li>
            const $allLi = $rootUl.find('li').get().reverse();

            $allLi.forEach(function (li) {
                const $li = $(li);

                // Szukamy widocznych linków w danym elemencie (pomijając ukryte)
                const hasVisibleLinks = $li.find('.file-description:not(.hidden-by-tag)').length > 0;

                // Szukamy widocznych podkategorii (dzieci <li>)
                const hasVisibleChildren = $li.children('ul').find('> li:not(.hidden-by-tag)').length > 0;

                // Jeśli ma widoczne linki lub widoczne dzieci — pokaż
                if (hasVisibleLinks || hasVisibleChildren) {
                    $li.removeClass("hidden-by-tag");
                } else {
                    $li.addClass("hidden-by-tag");
                }
            });
        }

        const $rootUl = $('ul.files'); // lub konkretniej jeśli trzeba
        updateCategoryVisibility($rootUl);

    })
})(jQuery);