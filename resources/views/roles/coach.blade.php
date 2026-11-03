<section id="coachStudents" class="view hidden">
  <div class="coach-hero">
    <div>
      <p class="eyebrow">SLB'er / coach</p>
      <h1>Studenten begeleiden</h1>
      <p class="coach-intro">Bekijk de voortgang van je studenten en werk uren en leeruitkomsten eenvoudig bij.</p>
    </div>
  </div>

  @if (session('status'))
    <div class="notice" role="status">{{ session('status') }}</div>
  @endif

  @php($totalHours = array_sum(array_column($roleData, 'hours')))
  @php($totalOutcomes = collect($roleData)->sum(fn ($student) => count($student['outcomes'])))
  <div class="coach-metrics" aria-label="Totaaloverzicht">
    <article class="coach-metric">
      <span class="coach-metric-icon coach-metric-icon--students" aria-hidden="true">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"></circle><path d="M3 20v-1.5a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5V20M16 5.2a3.5 3.5 0 0 1 0 6.6M18 14a4 4 0 0 1 3 3.9V20"></path></svg>
      </span>
      <div>
        <strong>{{ count($roleData) }}</strong>
        <span>{{ count($roleData) === 1 ? 'Student' : 'Studenten' }}</span>
      </div>
    </article>
    <article class="coach-metric">
      <span class="coach-metric-icon coach-metric-icon--hours" aria-hidden="true">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7v5l3.5 2"></path></svg>
      </span>
      <div>
        <strong>{{ $totalHours }}</strong>
        <span>Gevalideerde uren</span>
      </div>
    </article>
    <article class="coach-metric">
      <span class="coach-metric-icon coach-metric-icon--outcomes" aria-hidden="true">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"></circle><circle cx="12" cy="12" r="4.5"></circle><circle cx="12" cy="12" r="1"></circle></svg>
      </span>
      <div>
        <strong>{{ $totalOutcomes }}</strong>
        <span>Geregistreerde leeruitkomsten</span>
      </div>
    </article>
  </div>

  <section class="coach-students-section" aria-labelledby="coachStudentsTitle">
    <div class="coach-section-heading">
      <div>
        <h2 id="coachStudentsTitle">Mijn studenten</h2>
        <p class="dim">Selecteer een student om de voortgang te bekijken of aan te passen.</p>
      </div>
      <span class="pill">{{ count($roleData) }} {{ count($roleData) === 1 ? 'student' : 'studenten' }}</span>
    </div>

    @if (count($roleData) === 0)
      <div class="coach-empty-state">
        <span class="coach-empty-icon" aria-hidden="true">S</span>
        <h3>Er zijn nog geen studenten</h3>
        <p class="dim">Studenten verschijnen hier zodra ze aan de leeromgeving zijn toegevoegd.</p>
      </div>
    @else
      <div class="coach-student-list">
        @foreach ($roleData as $student)
          <button class="coach-student-card" type="button" onclick="show('coachStudent-{{ $student['id'] }}')" aria-label="Bekijk de voortgang van {{ $student['name'] }}">
            <span class="coach-student-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($student['name'], 0, 1)) }}</span>
            <span class="coach-student-content">
              <span class="coach-student-name">{{ $student['name'] }}</span>
              <span class="coach-student-phase">{{ $student['phase'] ?? 'Geen studiefase ingesteld' }}</span>
              <span class="coach-student-stats">
                <span><strong>{{ $student['hours'] }}</strong> gevalideerde uren</span>
                <span><strong>{{ count($student['outcomes']) }}</strong> leeruitkomsten</span>
              </span>
            </span>
            <span class="coach-student-action" aria-hidden="true">Bekijk voortgang <span>→</span></span>
          </button>
        @endforeach
      </div>
    @endif
  </section>
</section>

@foreach ($roleData as $student)
  <section id="coachStudent-{{ $student['id'] }}" class="view hidden">
    <div class="coach-hero coach-detail-hero">
      <div>
        <p class="eyebrow">Studentvoortgang</p>
        <h1>{{ $student['name'] }}</h1>
        <p class="coach-intro">Bekijk en werk uren en status per leeruitkomst bij.</p>
      </div>
      <button class="button coach-back-button" type="button" onclick="show('coachStudents')"><span aria-hidden="true">←</span> Alle studenten</button>
    </div>

    <div class="coach-metrics coach-detail-metrics" aria-label="Studentoverzicht">
      <article class="coach-metric">
        <span class="coach-metric-icon coach-metric-icon--hours" aria-hidden="true">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7v5l3.5 2"></path></svg>
        </span>
        <div><strong>{{ $student['hours'] }}</strong><span>Gevalideerde uren</span></div>
      </article>
      <article class="coach-metric">
        <span class="coach-metric-icon coach-metric-icon--outcomes" aria-hidden="true">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"></circle><circle cx="12" cy="12" r="4.5"></circle><circle cx="12" cy="12" r="1"></circle></svg>
        </span>
        <div><strong>{{ count($student['outcomes']) }}</strong><span>Leeruitkomsten</span></div>
      </article>
      <article class="coach-metric">
        <span class="coach-metric-icon coach-metric-icon--phase" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="m3 9 9-5 9 5-9 5-9-5Z"></path><path d="M7 11.2V16c3.2 2.4 6.8 2.4 10 0v-4.8M21 9v6"></path></svg>
        </span>
        <div><strong class="coach-phase-value">{{ $student['phase'] ?? '—' }}</strong><span>Huidige studiefase</span></div>
      </article>
    </div>

    <section class="coach-outcomes-section" aria-labelledby="coachOutcomesTitle-{{ $student['id'] }}">
      <div class="coach-section-heading">
        <div>
          <h2 id="coachOutcomesTitle-{{ $student['id'] }}">Voortgang per leeruitkomst</h2>
          <p class="dim">Wijzig alleen de gegevens die je met de student hebt besproken.</p>
        </div>
      </div>
      <div class="coach-outcome-list">
        @forelse ($student['outcomes'] as $outcome)
          <form class="coach-outcome-form" method="POST" action="{{ route('progress.update', [$student['id'], $outcome['id']]) }}">
            @csrf
            @method('PATCH')
            <div class="coach-outcome-info">
              <h3>{{ $outcome['title'] }}</h3>
              <span class="code">{{ $outcome['code'] }}</span>
            </div>
            <label class="label coach-field">
              Uren
              <input class="input" name="gevalideerde_uren" type="number" min="0" max="10000" value="{{ $outcome['hours'] }}" required>
            </label>
            <label class="label coach-field">
              Status
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
            <button class="button dark coach-save-button" type="submit">Opslaan</button>
          </form>
        @empty
          <p class="coach-outcome-empty">Voor deze student zijn nog geen leeruitkomsten geregistreerd.</p>
        @endforelse
      </div>
    </section>
  </section>
@endforeach

{{-- Managementweergaven. --}}
