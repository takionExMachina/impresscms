<?php
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
/**
 * All functions for DHTML text area are here.
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 */
namespace ImpressCMS\Core\View\Form\Elements;

use ImpressCMS\Core\DataFilter;
use ImpressCMS\Core\Extensions\Editors\EditorsRegistry;

/**
 * A textarea with bbcode formatting and smilie buttons
 *
 * @package	ICMS\Form\Elements
 * @author	Kazumi Ono	<onokazu@xoops.org>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 */
class DHTMLTextAreaElement extends TextAreaElement {
	/**
	 * Extended HTML editor definition
	 *
	 * Note: the PM window doesn't use \ImpressCMS\Core\Form\Elements\DhtmltextareaElement, so no need to report it doesn't work here
	 *
	 * array('className', 'classPath'):  To create an instance of "className", declared in the file ICMS_ROOT_PATH . $classPath
	 *
	 * Example:
	 * $htmlEditor = array('XoopsFormTinyeditorTextArea', '/class/xoopseditor/tinyeditor/formtinyeditortextarea.php');
	 */
	public $htmlEditor = array();

	/**
	 * Hidden text
	 * @var	string
	 * @access	private
	 */
	private $_hiddenText;

	/**
	 * Constructor
	 *
	 * @param string $caption Caption
	 * @param string $name "name" attribute
	 * @param string $value Initial text
	 * @param int $rows Number of rows
	 * @param int $cols Number of columns
	 * @param string $hiddentext Hidden Text
	 * @throws Exception
	 */
	public function __construct($caption, $name, $value, $rows = 5, $cols = 50, $hiddentext = 'xoopsHiddenText', $options = array()) {
		parent::__construct($caption, $name, $value, $rows, $cols);
		$this->_hiddenText = $hiddentext;
		global $icmsConfig, $icmsModule;

		$groups = (is_object(\icms::$user)) ? \icms::$user->getGroups() : ICMS_GROUP_ANONYMOUS;
		$moduleid = (is_object($icmsModule) && $name !== 'com_text') ? $icmsModule->getVar('mid') : 1;

		if (isset($options['editor']) && $options['editor'] && $options['editor'] !== $icmsConfig['editor_default']) {
			$editor_default = $options['editor'];
		} else {
			$editor_default = $icmsConfig['editor_default'];
		}

		$gperm_handler = \icms::handler('icms_member_groupperm');
		$editorHandler = EditorsRegistry::getInstance('content');
		if ($editorHandler->has($editor_default) && $gperm_handler->checkRight('use_wysiwygeditor', $moduleid, $groups, 1, false)) {
			$this->htmlEditor = $editorHandler->get($editor_default);
		} else {
			$this->htmlEditor = false;
		}
	}

