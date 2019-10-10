function loadMessages() {
var xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    let start = this.responseText.indexOf('<!-- MessagesStart -->');
    let end = this.responseText.indexOf("<!-- MessagesEnd -->") + "<!-- MessagesEnd -->".length;
     document.querySelector('.messages-content').innerHTML = this.responseText.slice(start,end);
  }
};
xhttp.open("GET", "profile/messages", true);
xhttp.send();
}
  function toggleMessageNav() {
    if (document.querySelector('.messages-wrapp').style.width == '0px') {
      document.querySelector('.messages-button').style.visibility = "hidden";
      document.querySelector('.messages-wrapp').style.width = '20vw';
    } else {
      document.querySelector('.messages-button').style.visibility = "hidden";
      document.querySelector('.messages-wrapp').style.width = '0px';
    }

    document.querySelector('.messages-button').style.right = document.querySelector('.messages-wrapp').style.width;
    setTimeout(function() {
      document.querySelector('.messages-button').style.visibility = "visible";

    }, 500)
    }

    function showNewMessage(id) {
      if (document.querySelector(id).style.display == "none") {
        document.querySelector(id).style.display = "block";
      } else {
        document.querySelector(id).style.display = "none";
      }
    }
    function closeNewMessage(id) {
      document.querySelector(id).style.display = "none";
    }

    function loadUserLiveText(str) {
      if (str.length < 3) {
        document.querySelector(".hint").innerHTML ="";
        document.querySelector('.livesearch').style.display = "none";
        return;
      }
      if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
      } else {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.onreadystatechange=function() {
        if (this.readyState==4 && this.status==200) {
          let start = this.responseText.indexOf('<!-- UserLiveTextStart -->');
          let end = this.responseText.indexOf("<!-- UserLiveTextEnd -->") + "<!-- UserLiveTextEnd -->".length;
          document.querySelector(".hint").innerHTML = this.responseText.slice(start,end);
          document.querySelector('.livesearch').style.display = "block";
        }
      }
      xmlhttp.open("GET","profile/getusers/"+str,true);
      xmlhttp.send();
    }

    function selectUser(str) {
      document.getElementById('newMessage_username').value = str;
      document.querySelector('.livesearch').style.display = "none";
    }


    $('.toast').toast('show');
