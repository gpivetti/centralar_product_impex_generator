<?php
  echo "\n3 - Exportando as Informações dos Componentes....";

  $file = fopen(CSV_PATH.$filesNames[3].'.csv', 'wr');

  // Exportando as colunas Bases
  $columnsComponents = array(
    '# code', 
    'name', 
    'componentType',
    'supercategories',
    'approvalstatus',
    'catalogversion'
  );
  fwrite($file, implode(";", $columnsComponents)  . "\n");

  // Exportando os dados dos produtos
  foreach ($products as $key => $product) {
    for ($cont = 0 ; $cont < $product['numberOfComponents'] ; $cont++) {
      $componentName = 'Evaporadora'; 
      $componentCode = 'Evaporadora_' . $product['cod_pro'];
      if ($product['numberOfComponents'] > 1) {
        $componentName .= '_' . ($cont+1);
        $componentCode .= '_' . ($cont+1);
      }

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

      $informationsComponents = array(
        $componentCode,      
        $componentName,
        $evapType,
        'ESPECIFICACOES_TECNICAS:centralArClassificationCatalog:1.0',
        'approved'      
      );

      // Staged and Online Product
      $componentStaged = $informationsComponents;
      $componentOnline = $informationsComponents;
      $componentStaged[] = 'centralArProductCatalog:Staged';
      $componentOnline[] = 'centralArProductCatalog:Online';
      fwrite($file, implode(";", $componentStaged) . "\n");
      fwrite($file, implode(";", $componentOnline) . "\n");
    }
  }

  fclose($file);
?>