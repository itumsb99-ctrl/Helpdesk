<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number','title','description','priority','status','location','unit',
        'category_id','unit_id','user_id','technician_id','response_due_at','resolution_due_at',
        'first_response_at','resolved_at','closed_at','escalated_at'
    ];
    protected function casts(): array
    {
        return [
            'response_due_at'=>'datetime','resolution_due_at'=>'datetime',
            'response_warning_sent_at'=>'datetime','resolution_warning_sent_at'=>'datetime',
            'first_response_at'=>'datetime','resolved_at'=>'datetime',
            'closed_at'=>'datetime','escalated_at'=>'datetime',
        ];
    }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function hospitalUnit(): BelongsTo { return $this->belongsTo(Unit::class, 'unit_id'); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function technician(): BelongsTo { return $this->belongsTo(User::class, 'technician_id'); }
    public function comments(): HasMany { return $this->hasMany(TicketComment::class); }
    public function histories(): HasMany { return $this->hasMany(TicketHistory::class); }
    public function isOverdue(): bool
    {
        return !in_array($this->status, ['resolved','closed'], true)
            && $this->resolution_due_at && now()->greaterThan($this->resolution_due_at);
    }
    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
