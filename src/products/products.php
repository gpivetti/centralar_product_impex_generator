<?php
  echo "\n1 - Exportando as Informações dos Produtos....";

  $file = fopen(CSV_PATH.$filesNames[1].'.csv', 'wr');

  // Exportando as colunas Bases
  $informationsColumns = array(
    '# code', 
    'slog',
    'name',
    'alternateName',
    'componentProductType',
    'descriptionLongVerum',
    'descriptionShortVerum',
    'primaryImage',
    'imagemThumbNail',
    'otherImages',
    'supercategories',
    'approvalstatus',
    'catalogversion'
  );
  fwrite($file, implode(";", $informationsColumns) . "\n");

  // Exportando os dados dos produtos
  foreach ($products as $key => $product) {
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

    $informationProduct = array(
      $product['cod_pro'],
      trim($product['slug']),
      trim($product['nom_pro']),
      trim($product['nom_pro']),
      $condType,
      trim(preg_replace("/\r|\n/", " ", $product['des_pro'])),
      trim(preg_replace("/\r|\n/", " ", $product['description'])),
      $primaryImage,
      $primaryImage,
      $otherImages,
      'ESPECIFICACOES_TECNICAS:centralArClassificationCatalog:1.0',
      'approved'      
    );

    // Staged and Online Product
    $productStaged = $informationProduct;
    $productOnline = $informationProduct;
    $productStaged[] = 'centralArProductCatalog:Staged';
    $productOnline[] = 'centralArProductCatalog:Online';
    fwrite($file, implode(";", $productStaged) . "\n");
    fwrite($file, implode(";", $productOnline) . "\n");
  }

  fclose($file);
?>