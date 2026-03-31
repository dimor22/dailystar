<?php

namespace App\Livewire;

use App\Models\Kid;
use App\Models\KidTask;
use App\Models\PointsStoreItem;
use App\Models\StarReward;
use App\Models\StreakBonus;
use App\Models\TaskCompletion;
use App\Services\GamificationService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Livewire\Attributes\On;
use Livewire\Component;

class KidDashboard extends Component
{
    public int $kidId;

    public int $parentId = 0;

    public int $sharedKidId = 0;

    public array $tasks = [];

    public string $kidName = '';

    public string $kidAvatar = '';

    public string $kidAvatarDisplayMode = 'emoji';

    public ?string $kidAvatarImagePath = null;

    public string $kidColor = 'bg-blue-500';

    public int $points = 0;

    public int $stars = 0;

    public int $currentStreak = 0;

    public int $completedCount = 0;

    public int $taskCount = 0;

    public bool $showCelebration = false;

    public string $currentDate = '';

    public bool $hasLoadedOnce = false;

    public bool $wasAllTasksDone = false;

    public array $nextPointsItem = [];

    public array $nextStarReward = [];

    public array $nextStreakBonus = [];

    public array $starBadges = [];

    public function mount(?int $kidId = null): void
    {
        $this->parentId = (int) session('parent_user_id');
        $this->sharedKidId = (int) session('shared_kid_id');

        if ($this->parentId > 0 && $this->sharedKidId > 0) {
            session()->forget(['shared_kid_id', 'preselected_kid_id']);
            $this->sharedKidId = 0;
        }

        if ($this->parentId <= 0 && $this->sharedKidId <= 0) {
            session()->forget('kid_id');
            $this->redirectToKidLogin();
        }

        $resolvedKidId = $kidId ?? session('kid_id');

        if (! $resolvedKidId) {
            $this->redirectToKidLogin();
        }

        if ($this->parentId <= 0 && $this->sharedKidId > 0) {
            if ((int) $resolvedKidId !== $this->sharedKidId) {
                session()->forget('kid_id');
                session()->put('preselected_kid_id', $this->sharedKidId);
                $this->redirectToKidLogin();
            }
        }

        $this->kidId = (int) $resolvedKidId;
        $this->loadDashboard();
    }

    private function redirectToKidLogin(): never
    {
        throw new HttpResponseException(response()->redirectToRoute('kid.login'));
    }

