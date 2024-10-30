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
      $headers = [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . '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',
      ];
      $url = 'https://api.openai.com/v1/chat/completions';
      $response = Http::withHeaders($headers)->post($url, [
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
      ]);

      return $response->json();
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }
}
