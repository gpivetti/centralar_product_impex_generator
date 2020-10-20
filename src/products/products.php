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

    // Resumo do Produtos (Description)
    $productDescription = '';
    if (count($product['resumeFields']) > 0) {
      $productDescription .= '"<div>';
      foreach ($product['resumeFields'] as $resumeKey => $resumeValue) {
        $productDescription .= '<div>';
        $productDescription .= '<h6 style=""font-size: 14px; font-weight: bold; margin-bottom: 7px;"">' . trim(addslashes($resumeValue['title'])) . '</h6>';
        $productDescription .= '<p style=""margin-bottom: 20px;"">' . trim(addslashes(preg_replace("/\r|\n/", " ", $resumeValue['value']))) . '</p>';
        $productDescription .= '</div>';
      }
      $productDescription .= '</div>"';
    }

    // Dados bases do Produto
    $productsColumnsRows = array(
      $product['cod_pro'],
      trim($product['slug']),
      trim($product['nom_pro']),
      trim($product['nom_pro']),
      $condType,
      trim($productDescription),
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
        $keyOfField = getValueByRepeatedColumn($fieldKey);
        if(isset($product['descriptionFields'][$keyOfField]) and !empty($product['descriptionFields'][$keyOfField]['value'])) {
          $measurementUnity = $product['descriptionFields'][$keyOfField]['unity'];
          $descriptionValue = $product['descriptionFields'][$keyOfField]['value'];
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
    $productStaged[] = PRODUCT_CATALOG.':Staged';    
    $productOnline[] = 'approved';
    $productOnline[] = PRODUCT_CATALOG.':Online';
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
          $superCategoriesStaged .= trim($value) . ':'.PRODUCT_CATALOG.':Staged';

          if (!empty($superCategoriesOnline)) {
            $superCategoriesOnline .= ',';
          }
          $superCategoriesOnline .= trim($value) . ':'.PRODUCT_CATALOG.':Online';          
        }

        // Staged
        $supercategoriesColumnsStagedRows = array(
          $product['cod_pro'],
          $superCategoriesStaged,
          PRODUCT_CATALOG.':Staged'
        );
        fwrite($fileSupercategories, implode(";", $supercategoriesColumnsStagedRows) . "\n");

        // Online
        $supercategoriesColumnsOnlineRows = array(
          $product['cod_pro'],
          $superCategoriesOnline,
          PRODUCT_CATALOG.':Online'
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