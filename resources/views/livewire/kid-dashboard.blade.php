<div
    class="space-y-6"
    wire:poll.20s="loadDashboard"
    x-data="{
        modalQueue: [],
        activeModal: null,
        canDismissActiveModal: false,
        dismissTimerId: null,
        pointsShopModalOpen: false,
        dailyCelebrationGifUrl: '',
        dailyCelebrationGifs: [
            'https://media.giphy.com/media/5GoVLqeAOo6PK/giphy.gif',
            'https://media.giphy.com/media/XD9o33QG9BoMis7iM4/giphy.gif',
            'https://media.giphy.com/media/l4FGGafcOHmrlQxG0/giphy.gif',
            'https://media.giphy.com/media/3oriO0OEd9QIDdllqo/giphy.gif',
            'https://media.giphy.com/media/26ufdipQqU2lhNA4g/giphy.gif',
        ],
        confettiPieces: [],
        confettiWave: 0,
        normalizeDetail(detail) {
            return Array.isArray(detail) ? detail[0] : detail;
        },
        enqueueModal(type, payload = {}) {
            this.modalQueue.push({
                id: `${Date.now()}-${Math.random()}`,
                type,
                payload,
            });

            this.showNextModal();
        },
        showNextModal() {
            if (this.activeModal || this.modalQueue.length === 0) {
                return;
            }

            this.activeModal = this.modalQueue.shift();
            this.canDismissActiveModal = false;
            this.launchConfetti();

            if (this.dismissTimerId) {
                clearTimeout(this.dismissTimerId);
            }

            // Keep each modal visible briefly before allowing dismissal.
            this.dismissTimerId = setTimeout(() => {
                this.canDismissActiveModal = true;
                this.dismissTimerId = null;
            }, 1200);
        },
        closeActiveModal() {
            if (! this.activeModal || ! this.canDismissActiveModal) {
                return;
            }

            this.activeModal = null;
            this.canDismissActiveModal = false;
            this.confettiPieces = [];

            setTimeout(() => {
                this.showNextModal();
            }, 120);
        },
        launchConfetti() {
            this.confettiWave += 1;

            const colors = ['#22c55e', '#f97316', '#3b82f6', '#eab308', '#ef4444', '#14b8a6', '#a855f7'];
            const wave = this.confettiWave;

            this.confettiPieces = Array.from({ length: 80 }, (_, index) => {
                const size = 8 + Math.random() * 10;

                return {
                    id: `${wave}-${index}`,
                    left: Math.random() * 100,
                    size,
                    drift: (Math.random() * 140) - 70,
                    duration: 2800 + Math.random() * 2200,
                    delay: Math.random() * 350,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    radius: Math.floor(Math.random() * 40),
                };
            });

            setTimeout(() => {
                if (this.confettiWave === wave) {
                    this.confettiPieces = [];
                }
            }, 5600);
        },
        launchRedeemCelebration(detail) {
            const payload = this.normalizeDetail(detail);
            this.enqueueModal('reward-redeemed', {
                title: payload?.title ?? 'Reward',
                points: Number(payload?.points ?? 0),
            });
        },
        launchRewardUnlockedCelebration(detail) {
            const payload = this.normalizeDetail(detail);
            this.enqueueModal('points-reward-unlocked', {
                title: payload?.title ?? 'Reward',
                points: Number(payload?.points ?? 0),
            });
        },
        launchBadgeCelebration(detail) {
            const payload = this.normalizeDetail(detail);
            this.enqueueModal('badge-unlocked', {
                title: payload?.title ?? 'New Badge',
                image_url: payload?.image_url ?? null,
            });
        },
        launchStreakCelebration(detail) {
            const payload = this.normalizeDetail(detail);
            this.enqueueModal('streak-reached', {
                days: Number(payload?.days ?? 0),
            });
        },
        launchTaskCompletedCelebration(detail) {
            const payload = this.normalizeDetail(detail);
            this.enqueueModal('task-completed', {
                task_points: Number(payload?.task_points ?? 0),
                bonus_percent: Number(payload?.bonus_percent ?? 0),
                bonus_points: Number(payload?.bonus_points ?? 0),
                total_task_points: Number(payload?.total_task_points ?? 0),
                bonus_type_key: String(payload?.bonus_type_key ?? 'no_bonus'),
            });
        },
        launchDailyGoalsCelebration(detail) {
            const payload = this.normalizeDetail(detail);
            const randomGif = this.dailyCelebrationGifs[Math.floor(Math.random() * this.dailyCelebrationGifs.length)];
            this.dailyCelebrationGifUrl = randomGif;

            this.enqueueModal('daily-goals-complete', {
                date: payload?.date ?? null,
                points_earned_today: Number(payload?.points_earned_today ?? 0),
            });
        },
        dismissDailyGoalsCelebration() {
            if (! this.canDismissActiveModal || this.activeModal?.type !== 'daily-goals-complete') {
                return;
            }

            this.$wire.dismissCelebration();
            this.closeActiveModal();
        },
    }"
    x-on:points-reward-unlocked.window="launchRewardUnlockedCelebration($event.detail)"
    x-on:reward-redeemed.window="launchRedeemCelebration($event.detail)"
    x-on:badge-unlocked.window="launchBadgeCelebration($event.detail)"
    x-on:streak-reached.window="launchStreakCelebration($event.detail)"
    x-on:task-completed-details.window="launchTaskCompletedCelebration($event.detail)"
    x-on:daily-goals-complete.window="launchDailyGoalsCelebration($event.detail)"
    @keydown.escape.window="if (pointsShopModalOpen) pointsShopModalOpen = false; else closeActiveModal()"
