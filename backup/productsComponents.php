<?php
  include __DIR__.'/config/start.php';
  
  if (!isset($array_parameters['sku']) or empty($array_parameters['sku']) or 
      (!is_numeric($array_parameters['sku']) and strpos($array_parameters['sku'], ',') === false)
  ) {
    echo 'Informe um produto válido';
    exit;
  }

  // Arrays de exportação
  $columns  = array('# code');
  $products = array();

  $db = new Database();

  // Pesquisa os Produtos  para
  $sql = 'select 	p.cod_pro,
                  p.nom_pro,
                  p.des_pro,
                  p.btus,
                  p.inverter,
                  p.keyTOTVS,
                  p.cod_interno,
                  p.b1_codbar as ean_evaporadora,
                  p.ean_condensadora,
                  p.produto_origem,
                  pv.nom_volt,
                  p.cod_cat,
                  f.nome as fabNome,
                  case
                  	when not (select skus from produtos_outlet po where po.skus = p.cod_pro) is null then "V"
                  	else "F"
                  end produtoOutlet                   
          from	  produtos p
                  inner join fabricante f on p.fab_pro = f.codigo 
                  left join produto_voltagem pv on pv.cod_volt = p.voltagem 
          where	  p.cod_pro in ('.$array_parameters['sku'].') and
                  p.ati_pro = "S" and
                  not exists (
                    select skus from produtos_outlet po where po.skus = p.cod_pro 
                  )';
  $db->query($sql);
  $rowProdutos = $db->multiple();
  foreach($rowProdutos as $rowKey => $rowProduto) {    
    $rowProduto = (array) $rowProduto;

    // Atributos do Produto
    $rowProduto['descriptionFields'] = array();
    $sqlCaracteristicas = ' select	  pd.codigo, pd.nome_campo, pv.valor, pd.unidade_medida  
                            from 	    produto_valor pv
                                      join produtos_descricao pd on pd.codigo = pv.produto_descricao 
                            where	    pv.produto = '.$rowProduto['cod_pro'].'
                            order by  pd.codigo';
    $db->query($sqlCaracteristicas);
    $caracteristicas = $db->multiple();    
    foreach($caracteristicas as $item => $obj) {      
      $rowProduto['descriptionFields'][$obj->codigo] = array(
        'unity' => $obj->unidade_medida,
        'value' => $obj->valor
      );
    }
    
    if (count($rowProduto['descriptionFields']) > 0) {
      $product = array();
      $product[] = $rowProduto['cod_pro'];

      foreach ($condenserOrder as $fieldKey => $fieldValue) {
        // Descrição do produto para a chave
        if(isset($rowProduto['descriptionFields'][$fieldKey]) and !empty($rowProduto['descriptionFields'][$fieldKey]['value'])) {
          $measurementUnity = $rowProduto['descriptionFields'][$fieldKey]['unity'];
          $descriptionValue = $rowProduto['descriptionFields'][$fieldKey]['value'];
        }
        else {
          $measurementUnity = '';
          $descriptionValue = '';
        }

        // Retorna o valor correto do campo
        $columnValue = getAttributeValueByDefault($fieldValue);
        if (empty($columnValue)) {
          $columnValue = getAttributeValueByDescriptionIndetificator($fieldKey, $descriptionValue);
        }
        if (empty($columnValue)) {
          $columnValue = getAttributeValueByProductAttributes($fieldKey, $rowProduto);
        }
        if (empty($columnValue)) {
          $columnValue = getAttributeValueByValue($descriptionValue, $measurementUnity);
        }

        $product[] = $columnValue;
      }

      $products[] = $product;
    }
  }

  // Iniciando a exportação do arquivo
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=CentralArProduct.csv');
  $saida = fopen('php://output', 'w');

  // Exportando as colunas Bases
  foreach ($condenserOrder as $fieldKey => $fieldValue) {
    array_push($columns, $fieldValue);
  }
  array_push($columns, 'catalogVersion');
  fputcsv($saida, $columns);

  // Exportando os dados dos produtos
  foreach ($products as $key => $product) {
    $productStaged = $product;
    $productOnline = $product;
    $productStaged[] = 'centralArProductCatalog:Staged';
    $productOnline[] = 'centralArProductCatalog:Online';
    fputcsv($saida, $productStaged);
    fputcsv($saida, $productOnline);
  }
?>