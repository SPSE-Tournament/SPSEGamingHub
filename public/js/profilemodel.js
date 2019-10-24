function loadTeamDetail(str) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      let start = this.responseText.indexOf('<!-- TeamStart -->');
      let end = this.responseText.indexOf('<!-- TeamEnd -->') + '<!-- TeamEnd -->'.length;
       document.querySelector(".modal-content-team-preview").innerHTML = this.responseText.slice(start,end);
    }
  };
  xhttp.open("GET", "profile/getteam/"+str, true);
  xhttp.send();
}

function showInvitePlayerBox() {
  if (document.querySelector('.invite-player').style.display == 'none') {
    document.querySelector('.invite-player').style.display = 'block';
    document.querySelector('.invite-button').style.display = 'none';
  } else {
    document.querySelector('.invite-player').style.display = 'none';
    document.querySelector('.invite-button').style.display = 'block';

  }
}
