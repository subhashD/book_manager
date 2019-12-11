## JSON Filters
Option 1:
{
	"book_id":[1721, 2461],
	"language":["en", "fr"],
	"title":["Pride"],
	"topic":["child"],
	"author":["Gilbert"],
	"mime_type":["text/html; charset=utf-8"],
	"offset":0,
	"limit":25
}

Option 2:
{
	"book_id":[1721, 2461],
	"language":["en", "fr"],
	"title":"Pride",
	"topic":"child",
	"author":"Gilbert",
	"mime_type":["text/html; charset=utf-8"],
	"offset":0,
	"limit":25
}