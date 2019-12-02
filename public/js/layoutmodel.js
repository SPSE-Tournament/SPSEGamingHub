$(function () {
$('[data-toggle="tooltip"]').tooltip()
})
let curPageMes;
function loadMessages(msgType,page) {
      showInbox(msgType,page);
        curPageMes = parseInt(page);
}

  function showInbox(msgType,page) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        let start = this.responseText.indexOf('<!-- MessagesStart -->');
        let end = this.responseText.indexOf("<!-- MessagesEnd -->") + "<!-- MessagesEnd -->".length;
        let response = this.responseText.slice(start,end);
          document.querySelector('.messages-content').innerHTML = response;
      }
    };
    xhttp.open("GET", "profile/messages/"+msgType+"/"+page, true);
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

    function loadUserLiveText(str, livesearchelem, hintelem) {
      if (str.length < 3) {
        document.querySelector("."+hintelem).innerHTML ="";
        document.querySelector('.'+livesearchelem).style.display = "none";
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
          document.querySelector("."+hintelem).innerHTML = this.responseText.slice(start,end);
          document.querySelector('.'+livesearchelem).style.display = "block";
        }
      }
      xmlhttp.open("GET","profile/getusers/"+str,true);
      xmlhttp.send();
    }

    function loadTeamLiveText(str, livesearchelem, hintelem) {
      if (str.length < 3) {
        document.querySelector("."+hintelem).innerHTML ="";
        document.querySelector('.'+livesearchelem).style.display = "none";
        return;
      }
      if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
      } else {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.onreadystatechange=function() {
        if (this.readyState==4 && this.status==200) {
          let start = this.responseText.indexOf('<!-- TeamLiveTextStart -->');
          let end = this.responseText.indexOf("<!-- TeamLiveTextEnd -->") + "<!-- TeamLiveTextEnd -->".length;
          document.querySelector("."+hintelem).innerHTML = this.responseText.slice(start,end);
          document.querySelector('.'+livesearchelem).style.display = "block";
        }
      }
      xmlhttp.open("GET","profile/getteamhint/"+str,true);
      xmlhttp.send();
    }

    function selectUser(elem,str) {
      document.getElementById(elem).value = str;
      document.querySelector('.livesearch').style.display = "none";
      document.querySelector('.livesearch-verify').style.display = "none";
    }


    function openNav() {
      document.getElementById("slide-navigation").style.width = "100%";
    }

    function closeNav() {
      document.getElementById("slide-navigation").style.width = "0%";
    }



    $('.toast').toast('show');
