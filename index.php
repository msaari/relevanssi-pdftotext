<?php

require __DIR__ . '/vendor/autoload.php';
require 'PdfController.php';

$PdfProcessor = new PdfController();
$PdfProcessor->process($_POST);

?>
