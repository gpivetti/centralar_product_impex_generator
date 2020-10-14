<?php

function getAttributeValueByDefault($fieldValue = '') {
  if (strpos(trim(strtolower($fieldValue)), '[default]') !== false) { // Campo padrão
    $defaultvalue = explode('=', $fieldValue);
    if (isset($defaultvalue[1])) {
      return trim($defaultvalue[1]);
    }
  }
  return '';
}

function getAttributeValueByDescriptionIndetificator($descriptionId = 0, $descriptionValue = "") {
  $descriptionId = getValueByRepeatedColumn($descriptionId);
  if (!empty($descriptionId) and !empty($descriptionValue)) {
    try {
      global $descriptionFieldsFunctions;
      if (isset($descriptionFieldsFunctions[$descriptionId]) and !empty($descriptionFieldsFunctions[$descriptionId])) {
        return array(true, trim($descriptionFieldsFunctions[$descriptionId]::getValue($descriptionValue)));
      }
    }
    catch (Exception $e) {}
  }
  return array(false, '');
}

function getAttributeValueByProductAttributes($attributeName = '', $productObject = false) {
  if (!empty($attributeName) and $productObject) {
    try {
      global $productFieldsFunctions;
      if (isset($productFieldsFunctions[$attributeName]) and !empty($productFieldsFunctions[$attributeName])) {
        return array(true, trim($productFieldsFunctions[$attributeName]::getValue($productObject)));
      }
    }
    catch (Exception $e) {}
  }
  return array(false, '');
}

function getAttributeValueByValue($value = '', $measurementUnity = '') {
  if (!empty($value) and !empty($measurementUnity)) {
    if (trim(strtolower($measurementUnity)) == 'sim/não') {
      if (trim(strtolower($value)) == 'sim') {
        return 'true';
      }
      else {
        return 'false';
      }
    }
  }
  return trim($value);
}

function getValueByRepeatedColumn($descriptionId = '') {
  if (strpos(trim(strtolower($descriptionId)), '>') !== false) {
    $descriptionIdArray = explode('>', $descriptionId);
    if (isset($descriptionIdArray[0]) and !empty($descriptionIdArray[0]) and is_numeric($descriptionIdArray[0])) {
      $descriptionId = trim($descriptionIdArray[0]);
    }
  }
  return $descriptionId;
}
?>