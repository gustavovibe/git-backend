<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use App\Helpers\ApiResponse;

class OpenAIService
{

  public function __construct()
  {
    
    
  }

  public function getOpenAiChat($messages)
  {
    try {

      $openAiKey = !empty(env('OPENAI_API_KEY')) ? env('OPENAI_API_KEY') : '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';
      $headers = [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' .$openAiKey,
      ];
      $url = 'https://api.openai.com/v1/chat/completions';

      $response_format = [
        "type" => "json_schema",
        "json_schema" => [
          "name" => "travel_guide",
          "schema" => [
            "type" => "object",
            "properties" => [
              "quick_facts" => [
                "type" => "object",
                "properties" => [
                  "population" => ["type" => "string"],
                  "capital" => ["type" => "string"],
                  "area" => ["type" => "string"],
                  "currency" => ["type" => "string"],
                  "official_language" => ["type"=> "string"],
                  "country_code" => ["type" => "string"],
                  "plug_type" => ["type" => "string"],
                  "timezone" => ["type" => "string"],
                  "high_season" => ["type" => "string"],
                ],
                "required" => [
                  "population",
                  "capital",
                  "area",
                  "currency",
                  "official_language",
                  "country_code",
                  "plug_type",
                  "timezone",
                  "high_season"
                ],
                "additionalProperties" => false
              ],
              "things_to_do" => [
                "type" => "array",
                "items" => ["type" => "string"]
              ],
              "top_attractions" => [
                "type" => "array",
                "items" => ["type" => "string"]
              ],
              "travel_tips" => [
                "type" => "array",
                "items" => ["type" => "string"]
              ],
              "best_time_to_visit" => [
                "type" => "array",
                "items" => ["type" => "string"]
              ],
              "overview" => ["type" => "string"]
            ],
            "required" => ["quick_facts", "things_to_do", "top_attractions", "travel_tips", "best_time_to_visit", "overview"],
            "video_url" => ["type" => "string"],
            "additionalProperties" => false
          ],
          "strict" => true
        ]
      ];
    
      $response = Http::withHeaders($headers)->post($url, [
        'model' => 'gpt-4o-mini',
        'messages' => $messages,
        'response_format' => $response_format
      ]);

      return $response->json();
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function getOpenAiChatSimple($messages)
  {
    try {

      $openAiKey = !empty(env('OPENAI_API_KEY')) ? env('OPENAI_API_KEY') : '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';
      $headers = [
          'Accept' => 'application/json',
          'Authorization' => 'Bearer ' . $openAiKey,
      ];
      $url = 'https://api.openai.com/v1/chat/completions';

      $response = Http::withHeaders($headers)->post($url, [
          'model' => 'gpt-4o-mini',
          'messages' => $messages
      ]);

      return $response->json();

    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }
}
