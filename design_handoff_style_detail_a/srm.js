/* srm.js — Standard Reference Method (SRM) beer-color device.
   Maps an SRM value (~1–40) to an approximate sRGB hex, and provides helpers
   to build swatches, a min→max gradient, and a readable text color on top.
   SRM is the signature visual device for the styles pages. */
(function (g) {
  // Standard SRM 1..40 hex chart (Bryce/Druey approximation, widely used).
  var SRM = {
    1:'#FFE699',2:'#FFD878',3:'#FFCA5A',4:'#FFBF42',5:'#FBB123',6:'#F8A600',7:'#F39C00',
    8:'#EA8F00',9:'#E58500',10:'#DE7C00',11:'#D77200',12:'#CF6900',13:'#CB6200',14:'#C35900',
    15:'#BB5100',16:'#B54C00',17:'#B04500',18:'#A63E00',19:'#A13700',20:'#9B3200',21:'#952D00',
    22:'#8E2900',23:'#882300',24:'#821E00',25:'#7B1A00',26:'#771900',27:'#701400',28:'#6A0E00',
    29:'#660D00',30:'#5E0B00',31:'#5A0A02',32:'#560903',33:'#520907',34:'#4C0505',35:'#470606',
    36:'#440607',37:'#3F0708',38:'#3B0607',39:'#3A070B',40:'#360A0A'
  };
  function clamp(v, lo, hi) { return Math.max(lo, Math.min(hi, v)); }
  function srmHex(v) {
    if (v == null || isNaN(v)) return '#C8A86B';
    v = clamp(Math.round(v), 1, 40);
    return SRM[v] || (v < 1 ? SRM[1] : SRM[40]);
  }
  // perceived luminance → choose ink or paper for text sitting on the color
  function onColor(hex) {
    var c = hex.replace('#',''); if (c.length === 3) c = c.split('').map(function(x){return x+x;}).join('');
    var r = parseInt(c.substr(0,2),16), gg = parseInt(c.substr(2,2),16), b = parseInt(c.substr(4,2),16);
    var L = (0.2126*r + 0.7152*gg + 0.0722*b) / 255;
    return L > 0.62 ? '#1B1A17' : '#FAF7F2';
  }
  // CSS linear-gradient string spanning a min→max SRM range (light→dark)
  function srmGradient(min, max, angle) {
    angle = angle || '90deg';
    var steps = [], n = 5;
    for (var i = 0; i < n; i++) {
      var v = min + (max - min) * (i / (n - 1));
      steps.push(srmHex(v) + ' ' + Math.round(100 * i / (n - 1)) + '%');
    }
    return 'linear-gradient(' + angle + ',' + steps.join(',') + ')';
  }
  g.SRM = { hex: srmHex, onColor: onColor, gradient: srmGradient };
})(window);
