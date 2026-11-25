@extends('layouts.application')

@section('role-content')
@php
  $displayedStudentCount = count($roleData);
  $totalStudentXp = collect($roleData)->sum('xp');
@endphp
<section id="coachOverview" class="view hidden coach-page coach-overview-page" data-role-section="coachOverview" aria-labelledby="coachOverviewTitle">
  <header class="coach-page-hero coach-home-hero">
    <time class="coach-hero-date" datetime="{{ now()->toDateString() }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M8 3v4M16 3v4M3 10h18"></path>
      </svg>
      <span>{{ ucfirst(now()->locale('nl')->translatedFormat('l j F Y')) }}</span>
    </time>
    <div class="coach-home-hero-copy">
      <span class="coach-eyebrow">Jouw werkoverzicht</span>
      <h1 id="coachOverviewTitle">Jouw docentoverzicht</h1>
      <p>Bekijk ingeleverd werk, volg feedback op en open je groepen.</p>
      <div class="coach-overview-actions" aria-label="Snelle acties">
        <button class="button dark coach-overview-action" type="button" onclick="openCoachProjectQueue()">
          Inzendingen beoordelen <span aria-hidden="true">&#8594;</span>
        </button>
        <button class="button coach-overview-secondary" type="button" onclick="window.location.href = window.LARAVEL_COACH_ROUTES.groups">
          Mijn groepen <span aria-hidden="true">&#8594;</span>
        </button>
      </div>
    </div>
    <div class="coach-home-hero-image">
      <img src="{{ asset('assets/images/coach-homepage.jpg') }}" alt="Docent begeleidt drie studenten bij een opdracht in de klas" fetchpriority="high">
    </div>
  </header>

  @if (session('status'))
    <div class="notice coach-success" role="status">{{ session('status') }}</div>
  @endif

  <section class="coach-summary coach-overview-summary" aria-label="Kerncijfers">

    <article class="coach-summary-card">
      <span class="coach-summary-icon coach-summary-icon-feedback" aria-hidden="true">&#8230;</span>
      <div><strong class="coach-summary-value">{{ count($frontendData['pendingFeedback']) }}</strong><span>Feedback te bevestigen</span></div>
    </article>
    <article class="coach-summary-card">
      <span class="coach-summary-icon coach-summary-icon-xp" aria-hidden="true">XP</span>
      <div><strong class="coach-summary-value">{{ number_format($totalStudentXp, 0, ',', '.') }}</strong><span>Totaal verdiende XP</span></div>
    </article>
  </section>

  <section class="coach-home-groups" aria-labelledby="coachHomeGroupsTitle">
    <div class="coach-home-groups-heading">
      <div>
        <span class="coach-eyebrow">Begeleiding</span>
        <h2 id="coachHomeGroupsTitle">Mijn groepen</h2>
      </div>
      <a class="coach-text-link" href="{{ route('coach.groups') }}">Alle groepen <span aria-hidden="true">&rarr;</span></a>
    </div>

    @if (count($frontendData['groups']))
      <div class="coach-home-group-grid">
        @foreach ($frontendData['groups'] as $group)
          <a class="coach-home-group-card" href="{{ route('coach.group', ['group' => $group['id']]) }}">
            <span class="coach-home-group-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M3.5 20.5h17M5.5 20V8.5L12 4l6.5 4.5V20M9 20v-5.5h6V20M8.5 9.5h.01M15.5 9.5h.01" /></svg>
            </span>
            <span class="coach-home-group-copy">
              <strong>{{ $group['name'] }}</strong>
              <span>{{ $group['schoolYear'] ?: 'Schooljaar niet ingesteld' }}</span>
            </span>
            <span class="coach-home-group-count"><strong>{{ count($group['students']) }}</strong><small>{{ count($group['students']) === 1 ? 'student' : 'studenten' }}</small></span>
            <span class="coach-home-group-arrow" aria-hidden="true">&rarr;</span>
          </a>
        @endforeach
      </div>
    @else
      <p class="coach-home-groups-empty">Er zijn nog geen groepen aan je account toegewezen.</p>
    @endif
  </section>

  <div class="coach-worklist-grid">
    <section class="coach-worklist-panel" aria-labelledby="coachPendingProjectsTitle">
      <div class="coach-section-heading">
        <div><span class="coach-eyebrow">Te beoordelen</span><h2 id="coachPendingProjectsTitle">Ingeleverde projecten</h2><p>Oudste inzendingen eerst.</p></div>
        <button class="coach-text-link" type="button" onclick="openCoachProjects()">Alle projecten <span aria-hidden="true">→</span></button>
      </div>
      @if (count($frontendData['pendingProjects']))
        <div class="coach-worklist" id="coachPendingProjects">
          @foreach ($frontendData['pendingProjects'] as $project)
            <article class="coach-worklist-item">
              <span class="coach-worklist-icon" aria-hidden="true">&#8599;</span>
              <div class="coach-worklist-copy">
                <strong>{{ $project['title'] }}</strong>
                <span>{{ $project['student'] }} · {{ $project['phase'] ?? 'Geen studiefase' }}</span>
                <small>Ingediend {{ $project['submittedAt'] ?? 'datum onbekend' }}</small>
              </div>
              <button class="button coach-worklist-button" type="button" onclick="openCoachProjectQueue()">Bekijk inzendingen</button>
            </article>
          @endforeach
        </div>
      @else
        <div class="coach-worklist-empty"><span class="coach-home-empty-icon" aria-hidden="true">&#10003;</span><strong>Alles bijgewerkt</strong><span>Er staan geen projecten klaar om te beoordelen.</span></div>
      @endif
    </section>

    <section class="coach-worklist-panel" aria-labelledby="coachPendingFeedbackTitle">
      <div class="coach-section-heading">
        <div><span class="coach-eyebrow">Opvolging</span><h2 id="coachPendingFeedbackTitle">Feedback bevestigen</h2><p>Beoordeel feedback en ken extra uren toe aan de leeruitkomst.</p></div>
      </div>
      @if (count($frontendData['pendingFeedback']))
        <div class="coach-feedback-confirmation-list">
          @foreach ($frontendData['pendingFeedback'] as $feedbackItem)
          @include('coach.feedback-review', ['feedbackItem' => $feedbackItem, 'reviewContext' => 'overview'])
        @endforeach
        </div>
      @else
        <div class="coach-worklist-empty"><span class="coach-home-empty-icon" aria-hidden="true">&#10003;</span><strong>Geen openstaande feedback</strong><span>Nieuwe verzoeken om bevestiging verschijnen hier.</span></div>
      @endif
    </section>
  </div>

</section>

