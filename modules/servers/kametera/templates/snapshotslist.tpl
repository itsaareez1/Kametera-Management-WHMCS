{* {if $successful}
    {include file="alert.tpl" type="success" msg="{lang key='changessavedsuccessfully'}" textcenter=true}
{/if}

{if $errormessage}
    {include file="alert.tpl" type="error" errorshtml=$errormessage}
{/if} *}
<div class="row">
    <div class="col-md-6">
        <div class="card text-center">
            {if $results|@count > 0}
                <div class="card-header bg-success" style="color: white;">Snapshot 1</div>
            {else}
                <div class="card-header">Snapshot 1</div>
            {/if}
                {if $results|@count > 0}
                    {assign var=name value="-"|explode:$results[0]->name}
                <div class="card-body">
                    Name: {$name[0]}{$results[0]->id}
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" action="clientarea.php?action=productdetails">
                                <input type="hidden" name="id" value="{$serviceid}" />
                                <input type="hidden" name="modop" value="{$modop}" />
                                <input type="hidden" name="a" value="ssOneDelete" />
                                <input class="btn btn-danger" type="submit" value="Delete" />
                            </form>  
                        </div>
                        <div class="col-md-6">
                            <form method="post" action="clientarea.php?action=productdetails">
                                <input type="hidden" name="id" value="{$serviceid}" />
                                <input type="hidden" name="modop" value="{$modop}" />
                                <input type="hidden" name="a" value="ssOneRevert" />
                                <input class="btn btn-primary" type="submit" value="Revert" />
                            </form>                                               
                        </div>
                    </div> 
                </div>               
                {else}
                <div class="card-footer">
                    <form method="post" action="clientarea.php?action=productdetails">
                        <input type="hidden" name="id" value="{$serviceid}" />
                        <input type="hidden" name="modop" value="{$modop}" />
                        <input type="hidden" name="a" value="{$action}" />
                        <input class="btn btn-primary" type="submit" value="Create" />
                    </form> 
                </div>                   
                {/if}
            </div>
    </div>
    <div class="col-md-6">
        {if $results|@count > 0}
        <div class="card text-center">
                {if $results[0]->child|@count > 0}
                    <div class="card-header bg-success" style="color: white;">Snapshot 2</div>
                {else}
                    <div class="card-header">Snapshot 2</div>
                {/if}

                {if $results|@count > 0}
                    {if $results[0]->child|@count > 0}
                    {assign var=name value="-"|explode:$results[0]->child[0]->name}
                <div class="card-body">
                    Name: {$name[0]}{$results[0]->child[0]->id}
                </div> 
                    <div class="card-footer">

                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" action="clientarea.php?action=productdetails">
                                <input type="hidden" name="id" value="{$serviceid}" />
                                <input type="hidden" name="modop" value="{$modop}" />
                                <input type="hidden" name="a" value="ssTwoDelete" />
                                <input class="btn btn-danger" type="submit" value="Delete" />
                            </form>                              
                        </div>
                        <div class="col-md-6">
                            <form method="post" action="clientarea.php?action=productdetails">
                                <input type="hidden" name="id" value="{$serviceid}" />
                                <input type="hidden" name="modop" value="{$modop}" />
                                <input type="hidden" name="a" value="ssTwoRevert" />
                                <input class="btn btn-primary" type="submit" value="Revert" />
                            </form>                                               
                        </div>
                    </div>
                        
                    </div>              
                    {else}
                                    <div class="card-footer">
                        <form method="post" action="clientarea.php?action=productdetails">
                            <input type="hidden" name="id" value="{$serviceid}" />
                            <input type="hidden" name="modop" value="{$modop}" />
                            <input type="hidden" name="a" value="{$action}" />
                            <input class="btn btn-primary" type="submit" value="Create" />
                        </form>       
                        </div>               
                    {/if}       
                {/if}
            
        </div>
        {/if}
    </div>
    <div class="col-md-6">
            {if $results|@count > 0}
                {if $results[0]->child|@count > 0}
                    <div class="card text-center">
                    {if $results[0]->child[0]->child|@count > 0}
                        <div class="card-header bg-success" style="color: white;">Snapshot 3</div> 
                    {else}
                        <div class="card-header">Snapshot 3</div> 
                    {/if} 

                            {if $results|@count > 0}
                                {if $results[0]->child|@count > 0}
                                    {if $results[0]->child[0]->child|@count > 0}
                                    {assign var=name value="-"|explode:$results[0]->child[0]->child[0]->name}
                                        <div class="card-body">
                                            Name: {$name[0]}{$results[0]->child[0]->child[0]->id}
                                        </div> 
                                    <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                                        <form method="post" action="clientarea.php?action=productdetails">
                                            <input type="hidden" name="id" value="{$serviceid}" />
                                            <input type="hidden" name="modop" value="{$modop}" />
                                            <input type="hidden" name="a" value="ssThreeDelete" />
                                            <input class="btn btn-danger" type="submit" value="Delete" />
                                        </form>                         
                        </div>
                        <div class="col-md-6">
                                        <form method="post" action="clientarea.php?action=productdetails">
                                            <input type="hidden" name="id" value="{$serviceid}" />
                                            <input type="hidden" name="modop" value="{$modop}" />
                                            <input type="hidden" name="a" value="ssThreeRevert" />
                                            <input class="btn btn-primary" type="submit" value="Revert" />
                                        </form>                             
                        </div>
                    </div>


                  

                                    </div>
                                    {else}
                                    <div class="card-footer">
                                        <form method="post" action="clientarea.php?action=productdetails">
                                            <input type="hidden" name="id" value="{$serviceid}" />
                                            <input type="hidden" name="modop" value="{$modop}" />
                                            <input type="hidden" name="a" value="{$action}" />
                                            <input class="btn btn-primary" type="submit" value="Create" />
                                        </form>     
                                    </div>                 
                                    {/if} 
                                {/if}
                            {/if}   
                        
                    </div>
                {/if}
            {/if}
    </div>
    <div class="col-md-6">
                {if $results|@count > 0}
                    {if $results[0]->child|@count > 0}
                        {if $results[0]->child[0]->child|@count > 0}
                        <div class="card text-center">
                            {if $results[0]->child[0]->child[0]->child|@count > 0}
                                <div class="card-header bg-success" style="color: white;">Snapshot 4</div>
                            {else}
                                <div class="card-header">Snapshot 4</div>
                            {/if}

                {if $results|@count > 0}
                    {if $results[0]->child|@count > 0}
                        {if $results[0]->child[0]->child|@count > 0}
                            {if $results[0]->child[0]->child[0]->child|@count > 0}
                            {assign var=name value="-"|explode:$results[0]->child[0]->child[0]->child[0]->name}
                                    <div class="card-body">
                                        Name: {$name[0]}{$results[0]->child[0]->child[0]->child[0]->id}
                                    </div> 
                                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                                    <form method="post" action="clientarea.php?action=productdetails">
                                        <input type="hidden" name="id" value="{$serviceid}" />
                                        <input type="hidden" name="modop" value="{$modop}" />
                                        <input type="hidden" name="a" value="ssFourDelete" />
                                        <input class="btn btn-danger" type="submit" value="Delete" />
                                    </form>                         
                        </div>
                        <div class="col-md-6">

                                    <form method="post" action="clientarea.php?action=productdetails">
                                        <input type="hidden" name="id" value="{$serviceid}" />
                                        <input type="hidden" name="modop" value="{$modop}" />
                                        <input type="hidden" name="a" value="ssFourRevert" />
                                        <input class="btn btn-primary" type="submit" value="Revert" />
                                    </form>                          
                        </div>
                    </div>

                     

                                </div>

                            {else}
                                <div class="card-footer">
                                <form method="post" action="clientarea.php?action=productdetails">
                                    <input type="hidden" name="id" value="{$serviceid}" />
                                    <input type="hidden" name="modop" value="{$modop}" />
                                    <input type="hidden" name="a" value="{$action}" />
                                    <input class="btn btn-primary" type="submit" value="Create" />
                                </form>     
                                </div>                 
                            {/if}   
                        {/if}   
                    {/if}
                {/if}
            
        </div>
                        {/if}   
                    {/if}
                {/if}
    </div>

</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4>Caution: </h4>
                <div style="color: red;">Snapshots are automatically deleted when you perform any operation except Start, Restart and Shutdown.</div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="{$BASE_PATH_JS}/kametera.js"></script>