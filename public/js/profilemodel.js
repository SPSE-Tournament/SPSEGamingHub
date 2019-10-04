let profile;
function loadDoc() {
var xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    let start = this.responseText.indexOf('<header');
    let end = this.responseText.indexOf("</header>") + "</header>".length;
     document.getElementById("nav-messages").innerHTML = this.responseText.slice(start,end);
  }
};
xhttp.open("GET", "profile/messages", true);
xhttp.send();
}

function loadUserLiveText(str) {
  if (str.length==0) {
    document.getElementById("livesearch").innerHTML="";
    document.getElementById("livesearch").style.border="0px";
    return;
  }
  if (window.XMLHttpRequest) {
    xmlhttp=new XMLHttpRequest();
  } else {
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function() {
    if (this.readyState==4 && this.status==200) {
      let start = this.responseText.indexOf('<footer');
      let end = this.responseText.indexOf("</footer>") + "</footer>".length;
      document.getElementById("livesearch").innerHTML = this.responseText.slice(start,end);
      document.getElementById('livesearch').style.display = "block;";
      document.getElementById("livesearch").style.border="1px solid #A5ACB2";
    }
  }
  xmlhttp.open("GET","profile/getusers/"+str,true);
  xmlhttp.send();
}

function showNewMessage(id) {
  if (document.querySelector(id).style.display == "none") {
    document.querySelector(id).style.display = "block";
  } else {
    document.querySelector(id).style.display = "none";
  }
}

function selectUser(str) {
  document.getElementById('newMessage_username').value = str;
  document.getElementById('livesearch').style.display = "none";
}

function closeNewMessage(id) {
  document.querySelector(id).style.display = "none";
}
loadDoc();
