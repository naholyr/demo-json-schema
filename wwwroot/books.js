jQuery(function ($) {

  // Load list

  listBooks = (function () {
    var $list = $('#books-list');
    var $waiting = $('#books-list-waiting');
    var tpl = $('#book-template').text();
    return function doRefreshListBooks () {
      $waiting.show();
      $list.hide().empty();
      $.ajax({url: "/books", type: "GET"})
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
        });
    };
  })();

  listBooks();

  // Form

  (function () {
    var $form = $('#book-form');
    var tpl = $('#form-author-template').text();
    // Authors sub-forms
    $form.find('.new-author').on('click', function ($e) {
      $e.preventDefault();
      $form.find('.authors').append($(tpl));
    });
    $form.on('click', '.remove-author', function ($e) {
      $e.preventDefault();
      $($e.target).closest('.form-author').remove();
    });
    // Post on submit
    $form.on('submit', function ($e) {
      $e.preventDefault();
      var data = $form.serializeArray().reduce(function (data, field) {
        switch (field.name) {
          case 'title':
          case 'price':
          case 'isbn':
            data[field.name] = field.value;
            break;
          case 'authors[][name]':
          case 'authors[][birthdate]':
            if (!data.authors) data.authors = [];
            var fieldname = field.name.substring(10, field.name.length - 1);
            var i = data.authors.length - 1;
            if (!data.authors[i] || typeof data.authors[i][fieldname] !== 'undefined') data.authors[++i] = {};
            data.authors[i][fieldname] = field.value;
            break;
        }
        return data;
      }, {});
      $.ajax({url: "/books", type: "POST", data: JSON.stringify(data), contentType: "application/json"})
        .fail(function (err) { $form.find('.error').text(JSON.stringify(JSON.parse(err.responseText), null, 3)).show() })
        .done(function () {
          // Refresh list
          listBooks();
          // Reset form
          $form.find('.error').hide().empty();
          $form.find('.authors').empty();
          $form.find('input[type=text]').val('');
        })
    });
  })();

});
