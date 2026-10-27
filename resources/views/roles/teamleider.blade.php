<section id="managerGroups" class="view hidden">
          <h1>Groepsoverzicht</h1>
          <p class="dim">Monitor ontwikkeling per klas, leerjaar en opleiding binnen de eigen rechten.</p>
          <div class="notice">Groepscijfers, trends en studenten zijn demogegevens. Rapportagedefinities en privacygrenzen zijn nog te bepalen.</div>
          <div class="filter">
            <label class="label"
              >Opleiding<select class="input">
                <option>Alle opleidingen</option>
              </select>
            </label>
            <label class="label"
              >Leerjaar<select class="input">
                <option>Alle leerjaren</option>
                <option>1</option>
                <option>2</option>
              </select>
            </label>
            <label class="label"
              >Klas<select class="input">
                <option>Alle klassen</option>
                <option>Klas A</option>
              </select>
            </label>
          </div>
          <div class="grid3">
            <div class="card">
              <div class="big">28</div>
              <span class="dim">studenten · demo</span>
            </div>
            <div class="card">
              <div class="big">58%</div>
              <span class="dim">gemiddelde voorbeeldontwikkeling</span>
            </div>
            <div class="card">
              <div class="big">6</div>
              <span class="dim">leeruitkomsten met aandachtssignaal</span>
            </div>
          </div>
          <div class="card">
            <h2>Trend per WRO-fase · demo</h2>
            <div class="kv">
              <b>Oriëntatiefase</b>
              <div class="bar">
                <span style="width: 68%"> </span>
              </div>
            </div>
            <div class="kv">
              <b>Verdiepingsfase</b>
              <div class="bar">
                <span style="width: 51%"> </span>
              </div>
            </div>
            <div class="kv">
              <b>Afrondingsfase</b>
              <div class="bar">
                <span style="width: 27%"> </span>
              </div>
            </div>
          </div>
        </section>

        <section id="managerReports" class="view hidden">
          <h1>Rapportages</h1>
          <p class="dim">Historische gegevens vergelijken en gegevens exporteren binnen de toegekende rechten.</p>
          <div class="notice">Welke statistieken, anonimiseringsregels en exportformaten zijn toegestaan, moet de opdrachtgever vaststellen.</div>
          <div class="grid2">
            <div class="card">
              <h2>Periodevergelijking · demo</h2>
              <p>Vorige periode: 51% voorbeeldontwikkeling</p>
              <div class="bar">
                <span style="width: 51%"> </span>
              </div>
              <p>Huidige periode: 58% voorbeeldontwikkeling</p>
              <div class="bar">
                <span style="width: 58%"> </span>
              </div>
            </div>
            <div class="card">
              <h2>Rapportage samenstellen</h2>
              <label class="label">Uitsplitsing</label>
              <select class="input">
                <option>Per klas</option>
                <option>Per leerjaar</option>
                <option>Per opleiding</option>
              </select>
              <button class="button" onclick="hint('Rapportage-export is in dit mock-up nog niet gekoppeld.')">Exporteren (concept)</button>
            </div>
          </div>
        </section>

        {{-- Beheerweergaven voor verwerking en logging. --}}

