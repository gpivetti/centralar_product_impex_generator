<?php
  include __DIR__.'/config/start.php';
 
  // Productions fields ans settings
  include_once __DIR__.'/config/products/settings.php';
  include_once __DIR__.'/config/products/functions.php';
  include_once __DIR__.'/config/products/classes.php';

  if (isset($array_parameters['sku']) and !empty($array_parameters['sku']) and is_numeric($array_parameters['sku'])) {
    $productParameters = $array_parameters['sku'];
  }
  else {
    $productParameters = implode(',', $activeProducts);
  }

  $filesNames = array(
    1 => array(
      'impex' => 'Products',
      'csv'   => array('CentralArProduct', 'CentralArProductSupercategories')
    ),
    2 => array(
      'impex' => 'ProductsComponents',
      'csv'   => array('ComponentProduct', 'ComponentProductAttributes', 'CentralArProductComponents')
    )
  );

  $db = new Database();

  echo "=> Pesquisando os Produtos";

  // Pesquisa os Produtos
  $products = array(); 
  $sql = 'select 	p.cod_pro,
                  p.nom_pro,
                  p.des_pro,
                  p.description,
                  p.id_condensadora,
                  p.id_evaporizadora,
                  p.id_evaporizadora_2,
                  p.id_evaporizadora_3,
                  p.inverter,
                  p.btus,
                  p.inverter,
                  p.keyTOTVS,                  
                  p.cod_interno,
                  p.b1_codbar as ean_evaporadora,
                  p.ean_condensadora,
                  p.produto_origem,
                  pv.nom_volt,
                  p.cod_cat,
                  c.nom_cat,
                  c.titulo,
                  f.codigo as fabCodigo,
                  f.nome as fabNome,                  
                  u.url as slug,
                  case
                  	when not (select skus from produtos_outlet po where po.skus = p.cod_pro) is null then "V"
                  	else "F"
                  end produtoOutlet,
                  sc.cod_sub,
                  sc.nom_sub                    
          from	  produtos p
                  inner join fabricante f on p.fab_pro = f.codigo 
                  inner join categoria c on c.cod_cat = p.cod_cat
                  left join produto_voltagem pv on pv.cod_volt = p.voltagem 
                  left join url u on u.nivel = "prod" and u.id_nivel = p.cod_pro
                  left join sub_categoria sc on sc.cod_sub = p.cod_sub 
          where	  p.cod_pro in ('.$productParameters.')
                  and not p.cod_cat in (66, 50000)
                  and not exists (
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
    
    // Images			
    $rowProduto['images'] = array();
    for ($im = 1;$im <= 5;$im++) {      
      $fabricante = ($rowProduto['fabNome']) ? strtolower($rowProduto['fabNome']) : 'servicos';
      $titulo 	= ($rowProduto['cod_cat'] === 50000) ? '/' : (($rowProduto['cod_cat']) != 98 ? $rowProduto['titulo'].'/' : 'portatil/');
      $imageUrl = str_replace(" ", "-", 'https://cdn2.centralar.com.br/centralar/mds/produtos/'.strtolower($fabricante).'/'.strtolower($titulo).$rowProduto['cod_pro'].'/'.str_replace('.html', '', $rowProduto['slug']).'_Fot'.$rowProduto['cod_pro']. '_'.$im.'_Full.jpg');
      if(url_exists($imageUrl)) {
        $rowProduto['images'][] = $imageUrl;
      }
    }
    
    // Quantidade de componentes
    $rowProduto['numberOfComponents'] = 0;
    if (!empty($rowProduto['id_evaporizadora'])) {
      $rowProduto['numberOfComponents'] += 1;
    }
    if (!empty($rowProduto['id_evaporizadora_2'])) {
      $rowProduto['numberOfComponents'] += 1;
    }
    if (!empty($rowProduto['id_evaporizadora_3'])) {
      $rowProduto['numberOfComponents'] += 1;
    }

    // Slug do Produto
    $rowProduto['slug'] = trim(strtolower(str_ireplace(array('.html', '.htm'), '', $rowProduto['slug'])));

    $products[] = $rowProduto;
  }

  if (count($products) > 0) {
    echo "\n\n=> Iniciando a Criação dos CSVs";
    
    // Cria arquivo de products (1)
    include PRODUCT_PATH.'products.php';

    // Cria arquivo de componentes dos produtos (2)
    include COMPONENT_PATH.'components.php';

    // Zipando os arquivos
    echo "\n\n=> Exportando os Conteúdos";
    zipFiles($filesNames);

    echo "\n\n=> Finalizado!\n";
  }
?>