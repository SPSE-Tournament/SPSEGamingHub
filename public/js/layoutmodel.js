
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
      fetch(`/api/teams/livesearch/${str}`)
      .then(res=>res.json())
      .then(hint => {
        console.log(hint);
        for (let i of hint) {
          document.querySelector("."+hintelem).innerHTML += i.name;
        }
        document.querySelector('.'+livesearchelem).style.display = "block";
      })
      .catch(err => console.error(err));
    }

    function selectUser(elem,str) {
      document.getElementById(elem).value = str;
      document.querySelector('.livesearch').style.display = "none";
      document.querySelector('.livesearch-verify').style.display = "none";
    }


    function toggleNav() {
      if (document.querySelector(".overlay").style.width == "100%")
        document.querySelector(".overlay").style.width = "0%";
      else
        document.querySelector(".overlay").style.width = "100%";

    }

    $('.toast').toast('show');
