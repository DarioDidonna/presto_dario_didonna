<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:make-user-revisor {email}')]
#[Description('Rende un utente revisore')]
class MakeUserRevisor extends Command
{

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (!$user) {
            $this->error('Utente non trovato');
            return self::FAILURE;
        }
        $user->is_revisor = true;
        $user->save();
        $this->info("l'utente {$user->name} è ora revisore");
        return self::SUCCESS;
    }
}
