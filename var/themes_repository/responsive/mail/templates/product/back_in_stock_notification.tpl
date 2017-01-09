{* Modified by tommy from cs-cart.jp 2016 *}

{include file="common/letter_header.tpl"}

{if $smarty.const.CART_LANGUAGE == "ja"}
    {__("customer")}<br /><br />
{else}
    {__("dear")} {__("customer")},<br /><br />
{/if}

{__("back_in_stock_notification_header")}<br /><br />
{assign var="suffix" value=""}
{if "ULTIMATE"|fn_allowed_for}
    {assign var="suffix" value="&company_id=`$product.company_id`"}
{/if}

<b><a href="{"products.view?product_id=`$product_id``$suffix`"|fn_url:'C':'http'}">{$product.name nofilter}</a></b><br /><br />

{__("back_in_stock_notification_footer")}<br />

{include file="common/letter_footer.tpl"}