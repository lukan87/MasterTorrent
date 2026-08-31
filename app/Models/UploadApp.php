<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadApp extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'uploadapps';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'applicant_id',
        'staff_id',
        'internal_speed',
        'external_speed',
        'why_promoted',
        'external_sites',
        'scene_access',
        'know_torrents',
        'understand_seeding',
        'status',
    ];

    /**
     * Relationships
     */

    // Relationship to the user who applied
    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    // Relationship to the staff who reviewed the application
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
