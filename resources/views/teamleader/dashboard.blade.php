@extends('layouts.application')

@section('role-content')
<section id="managerGroups" class="view hidden" data-role-section="groups">
  <div class="head">
    <div>
      <h1>Groepsoverzicht</h1>
      <p class="dim">Voortgang en studenten per groep.</p>
    </div>
  </div>

  @forelse ($roleData['groups'] as $group)
    <article class="card" aria-labelledby="teamleader-group-{{ $group['id'] }}">
      <div class="head">
        <div>
          <h2 id="teamleader-group-{{ $group['id'] }}">{{ $group['name'] }}</h2>
          <p class="dim">
            Schooljaar: {{ $group['schoolYear'] ?: 'Niet ingesteld' }}
            · Docent / SLB'er: {{ $group['coach'] ?: 'Niet toegewezen' }}
          </p>
          <p>{{ $group['description'] ?: 'Geen omschrijving beschikbaar.' }}</p>
        </div>
      </div>

      <div class="grid3">
        <div class="card"><div class="big">{{ $group['studentCount'] }}</div><span class="dim">studenten</span></div>
        <div class="card"><div class="big">{{ $group['averagePercentage'] }}%</div><span class="dim">gemiddelde voortgang</span></div>
        <div class="card"><div class="big">{{ $group['attentionCount'] }}</div><span class="dim">voortgangsregels vragen aandacht</span></div>
      </div>

      <div class="card">
        <h3>Gemiddelde per fase</h3>
        @forelse ($group['phases'] as $fase => $percentage)
          <div class="kv">
            <b>{{ $fase }}</b>
            <div class="bar"><span style="width: {{ $percentage }}%"></span></div>
            <span>{{ $percentage }}%</span>
          </div>
        @empty
          <p class="dim">Er zijn nog geen studiefases ingesteld.</p>
        @endforelse
      </div>

      <div class="card">
        <h3>Studenten in deze groep</h3>
        @forelse ($group['students'] as $student)
          <details>
            <summary>
              <strong>{{ $student['name'] }}</strong>
              <span class="dim">{{ $student['studyPhase'] ?: 'Geen studiefase ingesteld' }}</span>
              <span>{{ $student['displayPercentage'] }}% gemiddelde voortgang{{ $student['studyPhase'] ? ' in '.$student['studyPhase'] : '' }}</span>
            </summary>
            <div class="bar" role="progressbar" aria-label="Voortgang van {{ $student['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $student['percentage'] }}">
              <span style="width: {{ $student['percentage'] }}%"></span>
            </div>
            <h4>Voortgang per leeruitkomst</h4>
            @if ($student['outcomes']->isNotEmpty())
              <div class="actions">
                <label class="label">
                  Filter voortgang op fase
                  <select data-teamleader-phase-filter aria-label="Filter voortgang van {{ $student['name'] }} op fase">
                    <option value="__all">Alle fases</option>
                    @foreach ($student['outcomes']->pluck('phase')->filter()->unique()->sort()->values() as $fase)
                      <option value="{{ $fase }}">{{ $fase }}</option>
                    @endforeach
                    @if ($student['outcomes']->contains(fn ($outcome) => $outcome['phase'] === null))
                      <option value="">Geen fase</option>
                    @endif
                  </select>
                </label>
                <span data-teamleader-phase-average aria-live="polite">Gemiddelde voortgang over alle fases: {{ $student['percentage'] }}%</span>
              </div>
            @endif
            @forelse ($student['outcomes'] as $outcome)
              <div
                class="row"
                data-teamleader-progress-phase="{{ $outcome['phase'] ?? '' }}"
                data-teamleader-progress-percentage="{{ $outcome['percentage'] ?? '' }}"
              >
                <b>{{ $outcome['code'] }} — {{ $outcome['title'] }}</b>
                <span>{{ $outcome['phase'] ?: 'Geen fase' }}</span>
                <span>{{ $outcome['hours'] }}{{ $outcome['target'] ? ' / '.$outcome['target'] : '' }} uur</span>
                <span>{{ $outcome['percentage'] === null ? 'Geen norm' : $outcome['percentage'].'%' }}</span>
                <span>{{ str_replace('_', ' ', ucfirst($outcome['status'])) }}</span>
              </div>
            @empty
              <p class="dim">Er is nog geen voortgang geregistreerd.</p>
            @endforelse
          </details>
        @empty
          <p class="dim">Er zijn nog geen studenten aan deze groep gekoppeld.</p>
        @endforelse
      </div>
    </article>
  @empty
    <div class="card">
      <p>Er zijn nog geen groepen aangemaakt door de beheerder.</p>
    </div>
  @endforelse
</section>

<section id="managerTeachers" class="view hidden" data-role-section="teachers">
  <div class="head">
    <div>
      <h1>Docenten</h1>
      <p class="dim">Overzicht van alle Docenten.</p>
    </div>
  </div>
  <div class="card manager-teachers-table-wrap">
    <table class="manager-teachers-table">
      <thead><tr><th>Naam</th><th>E-mailadres</th><th>Accounttype</th></tr></thead>
      <tbody>
        @forelse ($roleData['coaches'] as $coach)
          <tr>
            <td>{{ $coach->naam }}</td>
            <td><a href="mailto:{{ $coach->email }}">{{ $coach->email }}</a></td>
            <td>{{ ucfirst($coach->primaryRole?->naam ?? $coach->rol) }}</td>
          </tr>
        @empty
          <tr><td colspan="3">Er zijn nog geen docenten.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>

{{-- Report view reuses the same totals and explains why export is not yet available. --}}
<section id="managerReports" class="view hidden" data-role-section="reports">
  <h1>Rapportages</h1>
  <p class="dim">Dezelfde actuele databasecijfers in een compact overzicht.</p>
  <div class="grid2">
    <div class="card">
      <h2>Huidige stand</h2>
      <p><b>{{ $roleData['studentCount'] }}</b> studenten hebben samen een gemiddelde voortgang van <b>{{ $roleData['averagePercentage'] }}%</b>.</p>
    </div>
    <div class="card">
      <h2>Export</h2>
      <p class="dim">Een bestandsexport is nog niet aanwezig; daarvoor moeten eerst het formaat en de privacyregels worden vastgesteld.</p>
    </div>
  </div>
</section>

@include('components.role-dashboard-settings', [
  'preferencesPageId' => 'managerPreferencesSettings',
])

@endsection
