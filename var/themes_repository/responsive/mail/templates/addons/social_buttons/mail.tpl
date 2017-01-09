{* Modified by tommy from cs-cart.jp 2016 *}
{include file="common/letter_header.tpl"}

{if $smarty.const.CART_LANGUAGE == "ja"}
{$send_data.to_name}{__("dear")}<br /><br />
{else}
{__("hello")} {$send_data.to_name},<br /><br />
{/if}

{__("text_recommendation_notes")}<br />
<a href="{$link}">{$link}</a><br /><br />
<b>{__("notes")}:</b><br />
{$send_data.notes|replace:"\n":"<br />" nofilter}

{include file="common/letter_footer.tpl"}
