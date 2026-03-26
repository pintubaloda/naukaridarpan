<?php
namespace App\Console\Commands;

use App\Services\AI\BlogGeneratorService;
use Illuminate\Console\Command;

class GenerateBlogPostCommand extends Command
{
    protected $signature   = 'blog:generate {--lang=English : Language (English or Hindi)} {--count=1 : Number of posts} {--topic= : Force specific topic} {--category= : Force specific category}';
    protected $description = 'Generate AI blog post(s) about Sarkari Naukri / competitive exams';

    public function handle(BlogGeneratorService $service): int
    {
        $lang     = $this->option('lang');
        $count    = (int) $this->option('count');
        $topic    = $this->option('topic');
        $category = $this->option('category');

        $this->info("Generating {$count} blog post(s) in {$lang}...");

        for ($i = 0; $i < $count; $i++) {
            $post = $service->generateDailyPost($lang, $topic, $category);
            if ($post) {
                $this->info("✓ Created: {$post->title}");
            } else {
                $this->error("✗ Generation failed for post #" . ($i + 1));
            }
        }
        return Command::SUCCESS;
    }
}