<section id="coachGroups" class="view hidden coach-page {{ $coachGroup ? 'coach-group-detail' : 'coach-group-directory' }}" data-role-section="coachGroups" aria-labelledby="coachGroupsTitle">
  <header class="coach-page-hero coach-groups-hero">
    <div>
      <span class="coach-eyebrow">Jouw begeleiding</span>
      <h1 id="coachGroupsTitle">{{ $coachGroup['name'] ?? 'Mijn groepen' }}</h1>
      <p>{{ $coachGroup ? 'Studenten in deze groep.' : 'Kies een groep om de studenten te bekijken.' }}</p>
      @if ($coachGroup)
        <a class="coach-text-link" href="{{ route('coach.groups') }}">&larr; Terug naar mijn groepen</a>
      @endif
    </div>

  </header>

  @unless ($coachGroup)
    <section class="coach-group-list-panel" aria-label="Toegewezen groepen">
    <div class="coach-group-list-toolbar"><strong>Toegewezen groepen</strong><span>{{ count($frontendData['groups']) }} {{ count($frontendData['groups']) === 1 ? 'groep' : 'groepen' }}</span></div>
    <div class="coach-group-directory-tools"><label for="coachGroupSearch"><span>Groep zoeken</span><span class="coach-group-search-field"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg><input id="coachGroupSearch" type="search" placeholder="Zoek op groepsnaam of schooljaar" autocomplete="off"></span></label><button class="button" id="coachGroupCollapse" type="button">Alles inklappen</button></div>
    <p id="coachGroupSearchEmpty" class="coach-no-results hidden" role="status">Geen groepen gevonden.</p>
    <div class="coach-group-list-columns" aria-hidden="true"><span>Groep</span><span>Schooljaar</span><span>Studenten</span><span>Groepslevel</span><span></span></div>
    @forelse ($frontendData['groups'] as $group)
      @php
        $groupStudents = collect($roleData)->filter(fn (array $student): bool => in_array($group['id'], $student['groupIds']));
        $groupXp = (int) $groupStudents->sum('xp');
        $groupLevelStep = 5000;
        $groupLevel = intdiv($groupXp, $groupLevelStep) + 1;
        $groupLevelXp = $groupXp % $groupLevelStep;
        $groupLevelPercent = (int) round($groupLevelXp / $groupLevelStep * 100);
        $groupHours = (int) $groupStudents->sum('hours');
        $groupOutcomes = $groupStudents->flatMap(fn (array $student) => $student['outcomes']);
        $groupCompleted = $groupOutcomes->where('status', 'behaald')->count();
        $groupCompletion = $groupOutcomes->count() ? (int) round($groupCompleted / $groupOutcomes->count() * 100) : 0;
        $groupNoXp = $groupStudents->where('xp', 0)->count();
      @endphp
      <details class="coach-group-disclosure" data-group-search="{{ mb_strtolower($group['name'].' '.$group['schoolYear']) }}">
      <summary class="coach-group-list-row" aria-controls="coachGroupSnapshot-{{ $group['id'] }}">
        <span class="coach-group-list-name"><span class="coach-group-list-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 4v2"/></svg></span><span><strong>{{ $group['name'] }}</strong>@if ($group['description'])<small>{{ $group['description'] }}</small>@endif</span></span>
        <span class="coach-group-list-year"><span class="coach-group-mobile-label">Schooljaar </span>{{ $group['schoolYear'] ?: 'Niet ingesteld' }}</span>
        <span class="coach-group-list-count">{{ count($group['students']) }} <span>{{ count($group['students']) === 1 ? 'student' : 'studenten' }}</span></span>
        <span class="coach-group-summary-level" title="Elke 5.000 gezamenlijke XP een level hoger">Level {{ $groupLevel }}</span>
        <span class="coach-group-expand-action"><span class="coach-group-expand-label">Groepsinzicht</span><svg class="coach-group-list-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></span>
      </summary>
        <div class="coach-group-snapshot" id="coachGroupSnapshot-{{ $group['id'] }}">
          <div class="coach-group-snapshot-heading"><div><span class="coach-eyebrow">Groep in beeld</span><h3>{{ $group['name'] }}</h3></div><a class="coach-group-primary-action" href="{{ route('coach.group', ['group' => $group['id']]) }}">Bekijk studenten <span aria-hidden="true">&rarr;</span></a></div>
          <div class="coach-group-metrics">
            <article class="coach-group-metric-xp"><span>Totale XP</span><strong>{{ number_format($groupXp, 0, ',', '.') }} <small>XP</small></strong><p>Samen verdiend door de groep</p></article>
            <article class="coach-group-level-card"><span>Groepslevel</span><strong>Level {{ $groupLevel }}</strong><p>Gezamenlijke mijlpaal: elke 5.000 XP een level hoger. Grotere groepen verdienen samen sneller XP; dit is geen ranglijst.</p><div class="coach-group-level-track" role="progressbar" aria-label="Groepsvoortgang naar level {{ $groupLevel + 1 }}" aria-valuemin="0" aria-valuemax="5000" aria-valuenow="{{ $groupLevelXp }}"><span style="width: {{ $groupLevelPercent }}%"></span></div><small>Nog {{ number_format($groupLevelStep - $groupLevelXp, 0, ',', '.') }} XP tot level {{ $groupLevel + 1 }}</small></article>
            <article><span>Gevalideerde uren</span><strong>{{ number_format($groupHours, 0, ',', '.') }} <small>uur</small></strong><p>Totaal geregistreerde voortgang</p></article>
            <article><span>Leeruitkomsten behaald</span><strong>{{ $groupCompletion }}<small>%</small></strong><p>{{ $groupCompleted }} van {{ $groupOutcomes->count() }} leeruitkomsten over alle studenten</p></article>
          </div>
          <div class="coach-group-snapshot-bottom">
            <div class="coach-group-completion"><span><strong>Behaalde leeruitkomsten</strong><small>{{ $groupCompletion }}%</small></span><div role="progressbar" aria-label="Behaalde leeruitkomsten in {{ $group['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $groupCompletion }}"><span style="width: {{ $groupCompletion }}%"></span></div></div>
            <p class="coach-group-xp-signal">@if ($groupStudents->isEmpty())Nog geen studenten gekoppeld.@elseif ($groupNoXp){{ $groupNoXp }} {{ $groupNoXp === 1 ? 'student heeft' : 'studenten hebben' }} nog geen XP verdiend.@else Alle studenten hebben al XP verdiend.@endif</p>
          </div>
        </div>
      </details>
    @empty
      <div class="coach-worklist-empty"><strong>Er zijn nog geen groepen aan jou toegewezen</strong><span>Neem contact op met de beheerder om een groep aan je account te koppelen.</span></div>
    @endforelse
  </section>
  @php
    $directoryStudents = collect($roleData)->map(function (array $student): array {
      $student['directoryProgress'] = (int) round($student['educationPercentage']);
      return $student;
    });
    $attentionStudents = $directoryStudents->filter(fn (array $student): bool => $student['directoryProgress'] !== null && $student['directoryProgress'] < 40)->sortBy('directoryProgress')->values();
    $phaseCounts = $directoryStudents->countBy(fn (array $student): string => $student['phase'] ?: 'Niet ingesteld');
  @endphp
  <div class="coach-directory-context">
    <section class="coach-directory-attention" aria-labelledby="coachDirectoryAttentionTitle">
      <header><div><span class="coach-eyebrow">Begeleiding</span><h2 id="coachDirectoryAttentionTitle">Voortgang onder 40%</h2></div><span class="coach-directory-context-count">{{ $attentionStudents->count() }}</span></header>
      <p>Op basis van gevalideerde uren. Dit percentage zegt niets over achterstand op de planning.</p>
      <div class="coach-directory-attention-list">
        @forelse ($attentionStudents->take(5) as $student)
          <a href="{{ route('coach.student', ['student' => $student['id']]) }}" class="coach-directory-attention-row">
            <span class="coach-directory-context-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($student['name'], 0, 1)) }}</span>
            <span class="coach-directory-context-person"><strong>{{ $student['name'] }}</strong><small>{{ implode(', ', $student['groupNames']) }}</small></span>
            <span class="coach-directory-context-progress">{{ $student['directoryProgress'] }}%</span>
            <span class="coach-directory-context-link">Bekijk profiel <span aria-hidden="true">&rarr;</span></span>
          </a>
        @empty
          <div class="coach-directory-context-empty">Er zijn geen studenten met minder dan 40% geregistreerde voortgang.</div>
        @endforelse
      </div>
      @if ($attentionStudents->count() > 5)<p class="coach-directory-context-note">De 5 studenten met de laagste voortgang worden getoond. Open een groep en filter op rood voor het volledige overzicht.</p>@endif
    </section>
    <section class="coach-directory-phases" aria-labelledby="coachDirectoryPhasesTitle">
      <header><div><span class="coach-eyebrow">Studiefases</span><h2 id="coachDirectoryPhasesTitle">Studiefases</h2></div></header>
      <p>Verdeling van je {{ $directoryStudents->count() }} unieke studenten over alle groepen.</p>
      <div class="coach-directory-phase-list">
        @foreach (['Oriëntatiefase', 'Verdiepingsfase', 'Afrondingsfase'] as $phase)
          @php
            $phaseCount = (int) ($phaseCounts[$phase] ?? 0);
          @endphp
          <div class="coach-directory-phase-row"><span><strong>{{ $phase }}</strong><small>{{ $phaseCount }} {{ $phaseCount === 1 ? 'student' : 'studenten' }}</small></span><div class="coach-directory-phase-track" aria-hidden="true"><span style="width: {{ $directoryStudents->count() ? round($phaseCount / $directoryStudents->count() * 100) : 0 }}%"></span></div></div>
        @endforeach
      </div>
      @if ($phaseCounts['Niet ingesteld'] ?? 0)<p class="coach-directory-context-note">Bij {{ $phaseCounts['Niet ingesteld'] }} {{ $phaseCounts['Niet ingesteld'] === 1 ? 'student is' : 'studenten is' }} nog geen studiefase ingesteld.</p>@endif
    </section>
  </div>
  @endunless

  @if ($coachGroup)
  @php
    $coachProgressBands = collect($roleData)->map(function (array $student): string {
        $targetHours = collect($student['outcomes'])->sum(fn (array $outcome): int => (int) $outcome['target']);
        if ($targetHours === 0) return 'unknown';
        $percentage = (int) round($student['educationPercentage']);
        return $percentage < 40 ? 'red' : ($percentage < 75 ? 'yellow' : 'green');
    })->countBy();
  @endphp
  <details class="coach-progress-overview coach-group-progress-disclosure">
    <summary><strong>Groepsvoortgang</strong><span>{{ $coachProgressBands['red'] ?? 0 }} rood &middot; {{ $coachProgressBands['yellow'] ?? 0 }} geel &middot; {{ $coachProgressBands['green'] ?? 0 }} groen @if ($coachProgressBands['unknown'] ?? 0) &middot; {{ $coachProgressBands['unknown'] }} zonder norm @endif</span><span aria-hidden="true">&#8964;</span></summary>
    <div class="coach-progress-overview-heading">
      <div><span class="coach-eyebrow">Groepsvoortgang</span><p>Gevalideerde uren ten opzichte van normuren. De kleur toont geregistreerde voortgang, geen achterstand ten opzichte van een planning.</p></div>
    </div>
    <div class="coach-progress-band-list">
      <div class="coach-progress-band coach-progress-band-red"><span class="coach-progress-band-dot"></span><span>Rood</span><small>0–39%</small><strong>{{ $coachProgressBands['red'] ?? 0 }}</strong></div>
      <div class="coach-progress-band coach-progress-band-yellow"><span class="coach-progress-band-dot"></span><span>Geel</span><small>40–74%</small><strong>{{ $coachProgressBands['yellow'] ?? 0 }}</strong></div>
      <div class="coach-progress-band coach-progress-band-green"><span class="coach-progress-band-dot"></span><span>Groen</span><small>75–100%</small><strong>{{ $coachProgressBands['green'] ?? 0 }}</strong></div>
      @if (($coachProgressBands['unknown'] ?? 0) > 0)
        <div class="coach-progress-band coach-progress-band-unknown"><span class="coach-progress-band-dot"></span><span>Geen norm</span><strong>{{ $coachProgressBands['unknown'] }}</strong></div>
      @endif
    </div>
  </details>
  <section class="coach-roster" id="coachStudentRoster" data-roster-scope="{{ $coachGroup['id'] ?? 'all' }}" data-roster-url="{{ $coachGroup ? route('coach.group', ['group' => $coachGroup['id']]) : route('coach.groups') }}">
    <div class="coach-section-heading">
      <div>
        <span class="coach-eyebrow">Groepsleden</span>
        <h2 id="coachRosterTitle" data-default-title="{{ $coachGroup ? 'Studenten in '.$coachGroup['name'] : 'Studenten in mijn groepen' }}">{{ $coachGroup ? 'Studenten in '.$coachGroup['name'] : 'Studenten in mijn groepen' }}</h2>
        <p>Open een profiel voor details. Voortgang is gebaseerd op gevalideerde uren ten opzichte van normuren.</p>
      </div>
      <div class="coach-roster-heading-actions">
        <button class="button coach-filter-reset hidden" id="coachRosterReset" type="button">Filters wissen</button>
        <span class="coach-section-count" id="coachStudentCount" aria-live="polite">{{ $displayedStudentCount }} {{ $displayedStudentCount === 1 ? 'student' : 'studenten' }}</span>
      </div>
    </div>

    @if ($displayedStudentCount)
      <div class="coach-roster-tools">
        <label class="coach-search">
          <span class="coach-control-label">Student zoeken</span>
          <span class="coach-search-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path></svg>
            <input id="coachStudentSearch" type="search" autocomplete="off" placeholder="Zoek op naam of studentnummer">
          </span>
        </label>
        <details class="coach-roster-more-filters"><summary>Filters en sortering <span id="coachRosterFilterCount"></span><span aria-hidden="true">&#8964;</span></summary><div class="coach-roster-extra-fields">
        @unless ($coachGroup)
        <label class="coach-class-filter">
          <span class="coach-control-label">Groep</span>
          <select id="coachGroupFilter">
            <option value="">Alle studenten in mijn groepen</option>
            @foreach ($frontendData['groups'] as $group)
              <option value="{{ $group['id'] }}">{{ $group['name'] }}{{ $group['schoolYear'] ? ' · '.$group['schoolYear'] : '' }}</option>
            @endforeach
          </select>
        </label>
        @endunless
        <label class="coach-phase-filter">
          <span class="coach-control-label">Studiefase</span>
          <select id="coachPhaseFilter">
            <option value="">Alle studiefases</option>
            @foreach (['Oriëntatiefase', 'Verdiepingsfase', 'Afrondingsfase'] as $phase)
              <option value="{{ mb_strtolower($phase) }}">{{ $phase }}</option>
            @endforeach

          </select>
        </label>
        <label class="coach-phase-filter"><span class="coach-control-label">Voortgang</span><select id="coachProgressFilter"><option value="">Alle voortgang</option><option value="red">Rood · 0–39%</option><option value="yellow">Geel · 40–74%</option><option value="green">Groen · 75–100%</option><option value="unknown">Geen norm</option></select></label>        <label class="coach-sort-filter">
          <span class="coach-control-label">Sorteren op</span>
          <select id="coachStudentSort">
            <option value="name">Naam</option>
            <option value="progress-asc">Laagste voortgang</option>
            <option value="activity-desc">Recentste activiteit</option>
              <option value="xp-desc">Hoogste XP eerst</option>
              <option value="xp-asc">Laagste XP eerst</option>
          </select>
        </label>
        </div></details>
      </div>

      <div class="coach-roster-columns" aria-hidden="true">
        <span>Student</span>
        <span>Voortgang</span>
        <span>XP</span>
        <span></span>
      </div>
      <div class="coach-student-grid">
        {{-- Open het volledige studentprofiel vanuit de compacte groepslijst. --}}
        @foreach ($roleData as $student)
          @php($studentTargetHours = collect($student['outcomes'])->sum(fn (array $outcome): int => (int) $outcome['target']))
          @php($studentProgress = (int) round($student['educationPercentage']))
          @php($studentProgressBand = $studentTargetHours === 0 ? 'unknown' : ($studentProgress < 40 ? 'red' : ($studentProgress < 75 ? 'yellow' : 'green')))
          @php($studentProgressLabel = ['red' => 'Rood', 'yellow' => 'Geel', 'green' => 'Groen', 'unknown' => 'Geen norm'][$studentProgressBand])
          <a
            class="coach-student-card"
            href="{{ route('coach.student', ['student' => $student['id']]) }}"
            data-coach-student-card="{{ $student['id'] }}"
            data-coach-target-hours="{{ $studentTargetHours }}"
            data-student-progress="{{ $studentProgress }}"
            data-student-progress-band="{{ $studentProgressBand }}"
            data-student-xp="{{ $student['xp'] }}"
            data-student-activity="{{ $student['lastActivity']['at'] ?? '' }}"
            data-student-group-ids=",{{ implode(',', $student['groupIds']) }},"
            data-student-name="{{ mb_strtolower($student['name']) }}" data-student-search="{{ mb_strtolower($student['name'].' '.$student['studentNumber']) }}"
            data-student-phase="{{ mb_strtolower($student['phase'] ?? 'Geen studiefase ingesteld') }}"
            aria-label="Open het profiel van {{ $student['name'] }}, {{ number_format($student['xp'], 0, ',', '.') }} XP"
          >
            <span class="coach-student-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($student['name'], 0, 1)) }}</span>
            <span class="coach-student-info">
              <strong>{{ $student['name'] }}</strong>
              <span class="coach-student-identity">{{ $student['studentNumber'] ?: 'Studentnummer niet ingesteld' }}@if ($student['year']) &middot; Leerjaar {{ $student['year'] }}@endif</span>
              <span class="coach-student-phase">{{ $student['phase'] ?: 'Geen studiefase ingesteld' }}</span>
              @if ($student['lastActivity'])
                <small class="coach-student-last-activity">{{ $student['lastActivity']['label'] }} &middot; <time datetime="{{ $student['lastActivity']['at'] }}" title="{{ $student['lastActivity']['date'] }}">{{ $student['lastActivity']['relative'] }}</time></small>
              @else
                <small class="coach-student-last-activity">Nog geen project, reflectie of eigen feedback toegevoegd</small>
              @endif
            </span>
            <span class="coach-student-progress-cell coach-progress-{{ $studentProgressBand }}">
              <span class="coach-student-progress-top">
                <span class="coach-student-progress-state"><span class="coach-student-progress-dot" aria-hidden="true"></span>{{ $studentProgressLabel }}</span>
                <strong>{{ $studentTargetHours > 0 ? $studentProgress.'%' : '—' }}</strong>
              </span>
              <span class="coach-student-progress-meter" @if ($studentTargetHours > 0) role="progressbar" aria-label="Voortgang van {{ $student['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $studentProgress }}" aria-valuetext="{{ $studentProgressLabel }}, {{ $studentProgress }} procent" @endif>
                <span style="width: {{ $studentProgress }}%"></span>
              </span>
            </span>
            <span class="coach-student-xp-badge" aria-label="{{ number_format($student['xp'], 0, ',', '.') }} XP"><strong>{{ number_format($student['xp'], 0, ',', '.') }}</strong><small>XP</small></span>
            <span class="coach-student-action">
              <span>Bekijk profiel</span>
              <span aria-hidden="true">&rarr;</span>
            </span>
          </a>
        @endforeach

      </div>
      <p class="coach-no-results hidden" id="coachNoResults" role="status">Geen studenten gevonden.</p>
    @else
      {{-- Explain the empty roster instead of leaving the coach with a blank page. --}}
      <div class="coach-empty-state">
        <span class="coach-empty-icon" aria-hidden="true">♙</span>
        <h3>Er zijn nog geen studenten</h3>
        <p>Vraag de beheerder om studenten aan deze groep toe te wijzen.</p>
      </div>
    @endif
  </section>
  @endif
