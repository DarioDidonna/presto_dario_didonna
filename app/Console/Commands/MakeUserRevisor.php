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

    protected $signature = 'app.make-user-revisor {email}';
    protected $description = 'Rende un utente revisore';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (!$user) {
            $this->error('Utente non trovato');
            return;
        }
        $user->is_revisor = true;
        $user->save();
        $this->info("l'utente {$user->name} è ora revisore");
    }
}
