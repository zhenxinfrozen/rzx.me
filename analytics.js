// Google Analytics
(function(i, s, o, g, r, a, m) {
  i['GoogleAnalyticsObject'] = r;
  i[r] = i[r] || function() {
    (i[r].q = i[r].q || []).push(arguments);
  };
  i[r].l = 1 * new Date();
  a = s.createElement(o);
  m = s.getElementsByTagName(o)[0];
  a.async = 1;
  a.src = g;
  m.parentNode.insertBefore(a, m);
})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

ga('create', 'UA-18450539-1', 'rzx.me');
ga('send', 'pageview');

// Tencent Analytics
(function() {
  var s = document.createElement('script');
  s.src = 'http://tajs.qq.com/stats?sId=21583056';
  s.charset = 'UTF-8';
  s.async = true;
  document.head.appendChild(s);
})();
