<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactEmail extends Model
{
    use HasFactory;
    protected $table = 'contact_emails';
    protected $fillable= [
        'mail_from',
        'mail_type',
        'message',
        'link',
        'order',
        'status',
    ];
    protected $hidden = [ 'updated_at'];
}
