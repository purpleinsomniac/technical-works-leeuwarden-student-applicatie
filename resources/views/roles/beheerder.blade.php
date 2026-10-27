<section id="adminProcessing" class="view hidden">
          <h1>Gegevensverwerking</h1>
          <p class="dim">Controleer ontvangen gegevens, vaste verdeling en verwerking; wijzig geen studentontwikkeling handmatig.</p>
          <div class="notice">
            De applicatie ontvangt alleen uren, bewaart eerdere berekeningen en toont de meest recente verwerkte stand. Externe koppelingen zijn nog niet
            gebouwd.
          </div>
          <div class="grid4">
            <div class="card">
              <div class="big">12 sep</div>
              <span class="dim">laatste verwerking · demo</span>
            </div>
            <div class="card">
              <div class="big">24</div>
              <span class="dim">registraties verwerkt · demo</span>
            </div>
            <div class="card">
              <div class="big">1</div>
              <span class="dim">fout ter controle · demo</span>
            </div>
            <div class="card">
              <div class="big">v1</div>
              <span class="dim">vaste verdeling · concept</span>
            </div>
          </div>
          <div class="grid2">
            <div class="card">
              <h2>Datastroom</h2>
              <div class="kv">
                <b>Ontvangst</b>
                <span>Uren uit externe bron</span>
              </div>
              <div class="kv">
                <b>Controle</b>
                <span>Herkomst, studentkoppeling en validatiestatus</span>
              </div>
              <div class="kv">
                <b>Verdeling</b>
                <span>Vooraf vastgestelde toewijzing naar leeruitkomsten</span>
              </div>
              <div class="kv">
                <b>Resultaat</b>
                <span>Nieuwe stand bewaren; historie intact laten</span>
              </div>
            </div>
            <div class="card">
              <h2>Te controleren fout · demo</h2>
              <p><b>REG-1042:</b> geen geldige leeruitkomstkoppeling aangetroffen.</p>
              <p class="dim">Registratie niet in de zichtbare voortgang opgenomen. De fout en bron blijven herleidbaar.</p>
              <button class="button" onclick="show('adminLog')">Bekijk logboek →</button>
            </div>
          </div>
        </section>

        <section id="adminLog" class="view hidden">
          <h1>Verwerkingslogboek</h1>
          <p class="dim">Herleid iedere verwerking en fout naar bron, tijdstip en toegepaste verdelingsversie.</p>
          <div class="notice">Alle logregels hieronder zijn voorbeelden. Bewaartermijn, auditvelden en toegangsrechten moeten worden vastgesteld.</div>
          <div class="card">
            <div class="simple">
              <b>12-09-2026 09:30</b>
              <span>24 gevalideerde registraties verwerkt · versie v1</span>
              <span>Voltooid</span>
            </div>
            <div class="simple">
              <b>12-09-2026 09:29</b>
              <span>REG-1042 · leeruitkomstkoppeling ontbreekt</span>
              <span>Fout</span>
            </div>
            <div class="simple">
              <b>04-09-2026 10:15</b>
              <span>Eerdere berekening bewaard · versie v1</span>
              <span>Historie</span>
            </div>
          </div>
        </section>

