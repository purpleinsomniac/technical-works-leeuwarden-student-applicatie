<section id="coachStudents" class="view hidden">
          <h1>Mijn studenten</h1>
          <p class="dim">Bekijk uitsluitend studenten die aan deze coach zijn toegewezen.</p>
          <div class="notice">Namen, resultaten en signaleringen zijn demogegevens. Toegangsrechten moeten worden vastgesteld en technisch afgedwongen.</div>
          <div class="card">
            <h2>Begeleide studenten</h2>
            <div class="simple click" onclick="show('coachStudent')">
              <b>Sam de Vries</b>
              <span>Oriëntatiefase · 342 uur (demo)</span>
              <span>2 leeruitkomsten vragen aandacht →</span>
            </div>
            <div class="simple">
              <b>Noor Bakker</b>
              <span>Verdiepingsfase · 221 uur (demo)</span>
              <span>Bijgewerkt: 12 sep (demo)</span>
            </div>
          </div>
        </section>

        <section id="coachStudent" class="view hidden">
          <div class="head">
            <div>
              <h1>Sam de Vries</h1>
              <p class="dim">Coachoverzicht: ontwikkelfase, leerdoelen, onderbouwing en gespreksgeschiedenis.</p>
            </div>
            <button class="button" onclick="show('coachStudents')">← Studenten</button>
          </div>
          <div class="notice">Demogegevens. Persoonlijke reflecties worden alleen getoond als de vastgestelde rechten dit toestaan.</div>
          <div class="grid3">
            <div class="card">
              <div class="big">60%</div>
              <span class="dim">oriëntatie · voorbeeld</span>
            </div>
            <div class="card">
              <div class="big">342 uur</div>
              <span class="dim">gevalideerd · demo</span>
            </div>
            <div class="card">
              <div class="big">12 sep 2026</div>
              <span class="dim">laatst bijgewerkt · demo</span>
            </div>
          </div>
          <div class="grid2">
            <div class="card">
              <h2>Voor gesprek</h2>
              <p><b>Vraagt aandacht:</b> samenwerken en reflecteren, lerende houding (demo).</p>
              <p><b>Persoonlijk leerdoel:</b> technische keuzes zelfstandig uitleggen.</p>
              <p><b>Onderbouwing:</b> bekijk opdrachten en uren per leeruitkomst.</p>
            </div>
            <div class="card">
              <h2>Coachnotitie</h2>
              <label class="label">Opmerking voor begeleiding</label>
              <textarea class="input" placeholder="Noteer een observatie of afspraak"></textarea>
              <button class="button" onclick="hint('Coachnotities worden in het mock-up niet opgeslagen.')">Notitie bewaren</button>
            </div>
          </div>
          <div class="card">
            <h2>Eerdere gesprekken</h2>
            <div class="kv">
              <b>4 sep 2026</b>
              <span>Installatieopdracht besproken; vervolgstap afgesproken (demo).</span>
            </div>
            <div class="kv">
              <b>22 aug 2026</b>
              <span>Leerdoel voor zelfstandiger werken vastgelegd (demo).</span>
            </div>
          </div>
        </section>

        {{-- Managementweergaven. --}}

