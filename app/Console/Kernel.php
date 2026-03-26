<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Daily AI blog post at 6 AM IST
        $schedule->command('blog:generate --lang=English')->dailyAt('06:00')->withoutOverlapping();
        $schedule->command('blog:generate --lang=Hindi')->dailyAt('07:00')->withoutOverlapping();

        // Process 48-hr settlements every hour
        $schedule->command('settlements:process')->hourly();

        // Scrape new papers every Sunday 2 AM
        $schedule->command('scrape:papers --source=all --parse')->weekly()->sundays()->at('02:00');

        // Scrape professor leads every Monday 3 AM
        $schedule->command('scrape:professors')->weekly()->mondays()->at('03:00');

        // Weekly blogs (configurable via settings)
        $schedule->command('blog:generate --lang=English --topic="Weekly Current Affairs" --category="Current Affairs"')
            ->weekly()->mondays()->at('08:00')->withoutOverlapping()
            ->when(fn() => \App\Models\PlatformSetting::get('weekly_current_affairs_enabled', '1') === '1');

        $schedule->command('blog:generate --lang=English --topic="Weekly Historical News" --category="Historical News"')
            ->weekly()->tuesdays()->at('08:00')->withoutOverlapping()
            ->when(fn() => \App\Models\PlatformSetting::get('weekly_historical_news_enabled', '1') === '1');

        $schedule->command('blog:generate --lang=English --topic="Weekly Sports News" --category="Sports News"')
            ->weekly()->wednesdays()->at('08:00')->withoutOverlapping()
            ->when(fn() => \App\Models\PlatformSetting::get('weekly_sports_news_enabled', '1') === '1');

        $schedule->command('blog:generate --lang=English --topic="Weekly Most Important News" --category="Most Important News"')
            ->weekly()->thursdays()->at('08:00')->withoutOverlapping()
            ->when(fn() => \App\Models\PlatformSetting::get('weekly_top_news_enabled', '1') === '1');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
