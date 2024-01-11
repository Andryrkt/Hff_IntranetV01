<?php

class FileReaderControl{
    private $FileReaderModel;

    public function __construct(FileReaderModel $fileReaderModel)
    {
        $this->FileReaderModel = $fileReaderModel;
    }
    public function getcontentFile(){ 
        
        return $this->FileReaderModel->readContent();
    }
}   