<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Engine\ActionFilter;

class Ceh9WebFormComponent extends CBitrixComponent implements Controllerable
{
    protected $errorCollection;

    const FIELD_TYPES_WITH_SID = ['dropdown', 'radio', 'checkbox', 'multiselect'];

    const TEXT_FIELD_TYPES = ['text', 'email', 'tel', 'url', 'date', 'integer', 'float', 'textarea'];
    const CACHE_VERSION = 2;

    const FIELD_TYPES_WITH_SELECTED = ['dropdown', 'multiselect'];
    const FIELD_TYPES_WITH_CHECKED = ['radio', 'checkbox'];

    const DEFAULT_CACHE_TIME = 3600;
    const DEFAULT_FORM_TITLE = 'CEH9_WEBFORM_FORM_TITLE_DEFAULT';
    const DEFAULT_BUTTON_TEXT = 'CEH9_WEBFORM_DEFAULT_BUTTON_TEXT';
    const DEFAULT_SUCCESS_MESSAGE = 'CEH9_WEBFORM_SUCCESS_DEFAULT_MESSAGE';
    const DEFAULT_AGREEMENT_TEXT = 'CEH9_WEBFORM_AGREEMENT_TEXT_DEFAULT';

    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->errorCollection = new ErrorCollection();
    }

    public function configureActions()
    {
        return [
            'submit' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ]
        ];
    }

    protected function listKeysSignedParameters()
    {
        return [
            'WEB_FORM_ID',
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams['WEB_FORM_ID'] = isset($arParams['WEB_FORM_ID']) ? (int)$arParams['WEB_FORM_ID'] : 0;
        $arParams['FORM_TITLE'] = isset($arParams['FORM_TITLE']) 
            ? trim($arParams['FORM_TITLE']) 
            : '';
        $arParams['BUTTON_TEXT'] = (isset($arParams['BUTTON_TEXT']) && trim($arParams['BUTTON_TEXT']) !== '') 
            ? trim($arParams['BUTTON_TEXT']) 
            : Loc::getMessage(self::DEFAULT_BUTTON_TEXT);
        $arParams['SUCCESS_MESSAGE'] = (isset($arParams['SUCCESS_MESSAGE']) && trim($arParams['SUCCESS_MESSAGE']) !== '') 
            ? trim($arParams['SUCCESS_MESSAGE']) 
            : Loc::getMessage(self::DEFAULT_SUCCESS_MESSAGE);
        $arParams['AGREEMENT_TEXT'] = (isset($arParams['AGREEMENT_TEXT']) && trim($arParams['AGREEMENT_TEXT']) !== '') 
            ? trim($arParams['AGREEMENT_TEXT']) 
            : Loc::getMessage(self::DEFAULT_AGREEMENT_TEXT);
        $arParams['PHONE_FIELD_SID'] = isset($arParams['PHONE_FIELD_SID']) 
            ? trim($arParams['PHONE_FIELD_SID']) 
            : '';
        $arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) 
            ? (int)$arParams['CACHE_TIME'] 
            : self::DEFAULT_CACHE_TIME;
        
        return $arParams;
    }

    public function executeComponent()
    {
        if (!$this->checkModules())
        {
            $this->showErrors();
            return;
        }

        $cacheId = [
            'v' => self::CACHE_VERSION,
            'form' => (int)$this->arParams['WEB_FORM_ID'],
            'lang' => defined('LANGUAGE_ID') ? LANGUAGE_ID : '',
        ];

        if ($this->startResultCache(false, $cacheId))
        {
            $this->prepareResult();
            $this->includeComponentTemplate();
        }
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('form'))
        {
            $this->errorCollection[] = new Error(
                Loc::getMessage('CEH9_WEBFORM_ERROR_MODULE_NOT_INSTALLED')
            );
            return false;
        }

        if (empty($this->arParams['WEB_FORM_ID']))
        {
            $this->errorCollection[] = new Error(
                Loc::getMessage('CEH9_WEBFORM_ERROR_FORM_NOT_SELECTED')
            );
            return false;
        }

        return true;
    }

    protected function prepareResult()
    {
        $formId = (int)$this->arParams['WEB_FORM_ID'];
        
        $form = $this->getFormData($formId);
        if (!$form)
        {
            $this->errorCollection[] = new Error(
                Loc::getMessage('CEH9_WEBFORM_ERROR_FORM_NOT_FOUND')
            );
            return;
        }

        $this->arResult['FORM'] = $form;
        $this->arResult['FORM_ID'] = $formId;
        $this->arResult['FORM_TITLE'] = $this->getFormTitle($formId);
        $this->arResult['BUTTON_TEXT'] = $this->arParams['BUTTON_TEXT'];
        $this->arResult['SUCCESS_MESSAGE'] = $this->arParams['SUCCESS_MESSAGE'];
        $this->arResult['AGREEMENT_TEXT'] = $this->arParams['AGREEMENT_TEXT'];
        $this->arResult['PHONE_FIELD_SID'] = $this->arParams['PHONE_FIELD_SID'];
        $this->arResult['FIELDS'] = $this->getFormFields($formId);
        $this->arResult['SIGNED_PARAMETERS'] = $this->getFormSignedParameters();
    }

    protected function getFormData($formId)
    {
        $rsForm = CForm::GetByID($formId);
        return $rsForm ? $rsForm->Fetch() : false;
    }

    protected function getFormTitle($formId)
    {
        if (!empty($this->arParams['FORM_TITLE']))
        {
            return str_replace('#FORM_ID#', $formId, $this->arParams['FORM_TITLE']);
        }

        $defaultTitle = Loc::getMessage(self::DEFAULT_FORM_TITLE);
        return str_replace('#FORM_ID#', $formId, $defaultTitle);
    }

    protected function getFormFields($formId)
    {
        $fields = [];
        
        $rsFields = CFormField::GetList(
            $formId,
            'ALL',
            's_c_sort',
            'asc',
            ['ACTIVE' => 'Y']
        );

        if (!$rsFields)
        {
            return $fields;
        }

        while ($field = $rsFields->Fetch())
        {
            $fieldId = (int)$field['ID'];
            $fieldData = $this->processField($field, $fieldId);
            
            if ($fieldData)
            {
                $fields[] = $fieldData;
            }
        }

        return $fields;
    }

    protected function processField(array $field, $fieldId)
    {
        $answers = $this->getFieldAnswers($fieldId);
        
        if (empty($answers))
        {
            return null;
        }

        $fieldType = $this->determineFieldType($answers, $field);
        $defaultValue = $this->getDefaultValue($answers, $fieldType);
        $processedAnswers = $this->processAnswers($answers, $fieldType);
        $fieldName = $this->buildFieldName($fieldType, $field, $fieldId, $answers);

        return [
            'ID' => $fieldId,
            'SID' => $field['SID'],
            'NAME' => $fieldName,
            'TITLE' => $field['TITLE'],
            'TYPE' => $fieldType,
            'REQUIRED' => $field['REQUIRED'] === 'Y',
            'SORT' => (int)$field['C_SORT'],
            'DEFAULT_VALUE' => $defaultValue,
            'ANSWERS' => $processedAnswers,
        ];
    }

    protected function getFieldAnswers($fieldId)
    {
        $answers = [];
        $rsAnswers = CFormAnswer::GetList($fieldId, 'sort', 'asc', ['ACTIVE' => 'Y']);
        
        if (!$rsAnswers)
        {
            return $answers;
        }

        while ($answer = $rsAnswers->Fetch())
        {
            $answers[] = $answer;
        }

        return $answers;
    }

    protected function determineFieldType(array $answers, array $field)
    {
        foreach ($answers as $answer)
        {
            if (!empty($answer['FIELD_TYPE']))
            {
                return $answer['FIELD_TYPE'];
            }
        }

        return isset($field['FIELD_TYPE']) ? $field['FIELD_TYPE'] : '';
    }

    protected function getDefaultValue(array $answers, $fieldType)
    {
        if (!in_array($fieldType, self::TEXT_FIELD_TYPES, true))
        {
            return '';
        }

        foreach ($answers as $answer)
        {
            if (!empty($answer['VALUE']))
            {
                return $answer['VALUE'];
            }
        }

        return '';
    }

    protected function processAnswers(array $answers, $fieldType)
    {
        $processed = [];

        foreach ($answers as $answer)
        {
            $isSelected = $this->isAnswerSelected($answer, $fieldType);
            $isChecked = $this->isAnswerChecked($answer, $fieldType);

            $processed[] = [
                'ID' => (int)$answer['ID'],
                'MESSAGE' => $answer['MESSAGE'],
                'VALUE' => $answer['VALUE'],
                'FIELD_TYPE' => $answer['FIELD_TYPE'],
                'SELECTED' => $isSelected,
                'CHECKED' => $isChecked,
            ];
        }

        return $processed;
    }

    protected function isAnswerSelected(array $answer, $fieldType)
    {
        return in_array($fieldType, self::FIELD_TYPES_WITH_SELECTED, true)
            && $answer['FIELD_PARAM'] === 'SELECTED';
    }

    protected function isAnswerChecked(array $answer, $fieldType)
    {
        return in_array($fieldType, self::FIELD_TYPES_WITH_CHECKED, true)
            && $answer['FIELD_PARAM'] === 'CHECKED';
    }

    protected function buildFieldName($fieldType, array $field, $fieldId, array $answers = [])
    {
        $typePrefix = strtolower($fieldType);
        
        if (in_array($fieldType, self::FIELD_TYPES_WITH_SID, true))
        {
            return sprintf('form_%s_%s', $typePrefix, $field['SID']);
        }

        $answerId = 0;
        if (!empty($answers) && isset($answers[0]['ID']))
        {
            $answerId = (int)$answers[0]['ID'];
        }

        return sprintf('form_%s_%d', $typePrefix, ($answerId > 0 ? $answerId : (int)$fieldId));
    }

    protected function getFormSignedParameters()
    {
        $componentName = $this->getName();
        return ParameterSigner::signParameters($componentName, [
            'WEB_FORM_ID' => $this->arParams['WEB_FORM_ID'],
        ]);
    }

    public function submitAction($formData)
    {
        if (!Loader::includeModule('form'))
        {
            return $this->buildErrorResponse(
                Loc::getMessage('CEH9_WEBFORM_ERROR_MODULE_NOT_INSTALLED')
            );
        }

        $formId = isset($this->arParams['WEB_FORM_ID']) ? (int)$this->arParams['WEB_FORM_ID'] : 0;
        
        if ($formId <= 0)
        {
            return $this->buildErrorResponse(
                Loc::getMessage('CEH9_WEBFORM_ERROR_FORM_ID_NOT_SPECIFIED')
            );
        }

        if (!$this->checkSession())
        {
            return $this->buildErrorResponse(
                Loc::getMessage('CEH9_WEBFORM_ERROR_SESSION_EXPIRED')
            );
        }

        $formData = $this->normalizeFormData($formId, $formData);

        $validationErrors = $this->validateForm($formId, $formData);
        if (!empty($validationErrors))
        {
            return $this->buildValidationErrorResponse($validationErrors);
        }

        $resultId = $this->saveFormResult($formId, $formData);
        if (!$resultId)
        {
            return $this->buildErrorResponse(
                Loc::getMessage('CEH9_WEBFORM_ERROR_SAVE_FAILED')
            );
        }

        $this->sendNotifications($formId, $resultId);

        return $this->buildSuccessResponse($resultId);
    }

    protected function checkSession()
    {
        return check_bitrix_sessid();
    }

    protected function validateForm($formId, array $formData)
    {
        $errors = CForm::Check($formId, $formData, false, 'Y', 'Y');
        
        if (empty($errors))
        {
            return [];
        }

        if (!is_array($errors))
        {
            return [$errors];
        }

        return $this->normalizeValidationErrors($errors);
    }

    protected function normalizeFormData($formId, array $formData)
    {
        $fields = $this->getFormFields($formId);
        if (empty($fields))
        {
            return $formData;
        }

        foreach ($fields as $field)
        {
            if (!isset($field['TYPE']) || $field['TYPE'] !== 'date')
            {
                continue;
            }

            $name = isset($field['NAME']) ? $field['NAME'] : '';
            if ($name === '' || !isset($formData[$name]))
            {
                $qid = isset($field['ID']) ? (int)$field['ID'] : 0;
                $oldName = ($qid > 0) ? ('form_date_' . $qid) : '';
                if ($oldName !== '' && isset($formData[$oldName]) && !isset($formData[$name]))
                {
                    $formData[$name] = $formData[$oldName];
                }

                if (!isset($formData[$name]))
                {
                    continue;
                }
            }

            $value = $formData[$name];
            if (!is_string($value))
            {
                continue;
            }

            $value = trim($value);
            $parts = $this->parseDateParts($value);
            if (!empty($parts))
            {
                $formData[$name] = $this->formatDateForSite($parts['Y'], $parts['M'], $parts['D']);
            }
        }

        return $formData;
    }

    protected function parseDateParts($value)
    {
        // Accepts: YYYY-MM-DD, DD.MM.YYYY, DD/MM/YYYY
        $value = trim((string)$value);

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m))
        {
            return ['Y' => (int)$m[1], 'M' => (int)$m[2], 'D' => (int)$m[3]];
        }

        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $value, $m))
        {
            return ['Y' => (int)$m[3], 'M' => (int)$m[2], 'D' => (int)$m[1]];
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m))
        {
            return ['Y' => (int)$m[3], 'M' => (int)$m[2], 'D' => (int)$m[1]];
        }

        return [];
    }

    protected function formatDateForSite($year, $month, $day)
    {
        $fmt = '';
        if (class_exists('CSite'))
        {
            $fmt = (string)CSite::GetDateFormat('SHORT');
        }
        if ($fmt === '' && defined('FORMAT_DATE'))
        {
            $fmt = (string)FORMAT_DATE;
        }
        if ($fmt === '')
        {
            $fmt = 'DD.MM.YYYY';
        }

        $out = $fmt;
        $out = str_replace('YYYY', sprintf('%04d', (int)$year), $out);
        $out = str_replace('YY', sprintf('%02d', (int)$year % 100), $out);
        $out = str_replace('MM', sprintf('%02d', (int)$month), $out);
        $out = str_replace('DD', sprintf('%02d', (int)$day), $out);

        $out = str_replace('M', (string)(int)$month, $out);
        $out = str_replace('D', (string)(int)$day, $out);

        return $out;
    }

    protected function normalizeValidationErrors(array $errors)
    {
        $normalized = [];

        foreach ($errors as $error)
        {
            if (is_array($error))
            {
                $message = isset($error['MESSAGE']) ? $error['MESSAGE'] : (isset($error['TEXT']) ? $error['TEXT'] : '');
                if (!empty($message))
                {
                    $normalized[] = $message;
                }
            }
            elseif (!empty($error))
            {
                $normalized[] = $error;
            }
        }

        return array_filter($normalized);
    }

    protected function saveFormResult($formId, array $formData)
    {
        return CFormResult::Add($formId, $formData);
    }

    protected function sendNotifications($formId, $resultId)
    {
        CFormCRM::onResultAdded($formId, $resultId);
        CFormResult::SetEvent($resultId);
        CFormResult::Mail($resultId);
    }

    protected function buildErrorResponse($message)
    {
        return [
            'status' => 'error',
            'message' => $message,
        ];
    }

    protected function buildValidationErrorResponse(array $errors)
    {
        return [
            'status' => 'error',
            'message' => Loc::getMessage('CEH9_WEBFORM_ERROR_VALIDATION'),
            'errors' => $errors,
        ];
    }

    protected function buildSuccessResponse($resultId)
    {
        $message = isset($this->arParams['SUCCESS_MESSAGE']) 
            ? $this->arParams['SUCCESS_MESSAGE'] 
            : Loc::getMessage(self::DEFAULT_SUCCESS_MESSAGE);

        return [
            'status' => 'success',
            'message' => $message,
            'result_id' => $resultId,
        ];
    }

    protected function showErrors()
    {
        foreach ($this->errorCollection as $error)
        {
            ShowError($error->getMessage());
        }
    }

    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }
}
