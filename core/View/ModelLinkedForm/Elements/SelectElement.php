<?php
namespace ImpressCMS\Core\View\ModelLinkedForm\Elements;

use icms;
use ImpressCMS\Core\IPF\AbstractDatabaseModel;

/**
 * Form control creating a selectbox for an object derived from \ImpressCMS\Core\IPF\AbstractModel
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package	ICMS\IPF\Form\Elements
 * @since	1.1
 * @author	marcan <marcan@impresscms.org>
 */
class SelectElement extends \ImpressCMS\Core\View\Form\Elements\SelectElement {
	protected $_multiple = false;

	/**
	 * Constructor
	 * @param	AbstractDatabaseModel    $object   reference to targetobject
	 * @param	string    $key      the form name
	 */
	public function __construct($object, $key) {
		$var = $object->getVarInfo($key);
		$size = $var['size'] ?? $this->_multiple ? 5 : 1;

		// Adding the options inside this SelectBox
		// If the custom method is not from a module, than it's from the core
		$control = $object->getControl($key);

		$value = $control['value'] ?? $object->getVar($key, 'e');

		parent::__construct($var['form_caption'], $key, $value, $size, $this->_multiple);

		if (isset($control['options'])) {
			$this->addOptionArray($control['options']);
		} else {
			// let's find if the method we need to call comes from an already defined object
			if (isset($control['object'])) {
				if (method_exists($control['object'], $control['method'])) {
					if ($option_array = $control['object']->$control['method']()) {
						// Adding the options array to the select element
						$this->addOptionArray($option_array);
					}
				}
			} else {
				// finding the itemHandler; if none, let's take the itemHandler of the $object
				if (isset($control['itemHandler'])) {
					if (!isset($control['module'])) {
						// Creating the specified core object handler
						$control_handler = icms::handler($control['itemHandler']);
					} elseif ($control['module'] === 'icms') {
						$control_handler = icms::handler($control['module'] . '_' . $control['itemHandler']);
					} else {
						$control_handler = & icms_getModuleHandler($control['itemHandler'], $control['module']);
					}
				} else {
					$control_handler = & $object->handler;
				}

				// Checking if the specified method exists
				if (method_exists($control_handler, $control['method'])) {
					$option_array = call_user_func_array([$control_handler, $control['method']],
						$control['params'] ?? []);
					if (is_array($option_array) && count($option_array) > 0) {
						// Adding the options array to the select element
						$this->addOptionArray($option_array);
					}
				}
			}
		}
	}
}