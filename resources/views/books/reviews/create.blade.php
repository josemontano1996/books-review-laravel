@extends('layouts.app')

@section('content')
    <h1 class='mb-10 text-2xl'> Add Review for {{ $book->title }} </h1>
    <form action="{{ route('books.reviews.store', $book) }}" method="POST">

        @csrf
        <div class="mb-4">
            <label for="review">Review</label>

            <textarea name="review" id="review" required class="input"></textarea>
            @error('review')
                <p class="text-red-500 text-sm ">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="rating">Rating</label>
            <select name="rating" id="rating" class="input mb-4" required>
                <option value="">Select a rating</option>
                @for ($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
            @error('rating')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <button class="btn">Post review</button>
    </form>
@endsection