</section>

<section id="coachFeedback" class="view hidden coach-page coach-directory-page" data-role-section="coachFeedback" aria-labelledby="coachFeedbackTitle">
  <header class="coach-page-hero">
    <div>
      <span class="coach-eyebrow">Opvolging</span>
      <h1 id="coachFeedbackTitle">Feedback</h1>
      <p>Bekijk feedback, bevestig de verwerking en bepaal hoeveel extra uren de student krijgt.</p>
    </div>
  </header>

  @if (session('status'))<div class="notice coach-success" role="status">{{ session('status') }}</div>@endif
  @if (count($frontendData['pendingFeedback']))
    <section class="coach-worklist-panel" aria-label="Feedback die bevestiging nodig heeft">
      <div class="coach-section-heading">
        <div><h2>Open bevestigingen</h2><p>Feedback die nog opvolging vraagt.</p></div>
      </div>
      <div class="coach-feedback-confirmation-list">
        @foreach ($frontendData['pendingFeedback'] as $feedbackItem)
          @include('coach.feedback-review', ['feedbackItem' => $feedbackItem, 'reviewContext' => 'feedback'])
        @endforeach
      </div>
    </section>
  @else
    <div class="coach-empty-state">
      <span class="coach-empty-icon" aria-hidden="true">✓</span>
      <h2>Alles bijgewerkt</h2>
      <p>Er wachten geen feedbackbevestigingen.</p>
    </div>
  @endif
  @if (count($frontendData['reviewedFeedback']))
    <section class="coach-feedback-history" aria-labelledby="coachFeedbackHistoryTitle">
      <div class="coach-section-heading"><div><h2 id="coachFeedbackHistoryTitle">Recent beoordeeld</h2><p>De laatste 10 bevestigingen en toegekende uren.</p></div></div>
      @foreach ($frontendData['reviewedFeedback'] as $review)
        <article><div><strong>{{ $review['student'] }}</strong><span>{{ $review['title'] }} &middot; {{ $review['outcome'] }}</span><small>{{ $review['date'] }}@if ($review['reviewer']) &middot; {{ $review['reviewer'] }}@endif</small></div><span class="coach-feedback-hours-awarded">{{ $review['hours'] === null ? 'Uren niet geregistreerd' : $review['hours'].' uur toegekend' }}</span></article>
      @endforeach
    </section>
  @endif