>
    <div class="grid gap-4 lg:grid-cols-3">
        @php
            $cardGradientColor = match ($kidColor ?? 'bg-blue-500') {
                'bg-blue-500' => 'rgba(59, 130, 246, 0.24)',
                'bg-pink-500' => 'rgba(236, 72, 153, 0.24)',
                'bg-green-500' => 'rgba(34, 197, 94, 0.24)',
                'bg-yellow-500' => 'rgba(234, 179, 8, 0.24)',
                'bg-purple-500' => 'rgba(168, 85, 247, 0.24)',
                'bg-orange-500' => 'rgba(249, 115, 22, 0.24)',
                default => 'rgba(148, 163, 184, 0.2)',
            };
        @endphp

        <div class="kid-card relative overflow-hidden grid grid-cols-2 gap-4 lg:col-span-2">
            <div
                class="pointer-events-none absolute inset-0"
                style="background: linear-gradient(to bottom left, {{ $cardGradientColor }} 0%, rgba(255, 255, 255, 0) 62%);"
            ></div>

            <div class="relative z-10">
                <div class="relative">
                    @if($kidAvatarDisplayMode === 'image' && $kidAvatarImagePath)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($kidAvatarImagePath) }}" alt="{{ $kidName }} avatar" class="mt-3 h-[8rem] w-[8rem] rounded-full object-cover bg-white p-1" />
                    @else
                        <div class="mt-3 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-5xl">{{ $kidAvatar }}</div>
                    @endif

                    @if(!empty($currentStreakImage))
                        <img src="{{ $currentStreakImage }}" alt="Current streak image" class="h-20 w-20 absolute left-[4.5rem] top-[43px]" />
                    @endif
                </div>

                <p class="kid-title">{{ $kidName }}</p>
                <p class="text-sm text-slate-500">Timezone: {{ config('app.timezone') }}</p>


            </div>

            <div class="relative z-10">
                <div class="">
                    <p class="text-lg font-bold text-slate-700">Points: {{ $points }}</p>
                    <p class="text-lg font-bold text-slate-700">Stars: {{ $stars }} ⭐</p>
                </div>



                <div class="mt-4">
                    <x-progress-bar :current="$completedCount" :total="$taskCount" />
                </div>
            </div>
            <div class="col-span-2 mt-4">
                @if(!empty($starBadges))
                    <div class="flex gap-2 overflow-x-auto pb-1">
                        @foreach($starBadges as $badge)
                            <div class="relative shrink-0" title="{{ $badge['title'] }}">
                                <div class="flex h-14 w-14 items-center justify-center overflow-hidden {{ $badge['earned'] ? '' : 'grayscale' }}">
                                    @if(!empty($badge['image_path']))
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($badge['image_path']) }}" alt="{{ $badge['title'] }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-2xl">⭐</span>
                                    @endif
                                </div>

                                @if(! $badge['earned'])
                                    <div class="pointer-events-none absolute inset-0 rounded-xl bg-slate-400/45"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">No badges yet.</p>
                @endif
            </div>
        </div>

        {{-- Milestones slider --}}
        <div
            class="kid-card relative overflow-hidden flex flex-col lg:col-span-1"
            x-data="{ slide: 0 }"
            @touchstart.passive="$el._tx = $event.touches[0].clientX"
            @touchend.passive="let dx = $event.changedTouches[0].clientX - ($el._tx ?? 0); if (dx < -40) slide = (slide + 1) % 3; else if (dx > 40) slide = (slide - 1 + 3) % 3"
        >
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-kid-xl font-bold text-slate-800">Next Up</h2>
                <div class="flex items-center gap-1.5">
                    <button @click="slide = 0" :class="slide === 0 ? 'w-5 bg-amber-400' : 'w-2.5 bg-slate-200'" class="h-2.5 rounded-full transition-all duration-200" aria-label="Points Shop"></button>
                    <button @click="slide = 1" :class="slide === 1 ? 'w-5 bg-yellow-400' : 'w-2.5 bg-slate-200'" class="h-2.5 rounded-full transition-all duration-200" aria-label="Next Badge"></button>
                    <button @click="slide = 2" :class="slide === 2 ? 'w-5 bg-orange-400' : 'w-2.5 bg-slate-200'" class="h-2.5 rounded-full transition-all duration-200" aria-label="Streak Goal"></button>
                </div>
            </div>

            <div class="relative min-h-[96px]">
            {{-- Slide 0: Points Shop --}}
            <div x-show="slide === 0" x-transition.opacity class="absolute inset-0">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 overflow-hidden text-4xl">
                        @if(!empty($nextPointsItem['image_path']))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($nextPointsItem['image_path']) }}" alt="{{ $nextPointsItem['title'] }}" class="h-full w-full object-cover">
                        @else
                            🛍️
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-wide text-amber-500">Points Shop</p>
                        @if(!empty($nextPointsItem))
                            <p class="text-lg font-bold text-slate-800 truncate">{{ $nextPointsItem['title'] }}</p>
                            @if($nextPointsItem['can_afford'])
                                <p class="mt-1 text-sm font-bold text-green-600">🎉 You can claim this!</p>
                            @else
                                <p class="mt-1 text-sm text-slate-500">{{ $points }} / {{ $nextPointsItem['points'] }} pts</p>
                            @endif
                            <div class="mt-2 h-2.5 w-full rounded-full bg-slate-100">
                                <div class="h-2.5 rounded-full bg-amber-400 transition-all duration-500"
                                    style="width: {{ min(100, (int) round($points / max(1, $nextPointsItem['points']) * 100)) }}%"></div>
                            </div>
                            <button
                                type="button"
                                class="mt-3 rounded-lg bg-amber-500 px-3 py-2 text-xs font-bold text-white transition hover:bg-amber-600"
                                @click="pointsShopModalOpen = true"
                            >
                                View Shop
                            </button>
                        @else
                            <p class="text-lg font-bold text-slate-400">No items yet</p>
                            <p class="mt-1 text-sm text-slate-400">Ask your parent to add store items!</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Slide 1: Star Reward --}}
            <div x-show="slide === 1" x-transition.opacity class="absolute inset-0">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 flex h-16 w-16 items-center justify-center rounded-2xl bg-yellow-50 overflow-hidden text-4xl">
                        @if(!empty($nextStarReward['image_path']))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($nextStarReward['image_path']) }}" alt="{{ $nextStarReward['title'] }}" class="h-full w-full object-cover">
                        @else
                            ⭐
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-wide text-yellow-500">Next Badge</p>
                        @if(!empty($nextStarReward))
                            <p class="text-lg font-bold text-slate-800 truncate">{{ $nextStarReward['title'] }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $stars }} / {{ $nextStarReward['stars_needed'] }} ⭐</p>
                            <div class="mt-2 h-2.5 w-full rounded-full bg-slate-100">
                                <div class="h-2.5 rounded-full bg-yellow-400 transition-all duration-500"
                                    style="width: {{ min(100, (int) round($stars / max(1, $nextStarReward['stars_needed']) * 100)) }}%"></div>
                            </div>
                        @else
                            <p class="text-lg font-bold text-green-600">All badges earned! 🏆</p>
                            <p class="mt-1 text-sm text-slate-500">You've collected every star badge!</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Slide 2: Streak Goal --}}
            <div x-show="slide === 2" x-transition.opacity class="absolute inset-0">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 flex h-16 w-16 items-center justify-center overflow-hidden text-4xl">
                        @if(!empty($nextStreakBonus['image_path']))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($nextStreakBonus['image_path']) }}" alt="{{ $nextStreakBonus['title'] }}" class="h-full w-full object-cover">
                        @else
                            🔥
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-wide text-orange-500">Streak Goal</p>
                        @if(!empty($nextStreakBonus))
                            <p class="text-lg font-bold text-slate-800 truncate">{{ $nextStreakBonus['title'] }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $currentStreak }} / {{ $nextStreakBonus['day_target'] }} 🔥 days</p>
                            <div class="mt-2 h-2.5 w-full rounded-full bg-slate-100">
                                <div class="h-2.5 rounded-full bg-orange-400 transition-all duration-500"
                                    style="width: {{ min(100, (int) round($currentStreak / max(1, $nextStreakBonus['day_target']) * 100)) }}%"></div>
                            </div>
                        @else
                            <p class="text-lg font-bold text-green-600">All goals smashed! 🏆</p>
                            <p class="mt-1 text-sm text-slate-500">You've hit every streak goal!</p>
                        @endif
                    </div>
                </div>
            </div>
            </div>

            {{-- Nav arrows --}}
            <div class="mt-4 flex justify-between">
                <button @click="slide = (slide - 1 + 3) % 3"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xl font-bold text-slate-500 hover:bg-slate-200 transition">‹</button>
                <button @click="slide = (slide + 1) % 3"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xl font-bold text-slate-500 hover:bg-slate-200 transition">›</button>
            </div>


        </div>
    </div>

    <div class="kid-card">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-kid-xl font-bold text-slate-800">Reward Shop</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-700">
                {{ count($redeemablePointsItems) }} ready to redeem
            </span>
        </div>

        @if(!empty($redeemablePointsItems))
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($redeemablePointsItems as $reward)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4">
                        <div class="flex items-stretch gap-4">
                            <div class="basis-[30%] shrink-0">
                                <div class="flex h-full min-h-[120px] w-full items-center justify-center overflow-hidden rounded-xl bg-white text-5xl">
                                @if(!empty($reward['image_path']))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($reward['image_path']) }}" alt="{{ $reward['title'] }}" class="h-full w-full object-cover" />
                                @else
                                    🎁
                                @endif
                                </div>
                            </div>

                            <div class="basis-[70%] min-w-0 flex-1 flex flex-col justify-between">
                                <div>
                                <p class="truncate text-lg font-bold text-slate-800">{{ $reward['title'] }}</p>
                                <p class="text-sm font-semibold text-emerald-700">{{ $reward['points'] }} points</p>
                                @if(!empty($reward['description']))
                                    <p class="mt-1 text-sm text-slate-600 line-clamp-2">{{ $reward['description'] }}</p>
                                @endif
                                </div>

                                <button
                                    type="button"
                                    class="kid-btn kid-btn-success mt-4 w-full"
                                    wire:click="redeemPointsReward({{ $reward['id'] }})"
                                    wire:loading.attr="disabled"
                                    wire:target="redeemPointsReward"
                                >
                                    Redeem Reward
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-3 text-slate-500">No rewards are redeemable yet. Keep completing tasks to unlock rewards.</p>
        @endif
    </div>

    <div
        x-show="pointsShopModalOpen"
        x-cloak
        class="fixed inset-0 z-[98] grid place-items-center bg-slate-900/45 p-4"
        @click.self="pointsShopModalOpen = false"
    >
        <div class="kid-card w-full max-w-3xl">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-kid-xl font-bold text-slate-800">Points Shop</h2>
                <button type="button" class="rounded-lg bg-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300" @click="pointsShopModalOpen = false">Close</button>
            </div>

            <div class="mt-4 max-h-[68vh] overflow-y-auto pr-1">
                @if(!empty($pointsStoreItems))
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($pointsStoreItems as $shopReward)
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-50 text-3xl">
                                        @if(!empty($shopReward['image_path']))
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($shopReward['image_path']) }}" alt="{{ $shopReward['title'] }}" class="h-full w-full object-cover" />
                                        @else
                                            🎁
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-base font-bold text-slate-800">{{ $shopReward['title'] }}</p>
                                        <p class="text-sm font-semibold text-emerald-700">{{ $shopReward['points'] }} points</p>
                                        @if(!empty($shopReward['description']))
                                            <p class="mt-1 text-sm text-slate-600 line-clamp-2">{{ $shopReward['description'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                <p class="mt-2 text-xs font-semibold {{ $shopReward['can_afford'] ? 'text-green-600' : 'text-slate-500' }}">
                                    {{ $shopReward['can_afford'] ? 'Ready to redeem' : 'Keep earning points' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">No active point rewards yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($tasks as $task)
            <livewire:task-card
                :kidId="$kidId"
                :taskId="$task['id']"
                :title="$task['title']"
                :description="$task['description']"
                :taskImagePath="$task['image_path']"
                :points="$task['points']"
                :completed="$task['completed']"
                :key="'task-'.$kidId.'-'.$task['id'].'-'.$task['completed']"
            />
        @endforeach
    </div>

    <div class="pointer-events-none fixed inset-0 z-[95] overflow-hidden" aria-hidden="true">
        <template x-for="piece in confettiPieces" :key="piece.id">
            <span
                class="reward-confetti-piece"
                :style="`
                    left: ${piece.left}%;
                    width: ${piece.size}px;
                    height: ${Math.max(6, piece.size * 0.55)}px;
                    background: ${piece.color};
                    border-radius: ${piece.radius}%;
                    --reward-confetti-drift: ${piece.drift}px;
                    animation-duration: ${piece.duration}ms;
                    animation-delay: ${piece.delay}ms;
                `"
            ></span>
        </template>
    </div>

    <div
        x-show="activeModal?.type === 'points-reward-unlocked'"
        x-cloak
        class="fixed inset-0 z-[100] grid place-items-center bg-slate-900/45 p-4"
        @click.self="closeActiveModal()"
    >
        <div class="kid-card w-full max-w-md text-center">
            <p class="text-5xl">🎁</p>
            <h2 class="mt-2 text-kid-2xl font-extrabold text-slate-800">New Reward Unlocked!</h2>
            <p class="mt-2 text-lg font-semibold text-slate-700">
                <span class="text-emerald-600" x-text="activeModal?.payload?.title ?? 'Reward'"></span>
                is now available in your reward shop.
            </p>
            <p class="mt-1 text-sm text-slate-500">
                You reached <span class="font-bold" x-text="activeModal?.payload?.points ?? 0"></span> points. Go redeem it!
            </p>

            <button
                type="button"
                class="kid-btn kid-btn-success mt-5 w-full"
                :disabled="!canDismissActiveModal"
                @click="closeActiveModal()"
            >
                Let's Go!
            </button>
        </div>
    </div>

    <div
        x-show="activeModal?.type === 'reward-redeemed'"
        x-cloak
        class="fixed inset-0 z-[100] grid place-items-center bg-slate-900/45 p-4"
        @click.self="closeActiveModal()"
    >
        <div class="kid-card w-full max-w-md text-center">
            <p class="text-5xl">🎉</p>
            <h2 class="mt-2 text-kid-2xl font-extrabold text-slate-800">Reward Redeemed!</h2>
            <p class="mt-2 text-lg font-semibold text-slate-700">
                You unlocked <span class="text-emerald-600" x-text="activeModal?.payload?.title ?? 'Reward'"></span>
            </p>
            <p class="mt-1 text-sm text-slate-500">
                <span x-text="activeModal?.payload?.points ?? 0"></span> points spent. Nice work!
            </p>

            <button
                type="button"
                class="kid-btn kid-btn-success mt-5 w-full"
                :disabled="!canDismissActiveModal"
                @click="closeActiveModal()"
            >
                Awesome!
            </button>
        </div>
    </div>

    <div
        x-show="activeModal?.type === 'badge-unlocked'"
        x-cloak
        class="fixed inset-0 z-[100] grid place-items-center bg-slate-900/45 p-4"
        @click.self="closeActiveModal()"
    >
        <div class="kid-card w-full max-w-md text-center">
            <p class="text-5xl">⭐</p>
            <h2 class="mt-2 text-kid-2xl font-extrabold text-slate-800">New Badge Unlocked!</h2>

            <div class="mx-auto mt-4 flex h-44 w-44 items-center justify-center overflow-hidden rounded-3xl bg-yellow-50 shadow-inner">
                <template x-if="activeModal?.payload?.image_url">
                    <img :src="activeModal?.payload?.image_url" alt="Unlocked badge" class="h-full w-full object-cover" />
                </template>
                <template x-if="!activeModal?.payload?.image_url">
                    <span class="text-7xl">⭐</span>
                </template>
            </div>

            <p class="mt-4 text-lg font-semibold text-slate-700" x-text="activeModal?.payload?.title ?? 'New Badge'"></p>

            <button
                type="button"
                class="kid-btn kid-btn-success mt-5 w-full"
                :disabled="!canDismissActiveModal"
                @click="closeActiveModal()"
            >
                So Cool!
            </button>
        </div>
    </div>

    <div
        x-show="activeModal?.type === 'streak-reached'"
        x-cloak
        class="fixed inset-0 z-[100] grid place-items-center bg-slate-900/45 p-4"
        @click.self="closeActiveModal()"
    >
        <div class="kid-card w-full max-w-md text-center">
            <p class="text-5xl">🔥</p>
            <h2 class="mt-2 text-kid-2xl font-extrabold text-slate-800">Streak Level Up!</h2>

            <div class="mx-auto mt-4 flex h-44 w-44 items-center justify-center rounded-3xl bg-orange-50 shadow-inner">
                <div class="text-center">
                    <p class="text-7xl leading-none">🔥</p>
                    <p class="mt-2 text-2xl font-extrabold text-orange-700" x-text="`${activeModal?.payload?.days ?? 0} days`"></p>
                </div>
            </div>

            <p class="mt-4 text-lg font-semibold text-slate-700">Amazing consistency. Keep it going!</p>

            <button
                type="button"
                class="kid-btn kid-btn-success mt-5 w-full"
                :disabled="!canDismissActiveModal"
                @click="closeActiveModal()"
            >
                Keep Going!
            </button>
        </div>
    </div>

    <div
        x-show="activeModal?.type === 'task-completed'"
        x-cloak
        class="fixed inset-0 z-[100] grid place-items-center bg-slate-900/45 p-4"
        @click.self="closeActiveModal()"
    >
        <div class="kid-card w-full max-w-md text-center">
            <p class="text-kid-xl font-bold text-slate-800">Task Completed! 🎉</p>
            <p class="mt-2 text-lg text-slate-600" x-show="(activeModal?.payload?.bonus_type_key ?? 'no_bonus') === 'no_bonus'">
                Amazing consistency. Keep going!
            </p>
            <p class="mt-2 text-lg text-slate-600" x-show="(activeModal?.payload?.bonus_type_key ?? 'no_bonus') !== 'no_bonus'">
                Streak bonus active: <span class="font-extrabold text-emerald-600" x-text="`+${activeModal?.payload?.bonus_percent ?? 0}%`"></span>
            </p>

            <div class="mt-4 rounded-xl bg-slate-50 p-4 text-left text-sm text-slate-700">
                <p>Base points: <span class="font-bold" x-text="activeModal?.payload?.task_points ?? 0"></span></p>
                <p>Bonus points: <span class="font-bold" x-text="activeModal?.payload?.bonus_points ?? 0"></span></p>
                <p>Total earned: <span class="font-extrabold text-emerald-700" x-text="activeModal?.payload?.total_task_points ?? 0"></span></p>
            </div>

            <button class="kid-btn kid-btn-success mt-5 w-full" :disabled="!canDismissActiveModal" @click="closeActiveModal()">Awesome!</button>
        </div>
    </div>

    <div
        x-show="activeModal?.type === 'daily-goals-complete'"
        x-cloak
        class="fixed inset-0 z-[100] grid place-items-center bg-indigo-950/60 p-4"
        @click.self="dismissDailyGoalsCelebration()"
    >
        <div class="max-w-lg rounded-2xl bg-white p-8 text-center shadow-2xl">
            <h2 class="kid-title">Amazing Work! 🎉</h2>
            <p class="mt-3 text-kid-xl text-slate-700">
                You finished all your tasks today and earned
                <span class="font-extrabold text-amber-500" x-text="activeModal?.payload?.points_earned_today ?? 0"></span>
                <span x-text="(activeModal?.payload?.points_earned_today ?? 0) === 1 ? 'point' : 'points'"></span>
                today.
            </p>
            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                <img :src="dailyCelebrationGifUrl" alt="Funny celebration" class="mx-auto h-48 w-full object-cover" loading="lazy" />
            </div>
            <button class="kid-btn kid-btn-primary mt-6" type="button" :disabled="!canDismissActiveModal" @click="dismissDailyGoalsCelebration()">Yay!</button>
        </div>
    </div>
</div>
