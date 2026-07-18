<?php
// app/Jobs/ProcessSecurityAnswersJob.php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class ProcessSecurityAnswersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $answer1;
    protected $answer2;
    protected $answer3;
    protected $questions;

    public function __construct(User $user, $questions, $answer1, $answer2, $answer3)
    {
        $this->user = $user;
        $this->questions = $questions;
        $this->answer1 = $answer1;
        $this->answer2 = $answer2;
        $this->answer3 = $answer3;
    }

    public function handle()
    {
        // Procesar los hashes en segundo plano
        $this->user->update([
            'security_questions' => json_encode($this->questions, JSON_UNESCAPED_UNICODE),
            'security_answer_1' => Hash::make($this->answer1),
            'security_answer_2' => Hash::make($this->answer2),
            'security_answer_3' => Hash::make($this->answer3),
            'security_questions_set_at' => now(),
        ]);
    }
}
