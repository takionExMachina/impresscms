<?php
/**
 * Manage Objects
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		LICENSE.txt
 * @category	ICMS
 * @package		Core
 * @version		SVN: $Id: Object.php 11326 2011-08-25 16:55:48Z phoenyx $
 */

/**#@+
 * Object datatype
 *
 **/
define('XOBJ_DTYPE_TXTBOX', icms_ipf_Properties::DTYPE_DEP_TXTBOX);
define('XOBJ_DTYPE_TXTAREA', icms_ipf_Properties::DTYPE_STRING);
define('XOBJ_DTYPE_INT', icms_ipf_Properties::DTYPE_INTEGER);
define('XOBJ_DTYPE_URL', icms_ipf_Properties::DTYPE_DEP_URL);
define('XOBJ_DTYPE_EMAIL', icms_ipf_Properties::DTYPE_DEP_EMAIL);
define('XOBJ_DTYPE_ARRAY', icms_ipf_Properties::DTYPE_ARRAY);
define('XOBJ_DTYPE_OTHER', icms_ipf_Properties::DTYPE_OTHER);
define('XOBJ_DTYPE_SOURCE', icms_ipf_Properties::DTYPE_DEP_SOURCE);
define('XOBJ_DTYPE_STIME', icms_ipf_Properties::DTYPE_DEP_STIME);
define('XOBJ_DTYPE_MTIME', icms_ipf_Properties::DTYPE_DEP_MTIME);
define('XOBJ_DTYPE_LTIME', icms_ipf_Properties::DTYPE_DATETIME);

define('XOBJ_DTYPE_SIMPLE_ARRAY', icms_ipf_Properties::DTYPE_LIST);
define('XOBJ_DTYPE_CURRENCY', icms_ipf_Properties::DTYPE_DEP_CURRENCY);
define('XOBJ_DTYPE_FLOAT', icms_ipf_Properties::DTYPE_FLOAT);
define('XOBJ_DTYPE_TIME_ONLY', icms_ipf_Properties::DTYPE_DEP_TIME_ONLY);
define('XOBJ_DTYPE_URLLINK', icms_ipf_Properties::DTYPE_DEP_URLLINK);
define('XOBJ_DTYPE_FILE', icms_ipf_Properties::DTYPE_DEP_FILE);
define('XOBJ_DTYPE_IMAGE', icms_ipf_Properties::DTYPE_DEP_IMAGE);
define('XOBJ_DTYPE_FORM_SECTION', icms_ipf_Properties::DTYPE_DEP_FORM_SECTION);
define('XOBJ_DTYPE_FORM_SECTION_CLOSE', icms_ipf_Properties::DTYPE_DEP_FORM_SECTION_CLOSE);
/**#@-*/

/**
 * Base class for all objects in the kernel (and beyond)
 *
 * @category	ICMS
 * @package		Core
 *
 * @author Kazumi Ono (AKA onokazu)
 **/
class icms_core_Object
    extends icms_ipf_Properties {

	/**
	 * is it a newly created object?
	 *
	 * @var bool
	 * @access private
	 */
	private $_isNew = false;

	/**
	 * errors
	 *
	 * @var array
	 * @access private
	 */
	private $_errors = array();

	/**
	 * additional filters registered dynamically by a child class object
	 *
	 * @access private
	 */
	private $_filters = array();

	/**
	 * constructor
	 *
	 * normally, this is called from child classes only
	 * @access public
	 */
	public function __construct() {
	}

	/**#@+
	 * used for new/clone objects
	 *
	 * @access public
	 */
	public function setNew() {
		$this->_isNew = true;
	}
	public function unsetNew() {
		$this->_isNew = false;
	}
	public function isNew() {
		return $this->_isNew;
	}
	/**#@-*/

	/**#@+
	 * mark modified objects as dirty
	 *
	 * used for modified objects only
	 * @access public
	 */
	public function setDirty() {
		$this->setVarInfo(null, parent::VARCFG_CHANGED, true);
	}
	public function unsetDirty() {
		$this->setVarInfo(null, parent::VARCFG_CHANGED, false);
	}
	public function isDirty() {
		return count($this->getChangedVars()) > 0;
	}
	/**#@-*/

	/**
	 * initialize variables for the object
	 *
	 * @access public
	 * @param string $key
	 * @param int $data_type  set to one of XOBJ_DTYPE_XXX constants (set to XOBJ_DTYPE_OTHER if no data type ckecking nor text sanitizing is required)
	 * @param mixed
	 * @param bool $required  require html form input?
	 * @param int $maxlength  for XOBJ_DTYPE_TXTBOX type only
	 * @param string $option  does this data have any select options?
	 */
	public function initVar($key, $data_type, $value = null, $required = false, $maxlength = null, $options = '') {
        parent::initVar($key, $data_type, $value, $required, array(
                                                                     parent::VARCFG_MAX_LENGTH => $maxlength,
                                                                     'options' => $options
                                                             )
                       );
	}

	/**
	 * Assign values to multiple variables in a batch
	 *
	 * Meant for a CGI context:
	 * - prefixed CGI args are considered safe
	 * - avoids polluting of namespace with CGI args
	 *
	 * @access public
	 * @param array $var_arr associative array of values to assign
	 * @param string $pref prefix (only keys starting with the prefix will be set)
	 */
	public function setFormVars($var_arr=null, $pref='xo_', $not_gpc=false) {
		$len = strlen($pref);
		foreach ($var_arr as $key => $value) {
			if ($pref == substr($key, 0, $len)) {
				$this->setVar(substr($key, $len), $value, $not_gpc);
			}
		}
    }

	/**
	 * dynamically register additional filter for the object
	 *
	 * @param string $filtername name of the filter
	 * @access public
	 */
	public function registerFilter($filtername) {
		$this->_filters[] = $filtername;
	}

	/**
	 * load all additional filters that have been registered to the object
	 *
	 * @access private
	 */
	private function _loadFilters() {}
    
    public function __call($name, $arguments) {
        switch ($name) {
            case 'xoopsClone':
                trigger_error('Deprecached method xoopsClone', E_STRICT);
                return clone $this;
        }
        parent::__call($name, $arguments);
    }
    
    /**
     * Create cloned copy of current object
     */
    public function __clone() {        
        $this->setNew();
    }
    
    /**
	 * add an error
	 *
	 * @param string $value error to add
	 * @access public
	 */
	public function setErrors($err_str, $prefix = false) {
		if (is_array($err_str)) {
			foreach ($err_str as $str) {
				$this->setErrors($str, $prefix);
			}
		} else {
			if ($prefix) {
				$err_str = "[" . $prefix . "] " . $err_str;
			}
			$this->_errors[] = trim($err_str);
		}
	}

	/**
	 * return the errors for this object as an array
	 *
	 * @return array an array of errors
	 * @access public
	 */
	public function getErrors() {
		return $this->_errors;
	}

	/**
	 * return the errors for this object as html
	 *
	 * @return string html listing the errors
	 * @access public
	 */
	public function getHtmlErrors() {
		$ret = '<h4>' . _ERROR . '</h4>';
        if (empty($this->_errors)) 
            $ret .= _NONE . '<br />';
        else
            $ret .= implode('<br />', $this->_errors);		
		return $ret;
	}
    
    /**
	 *
	 */
	public function hasError() {
		return count($this->_errors) > 0;
	}
    
}