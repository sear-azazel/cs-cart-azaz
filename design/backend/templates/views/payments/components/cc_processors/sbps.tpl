{* $Id: sbps.tpl by tommy from cs-cart.jp 2013 *}

<p>{__("jp_sbps_notice")}</p>
<hr />

<div class="control-group">
	<label class="control-label" for="shop_id">{__("jp_sbps_merchant_id")}:</label>
    <div class="controls">
	    <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}" size="20" />
    </div>
</div>

<div class="control-group">
	<label class="control-label" for="shop_password">{__("jp_sbps_service_id")}:</label>
    <div class="controls">
	    <input type="text" name="payment_data[processor_params][service_id]" id="service_id" value="{$processor_params.service_id}" size="20" />
    </div>
</div>

<div class="control-group">
	<label class="control-label" for="shop_password">{__("jp_sbps_hashkey")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][hashkey]" id="hashkey" value="{$processor_params.hashkey}" size="45" />
    </div>
</div>

<div class="control-group">
	<label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="connection_support" {if $processor_params.mode == "connection_support"}selected="selected"{/if}>{__("jp_sbps_connection_support")}</option>
            <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
	<label class="control-label" for="url_test">{__("jp_sbps_url_connection_support")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][url_connection_support]" id="url_connection_support" value="{$processor_params.url_connection_support}" class="input-text-large main-input" />
    </div>
</div>

<div class="control-group">
	<label class="control-label" for="url_test">{__("jp_sbps_url_test")}:</label>
    <div class="controls">
	    <input type="text" name="payment_data[processor_params][url_test]" id="url_test" value="{$processor_params.url_test}" />
    </div>
</div>

<div class="control-group">
	<label class="control-label" for="url_production">{__("jp_sbps_url_production")}:</label>
    <div class="controls">
	    <input type="text" name="payment_data[processor_params][url_production]" id="url_production" value="{$processor_params.url_production}" />
    </div>
</div>
