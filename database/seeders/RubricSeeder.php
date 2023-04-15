<?php

namespace Database\Seeders;

use App\Models\Rubric;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RubricSeeder extends Seeder
{
    private static function createRubrics($items, $parentId = null)
    {
        foreach ($items as $k => $v) {
            $rubricName = is_string($v)
                ? $v
                : $k;

            $rubricId = Rubric::query()->create([
                'name'      => $rubricName,
                'slug'      => Str::slug($rubricName),
                'parent_id' => $parentId,
            ])->id;

            if (is_array($v)) {
                self::createRubrics($v, $rubricId);
            }
        }
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rubricsStructure = [
            'Общество'    => [
                'Городская жизнь',
                'Выборы',
            ],
            'День города' => [
                'Cалюты',
                'Детская площадка' => [
                    '0-3 года',
                    '3-7 года',
                ],
            ],
            'Спорт',
        ];

        self::createRubrics($rubricsStructure);
    }
}