</section>

{{-- Leeruitkomsten, leerdoelen en projecten zijn alleen-lezenoverzichten voor de coach. --}}
<section id="coachActivities" class="view hidden coach-page coach-directory-page" data-role-section="coachActivities" aria-labelledby="coachActivitiesTitle">
  <header class="coach-page-hero coach-outcomes-hero">
    <div>
      <span class="coach-eyebrow">Onderwijsinhoud</span>
      <h1 id="coachActivitiesTitle">Leeruitkomsten</h1>
      <p>Bekijk voortgang per studiefase.</p>
    </div>
  </header>

  @php($activityCount = count($frontendData['activities']))
  @php($activeActivityCount = collect($frontendData['activities'])->sum('activeStudentCount'))
  <section class="coach-insight-strip coach-insight-activities" aria-label="Inzicht in leeruitkomsten">
    <article><span class="coach-insight-icon" aria-hidden="true">LU</span><div><strong>{{ $activityCount }}</strong><span>leeruitkomsten</span></div></article>
    <article><span class="coach-insight-icon" aria-hidden="true">ACT</span><div><strong>{{ $activeActivityCount }}</strong><span>Studenten bezig met leeruitkomsten</span></div></article>
  </section>
  <p class="coach-insight-explanation">Telt studenten per leeruitkomst met status Gestart of In ontwikkeling. Een student die aan meerdere leeruitkomsten werkt, telt meerdere keren mee.</p>

  @if (count($frontendData['activities']))
    <div class="coach-directory-tools">
      <label class="coach-search">
        <span class="coach-control-label">Leeruitkomst zoeken</span>
        <span class="coach-search-field">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path></svg>
          <input id="coachActivitySearch" type="search" autocomplete="off" placeholder="Zoek op titel, code of omschrijving">
        </span>
      </label>
      <label class="coach-directory-select">
        <span class="coach-control-label">Studiefase</span>
        <select id="coachActivityPhaseFilter">
          <option value="">Alle studiefases</option>
          @foreach (['Oriëntatiefase', 'Verdiepingsfase', 'Afrondingsfase'] as $phase)
            <option value="{{ mb_strtolower($phase) }}">{{ $phase }}</option>
          @endforeach
        </select>
      </label>
      <span class="coach-outcomes-result-count hidden" id="coachActivityResults" aria-live="polite">{{ $activityCount }} {{ $activityCount === 1 ? 'leeruitkomst' : 'leeruitkomsten' }}</span>
      <button class="button coach-outcomes-reset" id="coachActivityReset" type="button">Filters wissen</button>
    </div>
    <p class="coach-no-results hidden" id="coachActivityNoResults" role="status">Geen leeruitkomsten gevonden.</p>
    <div class="coach-directory-grid">
      @foreach ($frontendData['activities'] as $activity)
        @php($activityCoverage = $frontendData['studentCount'] > 0 ? min(100, (int) round(($activity['activeStudentCount'] / $frontendData['studentCount']) * 100)) : 0)
        <article class="coach-directory-card coach-activity-card" data-directory-phase="{{ mb_strtolower($activity['phase'] ?? '') }}" data-directory-search="{{ mb_strtolower($activity['code'].' '.$activity['title'].' '.$activity['description'].' '.$activity['phase']) }}">
          <div class="coach-directory-card-head">
            <span class="coach-directory-card-icon" aria-hidden="true">↗</span>
            <div class="coach-directory-meta">
              <span class="coach-directory-code">{{ $activity['code'] }}</span>
              <span class="coach-phase-badge">{{ $activity['phase'] ?? 'Geen studiefase' }}</span>
            </div>
          </div>
          <h2>{{ $activity['title'] }}</h2>
          <p class="coach-directory-description">{{ $activity['description'] }}</p>
          <div class="coach-directory-footer">
            <span class="coach-directory-activity-count"><strong>{{ $activity['activeStudentCount'] }}</strong> {{ $activity['activeStudentCount'] === 1 ? 'student werkt' : 'studenten werken' }} hieraan</span>
            <span class="coach-activity-coverage-label">{{ $activityCoverage }}% van de studenten</span>
            <span class="coach-directory-mini-progress" role="progressbar" aria-label="Studenten actief bij {{ $activity['title'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $activityCoverage }}"><span style="width: {{ $activityCoverage }}%"></span></span>
          </div>
        </article>
      @endforeach
    </div>
  @else
    <div class="coach-empty-state">
      <span class="coach-empty-icon" aria-hidden="true">✳</span>
      <h3>Er zijn nog geen leeruitkomsten</h3>
      <p>Zodra leeruitkomsten zijn toegevoegd, verschijnen ze hier.</p>
    </div>
  @endif
