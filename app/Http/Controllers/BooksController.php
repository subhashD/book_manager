<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Book;
use DB;

class BooksController extends Controller
{
    private function getBookQueryBackup(Request $request)
    {
        $query = Book::orderBy('download_count', 'desc')
                    ->with(['authors', 'shelves', 'subjects', 'languages', 'formats']);
        
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
                    if(is_array($request->topic)) {
                        foreach($request->topic as $tp_key => $topic)
                        {
                            if($tp_key === 0)
                                $qt->where('name', 'LIKE', '%'.$topic);     
                            else
                                $qt->orWhere('name', 'LIKE', '%'.$topic);     
                        }
                    } else {
                        $qt->where('name', 'LIKE', '%'.$request->topic);
                    }
                })->orWhereHas('subjects', function($qt) use ($request) {
                    if(is_array($request->topic)) {
                        foreach($request->topic as $tp_key => $topic)
                        {
                            if($tp_key === 0)
                                $qt->where('name', 'LIKE', '%'.$topic);     
                            else
                                $qt->orWhere('name', 'LIKE', '%'.$topic);     
                        }
                    } else {
                        $qt->where('name', 'LIKE', '%'.$request->topic);
                    }
                });
            // });
        }
        
        if($request->has('author')) {
            $query->whereHas('authors', function($q) use ($request) {
                if(is_array($request->author)) {
                    foreach($request->author as $a_key => $author)
                    {
                        if($a_key === 0)
                            $q->where('name', 'LIKE', '%'.$author);     
                        else
                            $q->orWhere('name', 'LIKE', '%'.$author);     
                    }
                } else {
                    $q->where('name', 'LIKE', '%'.$request->author);
                }
            });
        }
        
        if($request->has('title')) {
            if(is_array($request->title)) {
                foreach($request->title as $t_key => $title)
                {
                    if($t_key === 0)
                        $query->where('title', 'LIKE', '%'.$title);     
                    else
                        $query->orWhere('title', 'LIKE', '%'.$title);     
                }
            } else {
                $query->where('title', 'LIKE', '%'.$request->title); 
            }
        }

        return $query;
    }
 
    public function filter(Request $request) 
    {
        $query = $this->getBookQueryBackup($request);
        $totalCount = $query->count();

        if($request->has('limit'))
            $query->limit($request->limit);
        
        if($request->has('offset'))
            $query->offset($request->offset);
        
        $results = $query->get();
        return response()->json(['totalCount' => $totalCount, 'results' => $results]);
    }
}