	/**
	 * Prepare HTML for output
	 *
	 * @return	string  HTML
	 */
	public function render() {
		global $icmsConfigPlugins, $icmsConfigMultilang;
		$editor = false;
		if ($this->htmlEditor && is_object($this->htmlEditor)) {
			if (!isset($this->htmlEditor->isEnabled) || $this->htmlEditor->isEnabled) {
				$editor = true;
			}
		}
		if ($editor) {
			return $this->htmlEditor->render();
		}
		$name = $this->getName();
		$ele_name = $name . '_tarea';
		$ret = "<a name='moresmiley'></a>"
			. "<img onmouseover='style.cursor=\"pointer\"' src='" . ICMS_URL . "/images/url.gif' alt='url' onclick='xoopsCodeUrl(\"" . $ele_name . '", "' . htmlspecialchars(_ENTERURL, ENT_QUOTES) . '", "' . htmlspecialchars(_ENTERWEBTITLE, ENT_QUOTES, _CHARSET) . "\");' />&nbsp;"
			. "<img onmouseover='style.cursor=\"pointer\"' src='" . ICMS_URL . "/images/email.gif' alt='email' onclick='javascript:xoopsCodeEmail(\"" . $ele_name . '", "' . htmlspecialchars(_ENTEREMAIL, ENT_QUOTES, _CHARSET) . "\");' />&nbsp;"
			. "<img onclick='javascript:xoopsCodeImg(\"" . $ele_name . '", "' . htmlspecialchars(_ENTERIMGURL, ENT_QUOTES) . '", "' . htmlspecialchars(_ENTERIMGPOS, ENT_QUOTES) . '", "' . htmlspecialchars(_IMGPOSRORL, ENT_QUOTES) . "\", \"" . htmlspecialchars(_ERRORIMGPOS, ENT_QUOTES, _CHARSET) . "\");' onmouseover='style.cursor=\"pointer\"' src='" . ICMS_URL . "/images/imgsrc.gif' alt='imgsrc' />&nbsp;"
			. "<img onmouseover='style.cursor=\"pointer\"' onclick='javascript:openWithSelfMain(\"" . ICMS_URL . '/modules/system/admin/images/browser.php?target=' . $ele_name . "&type=iman\",\"imgmanager\",985,470);' src='" . ICMS_URL . "/images/image.gif' alt='image' />&nbsp;";
		$jscript = '';
		foreach ($icmsConfigPlugins['sanitizer_plugins'] as $key) {
			$extension = DataFilter::loadExtension($key);
			$func = "render_{$key}";
			if (function_exists($func)) {
				@list($encode, $js) = $func($ele_name);
				if (empty($encode)) {
					continue;
				}
				$ret .= $encode;
			}
		}
		$ret .= "<img src='" . ICMS_URL . "/images/code.gif' onmouseover='style.cursor=\"pointer\"' alt='code' onclick='javascript:xoopsCodeCode(\"" . $ele_name . '", "' . htmlspecialchars(_ENTERCODE, ENT_QUOTES, _CHARSET) . "\");' />&nbsp;"
			. "<img onclick='javascript:xoopsCodeQuote(\"" . $ele_name . '", "' . htmlspecialchars(_ENTERQUOTE, ENT_QUOTES, _CHARSET) . "\");' onmouseover='style.cursor=\"pointer\"' src='" . ICMS_URL . "/images/quote.gif' alt='quote' /><br />\n";
		$easiestml_exist = ($icmsConfigMultilang['ml_enable'] === '1' && defined('EASIESTML_LANGS') && defined('EASIESTML_LANGNAMES'));
		if ($easiestml_exist) {
			$easiestml_langs = explode(',', EASIESTML_LANGS);
			$langlocalnames = explode(',', EASIESTML_LANGNAMES);
			$langnames = explode(',', $icmsConfigMultilang['ml_names']);

			$code = '';
			$javascript = '';

			foreach ($easiestml_langs as $l => $lang) {
				$ret .= "<img onclick='javascript:icmsCode_languages(\"" . $ele_name . '", "' . htmlspecialchars(sprintf(_ENTERLANGCONTENT, $langlocalnames[$l]), ENT_QUOTES, _CHARSET) . '", "' . $lang . "\");' onmouseover='style.cursor=\"pointer\"' src='" . ICMS_URL . '/images/flags/' . $langnames[$l] . ".gif' alt='" . $langlocalnames[$l] . "' />&nbsp;";
			}
			$ret .= "<br />\n";
		}

		$sizearray = ['xx-small', 'x-small', 'small', 'medium', 'large', 'x-large', 'xx-large'];
		$ret .= "<select id='" . $ele_name . "Size' onchange='setVisible(\"" . $this->_hiddenText . '");setElementSize("' . $this->_hiddenText . "\",this.options[this.selectedIndex].value);'>\n";
		$ret .= "<option value='SIZE'>" . _SIZE . "</option>\n";
		foreach ($sizearray as $size) {
			$ret .= "<option value='$size'>$size</option>\n";
		}
		$ret .= "</select>\n";
		$fontarray = ['Arial', 'Courier', 'Georgia', 'Helvetica', 'Impact', 'Tahoma', 'Verdana'];
		$ret .= "<select id='" . $ele_name . "Font' onchange='setVisible(\"" . $this->_hiddenText . '");setElementFont("' . $this->_hiddenText . "\",this.options[this.selectedIndex].value);'>\n";
		$ret .= "<option value='FONT'>" . _FONT . "</option>\n";
		foreach ($fontarray as $font) {
			$ret .= "<option value='$font'>$font</option>\n";
		}
		$ret .= "</select>\n";
		$colorarray = ['00', '33', '66', '99', 'CC', 'FF'];
		$ret .= "<select id='" . $ele_name . "Color' onchange='setVisible(\"" . $this->_hiddenText . '");setElementColor("' . $this->_hiddenText . "\",this.options[this.selectedIndex].value);'>\n";
		$ret .= "<option value='COLOR'>" . _COLOR . "</option>\n";
		foreach ($colorarray as $color1) {
			foreach ($colorarray as $color2) {
				foreach ($colorarray as $color3) {
					$ret .= "<option value='" . $color1 . $color2 . $color3 . "' style='background-color:#" . $color1 . $color2 . $color3 . ';color:#' . $color1 . $color2 . $color3 . ";'>#" . $color1 . $color2 . $color3 . "</option>\n";
				}
			}
		}
		$ret .= "</select><span id='" . $this->_hiddenText . "'>" . _EXAMPLE . "</span>\n";
		$ret .= "<br />\n";
		$ret .= sprintf(
			"<img onclick='javascript:xoopsmake%s(\"%s\", \"%s\");' onmouseover='style.cursor=\"pointer\"' src='%s/images/align%s.gif' alt='align%s' />&nbsp;<img onclick='javascript:xoopsmakecenter(\"%s\", \"%s\");' onmouseover='style.cursor=\"pointer\"' src='%s/images/aligncenter.gif' alt='aligncenter' />&nbsp;<img onclick='javascript:xoopsmake%s(\"%s\", \"%s\");' onmouseover='style.cursor=\"pointer\"' src='%s/images/align%s.gif' alt='align%s' />&nbsp;<img onclick='javascript:setVisible(\"%s\");makeBold(\"%s\");' onmouseover='style.cursor=\"pointer\"' src='%s/images/bold.gif' alt='bold' />&nbsp;<img onclick='javascript:setVisible(\"%s\");makeItalic(\"%s\");' onmouseover='style.cursor=\"pointer\"' src='%s/images/italic.gif' alt='italic' />&nbsp;<img onclick='javascript:setVisible(\"%s\");makeUnderline(\"%s\");' onmouseover='style.cursor=\"pointer\"' src='%s/images/underline.gif' alt='underline' />&nbsp;<img onclick='javascript:setVisible(\"%s\");makeLineThrough(\"%s\");' src='%s/images/linethrough.gif' alt='linethrough' onmouseover='style.cursor=\"pointer\"' />&nbsp;&nbsp;<input type='text' id='%sAddtext' size='20' />&nbsp;<input type='button' onclick='xoopsCodeText(\"%s\", \"%s\", \"%s\")' class='formButton' value='%s' /><br /><br /><textarea id='%s' name='%s' onselect=\"xoopsSavePosition('%s');\"' onclick=\"xoopsSavePosition('%s');\"' onkeyup=\"xoopsSavePosition('%s');\"' cols='%s' rows='%s'%s>%s</textarea><br />\n",
			_GLOBAL_LEFT,
			$ele_name,
			htmlspecialchars(((defined('_ADM_USE_RTL') && _ADM_USE_RTL) ? _ALRIGHTCON : _ALLEFTCON), ENT_QUOTES),
			ICMS_URL,
			_GLOBAL_LEFT,
			_GLOBAL_LEFT,
			$ele_name,
			htmlspecialchars(_ALCENTERCON, ENT_QUOTES, _CHARSET),
			ICMS_URL,
			_GLOBAL_RIGHT,
			$ele_name,
			htmlspecialchars(((defined('_ADM_USE_RTL') && _ADM_USE_RTL) ? _ALLEFTCON : _ALRIGHTCON), ENT_QUOTES),
			ICMS_URL,
			_GLOBAL_RIGHT,
			_GLOBAL_RIGHT,
			$this->_hiddenText,
			$this->_hiddenText,
			ICMS_URL,
			$this->_hiddenText,
			$this->_hiddenText,
			ICMS_URL,
			$this->_hiddenText,
			$this->_hiddenText,
			ICMS_URL,
			$this->_hiddenText,
			$this->_hiddenText,
			ICMS_URL,
			$ele_name,
			$ele_name,
			$this->_hiddenText,
			htmlspecialchars(_ENTERTEXTBOX, ENT_QUOTES, _CHARSET),
			_ADD,
			$ele_name,
			$name,
			$ele_name,
			$ele_name,
			$ele_name,
			$this->getCols(),
			$this->getRows(),
			$this->getExtra(),
			$this->getValue()
		);
		$ret .= $this->_renderSmileys();
		return $ret;
	}

