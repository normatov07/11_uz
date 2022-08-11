window.addEventListener("resize", function() {
  console.log(window.innerWidth);
    let remTag = document.getElementsByTagName("main")[0];
    if (window.innerWidth < 1100) {
        remTag.classList.remove("wrapper");
    }
    console.log(window.innerWidth);
    if (window.innerWidth > 1100) {
        remTag.classList.add("wrapper");
    }
  });
// console.log("asdas");

if (window.innerWidth < 1100) {
    let remTag = document.getElementsByTagName("main")[0];
    remTag.classList.remove("wrapper");
}

function openNav() {
    document.getElementsByClassName("collapse")[1].style.whiteSpace = "normal";
    document.getElementById("mySidenav").style.width = "250px";
  }
  
  function closeNav() {
    document.getElementsByClassName("collapse")[1].style.whiteSpace = "nowrap";
    document.getElementById("mySidenav").style.width = "0";
  }