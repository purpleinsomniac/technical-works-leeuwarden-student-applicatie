@extends('layouts.application')

@section('role-content')
@php($displayedStudentCount = count($roleData))
@php($totalStudentXp = collect($roleData)->sum('xp'))
<section id="coachOverview" class="view hidden coach-page coach-overview-page" aria-labelledby="coachOverviewTitle">
  <header class="coach-page-hero">
    <div>
      <span class="coach-eyebrow">Jouw werkoverzicht</span>
      <h1 id="coachOverviewTitle">Begeleiding in één oogopslag</h1>
      <p>Begin met ingeleverd werk en opvolging. Open daarna direct de student of het volledige overzicht.</p>
    </div>
    <button class="button dark coach-overview-action" type="button" onclick="show('coachStudents')">Studenten bekijken <span aria-hidden="true">→</span></button>
  </header>

  @if (session('status'))
    <div class="notice coach-success" role="status">{{ session('status') }}</div>
  @endif

  <section class="coach-summary coach-overview-summary" aria-label="Actuele begeleiding in cijfers">
    <article class="coach-summary-card">
      <span class="coach-summary-icon" aria-hidden="true">↗</span>
      <div><strong class="coach-summary-value">{{ count($frontendData['pendingProjects']) }}</strong><span>projecten ter beoordeling</span></div>
    </article>
    <article class="coach-summary-card">
      <span class="coach-summary-icon coach-summary-icon-hours" aria-hidden="true">✓</span>
      <div><strong class="coach-summary-value">{{ count($frontendData['pendingFeedback']) }}</strong><span>feedbackacties open</span></div>
    </article>
    <article class="coach-summary-card">
      <span class="coach-summary-icon coach-summary-icon-outcomes" aria-hidden="true">01</span>
      <div><strong class="coach-summary-value">{{ $displayedStudentCount }}</strong><span>studenten in overzicht</span></div>
    </article>
    <article class="coach-summary-card">
      <span class="coach-summary-icon coach-summary-icon-xp" aria-hidden="true">XP</span>
      <div><strong class="coach-summary-value">{{ number_format($totalStudentXp, 0, ',', '.') }}</strong><span>XP verdiend door studenten</span></div>
    </article>
  </section>

  <div class="coach-worklist-grid">
    <section class="coach-worklist-panel" aria-labelledby="coachPendingProjectsTitle">
      <div class="coach-section-heading">
        <div><span class="coach-eyebrow">Eerstvolgende actie</span><h2 id="coachPendingProjectsTitle">Ingeleverd werk</h2><p>Oudste inzendingen staan bovenaan.</p></div>
        <button class="coach-text-link" type="button" onclick="openCoachProjects()">Alle projecten <span aria-hidden="true">→</span></button>
      </div>
      @if (count($frontendData['pendingProjects']))
        <div class="coach-worklist" id="coachPendingProjects">
          @foreach ($frontendData['pendingProjects'] as $project)
            <article class="coach-worklist-item">
              <span class="coach-worklist-icon" aria-hidden="true">↗</span>
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
        <div class="coach-worklist-empty"><strong>Geen ingeleverde projecten</strong><span>Nieuwe inzendingen verschijnen hier ter beoordeling.</span></div>
      @endif
    </section>

    <section class="coach-worklist-panel" aria-labelledby="coachPendingFeedbackTitle">
      <div class="coach-section-heading">
        <div><span class="coach-eyebrow">Opvolging</span><h2 id="coachPendingFeedbackTitle">Feedback bevestigen</h2><p>Bevestig alleen nadat je de verwerking hebt gecontroleerd.</p></div>
      </div>
      @if (count($frontendData['pendingFeedback']))
        <div class="coach-feedback-confirmation-list">
          @foreach ($frontendData['pendingFeedback'] as $feedbackItem)
            <div class="coach-feedback-confirmation-item">
              <span><strong>{{ $feedbackItem['student'] }}</strong><span>{{ $feedbackItem['title'] }} · {{ $feedbackItem['updatedAt'] ?? 'datum onbekend' }}</span></span>
              <form method="POST" action="{{ route('feedback.confirmProcessed', $feedbackItem['id']) }}">
                @csrf
                <button class="button" type="submit">Verwerking bevestigen</button>
              </form>
            </div>
          @endforeach
        </div>
      @else
        <div class="coach-worklist-empty"><strong>Geen feedback te bevestigen</strong><span>Je ziet hier feedback die op jouw bevestiging wacht.</span></div>
      @endif
    </section>
  </div>

  <section class="coach-phase-groups" aria-labelledby="coachPhaseGroupsTitle">
    <div class="coach-section-heading">
      <div><span class="coach-eyebrow">Studiefase-indeling</span><h2 id="coachPhaseGroupsTitle">Studenten per studiefase</h2><p>De beschikbare indeling is gebaseerd op de studiefase in het studentprofiel.</p></div>
      <button class="coach-text-link" type="button" onclick="show('coachStudents')">Studentenlijst openen <span aria-hidden="true">→</span></button>
    </div>
    <div class="coach-phase-group-list">
      @forelse (collect($roleData)->groupBy(fn (array $student): string => $student['phase'] ?? 'Geen studiefase ingesteld') as $phase => $phaseStudents)
        <button class="coach-phase-group" type="button" onclick="openCoachPhase(@js(mb_strtolower($phase)))">
          <span><strong>{{ $phase }}</strong><small>{{ $phaseStudents->count() === 1 ? '1 student' : $phaseStudents->count().' studenten' }}</small></span>
          <span aria-hidden="true">→</span>
        </button>
      @empty
        <div class="coach-worklist-empty"><strong>Er zijn nog geen studenten</strong><span>Studenten verschijnen hier zodra ze in de leeromgeving zijn aangemaakt.</span></div>
      @endforelse
    </div>
  </section>
