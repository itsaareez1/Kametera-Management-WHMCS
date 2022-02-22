{include file="orderforms/digitalocean/common.tpl"}
<div id="order-standard_cart">
    <div class="row">
        <div class="col-md-12">
            <div class="header-lined">
                <h1>
                {if $productGroup.headline}
                {$productGroup.headline}
                {else}
                {$productGroup.name}
                {/if}
                {if $productGroup.tagline}
                <small>{$productGroup.tagline}</small>
                {/if}
                </h1>
                <div class="dropnav-header-lined">
                    <button id="dropside-content" type="button" class="drop-down-btn dropside-content" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="ico-more-vertical d-block f-20"></span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropside-content">
                        {include file="orderforms/digitalocean/sidebar-categories.tpl"}
                    </div>
                </div>
            </div>
            {if $errormessage}
            <div class="alert alert-danger">
                {$errormessage}
            </div>
            {/if}
        </div>
        <div class="col-md-12">
            {include file="orderforms/digitalocean/sidebar-categories-collapsed.tpl"}
            <div class="product" id="products">
                {foreach $products as $key => $product}
                    <div class="col-md-4">
                        <div class="wrapper-plan" data-aos="fade-up" data-aos-duration="800">
                            {if $product.isFeatured}
                                <div class="badge feat bg-puretheme">{$LANG.featuredProduct|upper}</div>
                            {/if}
                            <div class="top-content">
                                {if $product.name|strstr:"Dedicated Server"}
                                    <img class="svg mb-10 wh-50" src="{$WEB_ROOT}/templates/{$template}/assets/fonts/svg/managedserver.svg">
                                {elseif $product.name|strstr:"Cloud"}
                                    <img class="svg mb-10 wh-50" src="{$WEB_ROOT}/templates/{$template}/assets/fonts/svg/cloudserver.svg">
                                {else}
                                    <img class="svg mb-10 wh-50" src="{$WEB_ROOT}/templates/{$template}/assets/fonts/svg/cloudmanaged.svg">      
                                {/if}
                                <div class="plan-title" id="product{$product@iteration}-name">{$product.name} <span>Starting at:</span></div>
                                <div id="product{$product@iteration}-price">
                                    {if $product.bid}
                                        {$LANG.bundledeal}<br />
                                        {if $product.displayprice}
                                        <span class="plan-price">{$product.displayprice}</span>
                                        {/if}
                                        {else}
                                        {if $product.pricing.hasconfigoptions}
                                        {$LANG.startingfrom}
                                        {/if}
                                        <span class="plan-price">{$product.pricing.minprice.price}</span>
                                        <span class="period">{if $product.pricing.minprice.cycle eq "monthly"}
                                            {$LANG.orderpaymenttermmonthly}
                                            {elseif $product.pricing.minprice.cycle eq "quarterly"}
                                            {$LANG.orderpaymenttermquarterly}
                                            {elseif $product.pricing.minprice.cycle eq "semiannually"}
                                            {$LANG.orderpaymenttermsemiannually}
                                            {elseif $product.pricing.minprice.cycle eq "annually"}
                                            {$LANG.orderpaymenttermannually}
                                            {elseif $product.pricing.minprice.cycle eq "biennially"}
                                            {$LANG.orderpaymenttermbiennially}
                                            {elseif $product.pricing.minprice.cycle eq "triennially"}
                                            {$LANG.orderpaymenttermtriennially}
                                        {/if}</span>
                                        <br>
                                        {/if}
                                
                                </div>
                            </div>
                            <ul class="specs-content bg-lighttheme" id="product{$product@iteration}-description">
                                 {if $product.featuresdesc}
                                    {$product.featuresdesc}
                                 {/if}
                                {foreach $product.features as $feature => $value}
                                <li id="product{$product@iteration}-feature{$value@iteration}">
                                    <span class="feature-value">{$value}</span>
                                    {$feature}
                                </li>
                                {/foreach}   
                                <div class="text-center">
                                    <a id="product{$product@iteration}-order-button" class="btn btn-prussian mt-15" href="{$WEB_ROOT}/cart.php?a=add&{if $product.bid}bid={$product.bid}{else}pid={$product.pid}{/if}"><span>{$LANG.ordernowbutton}</span></a>
                                </div>                    
                                <div class="text-center">
                                {if $product.pricing.minprice.setupFee}
                                    <small class="setupfee">{$product.pricing.minprice.setupFee->toPrefixed()} {$LANG.ordersetupfee}</small>
                                {else}
                                    <small class="setupfee">{$LANG.orderpromofreesetup}</small>
                                {/if}
                                </div>
                            </ul>
                        </div>
                    </div>                
                <div class="col-md-4 plan-content {if $product.isFeatured}feature-plan{/if}">
                    <div class="clearfix" id="product{$product@iteration}">
                        {if $product.isFeatured}
                        <div class="badge feat tt-lower bg-puretheme">{$LANG.featuredProduct|upper}</div>
                        {/if}
                        <div class="header-content">
                            <header>
                                <span class="product-name" id="product{$product@iteration}-name">{$product.name}</span>
                                {if $product.stockControlEnabled}
                                <span class="qty">
                                    {$product.qty} {$LANG.orderavailable}
                                </span>
                                {/if}
                            </header>
                            <div class="product-pricing" id="product{$product@iteration}-price">
                                {if $product.bid}
                                {$LANG.bundledeal}<br />
                                {if $product.displayprice}
                                <span class="price">{$product.displayprice}</span>
                                {/if}
                                {else}
                                {if $product.pricing.hasconfigoptions}
                                {$LANG.startingfrom}
                                {/if}
                                <span class="price">{$product.pricing.minprice.price}</span>
                                <span class="period">{if $product.pricing.minprice.cycle eq "monthly"}
                                    {$LANG.orderpaymenttermmonthly}
                                    {elseif $product.pricing.minprice.cycle eq "quarterly"}
                                    {$LANG.orderpaymenttermquarterly}
                                    {elseif $product.pricing.minprice.cycle eq "semiannually"}
                                    {$LANG.orderpaymenttermsemiannually}
                                    {elseif $product.pricing.minprice.cycle eq "annually"}
                                    {$LANG.orderpaymenttermannually}
                                    {elseif $product.pricing.minprice.cycle eq "biennially"}
                                    {$LANG.orderpaymenttermbiennially}
                                    {elseif $product.pricing.minprice.cycle eq "triennially"}
                                    {$LANG.orderpaymenttermtriennially}
                                {/if}</span>
                                <br>
                                {/if}
                            </div>
                        </div>
                        <div class="product-desc">
                            {if $product.featuresdesc}
                            <div class="prod-desc-div" id="product{$product@iteration}-description">
                                {$product.featuresdesc}
                            </div>
                            {/if}
                            <ul class="prod-desc-ul">
                                {foreach $product.features as $feature => $value}
                                <li id="product{$product@iteration}-feature{$value@iteration}">
                                    <span class="feature-value">{$value}</span>
                                    {$feature}
                                </li>
                                {/foreach}
                            </ul>
                            <a href="{$WEB_ROOT}/cart.php?a=add&{if $product.bid}bid={$product.bid}{else}pid={$product.pid}{/if}" class="btn btn-prussian btn-sm" id="product{$product@iteration}-order-button">
                                {$LANG.ordernowbutton}
                            </a>
                            {if $product.pricing.minprice.setupFee}
                            <small class="setupfee">{$product.pricing.minprice.setupFee->toPrefixed()} {$LANG.ordersetupfee}</small>
                            {else}
                            <small class="setupfee">{$LANG.orderpromofreesetup}</small>
                            {/if}
                        </div>
                    </div>
                </div>
                {if $product@iteration % 3 == 0}
                <div class="row-eq-height">
                    {/if}
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/digitalocean/js/main.js?v={$versionHash}"></script>