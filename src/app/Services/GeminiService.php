<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * Key pages from gais.jp to use as context
     */
    private array $gaisUrls = [
        'https://gais.jp/',
        'https://gais.jp/information/',
        'https://gais.jp/official_member_information/',
        'https://gais.jp/member_rule/',
        'https://gais.jp/category/news/',
        'https://gais.jp/category/events/',
        'https://gais.jp/category/wg/',
        'https://gais.jp/inquiry/',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
    }

    /**
     * Send a chat message to Gemini API
     *
     * @param string $message User's message
     * @param array $history Previous conversation history
     * @param string|null $systemPrompt System instruction for the AI
     * @param array $tools Tools to enable (google_search, url_context)
     * @return array
     */
    public function chat(string $message, array $history = [], ?string $systemPrompt = null, array $tools = []): array
    {
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent";

        // Build contents array with history and new message
        $contents = [];

        // Add conversation history
        foreach ($history as $item) {
            $contents[] = [
                'role' => $item['role'],
                'parts' => [['text' => $item['content']]]
            ];
        }

        // Add the new user message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];

        // Build request payload
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'topP' => 0.95,
                'topK' => 40,
                'maxOutputTokens' => 4096,
            ]
        ];

        // Add system instruction if provided
        if ($systemPrompt) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemPrompt]]
            ];
        }

        // Add tools if provided
        if (!empty($tools)) {
            $payload['tools'] = $tools;
        }

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ])->timeout(120)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                // Extract the response text
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($text) {
                    return [
                        'success' => true,
                        'message' => $text,
                        'usage' => $data['usageMetadata'] ?? null,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'No response generated',
                    'error' => 'empty_response',
                ];
            }

            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'API request failed',
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];

        } catch (\Exception $e) {
            Log::error('Gemini API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to connect to Gemini API',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Chat with URL Context - fetches gais.jp pages for accurate answers
     *
     * @param string $message User's message
     * @param array $history Previous conversation history
     * @return array
     */
    public function simpleChat(string $message, array $history = []): array
    {
        // Build the prompt with URLs for context
        $urlList = implode("\n", $this->gaisUrls);

        $promptWithUrls = <<<PROMPT
以下のgais.jp公式ページの内容を参照して、ユーザーの質問に回答してください。

【参照するページ】
{$urlList}

【ユーザーの質問】
{$message}
PROMPT;

        $systemPrompt = <<<SYSTEM
あなたは「GAISペディア」、生成AI協会（GAIS）の公式ナレッジアシスタントです。

【最重要ルール - 必ず守ること】
★ 回答は必ず日本語で行ってください。英語での回答は禁止です。
★ ユーザーが英語で質問しても、日本語で回答してください。

【重要なルール】
1. 提供されたgais.jpのページ内容のみを参照して回答してください
2. ページに記載されていない情報は「公式サイトに該当する情報が見つかりませんでした」と回答してください
3. 日本語で丁寧に回答してください
4. 必要に応じて箇条書きや表形式を使って読みやすくしてください
5. 回答の最後に参照したページのリンクを必ず記載してください
6. 回答がわからない場合は「公式サイトに該当する情報が見つかりませんでした」と正直に回答してください
7. イベント情報に関して現時点と比較して、過去の日付の場合は「そのイベントは既に終了しています」と回答してください

【参照リンクのルール】
- 回答の最後に「参照リンク:」セクションを追加してください
- 実際に参照したgais.jpのページURLのみを表示してください
- 形式: - [ページタイトル](https://gais.jp/実際のパス/)
- 参照していないページのリンクは表示しないでください
- URLは提供されたURLリストから選んでください（創作禁止）

【例】
参照リンク:
- [正会員のご案内](https://gais.jp/official_member_information/)
- [会員規則](https://gais.jp/member_rule/)
SYSTEM;

        // Use URL Context tool
        $tools = [
            ['url_context' => new \stdClass()]
        ];

        return $this->chat($promptWithUrls, $history, $systemPrompt, $tools);
    }

    /**
     * Get the list of GAIS URLs used for context
     *
     * @return array
     */
    public function getGaisUrls(): array
    {
        return $this->gaisUrls;
    }
}
