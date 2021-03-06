<?php
namespace ImpressCMS\Core\View\ModelLinkedForm;

use icms;
use ImpressCMS\Core\View\Form\AbstractFormElement;
use ImpressCMS\Core\View\Form\Elements\ButtonElement;
use ImpressCMS\Core\View\Form\Elements\CheckboxElement;
use ImpressCMS\Core\View\Form\Elements\ColorPickerElement;
use ImpressCMS\Core\View\Form\Elements\DHTMLTextAreaElement;
use ImpressCMS\Core\View\Form\Elements\HiddenElement;
use ImpressCMS\Core\View\Form\Elements\LabelElement;
use ImpressCMS\Core\View\Form\Elements\PasswordElement;
use ImpressCMS\Core\View\Form\Elements\RadioElement;
use ImpressCMS\Core\View\Form\Elements\Select\CountryElement;
use ImpressCMS\Core\View\Form\Elements\Select\GroupElement;
use ImpressCMS\Core\View\Form\Elements\Select\TimeZoneElement;
use ImpressCMS\Core\View\Form\Elements\Select\UserElement;
use ImpressCMS\Core\View\Form\Elements\SelectElement;
use ImpressCMS\Core\View\Form\Elements\TextAreaElement;
use ImpressCMS\Core\View\Form\Elements\TrayElement;
use ImpressCMS\Core\View\Form\ThemeForm;
use ImpressCMS\Core\View\ModelLinkedForm\Elements\FormSectionElement;
use ImpressCMS\Core\View\ModelLinkedForm\Elements\SelectMultiElement;
use ImpressCMS\Core\View\Theme\ThemeFactory;
use Smarty;

/**
 * Form control creating an image upload element for an object derived from \ImpressCMS\Core\IPF\AbstractModel
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package	ICMS\IPF\Form
 * @since	1.1
 * @author	marcan <marcan@impresscms.org>
 */
class Form extends ThemeForm {
	public $targetObject = null;
	public $form_fields = null;
	private $_cancel_js_action = false;
	private $_custom_button = false;
	private $_captcha = false;
	private $_form_name = false;
	private $_form_caption = false;
	private $_submit_button_caption = false;

	/**
	 * Constructor
	 * Sets all the values / variables for the IcmsForm class
	 * @param	string    &$target                  reference to targetobject (@todo, which object will be passed here?)
	 * @param	string    $form_name                the form name
	 * @param	string    $form_caption             the form caption
	 * @param	string    $form_action              the form action
	 * @param	string    $form_fields              the form fields
	 * @param	string|false    $submit_button_caption    whether to add a caption to the submit button
	 * @param	bool      $cancel_js_action         whether to invoke a javascript action when cancel button is clicked
	 * @param	bool      $captcha                  whether to add captcha
	 */
	public function __construct(&$target, $form_name, $form_caption, $form_action, $form_fields = null, $submit_button_caption = false, $cancel_js_action = false, $captcha = false) {
		$this->targetObject = & $target;
		$this->form_fields = $form_fields;
		$this->_cancel_js_action = $cancel_js_action;
		$this->_captcha = $captcha;
		$this->_form_name = $form_name;
		$this->_form_caption = $form_caption;
		$this->_submit_button_caption = $submit_button_caption;

		if (!isset($form_action)) {
			$form_action = xoops_getenv('SCRIPT_NAME');
		}

		parent::__construct($form_caption, $form_name, $form_action);

		$this->setExtra('enctype="multipart/form-data"');
		$this->createElements();
		//if ($captcha) $this->addCaptcha();
		$this->createPermissionControls();
		$this->createButtons($form_name, $form_caption, $submit_button_caption);
	}

	/**
	 * @todo to be implented later...
	 */
	/*
	 function addCaptcha() {
		include_once SMARTOBJECT_ROOT_PATH . 'include/captcha/formcaptcha.php' ;
		$this->addElement(new XoopsFormCaptcha(), TRUE);
		}
		*/

