{include file="orderforms/horn/common.tpl"}
<div id="order-standard_cart">
    <div class="row">
        <div class="col-md-12">
            <div class="header-lined">
                <h1>
                {if $totalResults > 1}{lang key='domainrenewals'}{else}{lang key='domainrenewal'}{/if}
                {if $totalResults > 5}
                <div class="pull-right">
                    <input id="domainRenewalFilter" type="search" class="domain-renewals-filter form-control input-inline-100" placeholder="{lang key='searchenterdomain'}">
                </div>
                {/if}
                </h1>
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
                    {if $totalResults < $totalDomainCount}
                    <div class="text-left mb-15">
                        {lang key='domainRenewal.showingDomains' showing=$totalResults totalCount=$totalDomainCount}
                        <a class="c-prussian fw500" id="linkShowAll" href="{routePath('cart-domain-renewals')}">{lang key='domainRenewal.showAll'}</a>
                    </div>
                    {/if}
                    <div id="domainRenewals" class="domain-renewals">
                        {foreach $renewalsData as $renewalData}
                        <div class="domain-renewal" data-domain="{$renewalData.domain}">
                            
                            <div class="pull-right">
                                {if !$renewalData.eligibleForRenewal}
                                <span class="label label-info">
                                    {if $renewalData.freeDomainRenewal}
                                        {lang key='domainRenewal.freeWithService'}
                                    {else}
                                        {lang key='domainRenewal.unavailable'}
                                    {/if}
                                </span>
                                <div class="badge feat bg-prussian" data-toggle="tooltip" data-placement="left" title="{lang key='domainRenewal.unavailable'}"><i class="ico-info f-16"></i> </div>
                                {elseif ($renewalData.pastGracePeriod && $renewalData.pastRedemptionGracePeriod)}
                                <div class="badge feat bg-prussian" data-toggle="tooltip" data-placement="left" title="{lang key='domainrenewalspastgraceperiod'}"><i class="ico-info f-16"></i> </div>
                                {elseif !$renewalData.beforeRenewLimit && $renewalData.daysUntilExpiry > 0}
                                <div class="badge feat bg-success mr-20 mt-10" {if $renewalData.daysUntilExpiry > 30}success{else}warning{/if} data-toggle="tooltip" data-placement="left" title="{lang key='domainRenewal.expiringIn' days=$renewalData.daysUntilExpiry}"><i class="ico-info f-16"></i> </div>
                                {elseif $renewalData.daysUntilExpiry === 0}
                                <div class="badge feat bg-default mr-30" data-toggle="tooltip" data-placement="left" title="{lang key='expiresToday'}"><i class="ico-info f-18"></i> </div>
                                {elseif $renewalData.beforeRenewLimit}
                                <div class="badge feat bg-prussian" data-toggle="tooltip" data-placement="left" title="{lang key='domainRenewal.maximumAdvanceRenewal' days=$renewalData.beforeRenewLimitDays}"><i class="ico-info f-16"></i> </div>
                                {else}
                                <div class="badge feat bg-danger mr-30" data-toggle="tooltip" data-placement="left" title="{lang key='domainRenewal.expiredDaysAgo' days=$renewalData.daysUntilExpiry*-1}"><i class="ico-info f-16"></i> </div>
                                {/if}
                            </div>
                            <h3 class="mb-10">{$renewalData.domain}</h3>
                            <p>{lang key='clientareadomainexpirydate'}: {$renewalData.expiryDate->format('j M Y')} ({$renewalData.expiryDate->diffForHumans()})</p>
                            {if $renewalData.freeDomainRenewal}
                                <p class="domain-renewal-desc">{lang key='domainRenewal.freeWithServiceDesc'}</p>
                            {/if}
                            {if ($renewalData.pastGracePeriod && $renewalData.pastRedemptionGracePeriod) || !count($renewalData.renewalOptions)}
                            {else}
                            <form class="form-horizontal">
                                <div class="form-group row">
                                    <label for="renewalPricing{$renewalData.id}" class="col-md-12">
                                        {lang key='domainRenewal.availablePeriods'}
                                        {if $renewalData.inGracePeriod || $renewalData.inRedemptionGracePeriod}
                                        *
                                        {/if}
                                    </label>
                                    <div class="col-sm-6 custom-arrow">
                                        <select class="form-control select-renewal-pricing" id="renewalPricing{$renewalData.id}" data-domain-id="{$renewalData.id}">
                                            {foreach $renewalData.renewalOptions as $renewalOption}
                                            <option value="{$renewalOption.period}">
                                                {$renewalOption.period} {lang key='orderyears'} @ {$renewalOption.rawRenewalPrice}
                                                {if $renewalOption.gracePeriodFee && $renewalOption.gracePeriodFee->toNumeric() != 0.00}
                                                + {$renewalOption.gracePeriodFee} {lang key='domainRenewal.graceFee'}
                                                {/if}
                                                {if $renewalOption.redemptionGracePeriodFee && $renewalOption.redemptionGracePeriodFee->toNumeric() != 0.00}
                                                + {$renewalOption.redemptionGracePeriodFee} {lang key='domainRenewal.redemptionFee'}
                                                {/if}
                                            </option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </form>
                            {/if}
                            <div class="text-left">
                                {if !$renewalData.eligibleForRenewal || $renewalData.beforeRenewLimit || ($renewalData.pastGracePeriod && $renewalData.pastRedemptionGracePeriod)}
                                {else}
                                <button id="renewDomain{$renewalData.id}" class="btn btn-medium btn-prussian btn-add-renewal-to-cart" data-domain-id="{$renewalData.id}"> <i class="ico-shopping-cart f-14"></i>
                                <span class="to-add">
                                    <i class="fas fa-fw fa-spinner fa-spin"></i>
                                    {lang key='addtocart'}
                                </span>
                                <span class="added">{lang key='domaincheckeradded'}</span>
                                </button>
                                {/if}
                            </div>
                        </div>
                        {foreachelse}
                        <div class="no-domains">
                            {lang key='domainRenewal.noDomains'}
                        </div>
                        {/foreach}
                    </div>
                    <div class="text-left">
                        <small>
                        {if $hasDomainsInGracePeriod}
                        * {lang key='domainRenewal.graceRenewalPeriodDescription'}
                        {/if}
                        </small>
                    </div>
                </div>
                <div class="col-md-4" id="scrollingPanelContainer">
                    <div id="orderSummary">
                        <div class="order-summary">
                            <div class="loader" id="orderSummaryLoader">
                                <i class="fas fa-fw fa-sync fa-spin"></i>
                            </div>
                            <h2>{lang key='ordersummary'}</h2>
                            <div class="summary-container" id="producttotal"></div>
                        </div>
                        <div class="text-right">
                            <a id="btnGoToCart" class="btn btn-prussian btn-lg" href="{$WEB_ROOT}/cart.php?a=view">
                                {lang key='viewcart'}
                                <i class="glyphicon glyphicon-shopping-cart"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="removeRenewalForm" method="post" action="{$WEB_ROOT}/cart.php">
        <input type="hidden" name="a" value="remove" />
        <input type="hidden" name="r" value="" id="inputRemoveItemType" />
        <input type="hidden" name="i" value="" id="inputRemoveItemRef" />
        <div class="modal fade modal-remove-item" id="modalRemoveItem" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{lang key='orderForm.close'}">
                        <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">
                        <i class="fas fa-times fa-3x"></i>
                        <span>{lang key='orderForm.removeItem'}</span>
                        </h4>
                    </div>
                    <div class="modal-body">
                        {lang key='cartremoveitemconfirm'}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{lang key='no'}</button>
                        <button type="submit" class="btn btn-primary">{lang key='yes'}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script>recalculateRenewalTotals();</script>