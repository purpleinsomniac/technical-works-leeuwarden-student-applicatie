@extends('layouts.application')

@section('role-content')
@php($previewStudents = [
  ['name' => 'Sem Jansen',      'phase' => 'Oriëntatiefase',  'hours' => 142, 'outcomes' => 48, 'progress' => 15],
  ['name' => 'Noor Bakker',     'phase' => 'Oriëntatiefase',  'hours' => 268, 'outcomes' => 48, 'progress' => 28],
  ['name' => 'Fleur Smit',      'phase' => 'Oriëntatiefase',  'hours' => 305, 'outcomes' => 48, 'progress' => 32],
  ['name' => 'Yara El Amrani',  'phase' => 'Propedeusefase',  'hours' => 468, 'outcomes' => 48, 'progress' => 49],
  ['name' => 'Milan Visser',    'phase' => 'Propedeusefase',  'hours' => 512, 'outcomes' => 48, 'progress' => 54],
  ['name' => 'Daan Mulder',     'phase' => 'Propedeusefase',  'hours' => 590, 'outcomes' => 48, 'progress' => 62],
  ['name' => 'Thijs de Boer',   'phase' => 'Verdiepingsfase', 'hours' => 681, 'outcomes' => 48, 'progress' => 72],
  ['name' => 'Aisha de Jong',   'phase' => 'Verdiepingsfase', 'hours' => 734, 'outcomes' => 48, 'progress' => 77],
  ['name' => 'Lotte Hendriks',  'phase' => 'Verdiepingsfase', 'hours' => 812, 'outcomes' => 48, 'progress' => 85],
  ['name' => 'Jesse Bos',       'phase' => 'Afstudeerfase',   'hours' => 858, 'outcomes' => 48, 'progress' => 90],
  ['name' => 'Lars Meijer',     'phase' => 'Afstudeerfase',   'hours' => 891, 'outcomes' => 48, 'progress' => 94],
  ['name' => 'Emma van Dijk',   'phase' => 'Afstudeerfase',   'hours' => 925, 'outcomes' => 48, 'progress' => 97],
])
@php($displayedStudentCount = count($roleData) + count($previewStudents))
@php($displayedPhaseCount = collect($roleData)->pluck('phase')->merge(collect($previewStudents)->pluck('phase'))->filter()->unique()->count())
@php($activeStudentCount = count($roleData))
<section id="coachStudents" class="view hidden coach-page" aria-labelledby="coachStudentsTitle">
  <header class="coach-page-hero">
    <div>
      <span class="coach-eyebrow">Jouw begeleiding</span>
      <h1 id="coachStudentsTitle">Studenten</h1>
      <p>Een helder overzicht van de voortgang en ontwikkeling van je studenten.</p>
    </div>
  </header>

  @if (session('status'))
    <div class="notice coach-success" role="status">{{ session('status') }}</div>
  @endif

  <section class="coach-summary" aria-label="Begeleiding in cijfers">
    <article class="coach-summary-card">
      <span class="coach-summary-icon" aria-hidden="true">01</span>
      <div>
        <strong class="coach-summary-value">{{ $displayedStudentCount }}</strong>
        <span>studenten in overzicht</span>
      </div>
    </article>
    <article class="coach-summary-card">
      <span class="coach-summary-icon coach-summary-icon-hours" aria-hidden="true">✳</span>
      <div>
        <strong class="coach-summary-value">{{ $displayedPhaseCount }}</strong>
        <span>studiefases vertegenwoordigd</span>
      </div>
    </article>
    <article class="coach-summary-card">
      <span class="coach-summary-icon coach-summary-icon-outcomes" aria-hidden="true">↗</span>
      <div>
        <strong class="coach-summary-value">{{ $activeStudentCount }}</strong>
        <span>{{ $activeStudentCount === 1 ? 'profiel gekoppeld' : 'profielen gekoppeld' }}</span>
      </div>
    </article>
  </section>

  <section class="coach-roster">
    <div class="coach-section-heading">
      <div>
        <span class="coach-eyebrow">Overzicht</span>
        <h2>Mijn studenten</h2>
        <p>Open een profiel om voortgang te bekijken en bij te werken.</p>
      </div>
      <span class="coach-section-count">{{ $displayedStudentCount }} studenten</span>
    </div>

    @if ($displayedStudentCount)
      <div class="coach-roster-tools">
        <label class="coach-search">
          <span class="coach-control-label">Student zoeken</span>
          <span class="coach-search-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path></svg>
            <input id="coachStudentSearch" type="search" autocomplete="off" placeholder="Zoek op naam">
          </span>
        </label>
        <label class="coach-phase-filter">
          <span class="coach-control-label">Studiefase</span>
          <select id="coachPhaseFilter">
            <option value="">Alle studiefases</option>
            @foreach (collect($roleData)->pluck('phase')->merge(collect($previewStudents)->pluck('phase'))->filter()->unique()->sort()->values() as $phase)
              <option value="{{ mb_strtolower($phase) }}">{{ $phase }}</option>
            @endforeach
          </select>
        </label>
      </div>

      <div class="coach-student-grid">
        <div class="coach-student-head" aria-hidden="true">
          <span></span><span>Student</span><span>Gevalideerd</span><span>Voortgang</span><span></span>
        </div>

        @foreach ($roleData as $student)
          @php($studentTargetHours = collect($student['outcomes'])->sum('target'))
          @php($studentProgress = $studentTargetHours > 0 ? min(100, (int) round(($student['hours'] / $studentTargetHours) * 100)) : 0)
          <button
            class="coach-student-card"
            type="button"
            data-student-name="{{ mb_strtolower($student['name']) }}"
            data-student-phase="{{ mb_strtolower($student['phase'] ?? '') }}"
            onclick="show('coachStudent-{{ $student['id'] }}')"
            aria-label="Bekijk voortgang van {{ $student['name'] }}"
          >
            <span class="coach-student-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($student['name'], 0, 1)) }}</span>
            <span class="coach-student-info">
              <strong>{{ $student['name'] }}</strong>
              <span class="coach-phase-badge">{{ $student['phase'] ?? 'Geen studiefase ingesteld' }}</span>
            </span>
            <span class="coach-student-meta">
              <span><strong>{{ $student['hours'] }}</strong> gevalideerde uren</span>
              <span><strong>{{ count($student['outcomes']) }}</strong> leeruitkomsten</span>
            </span>
            <span class="coach-student-progress">
              <span class="coach-progress-label">Voortgang</span>
              <span class="coach-progress-track" role="progressbar" aria-label="Voortgang voor {{ $student['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $studentProgress }}"><span style="width: {{ $studentProgress }}%"></span></span>
              <span class="coach-progress-value">{{ $studentProgress }}%</span>
            </span>
            <span class="coach-student-action"><span aria-hidden="true">→</span></span>
          </button>
        @endforeach

        @foreach ($previewStudents as $student)
          <article
            class="coach-student-card coach-student-card-static"
            data-student-name="{{ mb_strtolower($student['name']) }}"
            data-student-phase="{{ mb_strtolower($student['phase']) }}"
            data-preview-student
          >
            <span class="coach-student-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($student['name'], 0, 1)) }}</span>
            <span class="coach-student-info">
              <strong>{{ $student['name'] }}</strong>
              <span class="coach-phase-badge">{{ $student['phase'] }}</span>
            </span>
            <span class="coach-student-meta">
              <span><strong>{{ $student['hours'] }}</strong> gevalideerde uren</span>
              <span><strong>{{ $student['outcomes'] }}</strong> leeruitkomsten</span>
            </span>
            <span class="coach-student-progress">
              <span class="coach-progress-label">Voortgang</span>
              <span class="coach-progress-track" role="progressbar" aria-label="Voortgang van {{ $student['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $student['progress'] }}"><span style="width: {{ $student['progress'] }}%"></span></span>
              <span class="coach-progress-value">{{ $student['progress'] }}%</span>
            </span>
          </article>
        @endforeach
      </div>
      <p class="coach-no-results hidden" id="coachNoResults" role="status">Geen studenten gevonden.</p>
    @else
      <div class="coach-empty-state">
        <span class="coach-empty-icon" aria-hidden="true">♙</span>
        <h3>Er zijn nog geen studenten</h3>
        <p>Zodra studenten aan de leeromgeving zijn toegevoegd, verschijnen ze hier.</p>
      </div>
    @endif
  </section>
