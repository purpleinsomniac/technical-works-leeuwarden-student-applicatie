@extends('layouts.application')

@section('role-content')
{{-- Each section is a separate dashboard view; the role navigation shows one at a time. --}}
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

{{-- Account management: create accounts, filter the list, and edit or delete existing users. --}}
<section id="adminUsers" class="view hidden admin-dashboard">
  <header class="admin-hero">
    <div class="admin-hero-copy">
      <span class="admin-eyebrow">Beheeromgeving</span>
      <h1>Gebruikers</h1>
      <p>Maak accounts aan, wijzig gegevens en beheer accountrollen.</p>
    </div>
    {{-- The query flag keeps the form open after validation redirects back to this page. --}}
    <button class="button admin-hero-action" id="toggleAdminUserForm" type="button" aria-controls="createAdminUser" aria-expanded="{{ request()->boolean('new') ? 'true' : 'false' }}" onclick="toggleAdminUserForm()">Account aanmaken <span aria-hidden="true">+</span></button>
  </header>

  {{-- Give administrators a visible result after a save or a validation error. --}}
  @if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
  @if ($errors->any())
    <div class="notice" role="alert">
      <p>De wijziging is niet opgeslagen. Controleer de volgende melding(en):</p>
      <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- Keep account creation collapsed until requested, or reopened after a failed submission. --}}
  <section class="admin-panel admin-user-create" id="createAdminUser" @if (!request()->boolean('new')) hidden @endif>
    <div class="admin-panel-heading">
      <div><h2>Account aanmaken</h2><p>Vul de gegevens in en kies het accounttype.</p></div>
    </div>
    <form action="{{ route('admin.users.store', ['page' => 'gebruikers', 'new' => 1]) }}" method="post" class="admin-user-form">
      {{-- Protect the account-creation request against cross-site form submissions. --}}
      @csrf
      {{-- Restore non-sensitive fields after validation; passwords must be entered again. --}}
      <div class="admin-user-fields">
        <div><label for="new-user-name">Naam</label><input class="input" id="new-user-name" name="naam" value="{{ old('naam') }}" maxlength="255" required></div>
        <div><label for="new-user-email">E-mailadres</label><input class="input" id="new-user-email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required></div>
        <div>
          <label for="new-user-role">Accounttype</label>
          {{-- Role options come from the roles supplied by the administrator controller. --}}
          <select class="input" id="new-user-role" name="rol" required>
            @foreach ($roleData['roles'] as $role)<option value="{{ $role->naam }}" @selected(old('rol', 'student') === $role->naam)>{{ ucfirst($role->naam) }}</option>@endforeach
          </select>
        </div>
        <div><label for="new-user-password">Tijdelijk wachtwoord</label><input class="input" id="new-user-password" name="password" type="password" autocomplete="new-password" minlength="8" required></div>
        <div><label for="new-user-password-confirmation">Herhaal wachtwoord</label><input class="input" id="new-user-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required></div>
      </div>
      <button class="button dark" type="submit">Account aanmaken</button>
    </form>
  </section>

  {{-- The table is rendered server-side; the role selector filters its rows in the browser. --}}
  <section class="admin-panel admin-user-list">
    <div class="admin-panel-heading">
      <div><h2>Alle accounts</h2><p id="adminUsersCount" aria-live="polite">{{ $roleData['users']->count() }} gebruikers</p></div>
    </div>
    {{-- Option values match each row's data-user-role attribute for the JavaScript filter. --}}
    <div class="admin-user-filter">
      <label for="adminUserRoleFilter">Filter op accounttype</label>
      <select class="input" id="adminUserRoleFilter">
        <option value="all">Alle accounttypen</option>
        @foreach ($roleData['roles'] as $role)<option value="{{ $role->naam }}">{{ ucfirst($role->naam) }}</option>@endforeach
      </select>
    </div>
    <div class="admin-users-table-wrap">
      <table class="admin-users-table">
        <thead><tr><th>Naam</th><th>E-mailadres</th><th>Accounttype</th><th>Beheren</th></tr></thead>
        <tbody>
          {{-- Show a data-driven row for each account so its existing role is available to the filter. --}}
          @forelse ($roleData['users'] as $managedUser)
            <tr data-user-role="{{ $managedUser->primaryRole?->naam ?? $managedUser->rol }}">
              <td>{{ $managedUser->naam }}</td>
              <td><a href="mailto:{{ $managedUser->email }}">{{ $managedUser->email }}</a></td>
              <td>{{ ucfirst($managedUser->primaryRole?->naam ?? $managedUser->rol) }}</td>
              <td>
                {{-- Keep each edit form beside its user to avoid mixing account identifiers. --}}
                <details class="admin-user-edit">
                  <summary class="button">Wijzigen</summary>
                  <form action="{{ route('admin.users.update', ['user' => $managedUser, 'page' => 'gebruikers']) }}" method="post" class="admin-user-form">
                    @csrf
                    {{-- Laravel interprets this form as an update while the browser submits POST. --}}
                    @method('PUT')
                    <div class="admin-user-fields">
                      <div><label for="user-{{ $managedUser->id }}-name">Naam</label><input class="input" id="user-{{ $managedUser->id }}-name" name="naam" value="{{ $managedUser->naam }}" maxlength="255" required></div>
                      <div><label for="user-{{ $managedUser->id }}-email">E-mailadres</label><input class="input" id="user-{{ $managedUser->id }}-email" name="email" type="email" value="{{ $managedUser->email }}" maxlength="255" required></div>
                      <div>
                        <label for="user-{{ $managedUser->id }}-role">Accounttype</label>
                        <select class="input" id="user-{{ $managedUser->id }}-role" name="rol" required>
                          @foreach ($roleData['roles'] as $role)<option value="{{ $role->naam }}" @selected(($managedUser->primaryRole?->naam ?? $managedUser->rol) === $role->naam)>{{ ucfirst($role->naam) }}</option>@endforeach
                        </select>
                      </div>
                      <div><label for="user-{{ $managedUser->id }}-password">Nieuw wachtwoord (optioneel)</label><input class="input" id="user-{{ $managedUser->id }}-password" name="password" type="password" autocomplete="new-password" minlength="8"></div>
                      <div><label for="user-{{ $managedUser->id }}-password-confirmation">Herhaal nieuw wachtwoord</label><input class="input" id="user-{{ $managedUser->id }}-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8"></div>
                    </div>
                    <button class="button dark" type="submit">Wijzigingen opslaan</button>
                  </form>
                </details>
                {{-- Warn that deleting a user also removes their linked learning records. --}}
                <form action="{{ route('admin.users.destroy', ['user' => $managedUser, 'page' => 'gebruikers']) }}" method="post" onsubmit="return confirm('Weet je zeker dat je dit account wilt verwijderen? Gekoppelde voortgang, opdrachten, reflecties en feedback worden ook verwijderd.')">
                  @csrf
                  @method('DELETE')
                  <button class="button admin-user-delete" type="submit">Verwijderen</button>
                </form>
              </td>
            </tr>
          @empty
            <tr data-user-empty><td colspan="4">Er zijn nog geen accounts.</td></tr>
          @endforelse
          {{-- The filter reveals this row when accounts exist but none match the chosen role. --}}
          <tr id="adminUsersNoMatches" hidden><td colspan="4">Geen gebruikers gevonden voor dit accounttype.</td></tr>
        </tbody>
      </table>
    </div>
  </section>
</section>

{{-- Shows learning outcomes as sorted tiles and keeps their create/edit controls on a dedicated page. --}}
<section id="adminLearningOutcomes" class="view hidden admin-dashboard">
  <header class="admin-hero">
    <div class="admin-hero-copy">
      <span class="admin-eyebrow">Beheeromgeving</span>
      <h1>Leeruitkomsten</h1>
      <p>Bekijk leeruitkomsten en wijzig alleen de normwaarden, gesorteerd per studiefase en code.</p>
    </div>
  </header>
  {{-- Surface successful edits and validation failures on this management page. --}}
  @if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
  @if ($errors->any())
    <div class="notice" role="alert">
      <p>De wijzigingen zijn niet opgeslagen. Controleer de volgende velden:</p>
      <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- The controller orders tiles by study phase and then code for predictable scanning. --}}
  <div class="admin-outcome-tiles">
    @forelse ($roleData['learningOutcomes'] as $outcome)
      <article class="admin-panel admin-outcome-tile">
        <span class="admin-eyebrow">{{ $outcome->studyPhase->naam }}</span>
        <h2>{{ $outcome->code }}</h2>
        <h3>{{ $outcome->titel }}</h3>
        <p>{{ $outcome->omschrijving }}</p>
        <div class="admin-detail"><span>Type</span><strong>{{ $outcome->type ?: 'Niet ingesteld' }}</strong></div>
        {{-- Escape administrator-entered text before preserving its line breaks for display. --}}
        <div class="admin-detail"><span>Normwaarden</span><strong>{!! nl2br(e($outcome->normwaarden ?? 'Niet ingesteld')) !!}</strong></div>

        {{-- Only normwaarden can be changed; all other outcome details are read-only here. --}}
        <details class="admin-outcome-edit">
          <summary class="button">Normwaarden wijzigen</summary>
          <form action="{{ route('admin.learning-outcomes.update', ['learningOutcome' => $outcome, 'page' => 'leeruitkomsten']) }}" method="post">
            @csrf
            @method('PUT')
            <label for="outcome-{{ $outcome->id }}-values">Normwaarden (optioneel)</label><textarea class="input" id="outcome-{{ $outcome->id }}-values" name="normwaarden" rows="3">{{ $outcome->normwaarden }}</textarea>
            <button class="button dark" type="submit">Normwaarden opslaan</button>
          </form>
        </details>
      </article>
    {{-- Keep the page useful when the database has no outcomes yet. --}}
    @empty
      <p class="dim">Er zijn nog geen leeruitkomsten om te beheren.</p>
    @endforelse
  </div>
</section>

{{-- Placeholder for the learning-activities page until its contents are defined. --}}
<section id="adminLearningActivities" class="view hidden admin-dashboard">
  <header class="admin-hero">
    <div class="admin-hero-copy">
      <span class="admin-eyebrow">Beheeromgeving</span>
      <h1>Leeractiviteiten</h1>
      <p>De inhoud van deze pagina wordt later bepaald.</p>
    </div>
  </header>
  <section class="admin-empty-state">
    <span class="admin-empty-icon" aria-hidden="true">▤</span>
    <span class="admin-eyebrow">Binnenkort beschikbaar</span>
    <h2>Leeractiviteiten</h2>
    <p>Deze pagina is alvast toegevoegd. De inhoud volgt zodra die is afgestemd.</p>
  </section>
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
{{-- this is the blade template for the admin dashboard --}}