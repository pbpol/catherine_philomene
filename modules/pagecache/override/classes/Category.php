<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */

class Category extends CategoryCore
{

    public function checkAccess($id_customer)
    {
        $context = Context::getContext();
        if (!$id_customer
            && isset($context->cookie)
            && isset($context->cookie->pc_groups)) {

            $groups = explode(',', $context->cookie->pc_groups);
            if ($groups !== false && count($groups) > 0) {
                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
                    SELECT ctg.`id_group`
                    FROM '._DB_PREFIX_.'category_group ctg
                    WHERE ctg.`id_category` = '.(int)$this->id . ' AND ctg.`id_group` IN(' . implode(',', $groups) . ')'
                );
                if ($result && isset($result['id_group']) && $result['id_group'])
                    return true;
                return false;
            }
        }
        return parent::checkAccess($id_customer);
    }
}