</section>

<section id="coachActivities" class="view hidden coach-page coach-directory-page" aria-labelledby="coachActivitiesTitle">
  <header class="coach-page-hero">
    <div>
      <span class="coach-eyebrow">Onderwijsinhoud</span>
      <h1 id="coachActivitiesTitle">Leeractiviteiten</h1>
      <p>Leeruitkomsten en activiteiten die de studenten helpen zich te ontwikkelen.</p>
    </div>
    <span class="coach-section-count">{{ count($frontendData['activities']) }} activiteiten</span>
  </header>

  @if (count($frontendData['activities']))
    <div class="coach-directory-grid">
      @foreach ($frontendData['activities'] as $activity)
        <article class="coach-directory-card">
          <div class="coach-directory-meta">
            <span class="coach-directory-code">{{ $activity['code'] }}</span>
            <span class="coach-phase-badge">{{ $activity['phase'] ?? 'Geen studiefase' }}</span>
          </div>
          <h2>{{ $activity['title'] }}</h2>
          <p>{{ $activity['description'] }}</p>
          <div class="coach-directory-footer">
            <span>{{ $activity['activeStudentCount'] }} {{ $activity['activeStudentCount'] === 1 ? 'student werkt' : 'studenten werken' }} hier momenteel aan</span>
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
      <p>De leerdoelen die studenten voor hun ontwikkeling hebben aangemaakt.</p>
    </div>
    <span class="coach-section-count">{{ count($frontendData['goals']) }} leerdoelen</span>
  </header>

  @if (count($frontendData['goals']))
    <div class="coach-directory-grid">
      @foreach ($frontendData['goals'] as $goal)
        <article class="coach-directory-card">
          <div class="coach-directory-meta">
            <span class="coach-directory-owner">{{ $goal['student'] }}</span>
            <span class="coach-status-badge">{{ str_replace('_', ' ', ucfirst($goal['status'])) }}</span>
          </div>
          <h2>{{ $goal['title'] }}</h2>
          <p>{{ $goal['description'] }}</p>
          @if ($goal['outcome'])
            <div class="coach-directory-footer">
              <span>Gekoppeld aan: {{ $goal['outcome'] }}</span>
            </div>
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
      <p>Projecten zijn gebaseerd op de opdrachten die studenten uitvoeren.</p>
    </div>
    <span class="coach-section-count">{{ count($frontendData['projects']) }} projecten</span>
  </header>

  @if (count($frontendData['projects']))
    <div class="coach-directory-grid">
      @foreach ($frontendData['projects'] as $project)
        <article class="coach-directory-card">
          <div class="coach-directory-meta">
            <span class="coach-directory-owner">{{ $project['student'] }}</span>
            <span class="coach-status-badge coach-status-{{ $project['status'] }}">{{ str_replace('_', ' ', ucfirst($project['status'])) }}</span>
          </div>
          <h2>{{ $project['title'] }}</h2>
          <p>{{ $project['description'] ?: $project['type'] }}</p>
          <div class="coach-directory-footer">
            <span>{{ $project['type'] }}</span>
            @if ($project['dueDate'])
              <span>Inleverdatum: {{ $project['dueDate'] }}</span>
            @endif
          </div>
          @if (count($project['outcomes']))
            <p class="coach-directory-outcomes">Leeruitkomsten: {{ implode(', ', $project['outcomes']) }}</p>
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

