<?php

class Matheus_ImportReviews_StartController extends Mage_Adminhtml_Controller_Action{
	public function indexAction() {
		/** Set default timezone */
                date_default_timezone_set('America/Bahia');
                /** Set file directory */
                $this->tmpDir = __DIR__.'/../temp/reviews.csv';
                $sheetName = basename($_FILES['file_to_upload']['name']);
                $excelFileType = strtolower(pathinfo($sheetName,PATHINFO_EXTENSION));
		/** Material Icons */
		echo "<link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>"; 
		echo "<pre>";
		$this->error_icon = "<i class='material-icons' style='font-size:22px;color:red;vertical-align: bottom;'>error_outline</i>";
		$this->done_icon = "<i class='material-icons' style='font-size:22px;color:green;vertical-align: bottom;'>done</i>";
		$this->loading_icon = "<i class='material-icons' style='font-size:22px;vertical-align: bottom;'>schedule</i>";
		/** Process start */
                if($excelFileType!='csv'){
                        echo "<p>".$this->error_icon."<b> ".date('H:i:s')." </b>Error: file format '".$excelFileType."' isn't accepted. You need to select a csv file.</p>";
                }
                elseif(move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $this->tmpDir)){
                        echo "<p>".$this->loading_icon."<b> ".date('H:i:s')." </b>Starting process...</p>";
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
					->setDetail($worksheet->getCellByColumnAndRow(2, $i)->getValue()) 
					->setStatusId($worksheet->getCellByColumnAndRow(3, $i)->getValue())
					->setNickname($worksheet->getCellByColumnAndRow(4, $i)->getValue()) 
					->setCustomerId($worksheet->getCellByColumnAndRow(5, $i)->getValue()) 
					->setStores(array(Mage::app()->getStore()->getId()))
					->setStoreId(0)
					->setEntityId(1) 
					->save();
					
				$stars = $worksheet->getCellByColumnAndRow(6, $i)->getValue();
				if($stars != null){
					$ratings = $this->getRatings();
					foreach ($ratings as $ratingId => $options) {
						Mage::getModel('rating/rating')
						    ->setRatingId($ratingId)
						    ->setReviewId($review->getId())
						    ->addOptionVote($options[$worksheet->getCellByColumnAndRow(6, $i)->getValue()], $productId);
					}
				}
				$review->aggregate();
				$review->save();
				$successIndex += 1;
			}
			else{
				$errorLines[$errorIndex] = $i;
				$errorIndex += 1;
			}
		}
                if($errorIndex != 0){
                        $errorString = "{";
                        foreach($errorLines as $line){
                                $errorString .= $line.",";
                        }
                        $errorString = substr($errorString, 0, -1);
                        $errorString .= "}";
                        echo "<p>".$this->error_icon."<b> ".date('H:i:s')," </b>The following rows had invalid skus and were ignored: <b>".$errorString."</b></p>";
                }
		echo "<p>".$this->done_icon."<b> ".date('H:i:s')."</b> Process finished. <b>".$successIndex."</b> reviews were successfully added to their products.<p>";
	}

	private function skuExists($sku){
                $ver = True;
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                if(!$product){
                        $ver = False;
                }
                return $ver;
        }

	private function getRatings(){
		$ratings = [];
		$options = Mage::getModel('rating/rating_option')->getCollection();
		foreach ($options as $option) {
		    $ratings[$option->getRatingId()][$option->getValue()] = $option->getOptionId();
		}
		return $ratings;
        }
	
	private function deleteTmpFile(){
                  chmod($this->tmpDir,0755); 
                  unlink($this->tmpDir); 
        }
}
