Create a new Laravel project for a kids task manager app with the following specifications:

1. Set up Laravel 10 project.
2. Install and configure Livewire.
3. Install TailwindCSS with default configuration.
4. Include Alpine.js for interactivity.
5. Create the following database structure:
   - users: id, name, email, password, role, timezone, created_at, updated_at
   - kids: id, parent_id, name, avatar, color, pin, points, created_at, updated_at
   - tasks: id, title, description, points, category, active, created_at, updated_at
   - kid_tasks: id, kid_id, task_id, order, active, created_at
   - task_completions: id, kid_id, task_id, completed_date, completed_at, created_at
   - activity_logs: id, kid_id, task_id, action, created_at
   - streaks: id, kid_id, current_streak, longest_streak, last_completed_date, updated_at
6. Create Livewire components:
   - KidSelector: displays kids’ avatars for login
   - PinLogin: handles PIN input for kids
   - KidDashboard: shows the daily tasks, stars, points, streaks
   - TaskCard: displays individual task with big buttons and confirmation modal
   - CelebrationModal: shown when all tasks are done
   - ParentDashboard: shows all kids, daily progress, activity log, and streaks
7. Include reusable components:
   - ProgressBar: shows task completion visually
   - StarCounter: shows stars earned
8. Configure Tailwind to have large colorful buttons, rounded cards, big readable text.
9. Seed the database with:
   - 1 parent user
   - 4 kids with avatars and colors
   - 5 tasks (Math, Reading, Writing, Science, Scripture Study)
   - Assign all tasks to all kids
10. Use best DRY practices: all gamification logic (points, stars, streaks) goes in a GamificationService, email notifications in EmailService, and Livewire components just display data.
11. Include routes for:
   - /login (kids)
   - /dashboard (parents)
   - /api/complete-task (used by Livewire to mark task done)
12. Include Alpine.js directives for confirmation popup and star animations.