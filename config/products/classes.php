<?php

interface IDescriptionFields {
  public static function getValue($value = "");
}

interface IProductFields {
  public static function getValue($productObject = false);
}

/** By Product Fields */
class Fabricante implements IProductFields {
  public static function getValue($productObject = false) {
    return trim($productObject['fabNome']);
  }
}

class EanCond implements IProductFields {
  public static function getValue($productObject = false) {
    return trim($productObject['ean_condensadora']);
  }
}

class EanEvap implements IProductFields {
  public static function getValue($productObject = false) {
    return trim($productObject['ean_evaporadora']);
  }
}

class BTUS implements IProductFields {
  public static function getValue($productObject = false) {
    return trim($productObject['btus']);
  }
}

class CompressorTecnology implements IProductFields {
  public static function getValue($productObject = false) {
    if (empty($productObject['inverter'])) {
      return '';
    }
    else {
      if (trim(strtoupper($productObject['inverter'])) == 'S') {
        return 'inverter';
      }
      else {
        return 'onOff';
      }
    }
  }
}

/** By Description Fields */
class AlimentacaoEnergia implements IDescriptionFields {
  public static function getValue($value = "") {
    if (trim(strtolower($value)) == 'condensadora') {
      return 'condenser';
    }
    else if (trim(strtolower($value)) == 'evaporadora') {
      return 'evaporator';
    } 
    else {
      return '';
    }
  }
}

class Ciclo implements IDescriptionFields {
  public static function getValue($value = "") {
    if (trim(strtolower($value)) == 'frio') {
      return 'cold';
    }
    else {
      if (strpos(trim(strtolower($value)), 'quente') !== false) {
        return 'hotCold';
      } 
      else {
        return '';
      }
    }
  }
}

class ClassificacaoInmetro implements IDescriptionFields {
  public static function getValue($value = "") {
    if (trim(strtoupper($value)) == 'A') {
      return 'a_inmetro';
    }
    else if (trim(strtoupper($value)) == 'B') {
      return 'b_inmetro';
    }
    else if (trim(strtoupper($value)) == 'C') {
      return 'c_inmetro';
    }
    else if (trim(strtoupper($value)) == 'D') {
      return 'd_inmetro';
    }
    else if (trim(strtoupper($value)) == 'E') {
      return 'e_inmetro';
    }
    else if (trim(strtoupper($value)) == 'F') {
      return 'f_inmetro';
    }
    else {
      return '';
    }
  }
}

class ControleCimaBaixo implements IDescriptionFields {
  public static function getValue($value = "") {
    if (trim(strtolower($value)) == 'automatico' or trim(strtolower($value)) == 'automático') {
      return 'automatic';
    }
    else if (trim(strtolower($value)) == 'manual') {
      return 'manual';
    } 
    else {
      return '';
    }
  }
}

class ControleDireitaEsqueda implements IDescriptionFields {
  public static function getValue($value = "") {
    if (trim(strtolower($value)) == 'automatico' or trim(strtolower($value)) == 'automático') {
      return 'automatic';
    }
    else if (trim(strtolower($value)) == 'manual') {
      return 'manual';
    } 
    else {
      return '';
    }    
  }
}

class Fase implements IDescriptionFields {
  public static function getValue($value = "") {
    if (strpos(trim(strtolower($value)), 'mono') !== false) {
      return 'singlePhase';
    }
    else if (strpos(trim(strtolower($value)), 'bi') !== false) {
      return 'biphasic';
    }
    else if (strpos(trim(strtolower($value)), 'tri') !== false) {
      return 'threePhase';
    }
    else {
      return '';
    }
  }
}

class Gas implements IDescriptionFields {
  public static function getValue($value = "") {
    if (strpos(trim(strtolower($value)), '22') !== false) {
      return 'R22';
    }
    else if (strpos(trim(strtolower($value)), '32') !== false) {
      return 'R22';
    }
    else if (strpos(trim(strtolower($value)), '410') !== false) {
      return 'R410-A';
    }    
    else {
      return '';
    }
  }
}

class MaterialSerpentina implements IDescriptionFields {
  public static function getValue($value = "") {
    if (strpos(trim(strtolower($value)), 'alum') !== false) {
      return 'aluminium';
    }
    else if (strpos(trim(strtolower($value)), 'cobre') !== false) {
      return 'coper';
    }
    else if (strpos(trim(strtolower($value)), 'duraf') !== false) {
      return 'durafim';
    }
    else if (strpos(trim(strtolower($value)), 'micro') !== false) {
      return 'microcanal';
    }    
    else {
      return '';
    }    
  }
}

class TipoCondensador implements IDescriptionFields {
  public static function getValue($value = "") {
    if (strpos(trim(strtolower($value)), 'ver') !== false) {
      return 'vertical';
    }
    else if (strpos(trim(strtolower($value)), 'hori') !== false) {
      return 'horizontal';
    }
    else {
      return '';
    }
  }
}

class Voltagem implements IDescriptionFields {
  public static function getValue($value = "") {
    if (strpos(trim(strtolower($value)), '110') !== false) {
      return '110V';
    }
    else if (strpos(trim(strtolower($value)), '120') !== false) {
      return '110V';
    }
    else if (strpos(trim(strtolower($value)), '127') !== false) {
      return '110V';
    }
    else if (strpos(trim(strtolower($value)), '220') !== false) {
      return '220V';
    }
    else if (strpos(trim(strtolower($value)), '380') !== false) {
      return '380V';
    }        
    else {
      return '';
    }    
  }
}

?>