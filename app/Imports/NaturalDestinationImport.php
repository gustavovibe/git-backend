<?php

namespace App\Imports;

use App\Models\NaturalDestination;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NaturalDestinationImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        try {
            return new NaturalDestination([
                "t_natural_id" => $row['t_natural_id'],
                "name" => $row['name'],
                "type" => $row['type'],
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
    public function rules(): array
    {
        return [
            '*.t_natural_id' => ['required', 'integer'],
            '*.name' => ['required', 'string'],
            '*.type' => ['required', 'string'],
        ];
    }
}
