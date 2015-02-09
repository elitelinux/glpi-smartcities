/*
 I put into this script everything I thought would be remotely confusing.
 If you understood everything, I recommend you get some help, immediately.
 Copyright (C) 2004 PatD Productions
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2, or (at your option)
 any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 http://www.fsf.org/licenses/info/GPLv2.html
 http://www.fsf.org/licenses/info/GPLv2orLater.html
 */
var FF, FM, FXI, FXA, FYI, FYA;
var PicW = new Number(320);
var PicH = new Number(200);
var PicData = new Picture();
var PlotType = false;

var Xi = new Number(0);
var Xa = new Number(0);
var Yi = new Number(0);
var Ya = new Number(0);

var Muster = new Array(32);
Muster[0] = "   #";
Muster[1] = "    ";
Muster[2] = " #  ";
Muster[3] = "    ";
Muster[4] = "  # ";
Muster[5] = "#   ";
Muster[6] = "   #";
Muster[7] = " #  ";
Muster[8] = "#   ";
Muster[9] = "  ##";
Muster[10] = "##  ";
Muster[11] = "   #";
Muster[12] = "  ##";
Muster[13] = "##  ";
Muster[14] = "  ##";
Muster[15] = "##  ";
Muster[16] = "### ";
Muster[17] = " # #";
Muster[18] = "# # ";
Muster[19] = " ###";
Muster[20] = " ###";
Muster[21] = "## #";
Muster[22] = " ###";
Muster[23] = "## #";
Muster[24] = "####";
Muster[25] = "## #";
Muster[26] = "####";
Muster[27] = "# ##";
Muster[28] = "####";
Muster[29] = "####";
Muster[30] = "####";
Muster[31] = "####";

for (i = 0; i < 32; i++) 
  for (j = 0; j < 8; j++) 
    Muster[i] += Muster[i];

var Precedence = new Array();
Precedence['sin'] = 16;
Precedence['cos'] = 16;
Precedence['tan'] = 16;
Precedence['sec'] = 16;
Precedence['csc'] = 16;
Precedence['cot'] = 16;
Precedence['asin'] = 16;
Precedence['acos'] = 16;
Precedence['atan'] = 16;
Precedence['asec'] = 16;
Precedence['acsc'] = 16;
Precedence['acot'] = 16;
Precedence['sinh'] = 16;
Precedence['cosh'] = 16;
Precedence['tanh'] = 16;
Precedence['sech'] = 16;
Precedence['csch'] = 16;
Precedence['coth'] = 16;
Precedence['asinh'] = 16;
Precedence['acosh'] = 16;
Precedence['atanh'] = 16;
Precedence['asech'] = 16;
Precedence['acsch'] = 16;
Precedence['acoth'] = 16;
Precedence['exp'] = 16;
Precedence['ln'] = 16;
Precedence['log'] = 16;
Precedence['logb'] = 16;
Precedence['pow'] = 16;
Precedence['sq'] = 16;
Precedence['sqrt'] = 16;
Precedence['abs'] = 16;
Precedence['sgn'] = 16;
Precedence['chs'] = 16;
Precedence['floor'] = 16;
Precedence['ceil'] = 16;
Precedence['ip'] = 16;
Precedence['fp'] = 16;
Precedence['inv'] = 16;
Precedence['min'] = 16;
Precedence['max'] = 16;
Precedence['gamma'] = 16;
Precedence['fact'] = 16;
Precedence['comb'] = 16;
Precedence['perm'] = 16;
Precedence['rand'] = 16;
Precedence['gcd'] = 16;
Precedence['lcm'] = 16;
Precedence['prime'] = 16;
Precedence['and'] = 16;
Precedence['or'] = 16;
Precedence['xor'] = 16;
Precedence['x'] = 16;
Precedence['y'] = 16;
Precedence['pi'] = 16;
Precedence['e'] = 16;
Precedence['neg'] = 15;
Precedence['not'] = 15;
Precedence['^'] = 14;
Precedence['*'] = 13;
Precedence['/'] = 13;
Precedence['mod'] = 13;
Precedence['div'] = 13;
Precedence['+'] = 12;
Precedence['-'] = 12;
Precedence['<'] = 10;
Precedence['<='] = 10;
Precedence['=='] = 10;
Precedence['!='] = 10;
Precedence['>='] = 10;
Precedence['>'] = 10;
Precedence['&&'] = 5;
Precedence['||'] = 4;
Precedence[','] = 1;