	/**
	 * Sets variables for adding custom button
	 *
	 * @param   string  $name       button name
	 * @param   string  $caption    load the config's options now?
	 * @return	bool    $onclick    wheter to add onclick event
	 */
	public function addCustomButton($name, $caption, $onclick = false) {
		$custom_button_array = array(
						'name' => $name,
						'caption' => $caption,
						'onclick' => $onclick
		);
		$this->_custom_button[] = $custom_button_array;
	}

	/**
	 * Add an element to the form
	 *
	 * @param	AbstractFormElement  $formElement   reference
	 * @param	string|false  $key            encrypted key string for the form
	 * @param	string|false  $var            some form variables?
	 * @param	bool|string    $required       is this a "required" element?
	 */
	public function addElement($formElement, $key = false, $var = false, $required = 'notset') {
		if ($key) {
			if ($this->targetObject->getVarInfo($key, 'readonly')) {
				$formElement->setExtra('disabled="disabled"');
				$formElement->setName($key . '-readonly');
				// Since this element is disabled, we still want to pass it's value in the form
				$hidden = new HiddenElement($key, $this->targetObject->getVar($key, 'n'));
				$this->addElement($hidden);
			}
						if (isset($var['form_dsc']) && !empty($var['form_dsc'])) {
													$formElement->setDescription($var['form_dsc']);
						}
			if (isset($this->targetObject->controls[$key]['onSelect'])) {
				$hidden = new HiddenElement('changedField', false);
				$this->addElement($hidden);
				$otherExtra = $var['form_extra'] ?? '';
				$onchangedString = "this.form.elements.changedField.value='$key'; this.form.elements.op.value='changedField'; submit()";
				$formElement->setExtra('onchange="' . $onchangedString . '"' . ' ' . $otherExtra);
			} else {
				if (isset($var['form_extra'])) {
					$formElement->setExtra($var['form_extra']);
				}
			}
			$controls = $this->targetObject->controls;
			if (isset($controls[$key]['js'])) {
				$formElement->customValidationCode[] = $controls[$key]['js'];
			}
			parent::addElement($formElement, $required == 'notset'?$var['required']:$required);
		} else {
			parent::addElement($formElement, $required == 'notset'? false : true);
		}
		unset($formElement);
	}