</section>

<section id="coachStudents" class="view hidden coach-page" aria-labelledby="coachStudentsTitle">
  <header class="coach-page-hero">
    <div>
      <span class="coach-eyebrow">Jouw begeleiding</span>
      <h1 id="coachStudentsTitle">Studentenoverzicht</h1>
      <p>Bekijk de voortgang en ontwikkeling van je studenten en bied gerichte begeleiding.</p>
    </div>
  </header>

  <section class="coach-roster">
    <div class="coach-section-heading">
      <div>
        <span class="coach-eyebrow">Studentenbeheer</span>
        <h2>Mijn studenten</h2>
        <p>Open een profiel om voortgang te bekijken en bij te werken.</p>
      </div>
      <span class="coach-section-count">{{ $displayedStudentCount }} {{ $displayedStudentCount === 1 ? 'student' : 'studenten' }}</span>
    </div>

    @if ($displayedStudentCount)
      {{-- De keuzelijst toont de drie studiefases die in de applicatie worden gebruikt. --}}
      <div class="coach-roster-tools">
        <label class="coach-search">
          <span class="coach-control-label">Student zoeken</span>
          <span class="coach-search-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path></svg>
            <input id="coachStudentSearch" type="search" autocomplete="off" placeholder="Zoek op naam">
          </span>
        </label>
        <label class="coach-phase-filter">
          <span class="coach-control-label">Studiefasegroep</span>
          <select id="coachPhaseFilter">
            <option value="">Alle studiefases</option>
            @foreach (collect($roleData)->pluck('phase')->filter()->unique()->sort() as $phase)
              <option value="{{ mb_strtolower($phase) }}">{{ $phase }}</option>
            @endforeach
            @if (collect($roleData)->contains(fn (array $student): bool => empty($student['phase'])))
              <option value="geen studiefase ingesteld">Geen studiefase ingesteld</option>
            @endif
          </select>
        </label>
        <label class="coach-sort-filter">
          <span class="coach-control-label">Sorteren op</span>
          <select id="coachStudentSort">
            <option value="name">Naam</option>
            <option value="progress-asc">Laagste voortgang</option>
            <option value="xp-asc">Laagste XP totaal</option>
          </select>
        </label>
      </div>

      <div class="coach-student-grid">
        {{-- Elke kaart opent hieronder het voortgangsoverzicht van de betreffende student. --}}
        @foreach ($roleData as $student)
          @php($studentTargetHours = collect($student['outcomes'])->sum(fn (array $outcome): int => (int) $outcome['target']))
          @php($studentProgress = $studentTargetHours > 0 ? min(100, (int) round(($student['hours'] / $studentTargetHours) * 100)) : 0)
          <button
            class="coach-student-card"
            type="button"
            data-coach-student-card="{{ $student['id'] }}"
            data-coach-target-hours="{{ $studentTargetHours }}"
            data-student-progress="{{ $studentProgress }}"
            data-student-xp="{{ $student['xp'] }}"
            data-student-name="{{ mb_strtolower($student['name']) }}"
            data-student-phase="{{ mb_strtolower($student['phase'] ?? 'Geen studiefase ingesteld') }}"
            onclick="show('coachStudent-{{ $student['id'] }}')"
            aria-label="Bekijk voortgang van {{ $student['name'] }}"
          >
            <span class="coach-student-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($student['name'], 0, 1)) }}</span>
            <span class="coach-student-info">
              <strong>{{ $student['name'] }}</strong>
              <span class="coach-phase-badge">{{ $student['phase'] ?? 'Geen studiefase ingesteld' }}</span>
            </span>
            <span class="coach-student-meta">
              <span><strong data-coach-student-hours>{{ $student['hours'] }}</strong> gevalideerde uren</span>
              <span><strong>{{ count($student['outcomes']) }}</strong> leeruitkomsten</span>
            </span>
            <span class="coach-student-level">
              <span class="coach-student-level-badge" aria-hidden="true">{{ $student['level'] }}</span>
              <span class="coach-student-level-copy">
                <strong>Level {{ $student['level'] }} · {{ $student['levelName'] }}</strong>
                <span class="coach-student-level-track" role="progressbar" aria-label="XP-voortgang van {{ $student['name'] }} naar level {{ $student['level'] + 1 }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) round($student['levelProgressPercent']) }}"><span style="width: {{ $student['levelProgressPercent'] }}%"></span></span>
                <small>{{ number_format($student['xp'], 0, ',', '.') }} XP · {{ number_format(1000 - $student['levelProgress'], 0, ',', '.') }} XP tot level {{ $student['level'] + 1 }}</small>
              </span>
            </span>
            <span class="coach-student-progress">
              <span class="coach-progress-label">Voortgang</span>
              <span class="coach-progress-track" data-coach-student-progress role="progressbar" aria-label="Voortgang voor {{ $student['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $studentProgress }}"><span style="width: {{ $studentProgress }}%"></span></span>
              <span class="coach-progress-value" data-coach-student-progress-value>{{ $studentProgress }}%</span>
            </span>
            <span class="coach-student-action">Bekijk voortgang <span aria-hidden="true">→</span></span>
          </button>
        @endforeach

      </div>
      <p class="coach-no-results hidden" id="coachNoResults" role="status">Geen studenten gevonden.</p>
    @else
      {{-- Explain the empty roster instead of leaving the coach with a blank page. --}}
      <div class="coach-empty-state">
        <span class="coach-empty-icon" aria-hidden="true">♙</span>
        <h3>Er zijn nog geen studenten</h3>
        <p>Zodra studenten aan de leeromgeving zijn toegevoegd, verschijnen ze hier.</p>
      </div>
    @endif
  </section>
