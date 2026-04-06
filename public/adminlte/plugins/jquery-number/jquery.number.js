(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory(require('jquery'));
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    function normalizeNumber(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        if (typeof value === 'number') {
            return isFinite(value) ? value : 0;
        }

        var normalized = String(value).replace(/[^0-9+\-Ee.]/g, '');
        var parsed = parseFloat(normalized);

        return isFinite(parsed) ? parsed : 0;
    }

    function formatNumber(value, decimals, decimalSeparator, thousandsSeparator) {
        var number = normalizeNumber(value);
        var parsedDecimals = parseInt(decimals, 10);
        var precision = isFinite(parsedDecimals) ? Math.max(parsedDecimals, 0) : 0;
        var decimalPoint = typeof decimalSeparator === 'string' ? decimalSeparator : '.';
        var thousands = typeof thousandsSeparator === 'string' ? thousandsSeparator : ',';
        var fixed = number.toFixed(precision).split('.');
        var integerPart = fixed[0];
        var decimalPart = fixed.length > 1 ? fixed[1] : '';

        if (thousands !== '') {
            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousands);
        }

        return decimalPart ? integerPart + decimalPoint + decimalPart : integerPart;
    }

    $.number = function (value, decimals, decimalSeparator, thousandsSeparator) {
        return formatNumber(value, decimals, decimalSeparator, thousandsSeparator);
    };

    $.fn.number = function (value, decimals, decimalSeparator, thousandsSeparator) {
        return this.each(function () {
            var $element = $(this);
            var sourceValue = value;

            if (typeof value === 'boolean') {
                sourceValue = $element.is('input, textarea') ? $element.val() : $element.text();
            }

            var formatted = formatNumber(sourceValue, decimals, decimalSeparator, thousandsSeparator);

            if ($element.is('input, textarea')) {
                $element.val(formatted);
            } else {
                $element.text(formatted);
            }
        });
    };
}));