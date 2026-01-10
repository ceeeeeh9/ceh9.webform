<?php 
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) 
{
    die();
}

CJSCore::Init(array("popup", "jquery3", "masked_input"));
?>
<script>
(function() {
    var formId = <?= $formId ?>;
    var buttonId = 'ceh9-webform-modal-button-' + formId;
    var button = document.getElementById(buttonId);
    
    if (!button) return;
    
    var signedParams = '<?= CUtil::JSEscape($signedParameters) ?>';
    var componentName = '<?= CUtil::JSEscape($componentName) ?>';
    var formTitle = '<?= CUtil::JSEscape($arResult['FORM_TITLE']) ?>';
    var phoneFieldSid = '<?= CUtil::JSEscape($arResult['PHONE_FIELD_SID']) ?>';
    var phoneFieldMask = '<?= CUtil::JSEscape($arResult['PHONE_FIELD_MASK']) ?>';
    
    var formHtml = '<h2 class="ceh9-webform-title">' + formTitle.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</h2>';
    formHtml += '<form class="ceh9-webform-form" data-form-id="' + formId + '" data-signed-params="' + signedParams.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '" data-component-name="' + componentName.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '">';
    formHtml += '<?= CUtil::JSEscape(bitrix_sessid_post()) ?>';
    formHtml += '<input type="hidden" name="WEB_FORM_ID" value="' + formId + '">';
    
    <?php foreach ($arResult['FIELDS'] as $field): ?>
        formHtml += '<div class="ceh9-webform-field">';
        formHtml += '<label class="ceh9-webform-label" for="<?= htmlspecialcharsbx($field['NAME']) ?>_modal">';
        formHtml += '<?= CUtil::JSEscape($field['TITLE']) ?>';
        <?php if ($field['REQUIRED']): ?>
            formHtml += '<span class="ceh9-webform-label-required">*</span>';
        <?php endif; ?>
        formHtml += '</label>';
        
        <?php
        $fieldName = $field['NAME'];
        
        switch ($field['TYPE']):
            case 'text':
            case 'email': 
            case 'tel':
            case 'url':
            case 'date':
            case 'integer':
            case 'float':
                $inputType = 'text';
                if ($field['TYPE'] == 'email') $inputType = 'email';
                elseif ($field['TYPE'] == 'tel') $inputType = 'tel';
                elseif ($field['TYPE'] == 'url') $inputType = 'url';
                elseif ($field['TYPE'] == 'date') $inputType = 'date';
                elseif ($field['TYPE'] == 'integer' || $field['TYPE'] == 'float') $inputType = 'number';
                $defaultValue = '';
                if (!empty($field['DEFAULT_VALUE'])) {
                    $defaultValue = CUtil::JSEscape($field['DEFAULT_VALUE']);
                }
                ?>
                formHtml += '<input type="<?= $inputType ?>" name="<?= CUtil::JSEscape($fieldName) ?>" id="<?= htmlspecialcharsbx($fieldName) ?>_modal" class="ceh9-webform-input <?= 'sid_' . htmlspecialcharsbx($field['SID']) ?>" value="<?= $defaultValue ?>" <?= $field['REQUIRED'] ? 'required' : '' ?>>';
                <?php
                break;
            
            case 'textarea':
                $defaultValue = '';
                if (!empty($field['DEFAULT_VALUE'])) {
                    $defaultValue = CUtil::JSEscape($field['DEFAULT_VALUE']);
                }
                ?>
                formHtml += '<textarea name="<?= CUtil::JSEscape($fieldName) ?>" id="<?= htmlspecialcharsbx($fieldName) ?>_modal" class="ceh9-webform-textarea" rows="5" <?= $field['REQUIRED'] ? 'required' : '' ?>><?= $defaultValue ?></textarea>';
                <?php
                break;
            
            case 'dropdown':
                ?>
                formHtml += '<select name="<?= CUtil::JSEscape($fieldName) ?>" id="<?= htmlspecialcharsbx($fieldName) ?>_modal" class="ceh9-webform-select" <?= $field['REQUIRED'] ? 'required' : '' ?>>';
                formHtml += '<option value="">-- Выберите --</option>';
                <?php foreach ($field['ANSWERS'] as $answer): ?>
                    formHtml += '<option value="<?= htmlspecialcharsbx($answer['ID']) ?>" <?= !empty($answer['SELECTED']) ? 'selected' : '' ?>><?= CUtil::JSEscape($answer['MESSAGE']) ?></option>';
                <?php endforeach; ?>
                formHtml += '</select>';
                <?php
                break;
            
            case 'radio':
                ?>
                formHtml += '<div class="ceh9-webform-radio-group">';
                <?php foreach ($field['ANSWERS'] as $answer): ?>
                    formHtml += '<div class="ceh9-webform-radio-item">';
                    formHtml += '<input type="radio" name="<?= CUtil::JSEscape($fieldName) ?>" value="<?= htmlspecialcharsbx($answer['ID']) ?>" id="<?= htmlspecialcharsbx($fieldName) ?>_<?= $answer['ID'] ?>_modal" class="ceh9-webform-radio-input" <?= !empty($answer['CHECKED']) ? 'checked' : '' ?> <?= $field['REQUIRED'] ? 'required' : '' ?>>';
                    formHtml += '<label class="ceh9-webform-radio-label" for="<?= htmlspecialcharsbx($fieldName) ?>_<?= $answer['ID'] ?>_modal"><?= CUtil::JSEscape($answer['MESSAGE']) ?></label>';
                    formHtml += '</div>';
                <?php endforeach; ?>
                formHtml += '</div>';
                <?php
                break;
            
            case 'checkbox':
                ?>
                formHtml += '<div class="ceh9-webform-checkbox-group">';
                <?php foreach ($field['ANSWERS'] as $answer): ?>
                    formHtml += '<div class="ceh9-webform-checkbox-item">';
                    formHtml += '<input type="checkbox" name="<?= CUtil::JSEscape($fieldName) ?>[]" value="<?= htmlspecialcharsbx($answer['ID']) ?>" id="<?= htmlspecialcharsbx($fieldName) ?>_<?= $answer['ID'] ?>_modal" class="ceh9-webform-checkbox-input" <?= !empty($answer['CHECKED']) ? 'checked' : '' ?>>';
                    formHtml += '<label class="ceh9-webform-checkbox-label" for="<?= htmlspecialcharsbx($fieldName) ?>_<?= $answer['ID'] ?>_modal"><?= CUtil::JSEscape($answer['MESSAGE']) ?></label>';
                    formHtml += '</div>';
                <?php endforeach; ?>
                formHtml += '</div>';
                <?php
                break;
            
            case 'file':
                ?>
                formHtml += '<input type="file" name="<?= CUtil::JSEscape($fieldName) ?>" id="<?= htmlspecialcharsbx($fieldName) ?>_modal" class="ceh9-webform-input" <?= $field['REQUIRED'] ? 'required' : '' ?>>';
                <?php
                break;
        endswitch;
        ?>
        
        formHtml += '</div>';
    <?php endforeach; ?>
    
    formHtml += '<div class="ceh9-webform-field ceh9-webform-agreement">';
    formHtml += '<label class="ceh9-webform-agreement-label">';
    formHtml += '<input type="checkbox" name="agreement" class="ceh9-webform-agreement-checkbox" required>';
    formHtml += '<span class="ceh9-webform-agreement-text"><?= str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '\\n', '\\r'], $arResult['AGREEMENT_TEXT']) ?></span>';
    formHtml += '</label>';
    formHtml += '</div>';
    
    formHtml += '<div class="ceh9-webform-messages"></div>';
    formHtml += '<button type="submit" class="ceh9-webform-button"><?= CUtil::JSEscape($arResult['BUTTON_TEXT']) ?></button>';
    formHtml += '</form>';
    
    var addReviewForm = null;
    
    function createPopup() {
        var existingPopup = BX.PopupWindowManager.getPopupById('ceh9-webform-modal-' + formId);
        if (existingPopup) {
            existingPopup.destroy();
        }
        
        var contentDiv = document.createElement('div');
        contentDiv.className = 'ceh9-webform-modal-content';
        contentDiv.innerHTML = formHtml;
        
        addReviewForm = new BX.PopupWindow('ceh9-webform-modal-' + formId, null, {
            content: contentDiv,
            titleBar: false,
            closeIcon: true,
            closeByEsc: true,
            autoHide: false,
            zIndex: 1000,
            width: 600,
            height: 'auto',
            className: 'ceh9-webform-popup',
            contentPadding: 0,
            contentColor: '#fff',
            angle: false,
            overlay: {
                backgroundColor: 'black',
                opacity: 50
            },
            events: {
                onPopupShow: function() {
                    var popup = this;
                    var formElement = null;
                    
                    if (contentDiv) {
                        formElement = contentDiv.querySelector('.ceh9-webform-form');
                    }
                    if (!formElement && popup.contentContainer) {
                        formElement = popup.contentContainer.querySelector('.ceh9-webform-form');
                    }
                    if (!formElement && popup.content) {
                        formElement = popup.content.querySelector('.ceh9-webform-form');
                    }
                    
                    if (formElement) {
                        var messagesContainer = formElement.querySelector('.ceh9-webform-messages');
                        var submitButton = formElement.querySelector('.ceh9-webform-button');
                        var formFields = formElement.querySelectorAll('.ceh9-webform-field');
                    
                        if (phoneFieldSid && phoneFieldSid !== '') {
                            var initMask = function(attempt) {
                                attempt = attempt || 0;
                                
                                if (attempt > 30) {
                                    return;
                                }
                                
                                if (typeof BX === 'undefined') {
                                    setTimeout(function() { initMask(attempt + 1); }, 100);
                                    return;
                                }
                                
                                if (!BX.MaskedInput) {
                                    if (typeof BX.load === 'function') {
                                        BX.load(['masked_input'], function() {
                                            setTimeout(function() { initMask(attempt + 1); }, 100);
                                        });
                                    } else {
                                        setTimeout(function() { initMask(attempt + 1); }, 100);
                                    }
                                    return;
                                }
                                
                                var selector = '.sid_' + phoneFieldSid;
                                var phoneInput = null;
                                
                                phoneInput = document.querySelector(selector);
                                
                                if (!phoneInput && formElement) {
                                    phoneInput = formElement.querySelector(selector);
                                }
                                if (!phoneInput && contentDiv) {
                                    phoneInput = contentDiv.querySelector(selector);
                                }
                                if (!phoneInput && popup.contentContainer) {
                                    phoneInput = popup.contentContainer.querySelector(selector);
                                }
                                if (!phoneInput && popup.content) {
                                    phoneInput = popup.content.querySelector(selector);
                                }
                                
                                if (phoneInput) {
                                    try {
                                        var maskInstance = new BX.MaskedInput({
                                            mask: phoneFieldMask,
                                            input: phoneInput,
                                            placeholder: '_'
                                        });
                                        setTimeout(function() {
                                            if (maskInstance && typeof maskInstance.setValue === 'function') {
                                                maskInstance.setValue('_');
                                            }
                                        }, 50);
                                    } catch(e) {
                                        console.error('Mask initialization error:', e);
                                    }
                                } else {
                                    setTimeout(function() { initMask(attempt + 1); }, 100);
                                }
                            };
                            
                            setTimeout(function() { initMask(0); }, 300);
                        }
                    
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
                        
                        setTimeout(function() {
                            checkRequiredFields();
                        }, 100);
                        
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
                                    
                                    setTimeout(function() {
                                        addReviewForm.adjustPosition();
                                    }, 50);
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
                    }
                },
                onPopupClose: function() {
                    this.destroy();
                    addReviewForm = null;
                }
            }
        });
    }
    
    button.addEventListener('click', function() {
        createPopup();
        if (addReviewForm) {
            addReviewForm.show();
        }
    });
})();
</script>