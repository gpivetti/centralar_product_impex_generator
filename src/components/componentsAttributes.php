<?php
  echo "\n5 - Exportando as Informações dos Atributos dos Componentes....";

  $file = fopen(CSV_PATH.$filesNames[5].'.csv', 'wr');

  // Exportando as colunas Bases
  $attributeColumns = array('# code');
  foreach ($evaporatorOrder[1] as $fieldKey => $fieldValue) {
    array_push($attributeColumns, $fieldValue);
  }
  array_push($attributeColumns, 'catalogVersion');
  fwrite($file, implode(";", $attributeColumns) . "\n");

  // Exportando os dados dos produtos
  foreach ($products as $key => $product) {
    if (count($product['descriptionFields']) > 0) {
      for ($cont = 0 ; $cont < $product['numberOfComponents'] ; $cont++) {
        $componentCode = 'Evaporadora_' . $product['cod_pro'];
        if ($product['numberOfComponents'] > 1) {
          $componentCode .= '_' . ($cont+1);
        }

        $attributesComponents = array($componentCode);
        foreach ($evaporatorOrder[($cont+1)] as $fieldKey => $fieldValue) {
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
  
          $attributesComponents[] = $columnValue;
        }

        // Staged and Online Product Component
        $attributesComponentsStaged = $attributesComponents;
        $attributesComponentsOnline = $attributesComponents;
        $attributesComponentsStaged[] = 'centralArProductCatalog:Staged';
        $attributesComponentsOnline[] = 'centralArProductCatalog:Online';
        fwrite($file, implode(";", $attributesComponentsStaged) . "\n");
        fwrite($file, implode(";", $attributesComponentsOnline) . "\n");
      }
    }
  }

  fclose($file);
?>