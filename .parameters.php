<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

if (!Loader::includeModule("form")) return;

$arForms = [];
$siteFilter = !empty($_REQUEST["site"]) ? ["SITE" => $_REQUEST["site"]] : [];
$dbForms = CForm::GetList('s_sort', 'asc', $siteFilter);
while ($arForm = $dbForms->Fetch())
{
    $arForms[$arForm['ID']] = '[' . $arForm['ID'] . '] ' . $arForm['NAME'];
}

$arComponentParameters = [
    "GROUPS" => [
        "BASE" => [
            "NAME" => Loc::getMessage("CEH9_WEBFORM_GROUP_BASE"),
            "SORT" => 100,
        ],
        "VISUAL" => [
            "NAME" => Loc::getMessage("CEH9_WEBFORM_GROUP_VISUAL"),
            "SORT" => 300,
        ],
    ],
    "PARAMETERS" => [
        "WEB_FORM_ID" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("CEH9_WEBFORM_WEB_FORM_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arForms,
            "REFRESH" => "N",
            "ADDITIONAL_VALUES" => "Y",
            "DEFAULT" => "",
        ],
        "FORM_TITLE" => [
            "PARENT" => "VISUAL",
            "NAME" => Loc::getMessage("CEH9_WEBFORM_FORM_TITLE"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "BUTTON_TEXT" => [
            "PARENT" => "VISUAL",
            "NAME" => Loc::getMessage("CEH9_WEBFORM_BUTTON_TEXT"),
            "TYPE" => "STRING",
            "DEFAULT" => Loc::getMessage("CEH9_WEBFORM_BUTTON_TEXT_DEFAULT"),
        ],
        "SUCCESS_MESSAGE" => [
            "PARENT" => "VISUAL",
            "NAME" => Loc::getMessage("CEH9_WEBFORM_SUCCESS_MESSAGE"),
            "TYPE" => "STRING",
            "DEFAULT" => Loc::getMessage("CEH9_WEBFORM_SUCCESS_MESSAGE_DEFAULT"),
        ],
        "AGREEMENT_TEXT" => [
            "PARENT" => "VISUAL",
            "NAME" => Loc::getMessage("CEH9_WEBFORM_AGREEMENT_TEXT"),
            "TYPE" => "TEXT",
            "DEFAULT" => Loc::getMessage("CEH9_WEBFORM_AGREEMENT_TEXT_DEFAULT"),
        ],
        "PHONE_FIELD_SID" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("CEH9_WEBFORM_PHONE_FIELD_SID"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "CACHE_TIME" => [
            "DEFAULT" => "3600",
        ],
    ],
];
