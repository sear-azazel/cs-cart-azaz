{* Modified by tommy from cs-cart.jp 2016 *}
{* Modified to fix bug #006248 by tommy from cs-cart.jp 2016 *}
{* See : http://forum.cs-cart.com/tracker/issue-6248-custom-template-issues-with-gift-certificates-add-on/?verfilter=123 *}

{include file="common/letter_header.tpl"}

{if $smarty.const.CART_LANGUAGE == "ja"}
    {$gift_cert_data.recipient}{__("dear")}<br /><br />
{else}
    {__("dear")} {$gift_cert_data.recipient},<br /><br />
{/if}

{$certificate_status.email_header nofilter}<br /><br />

{include file="addons/gift_certificates/templates/`$gift_cert_data.template`"}
    
{include file="common/letter_footer.tpl"}