@foreach ($roleData as $student)
  <section id="coachStudent-{{ $student['id'] }}" class="view hidden coach-page coach-student-detail" aria-labelledby="coachStudentTitle-{{ $student['id'] }}">
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

    <section class="coach-student-stats" aria-label="Studentvoortgang">
      <article class="coach-detail-stat">
        <span class="coach-detail-stat-label">Gevalideerde uren</span>
        <strong>{{ $student['hours'] }} <small>uur</small></strong>
      </article>
      <article class="coach-detail-stat">
        <span class="coach-detail-stat-label">Leeruitkomsten</span>
        <strong>{{ count($student['outcomes']) }}</strong>
      </article>
      <article class="coach-detail-stat">
        <span class="coach-detail-stat-label">Studiefase</span>
        <strong class="coach-detail-phase">{{ $student['phase'] ?? '—' }}</strong>
      </article>
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
            <span class="coach-status-badge coach-status-{{ $outcome['status'] }}">{{ str_replace('_', ' ', ucfirst($outcome['status'])) }}</span>
          </div>

          <form class="coach-outcome-form" method="POST" action="{{ route('progress.update', [$student['id'], $outcome['id']]) }}">
            @csrf
            @method('PATCH')
            <label class="label coach-field">
              Gevalideerde uren
              <span class="coach-input-wrap">
                <input class="input" name="gevalideerde_uren" type="number" min="0" max="10000" value="{{ $outcome['hours'] }}" required>
                <span>uur</span>
              </span>
              @if ($outcome['target'])
                <small>Norm: {{ $outcome['target'] }} uur</small>
              @endif
            </label>
            <label class="label coach-field">
              Voortgangsstatus
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
          </form>
        </article>
      @empty
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