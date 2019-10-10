let profile;

function loadMessage(str) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      let start = this.responseText.indexOf('<!-- MessageStart -->');
      let end = this.responseText.indexOf('<!-- MessageEnd -->') + '<!-- MessageEnd -->'.length;
       document.getElementById("nav-messages").innerHTML = this.responseText.slice(start,end);
       console.log("Start="+start);
       console.log("End="+end);
       console.log(this.responseText);
    }
  };
  xhttp.open("GET", "profile/getmessage/"+str, true);
  xhttp.send();
}
