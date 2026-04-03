<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Kid;
use App\Models\KidTask;
use App\Models\PointsStoreItem;
use App\Models\StarReward;
use App\Models\StreakBonus;
use App\Models\TaskCompletion;
use App\Services\GamificationService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    public ?string $currentStreakImage = null;

    public int $completedCount = 0;

    public int $taskCount = 0;

    public bool $showCelebration = false;

    public string $currentDate = '';

    public bool $hasLoadedOnce = false;

    public bool $wasAllTasksDone = false;

    public array $nextPointsItem = [];

    public array $redeemablePointsItems = [];

    public array $nextStarReward = [];

    public array $nextStreakBonus = [];

    public array $starBadges = [];

    public array $announcedBadgeIds = [];

    public int $announcedStreakDays = 0;

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
        $kid = $this->resolveKid()->load(['tasks', 'streak']);
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

        if (! $this->hasLoadedOnce) {
            $this->announcedStreakDays = $this->currentStreak;
        } else {
            if ($this->currentStreak < $this->announcedStreakDays) {
                $this->announcedStreakDays = $this->currentStreak;
            }

            if ($this->currentStreak > $this->announcedStreakDays) {
                $this->dispatch('streak-reached', days: $this->currentStreak);
                $this->announcedStreakDays = $this->currentStreak;
            }
        }

        $parentId = $kid->parent_id;

        $currentStreakImagePath = StreakBonus::query()
            ->where('parent_id', $parentId)
            ->where('day_target', '<=', $this->currentStreak)
            ->whereNotNull('image_path')
            ->orderByDesc('day_target')
            ->value('image_path');

        $this->currentStreakImage = $currentStreakImagePath
            ? Storage::url($currentStreakImagePath)
            : null;

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

        $this->redeemablePointsItems = PointsStoreItem::query()
            ->where('parent_id', $parentId)
            ->where('active', true)
            ->where('points', '<=', $this->points)
            ->orderByDesc('points')
            ->orderBy('title')
            ->get()
            ->map(fn (PointsStoreItem $item) => [
                'id' => (int) $item->id,
                'title' => (string) $item->title,
                'description' => (string) ($item->description ?? ''),
                'points' => (int) $item->points,
                'image_path' => $item->image_path,
            ])
            ->values()
            ->all();

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

        $starBadgeModels = StarReward::query()
            ->where('parent_id', $parentId)
            ->where('active', true)
            ->orderBy('order_number')
            ->orderBy('stars_needed')
            ->get();

        $this->starBadges = $starBadgeModels
            ->map(fn (StarReward $reward) => [
                'id' => (int) $reward->id,
                'title' => $reward->title,
                'stars_needed' => (int) $reward->stars_needed,
                'image_path' => $reward->image_path,
                'earned' => $this->stars >= (int) $reward->stars_needed,
            ])
            ->values()
            ->all();

        $earnedBadges = $starBadgeModels
            ->filter(fn (StarReward $reward) => $this->stars >= (int) $reward->stars_needed)
            ->values();

        $earnedBadgeIds = $earnedBadges
            ->map(fn (StarReward $reward) => (int) $reward->id)
            ->all();

        if (! $this->hasLoadedOnce) {
            $this->announcedBadgeIds = $earnedBadgeIds;
        } else {
            $newlyUnlockedBadge = $earnedBadges
                ->first(fn (StarReward $reward) => ! in_array((int) $reward->id, $this->announcedBadgeIds, true));

            if ($newlyUnlockedBadge) {
                $this->dispatch(
                    'badge-unlocked',
                    title: (string) $newlyUnlockedBadge->title,
                    image_url: $newlyUnlockedBadge->image_path ? Storage::url($newlyUnlockedBadge->image_path) : null
                );
            }

            $this->announcedBadgeIds = $earnedBadgeIds;
        }

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

    public function redeemPointsReward(int $itemId): void
    {
        $kid = $this->resolveKid();

        $reward = PointsStoreItem::query()
            ->where('id', $itemId)
            ->where('parent_id', $kid->parent_id)
            ->where('active', true)
            ->first();

        if (! $reward) {
            $this->dispatch('toast', message: 'That reward is no longer available.', type: 'warning');

            return;
        }

        $didRedeem = false;

        DB::transaction(function () use ($kid, $reward, &$didRedeem): void {
            $lockedKid = Kid::query()->whereKey($kid->id)->lockForUpdate()->first();

            if (! $lockedKid || (int) $lockedKid->points < (int) $reward->points) {
                return;
            }

            $lockedKid->decrement('points', (int) $reward->points);

            ActivityLog::query()->create([
                'kid_id' => $lockedKid->id,
                'task_id' => null,
                'action' => 'Redeemed Reward: '.$reward->title,
                'completed_at' => now(),
            ]);

            $didRedeem = true;
        });

        if (! $didRedeem) {
            $this->dispatch('toast', message: 'Not enough points for that reward.', type: 'error');
            $this->loadDashboard();

            return;
        }

        $this->dispatch('reward-redeemed', title: $reward->title, points: (int) $reward->points);
        $this->loadDashboard();
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

    private function resolveKid(): Kid
    {
        $kidQuery = Kid::query();

        if ($this->parentId > 0) {
            $kidQuery->where('parent_id', $this->parentId);
        } else {
            $kidQuery->whereKey($this->sharedKidId);
        }

        return $kidQuery->findOrFail($this->kidId);
    }
}
