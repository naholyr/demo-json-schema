jQuery(function ($) {

  // JSON-Schema validation
  var validator = (function () {
    var schema = null; // Will be defined as soon as we know it
    var env = JSV.createEnvironment();
    return {
      setSchemaURL: function (url) {
        $.ajax({url: url, type: "GET", dataType: "json"})
          .done(function (_schema) { schema = _schema });
      },
      validate: function (object) {
        if (!schema) return true; // No schema, no validation
        // TODO
        var report = env.validate(object, schema);
        this.errors = report.errors;
        return this.errors.length === 0;
      },
      errors: null
    }
  })();


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
        .done(function (books, status, xhr) {
          // In headers, we'll find JSON-schema URL :)
          var link = xhr.getResponseHeader('Link');
          if (link) {
            link.split(/\s*,\s*/).some(function (link) {
              var m = link.match(/<?(.*?)>?\s*;\s*rel="?describedby"?/);
              if (m) {
                validator.setSchemaURL(m[1]);
                return true;
              }
            });
          }
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
      // Client-side validation
      if (!validator.validate(data)) {
        $form.find('.error').text(JSON.stringify(validator.errors, null, 3)).show();
        return;
      }
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
