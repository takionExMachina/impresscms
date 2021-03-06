<?php
/**
 * Contains the basic classe for managing a category object based on \ImpressCMS\Core\IPF\AbstractModel
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since	1.2
 * @author	marcan <marcan@impresscms.org>
 * @author	Sina Asghari (aka stranger) <pesian_stranger@users.sourceforge.net>
 */
namespace ImpressCMS\Core\Models;

/**
 * Provides data access mechanisms to the Category object
 *
 * @copyright 	The ImpressCMS Project http://www.impresscms.org/
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package	ICMS\IPF\Category
 * @since 	1.1
 */
class CategoryHandler extends AbstractExtendedHandler {
	/** */
	public $allCategoriesObj = false;
	/** */
	private $_allCategoriesId = false;

	/**
	 * Constructor for the object handler
	 *
	 * @param object $db A database object
	 * @param string $modulename The directory name for the module
	 * @return CategoryHandler
	 */
	public function __construct($db, $modulename) {
		parent::__construct($db, 'category', 'categoryid', 'name', 'description', $modulename);
	}

	/**
	 * Return all categories in an array
	 *
	 * @param int $parentid
	 * @param string $perm_name
	 * @param string $sort
	 * @param string $order
	 * @return array
	 */
	public function getAllCategoriesArray($parentid = 0, $perm_name = false, $sort = 'parentid', $order = 'ASC') {

		if (!$this->allCategoriesObj) {
			$criteria = new \ImpressCMS\Core\Database\Criteria\CriteriaCompo();
			$criteria->setSort($sort);
			$criteria->setOrder($order);
			$userIsAdmin = is_object(\icms::$user) && \icms::$user->isAdmin();

			if ($perm_name && !$userIsAdmin) {
				if (!$this->setGrantedObjectsCriteria($criteria, $perm_name)) {
					return false;
				}
			}

			$this->allCategoriesObj = & $this->getObjects($criteria, 'parentid');
		}

		$ret = array();
		if (isset($this->allCategoriesObj[$parentid])) {
			foreach ($this->allCategoriesObj[$parentid] as $categoryid=>$categoryObj) {
				$ret[$categoryid]['self'] = & $categoryObj->toArray();
				if (isset($this->allCategoriesObj[$categoryid])) {
					$ret[$categoryid]['sub'] = & $this->getAllCategoriesArray($categoryid);
					$ret[$categoryid]['subcatscount'] = count($ret[$categoryid]['sub']);
				}
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param	int		$parentid
	 * @param	bool	$asString
	 * @return	array|string	array of ids, or if $asString is TRUE a comma-separated string of ids
	 */
	public function getParentIds($parentid, $asString = true) {

		if (!$this->allCategoriesId) {

			$ret = array();
			$sql = 'SELECT categoryid, parentid FROM ' . $this->table
				. ' AS ' . $this->_itemname . ' ORDER BY parentid';

			$result = $this->db->query($sql);

			if (!$result) {
				return $ret;
			}

			while ($myrow = $this->db->fetchArray($result)) {
				$this->allCategoriesId[$myrow['categoryid']] = $myrow['parentid'];
			}
		}

		$retArray = array($parentid);
		while ($parentid != 0) {
			$parentid = $this->allCategoriesId[$parentid];
			if ($parentid != 0) {
				$retArray[] = $parentid;
			}
		}
		if ($asString) {
			return implode(', ', $retArray);
		} else {
			return $retArray;
		}
	}
}

