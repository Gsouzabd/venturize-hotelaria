// Auto update layout
(function () {
    window.layoutHelpers.setAutoUpdate(true);
})();

// Collapse menu
(function () {
    if ($('#layout-sidenav').hasClass('sidenav-horizontal') || window.layoutHelpers.isSmallScreen()) {
        return;
    }

    try {
        window.layoutHelpers.setCollapsed(localStorage.getItem('layoutCollapsed') === 'true', false);
    } catch (e) {
    }
})();

// Set laravel CRSF token for ajax requests
jQuery.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': APP_CSRF_TOKEN
    }
});

jQuery(function ($) {
    // Initialize sidenav
    $('#layout-sidenav').each(function () {
        new SideNav(this, {
            orientation: $(this).hasClass('sidenav-horizontal') ? 'horizontal' : 'vertical'
        });
    });

    // Initialize sidenav togglers
    $('body').on('click', '.layout-sidenav-toggle', function (e) {
        e.preventDefault();
        window.layoutHelpers.toggleCollapsed();

        if (!window.layoutHelpers.isSmallScreen()) {
            try {
                localStorage.setItem('layoutCollapsed', String(window.layoutHelpers.isCollapsed()));
            } catch (e) {
            }
        }
    });

    if ($('html').attr('dir') === 'rtl') {
        $('#layout-navbar .dropdown-menu').toggleClass('dropdown-menu-right');
    }

    // Apply masks
    let phoneMaskBehavior = function (val) {
        let val2 = val.replace(/\D/g, '');

        if (val2.length === 11 && val2.substr(0, 4) === '0800') {
            return '0000-000-0000';
        } else if (val2.length === 11) {
            return '(00) 0 0000-0000';
        } else {
            return '(00) 0000-00009';
        }
    };

    let phoneMaskOptions = {
        onKeyPress: function (val, e, field, options) {
            field.mask(phoneMaskBehavior.apply({}, arguments), options);
        }
    };

    let cpfCnpjMaskBehavior = function (val) {
        let val2 = val.replace(/\D/g, '');

        if (val2.length > 11) {
            return '00.000.000/0000-00';
        } else {
            return '000.000.000-00999';
        }
    };

    let cpfCnpjMaskOptions = {
        onKeyPress: function (val, e, field, options) {
            field.mask(cpfCnpjMaskBehavior.apply({}, arguments), options);
        }
    };

    $(".phone-mask").unmask().mask(phoneMaskBehavior, phoneMaskOptions);
    $(".date-mask").unmask().mask("00/00/0000");
    $(".daterange-mask").unmask().mask("00/00/0000 - 00/00/0000");
    $(".shorttime-mask").unmask().mask("00:00");
    $(".time-mask").unmask().mask("00:00:00");
    $(".datetime-mask").unmask().mask("00/00/0000 00:00:00");
    $(".cep-mask").unmask().mask("00000-000");
    $(".cpf-mask").mask('000.000.000-00');
    $(".cnpj-mask").unmask().mask("00.000.000/0000-00");
    $(".cpfcnpj-mask").unmask().mask(cpfCnpjMaskBehavior, cpfCnpjMaskOptions);
    $(".integer-mask").unmask().mask("#");
    $(".money-mask").unmask().mask("#.##0,00", {reverse: true});
});


function incrementValue(inputId) {
    const input = document.getElementById(inputId);
    let value = parseInt(input.value, 10);
    value = isNaN(value) ? 0 : value;
    value++;
    input.value = value;
}

function decrementValue(inputId) {
    const input = document.getElementById(inputId);
    let value = parseInt(input.value, 10);
    value = isNaN(value) ? 0 : value;
    value = value > 0 ? value - 1 : 0;
    input.value = value;
}
