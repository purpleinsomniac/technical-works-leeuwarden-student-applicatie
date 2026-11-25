<?php

namespace App\Services;

use App\Models\User;
use App\Models\Assignment;
use App\Models\Reflection;
use Illuminate\Support\Facades\DB;

class ChallengeService
{
    private function metric(User $user, string $type, string $phase): int
    {
        if (in_array($type, ['goals_created','goals_completed'], true)) {
            return $user->learningGoals()->whereHas('learningOutcome.studyPhase', fn ($q) => $q->where('naam',$phase))
                ->when($type === 'goals_completed', fn ($q) => $q->where('status','afgerond'))->count();
        }
        if (in_array($type, ['skill_balance','phase_percentage','points'], true)) {
            $catalog = \App\Models\LearningOutcome::with('studyPhase')->get();
            $outcomes = $catalog->filter(fn ($outcome) => $outcome->studyPhase?->naam === $phase);
            $scores = app(LearningProgress::class)->snapshot($user, $catalog)['outcomes'];
            $points = array_map(fn ($score) => $score['earned'], $scores);
            if ($type === 'skill_balance') return $outcomes->filter(fn ($o) => ($points[$o->id] ?? 0) > 0)->pluck('type')->filter(fn ($kind) => in_array($kind,['Softskills','Hardskills'],true))->unique()->count();
            $earned = (int) $outcomes->sum(fn ($o) => $points[$o->id] ?? 0);
            if ($type === 'points') return $earned;
            $target = (int) $outcomes->sum(fn ($o) => $scores[$o->id]['target']);
            return (int) StudentPointTargets::percentage($earned, $target);
        }
        if (in_array($type, ['evidence','on_time_projects','project_reflections','team_reflections'], true)) {
            $projects = $user->assignments()->where('status','completed')->with(['learningOutcomes.studyPhase','files'])->orderBy('id')->get()
                ->filter(fn ($p) => $p->learningOutcomes->sortBy('id')->first()?->studyPhase?->naam === $phase);
            if ($type === 'evidence') return $projects->filter(fn ($p) => $p->files->isNotEmpty())->count();
            if ($type === 'on_time_projects') return $projects->filter(fn ($p) => $p->completed_at && $p->inleverdatum && $p->completed_at->lte($p->inleverdatum->copy()->endOfDay()))->count();
            if ($type === 'team_reflections') $projects = $projects->filter(fn ($p) => preg_match('/samen|team/iu',$p->type.' '.$p->onderwerp));
            return Reflection::where('gebruiker_id',$user->id)->whereIn('opdracht_id',$projects->pluck('id'))->distinct()->count('opdracht_id');
        }
        if ($type === 'feedback') {
            return \App\Models\Feedback::where('student_id', $user->id)->whereNotNull('processed_confirmed_at')->whereHas('learningOutcome.studyPhase', fn ($q) => $q->where('naam', $phase))->count();
        }
        if ($type === 'projects') {
            return Assignment::where('gebruiker_id', $user->id)->where('status', 'completed')->with('learningOutcomes.studyPhase')->get()
                ->filter(fn ($project) => $project->learningOutcomes->first()?->studyPhase?->naam === $phase)->count();
        }
        return $type === 'reflections' ? Reflection::where('gebruiker_id', $user->id)->whereHas('learningOutcome.studyPhase', fn ($q) => $q->where('naam', $phase))->count() : 0;
    }

