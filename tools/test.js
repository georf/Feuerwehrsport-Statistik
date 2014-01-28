(function() {
  var EinGuterTest, list, number, opposite, square;

  EinGuterTest = (function() {
    function EinGuterTest() {}

    EinGuterTest.foo = function() {
      return "bar";
    };

    return EinGuterTest;

  })();

  number = 42;

  opposite = true;

  if (opposite) {
    number = -42;
  }

  square = function(x) {
    return x * x;
  };

  list = [1, 2, 3, 4, 5];

  new EinGuterTest();

}).call(this);
