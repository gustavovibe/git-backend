<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactEmail extends Model
{
    use HasFactory;
    protected $table = 'contact_emails';
    protected $fillable= [
        'sent',
        'message',
        'mail_from',
        'order',
        'subject',
        'link'
    ];
    protected $hidden = ['created_at', 'updated_at'];
}
