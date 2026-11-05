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
        if ($type === 'feedback') {
            return \App\Models\Feedback::where('student_id', $user->id)->whereNotNull('processed_confirmed_at')->whereHas('learningOutcome.studyPhase', fn ($q) => $q->where('naam', $phase))->count();
        }
        if ($type === 'points') {
            $points = app(ProjectPoints::class)->forStudent($user);
            return (int) \App\Models\LearningOutcome::whereHas('studyPhase', fn ($q) => $q->where('naam', $phase))->get()->sum(fn ($outcome) => $points[$outcome->id] ?? 0);
        }
        if ($type === 'projects') {
            return Assignment::where('gebruiker_id', $user->id)->where('status', 'completed')->with('learningOutcomes.studyPhase')->get()
                ->filter(fn ($project) => $project->learningOutcomes->first()?->studyPhase?->naam === $phase)->count();
        }
        return Reflection::where('gebruiker_id', $user->id)->whereHas('learningOutcome.studyPhase', fn ($q) => $q->where('naam', $phase))->count();
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
            $pointService = app(ProjectPoints::class);
            $points = $pointService->forStudent($user);
            foreach (\App\Models\LearningOutcome::with('studyPhase')->get() as $outcome) {
                if (($points[$outcome->id] ?? 0) >= $pointService->norm($outcome)) {
                    $rewards[] = ['user_id'=>$user->id,'source'=>'outcome','source_id'=>$outcome->id,'amount'=>75,'created_at'=>now()];
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
            $active = DB::table('student_challenges')->where('user_id', $user->id)->where('status', 'active')->first();
            if ($active) {
                $definition = DB::table('challenges')->find($active->challenge_id);
                $progress = min($definition->target, max($active->progress, $this->metric($user, $definition->type, $active->phase) - $active->baseline));
                DB::table('student_challenges')->where('id', $active->id)->update(['progress'=>$progress]);
                if ($progress >= $definition->target) {
                    DB::table('student_challenges')->where('id', $active->id)->update(['status'=>'completed','completed_at'=>now()]);
                    DB::table('xp_transactions')->insertOrIgnore(['user_id'=>$user->id,'student_challenge_id'=>$active->id,'amount'=>$definition->reward,'created_at'=>now()]);
                    $completed[] = ['title'=>$definition->title,'reward'=>$definition->reward];
                    $active = null;
                }
            }
            if (!$active) {
                $next = DB::table('challenges')->whereNotIn('id', DB::table('student_challenges')->where('user_id', $user->id)->select('challenge_id'))->orderBy('id')->first();
                if ($next) {
                    $phase = $user->studyPhase?->naam ?? 'Oriëntatiefase';
                    DB::table('student_challenges')->insert(['user_id'=>$user->id,'challenge_id'=>$next->id,'phase'=>$phase,'status'=>'active','baseline'=>$this->metric($user,$next->type,$phase),'progress'=>0,'started_at'=>now()]);
                }
            }
            $items = DB::table('student_challenges as sc')->join('challenges as c','c.id','=','sc.challenge_id')->where('sc.user_id',$user->id)->select('sc.*','c.title','c.description','c.target','c.reward','c.type')->get();
            $xp = (int) DB::table('xp_transactions')->where('user_id',$user->id)->sum('amount') + (int) DB::table('activity_xp')->where('user_id',$user->id)->sum('amount');
            return ['active'=>$items->firstWhere('status','active'),'history'=>$items->where('status','completed')->values(),'xp'=>$xp,'completed'=>$completed];
        });
    }
}
