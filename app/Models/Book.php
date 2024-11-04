<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', '%' . $title . '%');
    }

    public function scopePopular(Builder $query, $from = null, $to = null): Builder
    {
        return $query->withCount([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ])->orderBy('reviews_count', 'desc');
    }

    public function scopeHighestRated(Builder $query, $from = null, $to = null): Builder
    {
        return $query->withAvg(
            ['reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)],
            'rating'
        )
            ->orderBy('reviews_avg_rating', 'desc');
    }

    //need to call the popular method before in order to get the review_count row and access it
    public function scopeMinReviews(Builder $query, int $minReviews): Builder
    {
        return $query->where('reviews_count', '>=', $minReviews);
    }

    //no need to return anything because $query is an object, and objects are passsed by reference to functions
    private function dateRangeFilter(Builder $query, $from = null, $to = null)
    {
        if ($from && !$to) {
            $query->where('created_at', '>=', $from);
        } else if (!$from && $to) {
            $query->where('created_at', '<=', $to);
        } else if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    }

    public function scopePopularLastMonth(Builder $query): Builder
    {
        return $query->popular(now()->subMonth(), now())->highestRated(now()->subMonth(), now())->minReviews(2);
    }
    public function scopePopularLast6Months(Builder $query): Builder
    {
        return $query->popular(now()->subMonths(6), now())->highestRated(now()->subMonths(6), now())->minReviews(5);
    }
    public function scopeHighestRatedLastMonth(Builder $query): Builder
    {
        return $query->highestRated(now()->subMonth(), now())->popular(now()->subMonth(), now())->minReviews(2);
    }
    public function scopeHighestRatedLast6Months(Builder $query): Builder
    {
        return $query->highestRated(now()->subMonths(6), now())->popular(now()->subMonths(6), now())->minReviews(5);
    }
}
