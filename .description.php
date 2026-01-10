<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    "NAME" => Loc::getMessage("CEH9_WEBFORM_COMPONENT_NAME"),
    "DESCRIPTION" => Loc::getMessage("CEH9_WEBFORM_COMPONENT_DESCRIPTION"),
    "PATH" => [
        "ID" => "ceh9",
        "NAME" => "CEH9",
    ],
    "CACHE_PATH" => "Y",
];
