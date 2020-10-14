<?php
  echo "\n4 - Exportando os Atributos dos Produtos....";

  $file = fopen(CSV_PATH.$filesNames[4].'.csv', 'wr');

  // Exportando as colunas Bases
  $attributeColumns = array('# code', 'components');
  foreach ($condenserOrder as $fieldKey => $fieldValue) {
    array_push($attributeColumns, $fieldValue);
  }
  array_push($attributeColumns, 'catalogVersion');
  fwrite($file, implode(";", $attributeColumns) . "\n");

  // Exportando os atributos dos produtos
  foreach ($products as $key => $product) {
    if (count($product['descriptionFields']) > 0) {
      $attributeProduct = array();
      foreach ($condenserOrder as $fieldKey => $fieldValue) {
        // Descrição do produto para a chave
        if(isset($product['descriptionFields'][$fieldKey]) and !empty($product['descriptionFields'][$fieldKey]['value'])) {
          $measurementUnity = $product['descriptionFields'][$fieldKey]['unity'];
          $descriptionValue = $product['descriptionFields'][$fieldKey]['value'];
        }
        else {
          $measurementUnity = '';
          $descriptionValue = '';
        }

        // Retorna o valor correto do campo
        $columnValue = getAttributeValueByDefault($fieldValue);
        if (empty($columnValue)) {
          list($columnValueSuccess, $columnValue) = getAttributeValueByDescriptionIndetificator($fieldKey, $descriptionValue);
          if (!$columnValueSuccess) {            
            list($columnValueSuccess, $columnValue) = getAttributeValueByProductAttributes($fieldKey, $product);
            if (!$columnValueSuccess) {
              $columnValue = getAttributeValueByValue($descriptionValue, $measurementUnity);
            }
          }
        }

        $attributeProduct[] = $columnValue;
      }

      $components = array('staged' => '', 'online' => '');
      for ($cont = 0 ; $cont < $product['numberOfComponents'] ; $cont++) {
        $componentCode = 'Evaporadora_' . $product['cod_pro'];
        if ($product['numberOfComponents'] > 1) {
          $componentCode .= '_' . ($cont+1);
        }
        $components['staged'] = $componentCode . ':centralArProductCatalog:Staged';
        $components['online'] = $componentCode . ':centralArProductCatalog:Online';
      }

      // Staged Products
      $productStaged = array_merge(array(
        $product['cod_pro'],
        $components['staged'],
      ),$attributeProduct);
      $productStaged[] = 'centralArProductCatalog:Staged';
      fwrite($file, implode(";", $productStaged) . "\n");

      // Online Product
      $productOnline = array_merge(array(
        $product['cod_pro'],
        $components['online'],
      ),$attributeProduct);
      $productOnline[] = 'centralArProductCatalog:Online';
      fwrite($file, implode(";", $productOnline) . "\n");
    }
  }

  fclose($file);
?>