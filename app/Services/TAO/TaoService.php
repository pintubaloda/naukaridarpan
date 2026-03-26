<?php
namespace App\Services\TAO;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TAO Open-Source Testing Platform Integration
 * Self-hosted TAO via REST API. TAO implements QTI 2.2 standard.
 * Install: https://www.taotesting.com/get-tao/
 */
class TaoService
{
    private string $base;
    private string $user;
    private string $pass;

    public function __construct()
    {
        $this->base = rtrim(config('services.tao.url', ''), '/');
        $this->user = config('services.tao.username', 'admin');
        $this->pass = config('services.tao.password', '');
    }

    /** Push all questions from a parsed paper as a QTI test, return tao_test_id */
    public function createTestFromPaper(array $parsedData, string $title): ?string
    {
        $itemUris = [];
        foreach ($parsedData['questions'] ?? [] as $q) {
            $uri = $this->createItem($q, $title);
            if ($uri) $itemUris[] = $uri;
        }
        if (empty($itemUris)) return null;
        return $this->createTest($title, $itemUris, ['duration_minutes' => $parsedData['duration_minutes'] ?? 60]);
    }

    public function createItem(array $q, string $context = ''): ?string
    {
        $xml  = $this->buildQtiItem($q);
        $resp = $this->post('/taoQtiItem/RestQtiItem/createItem', [
            'label'   => "Q{$q['serial']}: " . mb_substr($q['text'], 0, 60),
            'content' => $xml,
        ]);
        return $resp['uri'] ?? null;
    }

    public function createTest(string $title, array $itemUris, array $opts = []): ?string
    {
        $xml  = $this->buildQtiTest($title, $itemUris, $opts);
        $resp = $this->post('/taoQtiTest/RestQtiTest/createTest', ['label' => $title, 'content' => $xml]);
        return $resp['uri'] ?? null;
    }

    public function getDeliveryLaunchUrl(string $testUri, int $studentId): ?string
    {
        $resp = $this->post('/taoDelivery/RestDelivery/createDelivery', ['test' => $testUri, 'label' => "ND_delivery_{$studentId}"]);
        $deliveryUri = $resp['uri'] ?? null;
        if (! $deliveryUri) return null;
        $launch = $this->get('/taoDelivery/RestDelivery/getLaunchUrl', ['delivery' => $deliveryUri, 'testtaker' => $studentId]);
        return $launch['launch_url'] ?? null;
    }

    public function createDeliveryWithLaunch(string $testUri, int $studentId): array
    {
        $resp = $this->post('/taoDelivery/RestDelivery/createDelivery', ['test' => $testUri, 'label' => "ND_delivery_{$studentId}"]);
        $deliveryUri = $resp['uri'] ?? null;
        if (! $deliveryUri) return ['delivery_uri' => null, 'launch_url' => null];
        $launch = $this->get('/taoDelivery/RestDelivery/getLaunchUrl', ['delivery' => $deliveryUri, 'testtaker' => $studentId]);
        return ['delivery_uri' => $deliveryUri, 'launch_url' => $launch['launch_url'] ?? null];
    }

    // ── QTI XML builders ───────────────────────────────────────────────

    private function buildQtiItem(array $q): string
    {
        return match ($q['type'] ?? 'mcq') {
            'mcq', 'omr'                       => $this->mcqXml($q, false),
            'msq'                              => $this->mcqXml($q, true),
            'fill_blank'                       => $this->fillBlankXml($q),
            'short_answer', 'long_answer'      => $this->extendedTextXml($q),
            'math'                             => $this->mathXml($q),
            default                            => $this->mcqXml($q, false),
        };
    }

