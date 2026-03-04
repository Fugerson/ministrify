<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCellNote extends Model
{
    protected $fillable = ['church_id', 'event_id', 'role_type', 'role_id', 'notes'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}
