<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\Rules\Phone;
use Propaganistas\LaravelPhone\PhoneNumber;

class ValidatorController extends Controller
{

    /**
     * Validate phone number.            
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function validatePhone(Request $request)
    {
        // Validate input
        $request->validate([
            'phone' => 'required|string',
            'country' => 'required|string|size:2', // Ensure valid ISO country code
        ]);

        // Format the phone number
        $formattedPhone = $this->formatPhoneNumber($request->input('phone'), $request->input('country'));

        if (!$formattedPhone) {
            return response()->json([
                'message' => 'Invalid phone number format.',
            ], 422);
        }

        // Validate the formatted phone number
        $rules = [
            'phone' => [
                'required',
                new Phone($request->input('country')),
            ],
        ];

        $validator = Validator::make(['phone' => $formattedPhone], $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid phone number.',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        // Return success response
        return response()->json([
            'message' => 'Phone number is valid.',
            'formatted_phone' => $formattedPhone,
        ], 200);
    }

    /**
     * Format phone number.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param string $phone Phone number
     * @param string $country Country code
     * @return string|null     
     */
    private function formatPhoneNumber(string $phone, string $country): ?string
    {
        try {
            // Create a PhoneNumber object with the phone and country
            $phoneNumber = new PhoneNumber($phone, $country);

            // Format as E.164 (international format with + prefix)
            return $phoneNumber->formatE164();
        } catch (\Exception $e) {
            return null; // Return null if formatting fails
        }
    }
}
