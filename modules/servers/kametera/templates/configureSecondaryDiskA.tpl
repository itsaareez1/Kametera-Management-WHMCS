<style>
input[id="cconfirm"] + label {
  float: left;
  margin-top: 2px;
  margin-right: 10px;
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid #bcbcbc;
  border-radius: 2px;
  cursor: pointer;
}
input[id="cconfirm"]:checked + label:after {
  position: relative;
  top: -4px;
  left: 2px;
  content: '\2714';
  font-size: 14px;
}
input[id="cconfirm"] {
  display: none;
}
</style>
<div class="card">
    <div class="card-body">
        <h3 class="card-title">Configure Secondary Disk A</h3>
        <ul>
          <li>
            If you don't have Disk A, this operation will attach a new Disk A in your server.
          </li>
          <li>
            If you already have Disk A, this operation will resize Disk A.
          </li>
          <li>
            This operation may take upto 5 minutes.
          </li>
        </ul>
        <hr>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
            Proceed
        </button>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Caution</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div>This operation will remove all existing snapshots and create new one.</div>
        <br/>
        <input type="checkbox" id="cconfirm"> Do you want to proceed?
        <label for="cconfirm"></label>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="yes">Yes</button>
        <input type="hidden" name="url" id="url" value="{$url}"
      </div>
    </div>
  </div>
</div>
<input type="hidden" name="coperation" id="coperation" value={$coperation}>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="{$BASE_PATH_JS}/kametera.js"></script>