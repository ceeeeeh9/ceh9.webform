<?php 
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) 
{
    die();
}

CJSCore::Init(array("jquery3", "masked_input"));
?>
<script>
(function() {
    var formElement = document.querySelector('#ceh9-webform-<?= $formId ?> .ceh9-webform-form');
    if (!formElement) return;
    
    var formId = formElement.getAttribute('data-form-id');
    var signedParams = formElement.getAttribute('data-signed-params');
    var componentName = formElement.getAttribute('data-component-name');
    var messagesContainer = formElement.querySelector('.ceh9-webform-messages');
    var submitButton = formElement.querySelector('.ceh9-webform-button');
        
    function checkRequiredFields() {
        var requiredFields = formElement.querySelectorAll('[required]');
        var allFilled = true;
        
        if (!requiredFields || requiredFields.length === 0) {
            if (submitButton) {
                submitButton.disabled = false;
            }
            return true;
        }
        
        for (var i = 0; i < requiredFields.length; i++) {
            var field = requiredFields[i];
            var isEmpty = false;
            
            if (field.type === 'checkbox') {
                var checkboxes = formElement.querySelectorAll('input[type="checkbox"][name="' + field.name + '"]');
                var checked = false;
                for (var j = 0; j < checkboxes.length; j++) {
                    if (checkboxes[j].checked) {
                        checked = true;
                        break;
                    }
                }
                isEmpty = !checked;
            } else if (field.type === 'radio') {
                var radios = formElement.querySelectorAll('input[type="radio"][name="' + field.name + '"]');
                var checked = false;
                for (var j = 0; j < radios.length; j++) {
                    if (radios[j].checked) {
                        checked = true;
                        break;
                    }
                }
                isEmpty = !checked;
            } else if (field.tagName === 'SELECT') {
                isEmpty = !field.value || field.value === '';
            } else if (field.type === 'file') {
                isEmpty = !field.files || field.files.length === 0;
            } else {
                isEmpty = !field.value || field.value.trim() === '';
            }
            
            if (isEmpty) {
                allFilled = false;
                break;
            }
        }
        
        if (submitButton) {
            submitButton.disabled = !allFilled;
        }
        
        return allFilled;
    }
    
    checkRequiredFields();
    
    formElement.addEventListener('input', checkRequiredFields);
    formElement.addEventListener('change', checkRequiredFields);
    
    formElement.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!checkRequiredFields()) {
            messagesContainer.className = 'ceh9-webform-messages ceh9-webform-messages-error';
            messagesContainer.innerHTML = '<div>Пожалуйста, заполните все обязательные поля</div>';
            return;
        }
        
        messagesContainer.innerHTML = '';
        messagesContainer.className = 'ceh9-webform-messages';
        
        if (submitButton) {
            submitButton.disabled = true;
        }
        
        var formData = new FormData(formElement);
        var data = {};
        for (var pair of formData.entries()) {
            if (data[pair[0]]) {
                if (!Array.isArray(data[pair[0]])) {
                    data[pair[0]] = [data[pair[0]]];
                }
                data[pair[0]].push(pair[1]);
            } else {
                data[pair[0]] = pair[1];
            }
        }
        
        BX.ajax.runComponentAction(componentName, 'submit', {
            mode: 'class',
            signedParameters: signedParams,
            data: {
                formData: data
            }
        }).then(function(response) {
            if (response.status === 'success') {
                var formFields = formElement.querySelectorAll('.ceh9-webform-field');
                if (formFields && formFields.length > 0) {
                    for (var i = 0; i < formFields.length; i++) {
                        formFields[i].style.display = 'none';
                    }
                }
                if (submitButton) {
                    submitButton.style.display = 'none';
                }
                
                messagesContainer.className = 'ceh9-webform-messages ceh9-webform-messages-success ceh9-webform-success-only';
                messagesContainer.innerHTML = '<div>' + (response.data.message || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>';
            } else {
                var errorHtml = '<div>' + (response.data.message || 'Ошибка отправки формы').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>';
                if (response.data.errors && Array.isArray(response.data.errors) && response.data.errors.length > 0) {
                    errorHtml += '<ul>';
                    for (var i = 0; i < response.data.errors.length; i++) {
                        errorHtml += '<li>' + String(response.data.errors[i]).replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</li>';
                    }
                    errorHtml += '</ul>';
                }
                messagesContainer.className = 'ceh9-webform-messages ceh9-webform-messages-error';
                messagesContainer.innerHTML = errorHtml;
                checkRequiredFields();
            }
        }, function(error) {
            messagesContainer.className = 'ceh9-webform-messages ceh9-webform-messages-error';
            messagesContainer.innerHTML = '<div>Ошибка отправки формы. Попробуйте позже.</div>';
            checkRequiredFields();
        });
    });
})();
</script>