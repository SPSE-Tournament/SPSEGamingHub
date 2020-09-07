document.querySelector("#eventLink").classList.add("nav-links-selected");
document.querySelector("#homeLink").classList.remove("nav-links-selected");
//document.querySelector("#adminLink").classList.remove("nav-links-selected");

let countDownDate = new Date(document.querySelector('.event-timestamp').value).getTime();
setInterval(function() {
  let now = new Date().getTime();
  let distance = countDownDate - now;
  let days = Math.floor(distance / (1000 * 60 * 60 * 24));
  let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  let seconds = Math.floor((distance % (1000 * 60)) / 1000);
  let response = "<span class='bg-red-main rounded p-2'>" + days + "d : " + hours + "h : " + minutes + "m : " + seconds + "s</span>";
  document.querySelector(".timer-countdown").innerHTML = response;
  if (distance < 0) {
    clearInterval();
    document.querySelector(".timer-countdown").innerHTML = "";
  }
}, 1000);

function toggleBracketFullscreen() {
  if (!document.fullscreenElement) {
    document.querySelector(".tab-content-profile").requestFullscreen();
    document.querySelector(".fullscreenEventName").innerHTML = document.querySelector(".event-name").innerHTML;
    for (let i of document.querySelectorAll(".scorewrite"))
      i.style.display = "none";
} else {
  if (document.exitFullscreen) {
    document.exitFullscreen();
    document.querySelector(".fullscreenEventName").innerHTML = "";
    for (let i of document.querySelectorAll(".scorewrite"))
      i.style.display = "block";
  }
}
}

function refreshMatches() {
  let eventId = document.querySelector(".event-id").value;
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
  }
  };
  xhttp.open("GET", "events/refreshmatches/" + eventId, true);
  xhttp.send();
}

function getBracket() {
  let eventId = document.querySelector(".event-id").value;
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    let start = this.responseText.indexOf('<!-- BracketStart -->');
    let end = this.responseText.indexOf("<!-- BracketEnd -->") + "<!-- BracketEnd -->".length;
     document.querySelector(".bracket-wrapper").innerHTML=this.responseText.slice(start,end);
  }
  };
  xhttp.open("GET", "events/getbracket/" + eventId, true);
  xhttp.send();
}

$('#nav-tab a[href="#nav-bracket"]').tab('show')
getBracket();
setInterval(function(){refreshMatches();getBracket();}, 10000)