</section>

{{-- Leeractiviteiten, leerdoelen en projecten zijn alleen-lezenoverzichten voor de coach. --}}
<section id="coachActivities" class="view hidden coach-page coach-directory-page" aria-labelledby="coachActivitiesTitle">
  <header class="coach-page-hero">
    <div>
      <span class="coach-eyebrow">Onderwijsinhoud</span>
      <h1 id="coachActivitiesTitle">Leeractiviteiten</h1>
      <p>Volg aan welke leeruitkomsten studenten werken en waar begeleiding nodig is.</p>
    </div>
    <span class="coach-directory-count"><strong>{{ count($frontendData['activities']) }}</strong><span>leeruitkomsten</span></span>
  </header>

  @if (count($frontendData['activities']))
    <div class="coach-directory-grid">
      @foreach ($frontendData['activities'] as $activity)
        @php($activityCoverage = $frontendData['studentCount'] > 0 ? min(100, (int) round(($activity['activeStudentCount'] / $frontendData['studentCount']) * 100)) : 0)
        <article class="coach-directory-card coach-activity-card">
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
            <span class="coach-directory-mini-progress" role="progressbar" aria-label="Studenten actief bij {{ $activity['title'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $activityCoverage }}"><span style="width: {{ $activityCoverage }}%"></span></span>
          </div>
        </article>
      @endforeach
    </div>
  @else
    <div class="coach-empty-state">
      <span class="coach-empty-icon" aria-hidden="true">✳</span>
      <h3>Er zijn nog geen leeractiviteiten</h3>
      <p>Zodra leeruitkomsten zijn toegevoegd, verschijnen ze hier.</p>
    </div>
  @endif
</section>

