<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */
class Group extends GroupCore
{
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    public static function getCurrent()
    {
        $context = Context::getContext();
        if ((!isset($context->customer) || !$context->customer->id)
            && isset($context->cookie)
            && isset($context->cookie->pc_group_default)) {
            $id_group = (int) $context->cookie->pc_group_default;
            if ($id_group > 0) {
                return new Group($context->cookie->pc_group_default);
            }
        }
        return parent::getCurrent();
    }
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    public static function getReduction($id_customer = null)
    {
        $context = Context::getContext();
        if (!$id_customer
            && isset($context->cookie)
            && isset($context->cookie->pc_group_default)) {
            $id_group = (int) $context->cookie->pc_group_default;
            if ($id_group > 0) {
                return Group::getReductionByIdGroup($context->cookie->pc_group_default);
            }
        }
        return parent::getReduction($id_customer);
    }
}