	/**
	 * Adds an element to the form
	 *
	 * gets all variables from &targetobject (@todo, which object will be passed here?)
	 * that was set during the construction of this object (in the constructor)
	 * loops through all variables and determines what data type the key has
	 * adds an element for each key based on the datatype
	 */
	private function createElements() {
		$controls = $this->targetObject->controls;
		$vars = $this->targetObject->getVars();
		foreach ($vars as $key=>$var) {
			// If $displayOnForm is FALSE OR this is the primary key, it doesn't
			// need to be displayed, then we only create an hidden field
			if ($key == $this->targetObject->handler->keyName || (isset($var['displayOnForm']) && !$var['displayOnForm'])) {
				$val = isset($var['value'])?$var['value']:null;
				$elementToAdd = new HiddenElement($key, $val);
				$this->addElement($elementToAdd, $key, $var, false);
				unset($elementToAdd);
				// If not, the we need to create the proper form control for this fields
			} else {
				// If this field has a specific control, we will use it

				if ($key == 'parentid') {
					/**
					 * Why this ?
					 */
				}
								if (!isset($controls[$key])) {
									$controls[$key] = $this->targetObject->getControl($key);
								}

								if ($controls[$key] === null) {
									$elementToAdd = new HiddenElement($key, $var['value'] ?? null);
									$this->addElement($elementToAdd, $key, $var, false);
									unset($elementToAdd);
									continue;
								}

				//if (isset($controls[$key])) {
					/* If the control has name, it's because it's an object already present in the script
					 * for example, "user"
					 * If the field does not have a name, than we will use a "select" (ie SelectElement)
					 */
					if (!isset($controls[$key]['name']) || !$controls[$key]['name']) {
						$controls[$key]['name'] = 'select';
					}

					$form_select = $this->getControl($controls[$key]['name'], $key);

					// Adding on the form, the control for this field
					$this->addElement($form_select, $key, $var);
					unset($form_select);

					// If this field don't have a specific control, we will use the standard one, depending on its data type
				//} else {

					/*switch ($var['data_type']) {
						case self::DTYPE_DEP_TXTBOX:
							$form_text = $this->getControl("text", $key);
							$this->addElement($form_text, $key, $var);
							unset($form_text);
							break;

						case self::DTYPE_INTEGER:
							$this->targetObject->setControl($key, array('name' => 'text', 'size' => '5'));
							$form_text = $this->getControl("text", $key);
							$this->addElement($form_text, $key, $var);
							unset($form_text);
							break;

						case self::DTYPE_FLOAT:
							$this->targetObject->setControl($key, array('name' => 'text', 'size' => '5'));
							$form_text = $this->getControl("text", $key);
							$this->addElement($form_text, $key, $var);
							unset($form_text);
							break;

						case self::DTYPE_DATETIME:
							$form_datetime = $this->getControl('datetime', $key);
							$this->addElement($form_datetime, $key, $var);
							unset($form_datetime);
							break;

						case self::DTYPE_DEP_STIME:
							$form_date = $this->getControl('date', $key);
							$this->addElement($form_date, $key, $var);
							unset($form_date);
							break;

						case self::DTYPE_DEP_TIME_ONLY:
							$form_time = $this->getControl('time', $key);
							$this->addElement($form_time, $key, $var);
							unset($form_time);
							break;

						case self::DTYPE_DEP_CURRENCY:
							$this->targetObject->setControl($key, array('name' => 'text', 'size' => '15'));
							$form_currency = $this->getControl("text", $key);
							$this->addElement($form_currency, $key, $var);
							unset($form_currency);
							break;

						case self::DTYPE_DEP_URLLINK:
							$form_urllink = $this->getControl("urllink", $key);
							$this->addElement($form_urllink, $key, $var);
							unset($form_urllink);
							break;

						case self::DTYPE_DEP_FILE:
							$form_file = $this->getControl("richfile", $key);
							$this->addElement($form_file, $key, $var);
							unset($form_file);
							break;

						case self::DTYPE_STRING:
							$form_text_area = $this->getControl('textarea', $key);
							$this->addElement($form_text_area, $key, $var);
							unset($form_text_area);
							break;

						case self::DTYPE_ARRAY:
							// TODO : To come...
							break;

						case self::DTYPE_DEP_SOURCE:
							$form_source_editor = $this->getControl('source', $key);
							$this->addElement($form_source_editor, $key, $var);
							unset($form_source_editor);
							break;

						case self::DTYPE_FORM_SECTION:
							$section_control = $this->getControl('section', $key);
							$this->addElement($section_control, $key, $var);
							unset($section_control);
							break;

						case self::DTYPE_FORM_SECTION_CLOSE:
							$this->targetObject->setControl($key, array("close" => TRUE));
							$section_control = $this->getControl('section', $key);
							$this->addElement($section_control, $key, $var);
							unset($section_control);
							break;
					}*/
				//}
			}
		}
		// Add a hidden field to store the URL of the page before this form
		$this->addElement(new HiddenElement('icms_page_before_form', icms_get_page_before_form()));
	}

