{* $Id: krnkab.tpl by tommy from cs-cart.jp 2016 *}

<p>{__("jp_kuroneko_webcollect_ab_notice")}</p>
<hr />
{include file="common/subheader.tpl" title=__("jp_kuroneko_webcollect_connections_settings") target="#krnkab_connection_settings"}
<div id="krnkab_connection_settings" class="in collapse">
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="mode">{__("test_live_mode")}:</label>
            <div class="controls">
                <select name="payment_data[processor_params][mode]" id="mode">
                    <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
                    <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
                </select>
            </div>
        </div>
    </fieldset>
</div>

{include file="common/subheader.tpl" title=__('jp_kuroneko_webcollect_ab_payment_settings') target="#krnkab_payment_settings"}

<div id="krnkab_payment_settings" class="in collapse">
    <div class="control-group">
        <label class="control-label" for="ship_after">{__('jp_kuroneko_webcollect_ab_ship_ymd')}:</label>
        <div class="controls">
            {__('jp_kuroneko_webcollect_ab_ship_ymd_prefix')}<input type="text" name="payment_data[processor_params][ship_after]" id="ship_after" value="{$processor_params.ship_after}" class="input-text-short" size="3" />{__('jp_kuroneko_webcollect_ab_ship_ymd_suffix')}
        </div>
    </div>
</div>

{include file="common/subheader.tpl" title=__('jp_kuroneko_webcollect_ab_cron_setting') target="#krnkab_cron_settings"}
<div id="krnkab_cron_settings" class="in collapse">
    <div class="control-group">{'ab'|fn_krnkwc_get_cron_command}</div>
</div>

