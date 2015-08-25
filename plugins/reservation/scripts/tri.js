function afficher_cacher(id)
{
if(document.getElementById(id).style.display=="none")
{
document.getElementById(id).style.display="inline";
document.getElementById("bouton_"+id).style.display="none";
}
else
{
document.getElementById(id).style.display="none";
document.getElementById("bouton_"+id).style.display="inline";
}
return true;
}
/*
function afficher_cacher(id)
{
if(document.getElementById(id).style.visibility=="hidden")
{
document.getElementById(id).style.visibility="visible";
}
else
{
document.getElementById(id).style.visibility="hidden";
}
return true;
}
*/


function sortTable (tb, n) {

var iter = 0;
while (!tb.tagName || tb.tagName.toLowerCase()
!= "table") {
if (!tb.parentNode) return;
tb = tb.parentNode;
}
if (tb.tBodies && tb.tBodies[0]) tb = tb.tBodies[0];

// Tri par sélection
 
var reg = /^\d+(\.\d+)?$/g;
var index = 0, value = null, minvalue = null;
console.log(tb.rows.length);
for (var i= tb.rows.length -2; i >= 0; i -= 1) {
    minvalue = value = null;
    index = -1;
    for (var j=i; j >= 0; j -= 1) {
        value = tb.rows[j].cells[n].firstChild.nodeValue;
        if (!isNaN(value)) value = parseFloat(value);
        if (minvalue == null || value < minvalue) { index = j; minvalue = value; }
    }

    if (index != -1) {
    var row = tb.rows[index];
    if (row) {
    tb.removeChild(row);
    tb.appendChild(row);
    }}

}
}


/*


function sortTable (tb, n) {

var iter = 0;
largeurTableau = tb.parentNode.parentNode.cells.length;



while (!tb.tagName || tb.tagName.toLowerCase()
!= "table") {
if (!tb.parentNode) return;
tb = tb.parentNode;
}
if (tb.tBodies && tb.tBodies[0]) tb = tb.tBodies[0];

// Tri par sélection
 
var reg = /^\d+(\.\d+)?$/g;
var index = 0, value = null, minvalue = null;


for (var i= tb.rows.length -2; i >= 0; i -= 1) {
    minvalue = value = null;
    index = -1;
    for (var j=i; j >= 0; j -= 1) {
        value = tb.rows[j].cells[n].firstChild.nodeValue;
        if (!isNaN(value)) value = parseFloat(value);
        if (minvalue == null || value < minvalue) { index = j; minvalue = value; }
    }

    console.log("toto");
   

    if (index != -1) {
    var row = tb.rows[index];
    if (row) {
    tb.removeChild(row);
    tb.appendChild(row);
    }}

}




}


 /*console.log(tb.rows[index].cells.length);
    if(tb.rows[index].cells.length < largeurTableau) { // si c'est inferieur, il faut regarder en haut ET peut etre en bas ! 
        console.log(tb.rows[index].cells.length);        
    }
    else { // si c'est egal : il faut regarder en bas et faire suivre (en triant egalement le bloc)
        console.log(tb.rows[index].cells.length); 
    } */