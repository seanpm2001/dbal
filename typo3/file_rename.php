<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 1999-2004 Kasper Skaarhoj (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license 
*  from the author is found in LICENSE.txt distributed with these scripts.
*
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Web>File: Renaming files and folders
 *
 * $Id$
 * Revised for TYPO3 3.6 November/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   75: class SC_file_rename 
 *   96:     function init()	
 *  148:     function main()	
 *  189:     function printContent()	
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
 


$BACK_PATH='';
require ('init.php');
require ('template.php');
require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');












/**
 * Script Class for the rename-file form.
 * 
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SC_file_rename {
	
		// Internal, static:
	var $doc;			// Template object.
	var $basicff;		// Instance of "t3lib_basicFileFunctions"
	var $icon;			// Will be set to the proper icon for the $target value.
	var $shortPath;		// Relative path to current found filemount
	var $title;			// Name of the filemount

		// Internal, static (GPVar):
	var $target;		// Set with the target path inputted in &target

		// Internal, dynamic:	
	var $content;		// Accumulating content
		
	
	/**
	 * Constructor function for class
	 * 
	 * @return	void		
	 */
	function init()	{
		global $LANG,$BACK_PATH,$TYPO3_CONF_VARS;

			// Initialize GPvars:
		$this->target = t3lib_div::GPvar('target');

			// Init basic-file-functions object:
		$this->basicff = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		$this->basicff->init($GLOBALS['FILEMOUNTS'],$TYPO3_CONF_VARS['BE']['fileExtensions']);
		
			// Cleaning and checking target
		if (@file_exists($this->target))	{
			$this->target=$this->basicff->cleanDirectoryName($this->target);		// Cleaning and checking target (file or dir)
		} else {
			$this->target='';
		}
		$key=$this->basicff->checkPathAgainstMounts($this->target.'/');
		if (!$this->target || !$key)	{
			t3lib_BEfunc::typo3PrintError ('Parameter Error','Target was not a directory!','');
			exit;
		}

			// Finding the icon
		switch($GLOBALS['FILEMOUNTS'][$key]['type'])	{
			case 'user':	$this->icon = 'gfx/i/_icon_ftp_user.gif';	break;
			case 'group':	$this->icon = 'gfx/i/_icon_ftp_group.gif';	break;
			default:		$this->icon = 'gfx/i/_icon_ftp.gif';	break;
		}
		
			// Relative path to filemount, $key:
		$this->shortPath = substr($this->target,strlen($GLOBALS['FILEMOUNTS'][$key]['path']));
		
			// Setting title:
		$this->title = $GLOBALS['FILEMOUNTS'][$key]['name'].': '.$this->shortPath;
		
			// Setting template object
		$this->doc = t3lib_div::makeInstance('smallDoc');
		$this->doc->docType = 'xhtml_trans';
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="tce_file.php" method="post" name="editform">';
		$this->doc->JScode=$this->doc->wrapScriptTags('
			function backToList()	{	//
				top.goToModule("file_list");
			}
		');
	}

	/**
	 * Main function, rendering the content of the rename form
	 * 
	 * @return	void		
	 */
	function main()	{
		global $LANG;

			// Make page header:
		$this->content='';
		$this->content.=$this->doc->startPage($LANG->sL('LLL:EXT:lang/locallang_core.php:file_rename.php.pagetitle'));
		$this->content.=$this->doc->header($LANG->sL('LLL:EXT:lang/locallang_core.php:file_rename.php.pagetitle'));
		$this->content.=$this->doc->spacer(5);
		$this->content.=$this->doc->section('',$this->doc->getFileheader($this->title,$this->shortPath,$this->icon));
		$this->content.=$this->doc->divider(5);
		
		
			// Making the formfields for renaming:
		$code='
			
			<div id="c-rename">
				<input type="text" name="file[rename][0][data]" value="'.htmlspecialchars(basename($this->shortPath)).'"'.$GLOBALS['TBE_TEMPLATE']->formWidth(20).' />
				<input type="hidden" name="file[rename][0][target]" value="'.htmlspecialchars($this->target).'" />
			</div>
		';
		
			// Making submit button:
		$code.='
			<div id="c-submit">
				<input type="submit" value="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:file_rename.php.submit',1).'" />
				<input type="submit" value="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.cancel',1).'" onclick="backToList(); return false;" />
			</div>
		';
		
			// Add the HTML as a section:
		$this->content.= $this->doc->section('',$code);

			// Ending page
		$this->content.= $this->doc->endPage();
	}

	/**
	 * Outputting the accumulated content to screen
	 * 
	 * @return	void		
	 */
	function printContent()	{

		echo $this->content;	
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/file_rename.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/file_rename.php']);
}












// Make instance:
$SOBE = t3lib_div::makeInstance('SC_file_rename');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>