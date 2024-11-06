<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ActionLog;
use App\Models\City;
use App\Models\Country;
use App\Models\Destination;
use App\Models\NaturalDestination;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use Unsplash\HttpClient;
use Unsplash\Search;

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
      $unsplash_access_key = env('UNSPLASH_ACCESS_KEY');
      HttpClient::init([
        'applicationId'	=> $unsplash_access_key,
        'utmSource' => 'VibeAdventures'
      ]);
    }

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

      if(!$destination->destination_id){
        
        $destination_name = $destination->name;
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
          'content' => 'Briefly describe the best time to visit (weather, local activities, celebration, no crowds, etc.) in up to 7 sentences in bullet points (each up to 15-25 words).'
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
            break;
          default:
            return ApiResponse::invalid('Invalid category');
          break;
        }


        $messages[]= $travel_tips_message;
        $messages[]= $best_time_message;
        
        

        try {

          $open_ai_response = $this->openAIService->getOpenAiChat($messages);
          
          if(empty($open_ai_response['choices'])){
            return ApiResponse::error($open_ai_response['error']['message']);
          }

          $chat_completion = $open_ai_response['choices'][0]['message'];
          $content = json_decode($chat_completion['content'],true);

          $things_to_do = is_array($content['things_to_do']) ? json_encode($content['things_to_do']) : $content['things_to_do'];
          $travel_tips = is_array($content['travel_tips']) ? json_encode($content['travel_tips']) : $content['travel_tips'];
          $best_time_to_visit = is_array($content['best_time_to_visit']) ? json_encode($content['best_time_to_visit']) : $content['best_time_to_visit'];
          $insert_data = [
            'overview' => 'overview',
            'quick_facts' => 'quick_facts',
            'qf_population' => $content['quick_facts']['population'],
            'qf_capital' => 'Berlin',
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

      return ApiResponse::success($destination, $response_message);

    }// end

    function getUnsplashGallery(Request $request){

      $destination = $request->input('destination');
     
      try {
       
        $response = Search::photos($destination, 1, 10);
        
        $images = collect($response->getResults())->map(function ($photo) {
            return [
                'id' => $photo['id'],
                'url' => $photo['urls']['regular']. '&w=500&h=350',
                'alt' => $photo['alt_description'],
                'description' => $photo['description'],
            ];
        });

        return ApiResponse::success($images, 'Images Found');
      } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
      }

    }
}
