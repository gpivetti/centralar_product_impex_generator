<?php
  echo "\n2 - Exportando as Informações das Supercategorias dos Produtos....";

  $file = fopen(CSV_PATH.$filesNames[2].'.csv', 'wr');

  // Exportando as colunas Bases
  $superCategoriesColumns = array(
    '# code',
    'supercategories',
    'catalogversion'
  );
  fwrite($file, implode(";", $superCategoriesColumns) . "\n");

  // Exportando os dados dos produtos
  foreach ($products as $key => $product) {
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

      // Supercategorias por categorias
      if (
        isset($arrayClassificationsByCategories[$product['cod_cat']][$product['fabCodigo']]) and
        !empty($arrayClassificationsByCategories[$product['cod_cat']][$product['fabCodigo']])
      ) {
        $superCategories[] = trim($arrayClassificationsByCategories[$product['cod_cat']][$product['fabCodigo']]);
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
        $superCategoriesInformationsStaged = array(
          $product['cod_pro'],
          $superCategoriesStaged,
          'centralArProductCatalog:Staged'
        );
        fwrite($file, implode(";", $superCategoriesInformationsStaged) . "\n");

        // Online
        $superCategoriesInformationsOnline = array(
          $product['cod_pro'],
          $superCategoriesOnline,
          'centralArProductCatalog:Online'
        );
        fwrite($file, implode(";", $superCategoriesInformationsOnline) . "\n");
      }
    }
  }

  fclose($file);
?>