{{-- Displays a summary of the key counts in the application database. --}}
<section id="adminProcessing" class="view hidden">
  <div class="head">
    <div>
      <h1>Databaseoverzicht</h1>
      <p class="dim">Controleer welke gegevens momenteel in de applicatiedatabase staan.</p>
    </div>
  </div>
  {{-- Displays the four key metrics as separate cards. --}}
  <div class="grid4">
    <div class="card"><div class="big">{{ $roleData['userCount'] }}</div><span class="dim">gebruikers</span></div>
    <div class="card"><div class="big">{{ $roleData['studentCount'] }}</div><span class="dim">studenten</span></div>
    <div class="card"><div class="big">{{ $roleData['outcomeCount'] }}</div><span class="dim">leeruitkomsten</span></div>
    <div class="card"><div class="big">{{ $roleData['progressCount'] }}</div><span class="dim">voortgangsregels</span></div>
  </div>
  {{-- Shows additional education data and information about external processing. --}}
  <div class="grid2">
    <div class="card">
      <h2>Onderwijsgegevens</h2>
      <div class="kv"><b>Opdrachten</b><span>{{ $roleData['assignmentCount'] }}</span></div>
      {{-- Shows a clear message when no progress update has been recorded. --}}
      <div class="kv"><b>Laatste voortgangswijziging</b><span>{{ $roleData['lastProgressUpdate'] ?? 'Nog niet bijgewerkt' }}</span></div>
    </div>
    <div class="card">
      <h2>Externe verwerking</h2>
      {{-- Describes the current data sources and clarifies that no external integrations are active. --}}
      <p class="dim">Er is nog geen externe urenimport of auditlog gekoppeld. De huidige gegevens komen uit Laravel en MySQL.</p>
    </div>
  </div>
</section>

{{-- Placeholder for the processing log until an audit log is available. --}}
<section id="adminLog" class="view hidden">
  <h1>Verwerkingslogboek</h1>
  {{-- Explains why no log entries are displayed. --}}
  <div class="notice">Er bestaat nog geen auditlogtabel. Daarom worden hier geen verzonnen logregels getoond.</div>
  <div class="card">
    <h2>Nog geen logregels</h2>
    {{-- Explains what must be established before the log can be populated. --}}
    <p class="dim">Voeg later een aparte auditlogmigratie toe wanneer is vastgesteld welke handelingen en bewaartermijnen nodig zijn.</p>
  </div>
</section>
