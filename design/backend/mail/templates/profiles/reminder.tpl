{* Modified by tommy from cs-cart.jp 2016 *}

{include file="common/letter_header.tpl"}

{if $smarty.const.CART_LANGUAGE == "ja"}
    {if $user_data.firstname}{$user_data.firstname}{else}{$user_data.user_type|fn_get_user_type_description|lower}{/if}{__("dear")}<br><br>
{else}
    {__("dear")} {if $user_data.firstname}{$user_data.firstname}{else}{$user_data.user_type|fn_get_user_type_description|lower}{/if},<br><br>
{/if}

{__("change_password_notification_body", ["[days]" => $days, "[store]" => $config.http_location])}<br><br>

<a href="{$link|replace:'&amp;':'&'}">{$link|replace:'&amp;':'&'}</a><br><br>

{include file="common/letter_footer.tpl"}