    private function mcqXml(array $q, bool $multi): string
    {
        $id   = "item_{$q['serial']}";
        $rid  = "RESP_{$q['serial']}";
        $card = $multi ? 'multiple' : 'single';
        $max  = $multi ? 0 : 1;
        $qt   = htmlspecialchars($q['text'] ?? '');
        $marks= $q['marks'] ?? 1;

        $choices = '';
        foreach ($q['options'] ?? [] as $opt) {
            $cid     = 'c_' . strtolower($opt['label']);
            $txt     = htmlspecialchars($opt['text'] ?? $opt['label']);
            $choices .= "<simpleChoice identifier=\"{$cid}\">{$txt}</simpleChoice>\n";
        }

        $correctVals = '';
        $ans = is_array($q['correct_answer'] ?? null) ? $q['correct_answer'] : [$q['correct_answer'] ?? ''];
        foreach (array_filter($ans) as $a) {
            $correctVals .= "<value>c_" . strtolower($a) . "</value>\n";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p2" identifier="{$id}" title="Question {$q['serial']}" adaptive="false" timeDependent="false">
  <responseDeclaration identifier="{$rid}" cardinality="{$card}" baseType="identifier">
    <correctResponse>{$correctVals}</correctResponse>
  </responseDeclaration>
  <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float"><defaultValue><value>0</value></defaultValue></outcomeDeclaration>
  <itemBody>
    <p>{$qt}</p>
    <choiceInteraction responseIdentifier="{$rid}" maxChoices="{$max}">
      <prompt>Select the correct option:</prompt>
      {$choices}
    </choiceInteraction>
  </itemBody>
  <responseProcessing template="http://www.imsglobal.org/question/qti_v2p2/rptemplates/match_correct"/>
</assessmentItem>
XML;
    }

    private function fillBlankXml(array $q): string
    {
        $id = "item_{$q['serial']}"; $rid = "RESP_{$q['serial']}";
        $qt = htmlspecialchars($q['text'] ?? '');
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p2" identifier="{$id}" title="Q{$q['serial']}" adaptive="false" timeDependent="false">
  <responseDeclaration identifier="{$rid}" cardinality="single" baseType="string"/>
  <itemBody><p>{$qt}</p><textEntryInteraction responseIdentifier="{$rid}" expectedLength="100"/></itemBody>
  <responseProcessing template="http://www.imsglobal.org/question/qti_v2p2/rptemplates/match_correct"/>
</assessmentItem>
XML;
    }

    private function extendedTextXml(array $q): string
    {
        $id = "item_{$q['serial']}"; $rid = "RESP_{$q['serial']}";
        $qt = htmlspecialchars($q['text'] ?? '');
        $max = $q['type'] === 'long_answer' ? 500 : 150;
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p2" identifier="{$id}" title="Q{$q['serial']}" adaptive="false" timeDependent="false">
  <responseDeclaration identifier="{$rid}" cardinality="single" baseType="string"/>
  <itemBody><p>{$qt}</p><extendedTextInteraction responseIdentifier="{$rid}" maxWords="{$max}"/></itemBody>
</assessmentItem>
XML;
    }

    private function mathXml(array $q): string
    {
        $id = "item_{$q['serial']}"; $rid = "RESP_{$q['serial']}";
        $qt = htmlspecialchars($q['text'] ?? '');
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p2" identifier="{$id}" title="Q{$q['serial']}" adaptive="false" timeDependent="false">
  <responseDeclaration identifier="{$rid}" cardinality="single" baseType="string"/>
  <itemBody><p class="math">{$qt}</p><textEntryInteraction responseIdentifier="{$rid}" expectedLength="50"/></itemBody>
</assessmentItem>
XML;
    }

    private function buildQtiTest(string $title, array $uris, array $opts): string
    {
        $dur  = ($opts['duration_minutes'] ?? 60) * 60;
        $safe = htmlspecialchars($title);
        $refs = implode("\n", array_map(fn($u, $i) => "<assessmentItemRef identifier=\"ref_{$i}\" href=\"{$u}\"/>", $uris, array_keys($uris)));
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<assessmentTest xmlns="http://www.imsglobal.org/xsd/imsqti_v2p2" identifier="test_nd" title="{$safe}">
  <timeLimits maxTime="PT{$dur}S"/>
  <testPart identifier="part1" navigationMode="linear" submissionMode="individual">
    <assessmentSection identifier="sec1" title="Questions" visible="true">
      <ordering shuffle="true"/>
      {$refs}
    </assessmentSection>
  </testPart>
</assessmentTest>
XML;
    }

    // ── HTTP helpers ───────────────────────────────────────────────────

    private function post(string $path, array $data): array
    {
        try {
            $r = Http::withBasicAuth($this->user, $this->pass)->timeout(30)->post($this->base . $path, $data);
            return $r->json() ?? [];
        } catch (\Exception $e) { Log::error("TAO POST {$path}: " . $e->getMessage()); return []; }
    }

    private function get(string $path, array $params = []): array
    {
        try {
            $r = Http::withBasicAuth($this->user, $this->pass)->timeout(30)->get($this->base . $path, $params);
            return $r->json() ?? [];
        } catch (\Exception $e) { Log::error("TAO GET {$path}: " . $e->getMessage()); return []; }
    }
}
