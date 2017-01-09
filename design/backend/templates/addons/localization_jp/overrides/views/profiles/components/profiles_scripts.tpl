{* Modified by tommy from cs-cart.jp 2016 and special thanks to mmochi from andplus.co.jp *}
<script type="text/javascript">
(function(_, $) {

    /* Do not put this code to document.ready, because it should be
       initialized first
    */
    $.ceRebuildStates('init', {
        default_country: '{$settings.General.default_country|escape:javascript}',
        states: {$states|json_encode nofilter}
    });


    {literal}
    $.ceFormValidator('setZipcode', {
        US: {
            regexp: /^(\d{5})(-\d{4})?$/,
            format: '01342 (01342-5678)'
        },
        CA: {
            regexp: /^(\w{3} ?\w{3})$/,
            format: 'K1A OB1 (K1AOB1)'
        },
        RU: {
            regexp: /^(\d{6})?$/,
            format: '123456'
        },
        JP: {
            regexp: /(^\d{3}\-\d{4}$)|(^\d{7}$)/,
            format: '123-4567 or 1234567'
        }
    });
    {/literal}

}(Tygh, Tygh.$));
</script>
