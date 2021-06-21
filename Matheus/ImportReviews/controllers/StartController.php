<?php

class Matheus_ImportReviews_StartController extends Mage_Adminhtml_Controller_Action{
	public function indexAction() {
		/** Set default timezone */
                date_default_timezone_set('America/Bahia');
                /** Set file directory */
                $this->tmpDir = __DIR__.'/../temp/reviews.csv';
                $sheetName = basename($_FILES['file_to_upload']['name']);
                $excelFileType = strtolower(pathinfo($sheetName,PATHINFO_EXTENSION));
                if($excelFileType!='csv'){
                        echo "<b>".date('H:i:s')." </b>Error: file format '".$excelFileType."' isn't accepted. You need to select a csv file.<br><br>";
                }
                elseif(move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $this->tmpDir)){
                        echo "<b>".date('H:i:s')." </b>Starting process...<br><br>";
                        $this->importReviews();
                }
                $this->deleteTmpFile();

	}

	private function importReviews(){
		require_once dirname(__FILE__).'/../Classes/PHPExcel.php';
                $inputFileType = PHPExcel_IOFactory::identify($this->tmpDir);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($this->tmpDir);
                $worksheet  = $objPHPExcel->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
		$errorLines = array();
                $errorIndex = 0;
                $successIndex = 0;
		for($i=2; $i<=(int)$highestRow; $i++){
			Mage::app()->setCurrentStore(0);
			$sku = $worksheet->getCellByColumnAndRow(0, $i)->getValue();
			if($this->skuExists($sku)){
				$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
				$productId = $product->getId();
				$review = Mage::getModel('review/review');
				$review->setEntityPkValue($productId)
					->setTitle($worksheet->getCellByColumnAndRow(1, $i)->getValue())  
					->setDetail($worksheet->getCellByColumnAndRow(2, $i)->getValue()) # Review
					->setEntityId(1) 
					->setStoreId(Mage::app()->getStore()->getId())
					->setStatusId($worksheet->getCellByColumnAndRow(3, $i)->getValue()) # 1 - Approved, 2 - Pending, 3 - Not Approved
					->setNickname($worksheet->getCellByColumnAndRow(4, $i)->getValue()) # Customer name
					->setCustomerId($worksheet->getCellByColumnAndRow(5, $i)->getValue()) # null is for administrator
					->setReviewId($review->getId())
					->setStores(array(Mage::app()->getStore()->getId()));

				$review->save();
				$review->aggregate();
				$successIndex += 1;
			}
			else{
				$errorLines[$errorIndex] = $i;
				$errorIndex += 1;
			}
		}
		echo "<b>".date('H:i:s')."</b> Process finished. <b>".$successIndex."</b> reviews were successfully added to their products.<br><br>";
                if($errorIndex != 0){
                        $errorString = "{";
                        foreach($errorLines as $line){
                                $errorString .= $line.",";
                        }
                        $errorString = substr($errorString, 0, -1);
                        $errorString .= "}";
                        echo "<b>".date('H:i:s')," </b>The following rows had invalid skus and were ignored: <b>".$errorString."</b>";
                }
	}

	private function skuExists($sku){
                $ver = True;
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                if(!$product){
                        $ver = False;
                }
                return $ver;
        }

	private function deleteTmpFile(){
                  chmod($this->tmpDir,0755); //Change the file permissions if allowed
                  unlink($this->tmpDir); //remove the file
        }

}
