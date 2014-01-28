(function() {
  var AlertFssWindow, ConfirmFssWindow, EventHandler, FssFormRow, FssWindow, WaitFssWindow, WariningFssWindow,
    __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    __slice = [].slice;

  EventHandler = (function() {
    function EventHandler() {
      this.handlers = {};
    }

    EventHandler.prototype.on = function(type, callback) {
      var _base;
      (_base = this.handlers)[type] || (_base[type] = []);
      this.handlers[type].push(callback);
      return this;
    };

    EventHandler.prototype.off = function(type, callback) {
      var i, method, _base, _i, _len, _ref;
      (_base = this.handlers)[type] || (_base[type] = []);
      _ref = this.handlers[type];
      for (i = _i = 0, _len = _ref.length; _i < _len; i = ++_i) {
        method = _ref[i];
        if (method === callback) {
          this.handlers.splice(i, 1);
          return this;
        }
      }
      return this;
    };

    EventHandler.prototype.fire = function(type, data) {
      var method, _base, _i, _len, _ref;
      (_base = this.handlers)[type] || (_base[type] = []);
      _ref = this.handlers[type];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        method = _ref[_i];
        method.call(this, data);
      }
      return this;
    };

    return EventHandler;

  })();

  FssWindow = (function(_super) {
    __extends(FssWindow, _super);

    function FssWindow(title) {
      var _this = this;
      this.title = title;
      this.data = __bind(this.data, this);
      this.close = __bind(this.close, this);
      this.open = __bind(this.open, this);
      this.add = __bind(this.add, this);
      this.render = __bind(this.render, this);
      FssWindow.__super__.constructor.apply(this, arguments);
      this.rows = [];
      this.rendered = false;
      this.on('pre-submit', function() {
        return _this.fire('submit', _this.data());
      });
    }

    FssWindow.prototype.render = function() {
      var cancel, form, row, submit, _i, _len, _ref,
        _this = this;
      this.container = $('<div/>').addClass('fss-window').append($('<div/>').addClass('fss-window-title').text(this.title));
      this.darkroom = $('<div/>').addClass('darkroom');
      if ((this.handlers['submit'] != null) && this.handlers['submit'].length > 0) {
        submit = $('<a/>').addClass('class').text('OK').on('click', function(e) {
          e.preventDefault();
          return _this.fire('pre-submit');
        });
        cancel = $('<button/>').text('Abbrechen').on('click', function(e) {
          e.preventDefault();
          return _this.fire('cancel');
        });
        this.add(new FssFormRow(submit, cancel));
      }
      form = $('<form/>').on('submit', function(e) {
        e.preventDefault();
        return _this.fire('pre-submit');
      });
      _ref = this.rows;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        row = _ref[_i];
        form.append(row.content());
      }
      this.container.append(form);
      this.rendered = true;
      return this;
    };

    FssWindow.prototype.add = function(row) {
      return this.rows.push(row);
    };

    FssWindow.prototype.open = function() {
      var left, row, top, _i, _len, _ref;
      if (!this.rendered) {
        this.render();
      }
      $('body').append(this.darkroom).append(this.container);
      left = window.innerWidth / 2 - parseInt(this.container.css('width')) / 2;
      top = window.innerHeight / 2 - parseInt(this.container.css('height')) / 2;
      if (left < 10) {
        left = 10;
      }
      if (top < 10) {
        top = 10;
      }
      top += parseInt($(document).scrollTop());
      this.container.css('top', top).css('left', left);
      _ref = this.rows;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        row = _ref[_i];
        if (row.focus()) {
          break;
        }
      }
      return this;
    };

    FssWindow.prototype.close = function() {
      this.container.remove();
      return this.darkroom.remove();
    };

    FssWindow.prototype.data = function() {
      var data, row, _i, _len, _ref;
      data = {};
      _ref = this.row;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        row = _ref[_i];
        data = row.appendData(data);
      }
      return data;
    };

    return FssWindow;

  })(EventHandler);

  FssFormRow = (function(_super) {
    __extends(FssFormRow, _super);

    function FssFormRow() {
      var fields;
      fields = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
      this.fields = fields;
      this.content = __bind(this.content, this);
      this.appendData = __bind(this.appendData, this);
      FssFormRow.__super__.constructor.apply(this, arguments);
    }

    FssFormRow.prototype.focus = function() {
      return false;
    };

    FssFormRow.prototype.appendData = function(data) {
      return data;
    };

    FssFormRow.prototype.content = function() {
      var container, field, _i, _len, _ref;
      container = $('<div/>').addClass('fss-form-row');
      _ref = this.fields;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        field = _ref[_i];
        container.append(field);
      }
      return container;
    };

    return FssFormRow;

  })(EventHandler);

  WariningFssWindow = (function(_super) {
    __extends(WariningFssWindow, _super);

    function WariningFssWindow(title) {
      var _this = this;
      WariningFssWindow.__super__.constructor.call(this, title);
      this.add(new FssFormRow($('<div/>').addClass("warning-fss-window")));
      this.add(new FssFormRow($('<button/>').text('OK').on('click', function(e) {
        e.preventDefault();
        return _this.close();
      })));
      this.open();
    }

    return WariningFssWindow;

  })(FssWindow);

  WaitFssWindow = (function(_super) {
    __extends(WaitFssWindow, _super);

    function WaitFssWindow() {
      WaitFssWindow.__super__.constructor.call(this, "Bitte warten");
      this.add(new FssFormRow($('<div/>').addClass("wait-fss-window")));
      this.open();
    }

    return WaitFssWindow;

  })(FssWindow);

  ConfirmFssWindow = (function(_super) {
    __extends(ConfirmFssWindow, _super);

    function ConfirmFssWindow(title, message, submit, cancel) {
      if (cancel == null) {
        cancel = false;
      }
      ConfirmFssWindow.__super__.constructor.call(this, title);
      this.add(new FssFormRow($('<p/>').text(message)));
      this.on('submit', submit);
      if (cancel) {
        this.on('cancel', cancel);
      }
      this.open();
    }

    return ConfirmFssWindow;

  })(FssWindow);

  AlertFssWindow = (function(_super) {
    __extends(AlertFssWindow, _super);

    function AlertFssWindow(title, message) {
      var _this = this;
      AlertFssWindow.__super__.constructor.call(this, title);
      this.add(new FssFormRow($('<p/>').text(message)));
      this.add(new FssFormRow($('<button/>').text('OK').on('click', function(e) {
        e.preventDefault();
        return _this.close();
      })));
      this.open();
    }

    return AlertFssWindow;

  })(FssWindow);

  new ConfirmFssWindow('title', 'message', console.log);

}).call(this);
