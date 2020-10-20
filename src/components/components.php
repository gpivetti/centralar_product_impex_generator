<?php
  echo "\n2 - Exportando as Informações dos Componentes....";

  $fileComponent   = fopen(CSV_PATH.$filesNames[2]['csv'][0].'.csv', 'wr');
  $fileAttributes  = fopen(CSV_PATH.$filesNames[2]['csv'][1].'.csv', 'wr');
  $fileProduct     = fopen(CSV_PATH.$filesNames[2]['csv'][2].'.csv', 'wr');

  // Exportando as colunas Bases dos Componentes
  $componentscolumns = array(
    '# code', 
    'name', 
    'componentType',
    'supercategories',
    'approvalstatus',
    'catalogVersion'
  );
  fwrite($fileComponent, implode(";", $componentscolumns) . "\n");

  // Exportando as colunas Bases dos Atributos dos Componentes
  $productComponentsAttributesColumns = array(
    '# code'
  );
  foreach ($evaporatorOrder[1] as $fieldKey => $fieldValue) {
    array_push($productComponentsAttributesColumns, $fieldValue);
  } 
  array_push($productComponentsAttributesColumns, 'catalogVersion'); 
  fwrite($fileAttributes, implode(";", $productComponentsAttributesColumns) . "\n");

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
      for ($cont = 1 ; $cont <= $product['numberOfComponents'] ; $cont++) {
        // Códigos do Componentes
        $componentName = 'Evaporadora'; 
        $componentCode = 'Evaporadora_' . $product['cod_pro'];
        if ($product['numberOfComponents'] > 1) {
          $componentName .= ' ' . $cont;
          $componentCode .= '_' . $cont;
        }

        // componentes
        $productsComponentsArray[] = $componentCode;
  
        $evapType = '';
        if ($cont == 1) {
          if (isset($evaporatorComponentType[$product['id_evaporizadora']])) {
            $evapType = $evaporatorComponentType[$product['id_evaporizadora']];
          }
        } else {
          $evapCodeColumn = 'id_evaporizadora_' . $cont;
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

        // Staged and Online Components
        $componentStaged   = $componentsColumnsRows;
        $componentOnline   = $componentsColumnsRows;
        $componentStaged[] = 'approved';
        $componentStaged[] = PRODUCT_CATALOG.':Staged';    
        $componentOnline[] = 'approved';
        $componentOnline[] = PRODUCT_CATALOG.':Online';
        fwrite($fileComponent, implode(";", $componentStaged) . "\n");
        // fwrite($fileComponent, implode(";", $componentOnline) . "\n");

        // Atributos
        $attributesValuesArray = array();
        $componentsAttributesColumnsRows = array($componentCode);
        foreach ($evaporatorOrder[$cont] as $fieldKey => $fieldValue) {
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
        if (count($attributesValuesArray) < count($evaporatorOrder[$cont])) {
          $attrbituesRemaining = count($evaporatorOrder[$cont]) - count($attributesValuesArray);
          for ($contRemaining = 0 ; $contRemaining < $attrbituesRemaining ; $contRemaining++) {
            $attributesValuesArray[] = '';
          }
        }      

        $componentsAttributesColumnsRows = array_merge($componentsAttributesColumnsRows,$attributesValuesArray);

        // Staged and Online Attributs
        $componentStaged   = $componentsAttributesColumnsRows;
        $componentOnline   = $componentsAttributesColumnsRows;
        $componentStaged[] = ''.PRODUCT_CATALOG.':Staged';    
        $componentOnline[] = ''.PRODUCT_CATALOG.':Online';
        fwrite($fileAttributes, implode(";", $componentStaged) . "\n");
        // fwrite($fileAttributes, implode(";", $componentOnline) . "\n");

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
        $productsComponentsArrayStaged .= trim($valueComponent) . ':'.PRODUCT_CATALOG.':Staged';
        $productsComponentsArrayOnline .= trim($valueComponent) . ':'.PRODUCT_CATALOG.':Online';
      }

      // Staged
      $productComponentsColumnsStagedRows = array(
        $product['cod_pro'],
        $productsComponentsArrayStaged,
        PRODUCT_CATALOG.':Staged',
      );
      fwrite($fileProduct, implode(";", $productComponentsColumnsStagedRows) . "\n");

      // Online
      $productComponentsColumnsOnlineRows = array(
        $product['cod_pro'],
        $productsComponentsArrayOnline,
        PRODUCT_CATALOG.':Online',
      );
      // fwrite($fileProduct, implode(";", $productComponentsColumnsOnlineRows) . "\n");
    }
  }

  fclose($fileComponent);
  fclose($fileAttributes);
  fclose($fileProduct);  
?>