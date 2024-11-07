<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'review',
        'rating'
    ];
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    /**
     * The "booted" method is automatically called when the model is booted.
     *
     * This method registers two model event listeners:
     *
     * 1. When a review is updated, it forgets the cache entry for the associated book.
     * 2. When a review is deleted, it forgets the cache entry for the associated book.
     *
     * @return void
     */

    protected static function booted()
    {
        static::created(fn(Review $review) => cache()->forget('book:' . $review->book_id));
        static::updated(fn(Review $review) => cache()->forget('book:' . $review->book_id));
        static::deleted(fn(Review $review) => cache()->forget('book:' . $review->book_id));
    }
}
