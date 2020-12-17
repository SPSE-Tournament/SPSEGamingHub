
function showInvitePlayerBox() {
  if (document.querySelector('.invite-player').style.display == 'none') {
    document.querySelector('.invite-player').style.display = 'block';
    document.querySelector('.invite-button').style.display = 'none';
  } else {
    document.querySelector('.invite-player').style.display = 'none';
    document.querySelector('.invite-button').style.display = 'block';

  }
}

$(".modal-team-detail").on("show.bs.modal", (event) =>{
  let button = event.relatedTarget;
  fetch('api/teams/id/'+button.dataset.teamid)
    .then(response => response.json())
    .then(team => {
      document.querySelector('.modal-team-title').innerHTML = team.team_name;
      team.players.forEach(p=>{
        let li = document.createElement("li")
        li.innerHTML = p.uname
        document.querySelector(".modal-team-userlist").append(li)
      })
      document.getElementsByName('team-name').forEach(el=>{
        el.value = team.team_name
      })
      document.getElementsByName('team-id').forEach(el=>{
        el.value = team.team_id
      })
      document.getElementsByName('team-game').forEach(el=>{
        el.value = team.game_name
      })
    })
    .catch(err => console.error(err));
 })
$(".modal-team-detail").on("hide.bs.modal", (event) =>{

  document.querySelector(".modal-team-userlist").innerHTML=""


})