	/**
	 * Creates Permission Controls
	 */
	private function createPermissionControls() {
		$icmsModuleConfig = $this->targetObject->handler->getModuleConfig();

		$permissions = $this->targetObject->handler->getPermissions();

		if ($permissions) {
			$member_handler = icms::handler('icms_member');
			$group_list = $member_handler->getGroupList();
			asort($group_list);
			foreach ($permissions as $permission) {
				$groups_value = false;
				if ($this->targetObject->isNew()) {
					if (isset($icmsModuleConfig['def_perm_' . $permission['perm_name']])) {
						$groups_value = $icmsModuleConfig['def_perm_' . $permission['perm_name']];
					}
				} else {
					$groups_value = $this->targetObject->getGroupPerm($permission['perm_name']);
				}
				$groups_select = new SelectElement($permission['caption'], $permission['perm_name'], $groups_value, 4, true);
				$groups_select->setDescription($permission['description']);
				$groups_select->addOptionArray($group_list);
				$this->addElement($groups_select);
				unset($groups_select);
			}
		}
	}

	/**
	 * Add an element to the form
	 *
	 * @param	string  $form_name              name of the form
	 * @param	string  $form_caption           caption of the form
	 * @param	string  $submit_button_caption  caption of the button
	 */
	private function createButtons($form_name, $form_caption, $submit_button_caption = false) {
		$button_tray = new TrayElement('', '');
		$button_tray->addElement(new HiddenElement('op', $form_name));
		if (!$submit_button_caption) {
			if ($this->targetObject->isNew()) {
				$butt_create = new ButtonElement('', 'create_button', _CO_ICMS_CREATE, 'submit');
			} else {
				$butt_create = new ButtonElement('', 'modify_button', _CO_ICMS_MODIFY, 'submit');
			}
		} else {
			$butt_create = new ButtonElement('', 'modify_button', $submit_button_caption, 'submit');
		}
		$butt_create->setExtra('onclick="this.form.elements.op.value=\'' . $form_name . '\'"');
		$button_tray->addElement($butt_create);

		//creating custom buttons
		if ($this->_custom_button) {
			foreach ($this->_custom_button as $custom_button) {
				$butt_custom = new ButtonElement('', $custom_button['name'], $custom_button['caption'], 'submit');
				if ($custom_button['onclick']) {
					$butt_custom->setExtra('onclick="' . $custom_button['onclick'] . '"');
				}
				$button_tray->addElement($butt_custom);
				unset($butt_custom);
			}
		}

		// creating the "cancel" button
		$butt_cancel = new ButtonElement('', 'cancel_button', _CO_ICMS_CANCEL, 'button');
		if ($this->_cancel_js_action) {
			$butt_cancel->setExtra('onclick="' . $this->_cancel_js_action . '"');
		} else {
			$butt_cancel->setExtra('onclick="history.go(-1)"');
		}
		$button_tray->addElement($butt_cancel);
		$this->addElement($button_tray);
	}