var Expects = new Array();
Expects['sin'] = 1;
Expects['cos'] = 1;
Expects['tan'] = 1;
Expects['sec'] = 1;
Expects['csc'] = 1;
Expects['cot'] = 1;
Expects['asin'] = 1;
Expects['acos'] = 1;
Expects['atan'] = 1;
Expects['asec'] = 1;
Expects['acsc'] = 1;
Expects['acot'] = 1;
Expects['sinh'] = 1;
Expects['cosh'] = 1;
Expects['tanh'] = 1;
Expects['sech'] = 1;
Expects['csch'] = 1;
Expects['coth'] = 1;
Expects['asinh'] = 1;
Expects['acosh'] = 1;
Expects['atanh'] = 1;
Expects['asech'] = 1;
Expects['acsch'] = 1;
Expects['acoth'] = 1;
Expects['exp'] = 1;
Expects['ln'] = 1;
Expects['log'] = 1;
Expects['logb'] = 2;
Expects['pow'] = 2;
Expects['sq'] = 1;
Expects['sqrt'] = 1;
Expects['abs'] = 1;
Expects['sgn'] = 1;
Expects['chs'] = 1;
Expects['floor'] = 1;
Expects['ceil'] = 1;
Expects['ip'] = 1;
Expects['fp'] = 1;
Expects['inv'] = 1;
Expects['min'] = 2;
Expects['max'] = 2;
Expects['gamma'] = 1;
Expects['fact'] = 1;
Expects['comb'] = 2;
Expects['perm'] = 2;
Expects['gcd'] = 2;
Expects['lcm'] = 2;
Expects['prime'] = 1;
Expects['and'] = 2;
Expects['or'] = 2;
Expects['xor'] = 2;
Expects['neg'] = 1;
Expects['not'] = 1;
Expects['^'] = 2;
Expects['*'] = 2;
Expects['/'] = 2;
Expects['mod'] = 2;
Expects['div'] = 2;
Expects['+'] = 2;
Expects['-'] = 2;
Expects['<'] = 2;
Expects['<='] = 2;
Expects['=='] = 2;
Expects['!='] = 2;
Expects['>='] = 2;
Expects['>'] = 2;
Expects['&&'] = 2;
Expects['||'] = 2;

function Implied(token) {
  if (token == 'neg') return false;
  return /^(\d+\.\d+|\d+\.?|\.\d+)(e[-+]?\d+)?|\w+$/.test(token);
}

