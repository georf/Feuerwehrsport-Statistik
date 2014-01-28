<fieldset>
  <legend>Wettkampf</legend>
  <div class="row">
    <div class="three columns">
      <input type="radio" name="competition-type" id="competition-type-sorted" value="sorted" checked="checked"/>
      <label for="competition-type-sorted">Sortiert</label>
      <br/>
      <input type="radio" name="competition-type" id="competition-type-latest" value="latest"/>
      <label for="competition-type-latest">Letzter</label>
      <br/>
      <input type="radio" name="competition-type" id="competition-type-new" value="new"/>
      <label for="competition-type-new">Neu</label>
    </div>
    <div class="six columns" id="select-competitions">
      <select id="competitions" style=""></select>
      <br/><br/>
      <a id="competition-link" href=""></a>
    </div>
    <div class="five columns" id="competition-scores">
    </div>
    <div class="five columns" id="create-competitions">
      <button class="add-competition">Wettkampf</button>
      <button class="add-event">Typ</button>
      <button class="add-place">Ort</button>
    </div>
  </div>
  <div class="row">
    <div class="three columns">
      <h4>Löschangriff</h4>
      <button class="add-discipline discipline-la-male">male</button>
      <button class="add-discipline discipline-la-female">female</button>
    </div>
    <div class="three columns">
      <h4>Hindernisbahn</h4>
      <button class="add-discipline discipline-hb-male">male</button>
      <button class="add-discipline discipline-hb-female">female</button>
    </div>
    <div class="three columns">
      <h4>Feuerwehrstafette</h4>
      <button class="add-discipline discipline-fs-male">male</button>
      <button class="add-discipline discipline-fs-female">female</button>
    </div>
    <div class="three columns">
      <h4>Hakenleitersteigen</h4>
      <button class="add-discipline discipline-hl-male">male</button>
    </div>
    <div class="three columns">
      <h4>Gruppenstafette</h4>
      <button class="add-discipline discipline-gs-female">female</button>
    </div>
  </div>
</fieldset>
<div id="disciplines">
</div>
