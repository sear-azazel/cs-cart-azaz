{* Modified by tommy from cs-cart.jp 2016 *}
<select name="{$name}" {if $id}id="{$id}"{/if}>
<option value="C" {if $value == "C"}selected="selected"{/if}>{__("comma")}</option>
<option value="S" {if $value == "S"}selected="selected"{/if}>{__("semicolon")}</option>
<option value="T" {if $value == "T"}selected="selected"{/if}>{__("tab")}</option>
</select>