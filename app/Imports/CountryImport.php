<?php

namespace App\Imports;

use App\Models\Country;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CountryImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        try {
            return new Country([
                "t_country_id" => $row['t_country_id'],
                "name" => $row['name'],
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.t_country_id' => ['required', 'integer'],
            '*.name' => ['required', 'string'],
        ];
    }
}
