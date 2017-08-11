<?php
class PdfController {
    private $tmp_path;

    public function __construct() {
        $this->tmp_path = "/tmp/";
    }

    private function getTempPath() {
        return $this->tmp_path;
    }

    private function createTempFile($type) {
        return tempnam($this->getTempPath(), $type . "_") . "." . $type;
    }

    /**
     */
    public function processPDF($url = null) {
        $url = urldecode($url);

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $this->returnError("Not a valid URL.");
        }

        $tempfile = $this->createTempFile("pdf");
        file_put_contents($tempfile, fopen($url, 'r'));
        
        if (filesize($tempfile) == 0) {
        	$this->returnError("Empty PDF file. Is the file publicly available?");
        }

        $text = null;
        try {
            $text = \Spatie\PdfToText\Pdf::getText($tempfile, '/usr/bin/pdftotext');
        } catch (Exception $e) {
            $this->returnError($e->getMessage());
        }

        unlink($tempfile);

        if (empty($text)) $this->returnError("No text in the PDF.");

        $json = json_encode($text);

        return $json;
    }

    private function isValidKey($key) {
        return true;
    }

    private function returnError($msg) {
        error_log($msg);
        header('HTTP/1.0 500 Internal Server Error');
        die(json_encode(array('error' => "PDF Processor error: " . $msg)));
    }

    public function process($data) {
        if (!isset($data['key'])) {
            $this->returnError("Key is missing.");
        }
        if (!$this->isValidKey($data['key'])) {
            $this->returnError("Key is not valid.");
        }
        if (isset($data['url'])) {
            $text = $this->processPDF($data['url']);
            die($text);
        }
        $this->returnError("No action selected.");
    }
}
