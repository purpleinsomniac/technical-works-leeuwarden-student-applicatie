@extends('layouts.application')

@section('role-content')
<section id="coachStudents" class="view hidden coach-page">
  <header class="coach-page-hero">
    <div>
      <span class="coach-eyebrow">SLB'er / coach</span>
      <h1>Studenten begeleiden</h1>
      <p>Bekijk de voortgang van je studenten en werk uren en status per leeruitkomst bij.</p>
    </div>
  </header>

  @if (session('status'))
    <div class="notice coach-success" role="status">{{ session('status') }}</div>
  @endif

  <section class="coach-summary" aria-label="Begeleiding in cijfers">
    <article class="coach-summary-card">
      <span class="coach-summary-icon" aria-hidden="true">♙</span>
      <div>
        <strong>{{ count($roleData) }}</strong>
        <span>Studenten</span>
      </div>
    </article>
    <article class="coach-summary-card">
      <span class="coach-summary-icon coach-summary-icon-hours" aria-hidden="true">◷</span>
      <div>
        <strong>{{ collect($roleData)->sum('hours') }}</strong>
        <span>Gevalideerde uren</span>
      </div>
    </article>
    <article class="coach-summary-card">
      <span class="coach-summary-icon coach-summary-icon-outcomes" aria-hidden="true">◎</span>
      <div>
        <strong>{{ collect($roleData)->sum(fn (array $student): int => count($student['outcomes'])) }}</strong>
        <span>Geregistreerde leeruitkomsten</span>
      </div>
    </article>
  </section>

  <section class="coach-roster">
    <div class="coach-section-heading">
      <div>
        <h2>Mijn studenten</h2>
        <p>Kies een student om de voortgang te bekijken of aan te passen.</p>
      </div>
      <span class="coach-section-count">{{ count($roleData) }} in totaal</span>
    </div>

    @if (count($roleData))
      <div class="coach-student-grid">
        @foreach ($roleData as $student)
          <button class="coach-student-card" type="button" onclick="show('coachStudent-{{ $student['id'] }}')" aria-label="Bekijk voortgang van {{ $student['name'] }}">
            <span class="coach-student-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($student['name'], 0, 1)) }}</span>
            <span class="coach-student-info">
              <strong>{{ $student['name'] }}</strong>
              <span>{{ $student['phase'] ?? 'Geen studiefase ingesteld' }}</span>
            </span>
            <span class="coach-student-arrow" aria-hidden="true">→</span>
            <span class="coach-student-meta">
              <span><strong>{{ $student['hours'] }}</strong> gevalideerde uren</span>
              <span><strong>{{ count($student['outcomes']) }}</strong> leeruitkomsten</span>
            </span>
          </button>
        @endforeach
      </div>
    @else
      <div class="coach-empty-state">
        <span class="coach-empty-icon" aria-hidden="true">♙</span>
        <h3>Er zijn nog geen studenten</h3>
        <p>Zodra studenten aan de leeromgeving zijn toegevoegd, verschijnen ze hier.</p>
      </div>
    @endif
  </section>
</section>

@foreach ($roleData as $student)
  <section id="coachStudent-{{ $student['id'] }}" class="view hidden coach-page coach-student-detail">
    <div class="coach-detail-top">
      <button class="button coach-back-button" type="button" onclick="show('coachStudents')">
        <span aria-hidden="true">←</span> Alle studenten
      </button>
      <span class="coach-eyebrow">Studentoverzicht</span>
    </div>

    <header class="coach-student-hero">
      <span class="coach-student-avatar coach-student-avatar-large" aria-hidden="true">{{ mb_strtoupper(mb_substr($student['name'], 0, 1)) }}</span>
      <div>
        <h1>{{ $student['name'] }}</h1>
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
          <p>Pas uren en status aan op basis van jullie gesprek.</p>
        </div>
        <span class="coach-section-count">{{ count($student['outcomes']) }} leeruitkomsten</span>
      </div>

      @forelse ($student['outcomes'] as $outcome)
        <article class="coach-outcome-card">
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
    </section>
  </section>
@endforeach
@endsection