</section>

<section id="coachGoals" class="view hidden coach-page coach-directory-page" data-role-section="coachGoals" aria-labelledby="coachGoalsTitle">
  <header class="coach-page-hero">
    <div>
      <span class="coach-eyebrow">Persoonlijke ontwikkeling</span>
      <h1 id="coachGoalsTitle">Leerdoelen</h1>
      <p>Leerdoelen van studenten en hun koppeling aan leeruitkomsten.</p>
    </div>
  </header>

  @php($activeGoalCount = collect($frontendData['goals'])->where('status', 'actief')->count())
  @php($completedGoalCount = collect($frontendData['goals'])->where('status', 'afgerond')->count())
  @php($linkedGoalCount = collect($frontendData['goals'])->filter(fn (array $goal): bool => !empty($goal['outcome']))->count())
  <section class="coach-insight-strip coach-insight-goals" aria-label="Inzicht in leerdoelen">
    <article><span class="coach-insight-icon" aria-hidden="true">DOEL</span><div><strong>{{ count($frontendData['goals']) }}</strong><span>leerdoelen</span></div></article>
    <article><span class="coach-insight-icon" aria-hidden="true">ACT</span><div><strong>{{ $activeGoalCount }}</strong><span>actief</span></div></article>
    <article><span class="coach-insight-icon" aria-hidden="true">OK</span><div><strong>{{ $completedGoalCount }}</strong><span>afgerond</span></div></article>
    <article><span class="coach-insight-icon" aria-hidden="true">LINK</span><div><strong>{{ $linkedGoalCount }}</strong><span>gekoppeld aan leeruitkomsten</span></div></article>
  </section>

  @if (count($frontendData['goalStudents']))
    <div class="coach-directory-tools">
      <label class="coach-search">
        <span class="coach-control-label">Leerdoel zoeken</span>
        <span class="coach-search-field">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path></svg>
          <input id="coachGoalSearch" type="search" autocomplete="off" placeholder="Zoek op student, doel of leeruitkomst">
        </span>
      </label>
      <label class="coach-directory-select">
        <span class="coach-control-label">Status</span>
        <select id="coachGoalStatusFilter">
          <option value="">Alle statussen</option>
          <option value="actief">Actief</option>
          <option value="afgerond">Afgerond</option>
        </select>
      </label>
    </div>
    <p class="coach-no-results hidden" id="coachGoalNoResults" role="status">Geen studenten of leerdoelen gevonden.</p>
    <div class="coach-directory-grid">
      @foreach ($frontendData['goalStudents'] as $goalStudent)
        <details
          class="coach-directory-card coach-goal-student-card"
          data-student-name="{{ mb_strtolower($goalStudent['name']) }}"
        >
          <summary class="coach-goal-student-summary">
            <span class="coach-directory-person-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($goalStudent['name'], 0, 1)) }}</span>
            <span class="coach-goal-student-name">{{ $goalStudent['name'] }}</span>
            <span class="coach-goal-student-count">{{ count($goalStudent['goals']) }} {{ count($goalStudent['goals']) === 1 ? 'leerdoel' : 'leerdoelen' }}</span>
            <span class="coach-goal-student-chevron" aria-hidden="true">⌄</span>
          </summary>
          <div class="coach-goal-list">
            @forelse ($goalStudent['goals'] as $goal)
              <article
                class="coach-goal-item"
                data-directory-status="{{ $goal['status'] }}"
                data-directory-search="{{ mb_strtolower($goal['title'].' '.$goal['description'].' '.$goal['outcome'].' '.$goal['status']) }}"
              >
                <header class="coach-goal-item-head">
                  <h2>{{ $goal['title'] }}</h2>
                  <span class="coach-status-badge coach-goal-status coach-goal-status-{{ $goal['status'] }}">{{ str_replace('_', ' ', ucfirst($goal['status'])) }}</span>
                </header>
                <p class="coach-directory-description">{{ $goal['description'] }}</p>
                @if ($goal['outcome'])
                  <div class="coach-directory-link"><span aria-hidden="true">◎</span><span><small>Gekoppeld aan</small><strong>{{ $goal['outcome'] }}</strong></span></div>
                @else
                  <div class="coach-directory-link coach-directory-link-muted"><span aria-hidden="true">◎</span><span><small>Nog niet gekoppeld</small><strong>Geen leeruitkomst geselecteerd</strong></span></div>
                @endif
              </article>
            @empty
              <p class="coach-goal-empty">Deze student heeft nog geen leerdoelen.</p>
            @endforelse
            @if ($goalStudent['canViewProgress'])
              <a class="coach-goal-student-profile coach-directory-owner-link" href="{{ route('coach.student', $goalStudent['id']) }}">
                Bekijk voortgang van {{ $goalStudent['name'] }} →
              </a>
            @endif
          </div>
        </details>
      @endforeach
    </div>
  @else
    <div class="coach-empty-state">
      <span class="coach-empty-icon" aria-hidden="true">◎</span>
      <h3>Er zijn nog geen leerdoelen</h3>
      <p>Leerdoelen die studenten aanmaken, verschijnen hier.</p>
    </div>
  @endif
