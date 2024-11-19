<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\Rules\Phone;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

class ValidatorController extends Controller
{
    public function validatePhone(Request $request)
    {
        // Ensure the country and phone fields are provided
        $request->validate([
            'phone' => 'required|string',
            'country' => 'required|string|size:2', // ISO country code should be 2 characters
        ]);

        // Combine the phone number with the country code
        $formattedPhone = $this->formatPhoneNumber($request->input('phone'), $request->input('country'));

        // Check if phone number was formatted successfully
        if (!$formattedPhone) {
            return response()->json([
                'message' => 'Invalid phone number format.',
            ], 422);
        }

        // Define the validation rules
        $rules = [
            'phone' => [
                'required',
                new Phone($request->input('country')), // Use the country code to validate the phone number
            ]
        ];

        // Validate the phone number
        $validator = Validator::make(['phone' => $formattedPhone], $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid phone number.',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        // Return success response if valid
        return response()->json([
            'message' => 'Phone number is valid.',
        ], 200);
    }

    /**
     * Format phone number into E.164 format using the country code.
     */
    private function formatPhoneNumber($phone, $country)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $number = $phoneUtil->parse($phone, strtoupper($country)); // Parse phone number
            return $phoneUtil->format($number, PhoneNumberFormat::E164); // Format into E.164 format
        } catch (\libphonenumber\NumberParseException $e) {
            return null; // Return null if the number is invalid
        }
    }
}
