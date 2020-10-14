<?php
  function url_exists($url) {
    $url = str_replace('https://','http://',trim($url));
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code >= 200 && $code < 300);
  }

  function zipFiles($fileNames = false, $subDirectory = '') {
    if ($fileNames and is_array($fileNames) and count($fileNames) > 0) {
      foreach($fileNames as $key => $value) {
        echo "\n".$key.' - '.$value['impex'].'.zip';
        $impexFile = IMPEX_PATH.$value['impex'].'.impex';
        foreach($value['csv'] as $keyCSV => $valueCSV) {
          $csvFile = CSV_PATH.$valueCSV.'.csv';
          if (!empty($valueCSV) and file_exists($impexFile) and file_exists($csvFile)) {
            echo "\n".'   '.$key.'.'.$keyCSV.' - '.$valueCSV.'.csv';
            $zip = new ZipArchive();
            if ($zip->open(ZIP_PATH . $subDirectory . $value['impex'] . '.zip', ZIPARCHIVE::CREATE) == TRUE) {
              $zip->addFile($impexFile, 'importscript.impex');
              $zip->addFile($csvFile, $valueCSV.'.csv');
            }
            $zip->close();
            unset($zip);
          }
        }
      }
    }
  }
?>