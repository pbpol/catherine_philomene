<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * PM model for Prestashop 1.4
 */
class OgonePM extends ObjectModel
{

    public $pm;

    public $brand;

    public $name;

    public $description;

    public $position;

    public $active;

    public $date_add;

    public $date_upd;


    protected $fieldsRequired = array('pm', 'brand');
    protected $fieldsSize = array('pm' => 128, 'brand' => 128);
    protected $fieldsValidate = array('active' => 'isBool', 'position' => 'isUnsignedInt',
        'date_add' => 'isDate', 'date_upd' => 'isDate');
    protected $table = 'ogone_pm';
    protected $identifier = 'id_ogone_pm';

    public function getTranslationsFieldsChild()
    {
        parent::validateFieldsLang();
        return parent::getTranslationsFields(array('name', 'description'));
    }

    public function getFields()
    {
        parent::validateFields();
        $fields = array();
        if (isset($this->id)) {
            $fields['id_ogone_pm'] = (int)($this->id);
        }
        $fields['pm'] = pSql($this->pm);
        $fields['brand'] = pSql($this->brand);
        $fields['position'] =  (int)($this->position);
        $fields['active'] = (int)($this->active);
        $fields['date_add'] = pSQL($this->date_add);
        $fields['date_upd'] = pSQL($this->date_upd);
        return $fields;
    }


    public static function getAllIds()
    {
        $result = array();
        $query = 'SELECT id_ogone_pm FROM ' . _DB_PREFIX_ . 'ogone_pm';
        foreach (Db::getInstance()->executeS($query, true, false) as $row) {
            $result[] = (int) $row['id_ogone_pm'];
        }

        return $result;
    }
}
