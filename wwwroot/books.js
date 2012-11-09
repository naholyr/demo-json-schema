jQuery(function ($) {

  // Load list

  listBooks = (function () {
    var $list = $('#books-list');
    var $waiting = $('#books-list-waiting');
    var tpl = $('#book-template').text();
    return function doRefreshListBooks () {
      $.ajax({ url: "/books", method: "GET" })
      .fail(function (err) { alert('OH FUCK !\n' + err); })
      .done(function (books) {
        console.log('Books list', books);
        // Yeah, modern browsers only, it's a demo man, live with it
        var html = books.map(function (book) {
          return tpl
            .replace(/\{\{title\}\}/g, book.title)
            .replace(/\{\{price\}\}/g, book.price)
            .replace(/\{\{authors\}\}/g, book.authors.map(function (author) { return author.name }).join(', '))
        });
        $list.append($(html.join('')));
        $waiting.hide();
        $list.show();
      })
    };
  })();

  listBooks();
});
