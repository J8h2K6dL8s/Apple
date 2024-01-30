<?php

namespace App\Jobs;

use App\Models\Variante;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class VarianteImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $varianteId;
    protected $imageName;

    /**
     * Create a new job instance.
     */
    public function __construct($varianteId,$imageName)
    {
        $this->varianteId = $varianteId;
        $this->imageName = $imageName;
    }

    /**
     * Execute the job.
     */

    
    
    public function handle()
    {
        DB::table('variante_images')->insert([
            'variante_id' => $this->varianteId,
            'chemin_image' => $this->imageName,
        ]);
    }

   
}
