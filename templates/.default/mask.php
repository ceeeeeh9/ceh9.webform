<?php 
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) 
{
    die();
}

CJSCore::Init(array("masked_input"));
?>
<script>
BX.ready(function() {
    var wrapper = document.getElementById('ceh9-webform-<?= $formId ?>');
    if (!wrapper) return;

	var phoneInput = wrapper.querySelector('.sid_<?= $arResult['PHONE_FIELD_SID'] ?>');
	if (phoneInput) {
		var phoneMask = new BX.MaskedInput({
			mask: '<?= CUtil::JSEscape($arResult['PHONE_FIELD_MASK']) ?>',
			input: phoneInput,
			placeholder: '_'
		});
		phoneMask.setValue('_');
	}
});
</script>