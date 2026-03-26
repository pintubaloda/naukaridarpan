<?php
namespace App\Services\Scraper;

use App\Models\{ExamPaper, Category, ProfessorLead};
use Illuminate\Support\Facades\{Http, Log, Storage};
use Illuminate\Support\Str;

class ScraperService
{
    private array $paperSources = [
        ['name' => 'UPSC',    'url' => 'https://upsc.gov.in/examinations/previous-question-papers', 'category' => 'upsc'],
        ['name' => 'SSC',     'url' => 'https://ssc.nic.in/portal/question-paper',                   'category' => 'ssc'],
        ['name' => 'Railways','url' => 'https://indianrailways.gov.in/railwayboard/',                 'category' => 'railway'],
    ];

    /** Scrape PYQ papers from govt sites via Playwright */
    public function scrapePapers(string $source = 'all'): array
    {
        $targets = $source === 'all' ? $this->paperSources
            : array_filter($this->paperSources, fn($s) => $s['name'] === $source);

        $results = [];
        foreach ($targets as $t) {
            try {
                $papers = $this->runScript('scrape_papers', ['url' => $t['url'], 'category' => $t['category']]);
                foreach ($papers as $p) {
                    $record = $this->storePaper($p, $t);
                    if ($record) $results[] = $record->id;
                }
            } catch (\Exception $e) {
                Log::warning("Scraper [{$t['name']}]: " . $e->getMessage());
            }
        }
        return $results;
    }

    private function storePaper(array $p, array $source): ?ExamPaper
    {
        if (empty($p['pdf_url'])) return null;

        $cat = Category::where('slug', $source['category'])->first();
        if (! $cat) return null;

        $pdfResp = Http::timeout(60)->get($p['pdf_url']);
        if (! $pdfResp->successful()) return null;

        $filename = 'scraped/' . Str::uuid() . '.pdf';
        Storage::disk('s3')->put($filename, $pdfResp->body());

        return ExamPaper::firstOrCreate(
            ['slug' => Str::slug($p['title'] ?? 'pyq') . '-' . now()->format('Y') . '-' . Str::random(4)],
            [
                'seller_id'     => 1, // platform admin
                'category_id'   => $cat->id,
                'title'         => $p['title'] ?? $source['name'] . ' Previous Year Paper',
                'description'   => "Official previous year question paper — {$source['name']}. Auto-scraped and AI-converted.",
                'source'        => 'scraped',
                'original_file' => $filename,
                'parse_status'  => 'pending',
                'is_free'       => true,
                'seller_price'  => 0,
                'student_price' => 0,
                'status'        => 'draft',
                'language'      => 'English',
                'difficulty'    => 'medium',
            ]
        );
    }

    /** Scrape professor contacts from college websites and YouTube */
    public function scrapeProfessorLeads(int $limit = 100): int
    {
        $contacts = $this->runScript('scrape_professors', ['limit' => $limit]);
        $saved    = 0;

        foreach ($contacts as $c) {
            if (empty($c['email']) && empty($c['phone'])) continue;
            $exists = ProfessorLead::where('email', $c['email'] ?? null)->orWhere('profile_url', $c['profile_url'] ?? null)->exists();
            if (! $exists) {
                ProfessorLead::create([
                    'name'             => $c['name'] ?? null,
                    'email'            => $c['email'] ?? null,
                    'phone'            => $c['phone'] ?? null,
                    'platform'         => $c['platform'] ?? null,
                    'institution'      => $c['institution'] ?? null,
                    'subject'          => $c['subject'] ?? null,
                    'profile_url'      => $c['profile_url'] ?? null,
                    'subscriber_count' => $c['subscriber_count'] ?? 0,
                    'outreach_status'  => 'new',
                ]);
                $saved++;
            }
        }
        return $saved;
    }

    /** Scrape YouTube educator channels via YouTube Data API */
    public function scrapeYouTubeEducators(int $limit = 50): int
    {
        $apiKey = config('services.youtube.api_key');
        if (! $apiKey) { Log::warning('YouTube API key not configured'); return 0; }

        $queries = ['SSC preparation', 'UPSC preparation hindi', 'Bank PO preparation', 'Railway exam preparation India', 'Current affairs India competitive exam'];
        $saved   = 0;

        foreach ($queries as $q) {
            try {
                $resp = Http::get('https://www.googleapis.com/youtube/v3/search', [
                    'part' => 'snippet', 'q' => $q, 'type' => 'channel',
                    'maxResults' => (int) ceil($limit / count($queries)),
                    'key' => $apiKey, 'regionCode' => 'IN', 'relevanceLanguage' => 'hi',
                ]);
                foreach ($resp->json('items', []) as $item) {
                    $sn = $item['snippet'] ?? [];
                    $ch = $item['id']['channelId'] ?? null;
                    if (! $ch) continue;
                    $exists = ProfessorLead::where('profile_url', 'https://youtube.com/channel/' . $ch)->exists();
                    if (! $exists) {
                        ProfessorLead::create([
                            'name'        => $sn['channelTitle'] ?? null,
                            'platform'    => 'YouTube',
                            'subject'     => $q,
                            'profile_url' => 'https://youtube.com/channel/' . $ch,
                            'outreach_status' => 'new',
                        ]);
                        $saved++;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("YouTube scrape '{$q}': " . $e->getMessage());
            }
        }
        return $saved;
    }

    /** Run a Node.js Playwright script */
    private function runScript(string $name, array $args = []): array
    {
        $path = base_path("scripts/playwright/{$name}.js");
        if (! file_exists($path)) { Log::warning("Script not found: $path"); return []; }
        $out  = shell_exec('node ' . escapeshellarg($path) . ' ' . escapeshellarg(json_encode($args)) . ' 2>&1');
        $data = json_decode($out ?? '[]', true);
        return is_array($data) ? $data : [];
    }
}
