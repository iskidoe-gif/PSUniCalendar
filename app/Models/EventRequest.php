<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'title',
        'venue_name',
        'campus',
        'description',
        'start_datetime',
        'end_datetime',
        'status',
        'digital_documents',
    ];

    protected $casts = [
        'digital_documents' => 'array',
    ];
}
