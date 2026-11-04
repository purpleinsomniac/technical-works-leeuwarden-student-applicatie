@extends('layouts.app')

@section('title', 'Ontwikkeling | Jouw leeromgeving')

@if ($roleAssets['style'])
  @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/'.$roleAssets['style']) }}">
  @endpush
@endif

@section('content')
  <div class="app" id="app">
    @include('components.sidebar')

    <main class="main">
      @include('components.topbar')

      @yield('role-content')

      <div id="hint" class="warning hidden" role="status"></div>
      @include('components.footer')
    </main>
  </div>
@endsection

@push('scripts')
  <script>
    window.LARAVEL_USER = {{ Illuminate\Support\Js::from($frontendUser) }};
    window.LARAVEL_STATE = {{ Illuminate\Support\Js::from($frontendState) }};
    window.LARAVEL_DATA = {{ Illuminate\Support\Js::from($frontendData) }};
    window.LARAVEL_SAVE_URL = {{ Illuminate\Support\Js::from(route('state.save')) }};
    window.LARAVEL_API = {{ Illuminate\Support\Js::from([
        'challengeSync' => route('challenges.sync'),
        'reflectionStore' => route('reflections.store'),
        'reflectionRestore' => route('reflections.restore', ['id'=>'__ID__']),
        'reflectionDelete' => route('reflections.destroy', ['reflection' => '__ID__']),
        'feedbackStore' => route('feedback.store'),
        'feedbackDelete' => route('feedback.destroy', ['feedback' => '__ID__']),
        'goalStore' => route('goals.store'),
        'goalComplete' => route('goals.complete', ['goal'=>'__ID__']),
        'assignmentSubmit' => route('assignments.submit', ['assignment' => '__ID__']),
        'assignmentReopen' => route('assignments.reopen', ['assignment' => '__ID__']),
        'assignmentFileStore' => route('assignments.files.store', ['assignment' => '__ID__']),
        'assignmentFileDelete' => route('assignments.files.destroy', ['assignmentFile' => '__ID__']),
    ]) }};
    window.LARAVEL_CSRF = {{ Illuminate\Support\Js::from(csrf_token()) }};
  </script>
  @foreach ($roleAssets['scripts'] as $script)
    <script src="{{ asset('assets/'.$script) }}"></script>
  @endforeach
@endpush
