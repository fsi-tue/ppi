<?php
class ExamProtocol {
    private $ID;
    private $status;
    private $uploadedByUserID;
    private $uploadedDate;
    private $remark;
    private $examiner;
    private $filePath;
    private $fileSize;
    private $fileType;
    private $fileExtension;

    function __construct($ID, $status, $uploadedByUserID, $uploadedDate, $remark, $examiner, $filePath, $fileSize, $fileType, $fileExtension) {
        $this->ID = $ID;
        $this->status = $status;
        $this->uploadedByUserID = $uploadedByUserID;
        $this->uploadedDate = $uploadedDate;
        $this->remark = $remark;
        $this->examiner = $examiner;
        $this->filePath = $filePath;
        $this->fileSize = $fileSize;
        $this->fileType = $fileType;
        $this->fileExtension = $fileExtension;
    }
    
    public function getID(){
        return $this->ID;
    }

    public function setID($ID){
        $this->ID = $ID;
    }

    public function getStatus(){
        return $this->status;
    }

    public function setStatus($status){
        $this->status = $status;
    }

    public function getUploadedByUserID(){
        return $this->uploadedByUserID;
    }

    public function setUploadedByUserID($uploadedByUserID){
        $this->uploadedByUserID = $uploadedByUserID;
    }

    public function getUploadedDate(){
        return $this->uploadedDate;
    }

    public function setUploadedDate($uploadedDate){
        $this->uploadedDate = $uploadedDate;
    }

    public function getRemark(){
        return $this->remark;
    }

    public function setRemark($remark){
        $this->remark = $remark;
    }

    public function getExaminer(){
        return $this->examiner;
    }

    public function setExaminer($examiner){
        $this->examiner = $examiner;
    }

    public function getFilePath(){
        return $this->filePath;
    }

    public function setFilePath($filePath){
        $this->filePath = $filePath;
    }

    public function getFileSize(){
        return $this->fileSize;
    }

    public function setFileSize($fileSize){
        $this->fileSize = $fileSize;
    }

    public function getFileType(){
        return $this->fileType;
    }

    public function setFileType($fileType){
        $this->fileType = $fileType;
    }

    public function getFileExtension(){
        return $this->fileExtension;
    }

    public function setFileExtension($fileExtension){
        $this->fileExtension = $fileExtension;
    }
}
?>
