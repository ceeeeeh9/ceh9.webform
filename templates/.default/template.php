<?php 
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) 
{
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */

if (empty($arResult['FORM'])) 
{
    return;
}

$formId = $arResult['FORM_ID'];
$signedParameters = $arResult['SIGNED_PARAMETERS'];
$componentName = $this->getComponent()->getName();
?>

<div class="ceh9-webform-wrapper" id="ceh9-webform-<?= $formId ?>">
    <h2 class="ceh9-webform-title"><?= htmlspecialchars($arResult['FORM_TITLE']) ?></h2>
    
    <form class="ceh9-webform-form" data-form-id="<?= $formId ?>" data-signed-params="<?= htmlspecialcharsbx($signedParameters) ?>" data-component-name="<?= htmlspecialcharsbx($componentName) ?>">
        <?= bitrix_sessid_post() ?>
        
        <input type="hidden" name="WEB_FORM_ID" value="<?= $formId ?>">

        <?php foreach ($arResult['FIELDS'] as $field): ?>
            <div class="ceh9-webform-field">
                <label class="ceh9-webform-label" for="<?= htmlspecialcharsbx($field['NAME']) ?>">
                    <?= htmlspecialchars($field['TITLE']) ?>
                    <?php if ($field['REQUIRED']): ?>
                        <span class="ceh9-webform-label-required">*</span>
                    <?php endif; ?>
                </label>
                        
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
                                    $defaultValue = htmlspecialchars($field['DEFAULT_VALUE']);
                                }
                                ?>
                                <input 
                                    type="<?= $inputType ?>"
                                    name="<?= htmlspecialcharsbx($fieldName) ?>"
                                    id="<?= htmlspecialcharsbx($fieldName) ?>"
                                    class="ceh9-webform-input <?= 'sid_' . $field['SID'] ?>"
                                    value="<?= $defaultValue ?>"
                                    <?= $field['REQUIRED'] ? 'required' : '' ?>
                                >
                                <?php
                                break;
                            
                            case 'textarea':
                                $defaultValue = '';
                                if (!empty($field['DEFAULT_VALUE'])) {
                                    $defaultValue = htmlspecialchars($field['DEFAULT_VALUE']);
                                }
                                ?>
                                <textarea 
                                    name="<?= htmlspecialcharsbx($fieldName) ?>"
                                    id="<?= htmlspecialcharsbx($fieldName) ?>"
                                    class="ceh9-webform-textarea"
                                    rows="5"
                                    <?= $field['REQUIRED'] ? 'required' : '' ?>
                                ><?= $defaultValue ?></textarea>
                                <?php
                                break;
                            
                            case 'dropdown':
                                ?>
                                <select 
                                    name="<?= htmlspecialcharsbx($fieldName) ?>"
                                    id="<?= htmlspecialcharsbx($fieldName) ?>"
                                    class="ceh9-webform-select"
                                    <?= $field['REQUIRED'] ? 'required' : '' ?>
                                >
                                    <option value="">-- Выберите --</option>
                                    <?php foreach ($field['ANSWERS'] as $answer): ?>
                                        <option value="<?= htmlspecialcharsbx($answer['ID']) ?>" <?= !empty($answer['SELECTED']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($answer['MESSAGE']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php
                                break;
                            
                            case 'radio':
                                ?>
                                <div class="ceh9-webform-radio-group">
                                    <?php foreach ($field['ANSWERS'] as $answer): ?>
                                        <div class="ceh9-webform-radio-item">
                                            <input 
                                                type="radio"
                                                name="<?= htmlspecialcharsbx($fieldName) ?>"
                                                value="<?= htmlspecialcharsbx($answer['ID']) ?>"
                                                id="<?= htmlspecialcharsbx($fieldName) ?>_<?= $answer['ID'] ?>"
                                                class="ceh9-webform-radio-input"
                                                <?= !empty($answer['CHECKED']) ? 'checked' : '' ?>
                                                <?= $field['REQUIRED'] ? 'required' : '' ?>
                                            >
                                            <label class="ceh9-webform-radio-label" for="<?= htmlspecialcharsbx($fieldName) ?>_<?= $answer['ID'] ?>">
                                                <?= htmlspecialchars($answer['MESSAGE']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php
                                break;
                            
                            case 'checkbox':
                                ?>
                                <div class="ceh9-webform-checkbox-group">
                                    <?php foreach ($field['ANSWERS'] as $answer): ?>
                                        <div class="ceh9-webform-checkbox-item">
                                            <input 
                                                type="checkbox"
                                                name="<?= htmlspecialcharsbx($fieldName) ?>[]"
                                                value="<?= htmlspecialcharsbx($answer['ID']) ?>"
                                                id="<?= htmlspecialcharsbx($fieldName) ?>_<?= $answer['ID'] ?>"
                                                class="ceh9-webform-checkbox-input"
                                                <?= !empty($answer['CHECKED']) ? 'checked' : '' ?>
                                            >
                                            <label class="ceh9-webform-checkbox-label" for="<?= htmlspecialcharsbx($fieldName) ?>_<?= $answer['ID'] ?>">
                                                <?= htmlspecialchars($answer['MESSAGE']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php
                                break;
                            
                            case 'file':
                                ?>
                                <input 
                                    type="file"
                                    name="<?= htmlspecialcharsbx($fieldName) ?>"
                                    id="<?= htmlspecialcharsbx($fieldName) ?>"
                                    class="ceh9-webform-input"
                                    <?= $field['REQUIRED'] ? 'required' : '' ?>
                                >
                                <?php
                                break;
                        endswitch;
                        ?>
            </div>
        <?php endforeach; ?>
        
        <div class="ceh9-webform-field ceh9-webform-agreement">
            <label class="ceh9-webform-agreement-label">
                <input type="checkbox" name="agreement" class="ceh9-webform-agreement-checkbox" required>
                <span class="ceh9-webform-agreement-text"><?= $arResult['AGREEMENT_TEXT'] ?></span>
            </label>
        </div>
        
        <div class="ceh9-webform-messages"></div>
        
        <button type="submit" class="ceh9-webform-button">
            <?= htmlspecialchars($arResult['BUTTON_TEXT']) ?>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/script.php'; ?>
<?php require_once __DIR__ . '/mask.php'; ?>