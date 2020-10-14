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

  function zipFiles($fileNames = false, $fileObjects = false, $subDirectory = '') {
    if (
      $fileNames and is_array($fileNames) and count($fileNames) > 0 and
      $fileObjects and is_array($fileObjects) and count($fileObjects) > 0 and
      count($fileNames) == count($fileObjects)
    ) {
      foreach($fileNames as $key => $value) {
        $impexFile = IMPEX_PATH.$value.'.impex';
        $csvFile = CSV_PATH.$value.'.csv';
        if (!empty($value) and file_exists($impexFile) and file_exists($csvFile)) {
          echo "\n".$key.' - '.$value.'.zip';
          $zip = new ZipArchive();
          if ($zip->open(ZIP_PATH . $subDirectory . $value . '.zip', ZIPARCHIVE::CREATE) == TRUE) {
            $zip->addFile($impexFile, 'importscript.impex');
            $zip->addFile($csvFile, $fileObjects[$key].'.csv');
          }
          $zip->close();
          unset($zip);
        }
      }
    }
  }
?>