    #[On('task-completed')]
    public function loadDashboard(): void
    {
        $kidQuery = Kid::query()->with(['tasks', 'streak']);

        if ($this->parentId > 0) {
            $kidQuery->where('parent_id', $this->parentId);
        } else {
            $kidQuery->whereKey($this->sharedKidId);
        }

        $kid = $kidQuery->findOrFail($this->kidId);
        $today = now()->toDateString();
        $todayWeekday = strtolower(now()->format('l'));
        $this->currentDate = $today;

        $visibleTaskIds = KidTask::query()
            ->where('kid_id', $kid->id)
            ->where('active', true)
            ->where(function ($query) use ($todayWeekday) {
                $query
                    ->whereNull('days_of_week')
                    ->orWhereJsonLength('days_of_week', 0)
                    ->orWhereJsonContains('days_of_week', $todayWeekday);
            })
            ->pluck('task_id')
            ->map(fn ($taskId) => (int) $taskId)
            ->all();

        $completedTaskIds = TaskCompletion::query()
            ->where('kid_id', $kid->id)
            ->whereDate('completed_date', $today)
            ->whereIn('task_id', $visibleTaskIds)
            ->pluck('task_id')
            ->all();

        $this->tasks = $kid->tasks
            ->filter(fn ($task) => in_array((int) $task->id, $visibleTaskIds, true))
            ->map(function ($task) use ($completedTaskIds) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'image_path' => $task->image_path,
                    'points' => $task->points,
                    'completed' => in_array($task->id, $completedTaskIds, true),
                ];
            })
            ->all();

        $this->kidName = $kid->name;
        $this->kidAvatar = (string) $kid->avatar;
        $this->kidAvatarDisplayMode = (string) ($kid->avatar_display_mode ?: 'emoji');
        $this->kidAvatarImagePath = $kid->avatar_image_path;
        $this->kidColor = (string) ($kid->color ?: 'bg-blue-500');
        $this->points = (int) $kid->points;
        $this->stars = app(GamificationService::class)->starsFromPoints($this->points);
        $this->currentStreak = (int) ($kid->streak->current_streak ?? 0);
        $this->taskCount = count($this->tasks);
        $this->completedCount = count(array_filter($this->tasks, fn (array $task) => $task['completed']));

        $parentId = $kid->parent_id;

        $nextPointsItemModel = PointsStoreItem::query()
            ->where('parent_id', $parentId)
            ->where('active', true)
            ->where('points', '>', $this->points)
            ->orderBy('points')
            ->first()
            ?? PointsStoreItem::query()
                ->where('parent_id', $parentId)
                ->where('active', true)
                ->orderBy('points')
                ->first();

        $this->nextPointsItem = $nextPointsItemModel ? [
            'title' => $nextPointsItemModel->title,
            'description' => $nextPointsItemModel->description,
            'points' => $nextPointsItemModel->points,
            'image_path' => $nextPointsItemModel->image_path,
            'can_afford' => $this->points >= $nextPointsItemModel->points,
        ] : [];

        $nextStarRewardModel = StarReward::query()
            ->where('parent_id', $parentId)
            ->where('active', true)
            ->where('stars_needed', '>', $this->stars)
            ->orderBy('stars_needed')
            ->first();

        $this->nextStarReward = $nextStarRewardModel ? [
            'title' => $nextStarRewardModel->title,
            'description' => $nextStarRewardModel->description,
            'stars_needed' => $nextStarRewardModel->stars_needed,
            'image_path' => $nextStarRewardModel->image_path,
        ] : [];

        $nextStreakBonusModel = StreakBonus::query()
            ->where('parent_id', $parentId)
            ->where('day_target', '>', $this->currentStreak)
            ->orderBy('day_target')
            ->first();

        $this->nextStreakBonus = $nextStreakBonusModel ? [
            'title' => $nextStreakBonusModel->title,
            'description' => $nextStreakBonusModel->description,
            'day_target' => $nextStreakBonusModel->day_target,
            'image_path' => $nextStreakBonusModel->image_path,
        ] : [];

        $this->starBadges = StarReward::query()
            ->where('parent_id', $parentId)
            ->where('active', true)
            ->orderBy('order_number')
            ->orderBy('stars_needed')
            ->get()
            ->map(fn (StarReward $reward) => [
                'title' => $reward->title,
                'stars_needed' => (int) $reward->stars_needed,
                'image_path' => $reward->image_path,
                'earned' => $this->stars >= (int) $reward->stars_needed,
            ])
            ->values()
            ->all();

        $allTasksDone = $this->taskCount > 0 && $this->completedCount === $this->taskCount;
        $dismissedDate = (string) session("celebration_dismissed.{$kid->id}");
        $dismissedToday = $dismissedDate === $today;

        if (! $this->hasLoadedOnce) {
            $this->hasLoadedOnce = true;
            $this->wasAllTasksDone = $allTasksDone;
            $this->showCelebration = false;

            return;
        }

        if ($allTasksDone && ! $this->wasAllTasksDone && ! $dismissedToday) {
            $this->showCelebration = true;
        }

        if (! $allTasksDone) {
            $this->showCelebration = false;
        }

        $this->wasAllTasksDone = $allTasksDone;
    }

    #[On('celebration-dismissed')]
    public function dismissCelebration(): void
    {
        session()->put("celebration_dismissed.{$this->kidId}", $this->currentDate ?: now()->toDateString());
        $this->showCelebration = false;
    }

    public function render()
    {
        return view('livewire.kid-dashboard', [
            'kid' => [
                'stars' => $this->stars,
                'streak' => $this->currentStreak,
                'color' => $this->kidColor,
            ],
        ]);
    }
}
