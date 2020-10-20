<?php

  $products = array();
  foreach($rowProdutos as $rowKey => $rowProduto) { 
    $rowProduto = (array) $rowProduto;

    // Atributos do Produto
    $rowProduto['descriptionFields'] = array();
    $sqlCaracteristicas = ' select	  pd.codigo, pd.descricao_dimensao, pd.nome_campo, pv.valor, pd.unidade_medida  
                            from 	    produto_valor pv
                                      join produtos_descricao pd on pd.codigo = pv.produto_descricao 
                            where	    pv.produto = '.$rowProduto['cod_pro'].'
                            order by  pd.codigo';
    $db->query($sqlCaracteristicas);
    $caracteristicas = $db->multiple();    
    foreach($caracteristicas as $item => $obj) {
      if ($obj->descricao_dimensao == 'S') {
        $valor = round($obj->valor/10, 2);
      }
      else {
        $valor = $obj->valor;
      }
      $rowProduto['descriptionFields'][$obj->codigo] = array(
        'unity' => $obj->unidade_medida,
        'value' => $valor
      );
    }   

    // Descrições (Resumo) do Produto
    $rowProduto['resumeFields'] = array();
    $sqlResumo = 'select titulo, valor from mds_produto_descricao p where p.cod_pro = '.$rowProduto['cod_pro'].' order by p.ordem';
    $db->query($sqlResumo);
    $resumo = $db->multiple();    
    foreach($resumo as $item => $obj) {  
      if (!empty($obj->titulo) and !empty($obj->valor)) {    
        $rowProduto['resumeFields'][] = array(
          'title' => $obj->titulo,
          'value' => $obj->valor
        );
      }
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
    
    // Quantidade de componentes (para quem possui)
    $rowProduto['numberOfComponents'] = 0;
    if (in_array($rowProduto['cod_cat'], array(63, 64, 65, 66, 99))) {
      if (!empty($rowProduto['id_evaporizadora']) and !empty($rowProduto['qtd_evaporizadoras'])) {
        $rowProduto['numberOfComponents'] += $rowProduto['qtd_evaporizadoras'];
      }
      if (!empty($rowProduto['id_evaporizadora_2']) and !empty($rowProduto['qtd_evaporizadoras_2'])) {
        $rowProduto['numberOfComponents'] += $rowProduto['qtd_evaporizadoras_2'];
      }
      if (!empty($rowProduto['id_evaporizadora_3']) and !empty($rowProduto['qtd_evaporizadoras_3'])) {
        $rowProduto['numberOfComponents'] += $rowProduto['qtd_evaporizadoras_3'];
      }
      if ($rowProduto['numberOfComponents'] > 5) {
        $rowProduto['numberOfComponents'] = 5;
      }
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