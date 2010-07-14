<?php
/**
 * Creates a form radiobutton attribute
 *
 * @copyright	http://www.xoops.org/ The XOOPS Project
 * @copyright	XOOPS_copyrights.txt
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license	LICENSE.txt
 * @package	XoopsForms
 * @since	XOOPS
 * @author	http://www.xoops.org The XOOPS Project
 * @author	modified by UnderDog <underdog@impresscms.org>
 * @version	$Id$
 */

if (!defined('ICMS_ROOT_PATH')) die("ImpressCMS root path not defined");
/**
 * @package     kernel
 * @subpackage  form
 *
 * @author	    Kazumi Ono	<onokazu@xoops.org>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 */

/**
 * base class
 */
include_once ICMS_ROOT_PATH."/class/xoopsform/formradio.php";

/**
 * Yes/No radio buttons.
 *
 * A pair of radio buttons labeled _YES and _NO with values 1 and 0
 *
 * @package     kernel
 * @subpackage  form
 *
 * @author	    Kazumi Ono	<onokazu@xoops.org>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 */
class XoopsFormRadioYN extends icms_form_elements_Radioyn
{
	private $_deprecated;
	public function __construct() {
		parent::getInstance();
		$this->_deprecated = icms_core_Debug::setDeprecated('icms_form_elements_Radioyn', sprintf(_CORE_REMOVE_IN_VERSION, '1.4'));
	}
}

?>