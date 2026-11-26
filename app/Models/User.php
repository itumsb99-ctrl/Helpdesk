<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','password','role','unit','is_active'];
    protected $hidden = ['password','remember_token'];
    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];
    }
    public function createdTickets(): HasMany { return $this->hasMany(Ticket::class, 'user_id'); }
    public function assignedTickets(): HasMany { return $this->hasMany(Ticket::class, 'technician_id'); }
    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(
            TicketAttachment::class,
            'uploaded_by'
    );
    }
}
