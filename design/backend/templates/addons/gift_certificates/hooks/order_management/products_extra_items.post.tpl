{* odified to fix bug #006334 by tommy from cs-cart.jp 2016 *}
{* See : http://forum.cs-cart.com/tracker/issue-6334-give-gift-certificate-promotion/?verfilter=127 *}

{if $cart.gift_certificates}
    {foreach from=$cart.gift_certificates item="certificate"}
        <tr>
            <td>&nbsp;</td>
            <td>
                {__("gift_certificate")}: <a href="{"gift_certificates.update?gift_cert_id=`$certificate.gift_cert_id`"|fn_url}">{$certificate.gift_cert_code}</a>
            </td>
            <td>&nbsp;</td>
            <td colspan="3">&nbsp;
                {include file="common/price.tpl" value=$certificate.display_subtotal}
            </td>
        </tr>
    {/foreach}
{/if}