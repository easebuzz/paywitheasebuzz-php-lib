(function () {
    'use strict';

    function resetFieldStyle(field) {
        field.setCustomValidity('');
        field.style.borderColor = '';
        field.style.backgroundColor = '';
        var existingError = field.parentNode.querySelector('.field-error-msg');
        if (existingError) existingError.remove();
    }

    function highlightFieldError(field, message) {
        field.style.borderColor = '#ff0000';
        field.style.backgroundColor = '#ffebee';
        var existingError = field.parentNode.querySelector('.field-error-msg');
        if (existingError) {
            existingError.textContent = message;
        } else if (message) {
            var errorSpan = document.createElement('span');
            errorSpan.className = 'field-error-msg';
            errorSpan.style.color = '#c62828';
            errorSpan.style.fontSize = '0.8em';
            errorSpan.style.display = 'block';
            errorSpan.style.marginTop = '3px';
            errorSpan.textContent = message;
            field.parentNode.appendChild(errorSpan);
        }
    }

    function validateAmountField(field) {
        var value = field.value.trim();
        if (!value) { resetFieldStyle(field); return; }

        if (!/^[0-9]+(\.[0-9]*)?$/.test(value)) {
            highlightFieldError(field, 'Amount must contain only numbers and one decimal point.');
            return;
        }
        if (value.indexOf('.') === -1) {
            highlightFieldError(field, 'Amount must contain a decimal point (e.g., 100.0).');
            return;
        }
        var parts = value.split('.');
        if (parts[1] && parts[1].length > 2) {
            highlightFieldError(field, 'Amount supports up to 2 decimal places only.');
            return;
        }
        var amt = parseFloat(value);
        if (isNaN(amt) || amt < 1) {
            highlightFieldError(field, 'Amount must be greater than or equal to 1.');
            return;
        }
        resetFieldStyle(field);
    }

    function validateForm(form) {
        var emptyFieldLabels = [];
        var firstInvalidField = null;
        var allValid = true;

        var allErrors = form.querySelectorAll('.field-error-msg');
        for (var i = 0; i < allErrors.length; i++) allErrors[i].remove();
        var allInputs = form.querySelectorAll('input, select');
        for (var i = 0; i < allInputs.length; i++) {
            allInputs[i].style.borderColor = '';
            allInputs[i].style.backgroundColor = '';
        }

        var requiredFields = form.querySelectorAll('input[required], select[required]');
        for (var i = 0; i < requiredFields.length; i++) {
            var field = requiredFields[i];
            if (field.disabled) continue;
            if (!field.value || !field.value.trim()) {
                var formField = field.closest('.form-field');
                var label = formField ? formField.querySelector('label') : null;
                var labelText = label ? label.textContent.replace('*', '').replace(/\(.*\)/, '').trim() : field.name;
                emptyFieldLabels.push(labelText);
                highlightFieldError(field, labelText + ' is required');
                if (!firstInvalidField) firstInvalidField = field;
            }
        }

        if (emptyFieldLabels.length > 0) {
            alert('Please fill in the following required fields:\n\n' + emptyFieldLabels.map(function(l) { return '\u2022 ' + l; }).join('\n'));
            if (firstInvalidField) firstInvalidField.focus();
            return false;
        }

        var patternFields = form.querySelectorAll('input[pattern]');
        for (var i = 0; i < patternFields.length; i++) {
            var field = patternFields[i];
            if (field.disabled || !field.value) continue;
            var regex = new RegExp(field.getAttribute('pattern'));
            if (!regex.test(field.value)) {
                highlightFieldError(field, field.title || 'Invalid format');
                allValid = false;
                if (!firstInvalidField) firstInvalidField = field;
            }
        }

        var amountField = form.querySelector('input[name="amount"]') || form.querySelector('input[name="refund_amount"]');
        if (amountField && amountField.value) {
            var amtVal = amountField.value.trim();
            if (amtVal.indexOf('.') === -1) {
                highlightFieldError(amountField, 'Amount must contain a decimal point (e.g., 100.0).');
                allValid = false;
                if (!firstInvalidField) firstInvalidField = amountField;
            } else {
                var amt = parseFloat(amtVal);
                var parts = amtVal.split('.');
                if (parts[1] && parts[1].length > 2) {
                    highlightFieldError(amountField, 'Amount supports up to 2 decimal places only.');
                    allValid = false;
                    if (!firstInvalidField) firstInvalidField = amountField;
                } else if (isNaN(amt) || amt < 1) {
                    highlightFieldError(amountField, 'Amount must be greater than or equal to 1.');
                    allValid = false;
                    if (!firstInvalidField) firstInvalidField = amountField;
                }
            }
        }

        if (!allValid) {
            alert('Please correct the highlighted fields.');
            if (firstInvalidField) firstInvalidField.focus();
            return false;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('form');

        for (var f = 0; f < forms.length; f++) {
            var form = forms[f];

            var patternFields = form.querySelectorAll('input[pattern]');
            for (var i = 0; i < patternFields.length; i++) {
                (function (field) {
                    field.addEventListener('input', function () {
                        if (!field.value) { resetFieldStyle(field); return; }
                        var regex = new RegExp(field.getAttribute('pattern'));
                        if (!regex.test(field.value)) {
                            highlightFieldError(field, field.title || 'Invalid format');
                        } else {
                            resetFieldStyle(field);
                        }
                    });
                })(patternFields[i]);
            }

            var amountFields = form.querySelectorAll('input[name="amount"], input[name="refund_amount"]');
            for (var i = 0; i < amountFields.length; i++) {
                (function (field) {
                    field.addEventListener('input', function () { validateAmountField(field); });
                })(amountFields[i]);
            }

            (function (frm) {
                frm.addEventListener('submit', function (e) {
                    if (!validateForm(frm)) e.preventDefault();
                });
            })(form);
        }
    });
})();
