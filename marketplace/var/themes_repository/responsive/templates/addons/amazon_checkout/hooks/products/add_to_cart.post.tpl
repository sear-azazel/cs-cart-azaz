{* add_to_cart.post.tpl by tommy from cs-cart.jp 2016 *}

{if $details_page}
    {include file="addons/amazon_checkout/buttons/pay_with_amazon_button.tpl" style="inline" obj_id=$obj_id obj_prefix=$obj_prefix}
{/if}