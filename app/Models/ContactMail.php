<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactEmail extends Model
{
    use HasFactory;
    protected $table = 'contact_emails';
    protected $fillable= [
        'link',
        'order',
        'mail_from',
        'mail_type',
        'message',
        'status',
    ];
    protected $hidden = ['created_at', 'updated_at'];

    public function traveler(){
        return $this->hasOne(Traveler::class,'mail','mail_from');
    }
}
