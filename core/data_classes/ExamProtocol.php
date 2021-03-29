<?php
class ExamProtocol {
    private $ID;
    private $status;
    private $uploadedByUserID;
    private $collaboratorIDs;
    private $uploadedDate;
    private $remark;
    private $examiner;
    private $fileName;
    private $fileSize;
    private $fileType;
    private $fileExtension;

    function __construct($ID, $status, $uploadedByUserID, $collaboratorIDs, $uploadedDate, $remark, $examiner, $fileName, $fileSize, $fileType, $fileExtension) {
        $this->ID = $ID;
        $this->status = $status;
        $this->uploadedByUserID = $uploadedByUserID;
        $this->collaboratorIDs = $collaboratorIDs;
        $this->uploadedDate = $uploadedDate;
        $this->remark = $remark;
        $this->examiner = $examiner;
        $this->fileName = $fileName;
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

    public function getCollaboratorIDs(){
        return $this->collaboratorIDs;
    }

    public function setCollaboratorIDs($collaboratorIDs){
        $this->collaboratorIDs = $collaboratorIDs;
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

    public function getFileName(){
        return $this->fileName;
    }

    public function setFileName($fileName){
        $this->fileName = $fileName;
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
