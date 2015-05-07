<?php
/*------------------------------------------------------------------------
# Copyright (C) 2005-2012 WebxSolution Ltd. All Rights Reserved.
# @license - GPLv2.0
# Author: WebxSolution Ltd
# Websites:  http://www.webxsolution.com
# Terms of Use: An extension that is derived from the JoomlaCK editor will only be allowed under the following conditions: http://joomlackeditor.com/terms-of-use
# ------------------------------------------------------------------------*/ 
defined('JPATH_BASE') or die;

 
JCKLoader::loadExtendClass('view'); 

class InstallViewInstall extends JCKView
{

	public function __construct($config = array())
	{
		parent::__construct($config);
		$document = JFactory::getDocument();
	    $document->addStyleSheet($this->baseurl.'/css/style.css' );

        jimport('joomla.environment.browser');
		$browser = JBrowser::getInstance();
        $browserType = $browser->getBrowser();
        $browserVersion = $browser->getMajor();
		
        if(($browserType == 'msie') && ($browserVersion == 7))
        {
			$document->addStyleSheet($this->baseurl. '/css/style_ie7.css' );
        }
		elseif(($browserType == 'msie') && ($browserVersion == 8))
		{
			$document->addStyleSheet($this->baseurl.'/css/style_ie8.css' );
		}
		
		if(defined('JLEGACY_CMS'))
	   		$document->addScript(str_replace('plugins/editors/jckeditor/install','',$this->baseurl) .'media/system/js/mootools.js');
		else
			$document->addScript(str_replace('plugins/editors/jckeditor/install','',$this->baseurl) .'media/system/js/mootools-core.js');	

	
	}
	
	public function display( $tpl = null)
	{
	   switch($this->getLayout())
	   {
		case 'default':
		 $this->assignRef('nonExecutableFilesTotal',$this->get('NonExecutableFilesTotal'));
		 $this->assignRef('incorrectChmodFilesTotal',$this->get('IncorrectChmodFilesTotal'));
		 $this->assignRef('incorrectChmodFoldersTotal',$this->get('IncorrectChmodFoldersTotal'));
		 $this->assignRef('nonWritableImageFolderTotal',$this->get('NonWritableImageFolderTotal'));
		 $this->assignRef('permission',$this->get('Permission'));
		 $this->assignRef('folderPermission',$this->get('folderPermission'));
		break;
		case 'default2':
		$model = JCKModel::getInstance('Font','InstallModel');
		$this->assignRef('fontFamilyList',$model->getFontFamilyList());
		$this->assignRef('defaultFontColor',$model->getDefaultFontColor());
		$this->assignRef('fontSizeList',$model->getFontSizeList());
		$this->assignRef('defaultBackgroundColor',$model->getDefaultBackgroundColor());
	   	break;
		case 'default3':
		$model = JCKModel::getInstance('Folders','InstallModel');
		$this->assignRef('useUserFolderBooleanList',$model->getUseUserFolderBooleanList());
		$this->assignRef('userFolderTypeList',$model->getUserFolderTypeList());
		if( !defined('JLEGACY_CMS') )
			$this->assignRef('userList',$model->getUserList());
	   	break;
		case 'default4':
		$model = JCKModel::getInstance('Template','InstallModel');
		$this->assignRef('templateList',$model->getTemplateList());
		$this->assignRef('stylesheetList',$model->getStylesheetList());
		$this->assignRef('templateStylesheets',$model->getTemplateStylesheets());
		$this->assignRef('JCKTypographyBooleanList',$model->getJCKTypographyBooleanList());
		break;
	   }
		
		parent::display($tpl);
	}





}