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
$buttonText = $arResult['BUTTON_TEXT'];
?>

<div class="ceh9-webform-modal-wrapper">
    <button type="button" class="ceh9-webform-modal-button" id="ceh9-webform-modal-button-<?= $formId ?>">
        <?= htmlspecialchars($buttonText) ?>
    </button>
</div>

<?php require_once __DIR__ . '/script.php'; ?>