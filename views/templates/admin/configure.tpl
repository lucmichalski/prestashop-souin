{*
* 2007-2021 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
	<h3><i class="icon icon-info-sign"></i> {l s='Souin - Cache' mod='souin'}</h3>
	<p>
		<strong>{l s='Here is the configuration related to Souin!' mod='souin'}</strong>
	</p>
	<p>
		{l s='This module will boost your performances!' mod='souin'}
	</p>
</div>

<div class="panel">
	<h3><i class="icon icon-cogs"></i> {l s='Settings' mod='souin'}</h3>
	<form method="post">
		{$souinConfiguration}
		<button class="button btn btn-success" name="submitSouinModule" type="submit">Submit</button>
	</form>
</div>
