<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ActionLog;
use App\Models\City;
use App\Models\Country;
use App\Models\Destination;
use App\Models\NaturalDestination;
use App\Models\TravelGuideGallery;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use Unsplash\HttpClient;
use Unsplash\Search;
use Illuminate\Support\Facades\Http;

class DestinationController extends Controller
{
  protected $openAIService;
  public function index()
  {
      //
  }

  public function __construct(OpenAIService $openAIService)
  {
    $this->openAIService = $openAIService;
    $unsplash_access_key = 'RsB-POlI_RSW0h8EzKksRl97YlzJVjQIQ4eH7pn1j8Q';
    HttpClient::init([
      'applicationId'	=> $unsplash_access_key,
      'utmSource' => 'VibeAdventures'
    ]);
  }

  /**
   * Store a new destination.
   * 
   * Updated at 10/12/2024 (user)
   * 
   * @param Request $request Request object
   * @return array
   */
  public function store(Request $request)
  {
      $user = auth()->user();
      $category = $request->category;
      $id = $request->id;
      $destination_name = '';
      switch ($category) {
          case 'natural_destination':
              $destination = NaturalDestination::with('destination')->where('t_natural_id', $id)->first();
              $destination_name = $destination->destination_name;

              break;
          case 'country':
              $destination = Country::with('destination')->where('t_country_id', $id)->first();
              $destination_name = $destination->name;
              break;
          case 'city':
              $destination = City::with('destination')->where('t_city_id', $id)->first();
              $destination_name = $destination->city_name;
              break;
          default:
              $destination = null;
      }


      if ($destination === null) {
          return ApiResponse::notFound('Destination not found');
      }

      try {
          $validatedData = $request->validate([
              'overview' => 'required|string',
              'quick_facts' => 'required|string',
              'things_to_do' => 'required|string',
              'travel_tips' => 'required|string',
              'best_time_to_visit' => 'required|string',
              'slug' => 'required|string',
              'excerpt' => 'required|string',
              'meta_description' => 'required|string',
          ]);
      } catch (\Illuminate\Validation\ValidationException $e) {
          $errors = $e->errors();

          $customErrors = [];
          foreach ($errors as $field => $messages) {
              $customErrors[] = implode(' ', $messages); // Unir todos los mensajes de un campo en una sola cadena
          }

          return ApiResponse::error([implode(' ', $customErrors)]);
      }

      $destination_detail = $destination->destination;

      if ($destination_detail) {

          $destination_detail->update($validatedData);

          ActionLog::create([
              'user_id' => $user->id,
              'type' => 'Update',
              'action' => 'Destination update successfully' . ' ' . $category . ' ' . $destination_name,
              'item' => 'Destination',
          ]);

          return ApiResponse::success($destination_detail, 'Destination updated successfully');
      } else {
          $newDestination = Destination::create($validatedData);

          $destination->update(['destination_id' => $newDestination->id]);

          $destination->save();

          ActionLog::create([
              'user_id' => $user->id,
              'type' => 'Create',
              'action' => 'Destination created successfully' . ' ' . $category . ' ' . $destination_name,
              'item' => 'Destination',
          ]);

          return ApiResponse::success($newDestination, 'Destination created successfully');


      }
  }

  /**
   * Get destination.
   * 
   * Updated at 10/12/2024 (user)
   * 
   * @param Request $request Request object
   * @param int $id Destination ID
   * @return array
   */
  public function show(Request $request, $id)
  {
      $category = $request->query('category');

      $destination = null;

      switch ($category) {
          case 'natural_destination':
              $destination = NaturalDestination::with('destination')->where('t_natural_id', $id)->first();;
              break;
          case 'country':
              $destination = Country::with('destination')->where('t_country_id', $id)->first();
              break;
          case 'city':
              $destination = City::with('destination')->where('t_city_id', $id)->first();
              break;
          default:
              return ApiResponse::invalid('Invalid category');
      }

      if (!$destination) {
          return ApiResponse::notFound('Destination not found');
      }

      return ApiResponse::success($destination);
  }

  public function edit($id)
  {
      //
  }

