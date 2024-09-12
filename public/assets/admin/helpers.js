window.appHelpers = {
    showPageLoader: function (message) {
        message = message || 'CARREGANDO...'

        $.blockUI({
            message: '<div class="sk-fold sk-primary mx-auto mb-4"><div class="sk-fold-cube"></div><div class="sk-fold-cube"></div><div class="sk-fold-cube"></div><div class="sk-fold-cube"></div></div><h5 class="text-body">' + message + '</h5>',
            css: {
                backgroundColor: 'transparent',
                border: '0',
                zIndex: 9999999
            },
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: 0.8,
                zIndex: 9999990
            }
        });
    },

    hidePageLoader: function () {
        $.unblockUI();
    },

    doAjaxForm: function (form, options) {
        let $form = $(form);
        let defaultOptions = {
            beforeSubmit: null,
            done: null,
            fail: null,
            afterFail: null
        };

        options = Object.assign(defaultOptions, (options || {}));

        $form.on("submit", function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (typeof options.beforeSubmit === 'function') {
                options.beforeSubmit($form);
            }

            appHelpers.showPageLoader();

            let ajaxOptions = {
                method: $form.attr("method"),
                url: $form.attr("action"),
                data: $form.serialize(),
            };

            if ($form.attr('enctype') !== 'multipart/form-data') {
                ajaxOptions = {...ajaxOptions, ...{data: $form.serialize()}};
            } else {
                ajaxOptions = {
                    ...ajaxOptions, ...{
                        data: new FormData($form[0]),
                        mimeType: "multipart/form-data",
                        cache: false,
                        contentType: false,
                        processData: false,
                    }
                };
            }

            $.ajax(ajaxOptions)
                .done(function (data, textStatus, jqXHR) {
                    appHelpers.hidePageLoader();

                    if ($form.attr('enctype') === 'multipart/form-data') {
                        data = JSON.parse(data);
                    }

                    if (typeof options.done === 'function') {
                        options.done(data, textStatus, jqXHR);
                    } else {
                        if (data.message) {
                            Swal.fire("Feito!", data.message, "success").then(function () {
                                if (data.redirect_to) {
                                    window.location = data.redirect_to;
                                }
                            });
                        } else {
                            if (data.redirect_to) {
                                window.location = data.redirect_to;
                            }
                        }
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    appHelpers.hidePageLoader();

                    if (typeof options.fail === 'function') {
                        options.fail(jqXHR, textStatus, errorThrown);
                    } else {
                        let response = JSON.parse(jqXHR.responseText);
                        let error = '';

                        if (jqXHR.status === 422) {
                            error = Object.values(response.errors)[0][0]; // Pega o primeiro erro da validação...
                        } else {
                            error = response.message || 'Ocorreu um erro desconhecido.';
                        }

                        Swal.fire("Ops!", error, "error");
                    }

                    if (typeof options.afterFail === 'function') {
                        options.afterFail($form);
                    }
                });
        });
    }
};
