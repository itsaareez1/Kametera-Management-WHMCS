<div class="categories-collapsed visible-xs visible-sm clearfix">
    
    {foreach $secondarySidebar as $panel}
        <div class="panel card{if $panel->getClass()}{$panel->getClass()}{else} panel-default{/if}">
            {include file="orderforms/standard_cart/sidebar-categories-selector.tpl"}
        </div>
    {/foreach}
    
    {if !$loggedin && $currencies}
        <div class="pull-right form-inline">
            <form method="post" action="{$WEB_ROOT}/cart.php{if $action}?a={$action}{elseif $gid}?gid={$gid}{/if}">
                <select name="currency" onchange="submit()" class="form-control">
                    <option value="">{$LANG.choosecurrency}</option>
                    {foreach from=$currencies item=listcurr}
                        <option value="{$listcurr.id}"{if $listcurr.id == $currency.id} selected{/if}>{$listcurr.code}</option>
                    {/foreach}
                </select>
            </form>
        </div>
    {/if}
</div>
