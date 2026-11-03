@extends('layouts.application')

@section('role-content')
<section id="adminProcessing" class="view hidden admin-dashboard">
  <header class="admin-hero">
    <div class="admin-hero-copy">
      <span class="admin-eyebrow">Beheeromgeving</span>
      <h1>Alles op één plek</h1>
      <p>Houd overzicht over studenten, begeleiding en de gegevens in de leeromgeving.</p>
    </div>
    <button class="button admin-hero-action" type="button" onclick="show('adminLog')">Bekijk het logboek <span aria-hidden="true">→</span></button>
  </header>
  {{-- Shows the database totals supplied by the administrator dashboard controller. --}}
  <section class="admin-stats" aria-label="Databaseoverzicht">
    <article class="admin-stat admin-stat-users"><span class="admin-stat-icon" aria-hidden="true">◉</span><span class="admin-stat-label">Gebruikers</span><strong>{{ $roleData['userCount'] }}</strong><span class="admin-stat-note">Accounts in de leeromgeving</span></article>
    <article class="admin-stat admin-stat-students"><span class="admin-stat-icon" aria-hidden="true">⌂</span><span class="admin-stat-label">Studenten</span><strong>{{ $roleData['studentCount'] }}</strong><span class="admin-stat-note">Studentaccounts</span></article>
    <article class="admin-stat admin-stat-outcomes"><span class="admin-stat-icon" aria-hidden="true">◎</span><span class="admin-stat-label">Leeruitkomsten</span><strong>{{ $roleData['outcomeCount'] }}</strong><span class="admin-stat-note">Beschikbaar in de applicatie</span></article>
    <article class="admin-stat admin-stat-progress"><span class="admin-stat-icon" aria-hidden="true">↗</span><span class="admin-stat-label">Voortgangsregels</span><strong>{{ $roleData['progressCount'] }}</strong><span class="admin-stat-note">Geregistreerde voortgang</span></article>
  </section>
  <div class="admin-section-heading">
    <div><span class="admin-eyebrow">Inzicht</span><h2>Applicatiegegevens</h2></div>
    <span class="admin-updated">Actuele databasegegevens</span>
  </div>
  <div class="admin-content-grid">
    <section class="admin-panel">
      <div class="admin-panel-heading"><span class="admin-panel-icon" aria-hidden="true">▤</span><div><h3>Onderwijsgegevens</h3><p>Gegevens die de leeromgeving ondersteunen.</p></div></div>
      <div class="admin-detail"><span>Opdrachten</span><strong>{{ $roleData['assignmentCount'] }}</strong></div>
      {{-- The fallback keeps the summary readable before any progress has been updated. --}}
      <div class="admin-detail"><span>Laatste voortgangswijziging</span><strong>{{ $roleData['lastProgressUpdate'] ?? 'Nog niet bijgewerkt' }}</strong></div>
    </section>

    {{-- This list describes the available roles; its entries are not live database counts. --}}
    <section class="admin-panel admin-roles-panel">
      <div class="admin-panel-heading"><span class="admin-panel-icon" aria-hidden="true">♧</span><div><h3>Rolverdeling</h3><p>De omgeving is ingericht voor begeleiding en studie.</p></div></div>
      <div class="admin-role-list">
        <div class="admin-role-row"><span class="admin-role-dot admin-role-student" aria-hidden="true"></span><div><strong>Student</strong><span>Werkt aan de eigen ontwikkeling</span></div></div>
        <div class="admin-role-row"><span class="admin-role-dot admin-role-coach" aria-hidden="true"></span><div><strong>Coach / SLB'er</strong><span>Begeleidt studenten en volgt voortgang</span></div></div>
        <div class="admin-role-row"><span class="admin-role-dot admin-role-teamleader" aria-hidden="true"></span><div><strong>Teamleider</strong><span>Heeft overzicht op teamniveau</span></div></div>
        <div class="admin-role-row"><span class="admin-role-dot admin-role-admin" aria-hidden="true"></span><div><strong>Beheerder</strong><span>Beheert de applicatieomgeving</span></div></div>
      </div>
    </section>
  </div>
  <section class="admin-processing-note">
    <span class="admin-processing-icon" aria-hidden="true">i</span>
    <div><strong>Externe verwerking nog niet gekoppeld</strong><p>Er is nog geen urenimport of auditlog gekoppeld. De getoonde gegevens komen uit Laravel en MySQL.</p></div>
  </section>
