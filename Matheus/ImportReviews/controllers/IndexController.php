<?php

class Matheus_ImportReviews_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
	$url = $this->getUrl('import_reviews/start/index');
	$urlValue = Mage::getSingleton('core/session')->getFormKey();

	$block_content = "
	<form action='$url' method='post' enctype='multipart/form-data'>
	  <h4>Select file to upload:</h4>
	  <input type='file' name='file_to_upload' id='file_to_upload'>
	  <br><br>
	  <input type='hidden' name='form_key' value='$urlValue'>
	  <input type='submit' class='btn-export' value='Start' name='import'>
	</form>
	<style type='text/css'>
	.btn-export{
		display: block;
		border: 0;
		width: 80px;
		background: #4E9CAF;
		padding: 5px 0%;
		text-align: center;
		border-radius: 5px;
		color: white;
		font-weight: bold;
		cursor: pointer;
		line-height: 25px;
	}
	</style>";
	$this->loadLayout();

	$this->_setActiveMenu('catalog/matheus');
	$block = $this->getLayout()
	    ->createBlock('core/text', 'import_reviews')
	    ->setText($block_content);

	 $this->_addContent($block);
	 $this->renderLayout();
    }
}
