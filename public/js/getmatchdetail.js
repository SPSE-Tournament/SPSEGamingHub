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
