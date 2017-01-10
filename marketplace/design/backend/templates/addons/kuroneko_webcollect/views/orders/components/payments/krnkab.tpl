{* $Id: krnkab.tpl by tommy from cs-cart.jp 2016 *}
{if $cart.ship_to_another}
    <input type="hidden" name="payment_info[sendDiv]" value=1 />
{else}
    <input type="hidden" name="payment_info[sendDiv]" value=0 />
{/if}
