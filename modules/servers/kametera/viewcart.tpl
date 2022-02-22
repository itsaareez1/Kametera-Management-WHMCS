{if $checkout}
{include file="orderforms/$carttpl/checkout.tpl"}
{else}
<script>
// Define state tab index value
var statesTab = 10;
var stateNotRequired = true;
</script>
{include file="orderforms/horn/common.tpl"}
<script type="text/javascript" src="{$BASE_PATH_JS}/StatesDropdown.js"></script>
<div id="order-standard_cart">
    <div class="row">
        <div class="col-md-12">
            <div class="header-lined">
                <h1>{$LANG.cartreviewcheckout}</h1>
                <div class="dropnav-header-lined">
                    <button id="dropside-content" type="button" class="drop-down-btn dropside-content" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="ico-more-vertical d-block f-20"></span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropside-content">
                        {include file="orderforms/horn/sidebar-categories.tpl"}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            {include file="orderforms/standard_cart/sidebar-categories-collapsed.tpl"}
            <div class="row">
                <div class="col-md-8">
                    {if $promoerrormessage}
                    <div class="alert alert-warning text-center" role="alert">
                        {$promoerrormessage}
                    </div>
                    {elseif $errormessage}
                    <div class="alert alert-danger" role="alert">
                        <p>{$LANG.orderForm.correctErrors}:</p>
                        <ul>
                            {$errormessage}
                        </ul>
                    </div>
                    {elseif $promotioncode && $rawdiscount eq "0.00"}
                    <div class="alert alert-info text-center" role="alert">
                        {$LANG.promoappliedbutnodiscount}
                    </div>
                    {elseif $promoaddedsuccess}
                    <div class="alert alert-success text-center" role="alert">
                        {$LANG.orderForm.promotionAccepted}
                    </div>
                    {/if}
                    {if $bundlewarnings}
                    <div class="alert alert-warning" role="alert">
                        <strong>{$LANG.bundlereqsnotmet}</strong><br />
                        <ul>
                            {foreach from=$bundlewarnings item=warning}
                            <li>{$warning}</li>
                            {/foreach}
                        </ul>
                    </div>
                    {/if}
                    <form method="post" action="{$WEB_ROOT}/cart.php?a=view">
                        
                        <div class="view-cart-items">
                            {foreach $products as $num => $product}
                            <div class="item">
                                <div class="item-header">
                                    <a class="badge feat bg-danger mr-30" data-toggle="tooltip" data-placement="left" title="{$LANG.orderForm.remove}" type="button" class="btn-remove-from-cart" onclick="removeItem('p','{$num}')"><i class="ico-trash-2 f-16"></i></a>
                                    
                                    <a class="badge feat bg-defaul mr-65" data-toggle="tooltip" data-placement="left" title="{$LANG.orderForm.edit}" href="{$WEB_ROOT}/cart.php?a=confproduct&i={$num}" class="btn-edit-item-cart"><i class="ico-tool f-16"></i></a>
                                    <div class="row">
                                        <div class="{if $showqtyoptions}col-sm-12 mb-15 {else}col-md-12 mb-15{/if}">
                                            <span class="item-title">
                                                {$product.productinfo.name}
                                            </span>
                                            <span class="item-group">
                                                {$product.productinfo.groupname}
                                            </span>
                                        </div>
                                        <div class="col-sm-5 item-price">
                                            <span>{$product.pricing.totalTodayExcludingTaxSetup}</span>
                                            <span class="cycle">{$product.billingcyclefriendly}</span>
                                            {if $product.pricing.productonlysetup}
                                            {$product.pricing.productonlysetup->toPrefixed()} {$LANG.ordersetupfee}
                                            {/if}
                                            {if $product.proratadate}<br />({$LANG.orderprorata} {$product.proratadate}){/if}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-footer">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            {if $product.domain}
                                            <span>{$LANG.yourdomains}:</span>
                                            <span class="item-domain">
                                                {$product.domain}
                                            </span>
                                            {/if}
                                            {if $product.configoptions}
                                            <small>
                                            {foreach key=confnum item=configoption from=$product.configoptions}
                                            &nbsp;&raquo; {$configoption.name}: {if $configoption.type eq 1 || $configoption.type eq 2}{$configoption.option}{elseif $configoption.type eq 3}{if $configoption.qty}{$configoption.option}{else}{$LANG.no}{/if}{elseif $configoption.type eq 4}{$configoption.qty} x {$configoption.option}{/if}<br />
                                            {/foreach}
                                            </small>
                                            {/if}
                                        </div>
                                        {if $showqtyoptions}
                                        <div class="col-md-6 col-sm-6 col-xs-12 item-qty quantity">
                                            {if $product.allowqty}
                                            <div class="btn-group float-right">
                                                <input type="number" name="qty[{$num}]" value="{$product.qty}" class="form-control text-center" min="0"/>
                                                <button type="submit" class="btn btn-prussian btn-xs btn-qty">
                                                {$LANG.orderForm.update}
                                                </button>
                                            </div>
                                            {/if}
                                        </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                            
                            {foreach $product.addons as $addonnum => $addon}
                            <div class="item">
                                <div class="item-header">
                                    <div class="row">
                                        <div class="{if $showAddonQtyOptions}col-md-12{else}col-sm-7{/if} mb-15">
                                            <span class="item-title">
                                                {$addon.name}
                                            </span>
                                            <span class="item-group">
                                                {$LANG.orderaddon}
                                            </span>
                                            {if $addon.setup}
                                                <span class="item-setup">
                                                    {$addon.setup} {$LANG.ordersetupfee}
                                                </span>
                                            {/if}
                                            {if $addon.setup}
                                            <span class="item-setup">
                                                {$addon.setup} {$LANG.ordersetupfee}
                                            </span>
                                            {/if}
                                        </div>
                                        {if $showAddonQtyOptions}
                                            <div class="col-sm-2 item-qty">
                                                {if $addon.allowqty === 2}
                                                    <input type="number" name="paddonqty[{$num}][{$addonnum}]" value="{$addon.qty}" class="form-control text-center" min="0" />
                                                    <button type="submit" class="btn btn-xs">
                                                        {$LANG.orderForm.update}
                                                    </button>
                                                {/if}
                                            </div>
                                        {/if}
                                        <div class="col-sm-4 item-price">
                                            <span>{$addon.totaltoday}</span>
                                            <span class="cycle">{$addon.billingcyclefriendly}</span>
                                            {if $addon.setup}{$addon.setup->toPrefixed()} {$LANG.ordersetupfee}{/if}
                                            {if $addon.isProrated}<br />({$LANG.orderprorata} {$addon.prorataDate}){/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {/foreach}
                            {/foreach}
                            
                            {foreach $addons as $num => $addon}
                            <div class="item">
                                <div class="item-header">
                                    <a class="badge feat bg-danger mr-30" data-toggle="tooltip" data-placement="left" title="{$LANG.orderForm.remove}" type="button" class="btn-remove-from-cart" onclick="removeItem('a','{$num}')"><i class="ico-trash-2 f-16"></i></a>
                                    <div class="row">
                                        <div class="col-md-12 mb-15">
                                            <div class="{if $showAddonQtyOptions}col-sm-5{else}{/if}">
                                                <span class="item-title">
                                                    {$addon.name}
                                                </span>
                                                <span class="item-group">
                                                    {$addon.productname}
                                                </span>
                                                {if $addon.setup}
                                                <span class="item-setup">
                                                    {$addon.setup} {$LANG.ordersetupfee}
                                                </span>
                                                {/if}
                                            </div>
                                        </div>
                                        <div class="col-sm-4 item-price">
                                            <span>{$addon.totaltoday}</span>
                                            <span class="cycle">{$addon.billingcyclefriendly}</span>
                                            {if $addon.setup}{$addon.setup->toPrefixed()} {$LANG.ordersetupfee}{/if}
                                            {if $addon.isProrated}<br />({$LANG.orderprorata} {$addon.prorataDate}){/if}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-footer">
                                    <div class="row">
                                        <div class="col-md-6"
                                            {if $addon.domainname}
                                            <span>{$LANG.yourdomains}:</span>
                                            <span class="item-domain">
                                                {$addon.domainname}
                                            </span>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {/foreach}
                            
                            {foreach $domains as $num => $domain}
                            <div class="item">
                                
                                <div class="item-header">
                                    <a class="badge feat bg-danger mr-30" data-toggle="tooltip" data-placement="left" title="{$LANG.orderForm.remove}" type="button" class="btn-remove-from-cart" onclick="removeItem('d','{$num}')"><i class="ico-trash-2 f-16"></i></a>
                                    
                                    <a class="badge feat bg-default mr-65" data-toggle="tooltip" data-placement="left" title="{$LANG.orderForm.edit}" href="{$WEB_ROOT}/cart.php?a=confdomains" class="btn-edit-item-cart"><i class="ico-tool f-16"></i></a>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <span class="item-title">
                                                {if $domain.type eq "register"}{$LANG.orderdomainregistration}{else}{$LANG.orderdomaintransfer}{/if}
                                            </span>
                                            
                                            <span class="item-domain-addons">
                                                {if $domain.dnsmanagement}- {$LANG.domaindnsmanagement}<br />{/if}
                                                {if $domain.emailforwarding}- {$LANG.domainemailforwarding}<br />{/if}
                                                {if $domain.idprotection}- {$LANG.domainidprotection}<br />{/if}
                                            </span>
                                        </div>
                                        <div class="col-sm-5 item-price">
                                            {if count($domain.pricing) == 1 || $domain.type == 'transfer'}
                                            <span name="{$domain.domain}Price">{$domain.price}</span>
                                            <span class="cycle">{$domain.regperiod} {$domain.yearsLanguage}</span>
                                            <span class="renewal cycle">
                                                {if isset($domain.renewprice)}{lang key='domainrenewalprice'} <span class="renewal-price cycle">{$domain.renewprice->toPrefixed()}{$domain.shortRenewalYearsLanguage}{/if}</span>
                                            </span>
                                            {else}
                                            <span name="{$domain.domain}Price">{$domain.price}</span>
                                            <span class="renewal cycle">
                                                {lang key='domainrenewalprice'} <span class="renewal-price cycle">{if isset($domain.renewprice)}{$domain.renewprice->toPrefixed()}{$domain.shortRenewalYearsLanguage}{/if}</span>
                                            </span>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-footer">
                                    <div class="row">
                                        <div class="col-md-8 col-xs-12">
                                            {if $domain.domain}
                                            <span>{$LANG.yourdomains}:</span>
                                            <span class="item-domain">
                                                {$domain.domain}
                                            </span>
                                            {/if}
                                        </div>
                                        <div class="col-md-4 col-xs-12">
                                            <div class="dropdown float-right">
                                                <button class="btn btn-prussian btn-xs dropdown-toggle domainyarsselect" type="button" id="{$domain.domain}Pricing" name="{$domain.domain}Pricing" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                {$domain.regperiod} {$domain.yearsLanguage}
                                                <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="{$domain.domain}Pricing">
                                                    {foreach $domain.pricing as $years => $price}
                                                    <li>
                                                        <a href="#" onclick="selectDomainPeriodInCart('{$domain.domain}', '{$price.register}', {$years}, '{if $years == 1}{lang key='orderForm.year'}{else}{lang key='orderForm.years'}{/if}');return false;">
                                                            {$years} {if $years == 1}{lang key='orderForm.year'}{else}{lang key='orderForm.years'}{/if} @ {$price.register}
                                                        </a>
                                                    </li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {/foreach}
                            {foreach $renewals as $num => $domain}
                            <div class="item">
                                <div class="item-header">
                                    <a class="badge feat bg-danger mr-30" data-toggle="tooltip" data-placement="left" title="{$LANG.orderForm.remove}" type="button" class="btn-remove-from-cart" onclick="removeItem('r','{$num}')"><i class="ico-trash-2 f-16"></i></a>
                                    <div class="row">
                                        <div class="col-md-12 mb-15">
                                            <span class="item-title">
                                                {$LANG.domainrenewal}
                                            </span>
                                        </div>
                                        <div class="col-md-5 item-price">
                                            <span>{$domain.price}</span>
                                            <span class="cycle">{$domain.regperiod} {$LANG.orderyears}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-footer">
                                    <div class="row">
                                        <div class="col-md-8 col-xs-12">
                                            {if $domain.domain}
                                            <span>{$LANG.yourdomains}:</span>
                                            <span class="item-domain">
                                                {$domain.domain}
                                            </span>
                                            {/if}
                                            {if $domain.dnsmanagement}&nbsp;&raquo; {$LANG.domaindnsmanagement}<br />{/if}
                                            {if $domain.emailforwarding}&nbsp;&raquo; {$LANG.domainemailforwarding}<br />{/if}
                                            {if $domain.idprotection}&nbsp;&raquo; {$LANG.domainidprotection}<br />{/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {/foreach}
                            {foreach $upgrades as $num => $upgrade}
                            <div class="item">
                                <div class="row">
                                    <a class="badge feat bg-danger mr-30" data-toggle="tooltip" data-placement="left" title="{$LANG.orderForm.remove}" type="button" class="btn-remove-from-cart" onclick="removeItem('u','{$num}')"><i class="ico-trash-2 f-16"></i></a>
                                    
                                    <div class="{if $showUpgradeQtyOptions}col-md-12{else}col-sm-7{/if}">
                                        <span class="item-title">
                                            {$LANG.upgrade}
                                        </span>
                                        <span class="item-group">
                                            {if $upgrade->type == 'service'}
                                            {$upgrade->originalProduct->productGroup->name}<br>{$upgrade->originalProduct->name} => {$upgrade->newProduct->name}
                                            {elseif $upgrade->type == 'addon'}
                                            {$upgrade->originalAddon->name} => {$upgrade->newAddon->name}
                                            {/if}
                                        </span>
                                        <span class="item-domain">
                                            {if $upgrade->type == 'service'}
                                            {$upgrade->service->domain}
                                            {/if}
                                        </span>
                                    </div>
                                    <div class="col-md-5 item-price">
                                        <span>{$upgrade->newRecurringAmount}</span>
                                        <span class="cycle">{$upgrade->localisedNewCycle}</span>
                                    </div>
                                </div>
                                {if $upgrade->totalDaysInCycle > 0}
                                <div class="row row-upgrade-credit">
                                    <div class="col-md-12">
                                        <span class="item-group">
                                            {$LANG.upgradeCredit}
                                        </span>
                                        <div class="upgrade-calc-msg">
                                            {lang key="upgradeCreditDescription" daysRemaining=$upgrade->daysRemaining totalDays=$upgrade->totalDaysInCycle}
                                        </div>
                                    </div>
                                    {if $showUpgradeQtyOptions}
                                        <div class="col-sm-2 item-qty">
                                            {if $upgrade->allowMultipleQuantities}
                                                <input type="number" name="upgradeqty[{$num}]" value="{$upgrade->qty}" class="form-control text-center" min="{$upgrade->minimumQuantity}" />
                                                <button type="submit" class="btn btn-xs">
                                                    {$LANG.orderForm.update}
                                                </button>
                                            {/if}
                                        </div>
                                    {/if}
                                    <div class="col-sm-4 item-price">
                                        <span>-{$upgrade->creditAmount}</span>
                                    </div>
                                </div>
                                {/if}
                            </div>
                            {/foreach}
                            {if $cartitems == 0}
                            <div class="view-cart-empty">
                                {$LANG.cartempty}
                            </div>
                            {/if}
                            
                        </div>
                        
                    </form>
                    {foreach $hookOutput as $output}
                    <div class="promo-code-cart-container">
                        {if $promotioncode}
                        <div class="view-cart-promotion-code">
                            {$promotioncode} - {$promotiondescription}
                            <a href="{$WEB_ROOT}/cart.php?a=removepromo" class="btn-cancel-promo" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="{$LANG.orderForm.removePromotionCode}">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        
                        {else}
                        <form method="post" action="{$WEB_ROOT}/cart.php?a=view">
                            <div class="form-group">
                                <input type="text" name="promocode" id="inputPromotionCode" class="field" placeholder="{lang key="orderPromoCodePlaceholder"}" required="required">
                                <button type="submit" name="validatepromo" class="btn-order-embeded" value="{$LANG.orderpromovalidatebutton}">
                                <i class="ico-check f-16"></i>
                                </button>
                            </div>
                        </form>
                        {/if}
                    </div>
                    <div>
                        {$output}
                    </div>
                    {/foreach}
                    {foreach $gatewaysoutput as $gatewayoutput}
                    <div class="view-cart-gateway-checkout">
                        {$gatewayoutput}
                    </div>
                    {/foreach}

                    {if $taxenabled && !$loggedin}
                    <div class="calcTaxes-cart-container pt-5">
                        <h5>{$LANG.orderForm.estimateTaxes}</h5>
                        <form method="post" action="{$WEB_ROOT}/cart.php?a=setstateandcountry">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="text" name="state" id="inputState" value="{$clientsdetails.state}" class="form-control"{if $loggedin} disabled="disabled"{/if} />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <select name="country" id="inputCountry" class="form-control">
                                            {foreach $countries as $countrycode => $countrylabel}
                                            <option value="{$countrycode}"{if (!$country && $countrycode == $defaultcountry) || $countrycode eq $country} selected{/if}>
                                                {$countrylabel}
                                            </option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group text-center">
                                    <button type="submit" class="btn">
                                    {$LANG.orderForm.updateTotals}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    {/if}
                </div>

                <div class="col-md-4" id="scrollingPanelContainer">                    
                    <div class="order-summary" id="orderSummary">
                        {if $cartitems > 0}
                        <div class="badge feat bg-danger empty-cart mr-20 mt-10" data-toggle="tooltip" data-placement="left" title="{$LANG.emptycart}" type="button" id="btnEmptyCart">
                        <i class="ico-trash-2 f-16"></i></div>
                        {/if}
                        
                        <div class="loader" id="orderSummaryLoader" style="display: none;">
                            <i class="fas fa-fw fa-sync fa-spin"></i>
                        </div>
                        <h2>{$LANG.ordersummary}</h2>
                        <div class="summary-container">
                            <div class="subtotal clearfix">
                                <span class="pull-left">{$LANG.ordersubtotal}</span>
                                <span id="subtotal" class="pull-right">{$subtotal}</span>
                            </div>
                            {if $promotioncode || $taxrate || $taxrate2}
                            <div class="bordered-totals">
                                {if $promotioncode}
                                <div class="clearfix">
                                    <span class="pull-left">{$promotiondescription}</span>
                                    <span id="discount" class="pull-right">{$discount}</span>
                                </div>
                                {/if}
                                {if $taxrate}
                                <div class="clearfix">
                                    <span class="pull-left">{$taxname} @ {$taxrate}%</span>
                                    <span id="taxTotal1" class="pull-right">{$taxtotal}</span>
                                </div>
                                {/if}
                                {if $taxrate2}
                                <div class="clearfix">
                                    <span class="pull-left">{$taxname2} @ {$taxrate2}%</span>
                                    <span id="taxTotal2" class="pull-right">{$taxtotal2}</span>
                                </div>
                                {/if}
                            </div>
                            {/if}
                            <div class="recurring-totals clearfix">
                                <span class="pull-left">{$LANG.orderForm.totals}</span>
                                <span id="recurring" class="pull-right recurring-charges">
                                    <span id="recurringMonthly" {if !$totalrecurringmonthly}style="display:none;"{/if}>
                                        <span class="cost">{$totalrecurringmonthly}</span> {$LANG.orderpaymenttermmonthly}<br />
                                    </span>
                                    <span id="recurringQuarterly" {if !$totalrecurringquarterly}style="display:none;"{/if}>
                                        <span class="cost">{$totalrecurringquarterly}</span> {$LANG.orderpaymenttermquarterly}<br />
                                    </span>
                                    <span id="recurringSemiAnnually" {if !$totalrecurringsemiannually}style="display:none;"{/if}>
                                        <span class="cost">{$totalrecurringsemiannually}</span> {$LANG.orderpaymenttermsemiannually}<br />
                                    </span>
                                    <span id="recurringAnnually" {if !$totalrecurringannually}style="display:none;"{/if}>
                                        <span class="cost">{$totalrecurringannually}</span> {$LANG.orderpaymenttermannually}<br />
                                    </span>
                                    <span id="recurringBiennially" {if !$totalrecurringbiennially}style="display:none;"{/if}>
                                        <span class="cost">{$totalrecurringbiennially}</span> {$LANG.orderpaymenttermbiennially}<br />
                                    </span>
                                    <span id="recurringTriennially" {if !$totalrecurringtriennially}style="display:none;"{/if}>
                                        <span class="cost">{$totalrecurringtriennially}</span> {$LANG.orderpaymenttermtriennially}<br />
                                    </span>
                                </span>
                            </div>
                            <div class="total-due-today total-due-today-padded">
                                <span id="totalDueToday" class="amt">{$total}</span>
                                <span>{$LANG.ordertotalduetoday}</span>
                            </div>
                            
                            <div class="express-checkout-buttons">
                                {foreach $expressCheckoutButtons as $checkoutButton}
                                {$checkoutButton}
                                <div class="separator">
                                    - {$LANG.or|strtoupper} -
                                </div>
                                {/foreach}
                            </div>
                            <div class="text-right">
                                <a href="{$WEB_ROOT}/cart.php" class="btn btn-link btn-continue-shopping" id="continueShopping">
                                    <i class="ico-chevron-left"></i> {$LANG.orderForm.continueShopping}
                                </a>
                                <a href="{$WEB_ROOT}/cart.php?a=checkout&e=false" class="btn btn-prussian btn-lg btn-checkout{if $cartitems == 0} disabled{/if}" id="checkout">
                                    {$LANG.orderForm.checkout} <i class="ico-chevron-right w-icon"></i>
                                </a><br/>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    <form method="post" action="{$WEB_ROOT}/cart.php">
        <input type="hidden" name="a" value="remove" />
        <input type="hidden" name="r" value="" id="inputRemoveItemType" />
        <input type="hidden" name="i" value="" id="inputRemoveItemRef" />
        <div class="modal fade modal-remove-item" id="modalRemoveItem" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{$LANG.orderForm.close}">
                        <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">
                        <i class="ico-trash-2 f-54 c-grey"></i>
                        <span class="c-grey">{$LANG.orderForm.removeItem}</span>
                        </h4>
                    </div>
                    <div class="modal-body">
                        {$LANG.cartremoveitemconfirm}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-small btn-default" data-dismiss="modal">{$LANG.no}</button>
                        <button type="submit" class="btn btn-small btn-danger">{$LANG.yes}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form method="post" action="{$WEB_ROOT}/cart.php">
        <input type="hidden" name="a" value="empty" />
        <div class="modal fade modal-remove-item" id="modalEmptyCart" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{$LANG.orderForm.close}">
                        <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">
                        <i class="ico-trash-2 f-54 c-grey"></i>
                        <span class="c-grey">{$LANG.emptycart}</span>
                        </h4>
                    </div>
                    <div class="modal-body">
                        {$LANG.cartemptyconfirm}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-small btn-default" data-dismiss="modal">{$LANG.no}</button>
                        <button type="submit" class="btn btn-small btn-danger">{$LANG.yes}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{/if}
<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/horn/js/main.js?v={$versionHash}"></script>