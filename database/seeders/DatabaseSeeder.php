<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $incomes = ['Зарплата', 'Подработка', 'Прочее'];
        foreach ($incomes as $income) {
            $category = IncomeCategory::where("title", $income)->first();
            if (!$category) {
                $category = new IncomeCategory();
                $category->title = $income;
                $category->save();
            }
        }

        $expenses = [
            ['title' => 'Продукты', 'isService' => false],
            ['title' => 'Одежда', 'isService' => false],
            ['title' => 'Кафе и рестораны', 'isService' => false],
            ['title' => 'Техника', 'isService' => false],
            ['title' => 'Здоровье', 'isService' => false],
            ['title' => 'Отдых и развлечения', 'isService' => false],
            ['title' => 'Прочее', 'isService' => true],
        ];
        foreach ($expenses as $expense) {
            $category = ExpenseCategory::where("title", $expense['title'])->first();
            if (!$category) {
                $category = new ExpenseCategory();
                $category->title = $expense['title'];
                $category->isService = $expense['isService'];
                $category->save();
            }
        }

        $sec = ExpenseCategory::secondary()->service()->first();
        if (!$sec) {
            $sec = new ExpenseCategory([
                'title' => "Прочее",
                'isService' => true,
                'level' => ExpenseCategory::SECONDARY_LEVEL,
            ]);
            $sec->save();
        }

        $final = ExpenseCategory::final()->service()->first();
        if (!$final) {
            $sec = new ExpenseCategory([
                'title' => "Прочее",
                'isService' => true,
                'level' => ExpenseCategory::FINAL_LEVEL,
            ]);
            $sec->save();
        }

        $admin = User::where("role", "admin")->first();
        if (!$admin) {
            $admin = new User();
            $admin->name = "Admin";
            $admin->email = "admin@qarjy.com";
            $admin->phone = '';
            $admin->password = Hash::make(123456);
            $admin->role='admin';
            $admin->save();
        }
    }
}
