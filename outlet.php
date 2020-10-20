<?php
  include __DIR__.'/config/start.php';
 
  // Productions fields ans settings
  include_once __DIR__.'/config/products/settings.php';
  include_once __DIR__.'/config/products/functions.php';
  include_once __DIR__.'/config/products/classes.php';

  define('PRODUCT_CATALOG', 'centralArOutletProductCatalog');

  if (isset($array_parameters['sku']) and !empty($array_parameters['sku'])) {
    $productParameters = $array_parameters['sku'];
  }
  else {
    $productParameters = implode(',', $activeProducts);
  }

  $filesNames = array(
    1 => array(
      'impex' => 'ProductsOutlet',
      'csv'   => array('CentralArProductOutlet', 'CentralArProductSupercategoriesOutlet')
    ),
    2 => array(
      'impex' => 'ProductsComponentsOutlet',
      'csv'   => array('ComponentProductOutlet', 'ComponentProductAttributesOutlet', 'CentralArProductComponentsOutlet')
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
                  p.qtd_evaporizadoras,
                  p.id_evaporizadora_2,
                  p.qtd_evaporizadoras_2,
                  p.id_evaporizadora_3,
                  p.qtd_evaporizadoras_3,
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
                  sc.cod_sub,
                  sc.nom_sub                    
          from	  produtos p
                  inner join fabricante f on p.fab_pro = f.codigo 
                  inner join categoria c on c.cod_cat = p.cod_cat
                  left join produto_voltagem pv on pv.cod_volt = p.voltagem 
                  left join url u on u.nivel = "prod" and u.id_nivel = p.cod_pro
                  left join sub_categoria sc on sc.cod_sub = p.cod_sub 
          where	  p.cod_pro in ('.$productParameters.')
                  and not p.cod_cat in (50000)
                  and p.cod_pro in (
                  	select skus from produtos_outlet po where po.skus = p.cod_pro
                  )';
  $db->query($sql);
  $rowProdutos = $db->multiple();
  
  include_once __DIR__.'/src/scrollingProducts.php';
?>