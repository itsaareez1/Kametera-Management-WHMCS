{include file="orderforms/horn/common.tpl"}
<div id="order-standard_cart">
    <div class="row">
        <div class="col-md-12">
            <div class="header-lined">
                <h1>
                {$LANG.orderForm.transferToUs}
                <small>{lang key='orderForm.transferExtend'}*</small>
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
            <p>{$LANG.carttransferdomainchoice} ({lang key='orderForm.singleTransfer'})</p>
            <div class="domain-transfer-container overlay">
                <div class="domain-transfer-bg clearfix">
                    <div class="badge feat bg-puretheme" data-toggle="tooltip" data-placement="left" title="{lang key='orderForm.authCodeTooltip'}"><i class="ico-help-circle f-18"></i> </div>
                    <form method="post" action="{$WEB_ROOT}/cart.php" id="frmDomainTransfer">
                        <input type="hidden" name="a" value="addDomainTransfer">
                        <div class="row">
                            <div class="col-sm-8 col-sm-offset-2">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="domain" id="inputTransferDomain" value="{$lookupTerm}" placeholder="{lang key='domainname'}" data-toggle="tooltip" data-placement="left" data-trigger="manual" title="{lang key='orderForm.enterDomain'}" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="epp" id="inputAuthCode" placeholder="{lang key='orderForm.authCodePlaceholder'}" data-toggle="tooltip" data-placement="left" data-trigger="manual" title="{lang key='orderForm.authCode'} {lang key='orderForm.required'}" />
                                        </div>
                                        <div id="transferUnavailable" class="alert alert-warning slim-alert text-center hidden"></div>
                                        {if $captcha->isEnabled() && !$captcha->recaptcha->isEnabled()}
                                        <div class="captcha-container" id="captchaContainer">
                                            <div class="default-captcha">
                                                <p>{lang key="cartSimpleCaptcha"}</p>
                                                <div>
                                                    <img id="inputCaptchaImage" src="{$systemurl}includes/verifyimage.php" />
                                                    <input id="inputCaptcha" type="text" name="code" maxlength="6" class="form-control input-sm" data-toggle="tooltip" data-placement="right" data-trigger="manual" title="{lang key='orderForm.required'}" />
                                                </div>
                                            </div>
                                        </div>
                                        {elseif $captcha->isEnabled() && $captcha->recaptcha->isEnabled() && !$captcha->recaptcha->isInvisible()}
                                        <div class="form-group recaptcha-container" id="captchaContainer"></div>
                                        {/if}
                                    </div>
                                    <div class="panel-footer text-right">
                                        <span class="renewed-point">* {lang key='orderForm.extendExclusions'}</span>
                                        <button type="submit" id="btnTransferDomain" class="btn btn-small btn-prussian ml-30 btn-transfer{$captcha->getButtonClass($captchaForm)}">
                                        <span class="loader hidden" id="addTransferLoader">
                                            <i class="fas fa-fw fa-spinner fa-spin"></i>
                                        </span>
                                        <span id="addToCart">{lang key="orderForm.addToCart"}</span>
                                        <i class="ico-chevron-right w-icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>