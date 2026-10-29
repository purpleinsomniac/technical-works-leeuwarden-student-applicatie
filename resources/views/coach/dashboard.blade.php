@extends('layouts.application')

@section('role-content')
<section id="coachStudents" class="view hidden">
  <div class="head">
    <div>
      <h1>Mijn studenten</h1>
      <p class="dim">Bekijk de actuele voortgang die in de database staat.</p>
    </div>
    <span class="pill">{{ count($roleData) }} STUDENTEN</span>
  </div>

  @if (session('status'))
    <div class="notice">{{ session('status') }}</div>
  @endif

  <div class="card">
    <h2>Studentenoverzicht</h2>
    @forelse ($roleData as $student)
      <button class="simple click" type="button" onclick="show('coachStudent-{{ $student['id'] }}')">
        <b>{{ $student['name'] }}</b>
        <span>{{ $student['phase'] ?? 'Geen fase' }} · {{ $student['hours'] }} gevalideerde uren</span>
        <span>{{ count($student['outcomes']) }} leeruitkomsten →</span>
      </button>
    @empty
      <p class="dim">Er staan nog geen studenten in de database.</p>
    @endforelse
  </div>
</section>

@foreach ($roleData as $student)
  <section id="coachStudent-{{ $student['id'] }}" class="view hidden">
    <div class="head">
      <div>
        <h1>{{ $student['name'] }}</h1>
        <p class="dim">Pas uren en status per leeruitkomst aan.</p>
      </div>
      <button class="button" type="button" onclick="show('coachStudents')">← Studenten</button>
    </div>

    <div class="grid3">
      <div class="card">
        <div class="big">{{ $student['hours'] }} uur</div>
        <span class="dim">Totaal gevalideerd</span>
      </div>
      <div class="card">
        <div class="big">{{ count($student['outcomes']) }}</div>
        <span class="dim">Leeruitkomsten</span>
      </div>
      <div class="card">
        <div class="big">{{ $student['phase'] ?? '—' }}</div>
        <span class="dim">Huidige studiefase</span>
      </div>
    </div>

    <div class="card coach-progress-panel">
      <div class="coach-progress-heading">
        <div>
          <h2>Voortgang aanpassen</h2>
          <p class="dim">Wijzig alleen de gegevens die je met de student hebt besproken.</p>
        </div>
        <span class="pill">{{ count($student['outcomes']) }} leeruitkomsten</span>
      </div>
      @foreach ($student['outcomes'] as $outcome)
        <form class="coach-outcome-form" method="POST" action="{{ route('progress.update', [$student['id'], $outcome['id']]) }}">
          @csrf
          @method('PATCH')
          <div class="coach-outcome-heading">
            <div>
              <b>{{ $outcome['title'] }}</b>
              <span class="code">{{ $outcome['code'] }}</span>
            </div>
            <span class="coach-outcome-status">{{ str_replace('_', ' ', ucfirst($outcome['status'])) }}</span>
          </div>
          <div class="coach-outcome-controls">
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
            <button class="button dark coach-save-button" type="submit">Opslaan <span aria-hidden="true">→</span></button>
          </div>
        </form>
      @endforeach
    </div>
  </section>
@endforeach

@endsection
