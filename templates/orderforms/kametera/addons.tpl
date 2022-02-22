{include file="orderforms/horn/common.tpl"}
<div id="order-standard_cart">
    <div class="row">
        <div class="col-md-12">
            <div class="header-lined">
                <h1>{$LANG.cartproductaddons}</h1>
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
            {if count($addons) == 0}
            <div class="alert alert-warning text-center" role="alert">
                {$LANG.cartproductaddonsnone}
            </div>
            <p class="text-center">
                <a href="{$WEB_ROOT}/clientarea.php" class="btn btn-default">
                    <i class="fas fa-arrow-circle-left"></i>
                    {$LANG.orderForm.returnToClientArea}
                </a>
            </p>
            {/if}
            <div class="products">
                <div class="row row-eq-height">
                    {foreach $addons as $num => $addon}
                    <div class="col-md-6">
                        <div class="product addons clearfix" id="product{$num}">
                            <form method="post" action="{$smarty.server.PHP_SELF}?a=add" class="form-inline">
                                <input type="hidden" name="aid" value="{$addon.id}" />
                                <div class="addon-content">
                                    <span class="addon-name">{$addon.name}</span>
                                    <div class="product-pricing">
                                        {if $addon.free}
                                        {$LANG.orderfree}
                                        {else}
                                        <span class="price">{$addon.recurringamount} <span class="f-13 fw500 c-grey">{$addon.billingcycle}</span></span>
                                        {if $addon.setupfee}<br />+ {$addon.setupfee} {$LANG.ordersetupfee}{/if}
                                        {/if}
                                    </div>
                                    <div class="input-group">
                                        <select name="productid" id="inputProductId{$num}" class="form-control">
                                            {foreach $addon.productids as $product}
                                            <option value="{$product.id}">
                                                {$product.product}{if $product.domain} - {$product.domain}{/if}
                                            </option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-medium btn-prussian mb-10 mt-20">
                                    <i class="ico-shopping-cart f-14"></i>
                                    {$LANG.ordernowbutton}
                                    </button>
                                </div>
                                <div class="badge feat bg-puretheme mr-30" data-toggle="tooltip" data-placement="left" title="{$addon.description}"><i class="ico-info f-16"></i> </div>
                                <div class="product-desc">
                                    <p>{$addon.description}</p>
                                </div>
                            </form>
                        </div>
                    </div>
                    {if $num % 2 != 0}
                </div>
                <div class="row row-eq-height">
                    {/if}
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>