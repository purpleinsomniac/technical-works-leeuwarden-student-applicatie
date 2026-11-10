@extends('layouts.application')

@section('role-content')
{{-- Each section is a separate dashboard view; the role navigation shows one at a time. --}}
<section id="adminProcessing" class="view hidden admin-dashboard" data-role-section="overview">
  <header class="admin-hero">
    <div class="admin-hero-copy">
      <span class="admin-eyebrow">Beheeromgeving</span>
      <h1>Alles op één plek</h1>
      <p>Houd overzicht over studenten, begeleiding en de gegevens in de leeromgeving.</p>
    </div>
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
<section id="adminUsers" class="view hidden admin-dashboard" data-role-section="users">
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

<section id="adminClasses" class="view hidden admin-dashboard" data-role-section="classes">
  @php($classForm = old('_class_form'))
  @php($selectedClassStudentIds = old('student_ids', []))
  @php($selectedClassStudentIds = is_array($selectedClassStudentIds) ? $selectedClassStudentIds : [])
  <header class="admin-hero">
    <div class="admin-hero-copy">
      <span class="admin-eyebrow">Beheeromgeving</span>
      <h1>Klassen</h1>
      <p>Maak klassen aan en wijs per klas een docent en studenten toe. Docenten kunnen hun eigen klassenoverzicht bekijken.</p>
    </div>
    <span class="admin-class-count">{{ $roleData['classGroups']->count() }} klassen</span>
  </header>

  @if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
  @if ($errors->any())
    <div class="notice" role="alert">
      <p>De klas is niet opgeslagen. Controleer de volgende melding(en):</p>
      <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  <details class="admin-panel admin-class-create" @if ($classForm === 'create') open @endif>
    <summary class="admin-class-create-toggle"><span><strong>Nieuwe klas aanmaken</strong><small>Naam, schooljaar, docent en studenten instellen</small></span><span aria-hidden="true">+</span></summary>
    <form action="{{ route('admin.classes.store', ['page' => 'klassen']) }}" method="post" class="admin-class-form">
      @csrf
      <input type="hidden" name="_class_form" value="create">
      <div class="admin-class-fields">
        <div><label for="new-class-name">Klasnaam</label><input class="input" id="new-class-name" name="naam" value="{{ $classForm === 'create' ? old('naam') : '' }}" maxlength="120" required></div>
        <div><label for="new-class-schoolyear">Schooljaar</label><input class="input" id="new-class-schoolyear" name="schooljaar" value="{{ $classForm === 'create' ? old('schooljaar') : '' }}" maxlength="20" placeholder="Bijv. 2026-2027"></div>
        <div>
          <label for="new-class-coach">Docent / SLB'er</label>
          <select class="input" id="new-class-coach" name="coach_id">
            <option value="">Nog geen docent gekoppeld</option>
            @foreach ($roleData['coaches'] as $coach)
              <option value="{{ $coach->id }}" @selected($classForm === 'create' && (string) old('coach_id') === (string) $coach->id)>{{ $coach->naam }}</option>
            @endforeach
          </select>
        </div>
        <div class="admin-class-description-field"><label for="new-class-description">Omschrijving (optioneel)</label><textarea class="input" id="new-class-description" name="omschrijving" rows="2" maxlength="2000">{{ $classForm === 'create' ? old('omschrijving') : '' }}</textarea></div>
      </div>
      <fieldset class="admin-class-members">
        <legend>Studenten in deze klas <span>({{ $roleData['students']->count() }} beschikbaar)</span></legend>
        @if ($roleData['students']->isNotEmpty())
          <label class="admin-class-search">Zoek student<input class="input" type="search" data-class-student-search autocomplete="off" placeholder="Naam of e-mailadres"></label>
          <div class="admin-class-student-list">
            @foreach ($roleData['students'] as $student)
              <label class="admin-class-student-option" data-class-student-option="{{ mb_strtolower($student->naam.' '.$student->email) }}">
                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" @checked($classForm === 'create' && in_array($student->id, $selectedClassStudentIds))>
                <span><strong>{{ $student->naam }}</strong><small>{{ $student->email }}</small></span>
              </label>
            @endforeach
          </div>
        @else
          <p class="dim">Maak eerst studentaccounts aan via Gebruikers.</p>
        @endif
      </fieldset>
      <button class="button dark" type="submit">Klas aanmaken</button>
    </form>
  </details>

  <div class="admin-class-list">
    @forelse ($roleData['classGroups'] as $classGroup)
      <article class="admin-panel admin-class-card">
        <div class="admin-class-card-heading">
          <div><span class="admin-eyebrow">{{ $classGroup->schooljaar ?: 'Schooljaar niet ingesteld' }}</span><h2>{{ $classGroup->naam }}</h2><p>{{ $classGroup->students->count() }} {{ $classGroup->students->count() === 1 ? 'student' : 'studenten' }} · {{ $classGroup->coach?->naam ?? 'Geen docent gekoppeld' }}</p></div>
          <div class="admin-class-actions">
            <details class="admin-class-edit" @if ($classForm === 'update' && (string) old('_class_group_id') === (string) $classGroup->id) open @endif>
              <summary class="button">Klas beheren</summary>
              <form action="{{ route('admin.classes.update', ['classGroup' => $classGroup, 'page' => 'klassen']) }}" method="post" class="admin-class-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="_class_form" value="update">
                <input type="hidden" name="_class_group_id" value="{{ $classGroup->id }}">
                <div class="admin-class-fields">
                  <div><label for="class-{{ $classGroup->id }}-name">Klasnaam</label><input class="input" id="class-{{ $classGroup->id }}-name" name="naam" value="{{ $classForm === 'update' && (string) old('_class_group_id') === (string) $classGroup->id ? old('naam', $classGroup->naam) : $classGroup->naam }}" maxlength="120" required></div>
                  <div><label for="class-{{ $classGroup->id }}-schoolyear">Schooljaar</label><input class="input" id="class-{{ $classGroup->id }}-schoolyear" name="schooljaar" value="{{ $classForm === 'update' && (string) old('_class_group_id') === (string) $classGroup->id ? old('schooljaar', $classGroup->schooljaar) : $classGroup->schooljaar }}" maxlength="20" placeholder="Bijv. 2026-2027"></div>
                  <div>
                    <label for="class-{{ $classGroup->id }}-coach">Docent / SLB'er</label>
                    <select class="input" id="class-{{ $classGroup->id }}-coach" name="coach_id">
                      <option value="">Geen docent gekoppeld</option>
                      @foreach ($roleData['coaches'] as $coach)
                        <option value="{{ $coach->id }}" @selected($classForm === 'update' && (string) old('_class_group_id') === (string) $classGroup->id ? (string) old('coach_id') === (string) $coach->id : $classGroup->coach_id === $coach->id)>{{ $coach->naam }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="admin-class-description-field"><label for="class-{{ $classGroup->id }}-description">Omschrijving</label><textarea class="input" id="class-{{ $classGroup->id }}-description" name="omschrijving" rows="2" maxlength="2000">{{ $classForm === 'update' && (string) old('_class_group_id') === (string) $classGroup->id ? old('omschrijving', $classGroup->omschrijving) : $classGroup->omschrijving }}</textarea></div>
                </div>
                <fieldset class="admin-class-members">
                  <legend>Studenten in deze klas</legend>
                  <label class="admin-class-search">Zoek student<input class="input" type="search" data-class-student-search autocomplete="off" placeholder="Naam of e-mailadres"></label>
                  <div class="admin-class-student-list">
                    @foreach ($roleData['students'] as $student)
                      <label class="admin-class-student-option" data-class-student-option="{{ mb_strtolower($student->naam.' '.$student->email) }}">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" @checked($classForm === 'update' && (string) old('_class_group_id') === (string) $classGroup->id ? in_array($student->id, $selectedClassStudentIds) : $classGroup->students->contains('id', $student->id))>
                        <span><strong>{{ $student->naam }}</strong><small>{{ $student->email }}</small></span>
                      </label>
                    @endforeach
                  </div>
                </fieldset>
                <div class="admin-class-form-actions"><button class="button dark" type="submit">Klas opslaan</button></div>
              </form>
            </details>
            <form action="{{ route('admin.classes.destroy', ['classGroup' => $classGroup, 'page' => 'klassen']) }}" method="post" onsubmit="return confirm('Weet je zeker dat je deze klas wilt verwijderen? Studentaccounts en hun voortgang blijven behouden.')">
              @csrf
              @method('DELETE')
              <button class="button admin-user-delete" type="submit">Verwijderen</button>
            </form>
          </div>
        </div>
        @if ($classGroup->omschrijving)
          <p class="admin-class-description">{{ $classGroup->omschrijving }}</p>
        @endif
        <div class="admin-class-roster">
          <strong>Studenten</strong>
          @if ($classGroup->students->isNotEmpty())
            <div>@foreach ($classGroup->students as $student)<span>{{ $student->naam }}</span>@endforeach</div>
          @else
            <span class="admin-class-empty">Er zijn nog geen studenten aan deze klas gekoppeld.</span>
          @endif
        </div>
      </article>
    @empty
      <section class="admin-panel admin-class-empty-state"><h2>Nog geen klassen</h2><p>Maak een klas aan en wijs er een docent en studenten aan toe. De docent ziet daarna deze klas en de gekoppelde studenten.</p></section>
    @endforelse
  </div>
</section>

{{-- Shows learning outcomes as sorted tiles and keeps their create/edit controls on a dedicated page. --}}
<section id="adminLearningOutcomes" class="view hidden admin-dashboard" data-role-section="learningOutcomes">
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

<section id="adminApplicationSettings" class="view hidden admin-dashboard" data-role-section="applicationSettings">
  <header class="admin-hero">
    <div class="admin-hero-copy">
      <span class="admin-eyebrow">Beheeromgeving</span>
      <h1>Applicatie-instellingen</h1>
      <p>Beheer hier de algemene instellingen van de leeromgeving.</p>
    </div>
  </header>
  @if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
  @if ($errors->any())
    <div class="notice" role="alert">
      <p>De instellingen zijn niet opgeslagen. Controleer de volgende melding(en):</p>
      <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif
  <form action="{{ route('admin.application-settings.update') }}" method="post">
    @csrf
    @method('PUT')
    <div class="admin-settings-actions">
      <button class="button dark admin-settings-save" type="submit">Instellingen opslaan</button>
    </div>
    <div class="admin-content-grid">
      <section class="admin-panel">
        <div class="admin-panel-heading"><span class="admin-panel-icon" aria-hidden="true">◈</span><div><h2>Applicatie-uiterlijk</h2><p>Pas de globale accentkleur en stijl aan voor alle dashboards.</p></div></div>
        <label for="application-accent-color">Globale accentkleur</label>
        <div class="admin-theme-color">
          <input id="application-accent-color" name="application_accent_color" type="color" value="{{ old('application_accent_color', $roleData['applicationSettings']->application_accent_color) }}" required>
          <span id="applicationAccentColorValue">{{ strtoupper(old('application_accent_color', $roleData['applicationSettings']->application_accent_color)) }}</span>
        </div>
        <label for="application-style">Stijl</label>
        <select class="input" id="application-style" name="application_style" required>
          <option value="standard" @selected(old('application_style', $roleData['applicationSettings']->application_style) === 'standard')>Standaard</option>
          <option value="rounded" @selected(old('application_style', $roleData['applicationSettings']->application_style) === 'rounded')>Afgerond</option>
          <option value="sharp" @selected(old('application_style', $roleData['applicationSettings']->application_style) === 'sharp')>Strak</option>
        </select>
        <p class="dim small">De kleurkiezer toont de hexcode. Bestaande persoonlijke accentvoorkeuren blijven behouden.</p>
      </section>
      <section class="admin-panel">
        <div class="admin-panel-heading"><span class="admin-panel-icon" aria-hidden="true">↥</span><div><h2>Bestandsinstellingen</h2><p>Bepaal welke bestanden studenten bij opdrachten mogen inleveren.</p></div></div>
        <label for="max-upload-size">Maximale bestandsgrootte (MB)</label>
        <input class="input" id="max-upload-size" name="max_upload_size_mb" type="number" min="1" max="50" value="{{ old('max_upload_size_mb', $roleData['applicationSettings']->max_upload_size_mb) }}" required>
        <fieldset class="admin-upload-types">
          <legend>Toegestane bestandstypen</legend>
          @php($allowedExtensions = session()->hasOldInput('_allowed_upload_extensions_submitted') ? old('allowed_upload_extensions', []) : $roleData['applicationSettings']->allowed_upload_extensions)
          @php($allowedExtensions = is_array($allowedExtensions) ? $allowedExtensions : [])
          <input type="hidden" name="_allowed_upload_extensions_submitted" value="1">
          @foreach (['pdf' => 'PDF', 'docx' => 'Word (DOCX)', 'jpg' => 'JPG', 'jpeg' => 'JPEG', 'png' => 'PNG'] as $extension => $label)
            <label><input type="checkbox" name="allowed_upload_extensions[]" value="{{ $extension }}" @checked(in_array($extension, $allowedExtensions, true))> {{ $label }}</label>
          @endforeach
        </fieldset>
        <p class="dim small">De serverconfiguratie kan een lagere maximale uploadgrootte afdwingen.</p>
      </section>

      <section class="admin-panel">
        <div class="admin-panel-heading"><span class="admin-panel-icon" aria-hidden="true">⚙</span><div><h2>Onderhoudsmodus</h2><p>Beperk tijdelijk de toegang voor studenten, docenten en teamleiders.</p></div></div>
        <label class="admin-maintenance-option">
          <input type="checkbox" name="maintenance_mode" value="1" @checked(old('maintenance_mode', $roleData['applicationSettings']->maintenance_mode))>
          <span><strong>Onderhoudsmodus inschakelen</strong><small>Beheerders behouden toegang tot de applicatie en kunnen de modus weer uitschakelen.</small></span>
        </label>
      </section>
    </div>
  </form>
  <section class="admin-processing-note">
    <span class="admin-processing-icon" aria-hidden="true">i</span>
    <div><strong>Notificaties zijn nog niet ingericht</strong><p>De applicatie verstuurt momenteel geen automatische meldingen bij nieuwe feedback. Daarom is deze instelling nog niet beschikbaar.</p></div>
  </section>
</section>

@include('components.role-dashboard-settings', [
  'preferencesPageId' => 'adminPreferencesSettings',
])

@endsection
{{-- this is the blade template for the admin dashboard --}}