	/**
	 * Gets a control from the targetobject (@param string $controlName name of the control element
	 * @param string $key key of the form variables in the targetobject
	 * @return ColorPickerElement|DHTMLTextAreaElement|LabelElement|PasswordElement|CountryElement|GroupElement|TimeZoneElement|UserElement|SelectElement|TextAreaElement
	 * @todo, which object will be passed here?)
	 *
	 */
	private function getControl($controlName, $key) {
		switch ($controlName) {
			case 'color':
				$control = $this->targetObject->getControl($key);
				$controlObj = new ColorPickerElement($this->targetObject->getVarInfo($key, 'form_caption'), $key, $this->targetObject->getVar($key));
				return $controlObj;
				break;

			case 'label':
				return new LabelElement($this->targetObject->getVarInfo($key, 'form_caption'), $this->targetObject->getVar($key));
				break;

			case 'textarea' :

				$form_rows = $this->targetObject->controls[$key]['rows'] ?? 5;
				$form_cols = $this->targetObject->controls[$key]['cols'] ?? 60;

				$editor = new TextAreaElement($this->targetObject->getVarInfo($key, 'form_caption'), $key, $this->targetObject->getVar($key, 'e'), $form_rows, $form_cols);
				if ($this->targetObject->getVarInfo($key, 'form_dsc')) {
					$editor->setDescription($this->targetObject->getVarInfo($key, 'form_dsc'));
				}
				return $editor;
				break;

			case 'dhtmltextarea' :
				$editor = new DHTMLTextAreaElement($this->targetObject->getVarInfo($key, 'form_caption'), $key, $this->targetObject->getVar($key, 'e'), 15, 50);
				if ($this->targetObject->getVarInfo($key, 'form_dsc')) {
					$editor->setDescription($this->targetObject->getVarInfo($key, 'form_dsc'));
				}
				return $editor;
				break;

			case 'theme':
				return $this->getThemeSelect($key, $this->targetObject->getVarInfo($key));
				break;

			case 'theme_multi':
				return $this->getThemeSelect($key, $this->targetObject->getVarInfo($key), true);
				break;

			case 'timezone':
				return new TimeZoneElement($this->targetObject->getVarInfo($key, 'form_caption'), $key, $this->targetObject->getVar($key));
				break;

			case 'group':
				return new GroupElement($this->targetObject->getVarInfo($key, 'form_caption'), $key, false, $this->targetObject->getVar($key, 'e'), 1, false);
				break;

			case 'group_multi':
				return new GroupElement($this->targetObject->getVarInfo($key, 'form_caption'), $key, false, $this->targetObject->getVar($key, 'e'), 5, true);
				break;

			case 'user_multi':
				return new UserElement($this->targetObject->getVarInfo($key, 'form_caption'), $key, false, $this->targetObject->getVar($key, 'e'), 5, true);
				break;

			case 'password':
				return new PasswordElement($this->targetObject->getVarInfo($key, 'form_caption'), $key, 50, 255, $this->targetObject->getVar($key, 'e'));
				break;

			case 'country':
				return new CountryElement($this->targetObject->getVarInfo($key, 'form_caption'), $key, $this->targetObject->getVar($key, 'e'));
				break;

			default:
				$classname = "\\ImpressCMS\\Core\\IPF\\Form\\Elements\\" . ucfirst($controlName);
				if (!class_exists($classname)) {
					// perhaps this is a control created by the module
					$moduleName = $this->targetObject->handler->_moduleName;
					$moduleFormElementsPath = $this->targetObject->handler->_modulePath . '/class/form/elements/';
					$classname = ucfirst($moduleName) . ucfirst($controlName) . 'Element';
					$classFileName = strtolower($classname) . '.php';

					if (file_exists($moduleFormElementsPath . $classFileName)) {
						include_once $moduleFormElementsPath . $classFileName;
					} else {
						trigger_error($classname . ' not found', E_USER_WARNING);
						return new LabelElement();
					}
				}
				return new $classname($this->targetObject, $key);
				break;
		}
	}

	/**
	 * Get information for the theme select box
	 *
	 * @param string $key key of the variables in the targetobject
	 * @param string $var key of the variables in the targetobject
	 * @param bool $multiple will you need a form element which shows multiple items
	 * @return SelectElement
	 */
	private function getThemeSelect($key, $var, $multiple = false) {
		$size = $multiple?5:1;
		$theme_select = new SelectElement($var['form_caption'], $key, $this->targetObject->getVar($key), $size, $multiple);

		$theme_select->addOptionArray(
			ThemeFactory::getThemesList()
		);

		return $theme_select;
	}

	/**
	 * Gets reference to the object for each key in the variables of the targetobject
	 *
	 * @param string $keyname name of the key
	 * @return false|AbstractFormElement
	 */
	public function &getElementById($keyname) {
		foreach ($this->_elements as $eleObj) {
			if ((string)$eleObj->getName() === (string)$keyname) {
				$ret = & $eleObj;
				break;
			}
		}
		return $ret ?? false;
	}

