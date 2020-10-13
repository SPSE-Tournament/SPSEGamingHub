document.querySelector("#adminLink").classList.add("nav-links-selected");
document.querySelector("#homeLink").classList.remove("nav-links-selected");
document.querySelector("#eventLink").classList.remove("nav-links-selected");
document.querySelector("#messagesLink").classList.remove("nav-links-selected");
document.querySelector(".live-form-game-update").style.display = "none";


$('.modal-log-detail').on('show.bs.modal', function(event) {
  let button = $(event.relatedTarget)
  let logId = button.data('logid')
  let modal = $(this)
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      let start = this.responseText.indexOf("<!-- LogDetailStart -->");
      let end = this.responseText.indexOf("<!-- LogDetailEnd -->") + "<!-- LogDetailEnd -->".length;
      document.querySelector(".modal-log-detail-body").innerHTML = this.responseText.slice(start, end);
    }
  };
  xhttp.open("GET", "administration/getlog/" + logId, true);
  xhttp.send();
  modal.find('.modal-title').text('Log')
});

function getGameForm(value) {
  console.log(value)
  if (value)
  document.querySelector(".live-form-game-update").style.display = "block";
  else
  document.querySelector(".live-form-game-update").style.display = "none";
  fetch("/api/games/id/"+value)
  .then(res => res.json())
  .then(game => {
    document.querySelector('#game-edit-id').value = game.game_id
    document.querySelector('#game-edit-name').value = game.game_name
    document.querySelector('#game-edit-shortname').value = game.game_short_name
    document.querySelector('#game-edit-playerlimit').value = game.game_playerlimitperteam
  })
  .catch(err => console.error(err));
}

function changeVerificationSelect(value) {
  for (let i = 0; i < document.querySelector(".user-input-group").childNodes.length; i++)
    document.querySelector(".user-input-group").removeChild(document.querySelector(".user-input-group").childNodes[0])
  if (value == "usr") {
    let i = document.createElement("input")
    i.setAttribute("class", "form-control my-1")
    i.setAttribute("id", "newMessage_username")
    i.setAttribute("placeholder", "Username#hexid")
    i.setAttribute("name", "verification_username")
    i.setAttribute("onkeyup", 'loadUserLiveText(this.value,"livesearch-verify","hint-verify")')
    document.querySelector(".user-input-group").append(i)
  } else if (value == "team") {
    let i = document.createElement("input")
    i.setAttribute("class", "form-control my-1")
    i.setAttribute("id", "newMessage_username")
    i.setAttribute("name", "verification_teamname")
    i.setAttribute("placeholder", "Teamname")
    i.setAttribute("onkeyup", 'loadTeamLiveText(this.value,"livesearch-verify","hint-verify")')
    document.querySelector(".user-input-group").append(i)
  }
}

function addUserInput() {
  document.querySelector('.user-input-group')
}
