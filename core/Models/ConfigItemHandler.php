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

/**
 * Manage configuration items
 *
 * @copyright	Copyright (c) 2000 XOOPS.org
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since        XOOPS
 * @author       Kazumi Ono (aka onokazo)
 * @author       http://www.xoops.org The XOOPS Project
 */

namespace ImpressCMS\Core\Models;

/**
 * Configuration handler class.
 *
 * This class is responsible for providing data access mechanisms to the data source
 * of configuration class objects.
 *
 * @author      Kazumi Ono <onokazu@xoops.org>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 * @package	ICMS\Config\Item
 */
class ConfigItemHandler extends AbstractExtendedHandler {

	public function __construct(&$db) {
			parent::__construct($db, 'config_item', 'conf_id', 'conf_name', 'conf_value', 'icms', 'config', 'conf_id');
		}

	/**
	 * This event is executed when saving
	 *
	 * @param icms_config_item_Object $obj        Saving object
	 *
	 * @return  boolean
	 */
	protected function afterSave(&$obj) {
		\icms::getInstance()->get('cache')->clear();

		return true;
	}
}
