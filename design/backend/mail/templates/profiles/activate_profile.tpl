{* Modified by tommy from cs-cart.jp 2016 *}

{include file="common/letter_header.tpl"}

{if $smarty.const.CART_LANGUAGE == "ja"}
    {assign var="_url" value="profiles.update?user_id=`$user_data.user_id`"|fn_url:'A':'http':$smarty.const.CART_LANGUAGE:true}
{else}
    {__("hello")},<br /><br />{assign var="_url" value="profiles.update?user_id=`$user_data.user_id`"|fn_url:'A':'http':$smarty.const.CART_LANGUAGE:true}
{/if}
{assign var="user_login" value=$user_data.email}
{__("text_new_user_activation", ["[user_login]" => $user_login, "[url]" => "<a href=\"`$_url`\">`$_url`</a>"])}

{include file="common/letter_footer.tpl" user_type='A'}