	/**
	 * create HTML to output the form as a theme-enabled table with validation.
	 *
	 * @return	string  $ret
	 */
	public function render() {
		$required = & $this->getRequired();
		$ret = "
			<form name='".$this->getName() . "_dorga' id='" . $this->getName() . "' action='" . $this->getAction() . "' method='" . $this->getMethod() . "' onsubmit='return xoopsFormValidate_" . $this->getName() . "(this);'" . $this->getExtra() . ">
			<table width='100%' class='outer table' cellspacing='1'>
			<tr><th colspan='2'>".$this->getTitle() . '</th></tr>
		';
		$hidden = '';
		$class = 'even';
		foreach ($this->getElements() as $ele) {
			if (!is_object($ele)) {
				$ret .= $ele;
			} elseif (!$ele->isHidden()) {
				if ((get_class($ele) === FormSectionElement::class) && !$ele->isClosingSection()) {
					$ret .= '<tr><th colspan="2">' . $ele->render() . '</th></tr>';
				} elseif ((get_class($ele) === FormSectionElement::class) && $ele->isClosingSection()) {
					$ret .= '<tr><td class="even" colspan="2">&nbsp;</td></tr>';
				} else {
					$ret .= "<tr id='" . $ele->getName() . "_row' valign='top' align='" . _GLOBAL_LEFT . "'><td class='head'>" . $ele->getCaption();
					if ($ele->getDescription()) {
						$ret .= '<br /><br /><span style="font-weight: normal;">' . $ele->getDescription() . '</span>';
					}
					$ret .= "</td><td class='$class'>" . $ele->render() . "</td></tr>\n";
				}
			} else {
				$hidden .= $ele->render();
			}
		}
		$ret .= "</table>\n$hidden\n</form>\n";
		$ret .= $this->renderValidationJS(true);
		return $ret;
	}

	/**
	 * assign to smarty form template instead of displaying directly
	 *
	 * @param	Smarty  &$tpl         reference
	 * @param	mixed   $smartyName   if smartyName is passed, assign it to the smarty call else assign the name of the form element
	 */
	public function assign(&$tpl, $smartyName = false) {
		$i = 0;
		$elements = array();
		foreach ($this->getElements() as $ele) {
			$n = $ele->getName()?:$i;
			$elements[$n]['name'] = $ele->getName();
			$elements[$n]['caption'] = $ele->getCaption();
			$elements[$n]['body'] = $ele->render();
			$elements[$n]['hidden'] = $ele->isHidden();
			$elements[$n]['required'] = $ele->isRequired();
			$elements[$n]['section'] = get_class($ele) === FormSectionElement::class && !$ele->isClosingSection();
			$elements[$n]['section_close'] = get_class($ele) === FormSectionElement::class && $ele->isClosingSection();
			$elements[$n]['hide'] = ($i === $n)?false:$this->targetObject->getVarInfo($n, 'hide', false);

			if ($ele->getDescription()) {
				$elements[$n]['description'] = $ele->getDescription();
			}
			$i++;
		}
		$js = $this->renderValidationJS();
		if (!$smartyName) {
			$smartyName = $this->getName();
		}

		$tpl->assign($smartyName, array('title' => $this->getTitle(), 'name' => $this->getName(), 'action' => $this->getAction(), 'method' => $this->getMethod(), 'extra' => 'onsubmit="return xoopsFormValidate_' . $this->getName() . '(this);"' . $this->getExtra(), 'javascript' => $js, 'elements' => $elements));
	}

