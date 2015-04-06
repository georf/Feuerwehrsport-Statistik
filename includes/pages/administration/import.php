<fieldset>
  <legend>Wettkampf</legend>
  <div class="row">
    <div class="col-md-2">
      <input type="radio" name="competition-type" id="competition-type-sorted" value="sorted" checked="checked"/>
      <label for="competition-type-sorted">Sortiert</label>
      <br/>
      <input type="radio" name="competition-type" id="competition-type-latest" value="latest"/>
      <label for="competition-type-latest">Letzter</label>
      <br/>
      <input type="radio" name="competition-type" id="competition-type-new" value="new"/>
      <label for="competition-type-new">Neu</label>
    </div>
    <div class="col-md-5" id="select-competitions">
      <select id="competitions" style=""></select>
      <br/><br/>
      <a id="competition-link" href=""></a><br/>
      <a id="competition-link-admin" href=""></a><br/>
      <a id="change-competition-score-type" href="">Mannschaftswertung<span>&nbsp;</span></a>
    </div>
    <div class="col-md-2" id="competition-scores">
    </div>
    <div class="col-md-1" id="competition-published">
    </div>
    <div class="col-md-3" id="create-competitions">
      <button class="add-competition">Wettkampf</button>
      <button class="add-event">Typ</button>
      <button class="add-place">Ort</button>
      <button class="add-group-score-type">Gruppen-Typ</button>
    </div>
    <div class="col-md-3" id="show-group-score-types">
      <table class="table"></table>
    </div>
  </div>
  <div class="row">
    <div class="col-md-2">
      <h4>Löschangriff</h4>
      <button class="add-discipline discipline-la-male">male</button>
      <button class="add-discipline discipline-la-female">female</button>
    </div>
    <div class="col-md-2">
      <h4>Hindernisbahn</h4>
      <button class="add-discipline discipline-hb-male">male</button>
      <button class="add-discipline discipline-hb-female">female</button>
    </div>
    <div class="col-md-2">
      <h4>Feuerwehrstafette</h4>
      <button class="add-discipline discipline-fs-male">male</button>
      <button class="add-discipline discipline-fs-female">female</button>
    </div>
    <div class="col-md-2">
      <h4>Hakenleitersteigen</h4>
      <button class="add-discipline discipline-hl-male">male</button>
      <button class="add-discipline discipline-hl-female">female</button>
    </div>
    <div class="col-md-2">
      <h4>Gruppenstafette</h4>
      <button class="add-discipline discipline-gs-female">female</button>
    </div>
  </div>
</fieldset>
<div id="disciplines">
</div>
