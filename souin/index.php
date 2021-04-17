<?php
/**
 * Souin Cache powered by Luc Michalski
 *
 *    @author    Luc Michalski
 *    @copyright 2017 Evolutive Group
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 *    @link      https://addons.prestashop.com/en/contact-us?id_product=26866
 */

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Location: ../");
exit;