</section>

{{-- Shows learning outcomes as sorted tiles and keeps their create/edit controls on a dedicated page. --}}
<section id="adminLearningOutcomes" class="view hidden admin-dashboard">
  <header class="admin-hero">
    <div class="admin-hero-copy">
      <span class="admin-eyebrow">Beheeromgeving</span>
      <h1>Leeruitkomsten</h1>
      <p>Bekijk en beheer leeruitkomsten, gesorteerd per studiefase en code.</p>
    </div>
    <button class="button admin-hero-action" id="toggleLearningOutcomeForm" type="button" aria-controls="createLearningOutcome" aria-expanded="{{ request()->boolean('new') ? 'true' : 'false' }}" onclick="toggleLearningOutcomeForm()">Leeruitkomst toevoegen <span aria-hidden="true">+</span></button>
  </header>
  {{-- Surface successful saves and validation failures on this management page. --}}
  @if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
  @if ($errors->any())
    <div class="notice" role="alert">
      <p>De leeruitkomst is niet opgeslagen. Controleer de volgende velden:</p>
      <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- The query flag keeps the form open after redirects that return validation errors. --}}
  <section class="admin-panel admin-outcome-create" id="createLearningOutcome" aria-labelledby="create-learning-outcome" @if (!request()->boolean('new')) hidden @endif>
    <div class="admin-panel-heading"><div><h2 id="create-learning-outcome">Nieuwe leeruitkomst</h2><p>Vul de gegevens in om een leeruitkomst toe te voegen.</p></div></div>
    {{-- Outcomes require an existing study phase to satisfy their database relationship. --}}
    @if ($roleData['studyPhases']->isEmpty())
      <p class="dim">Voeg eerst een studiefase toe voordat je een leeruitkomst kunt aanmaken.</p>
    @else
      <form action="{{ route('admin.learning-outcomes.store', ['page' => 'leeruitkomsten', 'new' => 1]) }}" method="post">
        @csrf
        {{-- old() restores submitted values when validation sends the user back. --}}
        <label for="new-outcome-phase">Studiefase</label>
        <select class="input" id="new-outcome-phase" name="studiefase_id" required>
          <option value="">Selecteer een studiefase</option>
          @foreach ($roleData['studyPhases'] as $phase)<option value="{{ $phase->id }}" @selected(old('studiefase_id') == $phase->id)>{{ $phase->naam }}</option>@endforeach
        </select>
        <label for="new-outcome-code">Code</label><input class="input" id="new-outcome-code" name="code" value="{{ old('code') }}" maxlength="100" required>
        <label for="new-outcome-title">Titel</label><input class="input" id="new-outcome-title" name="titel" value="{{ old('titel') }}" maxlength="255" required>
        <label for="new-outcome-type">Type (optioneel)</label><input class="input" id="new-outcome-type" name="type" value="{{ old('type') }}" maxlength="45">
        <label for="new-outcome-description">Omschrijving</label><textarea class="input" id="new-outcome-description" name="omschrijving" required>{{ old('omschrijving') }}</textarea>
        <label for="new-outcome-values">Normwaarden (optioneel)</label><textarea class="input" id="new-outcome-values" name="normwaarden" rows="3">{{ old('normwaarden') }}</textarea>
        <button class="button dark" type="submit">Opslaan</button>
      </form>
    @endif
  </section>
  {{-- The controller orders tiles by study phase and then code for predictable scanning. --}}
  <div class="admin-outcome-tiles">
    @forelse ($roleData['learningOutcomes'] as $outcome)
      <article class="admin-panel admin-outcome-tile">
        <span class="admin-eyebrow">{{ $outcome->studyPhase->naam }}</span>
        <h2>{{ $outcome->code }}</h2>
        <h3>{{ $outcome->titel }}</h3>
        <p>{{ $outcome->omschrijving }}</p>
        <div class="admin-detail"><span>Type</span><strong>{{ $outcome->type ?: 'Niet ingesteld' }}</strong></div>
        <div class="admin-detail"><span>Normwaarden</span><strong>{!! nl2br(e($outcome->normwaarden ?? 'Niet ingesteld')) !!}</strong></div>

        {{-- Each tile owns its edit form so submitted values stay tied to that outcome. --}}
        <details class="admin-outcome-edit">
          <summary class="button">Wijzigen</summary>
          <form action="{{ route('admin.learning-outcomes.update', ['learningOutcome' => $outcome, 'page' => 'leeruitkomsten']) }}" method="post">
            @csrf
            @method('PUT')
            <label for="outcome-{{ $outcome->id }}-phase">Studiefase</label>
            <select class="input" id="outcome-{{ $outcome->id }}-phase" name="studiefase_id" required>
              @foreach ($roleData['studyPhases'] as $phase)<option value="{{ $phase->id }}" @selected($outcome->studiefase_id === $phase->id)>{{ $phase->naam }}</option>@endforeach
            </select>
            <label for="outcome-{{ $outcome->id }}-code">Code</label><input class="input" id="outcome-{{ $outcome->id }}-code" name="code" value="{{ $outcome->code }}" maxlength="100" required>
            <label for="outcome-{{ $outcome->id }}-title">Titel</label><input class="input" id="outcome-{{ $outcome->id }}-title" name="titel" value="{{ $outcome->titel }}" maxlength="255" required>
            <label for="outcome-{{ $outcome->id }}-type">Type (optioneel)</label><input class="input" id="outcome-{{ $outcome->id }}-type" name="type" value="{{ $outcome->type }}" maxlength="45">
            <label for="outcome-{{ $outcome->id }}-description">Omschrijving</label><textarea class="input" id="outcome-{{ $outcome->id }}-description" name="omschrijving" required>{{ $outcome->omschrijving }}</textarea>
            <label for="outcome-{{ $outcome->id }}-values">Normwaarden (optioneel)</label><textarea class="input" id="outcome-{{ $outcome->id }}-values" name="normwaarden" rows="3">{{ $outcome->normwaarden }}</textarea>
            <button class="button dark" type="submit">Wijzigingen opslaan</button>
          </form>
        </details>

        {{-- Warn before deleting because the database cascades to dependent learning data. --}}
        <form action="{{ route('admin.learning-outcomes.destroy', ['learningOutcome' => $outcome, 'page' => 'leeruitkomsten']) }}" method="post" onsubmit="return confirm('Weet je zeker dat je deze leeruitkomst wilt verwijderen? Gekoppelde voortgang, opdrachtkoppelingen, reflecties en feedback worden ook verwijderd.')">
          @csrf
          @method('DELETE')
          <button class="button" type="submit">Verwijderen</button>
        </form>
      </article>
    {{-- Keep the page useful when the database has no outcomes yet. --}}
    @empty
      <p class="dim">Er zijn nog geen leeruitkomsten om te beheren.</p>
    @endforelse
  </div>
</section>

{{-- The log page is an honest placeholder until an audit-log table is implemented. --}}
<section id="adminLog" class="view hidden admin-dashboard">
  <header class="admin-hero admin-log-hero">
    <div class="admin-hero-copy"><span class="admin-eyebrow">Beheeromgeving</span><h1>Verwerkingslogboek</h1><p>Bekijk hier later welke beheerhandelingen zijn uitgevoerd.</p></div>
    <button class="button admin-hero-action" type="button" onclick="show('adminProcessing')"><span aria-hidden="true">←</span> Terug naar overzicht</button>
  </header>
  <section class="admin-empty-state">
    <span class="admin-empty-icon" aria-hidden="true">◷</span><span class="admin-eyebrow">Nog niet beschikbaar</span>
    <h2>Er zijn nog geen logregels</h2>
    <p>Er bestaat nog geen auditlogtabel. Daarom worden hier geen logregels getoond. Een auditlog kan later worden toegevoegd zodra de gewenste handelingen en bewaartermijnen zijn vastgesteld.</p>
  </section>
</section>
@endsection
