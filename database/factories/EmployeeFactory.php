<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{
    Employee,
    GeneralDirection,
    Direction,
    Subdirectorate,
    Department
};
use Faker\Factory as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $faker = Faker::create();
        $_name = $faker->name();
        $_urlAvatar = sprintf("https://ui-avatars.com/api/?name=%s&background=234551&color=eee", str_replace(" ", "+", $_name));
        
        $generalDirection = GeneralDirection::get()->random();

        $direction = Direction::where('general_direction_id', $generalDirection->id)->inRandomOrder()->first();
        if($direction == null){
            $direction = Direction::create([
                'name' => $faker->company(),
                'general_direction_id' => $generalDirection->id
            ]);
        }

        $subDirection = Subdirectorate::where('direction_id', $direction->id)->inRandomOrder()->first();
        if( $subDirection == null ){
            $subDirection = Subdirectorate::create([
                "name" => $faker->company(),
                "direction_id" => $direction->id
            ]);
        }

        $department = Department::where("subdirectorate_id", $subDirection->id )->inRandomOrder()->first();
        if( $department == null ){
            $department = Department::create([
                "name" => $faker->company(),
                "subdirectorate_id" => $subDirection->id
            ]);
        }
        
        return [
            'general_direction_id' => $generalDirection->id,
            'direction_id' => $direction !=null ?$direction->id :null,
            'subdirectorate_id' => $subDirection !=null ?$subDirection->id :null,
            'department_id' => $department !=null ?$department->id :null,
            'name' => $_name,
            'photo' => $_urlAvatar,
            'plantilla_id' => "9".$faker->randomNumber(8)
        ];
    }
}
