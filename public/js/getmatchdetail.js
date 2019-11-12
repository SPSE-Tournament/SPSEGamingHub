$('.modal-score-write').on('show.bs.modal', function (event) {
let button = $(event.relatedTarget)
let matchId = button.data('matchid')
let modal = $(this)
let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
  let start = this.responseText.indexOf('<!-- MatchStart -->');
  let end = this.responseText.indexOf("<!-- MatchEnd -->") + "<!-- MatchEnd -->".length;
   document.querySelector(".modal-match-detail-body").innerHTML=this.responseText.slice(start,end);
}
};
xhttp.open("GET", "events/getmatch/" + matchId, true);
xhttp.send();
});

function work() {
  console.log("ok")
}
