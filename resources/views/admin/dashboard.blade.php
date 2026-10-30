@extends('layouts.application')

@section('role-content')
{{-- Displays a summary of the key counts in the application database. --}}
<section id="adminProcessing" class="view hidden admin-dashboard">
  <header class="admin-hero">
    <div class="admin-hero-copy">
      <span class="admin-eyebrow">Beheeromgeving</span>
      <h1>Alles op één plek</h1>
      <p>Houd overzicht over studenten, begeleiding en de gegevens in de leeromgeving.</p>
    </div>
    <button class="button admin-hero-action" type="button" onclick="show('adminLog')">
      Bekijk het logboek <span aria-hidden="true">→</span>
    </button>
  </header>

  {{-- Displays the four key metrics as separate cards. --}}
  <section class="admin-stats" aria-label="Databaseoverzicht">
    <article class="admin-stat admin-stat-users">
      <span class="admin-stat-icon" aria-hidden="true">◉</span>
      <span class="admin-stat-label">Gebruikers</span>
      <strong>{{ $roleData['userCount'] }}</strong>
      <span class="admin-stat-note">Accounts in de leeromgeving</span>
    </article>
    <article class="admin-stat admin-stat-students">
      <span class="admin-stat-icon" aria-hidden="true">⌂</span>
      <span class="admin-stat-label">Studenten</span>
      <strong>{{ $roleData['studentCount'] }}</strong>
      <span class="admin-stat-note">Studentaccounts</span>
    </article>
    <article class="admin-stat admin-stat-outcomes">
      <span class="admin-stat-icon" aria-hidden="true">◎</span>
      <span class="admin-stat-label">Leeruitkomsten</span>
      <strong>{{ $roleData['outcomeCount'] }}</strong>
      <span class="admin-stat-note">Beschikbaar in de applicatie</span>
    </article>
    <article class="admin-stat admin-stat-progress">
      <span class="admin-stat-icon" aria-hidden="true">↗</span>
      <span class="admin-stat-label">Voortgangsregels</span>
      <strong>{{ $roleData['progressCount'] }}</strong>
      <span class="admin-stat-note">Geregistreerde voortgang</span>
    </article>
  </section>

  <div class="admin-section-heading">
    <div>
      <span class="admin-eyebrow">Inzicht</span>
      <h2>Applicatiegegevens</h2>
    </div>
    <span class="admin-updated">Actuele databasegegevens</span>
  </div>

  <div class="admin-content-grid">
    <section class="admin-panel">
      <div class="admin-panel-heading">
        <span class="admin-panel-icon" aria-hidden="true">▤</span>
        <div>
          <h3>Onderwijsgegevens</h3>
          <p>Gegevens die de leeromgeving ondersteunen.</p>
        </div>
      </div>
      <div class="admin-detail">
        <span>Opdrachten</span>
        <strong>{{ $roleData['assignmentCount'] }}</strong>
      </div>
      {{-- Shows a clear message when no progress update has been recorded. --}}
      <div class="admin-detail">
        <span>Laatste voortgangswijziging</span>
        <strong>{{ $roleData['lastProgressUpdate'] ?? 'Nog niet bijgewerkt' }}</strong>
      </div>
    </section>

    <section class="admin-panel admin-roles-panel">
      <div class="admin-panel-heading">
        <span class="admin-panel-icon" aria-hidden="true">♧</span>
        <div>
          <h3>Rolverdeling</h3>
          <p>De omgeving is ingericht voor begeleiding en studie.</p>
        </div>
      </div>
      <div class="admin-role-list">
        <div class="admin-role-row">
          <span class="admin-role-dot admin-role-student" aria-hidden="true"></span>
          <div><strong>Student</strong><span>Werkt aan de eigen ontwikkeling</span></div>
        </div>
        <div class="admin-role-row">
          <span class="admin-role-dot admin-role-coach" aria-hidden="true"></span>
          <div><strong>Coach / SLB'er</strong><span>Begeleidt studenten en volgt voortgang</span></div>
        </div>
        <div class="admin-role-row">
          <span class="admin-role-dot admin-role-teamleader" aria-hidden="true"></span>
          <div><strong>Teamleider</strong><span>Heeft overzicht op teamniveau</span></div>
        </div>
        <div class="admin-role-row">
          <span class="admin-role-dot admin-role-admin" aria-hidden="true"></span>
          <div><strong>Beheerder</strong><span>Beheert de applicatieomgeving</span></div>
        </div>
      </div>
    </section>
  </div>

  <section class="admin-processing-note">
    <span class="admin-processing-icon" aria-hidden="true">i</span>
    <div>
      <strong>Externe verwerking nog niet gekoppeld</strong>
      {{-- Describes the current data sources and clarifies that no external integrations are active. --}}
      <p>Er is nog geen urenimport of auditlog gekoppeld. De getoonde gegevens komen uit Laravel en MySQL.</p>
    </div>
  </section>
</section>

{{-- Placeholder for the processing log until an audit log is available. --}}
<section id="adminLog" class="view hidden admin-dashboard">
  <header class="admin-hero admin-log-hero">
    <div class="admin-hero-copy">
      <span class="admin-eyebrow">Beheeromgeving</span>
      <h1>Verwerkingslogboek</h1>
      <p>Bekijk hier later welke beheerhandelingen zijn uitgevoerd.</p>
    </div>
    <button class="button admin-hero-action" type="button" onclick="show('adminProcessing')">
      <span aria-hidden="true">←</span> Terug naar overzicht
    </button>
  </header>

  <section class="admin-empty-state">
    <span class="admin-empty-icon" aria-hidden="true">◷</span>
    <span class="admin-eyebrow">Nog niet beschikbaar</span>
    <h2>Er zijn nog geen logregels</h2>
    {{-- Explains why no log entries are displayed and what must be established before the log can be populated. --}}
    <p>Er bestaat nog geen auditlogtabel. Daarom worden hier geen logregels getoond. Een auditlog kan later worden toegevoegd zodra de gewenste handelingen en bewaartermijnen zijn vastgesteld.</p>
  </section>
</section>
@endsection
