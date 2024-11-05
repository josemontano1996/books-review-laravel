<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * Get the reviews for the book.
     *
     * This function defines a one-to-many relationship between the Book model
     * and the Review model. It indicates that a book can have multiple reviews.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Scope a query to search books by title.
     *
     * @param Builder $query The query builder instance.
     * @param string $title The title to search for.
     * @return Builder The query builder instance with the applied condition.
     */
    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', '%' . $title . '%');
    }

    /**
     * Scope a query to only include popular books based on the number of reviews.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $from The start date for the date range filter (optional).
     * @param string|null $to The end date for the date range filter (optional).
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePopular(Builder $query, $from = null, $to = null): Builder
    {
        return $query->withCount([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ])->orderBy('reviews_count', 'desc');
    }

    /**
     * Scope a query to include books with the highest average rating.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $from The start date for filtering reviews (optional).
     * @param string|null $to The end date for filtering reviews (optional).
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighestRated(Builder $query, $from = null, $to = null): Builder
    {
        return $query->withAvg(
            ['reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)],
            'rating'
        )
            ->orderBy('reviews_avg_rating', 'desc');
    }

    /**
     * Scope a query to only include books with a minimum number of reviews.
     *
     * Need to call the popular method before in order to get the review_count row and access it
     * @param Builder $query The query builder instance.
     * @param int $minReviews The minimum number of reviews required.
     * @return Builder The modified query builder instance.
     */
    public function scopeMinReviews(Builder $query, int $minReviews): Builder
    {
        return $query->where('reviews_count', '>=', $minReviews);
    }
    /**
     * Apply a date range filter to the query.
     *
     * This method filters the query results based on the 'created_at' column.
     * It supports filtering by a start date, an end date, or both.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance.
     * @param string|null $from The start date for the filter (inclusive).
     * @param string|null $to The end date for the filter (inclusive).
     * @return void
     */

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


    /**
     * Scope a query to only include popular books from the last given number of months.
     *
     * @param Builder $query The query builder instance.
     * @param int $lastMonths The number of months to look back for popular books. Defaults to 1.
     * @return Builder The modified query builder instance.
     */
    public function scopePopularByLastMonths(Builder $query, int $lastMonths = 1): Builder
    {
        $lastMonths < 1 && $lastMonths = 1;

        return $query->popular(now()->subMonths($lastMonths), now())->highestRated(now()->subMonths($lastMonths), now())->minReviews(2);
    }
    /**
     * Scope a query to include the highest rated books within the last specified number of months.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $lastMonths The number of months to look back for the highest rated books. Defaults to 1 month.
     * @return \Illuminate\Database\Eloquent\Builder
     */
      public function scopeHighestRatedByLastMonths(Builder $query, int $lastMonths = 1): Builder
    {
        $lastMonths < 1 && $lastMonths = 1;

        return $query->highestRated(now()->subMonths($lastMonths), now())->popular(now()->subMonths($lastMonths), now())->minReviews(5);
    }


    /**
     * Scope a query to apply a filter based on the given filter string.
     *
     * @param Builder $query The query builder instance.
     * @param string|null $filter The filter string to apply. Possible values:
     *                            - 'popular_last_month': Filter by popular books in the last month.
     *                            - 'popular_last_6months': Filter by popular books in the last 6 months.
     *                            - 'highest_rated_last_month': Filter by highest rated books in the last month.
     *                            - 'highest_rated_last_6months': Filter by highest rated books in the last 6 months.
     *                            - null or any other value: No filter applied.
     * @return Builder The modified query builder instance.
     */
    public function scopeApplyFilter(Builder $query, string|null $filter): Builder
    {
        return match ($filter) {

            'popular_last_month' => $query->popularByLastMonths(),
            'popular_last_6months' => $query->popularByLastMonths(6),

            'highest_rated_last_month' => $query->highestRatedByLastMonths(1),
            'highest_rated_last_6months' => $query->highestRatedByLastMonths(6),
            default => $query,
        };
    }

}
