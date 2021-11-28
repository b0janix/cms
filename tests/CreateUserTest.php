<?php

use App\Models\User;
use Database\Seeders\UserSeeder;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CreateUserTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Check whether the factory inserts the user
     * @return void
     */
    public function test_run_user_factory()
    {
        (new UserSeeder())->run();

        $user = User::findOrFail(1);
        $this->assertEquals(1, $user->id);
    }

    /**
     * Test whether it's the right password
     * @return void
     */
    public function test_verify_user_password()
    {
        (new UserSeeder())->run();

        $user = User::findOrFail(1);

        $this->assertTrue(password_verify('secret', $user->password));
    }
}
