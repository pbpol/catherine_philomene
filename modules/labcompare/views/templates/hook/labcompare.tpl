{*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- MODULE st compare -->
<section id="rightbar_compare" class="rightbar_wrap">
    <a id="rightbar-product_compare" class="rightbar_tri icon_wrap" href="{$link->getPageLink('products-comparison')|escape:'html'}" title="{l s="Compare Products" mod='labcompare'}">
        <i class="icon-ajust icon-0x"></i>
        <span class="icon_text">{l s='Compare' mod='labcompare'}</span>
        <span class="compare_quantity amount_circle {if !isset($compare_nbr) || !$compare_nbr} hidden {/if}{if (isset($compare_nbr) && $compare_nbr > 9)} dozens {/if}">{if isset($compare_nbr) && $compare_nbr}{$compare_nbr}{/if}</span>
    </a>
</section>
<!-- /MODULE st compare -->