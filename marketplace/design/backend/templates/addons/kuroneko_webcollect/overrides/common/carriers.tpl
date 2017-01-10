{* Modified by tommy from cs-cart.jp 2016 *}
{* 「配送手続きの追加」ポップアップにおいて、運送会社のデフォルト表示を「ヤマト運輸」に変更 *}
{if $capture}
{capture name="carrier_field"}
{/if}

<select {if $id}id="{$id}"{/if} name="{$name}" {if $meta}class="{$meta}"{/if}>
    {if $settings.General.use_shipments == "Y"}
        {if $name == "shipment_data[carrier]"}
        <option value="yamato" {if $carrier == "yamato"}{$carrier_name = __("carrier_yamato")}selected="selected"{/if}>{__("carrier_yamato")}</option>
        {else}
        <option value="">--</option>
        {/if}
    {else}
        {if $name == "update_shipping[{$shipping.group_key}][{$shipment_id}][carrier]"}
            <option value="yamato" {if $carrier == "yamato"}{$carrier_name = __("carrier_yamato")}selected="selected"{/if}>{__("carrier_yamato")}</option>
        {else}
            <option value="">--</option>
        {/if}
    {/if}
    {hook name="carriers:list"}
    {foreach from=$carriers item="code"}
        {if $name != "shipment_data[carrier]" || $code != 'yamato'}
    	<option value="{$code}" {if $carrier == "{$code}"}{$carrier_name = __("carrier_`$code`")}selected="selected"{/if}>{__("carrier_`$code`")}</option>
        {/if}
    {/foreach}
    {/hook}
</select>
{if $capture}
{/capture}

{capture name="carrier_name"}
{$carrier_name}
{/capture}
{/if}