</section>

<section id="coachProjects" class="view hidden coach-page coach-directory-page" data-role-section="coachProjects" aria-labelledby="coachProjectsTitle">
  <header class="coach-page-hero coach-projects-hero">
    <div>
      <span class="coach-eyebrow">Studentwerk</span>
      <h1 id="coachProjectsTitle">Projecten</h1>
      <p>Volg opdrachten en beoordeel ingeleverd werk.</p>
    </div>
  </header>

  @php($projectRecords = collect($frontendData['projects']))
  <div class="coach-project-insights" aria-label="Verdeling van projecten">
    <button class="coach-project-insight coach-project-insight-all" type="button" data-project-filter-shortcut="" aria-pressed="false" @disabled($projectRecords->isEmpty())><span>Alle projecten</span><strong>{{ $projectRecords->count() }}</strong></button>
    <button class="coach-project-insight coach-project-insight-review" type="button" data-project-filter-shortcut="review" aria-pressed="false" @disabled($projectRecords->isEmpty())><span>Ter beoordeling</span><strong>{{ $projectRecords->where('status', 'review')->count() }}</strong></button>
    <button class="coach-project-insight coach-project-insight-active" type="button" data-project-filter-shortcut="active" aria-pressed="false" @disabled($projectRecords->isEmpty())><span>In uitvoering</span><strong>{{ $projectRecords->where('status', 'active')->count() }}</strong></button>
    <button class="coach-project-insight coach-project-insight-concept" type="button" data-project-filter-shortcut="concept" aria-pressed="false" @disabled($projectRecords->isEmpty())><span>Concept</span><strong>{{ $projectRecords->where('status', 'concept')->count() }}</strong></button>
    <button class="coach-project-insight coach-project-insight-completed" type="button" data-project-filter-shortcut="completed" aria-pressed="false" @disabled($projectRecords->isEmpty())><span>Afgerond</span><strong>{{ $projectRecords->where('status', 'completed')->count() }}</strong></button>
  </div>

  @if (count($frontendData['projects']))
    <div class="coach-directory-tools">
      <label class="coach-search">
        <span class="coach-control-label">Project zoeken</span>
        <span class="coach-search-field">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path></svg>
          <input id="coachProjectSearch" type="search" autocomplete="off" placeholder="Zoek op project of student">
        </span>
      </label>
      <label class="coach-project-status-filter">
        <span class="coach-control-label">Status</span>
        <select id="coachProjectStatusFilter">
          <option value="">Alle statussen</option>
          <option value="review">Ter beoordeling</option>
          <option value="active">In uitvoering</option>
          <option value="concept">Concept</option>
          <option value="completed">Afgerond</option>
        </select>
      </label>
      <button class="button coach-project-reset" id="coachProjectReset" type="button">Filters wissen</button>
    </div>
    <p class="coach-no-results hidden" id="coachProjectNoResults" role="status">Geen projecten gevonden.</p>
    <div class="coach-project-list-head" aria-hidden="true"><span>Student</span><span>Project</span><span>Details</span></div>
  @endif

  @if (count($frontendData['projects']))
    <div class="coach-directory-grid">
      @foreach ($frontendData['projects'] as $project)
        <article
          class="coach-directory-card coach-project-card"
          data-coach-project-card="{{ $project['id'] }}"
          data-project-status="{{ $project['status'] }}"
          data-project-search="{{ mb_strtolower($project['student'].' '.$project['title'].' '.$project['type'].' '.$project['description'].' '.implode(' ', $project['outcomes'])) }}"
        >
          <div class="coach-directory-card-head">
            <span class="coach-directory-person">
              <span class="coach-directory-person-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($project['student'], 0, 1)) }}</span>
              <a class="coach-directory-owner coach-directory-owner-link" href="{{ route('coach.student', $project['studentId']) }}">{{ $project['student'] }}</a>
            </span>
            <span class="coach-status-badge coach-status-{{ $project['status'] }}">{{ $project['statusLabel'] }}</span>
          </div>
          <h2>{{ $project['title'] }}</h2>
          <p class="coach-directory-description">{{ $project['description'] ?: 'Er is nog geen projectomschrijving toegevoegd.' }}</p>
          <div class="coach-directory-footer">
            <span class="coach-project-type"><span aria-hidden="true">▤</span>{{ $project['type'] }}</span>
            @if ($project['dueDate'])
              <span class="coach-project-deadline"><span aria-hidden="true">◷</span>Inleverdatum · {{ $project['dueDate'] }}</span>
            @endif
          </div>
          @if (count($project['outcomes']))
            <div class="coach-project-outcomes">
              <span>Leeruitkomsten</span>
              <div>@foreach ($project['outcomes'] as $outcome)<span>{{ $outcome }}</span>@endforeach</div>
            </div>
          @endif
        </article>
      @endforeach
    </div>
  @else
    <div class="coach-empty-state">
      <span class="coach-empty-icon" aria-hidden="true">↗</span>
      <h3>Er zijn nog geen projecten</h3>
      <p>Projecten verschijnen hier zodra studenten ze aanmaken.</p>
    </div>
  @endif
