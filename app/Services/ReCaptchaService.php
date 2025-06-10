<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RecaptchaService
{
  protected string $projectId;
  protected string $apiKey;
  protected string $siteKeyFrontend;

  public function __construct(){

    $this->projectId = !empty(env('GCLOUD_PROJECT_ID')) ? env('GCLOUD_PROJECT_ID') : 'vibe-adventures-1538011096375';
    $this->apiKey = !empty(env('GCLOUD_API_KEY')) ? env('GCLOUD_API_KEY') : 'AIzaSyBIjpGancr9vQByFa1MUst_eo29spVtSmM';
    $this->siteKeyFrontend = !empty(env('GCLOUD_RECAPTCHA_KEY')) ? env('GCLOUD_RECAPTCHA_KEY') : '6Lfoj4sqAAAAAHLT71BIUo5OwjQOU-nYfcKkHdqr';
    if (empty($this->projectId) || empty($this->apiKey) || empty($this->siteKeyFrontend)) {
      Log::error('reCAPTCHA Enterprise environment variables are not fully configured in RecaptchaService.');
    }
    
  }

  /**
   * Verifies the reCAPTCHA Enterprise token with Google's API.
   *
   * @param string $recaptchaToken The reCAPTCHA token from the frontend.
   * @param string|null $userIpAddress The user's IP address for better assessment.
   * @param string $expectedAction The expected action for the reCAPTCHA token (e.g., 'LOGIN', 'SUBMIT_FORM').
   * @return array An associative array with 'success' (boolean), 'message' (string), and optionally 'score'.
   */
  public function verifyRecaptchaToken(string $recaptchaToken, ?string $userIpAddress = null, string $expectedAction = 'SUBMIT_FORM'): array
  {
    if(empty($this->projectId) || empty($this->apiKey) || empty($this->siteKeyFrontend)) {
      return ['success' => false, 'message' => 'reCAPTCHA service is not configured.', 'score' => 0];
    }

    $recaptchaUrl = "https://recaptchaenterprise.googleapis.com/v1/projects/{$this->projectId}/assessments?key={$this->apiKey}";

    try{

      $recaptchaResponse = Http::post($recaptchaUrl, [
        'event' => [
          'token' => $recaptchaToken,
          'siteKey' => $this->siteKeyFrontend,
          'expectedAction' => $expectedAction,
          'userIpAddress' => $userIpAddress,
        ],
      ])->json();

      $score = $recaptchaResponse['riskAnalysis']['score'] ?? 0;
      $isValidToken = $recaptchaResponse['tokenProperties']['isValid'] ?? false;
      $action = $recaptchaResponse['tokenProperties']['action'] ?? '';

      $threshold = 0.5;

      if ($isValidToken && $action === $expectedAction && $score >= $threshold) {
        return ['success' => true, 'message' => 'reCAPTCHA verification passed.', 'score' => $score];
      } else {
        Log::warning('reCAPTCHA verification failed:', [
          'tokenValid' => $isValidToken,
          'actionMatch' => ($action === $expectedAction),
          'score' => $score,
          'reasons' => $recaptchaResponse['riskAnalysis']['reasons'] ?? 'N/A'
        ]);
        return ['success' => false, 'message' => 'reCAPTCHA verification failed. Please try again or refresh the page.', 'score' => $score];
      }

    } catch (\Exception $e){
      $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
      Log::error('reCAPTCHA HTTP Request Error:', ['message' => $e->getMessage(), 'response' => $responseBody]);
      return ['success' => false, 'message' => 'Failed to communicate with reCAPTCHA service.', 'score' => 0];
    } catch (Exception $e) {
      Log::error('reCAPTCHA General Error:', ['message' => $e->getMessage()]);
      return ['success' => false, 'message' => 'An unexpected error occurred during reCAPTCHA verification.', 'score' => 0];
    }
  }
}