  public function update(Request $request, $id)
  {
      //
  }


  public function destroy($id)
  {

  }


  /**
   * Get Destination Guide for: tours/?search=true&type=country&t_id=84
   * Provides Info, Quick Facts, Galley
   * Gets:
   *  Category (string) natural destinattion, country or city
   *  Id (number)
   */
  public function getDestinationGuide(Request $request){

    $category = $request->input('category');
    $id = $request->input('id');
    $destination = null;
    $response_message = 'Destination guide found.';

    switch ($category) {
      case 'natural_destination':
        $destination = NaturalDestination::with('destinations')->where('t_country_id', $id)->first();
        break;
      case 'country':
        $destination = Country::with('destination')->where('t_country_id', $id)->first();
        break;
      case 'city':
        $destination = City::with('destination')->where('t_city_id', $id)->first();
        break;
      default:
        return ApiResponse::invalid('Invalid category');
    }

    if(!$destination){
      return ApiResponse::notFound('Destination not found');
    }
    
    if($destination->destination_id && $destination->destination->overview == 'overview'){

      $destinations_table = Destination::find($destination->destination_id);
      $messages = [
        ['role' => 'user', 'content' => 'Write a brief overview of the destination: '.$destination->name.', including the highlights of traveling there as well as a few characteristics. The text should not exceed 200 words.']
      ];
      $open_ai_response = $this->openAIService->getOpenAiChatSimple($messages);
      $overview = isset($open_ai_response['choices']) && !empty($open_ai_response['choices']) ? $open_ai_response['choices'][0]['message']['content'] : '';
      if(empty($open_ai_response['choices'])){
        return ApiResponse::error($open_ai_response['error']['message']);
      }

      $destinations_table->update([
        'overview' => $overview
      ]);
      $destination->overview = $overview;
      $destination->destination->$overview = $overview;

    }// end if($destination->destination_id && $destination->overview == 'overview'){

    if($destination->destination_id && empty($destination->destination->video_url)){
      /** YT Video searcch */
      $destinations_table = Destination::find($destination->destination_id);
      
      try {

        $gcloud_api_key = !empty(env('GCLOUD_API_KEY')) ? env('GCLOUD_API_KEY') : 'AIzaSyBIjpGancr9vQByFa1MUst_eo29spVtSmM';
        $search_query = 'Best places to visit in ' . $destination->name;
        $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
          'part' => 'snippet',
          'q' => $search_query,
          'maxResults' => 3,
          'key' => $gcloud_api_key,
          'type' => 'video',
          'relevanceLanguage' => 'en'
        ]);
        $data = $response->json();

        if(isset($data['items'][0]['id']['videoId'])){

          $video_id = $data['items'][0]['id']['videoId'];
          $video_url = "https://www.youtube.com/embed/" . $video_id;

          $destinations_table->update([
            'video_url' => $video_url
          ]);
          $destination->destination->video_url = $video_url;
          
        }

      } catch (\Exception $e) {
        return ApiResponse::error('Failed to fetch data from YouTube API: ' . $e->getMessage());
      }
      

    }// end if($destination->destination_id && $destination->overview == 'overview'){

    if(!$destination->destination_id){
      
      $destination_name = $category == 'country' ? $destination->name : ($category == 'city' ? $destination->city_name : '');
      $quick_facts_message = [
        'role'=> 'user',
        'content' => 'Give me the following quick facts about the destination, include brief data with inputs for Population, Area (km2), Currency, Official language(s), Country Code, Plug Type, Time Zone, and High Season'
      ];
      $things_to_do_message = [
        'role'=> 'user',
        'content' => 'Include 10 activities with a 15-25 word description each (choose geographically dispersed activities, spreading them over the territory of the destination, not all in one place)'
      ];
      $top_attractions_message = [
        'role'=> 'user',
        'content' => 'Make a list of 10, with #1 being the most popular, with each having a 15-25-word entrance describing it (choose geographically dispersed attractions, spreading them over the territory of the destination, not all in one place).'
      ];
      $travel_tips_message = [
        'role'=> 'user',
        'content' => 'Include 10 activities with a 15-25 word description each (choose geographically dispersed activities, spreading them over the territory of the destination, not all in one place).'
      ];
      $best_time_message = [
        'role'=> 'user',
        'content' => 'Make a list of 7 items that describe the best time to visit (weather, local activities, celebration, no crowds, etc.). (each up to 15-25 words).'
      ];
      $overview_message = [
        'role' => 'user',
        'content' => 'Write a brief overview of the destination, including the highlights of traveling there as well as a few characteristics. The text should not exceed 200 words.'
      ];
      $messages = array();
      switch ($category) {

        case 'natural_destination':
          $open_message = [
            'role'=> 'user',
            'content' =>"Hello, I'd like some information about this natural destination: ".$destination_name
          ];
          $messages[]= $open_message;
          $messages[]= $quick_facts_message;
          $messages[]= $things_to_do_message;
          
          break;
        case 'country':
          $open_message = [
            'role'=> 'user',
            'content' => "Hello, I'd like some information about this country: ".$destination_name
          ];
          $messages[]= $open_message;
          $messages[]= $quick_facts_message;
          $messages[]= $things_to_do_message;
          $messages[]= $top_attractions_message;
          $messages[]= $overview_message;
          break;
        case 'city':
          $open_message = [
            'role'=> 'user',
            'content' => "Hello, I'd like some information about this city: ".$destination_name
          ];
          $messages[]= $open_message;
          $messages[]= $quick_facts_message;
          $messages[]= $things_to_do_message;
          $messages[]= $top_attractions_message;
          $messages[]= $overview_message;
          break;
        default:
          return ApiResponse::invalid('Invalid category');
        break;
      }


      $messages[]= $travel_tips_message;
      $messages[]= $best_time_message;
      
      try {

        $open_ai_response = $this->openAIService->getOpenAiChat($messages);
        \Log::info('ChatGPT response log.', ['response' => $open_ai_response]);
        if(empty($open_ai_response['choices'])){
          return ApiResponse::error('Aqui, llega' . $open_ai_response['error']['message']);
        }

        $chat_completion = $open_ai_response['choices'][0]['message'];
        $content = json_decode($chat_completion['content'],true);

        $things_to_do = is_array($content['things_to_do']) ? json_encode($content['things_to_do']) : $content['things_to_do'];
        $travel_tips = is_array($content['travel_tips']) ? json_encode($content['travel_tips']) : $content['travel_tips'];
        $best_time_to_visit = is_array($content['best_time_to_visit']) ? json_encode($content['best_time_to_visit']) : $content['best_time_to_visit'];
        $overview = isset($content['overview']) ? $content['overview'] : 'overview';
        $tg_video = isset($content['tg_video']) ? $content['tg_video'] : '';
        $insert_data = [
          'overview' => $overview,
          'quick_facts' => 'quick_facts',
          'qf_population' => $content['quick_facts']['population'],
          'qf_capital' => $content['quick_facts']['capital'],
          'qf_area' => $content['quick_facts']['area'],
          'qf_currency' => $content['quick_facts']['currency'],
          'qf_official_language' => $content['quick_facts']['official_language'],
          'qf_country_code' => $content['quick_facts']['country_code'],
          'qf_plug_type' => $content['quick_facts']['plug_type'],
          'qf_time_zone' => $content['quick_facts']['timezone'],
          'qf_high_season' => $content['quick_facts']['high_season'],
          'things_to_do' => $things_to_do,
          'travel_tips' => $travel_tips,
          'best_time_to_visit' => $best_time_to_visit,
          'slug' => 'slug',
          'excerpt' => 'brief',
          'meta_description' => 'guide',
          'video_url' => $tg_video
        ];

        $new_destination = Destination::create($insert_data);
        $destination->update(['destination_id' => $new_destination->id]);
        $destination->save();

        switch ($category) {
          case 'natural_destination':
            $destination = NaturalDestination::with('destinations')->where('t_country_id', $id)->first();
            break;
          case 'country':
            $destination = Country::with('destination')->where('t_country_id', $id)->first();
            break;
          case 'city':
            $destination = City::with('destination')->where('t_city_id', $id)->first();
            break;
          default:
            return ApiResponse::invalid('Invalid category');
        }
        $response_message = 'Destination guide created successfully';
        return ApiResponse::success($destination, $response_message);

      } catch (\Illuminate\Validation\ValidationException $e) {

        $errors = $e->errors();
        $customErrors = [];
        foreach ($errors as $field => $messages) {
            $customErrors[] = implode(' ', $messages); // Unir todos los mensajes de un campo en una sola cadena
        }
        return ApiResponse::error([implode(' ', $customErrors)]);

      }

    }// end if(!destination->destination_id)

    $destination->best_time_to_visit = $this->parseTextContent($destination->destination->best_time_to_visit ?? '');
    $destination->travel_tips = $this->parseTextContent($destination->destination->travel_tips ?? '');
    $destination->things_to_do = $this->parseTextContent($destination->destination->things_to_do ?? '');
    $destination->video_url = $destination->destination->video_url;
    return ApiResponse::success($destination, $response_message);

  }// end

  /**
   * Provides Image Galleries for Tours destination pages: https://unsplash.com/es
   * Gets:
   * Id (number) tour id to search if has already a gallery
   * If its empty make a request to Unsplash API: https://unsplash.com/documentation
   * 
   */
  public function getUnsplashGallery(Request $request){

    $gallery = null;
    $id = $request->input('id');
    $destination = $request->input('destination');
    $response_message = 'Travel guide found.';
    $gallery = TravelGuideGallery::where('t_id', $id)->get();
    
    if($gallery->isEmpty()){
      
      try {
        
        $num_items = 10;
        $page = 1;
        $orientation = 'landscape';
        $insert_data = array();
        $response = Search::photos($destination, $page, $num_items, $orientation);
        $images = collect($response->getResults())->map(function ($photo) {
            return [
              'unsplash_id' => $photo['id'],
              'url' => $photo['urls']['regular']. '&w=1920&h=1080',
              'name' => $photo['alt_description'],
              'author' => $photo['user']['first_name']. ' ' .$photo['user']['last_name'],
              'author_url' => $photo['user']['links']['html']
            ];
        });

        foreach($images as $image){
          $insert_data[] = [
            't_id' => (int)$id,
            'unsplash_id' => $image['unsplash_id'],
            'name' => $image['name'],
            'url' => $image['url'],
            'author' => $image['author'],
            'author_url' => $image['author_url']
          ];
        }
        
        \Log::info('unsplash api response log.', ['response' => $response]);
        $new_gallery = TravelGuideGallery::insert($insert_data);

        return ApiResponse::success($insert_data, 'Unsplash Images');

      } catch (\Exception $e) {
        return ApiResponse::error($e->getMessage());
      }

    }else{
      return ApiResponse::success($gallery, $response_message);
    }

  }// end 

  function parseTextContent($value){
    
    if (is_array($value)) {
      return $value;
    }

    if (is_string($value)) {
      
      $jsonDecoded = json_decode($value, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($jsonDecoded)){
        return $jsonDecoded;
      }
      $items = preg_split('/[\•\-]\s*/u', $value, -1, PREG_SPLIT_NO_EMPTY);
      return array_map('trim', $items);

    }

    return [];
  }// private function parseTextContent($value){

  public function searchYTApi(Request $request){

    $search_query = $request->input('search', 'highlights de México para visitar el país');
    if(empty($search_query)){
      return ApiResponse::error('Search Query is empty');
    }
    
    $gcloud_api_key = !empty(env('GCLOUD_API_KEY')) ? env('GCLOUD_API_KEY') : 'AIzaSyBIjpGancr9vQByFa1MUst_eo29spVtSmM';
    
    try {

      $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
        'part' => 'snippet',
        'q' => $search_query,
        'maxResults' => 5,
        'key' => $gcloud_api_key,
        'type' => 'video'
      ]);
      $data = $response->json();
      return ApiResponse::success($data, 'Success');

    } catch (\Exception $e) {
      return ApiResponse::error('Failed to fetch data from YouTube API: ' . $e->getMessage());
    }
  

  }// end public function searchYTApi(Request $request){

}
