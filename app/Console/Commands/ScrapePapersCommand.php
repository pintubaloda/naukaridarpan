<?php
namespace App\Console\Commands;

use App\Services\Scraper\ScraperService;
use App\Services\AI\PaperParserService;
use App\Models\ExamPaper;
use App\Jobs\ParseExamPaperJob;
use Illuminate\Console\Command;

class ScrapePapersCommand extends Command
{
    protected $signature   = 'scrape:papers {--source=all : Source name or all} {--parse : Auto-trigger AI parsing after scrape}';
    protected $description = 'Scrape previous year question papers from government websites';

    public function handle(ScraperService $scraper): int
    {
        $source = $this->option('source');
        $this->info("Scraping papers from: {$source}");

        $ids = $scraper->scrapePapers($source);
        $this->info("Scraped " . count($ids) . " new papers.");

        if ($this->option('parse')) {
            $this->info('Queuing AI parsing...');
            foreach ($ids as $id) {
                $paper = ExamPaper::find($id);
                if ($paper) ParseExamPaperJob::dispatch($paper, 'pdf');
            }
            $this->info('Parsing jobs queued.');
        }
        return Command::SUCCESS;
    }
}