	/**
	 * Render Validation Javascript
	 *
	 * @return	mixed  rendered validation javascript or empty string
	 */
	public function renderValidationJS() {
		if ($this->htmlEditor && is_object($this->htmlEditor) && method_exists($this->htmlEditor, 'renderValidationJS')) {
			if (!isset($this->htmlEditor->isEnabled) || $this->htmlEditor->isEnabled) {
				return $this->htmlEditor->renderValidationJS();
			}
		}
		return '';
	}

	/**
	 * prepare HTML for output of the smiley list.
	 *
	 * @return	string HTML
	 */
	private function _renderSmileys() {
		$smiles = DataFilter::getSmileys();
		$ret = '';
		$count = count($smiles);
		$ele_name = $this->getName();
		for ($i = 0; $i < $count; $i++) {
			$ret .= sprintf(
				"<img onclick='xoopsCodeSmilie(\"%s_tarea\", \" %s \");' onmouseover='style.cursor=\"pointer\"' src='%s/%s' border='0' alt='' />",
				$ele_name,
				$smiles[$i]['code'],
				ICMS_UPLOAD_URL,
				htmlspecialchars($smiles[$i]['smile_url'], ENT_QUOTES, _CHARSET)
			);
		}
		$ret .= sprintf(
			"&nbsp;[<a href='#moresmiley' onclick='javascript:openWithSelfMain(\"%s/misc.php?action=showpopups&amp;type=smilies&amp;target=%s_tarea\",\"smilies\",300,475);'>%s</a>]",
			ICMS_URL,
			$ele_name,
			_MORE
		);
		return $ret;
	}
}
