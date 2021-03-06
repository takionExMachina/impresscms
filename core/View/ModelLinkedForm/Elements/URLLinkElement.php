<?php
namespace ImpressCMS\Core\View\ModelLinkedForm\Elements;

use icms;
use ImpressCMS\Core\IPF\AbstractDatabaseModel;
use ImpressCMS\Core\View\Form\Elements\HiddenElement;
use ImpressCMS\Core\View\Form\Elements\LabelElement;
use ImpressCMS\Core\View\Form\Elements\RadioElement;
use ImpressCMS\Core\View\Form\Elements\TrayElement;

/**
 * Form control creating an element to link and URL to an object derived from \ImpressCMS\Core\IPF\AbstractModel
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package	ICMS\IPF\Form\Elements
 * @since	1.1
 * @author	marcan <marcan@impresscms.org>
 */
class URLLinkElement extends TrayElement {
	/**
	 * Constructor
	 * @param	AbstractDatabaseModel	$object	target object
	 * @param	string			$key	the key
	 */
	public function __construct($object, $key) {
		parent::__construct($object->getVarInfo($key, 'form_caption'), '&nbsp;');
		$urllinkObj = $object->getUrlLinkObj($key);
		$module_handler = icms::handler('icms_module');
		$module = $module_handler->getByDirname($object->handler->_moduleName);

		$this->addElement(new LabelElement('', _CO_ICMS_URLLINK_URL));
		$this->addElement(new TextElement($urllinkObj, 'url_' . $key));
		$this->addElement(new LabelElement('', '<br/>' . _CO_ICMS_CAPTION));
		$this->addElement(new TextElement($urllinkObj, 'caption_' . $key));
		$this->addElement(new LabelElement('', '<br/>' . _CO_ICMS_DESC));
		$this->addElement(new TextElement($urllinkObj, 'desc_' . $key));
		$this->addElement(new LabelElement('', '<br/>' . _CO_ICMS_URLLINK_TARGET));
		$this->addElement(new HiddenElement('mid_' . $key, $module->getVar('mid')));
		$targ_val = $urllinkObj->getVar('target');
		$targetRadio = new RadioElement('', 'target_' . $key, $targ_val !== '' ?$targ_val: '_blank');
		$control = $urllinkObj->getControl('target');
		$targetRadio->addOptionArray($control['options']);
		$this->addElement($targetRadio);
	}
}