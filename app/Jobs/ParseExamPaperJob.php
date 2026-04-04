<?php
namespace App\Jobs;

use App\Models\ExamPaper;
use App\Services\AI\PaperParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class ParseExamPaperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 300;

    public function __construct(
        private ExamPaper $paper,
        private string    $type,         // 'pdf' | 'typed' | 'url'
        private ?string   $rawText = null
    ) {}

    public function handle(PaperParserService $parser): void
    {
        Log::info("ParseExamPaperJob started for paper #{$this->paper->id}");

        $parsed = match ($this->type) {
            'pdf'  => $parser->parsePdf($this->paper),
            'url'  => $parser->parseUrl($this->paper, $this->rawText ?? ''),
            default => $parser->parseText($this->paper, $this->rawText ?? ''),
        };

        if (empty($parsed['questions'] ?? [])) {
            Log::warning("ParseExamPaperJob: no questions extracted for #{$this->paper->id}");
            return;
        }

        $this->paper->update([
            'tao_sync_status' => 'pending',
            'tao_last_error' => null,
        ]);

        Log::info("ParseExamPaperJob completed for paper #{$this->paper->id}");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ParseExamPaperJob FAILED for paper #{$this->paper->id}: " . $e->getMessage());
        $this->paper->update(['parse_status' => 'failed', 'parse_log' => $e->getMessage()]);
    }
}
