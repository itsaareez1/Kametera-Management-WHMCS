{include file="orderforms/horn/common.tpl"}
<script>
var _localLang = {
'addToCart': '{$LANG.orderForm.addToCart|escape}',
'addedToCartRemove': '{$LANG.orderForm.addedToCartRemove|escape}'
}
</script>
<div id="order-standard_cart">
    <div class="row">
        <div class="col-md-12">
            <div class="header-lined">
                <h1>{$LANG.cartdomainsconfig}</h1>
                
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
            <form method="post" action="{$smarty.server.PHP_SELF}?a=confdomains" id="frmConfigureDomains">
                <input type="hidden" name="update" value="true" />
                <p>{$LANG.orderForm.reviewDomainAndAddons}</p>
                {if $errormessage}
                <div class="alert alert-danger" role="alert">
                    <p>{$LANG.orderForm.correctErrors}:</p>
                    <ul>
                        {$errormessage}
                    </ul>
                </div>
                {/if}
                {foreach $domains as $num => $domain}
                <div class="domainsaddionsbox">
                    <h5 class="domainsaddionsboxtitle">{$domain.domain} <span>{$LANG.orderregperiod}: {$domain.regperiod} {$LANG.orderyears}</span></h5>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="domainsaddionsbox-hosting-add">
                                {if $domain.hosting}
                                <span class="yes">{$LANG.cartdomainshashosting}</span>
                                {else}
                                <span class="no"><a href="{$WEB_ROOT}/cart.php">{$LANG.cartdomainsnohosting}</a></span>
                                {/if}
                            </div>
                        </div>
                        {if $domain.eppenabled}
                        <div class="col-sm-12">
                            <div class="form-group prepend-icon">
                                <input type="text" name="epp[{$num}]" id="inputEppcode{$num}" value="{$domain.eppvalue}" class="field" placeholder="{$LANG.domaineppcode}" />
                                <label for="inputEppcode{$num}" class="field-icon">
                                    <i class="fas fa-lock"></i>
                                </label>
                                <span class="field-help-text">
                                    {$LANG.domaineppcodedesc}
                                </span>
                            </div>
                        </div>
                        {/if}
                    </div>
                    {if $domain.dnsmanagement || $domain.emailforwarding || $domain.idprotection}
                    <div class="row addon-products">
                        {if $domain.dnsmanagement}
                        <div class="col-sm-{math equation="12 / numAddons" numAddons=$domain.addonsCount}">
                            <div class="panel panel-default panel-addon{if $domain.dnsmanagementselected} panel-addon-selected{/if}">
                                <div class="panel-body">
                                    <label class="chcktoadd">
                                        <input type="checkbox" name="dnsmanagement[{$num}]"{if $domain.dnsmanagementselected} checked{/if} />
                                        <span>{$LANG.domaindnsmanagement}</span>
                                    </label>
                                    
                                    
                                    <i data-toggle="tooltip" data-placement="left" title="{$LANG.domainaddonsdnsmanagementinfo}" class="feather icon-alert-circle chcktoaddmore"></i>
                                    <span class="adddomainprice">{$domain.dnsmanagementprice} / {$domain.regperiod} {$LANG.orderyears}</span>
                                </div>
                                
                            </div>
                        </div>
                        {/if}
                        {if $domain.idprotection}
                        <div class="col-sm-{math equation="12 / numAddons" numAddons=$domain.addonsCount}">
                            <div class="panel panel-default panel-addon{if $domain.idprotectionselected} panel-addon-selected{/if}">
                                <div class="panel-body">
                                    <label class="chcktoadd">
                                        <input type="checkbox" name="idprotection[{$num}]"{if $domain.idprotectionselected} checked{/if} />
                                        <span>{$LANG.domainidprotection}</span>
                                    </label>
                                    
                                    <i data-toggle="tooltip" data-placement="left" title="{$LANG.domainaddonsidprotectioninfo}" class="feather icon-alert-circle chcktoaddmore"></i>
                                    <span class="adddomainprice">{$domain.idprotectionprice} / {$domain.regperiod} {$LANG.orderyears}</span>
                                </div>
                            </div>
                        </div>
                        {/if}
                        {if $domain.emailforwarding}
                        <div class="col-sm-{math equation="12 / numAddons" numAddons=$domain.addonsCount}">
                            <div class="panel panel-default panel-addon{if $domain.emailforwardingselected} panel-addon-selected{/if}">
                                <div class="panel-body">
                                    <label class="chcktoadd">
                                        <input type="checkbox" name="emailforwarding[{$num}]"{if $domain.emailforwardingselected} checked{/if} />
                                        <span>{$LANG.domainemailforwarding}</span>
                                    </label>
                                    
                                    <i data-toggle="tooltip" data-placement="left" title="{$LANG.domainaddonsemailforwardinginfo}" class="feather icon-alert-circle chcktoaddmore"></i>
                                    <span class="adddomainprice">{$domain.emailforwardingprice} / {$domain.regperiod} {$LANG.orderyears}</span>
                                </div>
                            </div>
                        </div>
                        {/if}
                    </div>
                </div>
                {/if}
                {foreach from=$domain.fields key=domainfieldname item=domainfield}
                <div class="row">
                    <div class="col-sm-4">{$domainfieldname}:</div>
                    <div class="col-sm-8">{$domainfield}</div>
                </div>
                {/foreach}
                {/foreach}
            </div>
        </div>
        {if $atleastonenohosting}
        <div class="box-domainnameservers">
            <h5 class="box-title">{$LANG.domainnameservers}<span>{$LANG.cartnameserversdesc}</span></h5>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="inputNs1">{$LANG.domainnameserver1}</label>
                        <input type="text" class="form-control" id="inputNs1" name="domainns1" value="{$domainns1}" />
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="inputNs2">{$LANG.domainnameserver2}</label>
                        <input type="text" class="form-control" id="inputNs2" name="domainns2" value="{$domainns2}" />
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="inputNs3">{$LANG.domainnameserver3}</label>
                        <input type="text" class="form-control" id="inputNs3" name="domainns3" value="{$domainns3}" />
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="inputNs1">{$LANG.domainnameserver4}</label>
                        <input type="text" class="form-control" id="inputNs4" name="domainns4" value="{$domainns4}" />
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="inputNs5">{$LANG.domainnameserver5}</label>
                        <input type="text" class="form-control" id="inputNs5" name="domainns5" value="{$domainns5}" />
                    </div>
                </div>
            </div>
        </div>
        {/if}
        <div class="text-center">
            <button type="submit" class="btn btn-prussian btn-lg">
            {$LANG.continue}
            &nbsp;<i class="fas fa-arrow-circle-right"></i>
            </button>
        </div>
    </form>
</div>
</div>
</div>