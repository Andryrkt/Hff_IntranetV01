<?php

namespace App\Model;

class FileReaderModel
{
    private $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function readContent()
    {
        // Check if the file exists
        if (file_exists($this->filePath)) {
            // Read the file content
            $content = file_get_contents($this->filePath);

            // Display or return the content
            return $content;
        } else {
            return "File not found!";
        }
    }
}
