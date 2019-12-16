function getBracket() {
  let eventId = document.querySelector(".event-id-butt").value;
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    let start = this.responseText.indexOf('<!-- BracketStart -->');
    let end = this.responseText.indexOf("<!-- BracketEnd -->") + "<!-- BracketEnd -->".length;
     document.querySelector(".bracket-wrapper").innerHTML=this.responseText.slice(start,end);
  }
  };
  xhttp.open("GET", "events/" + url + "/bracket", true);
  xhttp.send();
}
setInterval(function(){getBracket()}, 1000)
