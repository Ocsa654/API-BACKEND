<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    // Define the table name if it's different from the plural form of the model name
    protected $table = 'songs';

    // Fillable fields based on the interface from the React component
    protected $fillable = [
        'title',        // Song title
        'artist',       // Artist name
        'album',        // Album name
        'duration',     // Song duration in seconds
        'cover_art_url' // URL of the cover art
    ];

    // Optional: if you want to handle type casting
    protected $casts = [
        'duration' => 'integer',
    ];
}
