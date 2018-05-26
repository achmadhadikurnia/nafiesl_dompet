<?php

namespace Tests\Feature\Api;

use App\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TransacionEntryTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_create_an_income_transaction()
    {
        $month = '01';
        $year = '2017';
        $date = '2017-01-01';
        $user = $this->createUser();
        $category = factory(Category::class)->create(['creator_id' => $user->id]);

        $this->postJson(route('api.transactions.store'), [
            'in_out'      => 1,
            'amount'      => 99.99,
            'date'        => $date,
            'description' => 'Income description',
            'category_id' => $category->id,
        ], [
            'Authorization' => 'Bearer '.$user->api_token,
        ]);

        $this->seeInDatabase('transactions', [
            'in_out'      => 1, // 0:spending, 1:income
            'amount'      => 99.99,
            'date'        => $date,
            'description' => 'Income description',
            'category_id' => $category->id,
        ]);

        $this->seeStatusCode(201);
        $this->seeJson([
            'message'     => __('transaction.income_added'),
            'amount'      => '99.99',
            'date'        => $date,
            'description' => 'Income description',
            'category'    => $category->name,
        ]);
    }

    /** @test */
    public function user_can_create_an_spending_transaction()
    {
        $month = '01';
        $year = '2017';
        $date = '2017-01-01';
        $user = $this->createUser();
        $category = factory(Category::class)->create(['creator_id' => $user->id]);

        $this->postJson(route('api.transactions.store'), [
            'in_out'      => 0,
            'amount'      => 99.99,
            'date'        => $date,
            'description' => 'Spending description',
            'category_id' => $category->id,
        ], [
            'Authorization' => 'Bearer '.$user->api_token,
        ]);

        $this->seeInDatabase('transactions', [
            'in_out'      => 0, // 0:spending, 1:spending
            'amount'      => 99.99,
            'date'        => $date,
            'description' => 'Spending description',
            'category_id' => $category->id,
        ]);

        $this->seeStatusCode(201);
        $this->seeJson([
            'message'     => __('transaction.spending_added'),
            'amount'      => '- 99.99',
            'date'        => $date,
            'description' => 'Spending description',
            'category'    => $category->name,
        ]);
    }
}