function Tokenize(e) {
  var tmp;
  var result = new Array();
  var previousToken = new String('');
  var r = /(\d+\.\d+|\d+\.?|\.\d+)(e[-+]?\d+)?|[xy]|[a-z]+|[-+*\/(),^]|<=?|>=?|==|!=|&&|\|\|/g;
  
  e = e.toLowerCase();
  e = e.replace(/\s/g, '');
  e = e.replace(/\+\+/g, '+').replace(/\+-/g, '-').replace(/-\+/g, '-').replace(/--/g, '+');
  e = e.replace(/^\+/, '').replace(/([(*\/&\|<>=])\+/g, '$1')
  e = e.replace(/^-/, 'neg ').replace(/([(*\/&\|<>=])-/g, '$1neg ')
  e = e.replace(/\)(\(|\w)/g, ")*$1");
  e = e.replace(/(\d)\(/g, "$1*(");
  while (tmp = r.exec(e)) {
    if (Implied(previousToken) && Implied(tmp[0].toString())) result.push('*');
    result.push(previousToken = tmp[0]);
  }
  return result;
}

function IsValidEnd(token) {
  return /^(\d+\.\d+|\d+\.?|\.\d+)(e[-+]?\d+)?|[)xye]|pi|rand$/.test(token);
}

function ToPostfix(infix) {
  var token = new String();
  var previousToken = new String("");
  var stack = new Array();
  var postfix = new Array();
  
  while (token = infix.shift()) {
    if (token == "(") stack.push(token);
    else if (Precedence[token]) {
      while (stack.length && stack[stack.length - 1] != "(" && Precedence[stack[stack.length - 1]] >= Precedence[token]) 
        postfix.push(stack.pop());
      if (token == "," && !IsValidEnd(previousToken)) postfix.push("Unexpected: " + previousToken);
      if (token != ",") stack.push(token);
    } else if (token == ")") {
      if (!IsValidEnd(previousToken)) postfix.push("Unexpected: " + previousToken);
      while (stack.length && stack[stack.length - 1] != "(") 
        postfix.push(stack.pop());
      if (stack.length == 0) postfix.push("Unmatched: )");
      stack.pop(); // discard the "("
    } else if (/^[-+]?(\d+\.?|\d*\.\d+)(e[-+]?\d+)?$/.test(token)) postfix.push(token);
    else postfix.push("Unknown: " + token);
    previousToken = token;
  }
  if (!IsValidEnd(previousToken)) postfix.push("Unexpected: " + previousToken);
  while (token = stack.pop()) {
    if (token == "(") postfix.push("Unmatched: (");
    postfix.push(token);
  }
  return postfix;
}

function SyntaxCheck(postfix) {
  var stack = new Number(0);
  var token = new String();
  
  for (var i = 0; i < postfix.length; i++, stack++) {
    if (/^Un(matched|known|expected).*/.test(token = postfix[i])) return token;
    if (Expects[token]) {
      if (stack < Expects[token]) return "Invalid use of: " + token;
      stack -= Expects[token];
    }
  }
  return stack == 1 ? "Passed" : (postfix.length ? "Unused arguments" : "No function");
}

function FillAreaStandard(x1, y1, x2, y2, x3, y3, x4, y4) {
  var x = 0;
  var y = 0;
  var lp = new Array();
  var rp = new Array();
  
  x1 = parseInt(x1);
  y1 = parseInt(y1);
  x2 = parseInt(x2);
  y2 = parseInt(y2);
  x3 = parseInt(x3);
  y3 = parseInt(y3);
  x4 = parseInt(x4);
  y4 = parseInt(y4);
  
  Scan(x1, y1, x2, y2, lp, rp);
  Scan(x2, y2, x3, y3, lp, rp);
  Scan(x3, y3, x4, y4, lp, rp);
  Scan(x4, y4, x1, y1, lp, rp);
  
  for (y in lp) {
    if (lp[y] > rp[y]) {
      x = lp[y];
      lp[y] = rp[y];
      rp[y] = x;
    }
    PicData.SetPixelMuster(lp[y], y, Muster[31].substring(0, 1 + rp[y] - lp[y]));
  }
  
  LineDirect(x1, y1, x2, y2);
  LineDirect(x2, y2, x3, y3);
  LineDirect(x3, y3, x4, y4);
  LineDirect(x4, y4, x1, y1);
}

function FillAreaSolid(x1, y1, x2, y2, x3, y3, x4, y4) {
  var x = 0;
  var y = 0;
  var lp = new Array();
  var rp = new Array();
  var offset = 0;
  
  x1 = parseInt(x1);
  y1 = parseInt(y1);
  x2 = parseInt(x2);
  y2 = parseInt(y2);
  x3 = parseInt(x3);
  y3 = parseInt(y3);
  x4 = parseInt(x4);
  y4 = parseInt(y4);
  
  Scan(x1, y1, x2, y2, lp, rp);
  Scan(x2, y2, x3, y3, lp, rp);
  Scan(x3, y3, x4, y4, lp, rp);
  Scan(x4, y4, x1, y1, lp, rp);
  
  avH = Math.min(7, Math.max(0, avH));
  for (y in lp) {
    if (lp[y] > rp[y]) {
      x = lp[y];
      lp[y] = rp[y];
      rp[y] = x;
    }
    offset = (lp[y] & 3);
    PicData.SetPixelMuster(lp[y], y, Muster[avH * 4 + (y & 3)].substring(offset, offset + 1 + rp[y] - lp[y]));
  }
  
  LineDirect(x1, y1, x2, y2);
  LineDirect(x2, y2, x3, y3);
  LineDirect(x3, y3, x4, y4);
  LineDirect(x4, y4, x1, y1);
}

function Scan(x1, y1, x2, y2, lp, rp) {
  var y = 0;
  var c = (x2 - x1) / (y2 - y1);
  var d = x1 - c * y1;
  
  if (y1 <= y2) {
    for (y = y1; y <= y2; y++) 
      rp[y] = parseInt(c * y + d);
  } else {
    for (y = y2; y <= y1; y++) 
      lp[y] = parseInt(c * y + d);
  }
}

function LineDirect(x1, y1, x2, y2) {
  var i = 0;
  
  x1 = parseInt(x1);
  y1 = parseInt(y1);
  if (x1 < 0 || x1 >= PicW || y1 < 0 || y1 >= PicH) i |= 1;
  
  x2 = parseInt(x2);
  y2 = parseInt(y2);
  if (x2 < 0 || x2 >= PicW || y2 < 0 || y2 >= PicH) i |= 2;
  
  if (i == 1) {
    PicData.SetPixel(x2, y2);
  } else if (i == 2) {
    PicData.SetPixel(x1, y1);
  }
  if (i > 2) return;
  
  if (x1 > x2) {
    i = x1;
    x1 = x2;
    x2 = i;
    i = y1;
    y1 = y2;
    y2 = i;
  }
  
  if (x1 == x2) {
    if (y1 == y2) {
      PicData.SetPixel(x1, y1);
      return;
    }
    for (i = Math.min(y1, y2); i <= Math.max(y1, y2); i++) 
      PicData.SetPixel(x1, i);
    return;
  }
  
  if (y1 == y2) {
    for (i = x1; i <= x2; i += 1) 
      PicData.SetPixel(i, y1);
    return;
  }
  
  if (x2 - x1 >= Math.abs(y1 - y2)) {
    m = (y2 - y1) / (x2 - x1);
    o = y1 - m;
    for (i = x1; i <= x2; PicData.SetPixel(i++, o += m)) 
      ;
  } else {
    if (y1 > y2) {
      i = x1;
      x1 = x2;
      x2 = i;
      i = y1;
      y1 = y2;
      y2 = i;
    }
    m = (x2 - x1) / (y2 - y1);
    o = x1 - m;
    for (i = y1; i <= y2; PicData.SetPixel(o += m, i++)) 
      ;
  }
}

function ReplaceCharacter(position, character) {
  if (position < 0 || position >= this.length) return this;
  return this.substring(0, position) + character + this.substring(position + character.length);
}

String.prototype.ReplaceChar = ReplaceCharacter;

function Picture() {
  this.lines = new Array();
  this.tempString = new String('#');
  while (this.tempString.length < PicW) 
    this.tempString += this.tempString;
  this.tempString = this.tempString.substring(0, PicW);
  while (this.lines.length < PicH) 
    this.lines.push(this.tempString);
  
  this.SetPixel = function(x, y) {
    y = parseInt(y);
    if (y < 0 || y >= PicH) return;
    if (this.lines[y].charAt(x) != ' ') this.lines[y] = this.lines[y].ReplaceChar(x, ' ');
  }
  this.SetPixelMuster = function(x, y, character) {
    y = parseInt(y);
    if (y < 0 || y >= PicH) return;
    this.lines[y] = this.lines[y].ReplaceChar(x, character);
  }
  this.SetPixelRange = function(x, y, DxInv, DyInv) {
    this.SetPixel((x - Xi) * DxInv, (y - Yi) * DyInv);
  }
  this.GetLine = function() {
    // TODO: line.length of-by-one at times...
    return this.lines.pop();
  }
}

function DoPlot(fx) {
  var i = 0;
  var tmp;
  var postfix = new Array();
  var graphic = new Array();
  var fnType = 0;
  var timeCalc = new Number(0);
  var timeDraw = new Number(0);
  
  if (fx == '') return ShowError("No function");
  
  ShowError('');
  window.status = "Working...";
  
  if (isNaN(Xi = parseFloat(FXI.value))) return ShowError("x-min invalid");
  if (isNaN(Xa = parseFloat(FXA.value))) return ShowError("x-max invalid");
  if (Xi > Xa) {
    i = Xi;
    Xi = Xa;
    Xa = i;
  }
  if (Xi == Xa) Xa += 0.0001;
  FXI.value = Xi;
  FXA.value = Xa;
  if (isNaN(Yi = parseFloat(FYI.value))) return ShowError("y-min invalid");
  if (isNaN(Ya = parseFloat(FYA.value))) return ShowError("y-max invalid");
  if (Yi > Ya) {
    i = Yi;
    Yi = Ya;
    Ya = i;
  }
  if (Yi == Ya) Ya += 0.0001;
  FYI.value = Yi;
  FYA.value = Ya;
  
  postfix = ToPostfix(Tokenize(fx));
  ShowError(tmp = SyntaxCheck(postfix));
  if (tmp != "Passed") return tmp;
  
  PicData = new Picture();
  
  for (i in postfix) 
    if (postfix[i] == "x") fnType |= 1;
    else if (postfix[i] == "y") fnType |= 2;
  if (fnType == 0) fnType = 1;
  
  timeCalc = GetTimer();
  if (fnType == 3) DoPlot3D(postfix);
  else DoPlot2D(postfix, fnType);
  timeCalc = GetTimer() - timeCalc;
  
  timeDraw = GetTimer();
  while (i = PicData.GetLine()) 
    graphic.push(i.substring(0, PicW).split(' ').join('&nbsp;'));
  window.document.getElementById('GraphHere').innerHTML = graphic.join('<br>');
  timeDraw = GetTimer() - timeDraw;
  
  window.status = 'Calculation time: ' + (Math.round(timeCalc / 100) / 10) + 's, drawing time: ' + (Math.round(timeDraw / 100) / 10) + 's';
}

function GetTimer() {
  var t = new Date();
  return ((t.getHours() * 60 + t.getMinutes()) * 60 + t.getSeconds()) * 1000 + t.getMilliseconds();
}

var Alias = new Array();
Alias['+'] = 'Add';
Alias['-'] = 'Sub';
Alias['*'] = 'Mul';
Alias['/'] = 'Div';
Alias['!'] = 'fact';
Alias['x^y'] = 'pow';
Alias['^'] = 'pow';
Alias['10^'] = 'Pow10';
Alias['div'] = 'Idiv';
Alias['<'] = 'Lt';
Alias['<='] = 'Le';
Alias['=='] = 'Eq';
Alias['!='] = 'Ne';
Alias['>='] = 'Ge';
Alias['>'] = 'Gt';
Alias['%'] = 'Pc';
Alias['%ch'] = 'Pcch';

function Add(y, x) {
  return x + y;
}

function Sub(y, x) {
  return x - y;
}

function Mul(y, x) {
  return x * y;
}

function Div(y, x) {
  return x / y;
}

function sin(x) {
  return Math.sin(x);
}

function cos(x) {
  return Math.cos(x);
}

function tan(x) {
  return Math.tan(x);
}

function sec(x) {
  return 1 / Math.cos(x);
}

function csc(x) {
  return 1 / Math.sin(x);
}

function cot(x) {
  return 1 / Math.tan(x);
}

function asin(x) {
  return FromRad(Math.asin(x));
}

function acos(x) {
  return FromRad(Math.acos(x));
}

function atan(x) {
  return FromRad(Math.atan(x));
}

function asec(x) {
  return FromRad(Math.acos(1 / x));
}

function acsc(x) {
  return FromRad(Math.asin(1 / x));
}

function acot(x) {
  return FromRad(Math.atan(1 / x));
}

function sinh(x) {
  return (Math.exp(x) - Math.exp(-x)) * 0.5;
}

function cosh(x) {
  return (Math.exp(x) + Math.exp(-x)) * 0.5;
}

function tanh(x) {
  return (Math.exp(x) - Math.exp(-x)) / (Math.exp(x) + Math.exp(-x));
}

function sech(x) {
  return 1 / cosh(x);
}

function csch(x) {
  return 1 / sinh(x);
}

function coth(x) {
  return 1 / tanh(x);
}

function asinh(x) {
  return Math.log(x + Math.sqrt(x * x + 1));
}

function acosh(x) {
  return Math.log(x + Math.sqrt(x * x - 1));
}

function atanh(x) {
  return Math.log((1 + x) / (1 - x)) * 0.5;
}

function asech(x) {
  return acosh(1 / x);
}

function acsch(x) {
  return asinh(1 / x);
}

function acoth(x) {
  return atanh(1 / x);
}

function exp(x) {
  return Math.exp(x);
}

function ln(x) {
  return Math.log(x);
}

function log(x) {
  return Math.log(x) / Math.LN10;
}

function logb(y, x) {
  return Math.log(x) / Math.log(y);
}

function pow(y, x) {
  return Math.pow(x, y);
}

function Pow10(x) {
  return Math.pow(10, x);
}

function sq(x) {
  return x * x;
}

function sqrt(x) {
  return Math.sqrt(x);
}

function abs(x) {
  return x >= 0 ? x : -x;
}

function sgn(x) {
  return x == 0 ? 0 : (x > 0 ? 1 : -1);
}

function chs(x) {
  return -x;
}

function neg(x) {
  return -x;
}

function floor(x) {
  return Math.floor(x);
}

function ceil(x) {
  return Math.ceil(x);
}

function ip(x) {
  return parseInt(x);
}

function fp(x) {
  return abs(x) - ip(abs(x));
}

function inv(x) {
  return 1 / x;
}

function Idiv(y, x) {
  return parseInt(x / y);
}

function mod(y, x) {
  return x % y;
}

function and(y, x) {
  return x & y;
}

function or(y, x) {
  return x | y;
}

function xor(y, x) {
  return x ^ y;
}

function not(x) {
  return ~ x;
}

function Lt(y, x) {
  return x < y ? 1 : 0;
}

function Le(y, x) {
  return x <= y ? 1 : 0;
}

function Eq(y, x) {
  return x == y ? 1 : 0;
}

function Ne(y, x) {
  return x != y ? 1 : 0;
}

function Ge(y, x) {
  return x >= y ? 1 : 0;
}

function Gt(y, x) {
  return x > y ? 1 : 0;
}

function min(y, x) {
  return Math.min(x, y);
}

function max(y, x) {
  return Math.max(x, y);
}

function Pc(y, x) {
  return x * y / 100;
}

function Pcch(y, x) {
  return 100 * y / x;
}

var GammaConsts = new Array(76.18009172947146, -86.50532032941677, 24.01409824083091, -1.231739572450155, 0.1208650973866179e-2, -0.5395239384953e-5);
function GammaLn(x) /* from "Numerical Recipies in C" */ {
  var n = new Number(x);
  var tmp = new Number(x + 5.5);
  var ser = new Number(1.000000000190015);
  
  tmp -= (x + 0.5) * Math.log(tmp);
  for (var i in GammaConsts) 
    ser += GammaConsts[i] / ++n;
  
  return Math.log(2.5066282746310005 * ser / x) - tmp;
}

function gamma(x) {
  return Math.exp(GammaLn(x));
}

function fact(n) {
  var r = new Number(n);
  
  if (n <= 1.0) return 1.0;
  if (n > 100.0 || (n % 1)) return Math.exp(GammaLn(n + 1.0));
  while (--n) 
    r *= n;
  return r;
}

function comb(y, x) {
  return Math.floor(0.5 + Math.exp(GammaLn(x + 1.0) - GammaLn(y + 1.0) - GammaLn(x - y + 1.0)));
}

function perm(y, x) {
  return Math.floor(0.5 + Math.exp(GammaLn(x + 1.0) - GammaLn(x - y + 1.0)));
}

function gcd(a, b) {
  if (a == 0) return (b || 1);
  return (b ? gcd(b, a % b) : a);
}

function lcm(a, b) {
  return (a / gcd(a, b)) * b;
}

function prime(n) {
  var d = new Number(5);
  var dd = new Number(4);
  var w = parseInt(n / d);
  
  if (n == 2 || n == 3 || n == 5) return 1;
  if (n < 5 || (n & 1) == 0 || (n % 3) == 0) return 0;
  while (d * w < n && d < w) 
    w = parseInt(n / (d += (dd = 6 - dd)));
  return (d * w == n ? 0 : 1);
}

function Evaluate(postfix, x, y) {
  var stack = new Array();
  var token = new String();
  
  for (var i = 0; i < postfix.length; i++) {
    token = postfix[i];
    if (token == 'x') stack.push(x);
    else if (token == 'y') stack.push(y);
    else if (token == 'pi') stack.push(Math.PI);
    else if (token == 'e') stack.push(Math.E);
    else if (token == 'rand') stack.push(Math.random());
    else if (window[Alias[token] || token]) stack.push(window[Alias[token] || token](stack.pop(), window[Alias[token] || token].length > 1 ? stack.pop() : 0));
    else stack.push(token);
    if (isNaN(stack[stack.length - 1])) return Number.NaN;
  }
  return parseFloat(stack.pop());
}

function DoPlot2D(postfix, fnType) {
  var i = 0;
  var x = 0;
  var y = 0;
  var lx = 0;
  var ly = 0;
  var Dx = (Xa - Xi) / PicW;
  var Dy = (Ya - Yi) / PicH;
  var DxInv = 1 / Dx;
  var DyInv = 1 / Dy;
  
  for (i = Xi; i <= Xa; i += 2 * Dx) 
    PicData.SetPixelRange(i, 0, DxInv, DyInv);
  if (Dx <= 0.2) {
    for (i = 0; i <= Xa; i++) 
      PicData.SetPixelRange(i, -Dy, DxInv, DyInv);
    for (i = 0; i >= Xi; i--) 
      PicData.SetPixelRange(i, -Dy, DxInv, DyInv);
  }
  for (i = Yi; i <= Ya; i += 2 * Dy) 
    PicData.SetPixelRange(0, i, DxInv, DyInv);
  if (Dy <= 0.2) {
    for (i = 0; i <= Ya; i++) 
      PicData.SetPixelRange(Dx, i, DxInv, DyInv);
    for (i = 0; i >= Yi; i--) 
      PicData.SetPixelRange(Dx, i, DxInv, DyInv);
  }
  
  if (fnType == 1) {
    x = Xi;
    for (i = 0; i < PicW; i++, x += Dx) 
      if (!isNaN(y = Evaluate(postfix, x, 0))) break;
    PicData.SetPixel(lx = i, ly = (y - Yi) * DyInv);
    for (; i < PicW; i++, x += Dx) {
      if (isNaN(y = Evaluate(postfix, x, 0))) continue;
      PicData.SetPixel(i, (y - Yi) * DyInv);
      if (PlotType) LineDirect(lx, ly, i, (y - Yi) * DyInv);
      else PicData.SetPixel(i, (y - Yi) * DyInv);
      lx = i;
      ly = (y - Yi) * DyInv;
    }
  } else {
    y = Yi;
    for (i = 0; i < PicH; i++, y += Dy) 
      if (!isNaN(x = Evaluate(postfix, 0, y))) break;
    PicData.SetPixel(lx = (x - Xi) * DxInv, ly = i);
    for (; i < PicH; i++, y += Dy) {
      if (isNaN(x = Evaluate(postfix, 0, y))) continue;
      PicData.SetPixel((x - Xi) * DxInv, i);
      if (PlotType) LineDirect(lx, ly, (x - Xi) * DxInv, i);
      else PicData.SetPixel((x - Xi) * DxInv, i);
      lx = (x - Xi) * DxInv;
      ly = i;
    }
  }
}

function DoPlot3D(postfix) {
  var x = 0;
  var y = 0;
  var z = 0;
  var i = 0;
  var j = 0;
  var za = -1000000;
  var zi = 1000000;
  var graph = new Array();
  var sinalpha = sin(3.14159 * 30 / 180);
  var cosalpha = cos(3.14159 * 30 / 180);
  var samples = (PicW == 160) ? (12) : ((PicW <= 640) ? (20) : (30));
  var cx = PicW / (samples + 0.7 * samples * cosalpha);
  var cy = 0.7 * cx;
  var cycosalpha = cy * cosalpha;
  var cysinalpha = cy * sinalpha;
  
  for (i = 0; i < samples; i++) {
    y = Yi + (Ya - Yi) * i / samples;
    for (j = 0; j < samples; j++) {
      x = Xi + (Xa - Xi) * j / samples;
      if (!isFinite(z = Evaluate(postfix, x, y))) z = 0;
      graph.push(z);
      zi = Math.min(zi, z);
      za = Math.max(za, z);
    }
  }
  
  if (zi == za) za = zi + 0.0001;
  z = (PicH / 2) / (za - zi);
  
  if (PlotType) {
    for (x = 0; x < samples - 1; x++) {
      for (y = samples - 2; y >= 0; y--) {
        avH = z * (graph[(x) + (y) * samples] + graph[(x + 1) + (y) * samples] + graph[(x + 1) + (y + 1) * samples] + graph[(x) + (y + 1) * samples]) / 4 - z * zi;
        avH = Math.floor(16 * avH / PicH);
        FillAreaSolid(x * cx + y * cycosalpha, y * cysinalpha + z * (graph[(x) + (y) * samples] - zi), (x + 1) * cx + y * cycosalpha, y * cysinalpha + z * (graph[(x + 1) + (y) * samples] - zi), (x + 1) * cx + (y + 1) * cycosalpha, (y + 1) * cysinalpha + z * (graph[(x + 1) + (y + 1) * samples] - zi), x * cx + (y + 1) * cycosalpha, (y + 1) * cysinalpha + z * (graph[(x) + (y + 1) * samples] - zi));
      }
    }
  } else {
    for (x = 0; x < samples - 1; x++) 
      for (y = samples - 2; y >= 0; y--) 
        FillAreaStandard(x * cx + y * cycosalpha, y * cysinalpha + z * (graph[(x) + (y) * samples] - zi), (x + 1) * cx + y * cycosalpha, y * cysinalpha + z * (graph[(x + 1) + (y) * samples] - zi), (x + 1) * cx + (y + 1) * cycosalpha, (y + 1) * cysinalpha + z * (graph[(x + 1) + (y + 1) * samples] - zi), x * cx + (y + 1) * cycosalpha, (y + 1) * cysinalpha + z * (graph[(x) + (y + 1) * samples] - zi));
  }
}

function Keep() {
  if (FF.value == "") return;
  FM.options[FM.length] = new Option(FF.value, FXI.value + " " + FXA.value + " " + FYI.value + " " + FYA.value);
  FM.options[FM.length - 1].selected = true;
}

function Recall() {
  if (FM.length == 0) return;
  var m = FM.options[FM.selectedIndex].value.split(" ");
  FF.value = FM.options[FM.selectedIndex].text;
  FXI.value = m[0];
  FXA.value = m[1];
  FYI.value = m[2];
  FYA.value = m[3];
}

function Remove() {
  if (FM.length < 1) return;
  for (var i = FM.selectedIndex + 1; i < FM.length; i++) 
    FM.options[i - 1].text = FM.options[i].text, FM.options[i - 1].value = FM.options[i].value;
  if (FM.selectedIndex >= --FM.length && FM.length) FM.options[FM.length - 1].selected = true;
}

function ShowError(showThis) {
  window.document.forms['plotForm']['errorHere'].value = showThis;
}

function SetSize(size) {
  PicW = parseInt(size.split('x')[0]);
  PicH = parseInt(size.split('x')[1]);
}

function DoOnLoad() {
  FF = window.document.forms['plotForm']['fnfx'];
  FXI = window.document.forms['plotForm']['xmin'];
  FXA = window.document.forms['plotForm']['xmax'];
  FYI = window.document.forms['plotForm']['ymin'];
  FYA = window.document.forms['plotForm']['ymax'];
  FM = window.document.forms['plotForm']['memory'];
  FF.focus();
}
