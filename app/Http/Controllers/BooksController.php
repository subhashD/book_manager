<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Book;
use DB;

class BooksController extends Controller
{
    private function getBookQueryBackup(Request $request)
    {
        $query = Book::/* orderBy('download_count', 'desc')
                    -> */with(['authors', 'shelves', 'subjects', 'languages', 'formats']);
        
        if($request->has('book_id'))
            $query->whereIn('gutenberg_id', $request->book_id);
        
        if($request->has('language'))
            $query->whereHas('languages', function($q) use ($request) {
                $q->whereIn('code', $request->language);
            });
        
        if($request->has('mime_type')) {
            $query->whereHas('formats', function($q) use ($request) {
                $q->whereIn('mime_type', $request->mime_type);
            });
            
        }

        if($request->has('topic')) {
            // $query->where(function($q) use ($request) {
                $query->orWhereHas('shelves', function($qt) use ($request) {
                    foreach($request->topic as $topic)
                    {
                        $qt->orWhere('name', 'LIKE', '%'.$topic);     
                    }
                })->orWhereHas('subjects', function($qt) use ($request) {
                    foreach($request->topic as $topic)
                    {
                        $qt->orWhere('name', 'LIKE', '%'.$topic);     
                    }
                });
            // });
        }
        
        if($request->has('author')) {
            $query->whereHas('authors', function($q) use ($request) {
                foreach($request->author as $author)
                {
                    $q->orWhere('name', 'LIKE', '%'.$author);     
                }
            });
        }
        
        if($request->has('title')) {
            foreach($request->title as $title)
            {
                $query->orWhere('title', 'LIKE', '%'.$title);     
            }
        }

        return $query;
    }
    
    private function getBookQuery(Request $request)
    {
        $query = Book::select("books_book.*", 
                            DB::raw("(select books_author.birth_year", "books_author.death_year","books_author.name as author_name) as authors"), 
                            DB::raw("(select books_bookshelf.name as book_shelf) as shevles"), 
                            DB::raw("(select books_subject.name as book_subject) as subjects"), 
                            DB::raw("(select books_language.code as book_language) as language"), 
                            DB::raw("(select books_format.mime_type", "books_format.url) as formats") 
                        )
            ->join("books_book_authors", "books_book.id", "=", "books_book_authors.book_id")
            ->join("books_author", "books_author.id", "=", "books_book_authors.author_id")
            ->join("books_book_bookshelves", "books_book.id", "=", "books_book_bookshelves.book_id")
            ->join("books_bookshelf", "books_bookshelf.id", "=", "books_book_bookshelves.bookshelf_id")
            ->join("books_book_subjects", "books_book.id", "=", "books_book_subjects.book_id")
            ->join("books_subject", "books_subject.id", "=", "books_book_subjects.subject_id")
            ->join("books_book_languages", "books_book.id", "=", "books_book_languages.book_id")
            ->join("books_language", "books_language.id", "=", "books_book_languages.language_id")
            ->join("books_format",function($join){
                $join->on("books_format.book_id","=","books_book.id");
            });
        
        if($request->has('book_id'))
            $query->whereIn('books_book.gutenberg_id', $request->book_id);
        
        if($request->has('language'))
            $query->whereIn('books_language.code', $request->language);
        
        if($request->has('mime_type'))
            $query->whereIn('books_format.mime_type', $request->mime_type);

        if($request->has('topic')) {
            $query->where(function($q) use ($request) {
                foreach($request->topic as $topic)
                {
                    $q->orWhere('books_subject.name', 'LIKE', '%'.$topic.'%')
                    ->orWhere('books_bookshelf.name', 'LIKE', '%'.$topic.'%');
                }
            });
        }
        
        if($request->has('author')) {
            foreach($request->author as $author)
            {
                $query->orWhere('books_author.name', 'LIKE', '%'.$author);     
            }
        }
        
        if($request->has('title')) {
            foreach($request->title as $title)
            {
                $query->orWhere('books_book.title', 'LIKE', '%'.$title);     
            }
        }

        $query->orderBy('books_book.download_count', 'DESC');
        // $query->groupBy('books_book.id');
        return $query;
    }

    public function filter(Request $request) 
    {
        $query = $this->getBookQuery($request);
        $totalCount = $query->count();

        if($request->has('limit'))
            $query->limit($request->limit);
        
        if($request->has('offset'))
            $query->offset($request->offset);
        
        $results = $query->get();
        return response()->json(['results' => $results, 'totalCount' => $totalCount]);
    }
}