</section>

{{-- Toon per student een alleen-lezen detailoverzicht, zonder een nieuw verzoek te hoeven laden. --}}
@foreach ($roleData as $student)
  @php($studentTargetHours = collect($student['outcomes'])->sum(fn (array $outcome): int => (int) $outcome['target']))
  @php($studentProgress = (int) round($student['educationPercentage']))
  <section id="coachStudent-{{ $student['id'] }}" class="view hidden coach-page coach-student-detail" data-role-section="coachGroups" data-student-id="{{ $student['id'] }}" data-coach-target-hours="{{ $studentTargetHours }}" aria-labelledby="coachStudentTitle-{{ $student['id'] }}">
    @php($completedOutcomes = collect($student['outcomes'])->where('status', 'behaald')->count())
    <div class="coach-student-detail-top">
      <a class="coach-profile-back" data-coach-roster-back href="{{ route('coach.groups') }}"><span aria-hidden="true">&larr;</span> Terug naar groepen</a>
      <span class="coach-eyebrow">Studentprofiel <span aria-hidden="true">&middot;</span> Begeleiding</span>
    </div>

    <header class="coach-student-profile-hero">
      <span class="coach-student-avatar coach-student-avatar-large" aria-hidden="true">{{ mb_strtoupper(mb_substr($student['name'], 0, 1)) }}</span>
      <div class="coach-student-profile-identity">
        <span class="coach-student-profile-label">Student</span>
        <h1 id="coachStudentTitle-{{ $student['id'] }}">{{ $student['name'] }}</h1>
        <div class="coach-student-profile-facts">
          @if ($student['studentNumber'])<span>{{ $student['studentNumber'] }}</span>@endif
          @if ($student['year'])<span>Leerjaar {{ $student['year'] }}</span>@endif
          <span>{{ $student['phase'] ?? 'Geen studiefase ingesteld' }}</span>
        </div>
        <p class="coach-student-profile-groups"><strong>Groepen</strong> {{ implode(', ', $student['groupNames']) ?: 'Geen groep gekoppeld' }}</p>
        <p class="coach-profile-last-activity"><strong>Laatste activiteit</strong> @if ($student['lastActivity']){{ $student['lastActivity']['label'] }} &middot; <time datetime="{{ $student['lastActivity']['at'] }}">{{ $student['lastActivity']['date'] }}</time>@else Nog geen project, reflectie of eigen feedback toegevoegd @endif</p>
      </div>
    </header>

    <div class="coach-student-overview-grid">
      <section class="coach-student-stats" aria-label="Kerncijfers van de student">
        <article class="coach-detail-stat">
          <span class="coach-detail-stat-label">Gevalideerde uren</span>
          <strong data-coach-total-hours>{{ $student['hours'] }} <small>uur</small></strong>
        </article>
        <article class="coach-detail-stat">
          <span class="coach-detail-stat-label">Leeruitkomsten</span>
          <strong>{{ count($student['outcomes']) }}</strong>
        </article>
        <article class="coach-detail-stat">
          <span class="coach-detail-stat-label">Behaald</span>
          <strong data-coach-completed-outcomes>{{ $completedOutcomes }}</strong>
        </article>
        <article class="coach-detail-stat coach-detail-stat-progress">
          <span class="coach-detail-stat-label">Totale voortgang</span>
          <strong data-coach-progress-value>{{ $studentProgress }}%</strong>
          <span class="coach-detail-progress-track" data-coach-progress-bar role="progressbar" aria-label="Voortgang van {{ $student['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $studentProgress }}"><span style="width: {{ $studentProgress }}%"></span></span>
        </article>
      </section>


    </div>
    <details class="coach-xp-sources coach-xp-disclosure">
      <summary class="coach-xp-compact-summary">
        <span><span class="coach-eyebrow">XP & ontwikkeling</span><strong>{{ number_format($student['xp'], 0, ',', '.') }} XP</strong></span>
        <span class="coach-xp-compact-level">Level {{ $student['level'] }} &middot; {{ $student['levelName'] }}<small>Nog {{ number_format(1000 - $student['levelProgress'], 0, ',', '.') }} XP tot level {{ $student['level'] + 1 }}</small></span>
        <span class="coach-disclosure-label">XP-details <span aria-hidden="true">&#8964;</span></span>
      </summary>
      <div class="coach-xp-expanded-content">
        <div class="coach-xp-source-grid">
          <article class="coach-xp-source"><span>Activiteiten</span><strong>{{ number_format($student['activityXp'], 0, ',', '.') }} <small>XP</small></strong><p>Projecten, leeruitkomsten, reflecties en feedback</p></article>
          <article class="coach-xp-source"><span>Challenges</span><strong>{{ number_format($student['challengeXp'], 0, ',', '.') }} <small>XP</small></strong><p>Verdiend met afgeronde challenges</p></article>
        </div>
        <div class="coach-xp-next"><div><strong>Op weg naar level {{ $student['level'] + 1 }}</strong><p>{{ number_format($student['levelProgress'], 0, ',', '.') }} van 1.000 XP binnen dit level</p><span class="coach-xp-overview-track" role="progressbar" aria-label="Voortgang naar level {{ $student['level'] + 1 }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) round($student['levelProgressPercent']) }}"><span style="width: {{ $student['levelProgressPercent'] }}%"></span></span></div></div>
      </div>
    </details>
    <section class="coach-progress-section" aria-labelledby="coachOutcomesTitle-{{ $student['id'] }}">
      <div class="coach-section-heading">
        <div>
          <span class="coach-eyebrow">Begeleiding</span>
          <h2 id="coachOutcomesTitle-{{ $student['id'] }}">Leeruitkomsten</h2>
          <p>Bekijk de behaalde resultaten, gevalideerde uren en verdiende XP per leeruitkomst.</p>
        </div>
        <span class="coach-section-count" data-coach-outcome-count>{{ count($student['outcomes']) }} leeruitkomsten</span>
      </div>

      @if (count($student['outcomes']))
        <div class="coach-outcome-tools">
          <label class="coach-outcome-search">
            Leeruitkomst zoeken
            <input class="coachOutcomeSearch" type="search" autocomplete="off" placeholder="Zoek op naam of code">
          </label>
          <label class="coach-outcome-status-filter">
            Status
            <select class="coachOutcomeStatus">
              <option value="">Alle statussen</option>
              <option value="niet_gestart">Niet gestart</option>
              <option value="gestart">Gestart</option>
              <option value="in_ontwikkeling">In ontwikkeling</option>
              <option value="behaald">Behaald</option>
            </select>
          </label>
          <button class="button coach-outcome-reset hidden" type="button">Filters wissen</button>
        </div>
      @endif
      @if (count($student['outcomes']))
        <div class="coach-outcome-list-head" aria-hidden="true"><span>Leeruitkomst</span><span>Gevalideerde uren</span><span>Verdiende XP</span><span>Status</span></div>
      @endif
      @forelse ($student['outcomes'] as $outcome)
        <article
          class="coach-outcome-card"
          data-outcome-search="{{ mb_strtolower($outcome['code'].' '.$outcome['title']) }}"
          data-outcome-status="{{ $outcome['status'] }}"
        >
          <div class="coach-outcome-heading">
            <div class="coach-outcome-title">
              <span class="coach-outcome-code">{{ $outcome['code'] }}</span>
              <h3>{{ $outcome['title'] }}</h3>
            </div>
            <span class="coach-outcome-current-hours" data-coach-outcome-hours>{{ $outcome['hours'] }} uur</span>
            <span class="coach-outcome-earned-xp" aria-label="{{ $outcome['xp'] }} XP verdiend voor deze leeruitkomst">{{ number_format($outcome['xp'], 0, ',', '.') }} <small>XP</small></span>
            <span class="coach-status-badge coach-status-{{ $outcome['status'] }}" data-outcome-status-label>{{ str_replace('_', ' ', ucfirst($outcome['status'])) }}</span>
          </div>


        </article>
      @empty
        {{-- Give a clear explanation when no progress rows exist for this student yet. --}}
        <div class="coach-empty-state coach-outcomes-empty">
          <span class="coach-empty-icon" aria-hidden="true">◎</span>
          <h3>Nog geen voortgang geregistreerd</h3>
        </div>
      @endforelse
      <p class="coach-outcomes-no-results hidden" role="status">Geen leeruitkomsten gevonden.</p>
      @if (count($student['outcomes']) > 5)
        <nav class="coach-outcome-pagination" aria-label="Pagina's met leeruitkomsten">
          <span class="coach-outcome-range" aria-live="polite"></span>
          <div class="coach-outcome-page-controls">
            <button class="coach-outcome-page-button coachOutcomePrevious" type="button" aria-label="Vorige pagina">←</button>
            <span class="coach-outcome-page-label" aria-live="polite"></span>
            <button class="coach-outcome-page-button coachOutcomeNext" type="button" aria-label="Volgende pagina">→</button>
          </div>
        </nav>
      @endif
    </section>
  </section>
@endforeach
@include('components.role-dashboard-settings', [
  'preferencesPageId' => 'coachSettings',
  'preferencesBackUrl' => route('coach.dashboard'),
])
@endsection
