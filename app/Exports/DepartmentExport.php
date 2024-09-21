<?php

namespace App\Exports;

use App\Models\Department;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DepartmentExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Department::with(['agreements', 'component', ])->get();
    }

    /**
     * @param mixed $department
     * @return array
     */
    public function map($department): array
    {
        // Convert the agreements to a comma-separated string
        $agreements = $department->agreements->pluck('agree_id')->join(', ');

        return [
            'ID' => $department->dept_id,
            'Nom' => $department->dept_name,
            'Abréviation' => $department->dept_shortname,
            'Couleur' => $department->dept_color,
            'Composante' => $department->component ? $department->component->comp_name : '',
            'Accords' => $agreements,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID du Département',
            'Nom du Département',
            'Abréviation',
            'Couleur',
            'Nom de la Composante',
            'Id des Accords',
        ];
    }
}