<section id="coachGoals" class="view hidden coach-page coach-directory-page" aria-labelledby="coachGoalsTitle">
  <header class="coach-page-hero">
    <div>
      <span class="coach-eyebrow">Persoonlijke ontwikkeling</span>
      <h1 id="coachGoalsTitle">Leerdoelen</h1>
      <p>Bekijk de doelen van studenten en hoe die aansluiten op hun leeruitkomsten.</p>
    </div>
    <span class="coach-directory-count"><strong>{{ count($frontendData['goals']) }}</strong><span>leerdoelen</span></span>
  </header>

  @if (count($frontendData['goals']))
    <div class="coach-directory-grid">
      @foreach ($frontendData['goals'] as $goal)
        <article class="coach-directory-card coach-goal-card">
          <div class="coach-directory-card-head">
            <span class="coach-directory-person">
              <span class="coach-directory-person-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($goal['student'], 0, 1)) }}</span>
              <span class="coach-directory-owner">{{ $goal['student'] }}</span>
            </span>
            <span class="coach-status-badge coach-goal-status">{{ str_replace('_', ' ', ucfirst($goal['status'])) }}</span>
          </div>
          <h2>{{ $goal['title'] }}</h2>
          <p class="coach-directory-description">{{ $goal['description'] }}</p>
          @if ($goal['outcome'])
            <div class="coach-directory-link"><span aria-hidden="true">◎</span><span><small>Gekoppeld aan</small><strong>{{ $goal['outcome'] }}</strong></span></div>
          @else
            <div class="coach-directory-link coach-directory-link-muted"><span aria-hidden="true">◎</span><span><small>Nog niet gekoppeld</small><strong>Geen leeruitkomst geselecteerd</strong></span></div>
          @endif
        </article>
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

<section id="coachProjects" class="view hidden coach-page coach-directory-page" aria-labelledby="coachProjectsTitle">
  <header class="coach-page-hero">
    <div>
      <span class="coach-eyebrow">Werk van studenten</span>
      <h1 id="coachProjectsTitle">Projecten</h1>
      <p>Volg de opdrachten, voortgang en deadlines van je studenten.</p>
    </div>
    <span class="coach-directory-count"><strong>{{ count($frontendData['projects']) }}</strong><span>projecten</span></span>
  </header>

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
    </div>
    <p class="coach-no-results hidden" id="coachProjectNoResults" role="status">Geen projecten gevonden.</p>
  @endif

  @if (count($frontendData['projects']))
    <div class="coach-directory-grid">
      @foreach ($frontendData['projects'] as $project)
        <article
          class="coach-directory-card coach-project-card"
          data-coach-project-card="{{ $project['id'] }}"
          data-project-status="{{ $project['status'] }}"
          data-project-search="{{ mb_strtolower($project['student'].' '.$project['title'].' '.$project['type']) }}"
        >
          <div class="coach-directory-card-head">
            <span class="coach-directory-person">
              <span class="coach-directory-person-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($project['student'], 0, 1)) }}</span>
              <span class="coach-directory-owner">{{ $project['student'] }}</span>
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
      <p>Opdrachten van studenten verschijnen hier als projecten.</p>
    </div>
  @endif
</section>

