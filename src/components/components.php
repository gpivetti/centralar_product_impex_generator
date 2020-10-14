<?php
  echo "\n2 - Exportando as Informações dos Componentes....";

  $fileComponent  = fopen(CSV_PATH.$filesNames[2]['csv'][0].'.csv', 'wr');
  $fileProduct    = fopen(CSV_PATH.$filesNames[2]['csv'][1].'.csv', 'wr');

  // Exportando as colunas Bases dos Componentes
  $componentscolumns = array(
    '# code', 
    'name', 
    'componentType',
    'supercategories'
  );
  foreach ($evaporatorOrder[1] as $fieldKey => $fieldValue) {
    array_push($componentscolumns, $fieldValue);
  } 
  array_push($componentscolumns, 'approvalstatus'); 
  array_push($componentscolumns, 'catalogVersion'); 
  fwrite($fileComponent, implode(";", $componentscolumns) . "\n");

  // Exportando as colunas Bases dos Produtos
  $productComponentsColumns = array(
    '# code',
    'components',
    'catalogVersion'
  );
  fwrite($fileProduct, implode(";", $productComponentsColumns) . "\n");  

  // Exportando os dados dos produtos
  foreach ($products as $key => $product) {
    $productsComponentsArray = array();

    // Atributos e descrições ténicas  
    if (count($product['descriptionFields']) > 0) {      
      for ($cont = 0 ; $cont < $product['numberOfComponents'] ; $cont++) {
        // Códigos do Componentes
        $componentName = 'Evaporadora'; 
        $componentCode = 'Evaporadora_' . $product['cod_pro'];
        if ($product['numberOfComponents'] > 1) {
          $componentName .= '_' . ($cont+1);
          $componentCode .= '_' . ($cont+1);
        }

        // componentes
        $productsComponentsArray[] = $componentCode;
  
        $evapType = '';
        if ($cont == 0) {
          if (isset($evaporatorComponentType[$product['id_evaporizadora']])) {
            $evapType = $evaporatorComponentType[$product['id_evaporizadora']];
          }
        } else {
          $evapCodeColumn = 'id_evaporizadora_' . ($cont + 1);
          if (isset($product[$evapCodeColumn]) and !empty($product[$evapCodeColumn])) {
            if (isset($evaporatorComponentType[$product[$evapCodeColumn]])) {
              $evapType = $evaporatorComponentType[$evapCodeColumn];
            }
          }
        }

        // Dados bases do Produto
        $componentsColumnsRows = array(
          $componentCode,      
          $componentName,
          $evapType,
          'ESPECIFICACOES_TECNICAS:centralArClassificationCatalog:1.0' 
        );

        $attributesValuesArray = array();
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
    
          $attributesValuesArray[] = $columnValue;
        }

        // Atributos que faltarão ficaram vazios
        if (count($attributesValuesArray) < count($evaporatorOrder[($cont+1)])) {
          $attrbituesRemaining = count($evaporatorOrder[($cont+1)]) - count($attributesValuesArray);
          for ($contRemaining = 0 ; $contRemaining < $attrbituesRemaining ; $contRemaining++) {
            $attributesValuesArray[] = '';
          }
        }      

        $componentsColumnsRows = array_merge($componentsColumnsRows,$attributesValuesArray);

        // Staged and Online Product
        $componentStaged   = $componentsColumnsRows;
        $componentOnline   = $componentsColumnsRows;
        $componentStaged[] = 'approved';
        $componentStaged[] = 'centralArProductCatalog:Staged';    
        $componentOnline[] = 'approved';
        $componentOnline[] = 'centralArProductCatalog:Online';
        fwrite($fileComponent, implode(";", $componentStaged) . "\n");
        fwrite($fileComponent, implode(";", $componentOnline) . "\n");
      }             
    }

    // Atribuindo os componentes nos produtos
    if (count($productsComponentsArray) > 0) {
      $productsComponentsArrayStaged = '';
      $productsComponentsArrayOnline = '';
      foreach ($productsComponentsArray as $keyComponent => $valueComponent) {
        if ($keyComponent > 0) {
          $productsComponentsArrayStaged .= ',';
          $productsComponentsArrayOnline .= ',';
        }
        $productsComponentsArrayStaged .= trim($valueComponent) . ':centralArProductCatalog:Staged';
        $productsComponentsArrayOnline .= trim($valueComponent) . ':centralArProductCatalog:Online';
      }

      // Staged
      $productComponentsColumnsStagedRows = array(
        $product['cod_pro'],
        $productsComponentsArrayStaged,
        'centralArProductCatalog:Staged',
      );
      fwrite($fileProduct, implode(";", $productComponentsColumnsStagedRows) . "\n");

      // Online
      $productComponentsColumnsOnlineRows = array(
        $product['cod_pro'],
        $productsComponentsArrayOnline,
        'centralArProductCatalog:Online',
      );
      fwrite($fileProduct, implode(";", $productComponentsColumnsOnlineRows) . "\n");
    }
  }

  fclose($fileComponent);
  fclose($fileProduct);
?>