<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'books_book';

    public $timestamps = false;

    public function authors() {
        return $this->belongsToMany('App\Author', 'books_book_authors', 'book_id', 'author_id');
    }
        
    public function shelves() {
        return $this->belongsToMany('App\Bookshelf', 'books_book_bookshelves', 'book_id', 'bookshelf_id');
    }

    public function languages() {
        return $this->belongsToMany('App\Language', 'books_book_languages', 'book_id', 'language_id');
    }
    
    public function subjects() {
        return $this->belongsToMany('App\Subject', 'books_book_subjects', 'book_id', 'subject_id');
    }
    
    public function formats() {
        return $this->hasMany('App\Format', 'book_id');
    }
}
