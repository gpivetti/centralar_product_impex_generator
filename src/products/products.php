<?php
  echo "\n1 - Exportando as Informações dos Produtos....";

  $file                 = fopen(CSV_PATH.$filesNames[1]['csv'][0].'.csv', 'wr');
  $fileSupercategories  = fopen(CSV_PATH.$filesNames[1]['csv'][1].'.csv', 'wr');

  // Exportando as colunas Bases
  $productsColumns = array(
    '# code', 
    'slog',
    'name',
    'alternateName',
    'componentProductType',
    'description',
    'descriptionLongVerum',
    'descriptionShortVerum',
    'primaryImage',
    'imagemThumbNail',
    'otherImages',
    'supercategories'
  );
  foreach ($condenserOrder as $fieldKey => $fieldValue) {
    array_push($productsColumns, $fieldValue);
  } 
  array_push($productsColumns, 'approvalstatus'); 
  array_push($productsColumns, 'catalogVersion'); 
  fwrite($file, implode(";", $productsColumns) . "\n");

  // Exportando as colunas Bases dos Produtos com as Categorias
  $productSupercategoriesColumns = array(
    '# code',
    'supercategories',
    'catalogVersion'
  );
  fwrite($fileSupercategories, implode(";", $productSupercategoriesColumns) . "\n");  

  // Exportando os dados dos produtos
  foreach ($products as $key => $product) {
    /**
     * ***********************************************************************
     * [START] PRODUCT
     * ***********************************************************************
     */    
    // Tipo da Condensadora
    if (isset($condenserComponentType[$product['id_condensadora']])) {
      $condType = $condenserComponentType[$product['id_condensadora']];
    }
    else {
      $condType = '';
    }

    // Images
    $primaryImage = '';
    $otherImages  = '';
    foreach ($product['images'] as $key => $value) {
      if ($key == 0) {
        $primaryImage = $value;
      }
      else {
        if (!empty($otherImages)) {
          $otherImages .= ',';
        }
        $otherImages .= trim($value);
      }
    }

    // Dados bases do Produto
    $productsColumnsRows = array(
      $product['cod_pro'],
      trim($product['slug']),
      trim($product['nom_pro']),
      trim($product['nom_pro']),
      $condType,
      trim(preg_replace("/\r|\n/", " ", $product['des_pro'])),
      trim(preg_replace("/\r|\n/", " ", $product['des_pro'])),
      trim($product['nom_pro']),
      $primaryImage,
      $primaryImage,
      $otherImages,
      'ESPECIFICACOES_TECNICAS:centralArClassificationCatalog:1.0'  
    );

    // Atributos e descrições ténicas
    $attributesValuesArray = array();
    if (count($product['descriptionFields']) > 0) {      
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

        $attributesValuesArray[] = $columnValue;
      }              
    }
    
    // Atributos que faltarão ficaram vazios
    if (count($attributesValuesArray) < count($condenserOrder)) {
      $attrbituesRemaining = count($condenserOrder) - count($attributesValuesArray);
      for ($cont = 0 ; $cont < $attrbituesRemaining ; $cont++) {
        $attributesValuesArray[] = '';
      }
    }

    $productsColumnsRows = array_merge($productsColumnsRows,$attributesValuesArray);

    // Staged and Online Product
    $productStaged = $productsColumnsRows;
    $productOnline = $productsColumnsRows;
    $productStaged[] = 'approved';
    $productStaged[] = 'centralArProductCatalog:Staged';    
    $productOnline[] = 'approved';
    $productOnline[] = 'centralArProductCatalog:Online';
    fwrite($file, implode(";", $productStaged) . "\n");
    // fwrite($file, implode(";", $productOnline) . "\n");
    /**
     * ***********************************************************************
     * [END] PRODUCT
     * ***********************************************************************
     */    


    /**
     * ***********************************************************************
     * [START] SUPER CATEGORIES
     * ***********************************************************************
     */
    if (isset($arrayClassificationsByBtus[$product['cod_cat']])) {
      $superCategories = array();

      // Último nível da categoria
      if ($product['cod_cat'] == 50000) { // Serviços

      }
      else if ($product['cod_cat'] == 66) { // Multi Split
        if (isset($arrayClassificationsByBtus[$product['cod_cat']][$product['cod_sub']])) {
          $superCategories[] = trim($arrayClassificationsByBtus[$product['cod_cat']][$product['cod_sub']]);
        }
      }
      else {
        foreach ($arrayClassificationsByBtus[$product['cod_cat']] as $key => $value) {
          if ($product['btus'] < $key) {
            $superCategories[] = trim($value);
            break;
          }
        }
      }

      // Supercategorias por fabricante/marca
      if (
        isset($arrayBrandsByCategories[$product['cod_cat']][$product['fabCodigo']]) and
        !empty($arrayBrandsByCategories[$product['cod_cat']][$product['fabCodigo']])
      ) {
        $superCategories[] = trim($arrayBrandsByCategories[$product['cod_cat']][$product['fabCodigo']]);
      }

      // Supercategorias por categorias
      if (isset($arrayBrandsClassifications[$product['fabCodigo']]) and !empty($arrayBrandsClassifications[$product['fabCodigo']])) {
        $superCategories[] = trim($arrayBrandsClassifications[$product['fabCodigo']]);
      }      

      if (count($superCategories) >= 2) {
        $superCategoriesStaged = '';
        $superCategoriesOnline = '';

        foreach ($superCategories as $key => $value) {
          if (!empty($superCategoriesStaged)) {
            $superCategoriesStaged .= ',';
          }
          $superCategoriesStaged .= trim($value) . ':centralArProductCatalog:Staged';

          if (!empty($superCategoriesOnline)) {
            $superCategoriesOnline .= ',';
          }
          $superCategoriesOnline .= trim($value) . ':centralArProductCatalog:Online';          
        }

        // Staged
        $supercategoriesColumnsStagedRows = array(
          $product['cod_pro'],
          $superCategoriesStaged,
          'centralArProductCatalog:Staged'
        );
        fwrite($fileSupercategories, implode(";", $supercategoriesColumnsStagedRows) . "\n");

        // Online
        $supercategoriesColumnsOnlineRows = array(
          $product['cod_pro'],
          $superCategoriesOnline,
          'centralArProductCatalog:Online'
        );
        // fwrite($fileSupercategories, implode(";", $supercategoriesColumnsOnlineRows) . "\n");
      }
    }
    /**
     * ***********************************************************************
     * [END] SUPER CATEGORIES
     * ***********************************************************************
     */     
  }

  fclose($file);
  fclose($fileSupercategories);
?>