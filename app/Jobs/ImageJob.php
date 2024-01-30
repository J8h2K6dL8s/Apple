<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $produitId;
    protected $imageName;

    /**
     * Create a new job instance.
     */
    public function __construct($produitId,$imageName)
    {
        $this->imageName = $imageName;
        $this->produitId = $produitId;
    }

    /**
     * Execute the job.
     */
    
    public function handle()
    {
        DB::table('images')->insert([
            'produit_id' => $this->produitId,
            'chemin_image' => $this->imageName,
        ]);
    }
}
