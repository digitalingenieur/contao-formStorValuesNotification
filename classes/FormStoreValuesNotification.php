<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   formStoreValuesNotification
 * @author    Samuel Heer
 * @license   LGPL-3.0+
 * @copyright diging 2015
 */

 /**
 * Provide methods to handle front end forms.
 *
 * @author Samuel Heer <https://github.com/diging>
 */
class FormStoreValuesNotification extends Backend {
	
	public function processFormData($arrPost, $arrForm, $arrFiles, $arrLabels, $objForm){
		
		if ($objForm->storeValues && $objForm->targetTable != '' && $objForm->sendViaEmail && $objForm->format == 'notification'){
			//Get the DatabaseEntry which was created
			$intId = $this->getStoredDatabaseEntry($arrPost, $objForm->targetTable);
			
			// Get the Backend Link for specified table
			$strLink = $this->getBackendLink($arrPost, $objForm->targetTable, $intId);
			
			$recipients = \String::splitCsv($objForm->recipient);

			// Format recipients
			foreach ($recipients as $k=>$v)
			{
				$recipients[$k] = str_replace(array('[', ']', '"'), array('<', '>', ''), $v);
			}

			$email = new \Email();

			// Set the admin e-mail as "from" address
			$email->from = $GLOBALS['TL_ADMIN_EMAIL'];
			$email->fromName = $GLOBALS['TL_ADMIN_NAME'];
			$email->subject = $this->replaceInsertTags($this->subject, false);
			
			$email->text = \String::decodeEntities($strLink);

			// Send the e-mail
			try
			{
				$email->sendTo($recipients);
			}
			catch (\Swift_SwiftException $e)
			{
				$this->log('Form "' . $this->title . '" could not be sent: ' . $e->getMessage(), __METHOD__, TL_ERROR);
			}
		}	
	}
	
	/**
	 * get ID for just stored database entry 
	 *
	 * @param array $arrPost
	 * @param string $targetTable
	 *
	 * @return int
	 */
	private function getStoredDatabaseEntry($arrPost, $targetTable){
	
		$arrWhereClause = array();
		foreach($arrPost as $field => $value){
			$arrWhereClause[] = $field.'=?';
		}
		
		//Get exact entry (all columns have to match)
		$objResult = $this->Database->prepare("SELECT * FROM " . $targetTable .' WHERE '. implode(' AND ',$arrWhereClause))->execute($arrPost);
		
		return $objResult->id;
	}
	
	
	private function getBackendLink($arrPost, $targetTable, $id){
		
		//Find out which module is relevant for given target table
		$searchTable = $targetTable;
		if($GLOBALS['TL_DCA'][$targetTable]['config']['dynamicPtable'] === true){
			$searchTable = $arrPost['ptable'];
		}

		foreach($GLOBALS['BE_MOD'] as $category){
			foreach($category as $moduleName => $module){
				
				if(is_array($module['tables']) && in_array($searchTable,$module['tables'])){
					$do = $moduleName;
				}
			}
		}
		
		//Return url
		$url = Environment::get('base');
		if (version_compare(VERSION, '4.0', '>=')) {
			$url .= \System::getContainer()->get('router')->generate('contao_backend');
		}
		else {
			$url .= 'contao/main.php';
		}
		return $url.'?do='.$do.'&amp;table='.$targetTable.'&amp;act=edit&amp;id='.$id.'&amp;ref=' . REQUEST_TOKEN;
	}
	
}