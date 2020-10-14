<?php
  include __DIR__.'/config/start.php';
  
  if (!isset($array_parameters['sku']) or empty($array_parameters['sku']) or 
      (!is_numeric($array_parameters['sku']) and strpos($array_parameters['sku'], ',') === false)
  ) {
    echo 'Informe um produto válido';
    exit;
  }
  
  $db = new Database();

  // Pesquisa os Produtos
  $produtos = array();
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
    $rowProdutoValor = array();
    $sqlCaracteristicas = ' select	  pd.codigo, pd.nome_campo, pv.valor 
                            from 	    produto_valor pv
                                      join produtos_descricao pd on pd.codigo = pv.produto_descricao 
                            where	    pv.produto = '.$rowProduto['cod_pro'].'
                            order by  pd.codigo';
    $db->query($sqlCaracteristicas);
    $caracteristicas = $db->multiple();    
    foreach($caracteristicas as $item => $obj) {      
      $rowProdutoValor[$obj->codigo] = $obj->valor;
    }
    
    if (count($rowProdutoValor) <= 0) {
      echo 'Não há características válidas para retornar para o Produto '.$rowProduto['cod_pro'].'<br>';
      exit;
    } else {
      $produtos[] = array(
        'sku'           => $rowProduto['cod_pro'],
        'nome'          => $rowProduto['nom_pro'],
        'descricao'     => $rowProduto['des_pro'],
        'erpCode'       => $rowProduto['keyTOTVS'],
        'produtoOutlet' => $rowProduto['produtoOutlet'],
        'custo'         => '',
        'condensadora'  => array(
          'codigoInternoErpCondensadora'             => trim($rowProduto['cod_interno']),
          'eanCondensadora'                          => trim($rowProduto['ean_condensadora']),
          'fabricanteCondensadora'                   => trim($rowProduto['fabNome']),
          'ncmCondensadora'                          => '',
          'importadoCondensadora'                    => ($rowProduto['produto_origem'] == 'I' ? 'V' : 'F'),
          'faseCondensadora'                         => trim($rowProdutoValor[57]),
          'frequenciaCondensadora'                   => trim($rowProdutoValor[80]),
          'classificacaoInmetroCondensadora'         => trim($rowProdutoValor[16]),
          'voltagemCondensadora'                     => (empty($rowProduto['nom_volt']) ? trim($rowProdutoValor[3]) : trim($rowProduto['nom_volt'])),
          'eficienciaEnergeticaCondensadora'         => trim($rowProdutoValor[5]),
          'consumoEnergiaCondensadora'               => trim($rowProdutoValor[22]),
          'capacidadeBtusCondensadora'               => trim($rowProduto['btus']),
          'capacidadeMinNomMaxCondensadora'          => trim($rowProdutoValor[300]),
          'cicloCondensadora'                        => trim($rowProdutoValor[13]),
          'tecnologiaCompressorCondensadora'         => ($rowProduto['inverter'] == 'S' ? 'Inverter' : 'On/Off'),
          'correnteEletricaRefrigeracaoCondensadora' => trim($rowProdutoValor[4]),
          'correnteEletricaAquecimentoCondensadora'  => trim($rowProdutoValor[55]),
          'potenciaRefrigeracaoCondensadora'         => trim($rowProdutoValor[7]),
          'potenciaAquecimentoCondensadora'          => trim($rowProdutoValor[56]),
          'gasRefrigeranteCondensadora'              => trim($rowProdutoValor[15]),
          'materialSerpentinaCondensadora'           => trim($rowProdutoValor[81]),
          'nivelRuidoExternoCondensadora'            => trim($rowProdutoValor[28]),
          'diametroLinhaSuccaoCondensadora'          => trim($rowProdutoValor[30]),
          'diametroLinhaLiquidoCondensadora'         => trim($rowProdutoValor[31]),
          'garantiaCondensadora'                     => trim($rowProdutoValor[26]),
          'tipoCondensadora'                         => trim($rowProdutoValor[17]),
          'materialCondensadora'                     => '',
          'alimentacaoEnergiaCondensadora'           => trim($rowProdutoValor[91]),
          'larguraCondensadora'                      => trim($rowProdutoValor[50]),
          'alturaCondensadora'                       => trim($rowProdutoValor[51]),
          'profundidadeCondensadora'                 => trim($rowProdutoValor[52]),
          'pesoLiquidoCondensadora'                  => trim($rowProdutoValor[12]),
          'pesoBrutoCondensadora'                    => trim($rowProdutoValor[12])
        ),    
        'evaporadora' => array(
          'eanEvaporadora'                           => trim($rowProduto['ean_evaporadora']),
          'fabricanteEvaporadora'                    => trim($rowProduto['fabNome']),
          'eficienciaEnergeticaEvaporadora'          => trim($rowProdutoValor[5]),
          'capacidadeBtusEvaporadora'                => trim($rowProduto['btus']),
          'capacidadeMinNomMaxEvaporadora'           => trim($rowProdutoValor[300]),
          'potenciaRefrigeracaoEvaporadora'          => trim($rowProdutoValor[7]),
          'potenciaAquecimentoEvaporadora'           => trim($rowProdutoValor[56]),
          'vazaoArEvaporadora'                       => trim($rowProdutoValor[14]),
          'direcionadorFluxoArHorizontalEvaporadora' => trim($rowProdutoValor[53]),
          'direcionadorFluxoArVerticalEvaporadora'   => trim($rowProdutoValor[24]),
          'nivelRuidoInternoEvaporadora'             => trim($rowProdutoValor[27]),
          'diametroLinhaSuccaoEvaporadora'           => trim($rowProdutoValor[30]),
          'diametroLinhaLiquidoEvaporadora'          => trim($rowProdutoValor[31]),
          'alimentacaoEnergiaEvaporadora'            => trim($rowProdutoValor[91]),
          'corEvaporadoraEvaporadora'                => trim($rowProdutoValor[29]),
          'larguraEvaporadora'                       => trim($rowProdutoValor[47]),
          'alturaEvaporadora'                        => trim($rowProdutoValor[48]),
          'profundidadeEvaporadora'                  => trim($rowProdutoValor[49]),
          'pesoLiquidoEvaporadora'                   => trim($rowProdutoValor[11]),
          'pesoBrutoEvaporadora'                     => trim($rowProdutoValor[11]),
          'funcaoTimerEvaporadora'                   => trim($rowProdutoValor[34]),
          'regulaVelocidadeVentilacaoEvaporadora'    => trim($rowProdutoValor[35]),
          'funcaoSleepEvaporadora'                   => trim($rowProdutoValor[36]),
          'funcaoSwingEvaporadora'                   => trim($rowProdutoValor[37]),
          'funcaoTurboEvaporadora'                   => trim($rowProdutoValor[38]),
          'memoriaEvaporadoraEvaporadora'            => trim($rowProdutoValor[39]),
          'avisaLimparFiltroEvaporadora'             => trim($rowProdutoValor[41]),
          'filtroAntiBacteriaEvaporadora'            => trim($rowProdutoValor[42]),
          'desumidificacaoEvaporadora'               => trim($rowProdutoValor[43]),
          'protecaoAntiCorrosaoEvaporadora'          => trim($rowProdutoValor[44]),
          'funcaoBrisaEvaporadora'                   => trim($rowProdutoValor[46]),
          'indicadorTemperaturaEvaporadora'          => trim($rowProdutoValor[54]),
          'controleTemperaturaEstavelEvaporadora'    => trim($rowProdutoValor[25]),
          'controleRemotoEvaporadora'                => trim($rowProdutoValor[76]),
          'controleRemotoIluminadoEvaporadora'       => trim($rowProdutoValor[23])
        )
      );
    }
  }

  // Iniciando a exportação do arquivo
  if (count($produtos) > 0) {
    if (isset($array_parameters['sku']) and !empty($array_parameters['sku']) and strpos($array_parameters['sku'], ',') === false) {
      $fileSufix = $array_parameters['sku'];
    } else {
      $fileSufix = date('YmdHis');
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=produtos_'.$fileSufix.'.csv');

    $saida = fopen('php://output', 'w');

    foreach ($produtos as $produtoKey => $produtoValue) {
      // Cabeçalho (Colunas)
      if ($produtoKey == 0) {
        $colunaBase = array('sku', 'nome', 'descricao', 'erpCode', 'custo');
        foreach($produtoValue['condensadora'] as $column => $value) {
          array_push($colunaBase, $column);
        }
        foreach($produtoValue['evaporadora'] as $column => $value) {
          array_push($colunaBase, $column);
        }
        fputcsv($saida, $colunaBase);
      }

      // Dados
      $colunaDados = array($produtoValue['sku'], $produtoValue['nome'], $produtoValue['descricao'], $produtoValue['erpCode'], $produtoValue['custo']);
      foreach($produtoValue['condensadora'] as $column => $value) {
        array_push($colunaDados, $value);
      }
      foreach($produtoValue['evaporadora'] as $column => $value) {
        array_push($colunaDados, $value);
      }
      fputcsv($saida, $colunaDados);   
    }
  } else {
    echo 'Sem produtos válidos para exportação<br>';
    exit;
  }
?>