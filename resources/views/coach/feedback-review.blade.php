<article class="coach-feedback-review">
  <div class="coach-feedback-review-head"><div><strong>{{ $feedbackItem['student'] }}</strong><h3>{{ $feedbackItem['title'] }}</h3><p>{{ $feedbackItem['outcome'] }} <span class="coach-outcome-code">{{ $feedbackItem['code'] }}</span></p></div><span class="coach-status-badge">Te beoordelen</span></div>
  <details class="coach-feedback-evidence"><summary>Feedback en vervolgafspraak bekijken</summary><dl><dt>Wat gaat goed?</dt><dd>{{ $feedbackItem['positive'] }}</dd><dt>Wat kan beter?</dt><dd>{{ $feedbackItem['improve'] }}</dd><dt>Vervolgafspraak</dt><dd>{{ $feedbackItem['action'] }}</dd></dl></details>
  <form method="POST" action="{{ route('feedback.confirmProcessed', $feedbackItem['id']) }}" class="coach-feedback-award-form">
    @csrf
    <input type="hidden" name="feedback_review_id" value="{{ $feedbackItem['id'] }}">
    <label for="feedbackHours-{{ $reviewContext }}-{{ $feedbackItem['id'] }}">Extra uren toekennen<input id="feedbackHours-{{ $reviewContext }}-{{ $feedbackItem['id'] }}" name="hours" type="number" inputmode="numeric" min="0" max="10000" step="1" placeholder="Bijv. 2" required value="{{ (string) old('feedback_review_id') === (string) $feedbackItem['id'] ? old('hours') : '' }}"><small>Hele uren, bovenop het huidige totaal. 0 uur is ook mogelijk.</small></label>
    <button class="button dark" type="submit">Bevestigen en uren toekennen <span aria-hidden="true">&rarr;</span></button>
    @if ((string) old('feedback_review_id') === (string) $feedbackItem['id'])
      @error('hours')<p class="coach-feedback-award-error" role="alert">{{ $message }}</p>@enderror
    @endif
  </form>
</article>
