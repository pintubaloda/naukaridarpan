<?php

namespace App\Console\Commands;

use App\Services\Scraper\ScraperService;
use Illuminate\Console\Command;

class ScrapeProfessorsCommand extends Command
{
    protected $signature   = 'scrape:professors {--source=college_directories} {--limit=100 : Max leads to scrape} {--youtube : Also scrape YouTube educators}';
    protected $description = 'Scrape professor and educator contact details for onboarding outreach';

    public function handle(ScraperService $scraper): int
    {
        $limit = (int) $this->option('limit');
        $total = 0;

        $this->info("Scraping professor leads (limit: {$limit})…");

        $saved = $scraper->scrapeProfessorLeads($limit);
        $this->info("Scraped {$saved} new professor leads from directories.");
        $total += $saved;

        if ($this->option('youtube')) {
            $ytSaved = $scraper->scrapeYouTubeEducators((int) ceil($limit / 2));
            $this->info("Scraped {$ytSaved} YouTube educator channels.");
            $total += $ytSaved;
        }

        $this->info("Total new leads added: {$total}");
        $this->info("View leads at: Admin → Professor Leads");

        return Command::SUCCESS;
    }
}
