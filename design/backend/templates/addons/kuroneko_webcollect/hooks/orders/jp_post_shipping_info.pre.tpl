{* jp_post_shipping_info.pre.tpl by tommy from cs-cart.jp 2016 *}
{if $order_info.pay_by_kuroneko == 'Y'}
<div class="control-group">
    <label class="control-label" for="carrier_key">{if $order_info.pay_by_kuroneko_atobarai == 'Y'}{__("jp_kuroneko_webcollect_ab_send_payment_no")}{else}{__("jp_kuroneko_webcollect_send_slip_no")}{/if}</label>
    <div class="controls">
        {if $shipments[$shipping.group_key].tracking_number}
            {assign var="krnk_send_shipment" value="N"}
        {else}
            {assign var="krnk_send_shipment" value="Y"}
        {/if}
        <input type="checkbox" name="update_shipping[{$shipping.group_key}][{$shipment_id}][send_slip_no]" id="krnkwc_send_slip_no" value="Y"{if $krnk_send_shipment == 'Y'} checked{/if} />
    </div>
</div>
{/if}