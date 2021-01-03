document.querySelector("#eventLink").classList.add("nav-links-selected");

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
  fetch("events/refreshmatches/" + eventId)
  .then(()=> {
    getBracket()
  })
  .catch(err => console.error(err))
}

function getBracket() {
  let eventId = document.querySelector(".event-id").value;
  fetch("/api/users/checkpl/x")
  .then(res => res.json())
  .then(pl => {
    fetch(`/api/bracket/${eventId}`)
    .then(res=>res.json())
    .then(res=>{
      const bracketEl = document.querySelector('.bracket');
      bracketEl.innerHTML = "";
      res.forEach(round => {
        const divEachRound = document.createElement("divEachRound");
        divEachRound.setAttribute("class", "each-round");
        const h2 = document.createElement("h2");
        h2.setAttribute("class", "text-light");
        h2.innerHTML = round[0].match_description;
        divEachRound.append(h2);
        bracketEl.append(divEachRound);
        round.forEach(match => {
          const divEachMatch = document.createElement("div");
          divEachMatch.setAttribute("class", "eachmatch m-3 bg-space rounded d-inline-flex flex-row justify-content-end align-items-center")
          divEachRound.append(divEachMatch)
          const divNames = document.createElement("div");
          divNames.setAttribute("class", "d-flex flex-column flex-fill p-3 mr-auto")
          const divScores = document.createElement("div");
          divScores.setAttribute("class", "d-flex align-items-center justify-content-center flex-column text-center p-2 bg-red-main rounded")
          divEachMatch.append(divNames, divScores)
          const divNamesInner1 = document.createElement("div");
          divNamesInner1.setAttribute("class", "d-flex flex-row align-items-center first-team-name text-center")
          const divNamesInner2 = document.createElement("div");
          divNamesInner2.setAttribute("class", "d-flex flex-row align-items-center second-team-name text-center")
          divNames.append(divNamesInner1, divNamesInner2)
          const name1 = document.createElement("h5")
          name1.innerHTML = match.match_first_team_name;
          const name2 = document.createElement("h5")
          name2.innerHTML = match.match_second_team_name;
          divNamesInner1.append(name1)
          divNamesInner2.append(name2)
          const score1 = document.createElement("h5")
          score1.innerHTML = match.match_first_team_score;
          const score2 = document.createElement("h5")
          score2.innerHTML = match.match_second_team_score;
          divScores.append(score1,score2)
          if (match.match_status == "finished") {
            const divStatus = document.createElement("div")
            divStatus.setAttribute("class", "match-status")
            const statusImg = document.createElement("img")
            setMultAttributes(statusImg, {
              "src":"/public/images/i-ok.png",
              "height":"40"
            })
            divStatus.append(statusImg)
            divEachRound.append(divStatus)
          }
          if (pl.response) {
            const divScoreWrite = document.createElement("div")
            divScoreWrite.setAttribute("class", "scorewrite")
            const scoreWriteButton = document.createElement("button")
            setMultAttributes(scoreWriteButton, {
              "class": "btn btn-main mt-1",
              "type": "button",
              "data-toggle": "modal",
              "data-target": ".modal-score-write",
              "data-matchid": match.match_id
            });
            const imgScore = document.createElement("img")
            imgScore.setAttribute("src", "/public/images/i-score.png")
            scoreWriteButton.append(imgScore)
            divScoreWrite.append(scoreWriteButton)
            divEachRound.append(divScoreWrite)
          }
        })
      })
    })
    .catch(err => console.error(err))
  })


}

$('.modal-score-write').on('show.bs.modal', function (event) {
  button = event.relatedTarget;
  fetch('api/match/'+button.dataset.matchid)
    .then(response => response.json())
    .then(match => {
      document.querySelector('.match-id-input').value = match.match_id;
      document.querySelector(".match-first-name").innerHTML = match.match_first_team_name;
      document.querySelector(".match-second-name").innerHTML = match.match_second_team_name;
      document.querySelector('.match-first-score').value = match.match_first_team_score;
      document.querySelector('.match-second-score').value = match.match_second_team_score;
      document.querySelector('.match-current-status').value = match.match_status;
      document.querySelector('.match-current-status').innerHTML = match.match_status;
    })
    .catch(err => console.error(err));
});


$('#nav-tab a[href="#nav-bracket"]').tab('show')

refreshMatches();
setInterval(function(){refreshMatches();}, 10000)
