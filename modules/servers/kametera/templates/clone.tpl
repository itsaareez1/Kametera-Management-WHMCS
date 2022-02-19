<div class="card">
    <div class="card-body">
        <h3 class="card-title">Create Clone Server</h3>
        <div style="color: red;">A server with same specifications and resources will be created.</div>
        <hr>
        <a class="btn btn-primary" href={$url}>Proceed</a>
    </div>
</div>
<input type="hidden" name="datacenter" id="datacenter" value={$datacenter}>
<input type="hidden" name="cpu" id="cpu" value={$cpu}>
<input type="hidden" name="ram" id="ram" value={$ram}>
<input type="hidden" name="traffic" id="traffic" value={$traffic}>
<input type="hidden" name="network" id="network" value={$network}>
<input type="hidden" name="serverid" id="serverid" value={$serverid}>
<input type="hidden" name="disk0" id="disk0" value={$disk0}>
<input type="hidden" name="disk1" id="disk1" value={$disk1}>
<input type="hidden" name="disk2" id="disk2" value={$disk2}>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="{$BASE_PATH_JS}/kametera.js"></script>