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

        $books = cache()->remember($cacheKey, 3600, fn() => $books->get());

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
    //this route is reaching the database because of the route model binding,
    //for it to not reach the db and use cache, we need to pass the id in params
    //and not the book instance
    public function show(Book $book)
    {
        $cacheKey = 'book:' . $book->id;

        $book = cache()->remember($cacheKey, 3600, fn() => $book->load(['reviews' => fn($query) => $query->latest()]));
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
