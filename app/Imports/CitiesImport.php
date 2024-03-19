<?php

namespace App\Imports;

use App\Models\City;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CitiesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        try {
            return new City([
                "city_id" => $row['city_id'],
                "t_city_id" => $row['t_city_id'],
                "city_name" => $row['city_name'],
                "kiwi_id" => $row['kiwi_id'],
                "t_country_id" => $row['t_country_id']
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.city_id' => ['required', 'integer'],
            '*.t_country_id' => ['integer', 'required', 'exists:countries,t_country_id'],
            '*.t_city_id' => ['required', 'integer'],
            '*.city_name' => ['required', 'string'],
            '*.kiwi_id' => ['required', 'string'],
        ];
    }
}