{{-- Toon per student een bewerkbaar detailoverzicht, zonder een nieuw verzoek te hoeven laden. --}}
@foreach ($roleData as $student)
  @php($studentTargetHours = collect($student['outcomes'])->sum(fn (array $outcome): int => (int) $outcome['target']))
  @php($studentProgress = $studentTargetHours > 0 ? min(100, (int) round(($student['hours'] / $studentTargetHours) * 100)) : 0)
  <section id="coachStudent-{{ $student['id'] }}" class="view hidden coach-page coach-student-detail" data-student-id="{{ $student['id'] }}" aria-labelledby="coachStudentTitle-{{ $student['id'] }}">
    <div class="coach-detail-top">
      <button class="button coach-back-button" type="button" onclick="show('coachStudents')">
        <span aria-hidden="true">←</span> Alle studenten
      </button>
      <span class="coach-eyebrow">Studentoverzicht</span>
    </div>

    <header class="coach-student-hero">
      <span class="coach-student-avatar coach-student-avatar-large" aria-hidden="true">{{ mb_strtoupper(mb_substr($student['name'], 0, 1)) }}</span>
      <div>
        <h1 id="coachStudentTitle-{{ $student['id'] }}">{{ $student['name'] }}</h1>
        <p>{{ $student['phase'] ?? 'Geen studiefase ingesteld' }}</p>
      </div>
    </header>

    {{-- These figures give context before the coach reviews individual learning outcomes. --}}
    <section class="coach-student-stats" aria-label="Studentvoortgang">
      <article class="coach-detail-stat">
        <span class="coach-detail-stat-label">Gevalideerde uren</span>
        <strong data-coach-total-hours>{{ $student['hours'] }} <small>uur</small></strong>
      </article>
      <article class="coach-detail-stat">
        <span class="coach-detail-stat-label">Leeruitkomsten</span>
        <strong>{{ count($student['outcomes']) }}</strong>
      </article>
      <article class="coach-detail-stat">
        <span class="coach-detail-stat-label">Studiefase</span>
        <strong class="coach-detail-phase">{{ $student['phase'] ?? '—' }}</strong>
      </article>
      <article class="coach-detail-stat coach-detail-stat-progress">
        <span class="coach-detail-stat-label">Voortgang</span>
        <strong data-coach-progress-value>{{ $studentProgress }}%</strong>
        <span class="coach-detail-progress-track" data-coach-progress-bar role="progressbar" aria-label="Voortgang van {{ $student['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $studentProgress }}"><span style="width: {{ $studentProgress }}%"></span></span>
      </article>
    </section>

    <section class="coach-xp-overview" aria-label="XP- en levelvoortgang">
      <span class="coach-xp-level-badge" aria-hidden="true">{{ $student['level'] }}</span>
      <div class="coach-xp-overview-copy">
        <span class="coach-eyebrow">XP-voortgang</span>
        <h2>Level {{ $student['level'] }} · {{ $student['levelName'] }}</h2>
        <p>{{ number_format($student['xp'], 0, ',', '.') }} XP verdiend · nog {{ number_format(1000 - $student['levelProgress'], 0, ',', '.') }} XP tot level {{ $student['level'] + 1 }}</p>
        <span class="coach-xp-overview-track" role="progressbar" aria-label="XP-voortgang van {{ $student['name'] }} naar level {{ $student['level'] + 1 }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) round($student['levelProgressPercent']) }}"><span style="width: {{ $student['levelProgressPercent'] }}%"></span></span>
      </div>
      <span class="coach-xp-level-caption">{{ number_format($student['levelProgress'], 0, ',', '.') }} / 1.000 XP naar volgend level</span>
    </section>

    <section class="coach-progress-section">
      <div class="coach-section-heading">
        <div>
          <span class="coach-eyebrow">Begeleiding</span>
          <h2>Voortgang per leeruitkomst</h2>
          <p>Lopende leeruitkomsten staan bovenaan. Pas uren en status aan op basis van jullie gesprek.</p>
        </div>
        <span class="coach-section-count">{{ count($student['outcomes']) }} leeruitkomsten</span>
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
        </div>
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
            <span class="coach-status-badge coach-status-{{ $outcome['status'] }}" data-outcome-status-label>{{ str_replace('_', ' ', ucfirst($outcome['status'])) }}</span>
          </div>

          <form class="coach-outcome-form" method="POST" action="{{ route('progress.update', [$student['id'], $outcome['id']]) }}">
            {{-- Protect the update request and tell Laravel to handle the POST as a PATCH. --}}
            @csrf
            @method('PATCH')
            <label class="label coach-field">
              Gevalideerde punten
              <span class="coach-input-wrap">
                <input class="input" name="gevalideerde_uren" type="number" min="0" max="10000" value="{{ $outcome['hours'] }}" required>
                <span>punten</span>
              </span>
              {{-- Escape the target text before displaying it, while retaining its line breaks. --}}
              @if ($outcome['target'])
                <small>Normwaarden: {!! nl2br(e($outcome['target'])) !!}</small>
              @endif
            </label>
            <label class="label coach-field">
              Voortgangsstatus
              {{-- Keep the status choices aligned with the progress values accepted by the controller. --}}
              <select class="input" name="status" required>
                @foreach ([
                  'niet_gestart' => 'Niet gestart',
                  'gestart' => 'Gestart',
                  'in_ontwikkeling' => 'In ontwikkeling',
                  'behaald' => 'Behaald',
                ] as $waarde => $label)
                  <option value="{{ $waarde }}" @selected($outcome['status'] === $waarde)>{{ $label }}</option>
                @endforeach
              </select>
            </label>
            <button class="button dark coach-save-button" type="submit">Wijzigingen opslaan <span aria-hidden="true">→</span></button>
            <span class="coach-save-feedback" role="status" aria-live="polite"></span>
          </form>
        </article>
      @empty
        {{-- Give a clear explanation when no progress rows exist for this student yet. --}}
        <div class="coach-empty-state coach-outcomes-empty">
          <span class="coach-empty-icon" aria-hidden="true">◎</span>
          <h3>Nog geen voortgang geregistreerd</h3>
          <p>Er zijn voor deze student nog geen voortgangsregels om te bekijken of aan te passen.</p>
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
@endsection
