{include file="orderforms/horn/common.tpl"}

<div id="order-standard_cart">

    <div class="row">

        <div class="col-md-12">

            <div class="header-lined">
                <h1>{$LANG.orderconfirmation}</h1>
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

            <p class="text-center">{$LANG.orderreceived}</p>

            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="alert alert-info order-confirmation">
                        {$LANG.ordernumberis} <span>{$ordernumber}</span>
                    </div>
                </div>
            </div>

            <p class="text-center">{$LANG.orderfinalinstructions}</p>

            {if $expressCheckoutInfo}
                <div class="alert alert-info text-center">
                    {$expressCheckoutInfo}
                </div>
            {elseif $expressCheckoutError}
                <div class="alert alert-danger text-center">
                    {$expressCheckoutError}
                </div>
            {elseif $invoiceid && !$ispaid}
                <div class="alert alert-warning text-center">
                    {$LANG.ordercompletebutnotpaid}
                    <br /><br />
                    <a href="{$WEB_ROOT}/viewinvoice.php?id={$invoiceid}" target="_blank" class="alert-link">
                        {$LANG.invoicenumber}{$invoiceid}
                    </a>
                </div>
            {/if}

            {foreach $addons_html as $addon_html}
                <div class="order-confirmation-addon-output">
                    {$addon_html}
                </div>
            {/foreach}

            {if $ispaid}
                <!-- Enter any HTML code which should be displayed when a user has completed checkout here -->
                <!-- Common uses of this include conversion and affiliate tracking scripts -->
            {/if}

            <div class="text-center">
                <a href="{$WEB_ROOT}/clientarea.php" class="btn btn-default">
                    {$LANG.orderForm.continueToClientArea}
                    &nbsp;<i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>

        </div>
    </div>
</div>
