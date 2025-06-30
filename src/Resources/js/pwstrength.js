$(function () {
    "use strict";

    i18next.init({
        lng: 'translated',
        resources: {
            'translated': {
                translation: {
                    // WORKAROUND: spaces added, because when
                    // key = value, thoes values are not displayed
                    "veryWeak": ' ' + 'bardzo słabe',
                    "weak": ' ' + 'słabe',
                    "normal": ' ' + 'normalne',
                    "medium": ' ' + 'dobre',
                    "strong": ' ' + 'silne',
                    "veryStrong": ' ' + 'bardzo silne'
                }
            }
        }
    }, function () {
        $('input.pwstrength').pwstrength({
            rules: {
                activated: {
                    wordTwoCharacterClasses: true,
                    wordRepetitions: true
                }
            },
            ui: {
                progressBarEmptyPercentage: 0,
                showVerdictsInsideProgressBar: true,
                showVerdicts: true,
                progressBarExtraCssClasses: '',
                zxcvbn: true
            }
        });
    });

    var passwordGenerator = {
        generatorTemplate: $('<div class="password-generate">' +
            '<button type="button" name="password_generate" class="btn-default btn btn-xs">Wygeneruj nowe hasło</button>' +
            '<span class="password_generated hidden">Wygenerowane hasło: <span></span>' +
            '<button type="button" name="password_generate_use" class="btn-default btn btn-xs btn-xs-margin-left">Użyj</button>' +
            '<button type="button" name="password_generate_copy" class="btn-default btn btn-xs btn-xs-margin-left">Kopiuj</button>' +
            '</div>' +
            '</div>'),

        passwordGenerate: function () {
            // return Math.random().toString(36).slice(2);
            return generatePassword(12, false);
        },

        init: function () {
            var self = this;
            $('input.pwstrength').each(function () {
                $(this).parent('div').append(self.generatorTemplate);

                self.generatorTemplate.find('button[name=password_generate]').on('click', function () {
                    self.generatorTemplate.find('.password_generated span').html(self.passwordGenerate());
                    self.generatorTemplate.find('.password_generated').removeClass('hidden');
                });

                self.generatorTemplate.find('button[name=password_generate_copy]').on('click', function () {
                    var tempInput = $("<input>");
                    $('body').append(tempInput);
                    tempInput.val(self.generatorTemplate.find('.password_generated span').text()).select();
                    document.execCommand("copy");
                    tempInput.remove();

                    $.notification('info', 'Hasło skopiowane do schowka');
                });

                self.generatorTemplate.find('button[name=password_generate_use]').on('click', function () {
                    var form = $(this).parents('form');

                    var input = form.find('input[type=password][name*=first]');
                    if (!input.length) {
                        input = form.find('input[type=text][name*=first]');

                        if (!input.length) {
                            return;
                        }
                    }
                    input.val(self.generatorTemplate.find('.password_generated span').text());
                    input.trigger('change');

                    var input2 = form.find('input[type=password][name*=second]');
                    if (!input2.length) {
                        input2 = form.find('input[type=text][name*=second]');
                        if (!input2.length) {
                            return;
                        }
                    }
                    input2.val(self.generatorTemplate.find('.password_generated span').text());
                });
            })
        }
    }.init();

});