	/**
	 * create HTML to output the form as a theme-enabled table with validation.
	 *
	 * @param	  bool  $withtags   whether to add script HTML tag to the $js string
	 * @return	bool  $js         the constructed javascript validation string
	 */
	public function renderValidationJS($withtags = true) {
		$js = "";
		if ($withtags) {
			$js .= "\n<!-- Start Form Validation JavaScript //-->\n<script type='text/javascript'>\n<!--//\n";
		}
		$formname = $this->getName();
		$js .= "function xoopsFormValidate_{$formname}(myform) {";
		// First, output code to check required elements
		$elements = $this->getRequired();
		foreach ($elements as $elt) {
			$eltname = $elt->getName();
			$eltcaption = trim($elt->getCaption());
			$eltmsg = empty($eltcaption)? sprintf(_FORM_ENTER, $eltname):sprintf(_FORM_ENTER, $eltcaption);
			$eltmsg = str_replace('"', '\"', stripslashes($eltmsg));
			if (get_class($elt) === RadioElement::class) {
				$js .= 'var myOption = -1;';
				$js .= "for (i=myform.{$eltname}.length-1; i > -1; i--) {
					if (myform.{$eltname}[i].checked) {
						myOption = i; i = -1;
					}
				}
				if (myOption == -1) {
					window.alert(\"{$eltmsg}\"); myform.{$eltname}[0].focus(); return false; }\n";

			/**
			 * @todo remove icmsformselect_multielement in 1.4
			 */
			} elseif (get_class($elt) === SelectMultiElement::class) {
				$js .= 'var hasSelections = FALSE;';
				$js .= "for(var i = 0; i < myform['{$eltname}[]'].length; i++){
					if (myform['{$eltname}[]'].options[i].selected) {
						hasSelections = TRUE;
					}

				}
				if (hasSelections == FALSE) {
					window.alert(\"{$eltmsg}\"); myform['{$eltname}[]'].options[0].focus(); return false; }\n";

			} elseif (get_class($elt) === CheckboxElement::class) {
				$js .= 'var hasSelections = FALSE;';
				//sometimes, there is an implicit '[]', sometimes not
				if (strpos($eltname, '[') === false) {
					$js .= "for(var i = 0; i < myform['{$eltname}[]'].length; i++){
						if (myform['{$eltname}[]'][i].checked) {
							hasSelections = TRUE;
						}

					}
					if (hasSelections == FALSE) {
						window.alert(\"{$eltmsg}\"); myform['{$eltname}[]'][0].focus(); return false; }\n";
				} else {
					$js .= "for(var i = 0; i < myform['{$eltname}'].length; i++){
						if (myform['{$eltname}'][i].checked) {
							hasSelections = TRUE;
						}

					}
					if (hasSelections == FALSE) {
						window.alert(\"{$eltmsg}\"); myform['{$eltname}'][0].focus(); return false; }\n";
				}

			} else {
				$js .= "if ( myform.{$eltname}.value == \"\" ) "
				. "{ window.alert(\"{$eltmsg}\"); myform.{$eltname}.focus(); return false; }\n";
			}
		}
		// Now, handle custom validation code
		$elements = $this->getElements(true);
		foreach ($elements as $elt) {
			if (method_exists($elt, 'renderValidationJS') && get_class($elt) !== CheckboxElement::class) {
				if ($eltjs = $elt->renderValidationJS()) {
					$js .= $eltjs . "\n";
				}
			}
		}
		$js .= "return true;\n}\n";
		if ($withtags) {
			$js .= "//--></script>\n<!-- End Form Vaidation JavaScript //-->\n";
		}
		return $js;
	}

	/**
	 * @param bool $withtags
	 * @todo what should we do with this function?
	 *
	 * @global <type> $xoTheme
	 */
	public function renderValidationJS2($withtags = true) {
		global $xoTheme;
		$rules = $titles = '';
		$elements = $this->getRequired();
		foreach ($elements as $elt) {
			if (!empty($rules)) {
						$rules .= ',';
			}
			$rules .= '\'' . $elt->getName() . '\': { required: TRUE }';
			if (!empty($titles)) {
						$titles .= ',';
			}
			$titles .= $elt->getName() . ': "' . _REQUIRED . '"';
		}
		$xoTheme->addScript('', array('type' => 'text/javascript'), 'alert($());$().ready(function() { $("#' . $this->getName() . '").validate({
		rules: {
			'.$rules . '
		},
		messages: {
			'.$titles . '
		}
		})});');
	}
}