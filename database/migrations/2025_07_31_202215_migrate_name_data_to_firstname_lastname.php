<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing name data to firstname/lastname
        $users = DB::table('users')->whereNull('firstname')->orWhereNull('lastname')->get();
        
        foreach ($users as $user) {
            if ($user->name && (!$user->firstname || !$user->lastname)) {
                $nameParts = explode(' ', trim($user->name), 2);
                $firstname = $nameParts[0] ?? '';
                $lastname = $nameParts[1] ?? '';
                
                // If lastname is empty, use the firstname as lastname too
                if (empty($lastname)) {
                    $lastname = $firstname;
                }
                
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'updated_at' => now()
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrate firstname/lastname back to name field
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            if ($user->firstname && $user->lastname) {
                $name = trim($user->firstname . ' ' . $user->lastname);
                
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'name' => $name,
                        'updated_at' => now()
                    ]);
            }
        }
    }
};
