<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance.
     * @return \Illuminate\View\View The view displaying the list of books.
     */
    public function index(Request $request)
    {
        $title = $request->input('title');
        $filter = $request->input('filter', '');

        $books = Book::when(
            $title,
            fn($query) => $query->title($title)
        )->applyFilter($filter);


        $cacheKey = 'books:' . $filter . ':' . $title;

        /*   $books =  cache()->remember(
              $cacheKey,
              3600,
              fn() =>
              $books->get()
           ); */

        $books = $books->paginate(10);


        return view(
            'books.index',
            ['books' => $books]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\View\View
     */

    public function show(int $id)
    {
        $cacheKey = 'book:' . $id;

        $book = cache()->remember(
            $cacheKey,
            3600,
            fn() =>
            Book::with(['reviews' => fn($query) => $query->latest()])
                ->withAvgRating()->withReviewsCount()->findOrfail($id)
        );

        return view(
            'books.show',
            ['book' => $book]
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
