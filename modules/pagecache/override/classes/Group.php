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

    public static function getCurrent()
    {
        $context = Context::getContext();

        if ((!isset($context->customer) || !$context->customer->id)
            && isset($context->cookie)
            && isset($context->cookie->pc_group_default)) {
            $id_group = (int) $context->cookie->pc_group_default;
            if ($id_group > 0) {
                //Logger::addLog("PageCache | new Group(".$context->cookie->pc_group_default.")", 1, null, null, null, true);
                return new Group($context->cookie->pc_group_default);
            }
        }
        //Logger::addLog("PageCache | parent::getCurrent()", 1, null, null, null, true);
        return parent::getCurrent();
    }

    public static function getReduction($id_customer = null)
    {
        $context = Context::getContext();

        if (!$id_customer
            && isset($context->cookie)
            && isset($context->cookie->pc_group_default)) {
            $id_group = (int) $context->cookie->pc_group_default;
            if ($id_group > 0) {
                //Logger::addLog("PageCache | Group::getReductionByIdGroup(".$context->cookie->pc_group_default.")", 1, null, null, null, true);
                return Group::getReductionByIdGroup($context->cookie->pc_group_default);
            }
        }
        //Logger::addLog("PageCache | parent::getReduction(".$id_customer.")", 1, null, null, null, true);
        return parent::getReduction($id_customer);
    }
}