    public function sync(User $user): array
    {
        return DB::transaction(function () use ($user) {
            // Serialiseer controles per student om dubbele challenges en beloningen te voorkomen.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            // Beloon ook eerdere resultaten; elke activiteit kan slechts eenmaal XP opleveren.
            $rewards = [];
            foreach ($user->assignments()->where('status', 'completed')->get() as $project) {
                $rewards[] = ['user_id'=>$user->id,'source'=>'project','source_id'=>$project->id,'amount'=>150,'created_at'=>now()];
            }
            // Neem ook beoordelingen mee die niet via een project zijn geregistreerd.
            foreach (app(LearningProgress::class)->snapshot($user)['outcomes'] as $outcomeId => $score) {
                if ($score['target'] > 0 && $score['earned'] >= $score['target']) {
                    $rewards[] = ['user_id'=>$user->id,'source'=>'outcome','source_id'=>$outcomeId,'amount'=>75,'created_at'=>now()];
                }
            }
            foreach ($user->reflections()->get() as $reflection) {
                $rewards[] = ['user_id'=>$user->id,'source'=>'reflection','source_id'=>$reflection->id,'amount'=>50,'created_at'=>now()];
            }
            foreach (\App\Models\Feedback::where('student_id',$user->id)->whereNotNull('processed_confirmed_at')->get() as $feedback) {
                $rewards[] = ['user_id'=>$user->id,'source'=>'feedback','source_id'=>$feedback->id,'amount'=>40,'created_at'=>now()];
            }
            if ($rewards) DB::table('activity_xp')->insertOrIgnore($rewards);
            $completed = [];
            $phase = $user->studyPhase?->naam ?? 'Oriëntatiefase';
            // Eén actieve challenge; bij een fasewissel blijft de oude voortgang bewaard.
            DB::table('student_challenges')->where('user_id',$user->id)->where('status','active')->where('phase','!=',$phase)->update(['status'=>'paused']);
            $limit = DB::table('challenges')->count();
            for ($step = 0; $step < $limit; $step++) {
                $active = DB::table('student_challenges')->where('user_id',$user->id)->where('phase',$phase)->whereIn('status',['active','paused'])->orderBy('id')->first();
                if (!$active) {
                    $next = DB::table('challenges')->whereNotIn('id', DB::table('student_challenges')->where('user_id',$user->id)->where('phase',$phase)->select('challenge_id'))->orderBy('id')->first();
                    if (!$next) break;
                    // De drie oorspronkelijke challenges blijven nieuwe activiteiten tellen.
                    $baseline = in_array($next->type,['points','projects','reflections'],true) ? $this->metric($user,$next->type,$phase) : 0;
                    $id = DB::table('student_challenges')->insertGetId(['user_id'=>$user->id,'challenge_id'=>$next->id,'phase'=>$phase,'status'=>'active','baseline'=>$baseline,'progress'=>0,'started_at'=>now()]);
                    $active = DB::table('student_challenges')->find($id);
                }
                $definition = DB::table('challenges')->find($active->challenge_id);
                $progress = min($definition->target, max($active->progress, $this->metric($user,$definition->type,$phase) - $active->baseline));
                DB::table('student_challenges')->where('id',$active->id)->update(['status'=>'active','progress'=>$progress]);
                if ($progress < $definition->target) break;
                DB::table('student_challenges')->where('id',$active->id)->update(['status'=>'completed','completed_at'=>now()]);
                DB::table('xp_transactions')->insertOrIgnore(['user_id'=>$user->id,'student_challenge_id'=>$active->id,'amount'=>$definition->reward,'created_at'=>now()]);
                $completed[] = ['title'=>$definition->title,'reward'=>$definition->reward];
            }
            $items = DB::table('student_challenges as sc')->join('challenges as c','c.id','=','sc.challenge_id')->where('sc.user_id',$user->id)->select('sc.*','c.title','c.description','c.target','c.reward','c.type')->get();
            $xp = (int) DB::table('xp_transactions')->where('user_id',$user->id)->sum('amount') + (int) DB::table('activity_xp')->where('user_id',$user->id)->sum('amount');
            $catalog = DB::table('challenges')->orderBy('id')->get()->map(function ($challenge) use ($user,$phase) {
                $challenge->progress = in_array($challenge->type,['points','projects','reflections'],true) ? 0 : min($challenge->target,$this->metric($user,$challenge->type,$phase));
                return $challenge;
            });
            return ['active'=>$items->firstWhere('status','active'),'history'=>$items->where('status','completed')->values(),'xp'=>$xp,'completed'=>$completed,'phase'=>$phase,'catalog'=>$catalog];
        });
    }
}
