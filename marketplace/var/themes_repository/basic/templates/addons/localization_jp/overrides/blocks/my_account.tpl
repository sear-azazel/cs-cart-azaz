{* Modified by tommy from cs-cart.jp 2016 *}
{** block-description:my_account **}

{capture name="title"}
    {if $smarty.const.CART_LANGUAGE == 'ja'}
        {if $auth.user_id}
            {if $user_info.firstname || $user_info.lastname}
                <i class="icon-user"></i><a href="{"profiles.update"|fn_url}">{__("jp_welcome")}{$user_info.firstname} {$user_info.lastname}{__("jp_dear_casual")}</a><i class="icon-down-micro"></i>
            {else}
            {/if}
        {else}
            <i class="icon-user"></i><a href="{"profiles.update"|fn_url}">{__("jp_login_or_register")}</a><i class="icon-down-micro"></i>
        {/if}
    {else}
        <i class="icon-user"></i><a href="{"profiles.update"|fn_url}" {live_edit name="block:name:{$block.block_id}"}>{$title}</a><i class="icon-down-micro"></i>
    {/if}
{/capture}

<div id="account_info_{$block.snapping_id}">
    {assign var="return_current_url" value=$config.current_url|escape:url}
    <ul class="account-info">
    {hook name="profiles:my_account_menu"}
        {if $auth.user_id}
            {if $user_info.firstname || $user_info.lastname}
                {if $smarty.const.CART_LANGUAGE == 'ja'}
                    <li class="user-name">{$user_info.firstname} {$user_info.lastname} {__("dear")}</li>
                {else}
                    <li class="user-name">{$user_info.firstname} {$user_info.lastname}</li>
                {/if}
            {else}
                <li class="user-name">{$user_info.email}</li>
            {/if}
            <li><a href="{"profiles.update"|fn_url}" rel="nofollow" class="underlined">{__("profile_details")}</a></li>
            {if $settings.General.enable_edp == "Y"}
            <li><a href="{"orders.downloads"|fn_url}" rel="nofollow" class="underlined">{__("downloads")}</a></li>
            {/if}
        {elseif $user_data.firstname || $user_data.lastname}
            <li class="user-name">{$user_data.firstname} {$user_data.lastname}</li>
        {elseif $user_data.email}
            <li class="user-name">{$user_data.email}</li>
        {/if}
        <li><a href="{"orders.search"|fn_url}" rel="nofollow" class="underlined">{__("orders")}</a></li>
        {assign var="compared_products" value=""|fn_get_comparison_products}
        <li><a href="{"product_features.compare"|fn_url}" rel="nofollow" class="underlined">{__("view_comparison_list")}{if $compared_products} ({$compared_products|count}){/if}</a></li>
        {if $auth.user_id|fn_lcjp_get_payquick_info}
            {if $auth.user_id && $auth.user_id > 0}
                <li><a class="ty-account-info__a" href="{"remise_card_info.view"|fn_url}" rel="nofollow" class="underlined">{__("jp_remise_payquick_registered_card")}</a></li>
            {/if}
        {/if}
    {/hook}

    {if "MULTIVENDOR"|fn_allowed_for && $settings.Vendors.apply_for_vendor == "Y" && !$user_info.company_id}
        <li><a href="{"companies.apply_for_vendor?return_previous_url=`$return_current_url`"|fn_url}" rel="nofollow" class="underlined">{__("apply_for_vendor_account")}</a></li>
    {/if}
    </ul>

    {if $settings.Appearance.display_track_orders == 'Y'}
    <div class="updates-wrapper track-orders" id="track_orders_block_{$block.snapping_id}">

    <form action="{""|fn_url}" method="get" class="cm-ajax cm-ajax-full-render" name="track_order_quick">
    <input type="hidden" name="result_ids" value="track_orders_block_*" />
    <input type="hidden" name="return_url" value="{$smarty.request.return_url|default:$config.current_url}" />

    <p class="text-track">{__("track_my_order")}</p>

    <div class="control-group input-append">
    <label for="track_order_item{$block.snapping_id}" class="cm-required hidden">{__("track_my_order")}</label>
            <input type="text" size="20" class="input-text cm-hint" id="track_order_item{$block.snapping_id}" name="track_data" value="{__("order_id")}{if !$auth.user_id}/{__("email")}{/if}" />{include file="buttons/go.tpl" but_name="orders.track_request" alt=__("go")}
            {include file="common/image_verification.tpl" option="track_orders" align="left" sidebox=true}
    </div>

    </form>

    <!--track_orders_block_{$block.snapping_id}--></div>
    {/if}

    {hook name="profiles:jp_auth_links"}
    <div class="buttons-container">
        {if $auth.user_id}
            <a href="{"auth.logout?redirect_url=`$return_current_url`"|fn_url}" rel="nofollow" class="account">{__("sign_out")}</a>
        {else}
            <a href="{if $runtime.controller == "auth" && $runtime.mode == "login_form"}{$config.current_url|fn_url}{else}{"auth.login_form?return_url=`$return_current_url`"|fn_url}{/if}" {if $settings.Security.secure_storefront != "partial"} data-ca-target-id="login_block{$block.snapping_id}" class="cm-dialog-opener cm-dialog-auto-size account"{else}class="account"{/if} rel="nofollow">{__("sign_in")}</a> | <a href="{"profiles.add"|fn_url}" rel="nofollow" class="account">{__("register")}</a>
            {if $settings.Security.secure_storefront != "partial"}
                <div  id="login_block{$block.snapping_id}" class="hidden" title="{__("sign_in")}">
                    <div class="login-popup">
                        {include file="views/auth/login_form.tpl" style="popup" id="popup`$block.snapping_id`"}
                    </div>
                </div>
            {/if}
        {/if}
    </div>
    {/hook}
<!--account_info_{$block.snapping_id}--></div>
