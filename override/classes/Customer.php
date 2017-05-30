<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */
class Customer extends CustomerCore
{
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    public static function getDefaultGroupId($id_customer)
    {
        $context = Context::getContext();
        if (!$id_customer
            && isset($context->cookie)
            && isset($context->cookie->pc_group_default)) {
            $id_group = (int) $context->cookie->pc_group_default;
            if ($id_group > 0) {
                return $context->cookie->pc_group_default;
            }
        }
        return parent::getDefaultGroupId($id_customer);
    }
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    public static function getGroupsStatic($id_customer)
    {
        $context = Context::getContext();
        if (!$id_customer
            && isset($context->cookie)
            && isset($context->cookie->pc_groups)) {
            $groups = explode(',', $context->cookie->pc_groups);
            if ($groups !== false && count($groups) > 0) {
                return $groups;
            }
        }
        return parent::getGroupsStatic($id_customer);
    }
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    public function isLogged($with_guest = false)
    {
        $context = Context::getContext();
        if (strcmp($this->getCallerMethod(),'getHookModuleExecList') === 0) {
            if ((!isset($context->customer) || !$context->customer->id)
                && isset($context->cookie)
                && isset($context->cookie->pc_is_logged)) {
                if ($with_guest) {
                    return $context->cookie->pc_is_logged;
                } else {
                    return $context->cookie->pc_is_logged_guest;
                }
            }
        }
        return parent::isLogged($with_guest);
    }
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    private function getCallerMethod()
    {
        $traces = debug_backtrace();
        if (isset($traces[2])) {
            return $traces[2]['function'];
        }
        return null;
    }
}
